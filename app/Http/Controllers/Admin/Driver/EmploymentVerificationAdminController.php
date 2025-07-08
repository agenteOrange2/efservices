<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Mail\EmploymentVerification;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\EmploymentVerificationToken;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmploymentVerificationAdminController extends Controller
{
    /**
     * Muestra la lista de verificaciones de empleo
     */
    public function index(Request $request)
    {
        $query = DriverEmploymentCompany::query()
            ->with(['userDriverDetail.user', 'masterCompany', 'verificationTokens'])
            ->where('email_sent', true);
        
        // Filtros
        if ($request->has('status')) {
            if ($request->status === 'verified') {
                $query->where('verification_status', 'verified');
            } elseif ($request->status === 'rejected') {
                $query->where('verification_status', 'rejected');
            } elseif ($request->status === 'pending') {
                $query->whereNull('verification_status');
            }
        }
        
        if ($request->has('driver')) {
            $driverId = $request->driver;
            $query->whereHas('userDriverDetail', function($q) use ($driverId) {
                $q->where('id', $driverId);
            });
        }
        
        $employmentVerifications = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        
        $drivers = UserDriverDetail::with('user')
            ->whereHas('employmentCompanies', function($q) {
                $q->where('email_sent', true);
            })
            ->get();
        
        return view('admin.drivers.employment-verification.index', [
            'employmentVerifications' => $employmentVerifications,
            'drivers' => $drivers
        ]);
    }
    
    /**
     * Muestra los detalles de una verificación de empleo específica
     */
    public function show($id)
    {
        $employmentCompany = DriverEmploymentCompany::with([
            'userDriverDetail.user', 
            'masterCompany', 
            'verificationTokens',
            'media'
        ])->findOrFail($id);
        
        return view('admin.drivers.employment-verification.show', [
            'employmentCompany' => $employmentCompany
        ]);
    }
    
    /**
     * Permite reenviar un correo de verificación de empleo
     */
    public function resendVerification($id)
    {
        $employmentCompany = DriverEmploymentCompany::with(['userDriverDetail', 'masterCompany'])
            ->findOrFail($id);
        
        // Crear un nuevo token de verificación
        $token = Str::random(64);
        
        // Guardar el token en la base de datos
        $verificationToken = new EmploymentVerificationToken([
            'token' => $token,
            'driver_id' => $employmentCompany->user_driver_detail_id,
            'employment_company_id' => $employmentCompany->id,
            'email' => $employmentCompany->email,
            'expires_at' => now()->addDays(7),
        ]);
        
        $verificationToken->save();
        
        // Enviar el correo electrónico
        try {
            // Obtener el nombre de la empresa
            $companyName = $employmentCompany->masterCompany ? $employmentCompany->masterCompany->name : 'Empresa personalizada';
            
            // Obtener el nombre completo del conductor
            $driverName = $employmentCompany->userDriverDetail->user->name . ' ' . $employmentCompany->userDriverDetail->last_name;
            
            // Preparar los datos de empleo para el correo
            $employmentData = [
                'positions_held' => $employmentCompany->positions_held,
                'employed_from' => $employmentCompany->employed_from,
                'employed_to' => $employmentCompany->employed_to,
                'reason_for_leaving' => $employmentCompany->reason_for_leaving,
                'subject_to_fmcsr' => $employmentCompany->subject_to_fmcsr,
                'safety_sensitive_function' => $employmentCompany->safety_sensitive_function
            ];
            
            Mail::to($employmentCompany->email)
                ->send(new EmploymentVerification(
                    $companyName,
                    $driverName,
                    $employmentData,
                    $token,
                    $employmentCompany->user_driver_detail_id,
                    $employmentCompany->id
                ));
            
            return redirect()->route('admin.drivers.employment-verification.index')
                ->with('success', 'El correo de verificación ha sido reenviado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al reenviar correo de verificación de empleo', [
                'error' => $e->getMessage(),
                'employment_company_id' => $employmentCompany->id
            ]);
            
            return redirect()->route('admin.drivers.employment-verification.index')
                ->with('error', 'Error al reenviar el correo de verificación: ' . $e->getMessage());
        }
    }
    
    /**
     * Permite marcar manualmente una verificación como verificada
     */
    public function markAsVerified(Request $request, $id)
    {
        $employmentCompany = DriverEmploymentCompany::findOrFail($id);
        
        $employmentCompany->verification_status = 'verified';
        $employmentCompany->verification_date = now();
        $employmentCompany->verification_notes = $request->notes ?? 'Verificado manualmente por administrador';
        $employmentCompany->verification_by = Auth::check() ? Auth::user()->name . ' (Admin)' : 'Administrador';
        $employmentCompany->save();
        
        return redirect()->route('admin.drivers.employment-verification.show', $id)
            ->with('success', 'La verificación de empleo ha sido marcada como verificada correctamente.');
    }
    
    /**
     * Permite marcar manualmente una verificación como rechazada
     */
    public function markAsRejected(Request $request, $id)
    {
        $employmentCompany = DriverEmploymentCompany::findOrFail($id);
        
        $employmentCompany->verification_status = 'rejected';
        $employmentCompany->verification_date = now();
        $employmentCompany->verification_notes = $request->notes ?? 'Rechazado manualmente por administrador';
        $employmentCompany->verification_by = Auth::check() ? Auth::user()->name . ' (Admin)' : 'Administrador';
        $employmentCompany->save();
        
        return redirect()->route('admin.drivers.employment-verification.show', $id)
            ->with('success', 'La verificación de empleo ha sido marcada como rechazada correctamente.');
    }
    
    /**
     * Procesa la subida de documentos de verificación de empleo
     * 
     * @param Request $request
     * @param int $id ID de la compañía de empleo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadDocument(Request $request, $id)
    {
        $employmentCompany = DriverEmploymentCompany::findOrFail($id);
        
        Log::info('Iniciando uploadDocument para verificación de empleo', [
            'employment_company_id' => $id,
            'request_data' => $request->all()
        ]);
        
        try {
            DB::beginTransaction();
            
            $uploadedCount = 0;
            $errors = [];
            
            // Verificar si estamos recibiendo archivos directos o JSON de Livewire
            if ($request->hasFile('documents')) {
                // Método tradicional con archivos directos
                $request->validate([
                    'documents' => 'required|array',
                    'documents.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
                ]);
                
                foreach ($request->file('documents') as $file) {
                    // Subir directamente a Media Library
                    $media = $employmentCompany->addMedia($file)
                        ->usingName('Employment Verification Manual')
                        ->usingFileName('Employment_verification_manual.pdf')
                        ->withCustomProperties([
                            'uploaded_by' => Auth::check() ? Auth::user()->name : 'Administrador',
                            'uploaded_at' => now()->format('Y-m-d H:i:s'),
                            'manual_upload' => true
                        ])
                        ->toMediaCollection('employment_verification_documents');
                    
                    $uploadedCount++;
                    
                    Log::info('Documento de verificación subido correctamente', [
                        'employment_company_id' => $employmentCompany->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name
                    ]);
                }
            } elseif ($request->filled('livewire_files')) {
                // Método Livewire con archivos temporales
                $livewireFiles = json_decode($request->input('livewire_files'), true);
                
                Log::info('Procesando archivos de Livewire', [
                    'livewire_files' => $livewireFiles
                ]);
                
                if (!is_array($livewireFiles) || empty($livewireFiles)) {
                    return redirect()->back()->with('error', 'No se recibieron archivos válidos');
                }
                
                // Procesar los archivos temporales de Livewire
                foreach ($livewireFiles as $fileData) {
                    // Verificar que tenemos la información necesaria
                    if (!isset($fileData['path']) && !isset($fileData['tempPath'])) {
                        $errors[] = 'Datos de archivo incompletos';
                        Log::warning('Datos de archivo incompletos', ['file' => $fileData]);
                        continue;
                    }
                    
                    // Obtener la ruta del archivo temporal
                    $tempPath = isset($fileData['path']) ? $fileData['path'] : $fileData['tempPath'];
                    $fullPath = storage_path('app/' . $tempPath);
                    
                    // Verificar que el archivo temporal existe
                    if (!file_exists($fullPath)) {
                        // Intentar con la ruta directa a la carpeta temp
                        $tempPath = 'temp/' . basename($tempPath);
                        $fullPath = storage_path('app/' . $tempPath);
                        
                        if (!file_exists($fullPath)) {
                            $errors[] = "Archivo temporal no encontrado: " . ($fileData['name'] ?? $fileData['originalName'] ?? 'Desconocido');
                            Log::error('Archivo temporal no encontrado', [
                                'temp_path' => $tempPath,
                                'full_path' => $fullPath,
                                'file_data' => $fileData
                            ]);
                            continue;
                        }
                    }
                    
                    $fileName = $fileData['name'] ?? $fileData['originalName'] ?? basename($fullPath);
                    
                    try {
                        // Subir desde el archivo temporal a Media Library
                        $media = $employmentCompany->addMedia($fullPath)
                            ->usingName('Employment Verification Manual')
                            ->usingFileName('Employment_verification_manual.pdf')
                            ->withCustomProperties([
                                'uploaded_by' => Auth::check() ? Auth::user()->name : 'Administrador',
                                'uploaded_at' => now()->format('Y-m-d H:i:s'),
                                'manual_upload' => true,
                                'original_name' => $fileName
                            ])
                            ->toMediaCollection('employment_verification_documents');
                        
                        $uploadedCount++;
                        
                        Log::info('Documento de verificación subido desde Livewire', [
                            'employment_company_id' => $employmentCompany->id,
                            'media_id' => $media->id,
                            'file_name' => $media->file_name,
                            'original_name' => $fileName
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = "Error al procesar {$fileName}: {$e->getMessage()}";
                        Log::error('Error al procesar archivo temporal', [
                            'file' => $fileData,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            } else {
                DB::rollBack();
                return redirect()->route('admin.drivers.employment-verification.show', $id)
                    ->with('error', 'No se recibieron archivos para subir');
            }
            
            // Si la verificación no está marcada como verificada, marcarla automáticamente
            if ($uploadedCount > 0 && $employmentCompany->verification_status !== 'verified') {
                $employmentCompany->verification_status = 'verified';
                $employmentCompany->verification_date = now();
                $employmentCompany->verification_notes = 'Verificado mediante documento subido manualmente';
                $employmentCompany->verification_by = Auth::check() ? Auth::user()->name . ' (Admin)' : 'Administrador';
                $employmentCompany->save();
                
                Log::info('Estado de verificación actualizado a verified', ['employment_company_id' => $id]);
            }
            
            DB::commit();
            
            $message = "$uploadedCount documentos subidos correctamente";
            if (!empty($errors)) {
                $message .= ", pero hubo errores con algunos archivos: " . implode(", ", $errors);
                return redirect()->route('admin.drivers.employment-verification.show', $id)
                    ->with('warning', $message);
            }
            
            return redirect()->route('admin.drivers.employment-verification.show', $id)
                ->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al subir documentos de verificación de empleo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'employment_company_id' => $id
            ]);
            
            return redirect()->route('admin.drivers.employment-verification.show', $id)
                ->with('error', 'Error al subir los documentos: ' . $e->getMessage());
        }
    }
    

    /**
     * Permite subir manualmente un documento de verificación de empleo digitalizado
     *
     * @param Request $request
     * @param int $id ID de la compañía de empleo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadManualVerification(Request $request, $id)
    {
        // Validar la solicitud
        $validator = Validator::make($request->all(), [
            'verification_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'verification_date' => 'required|date',
            'verification_notes' => 'nullable|string|max:500',
        ], [
            'verification_document.required' => 'Debes seleccionar un documento para subir.',
            'verification_document.file' => 'El archivo seleccionado no es válido.',
            'verification_document.mimes' => 'El documento debe ser un archivo PDF, JPG, JPEG o PNG.',
            'verification_document.max' => 'El documento no debe pesar más de 10MB.',
            'verification_date.required' => 'La fecha de verificación es obligatoria.',
            'verification_date.date' => 'La fecha de verificación debe tener un formato válido.',
            'verification_notes.max' => 'Las notas no pueden exceder los 500 caracteres.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Obtener la compañía de empleo
        $employmentCompany = DriverEmploymentCompany::findOrFail($id);
        $collection = 'employment_verification_documents';
        
        try {
            // Guardar el archivo usando Spatie Media Library
            if ($request->hasFile('verification_document')) {
                $file = $request->file('verification_document');
                $originalName = $file->getClientOriginalName();
                
                // Nombre estandarizado para documentos de verificación manual
                $standardizedFileName = 'Employment_verification_manual.' . $file->getClientOriginalExtension();
                
                // Agregar el documento a la colección de medios
                $media = $employmentCompany->addMedia($file->getRealPath())
                    ->usingName('Employment Verification Manual')
                    ->usingFileName($standardizedFileName)
                    ->withCustomProperties([
                        'uploaded_by' => Auth::user()->name,
                        'uploaded_at' => now()->format('Y-m-d H:i:s'),
                        'manual_upload' => true,
                        'original_name' => $originalName,
                        'verification_date' => $request->verification_date,
                        'verification_notes' => $request->verification_notes
                    ])
                    ->toMediaCollection($collection);
                    
                // Actualizar el estado de verificación
                $employmentCompany->verification_status = 'verified';
                // Usar verification_date para guardar la fecha de verificación
                $employmentCompany->verification_date = now();
                
                // Preparar las notas de verificación
                $verificationNotes = $request->verification_notes ?? '';
                $adminInfo = "\n\nVerificado manualmente por " . Auth::user()->name . ' el ' . now()->format('Y-m-d H:i:s');
                
                // Guardar notas con información de quien verificó
                $employmentCompany->verification_notes = $verificationNotes . $adminInfo;
                $employmentCompany->save();
                
                Log::info('Documento de verificación manual subido correctamente', [
                    'employment_company_id' => $employmentCompany->id,
                    'file_name' => $originalName,
                    'media_id' => $media->id
                ]);
                
                return redirect()->route('admin.drivers.employment-verification.show', $employmentCompany->id)
                    ->with('success', 'El documento de verificación ha sido subido y la verificación marcada como completada.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error al subir documento de verificación manual', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'employment_company_id' => $employmentCompany->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Ocurrió un error al subir el documento: ' . $e->getMessage())
                ->withInput();
        }
        
        return redirect()->back()
            ->with('error', 'No se encontró el documento a subir.')
            ->withInput();
    }
}
