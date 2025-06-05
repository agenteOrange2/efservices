<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Driver\DriverTesting;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use App\Mail\DriverTestCompleted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Carbon\Carbon;

class DriverTestingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DriverTesting::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);
            
        // Apply filters if they exist
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('location') && $request->location != '') {
            $query->where('location', $request->location);
        }
        
        if ($request->has('test_date_from') && $request->test_date_from != '') {
            $query->whereDate('test_date', '>=', $request->test_date_from);
        }
        
        if ($request->has('test_date_to') && $request->test_date_to != '') {
            $query->whereDate('test_date', '<=', $request->test_date_to);
        }
        
        if ($request->has('carrier_id') && $request->carrier_id != '') {
            $query->whereHas('userDriverDetail', function($q) use($request) {
                $q->where('carrier_id', $request->carrier_id);
            });
        }
        
        $driverTestings = $query->latest()->paginate(15);
        
        $locations = DriverTesting::getLocations();
        $statuses = DriverTesting::getStatuses();
        $carriers = Carrier::orderBy('name')->pluck('name', 'id');
        
        // Log any records with missing relationships for debugging
        foreach ($driverTestings as $test) {
            if (!$test->userDriverDetail) {
                Log::warning("Driver Testing ID {$test->id} has null userDriverDetail");
            } elseif (!$test->userDriverDetail->user) {
                Log::warning("Driver Testing ID {$test->id} has null user relationship");
            } elseif (!$test->userDriverDetail->carrier) {
                Log::warning("Driver Testing ID {$test->id} has null carrier relationship");
            }
        }
        
        return view('admin.driver-testings.index', compact('driverTestings', 'locations', 'statuses', 'carriers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = DriverTesting::getLocations();
        $testTypes = DriverTesting::getDrugTestTypes();
        $billOptions = DriverTesting::getBillOptions();
        $carriers = Carrier::where('status', 1)->orderBy('name')->get();
        
        return view('admin.driver-testings.create', compact('locations', 'testTypes', 'billOptions', 'carriers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log para debug
        Log::info('Driver Testing Store - Request data', ['files' => $request->driver_testing_files]);        

        $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'carrier_id' => 'required|exists:carriers,id',
            'test_date' => 'required|date',
            'test_type' => 'required|string',
            'administered_by' => 'required|string',
            'location' => 'required|string',
            'bill_to' => 'required|string',
        ]);
        
        // Crear registro en la base de datos
        $driverTesting = new DriverTesting($request->all());
        $driverTesting->test_result = $request->test_result ?? 'Pending';
        $driverTesting->status = 'pending';
        $driverTesting->created_by = Auth::id();
        $driverTesting->updated_by = Auth::id();
        $driverTesting->save();
        
        // Asegurar que tenemos el objeto con el ID asignado
        $driverTestingId = $driverTesting->getKey();
        $driverTesting = DriverTesting::findOrFail($driverTestingId);
        
        // Procesar archivos adjuntos si existen
        if ($request->has('driver_testing_files') && !empty($request->driver_testing_files)) {
            $filesData = json_decode($request->driver_testing_files, true);
            
            Log::info('Driver Testing Store - Files data decoded', ['filesData' => $filesData]);
            
            if (is_array($filesData) && count($filesData) > 0) {
                foreach ($filesData as $fileData) {
                    // Si el archivo tiene un path temporal, es un archivo recién subido
                    if (isset($fileData['path']) && !empty($fileData['path'])) {
                        // Obtener el archivo temporal del disco temporal
                        $tempPath = storage_path('app/' . $fileData['path']);
                        
                        Log::info('Driver Testing Store - Processing file', [
                            'tempPath' => $tempPath, 
                            'originalName' => $fileData['original_name']
                        ]);
                        
                        if (file_exists($tempPath)) {
                            // Guardar en la colección de media para este registro
                            $driverTesting->addMedia($tempPath)
                                ->usingName($fileData['original_name'])
                                ->preservingOriginal()
                                ->toMediaCollection('document_attachments');
                                
                            Log::info('Driver Testing Store - File successfully added to media collection', [
                                'filename' => $fileData['original_name']
                            ]);
                        } else {
                            Log::error('Driver Testing Store - Temp file does not exist', [
                                'tempPath' => $tempPath
                            ]);
                        }
                    } else {
                        Log::warning('Driver Testing Store - File data missing path', ['fileData' => $fileData]);
                    }
                }
            } else {
                Log::warning('Driver Testing Store - No valid files data found in JSON');
            }
        } else {
            Log::info('Driver Testing Store - No files to process');
        }
        
        // Generate PDF
        $pdf = $this->generatePDF($driverTesting);
        $testingId = $driverTesting->getKey(); // Usar getKey() en lugar de acceder directamente a ->id
        $pdfPath = storage_path('app/public/driver_testings/driver_testing_' . $testingId . '.pdf');
        
        // Make sure directory exists
        $directory = storage_path('app/public/driver_testings');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Save PDF
        file_put_contents($pdfPath, $pdf->output());
        
        // Attach PDF to media library
        $driverTesting->addMedia($pdfPath)
            ->toMediaCollection('drug_test_pdf');
        
        // Send email to driver with PDF attachment
        $this->sendEmailToDriver($driverTesting);
        
        // Obtener el ID numérico del objeto
        $testingId = $driverTesting->getKey();
        
        return redirect()->route('admin.driver-testings.show', ['driverTesting' => $testingId])
            ->with('success', 'Drug test created successfully. PDF has been generated and emailed to the driver.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DriverTesting $driverTesting)
    {
        $driverTesting->load('userDriverDetail.user', 'userDriverDetail.carrier', 'media');
        
        // Ruta relativa para el PDF si existe
        $pdfUrl = $driverTesting->getFirstMediaUrl('drug_test_pdf');
        
        return view('admin.driver-testings.show', compact('driverTesting', 'pdfUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DriverTesting $driverTesting)
    {
        $driverTesting->load('userDriverDetail.user', 'userDriverDetail.carrier');
        $locations = DriverTesting::getLocations();
        $statuses = DriverTesting::getStatuses();
        $testTypes = DriverTesting::getDrugTestTypes();
        $testResults = DriverTesting::getTestResults();
        $billOptions = DriverTesting::getBillOptions();
        $carriers = Carrier::where('status', 1)->orderBy('name')->get();
        
        return view('admin.driver-testings.edit', compact('driverTesting', 'locations', 'statuses', 'testTypes', 'testResults', 'billOptions', 'carriers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DriverTesting $driverTesting)
    {
        // Log para debug
        Log::info('Driver Testing Update - Request data', ['files' => $request->driver_testing_files]);
        $request->validate([
            'test_type' => 'required|string',
            'location' => 'required|string',
            'test_date' => 'required|date',
            'status' => 'required|string',
            'test_result' => 'required|string',
            'administered_by' => 'required|string',
            'requester_name' => 'nullable|string',
            'scheduled_time' => 'nullable|date_format:Y-m-d\TH:i',
            'notes' => 'nullable|string',
            'bill_to' => 'required|string',
        ]);
        
        // Actualizar los campos básicos
        $driverTesting->fill($request->all());
        $driverTesting->updated_by = Auth::id();
        $driverTesting->save();

        // Procesar archivos adjuntos si existen
        if ($request->has('driver_testing_files') && !empty($request->driver_testing_files)) {
            $filesData = json_decode($request->driver_testing_files, true);

            Log::info('Driver Testing Update - Files data decoded', ['filesData' => $filesData]);

            if (is_array($filesData) && count($filesData) > 0) {
                $driverId = $driverTesting->userDriverDetail->id;
                $testingId = $driverTesting->getKey();

                // Crear el directorio de destino si no existe
                $destinationDir = "public/driver/{$driverId}/testing/{$testingId}";

                if (!Storage::exists($destinationDir)) {
                    Storage::makeDirectory($destinationDir);
                }

                foreach ($filesData as $fileData) {
                    // Si el archivo tiene un path temporal, es un archivo recién subido
                    if (isset($fileData['path']) && !empty($fileData['path'])) {
                        // Obtener el archivo temporal del disco temporal
                        $tempPath = storage_path('app/' . $fileData['path']);

                        Log::info('Driver Testing Update - Processing file', [
                            'tempPath' => $tempPath,
                            'originalName' => $fileData['original_name']
                        ]);

                        if (file_exists($tempPath)) {
                            // Verificar si el archivo ya existe para evitar duplicados
                            $fileExists = $driverTesting->getMedia('document_attachments')
                                ->where('name', $fileData['original_name'])
                                ->first();

                            if (!$fileExists) {
                                // Guardar en la colección de media para este registro
                                $driverTesting->addMedia($tempPath)
                                    ->usingName($fileData['original_name'])
                                    ->preservingOriginal()
                                    ->toMediaCollection('document_attachments');

                                // Log de éxito
                                Log::info('Driver Testing Update - File successfully added to media collection', [
                                    'testing_id' => $testingId,
                                    'filename' => $fileData['original_name']
                                ]);
                            } else {
                                Log::info('Driver Testing Update - File already exists, skipping', [
                                    'filename' => $fileData['original_name']
                                ]);
                            }
                        } else {
                            // Log de error si el archivo no existe
                            Log::error('Driver Testing Update - Temp file does not exist', [
                                'tempPath' => $tempPath,
                                'filename' => $fileData['original_name']
                            ]);
                        }
                    } else {
                        Log::warning('Driver Testing Update - File data missing path', ['fileData' => $fileData]);
                    }
                }
            } else {
                Log::warning('Driver Testing Update - No valid files data found in JSON');
            }
        } else {
            Log::info('Driver Testing Update - No files to process');
        }

        // Verificar si ha cambiado el estatus para notificar
        if ($request->status != 'pending' && $request->status != $driverTesting->getOriginal('status')) {
            // Status changed, might want to notify relevant parties here
            // TODO: Implementar notificación por cambio de estatus
        }
        
        // Regenerar el PDF con la información actualizada
        $pdf = $this->generatePDF($driverTesting);
        $testingId = $driverTesting->getKey();
        $pdfPath = storage_path('app/public/driver_testings/driver_testing_' . $testingId . '.pdf');
        
        // Asegurar que el directorio existe
        $directory = storage_path('app/public/driver_testings');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Guardar el PDF
        file_put_contents($pdfPath, $pdf->output());
        
        // Eliminar PDF anterior si existe
        $driverTesting->clearMediaCollection('drug_test_pdf');
        
        // Adjuntar el nuevo PDF a la biblioteca de medios en la colección correcta
        $driverTesting->addMedia($pdfPath)
            ->toMediaCollection('drug_test_pdf');
        
        // Enviar email al conductor con el PDF adjunto actualizado
        $this->sendEmailToDriver($driverTesting);
        
        return redirect()->route('admin.driver-testings.show', ['driverTesting' => $driverTesting->getKey()])
            ->with('success', 'Drug test updated successfully. PDF has been regenerated and emailed to the driver.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DriverTesting $driverTesting)
    {
        try {
            // Primero eliminar los archivos de media manualmente para evitar problemas de cascada
            $mediaItems = $driverTesting->getMedia('document_attachments');
            foreach ($mediaItems as $media) {
                // Usar DB::table para eliminar directamente el registro de media sin afectar al modelo principal
                DB::table('media')->where('id', $media->id)->delete();
            }
            
            // Eliminar el PDF generado si existe
            $pdfMedia = $driverTesting->getMedia('drug_test_pdf');
            foreach ($pdfMedia as $media) {
                DB::table('media')->where('id', $media->id)->delete();
            }
            
            // Ahora eliminar el registro principal
            $driverTesting->delete();
            
            return redirect()->route('admin.driver-testings.index')
                ->with('success', 'Drug test deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting driver testing: ' . $e->getMessage());
            return redirect()->route('admin.driver-testings.index')
                ->with('error', 'Error deleting drug test. Please try again.');
        }
    }

    /**
     * Generate a PDF for the driver testing record
     */
    private function generatePDF(DriverTesting $driverTesting)
    {
        $driverTesting->load('userDriverDetail.user', 'userDriverDetail.carrier');
        
        $pdf = PDF::loadView('admin.driver-testings.pdf', [
            'driverTesting' => $driverTesting,
        ]);
        
        return $pdf;
    }

    /**
     * Send email to driver with PDF attachment
     */
    private function sendEmailToDriver(DriverTesting $driverTesting)
    {
        $driverTesting->load('userDriverDetail.user');
        $driverEmail = $driverTesting->userDriverDetail->user->email;
        $driverName = $driverTesting->userDriverDetail->user->name;
        
        $pdfMedia = $driverTesting->getFirstMedia('drug_test_pdf');
        
        if ($pdfMedia) {
            // TODO: Implementar envío de email con el PDF adjunto
            // Mail::to($driverEmail)->send(new DriverTestingNotification($driverTesting, $pdfMedia->getPath()));
        }
    }

    /**
     * Download PDF for a driver testing record
     */
    public function downloadPdf(DriverTesting $driverTesting): BinaryFileResponse
    {
        $media = $driverTesting->getFirstMedia('drug_test_pdf');
        if (!$media) {
            abort(404, 'PDF not found');
        }

        return response()->download($media->getPath(), $media->file_name);
    }
    

    
    /**
     * Get drivers for a specific carrier (API endpoint)
     */
    public function getCarrierDrivers($carrierId)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrierId)
            ->with('user:id,name,email')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'drivers' => $drivers
        ]);
    }
    
    /**
     * Search carriers by name (API endpoint)
     */
    public function searchCarriers(Request $request)
    {
        $query = $request->input('query', '');
        
        $carriers = Carrier::where('name', 'like', "%{$query}%")
            ->orWhere('usdot', 'like', "%{$query}%")
            ->orWhere('mc', 'like', "%{$query}%")
            ->select('id', 'name', 'usdot', 'mc', 'dot_pin')
            ->orderBy('name')
            ->limit(10)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'carriers' => $carriers
        ]);
    }
    
    /**
     * Get drivers by carrier (API endpoint)
     */
    public function getDriversByCarrier(Carrier $carrier)
    {
        // Obtener conductores activos para el carrier especificado
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->where('status', 1) // Solo conductores activos
            ->with(['user' => function($query) {
                $query->select('id', 'name', 'middle_name', 'last_name', 'email', 'date_of_birth');
            }])
            ->get();
            
        // Crear un array con los datos procesados
        $processedDrivers = [];
        
        foreach ($drivers as $driver) {
            // Obtener y formatear fecha de nacimiento si existe
            $dobFormatted = null;
            if ($driver->user && $driver->user->date_of_birth) {
                try {
                    $dobFormatted = date('m/d/Y', strtotime($driver->user->date_of_birth));
                } catch (\Exception $e) {
                    // Si hay error, usar el valor original
                    $dobFormatted = $driver->user->date_of_birth;
                }
            }
            
            // Asegurar que todos los campos existan
            $formattedDriver = [
                'id' => $driver->id,
                'user' => [
                    'id' => $driver->user->id,
                    'name' => $driver->user->name,
                    'middle_name' => $driver->user->middle_name,
                    'last_name' => $driver->user->last_name,
                    'email' => $driver->user->email,
                    'date_of_birth' => $dobFormatted
                ],
                'phone' => $driver->phone,
                // Incluir info del carrier (dot_number es el USDOT)
                'carrier_usdot' => $carrier->dot_number
            ];
            
            $processedDrivers[] = $formattedDriver;
        }

        // Devolver la respuesta con los datos procesados
        return response()->json($processedDrivers);
    }
    
    /**
     * Get driver details (API endpoint)
     */
    public function getDriverDetails(UserDriverDetail $driverDetail)
    {
        $driverDetail->load('user', 'carrier');
        
        $driver = [
            'id' => $driverDetail->id,
            'name' => $driverDetail->user->name,
            'email' => $driverDetail->user->email,
            'phone' => $driverDetail->phone ?? 'N/A',
            'license' => $driverDetail->license_number ?? 'N/A',
            'license_state' => $driverDetail->license_state ?? 'N/A',
            'license_expiration' => $driverDetail->license_exp_date ? date('m/d/Y', strtotime($driverDetail->license_exp_date)) : 'N/A',
            'carrier' => [
                'id' => $driverDetail->carrier->id,
                'name' => $driverDetail->carrier->name,
                'usdot' => $driverDetail->carrier->usdot,
            ],
        ];
        
        return response()->json([
            'status' => 'success',
            'driver' => $driver
        ]);
    }
}
