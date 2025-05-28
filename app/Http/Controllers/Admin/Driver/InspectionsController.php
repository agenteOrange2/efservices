<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InspectionsController extends Controller
{
    // Vista para todas las inspecciones
    public function index(Request $request)
    {
        $query = DriverInspection::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier', 'vehicle']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('inspection_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%')
                ->orWhere('inspector_name', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('carrier_filter')) {
            $query->whereHas('userDriverDetail', function ($subq) use ($request) {
                $subq->where('carrier_id', $request->carrier_filter);
            });
        }

        if ($request->filled('vehicle_filter')) {
            $query->where('vehicle_id', $request->vehicle_filter);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('inspection_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('inspection_date', '<=', $request->date_to);
        }

        if ($request->filled('inspection_type')) {
            $query->where('inspection_type', $request->inspection_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'inspection_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $inspections = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();
        $vehicles = Vehicle::all();

        // Obtener valores únicos para los filtros de desplegable
        $inspectionTypes = DriverInspection::distinct()->pluck('inspection_type')->filter()->toArray();
        $statuses = DriverInspection::distinct()->pluck('status')->filter()->toArray();

        return view('admin.drivers.inspections.index', compact(
            'inspections',
            'drivers',
            'carriers',
            'vehicles',
            'inspectionTypes',
            'statuses'
        ));
    }

    // Vista para el historial de inspecciones de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverInspection::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('inspection_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%')
                ->orWhere('inspector_name', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('vehicle_filter')) {
            $query->where('vehicle_id', $request->vehicle_filter);
        }

        if ($request->filled('inspection_type')) {
            $query->where('inspection_type', $request->inspection_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'inspection_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $inspections = $query->paginate(10);

        // Obtener vehículos del conductor para el filtro
        $driverVehicles = Vehicle::where(function ($query) use ($driver) {
            $query->where('user_driver_detail_id', $driver->id)
                ->orWhereHas('driverInspections', function ($q) use ($driver) {
                    $q->where('user_driver_detail_id', $driver->id);
                });
        })->get();

        // Obtener valores únicos para los filtros de desplegable
        $inspectionTypes = DriverInspection::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('inspection_type')->filter()->toArray();
        $statuses = DriverInspection::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('status')->filter()->toArray();

        return view('admin.drivers.inspections.driver_history', compact(
            'driver',
            'inspections',
            'driverVehicles',
            'inspectionTypes',
            'statuses'
        ));
    }

    // Método para almacenar una nueva inspección
    public function store(Request $request)
    {
        // Inicio de la transacción
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'inspection_date' => 'required|date',
                'inspection_type' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'status' => 'required|string|max:255',
                'inspector_name' => 'nullable|string|max:255',
                'violations_found' => 'boolean',
                'defect_details' => 'nullable|string',
                'corrective_actions' => 'nullable|string',
                'repair_completed' => 'boolean',
                'repair_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            // Manejar campos booleanos
            $validated['violations_found'] = isset($request->violations_found);
            $validated['repair_completed'] = isset($request->repair_completed);

            $inspection = DriverInspection::create($validated);
            
            // Procesar archivos subidos vía Livewire
            $filesUploaded = 0;
            
            // Procesar archivos de inspección
            $inspectionFiles = $request->get('inspection_files');
            if (!empty($inspectionFiles)) {
                $filesUploaded += $this->processLivewireFiles($inspection, $inspectionFiles, 'inspection_reports');
            }
            
            // Procesar fotos de defectos
            $defectFiles = $request->get('defect_files');
            if (!empty($defectFiles)) {
                $filesUploaded += $this->processLivewireFiles($inspection, $defectFiles, 'defect_photos');
            }
            
            // Procesar documentos de reparación
            $repairFiles = $request->get('repair_files');
            if (!empty($repairFiles)) {
                $filesUploaded += $this->processLivewireFiles($inspection, $repairFiles, 'repair_documents');
            }
            
            // También procesar archivos subidos de forma tradicional si existen
            if ($request->hasFile('inspection_reports')) {
                foreach ($request->file('inspection_reports') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('inspection_reports');
                    $filesUploaded++;
                }
            }

            if ($request->hasFile('defect_photos')) {
                foreach ($request->file('defect_photos') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('defect_photos');
                    $filesUploaded++;
                }
            }

            if ($request->hasFile('repair_documents')) {
                foreach ($request->file('repair_documents') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('repair_documents');
                    $filesUploaded++;
                }
            }
            
            DB::commit();
            
            if ($filesUploaded > 0) {
                Session::flash('success', "Inspection created successfully with $filesUploaded documents!");
            } else {
                Session::flash('success', 'Inspection created successfully!');
            }

            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('admin.drivers.inspection-history', $inspection->user_driver_detail_id);
            }

            return redirect()->route('admin.inspections.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating inspection: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error creating inspection: ' . $e->getMessage());
        }
    }

    // Método para actualizar una inspección existente
    public function update(DriverInspection $inspection, Request $request)
    {
        // Inicio de la transacción
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'inspection_date' => 'required|date',
                'inspection_type' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'status' => 'required|string|max:255',
                'inspector_name' => 'nullable|string|max:255',
                'violations_found' => 'boolean',
                'defect_details' => 'nullable|string',
                'corrective_actions' => 'nullable|string',
                'repair_completed' => 'boolean',
                'repair_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            // Manejar campos booleanos
            $validated['violations_found'] = isset($request->violations_found);
            $validated['repair_completed'] = isset($request->repair_completed);

            $inspection->update($validated);
            
            // Procesar archivos subidos vía Livewire
            $filesUploaded = 0;
            
            // Procesar archivos de inspección
            $inspectionFiles = $request->get('inspection_files');
            if (!empty($inspectionFiles)) {
                $filesUploaded += $this->processLivewireFiles($inspection, $inspectionFiles, 'inspection_reports');
            }
            
            // Procesar fotos de defectos
            $defectFiles = $request->get('defect_files');
            if (!empty($defectFiles)) {
                $filesUploaded += $this->processLivewireFiles($inspection, $defectFiles, 'defect_photos');
            }
            
            // Procesar documentos de reparación
            $repairFiles = $request->get('repair_files');
            if (!empty($repairFiles)) {
                $filesUploaded += $this->processLivewireFiles($inspection, $repairFiles, 'repair_documents');
            }

            // También procesar archivos subidos de forma tradicional si existen
            if ($request->hasFile('inspection_reports')) {
                foreach ($request->file('inspection_reports') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('inspection_reports');
                    $filesUploaded++;
                }
            }

            if ($request->hasFile('defect_photos')) {
                foreach ($request->file('defect_photos') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('defect_photos');
                    $filesUploaded++;
                }
            }

            if ($request->hasFile('repair_documents')) {
                foreach ($request->file('repair_documents') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('repair_documents');
                    $filesUploaded++;
                }
            }
            
            DB::commit();
            
            if ($filesUploaded > 0) {
                Session::flash('success', "Inspection updated successfully with $filesUploaded new documents!");
            } else {
                Session::flash('success', 'Inspection record updated successfully!');
            }

            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('admin.drivers.inspection-history', $inspection->user_driver_detail_id);
            }

            return redirect()->route('admin.inspections.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating inspection: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'inspection_id' => $inspection->id
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error updating inspection: ' . $e->getMessage());
        }
    }


    // Método para eliminar una inspección
    public function destroy(DriverInspection $inspection)
    {
        $driverId = $inspection->user_driver_detail_id;

        // Eliminar archivos adjuntos
        $inspection->clearMediaCollection('inspection_reports');
        $inspection->clearMediaCollection('defect_photos');
        $inspection->clearMediaCollection('repair_documents');

        $inspection->delete();

        Session::flash('success', 'Inspection record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'inspection-history') !== false) {
            return redirect()->route('admin.drivers.inspection-history', $driverId);
        }

        return redirect()->route('admin.inspections.index');
    }

    // Método para eliminar un archivo específico
    public function deleteFile($inspectionId, $mediaId)
    {
        $inspection = DriverInspection::findOrFail($inspectionId);
        $media = $inspection->media()->findOrFail($mediaId);
        $media->delete();

        return response()->json(['success' => true]);
    }

    // Nuevo método para obtener los archivos de una inspección
    public function getFiles(DriverInspection $inspection)
    {
        // Cargar la relación media si no está ya cargada
        if (!$inspection->relationLoaded('media')) {
            $inspection->load('media');
        }
        return response()->json([
            'media' => $inspection->media
        ]);
    }

    // Obtener vehículos por transportista
    public function getVehiclesByCarrier(Carrier $carrier)
    {
        $vehicles = Vehicle::where('carrier_id', $carrier->id)->get();
        return response()->json($vehicles);
    }

    // Obtener vehículos por conductor
    public function getVehiclesByDriver(UserDriverDetail $driver)
    {
        $vehicles = Vehicle::where(function ($query) use ($driver) {
            $query->where('user_driver_detail_id', $driver->id)
                ->orWhere('carrier_id', $driver->carrier_id);
        })->get();

        return response()->json($vehicles);
    }

    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with(['user']) // Asegúrate de incluir la relación con el usuario
            ->get();

        return response()->json($drivers);
    }
    
    /**
     * Método privado para procesar archivos subidos vía Livewire
     * 
     * @param DriverInspection $inspection Inspección a la que asociar los archivos
     * @param string $filesJson Datos de los archivos en formato JSON
     * @param string $collection Nombre de la colección donde guardar los archivos
     * @return int Número de archivos procesados correctamente
     */
    private function processLivewireFiles(DriverInspection $inspection, $filesJson, $collection)
    {
        $uploadedCount = 0;
        
        try {
            $filesArray = json_decode($filesJson, true);
            
            if (is_array($filesArray)) {
                foreach ($filesArray as $file) {
                    if (empty($file['path'])) {
                        continue;
                    }
                    
                    // Obtener la ruta completa del archivo
                    $filePath = $file['path'];
                    $fullPath = storage_path('app/' . $filePath);
                    
                    // Verificar si el archivo existe físicamente
                    if (!file_exists($fullPath)) {
                        Log::error('Archivo no encontrado', [
                            'path' => $filePath,
                            'full_path' => $fullPath,
                            'inspection_id' => $inspection->id
                        ]);
                        continue;
                    }
                    
                    $driverId = $inspection->userDriverDetail->id;
                    
                    // Usar addMedia directamente desde la ruta del archivo
                    $media = $inspection->addMedia($fullPath)
                        ->usingName($file['original_name'] ?? 'document')
                        ->usingFileName($file['original_name'] ?? 'document')
                        ->withCustomProperties([
                            'original_filename' => $file['original_name'] ?? 'document',
                            'mime_type' => $file['mime_type'] ?? 'application/octet-stream',
                            'inspection_id' => $inspection->id,
                            'driver_id' => $driverId,
                            'size' => $file['size'] ?? 0
                        ])
                        ->toMediaCollection($collection);
                    
                    $uploadedCount++;
                    
                    Log::info('Documento subido correctamente a ' . $collection, [
                        'inspection_id' => $inspection->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar documentos vía Livewire', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'inspection_id' => $inspection->id,
                'collection' => $collection
            ]);
        }
        
        return $uploadedCount;
    }
}
