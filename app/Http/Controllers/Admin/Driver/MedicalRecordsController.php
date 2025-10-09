<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverMedicalQualification;
use App\Models\UserDriverDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Admin\Driver\DriverCertificationStep;
use Illuminate\Validation\Rule;

class MedicalRecordsController extends Controller
{
    /**
     * Display a listing of the medical records.
     */
    public function index(Request $request)
    {
        try {
            $query = DriverMedicalQualification::with(['userDriverDetail.user', 'userDriverDetail.carrier']);
            
            // Apply filters
            if ($request->filled('search_term')) {
                $searchTerm = '%' . $request->search_term . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('medical_examiner_name', 'like', $searchTerm)
                      ->orWhere('medical_examiner_registry_number', 'like', $searchTerm)
                      ->orWhereHas('userDriverDetail.user', function($subQ) use ($searchTerm) {
                          $subQ->where('name', 'like', $searchTerm)
                               ->orWhere('email', 'like', $searchTerm);
                      });
                });
            }
            
            if ($request->filled('driver_filter')) {
                $query->where('user_driver_detail_id', $request->driver_filter);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'expired':
                        $query->where('medical_card_expiration_date', '<', now());
                        break;
                    case 'expiring':
                        $query->where('medical_card_expiration_date', '<=', now()->addDays(30))
                              ->where('medical_card_expiration_date', '>=', now());
                        break;
                    case 'active':
                        $query->where('medical_card_expiration_date', '>', now()->addDays(30));
                        break;
                }
            }
            
            // Handle sorting
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if (in_array($sortField, ['created_at', 'medical_card_expiration_date', 'medical_examiner_name'])) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $medicalRecords = $query->paginate(15);
            
            // Add document counts to each record
            foreach ($medicalRecords as $record) {
                $record->documents_count = $record->getMedia()->count();
            }
            
            // Get data for filters
            $drivers = UserDriverDetail::with('user')->get();
            
            // Get statistics for alerts
            $totalCount = DriverMedicalQualification::count();
            $expiringCount = DriverMedicalQualification::where('medical_card_expiration_date', '<=', now()->addDays(30))
                ->where('medical_card_expiration_date', '>=', now())
                ->count();
            $expiredCount = DriverMedicalQualification::where('medical_card_expiration_date', '<', now())->count();
            
            return view('admin.drivers.medical-records.index', compact(
                'medicalRecords',
                'drivers',
                'totalCount',
                'expiringCount',
                'expiredCount'
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading medical records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error loading medical records: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new medical record.
     */
    public function create()
    {
        $carriers = \App\Models\Carrier::where('status', 1)->orderBy('name')->get();
        $drivers = collect(); // Empty collection, will be populated via AJAX

        return view('admin.drivers.medical-records.create', compact('carriers', 'drivers'));
    }

    /**
     * Store a newly created medical record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'social_security_number' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'is_suspended' => 'boolean',
            'suspension_date' => 'nullable|date',
            'is_terminated' => 'boolean',
            'termination_date' => 'nullable|date',
            'medical_examiner_name' => 'nullable|string|max:255',
            'medical_examiner_registry_number' => 'nullable|string|max:255',
            'medical_card_expiration_date' => 'nullable|date',
            'medical_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ]);

        $medicalRecord = DriverMedicalQualification::create($validated);

        // Handle medical card upload using Spatie Media Library
        if ($request->hasFile('medical_card')) {
            $medicalRecord->addMediaFromRequest('medical_card')
                ->toMediaCollection('medical_card');
        }

        // Regenerar el PDF médico después de crear el registro
        $this->regenerateMedicalPDF($medicalRecord->user_driver_detail_id);

        return redirect()->route('admin.medical-records.index')
            ->with('success', 'Medical record created successfully.');
    }

    /**
     * Display the specified medical record.
     */
    public function show(DriverMedicalQualification $medicalRecord)
    {
        $medicalRecord->load(['driverDetail.user', 'media']);
        
        // Calculate document counts by collection type
        $documentCounts = [
            'medical_certificate' => $medicalRecord->getMedia('medical_certificate')->count(),
            'test_results' => $medicalRecord->getMedia('test_results')->count(),
            'additional_documents' => $medicalRecord->getMedia('additional_documents')->count(),
            'medical_card' => $medicalRecord->getMedia('medical_card')->count()
        ];
        
        // Calculate total documents
        $totalDocuments = array_sum($documentCounts);
        
        return view('admin.drivers.medical-records.show', compact('medicalRecord', 'totalDocuments', 'documentCounts'));
    }

    /**
     * Show the form for editing the specified medical record.
     */
    public function edit(DriverMedicalQualification $medicalRecord)
    {
        $carriers = \App\Models\Carrier::where('status', 1)->orderBy('name')->get();
        $drivers = \App\Models\UserDriverDetail::with('user')->get();

        $medicalRecord->load(['userDriverDetail.user', 'userDriverDetail.carrier']);

        return view('admin.drivers.medical-records.edit', compact('medicalRecord', 'carriers', 'drivers'));
    }

    /**
     * Update the specified medical record in storage.
     */
    public function update(Request $request, DriverMedicalQualification $medicalRecord)
    {
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'social_security_number' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'is_suspended' => 'boolean',
            'suspension_date' => 'nullable|date',
            'is_terminated' => 'boolean',
            'termination_date' => 'nullable|date',
            'medical_examiner_name' => 'nullable|string|max:255',
            'medical_examiner_registry_number' => 'nullable|string|max:255',
            'medical_card_expiration_date' => 'nullable|date',
            'medical_card' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:10240'
        ]);

        $medicalRecord->update($validated);

        // Handle medical card upload using Spatie Media Library
        if ($request->hasFile('medical_card')) {
            // Clear existing medical card
            $medicalRecord->clearMediaCollection('medical_card');
            
            // Add new medical card
            $medicalRecord->addMediaFromRequest('medical_card')
                ->toMediaCollection('medical_card');
        }

        // Regenerar el PDF médico después de actualizar el registro
        $this->regenerateMedicalPDF($medicalRecord->user_driver_detail_id);

        return redirect()->route('admin.medical-records.index')
            ->with('success', 'Medical record updated successfully.');
    }

    /**
     * Remove the specified medical record from storage.
     */
    public function destroy(DriverMedicalQualification $medicalRecord)
    {
        // Delete associated media files
        $medicalRecord->clearMediaCollection('medical_certificate');
        $medicalRecord->clearMediaCollection('test_results');
        $medicalRecord->clearMediaCollection('additional_documents');
        $medicalRecord->clearMediaCollection('medical_card');
        
        $medicalRecord->delete();

        return redirect()->route('admin.medical-records.index')
            ->with('success', 'Medical record deleted successfully.');
    }

    /**
     * Show documents for a specific medical record.
     */
    public function showDocuments(Request $request, DriverMedicalQualification $medicalRecord)
    {
        // Load the medical record with its relationships
        $medicalRecord->load(['driverDetail.user', 'media']);
        
        // Get current collection filter
        $currentCollection = $request->get('collection', 'all');
        
        // Calculate statistics for this specific medical record
        // Get counts from all collections since getMedia() without params doesn't work properly
        $medicalCertificates = $medicalRecord->getMedia('medical_certificate')->count();
        $additionalDocuments = $medicalRecord->getMedia('medical_documents')->count();
        $medicalCardDocuments = $medicalRecord->getMedia('medical_card')->count();
        $testResults = $medicalRecord->getMedia('test_results')->count();
        
        // Calculate total documents from all collections
        $totalDocuments = $medicalCertificates + $additionalDocuments + $medicalCardDocuments + $testResults;
        
        // Get filtered documents based on collection
        $documents = collect();
        if ($currentCollection === 'all') {
            // Get all media from all collections since getMedia() without params doesn't work properly
            $allCollections = ['medical_certificate', 'test_results', 'medical_documents', 'medical_card'];
            foreach ($allCollections as $collection) {
                $documents = $documents->merge($medicalRecord->getMedia($collection));
            }
        } else {
            // Map 'additional_documents' to 'medical_documents' for consistency
            $collectionName = $currentCollection === 'additional_documents' ? 'medical_documents' : $currentCollection;
            $documents = $medicalRecord->getMedia($collectionName);
        }
        
        // Apply additional filters
        if ($request->filled('date_range')) {
            $dateRange = explode(' - ', $request->date_range);
            if (count($dateRange) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', trim($dateRange[1]))->endOfDay();
                
                $documents = $documents->filter(function ($document) use ($startDate, $endDate) {
                    return $document->created_at >= $startDate && $document->created_at <= $endDate;
                });
            }
        }
        
        if ($request->filled('document_type') && $currentCollection === 'all') {
            $documentType = $request->document_type;
            // Map 'additional_documents' to 'medical_documents' for filtering
            $filterType = $documentType === 'additional_documents' ? 'medical_documents' : $documentType;
            $documents = $documents->filter(function ($document) use ($filterType) {
                return $document->collection_name === $filterType;
            });
        }

        return view('admin.drivers.medical-records.documents', compact(
            'medicalRecord',
            'documents',
            'totalDocuments',
            'medicalCertificates',
            'additionalDocuments',
            'medicalCardDocuments',
            'currentCollection'
        ));
    }

    /**
     * Get documents for a specific medical record.
     */
    public function documents(DriverMedicalQualification $medicalRecord)
    {
        $medicalRecord->load(['driverDetail.user', 'media']);
        
        $documents = [
            'medical_certificate' => $medicalRecord->getMedia('medical_certificate'),
            'test_results' => $medicalRecord->getMedia('test_results'),
            'additional_documents' => $medicalRecord->getMedia('medical_documents'),
            'medical_card' => $medicalRecord->getMedia('medical_card')
        ];

        return response()->json([
            'documents' => $documents,
            'medicalRecord' => $medicalRecord
        ]);
    }

    /**
     * Upload documents for medical record.
     */
    public function uploadDocument(Request $request, DriverMedicalQualification $medicalRecord)
    {
        try {
            $request->validate([
                'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
            ], [
                'documents.*.required' => 'Debe seleccionar al menos un archivo.',
                'documents.*.file' => 'El archivo debe ser válido.',
                'documents.*.mimes' => 'El archivo debe ser de tipo: pdf, jpg, jpeg, png, doc, docx.',
                'documents.*.max' => 'El archivo no debe ser mayor a 10MB.',
            ]);

            if (!$request->hasFile('documents')) {
                return redirect()->back()->with('error', 'No se seleccionaron archivos para subir.');
            }

            $uploadedCount = 0;
            $errors = [];

            foreach ($request->file('documents') as $file) {
                try {
                    // Usar Spatie Media Library para guardar el archivo
                    $medicalRecord->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->toMediaCollection('medical_documents');
                    
                    $uploadedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error al subir {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            if ($uploadedCount > 0) {
                $message = "Se subieron {$uploadedCount} documento(s) correctamente.";
                if (!empty($errors)) {
                    $message .= ' Algunos archivos tuvieron errores: ' . implode(', ', $errors);
                }
                return redirect()->back()->with('success', $message);
            } else {
                return redirect()->back()->with('error', 'No se pudo subir ningún documento. ' . implode(', ', $errors));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error uploading medical documents', [
                'medical_record_id' => $medicalRecord->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al subir documentos: ' . $e->getMessage());
        }
    }

    /**
     * Delete document from medical record.
     */
    public function destroyDocument($id)
    {
        try {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);
            
            // Verify the media belongs to a medical record
            if (!$media->model instanceof DriverMedicalQualification) {
                return redirect()->back()->with('error', 'Invalid document.');
            }

            $fileName = $media->name;
            $media->delete();

            return redirect()->back()->with('success', "Document '{$fileName}' deleted successfully.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting medical document', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error deleting document: ' . $e->getMessage());
        }
    }

    /**
     * Download document.
     */
    public function downloadDocument($mediaId)
    {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
        
        // Verify the media belongs to a medical record
        if (!$media->model instanceof DriverMedicalQualification) {
            abort(403, 'Unauthorized access to document.');
        }

        return response()->download($media->getPath(), $media->name);
    }

    /**
     * Delete medical card from medical record.
     */
    public function deleteMedicalCard(DriverMedicalQualification $medicalRecord)
    {
        try {
            if ($medicalRecord->medical_card_path) {
                // Delete the file from storage
                Storage::delete($medicalRecord->medical_card_path);
                
                // Update the record to remove the file path
                $medicalRecord->update(['medical_card_path' => null]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Medical card deleted successfully.'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No medical card found to delete.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting medical card: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Previsualiza o descarga un documento adjunto a un registro médico
     * Utilizando Spatie Media Library
     * 
     * @param int $id ID del documento a previsualizar o descargar
     * @param Request $request La solicitud HTTP con parámetro opcional 'download'
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewDocument($id, Request $request = null)
    {
        try {
            // Buscar el documento en la tabla media de Spatie
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);

            // Verificar que el documento pertenece a un registro médico
            if ($media->model_type !== DriverMedicalQualification::class) {
                return redirect()->back()->with('error', 'Tipo de documento inválido');
            }

            // Determinar si es descarga o visualización
            $isDownload = $request && $request->has('download');

            if ($isDownload) {
                // Si es descarga, usar el método de descarga de Spatie
                return response()->download(
                    $media->getPath(), 
                    $media->file_name,
                    ['Content-Type' => $media->mime_type]
                );
            } else {
                // Si es visualización, usar 'inline' para mostrar en el navegador si es posible
                $headers = [
                    'Content-Type' => $media->mime_type,
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
                ];
                
                return response()->file($media->getPath(), $headers);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al previsualizar documento médico', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al acceder al documento: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un documento vía AJAX usando eliminación directa de DB
     * 
     * @param int $id ID del documento a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyDocument($id)
    {
        try {
            // Verificar que el documento existe en la tabla media
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);

            // Verificar que el documento pertenece a un registro médico
            if ($media->model_type !== DriverMedicalQualification::class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type'
                ], 400);
            }
            
            $fileName = $media->file_name;
            $medicalRecordId = $media->model_id;
            $medicalRecord = DriverMedicalQualification::find($medicalRecordId);
            
            if (!$medicalRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Medical record not found'
                ], 404);
            }
            
            // Eliminar el archivo físico si existe
            $diskName = $media->disk;
            $filePath = $media->id . '/' . $media->file_name;
            
            if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists($filePath)) {
                \Illuminate\Support\Facades\Storage::disk($diskName)->delete($filePath);
            }
            
            // Eliminar directorio del media si existe
            $dirPath = $media->id;
            if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists($dirPath)) {
                \Illuminate\Support\Facades\Storage::disk($diskName)->deleteDirectory($dirPath);
            }
            
            // Eliminar el registro directamente de la base de datos
            $result = DB::table('media')->where('id', $id)->delete();
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete document'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Document '{$fileName}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting medical document via AJAX', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenera el PDF de calificacion_medica.pdf para un conductor específico
     * 
     * @param int $driverId ID del conductor
     * @return bool True si se regeneró exitosamente, false en caso contrario
     */
    private function regenerateMedicalPDF($driverId)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Iniciando regeneración de calificacion_medica.pdf', ['driver_id' => $driverId]);
            
            // Obtener el UserDriverDetail con todas las relaciones necesarias
            $userDriverDetail = UserDriverDetail::with([
                'application.addresses',
                'licenses',
                'medicalQualification',
                'criminalHistory',
                'carrier',
                'user',
                'application.details',
                'certification'
            ])->find($driverId);
            
            if (!$userDriverDetail) {
                \Illuminate\Support\Facades\Log::error('UserDriverDetail no encontrado', ['driver_id' => $driverId]);
                return false;
            }
            
            // Verificar que tenga calificación médica
            if (!$userDriverDetail->medicalQualification) {
                \Illuminate\Support\Facades\Log::warning('No se encontró calificación médica para el conductor', ['driver_id' => $driverId]);
                return false;
            }
            
            // Obtener la firma desde la certificación
            $signaturePath = null;
            if ($userDriverDetail->certification) {
                $signatureMedia = $userDriverDetail->certification->getMedia('signature')->first();
                if ($signatureMedia) {
                    $signaturePath = $signatureMedia->getPath();
                    \Illuminate\Support\Facades\Log::info('Signature found for medical PDF regeneration', [
                        'driver_id' => $driverId,
                        'signature_path' => $signaturePath
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::warning('No signature media found for driver', ['driver_id' => $driverId]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('No certification found for driver', ['driver_id' => $driverId]);
            }
            
            // Crear instancia de DriverCertificationStep para acceder a métodos privados
            $certificationStep = new \App\Livewire\Admin\Driver\DriverCertificationStep();
            
            // Obtener fechas efectivas usando reflexión para acceder al método privado
            $reflection = new \ReflectionClass($certificationStep);
            $getEffectiveDatesMethod = $reflection->getMethod('getEffectiveDates');
            $getEffectiveDatesMethod->setAccessible(true);
            $effectiveDates = $getEffectiveDatesMethod->invoke($certificationStep, $driverId);
            
            // Preparar la ruta de almacenamiento
            $driverPath = 'driver/' . $userDriverDetail->id;
            $appSubPath = $driverPath . '/driver_applications';
            
            // Asegurar que los directorios existen
            Storage::disk('public')->makeDirectory($driverPath);
            Storage::disk('public')->makeDirectory($appSubPath);
            
            // Preparar datos para el PDF
            $pdfData = [
                'userDriverDetail' => $userDriverDetail,
                'signaturePath' => $signaturePath, // Incluir la firma del conductor
                'title' => 'Medical Qualification',
                'date' => now()->format('d/m/Y'),
                'created_at' => $effectiveDates['created_at'],
                'updated_at' => $effectiveDates['updated_at'],
                'custom_created_at' => $effectiveDates['custom_created_at']
            ];
            
            // Preparar formatted_dates con ambas fechas cuando corresponda
            $formattedDates = [
                'updated_at' => $effectiveDates['updated_at']->format('m/d/Y'),
                'updated_at_long' => $effectiveDates['updated_at']->format('F j, Y')
            ];
            
            // Siempre incluir created_at (fecha de registro normal)
            if ($effectiveDates['show_created_at'] && $effectiveDates['created_at']) {
                $formattedDates['created_at'] = $effectiveDates['created_at']->format('m/d/Y');
                $formattedDates['created_at_long'] = $effectiveDates['created_at']->format('F j, Y');
            }
            
            // Incluir custom_created_at solo si está habilitado y tiene valor
            if ($effectiveDates['show_custom_created_at'] && $effectiveDates['custom_created_at']) {
                $formattedDates['custom_created_at'] = $effectiveDates['custom_created_at']->format('m/d/Y');
                $formattedDates['custom_created_at_long'] = $effectiveDates['custom_created_at']->format('F j, Y');
            }
            
            $pdfData['formatted_dates'] = $formattedDates;
            $pdfData['use_custom_dates'] = $effectiveDates['show_custom_created_at'];
            
            // Generar el PDF
            $pdf = \Illuminate\Support\Facades\App::make('dompdf.wrapper')->loadView('pdf.driver.medical', $pdfData);
            
            // Guardar PDF
            $pdfContent = $pdf->output();
            $filename = 'calificacion_medica.pdf';
            Storage::disk('public')->put($appSubPath . '/' . $filename, $pdfContent);
            
            \Illuminate\Support\Facades\Log::info('PDF calificacion_medica.pdf regenerado exitosamente con firma', [
                'driver_id' => $driverId,
                'filename' => $filename,
                'path' => $appSubPath . '/' . $filename,
                'has_signature' => $signaturePath !== null
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error regenerando calificacion_medica.pdf', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}