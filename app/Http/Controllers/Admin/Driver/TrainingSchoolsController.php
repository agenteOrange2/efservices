<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverTrainingSchool;
use App\Models\DocumentAttachment;
use App\Models\UserDriverDetail;
use App\Livewire\Admin\Driver\DriverCertificationStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use ReflectionClass;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrainingSchoolsController extends Controller
{
    /**
     * Vista para todas las escuelas de entrenamiento
     */
    public function index(Request $request)
    {
        $query = DriverTrainingSchool::query()
            ->with(['userDriverDetail.user']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where(function ($q) use ($request) {
                $q->where('school_name', 'like', '%' . $request->search_term . '%')
                    ->orWhere('city', 'like', '%' . $request->search_term . '%')
                    ->orWhere('state', 'like', '%' . $request->search_term . '%');
            });
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_start', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_end', '<=', $request->date_to);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $trainingSchools = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();

        return view('admin.drivers.training-school.index', compact('trainingSchools', 'drivers'));
    }

    /**
     * Muestra el formulario para crear una nueva escuela de entrenamiento
     */
    public function create()
    {
        // No cargar conductores inicialmente, se cargarán vía AJAX después de seleccionar un carrier
        // Usando el mismo filtro que en accidents: status=1
        $carriers = \App\Models\Carrier::where('status', 1)->orderBy('name')->get();
        return view('admin.drivers.training-school.create', compact('carriers'));
    }

    /**
     * Almacena una nueva escuela de entrenamiento
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validar datos
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'date_start' => 'required|date',
                'date_end' => 'required|date|after_or_equal:date_start',
                'school_name' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'training_skills' => 'nullable|array',
                'training_files' => 'nullable|string', // JSON de archivos del componente Livewire
            ]);

            // Crear el registro de escuela de entrenamiento
            $trainingSchool = new DriverTrainingSchool();
            $trainingSchool->user_driver_detail_id = $request->user_driver_detail_id;
            $trainingSchool->date_start = $request->date_start;
            $trainingSchool->date_end = $request->date_end;
            $trainingSchool->school_name = $request->school_name;
            $trainingSchool->city = $request->city;
            $trainingSchool->state = $request->state;

            $trainingSchool->graduated = $request->has('graduated');
            $trainingSchool->subject_to_safety_regulations = $request->has('subject_to_safety_regulations');
            $trainingSchool->performed_safety_functions = $request->has('performed_safety_functions');

            // Guardar habilidades de entrenamiento como JSON
            if ($request->has('training_skills')) {
                $trainingSchool->training_skills = json_encode($request->training_skills);
            }

            $trainingSchool->save();

            // Procesar archivos si existen usando Spatie Media Library
            if ($request->filled('training_files')) {
                $filesData = json_decode($request->training_files, true);
                
                if (is_array($filesData)) {
                    foreach ($filesData as $fileData) {
                        if (!empty($fileData['name'])) {
                            try {
                                // Ruta del archivo temporal
                                $tempPath = isset($fileData['tempPath']) 
                                    ? $fileData['tempPath'] 
                                    : (isset($fileData['path']) 
                                        ? $fileData['path'] 
                                        : null);
                                
                                if (empty($tempPath)) {
                                    Log::warning('Archivo sin ruta temporal', ['file' => $fileData]);
                                    continue;
                                }
                                
                                // Verificar que el archivo temporal existe
                                if (!Storage::exists($tempPath)) {
                                    // Intentar buscar en la carpeta temp directamente
                                    $tempPath = 'temp/' . basename($tempPath);
                                    
                                    if (!Storage::exists($tempPath)) {
                                        Log::error('Archivo temporal no encontrado (store)', [
                                            'temp_path' => $tempPath,
                                            'original_name' => $fileData['name']
                                        ]);
                                        continue;
                                    }
                                }
                                
                                // Obtener la ruta completa del archivo temporal
                                $fullTempPath = Storage::path($tempPath);
                                
                                // Añadir el archivo a la colección de Spatie Media Library
                                $media = $trainingSchool->addMedia($fullTempPath)
                                    ->usingName($fileData['name'])
                                    ->usingFileName($fileData['name'])
                                    ->withCustomProperties([
                                        'document_type' => 'training_certificate',
                                        'uploaded_by' => Auth::id(),
                                        'description' => 'Training School Document'
                                    ])
                                    ->toMediaCollection('school_certificates');
                                
                                Log::info('Documento guardado correctamente con Spatie Media Library', [
                                    'media_id' => $media->id,
                                    'file_name' => $fileData['name'],
                                    'training_school_id' => $trainingSchool->id
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Error al procesar archivo con Spatie Media Library', [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                    'file' => $fileData
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            
            // Regenerar el PDF de training schools después de crear
            $this->regenerateTrainingSchoolsPDF($request->user_driver_detail_id);
            
            return redirect()->route('admin.training-schools.index')
                ->with('success', 'Training school created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating training school', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating training school: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los detalles y documentos de una escuela de entrenamiento
     */
    public function show(DriverTrainingSchool $trainingSchool)
    {
        $trainingSchool->load('userDriverDetail.user');
        $school = $trainingSchool; // Renombrar para consistencia con la vista
        return view('admin.drivers.training-school.show', compact('school'));
    }

    /**
     * Muestra el formulario para editar una escuela de entrenamiento existente
     */
    public function edit(DriverTrainingSchool $trainingSchool)
    {
        $trainingSchool->load('userDriverDetail.user');
        
        // Obtener el transportista actual para preseleccionarlo
        $carrierId = optional($trainingSchool->userDriverDetail)->carrier_id;
        $carriers = \App\Models\Carrier::where('status', 1)->orderBy('name')->get();
                
        // Obtener conductores del transportista actual para el select
        $drivers = collect();
        if ($carrierId) {
            $drivers = UserDriverDetail::where('carrier_id', $carrierId)
                ->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })
                ->with('user')
                ->get();
        }
        
        // Cargar documentos existentes desde Spatie Media Library para mostrarlos
        $documents = Media::where('model_type', DriverTrainingSchool::class)
            ->where('model_id', $trainingSchool->id)
            ->where('collection_name', 'school_certificates')
            ->get();
        
        // Convertir los documentos a un formato que el componente FileUploader pueda entender
        $existingFilesArray = [];
        foreach ($documents as $media) {
            $existingFilesArray[] = [
                'id' => $media->id,
                'name' => $media->file_name,
                'original_name' => $media->name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'file_path' => $media->getUrl(),
                'url' => $media->getUrl(),
                'is_existing' => true,
                'document_id' => $media->id,
                'created_at' => $media->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        // Decodificar training_skills para los checkboxes
        $trainingSkills = is_array($trainingSchool->training_skills) 
            ? $trainingSchool->training_skills 
            : (json_decode($trainingSchool->training_skills) ?: []);
        
        return view('admin.drivers.training-school.edit', compact(
            'trainingSchool', 
            'carriers', 
            'drivers', 
            'carrierId',
            'existingFilesArray',
            'trainingSkills'
        ));
    }

    /**
     * Actualiza una escuela de entrenamiento existente
     */
    public function update(Request $request, DriverTrainingSchool $trainingSchool)
    {
        DB::beginTransaction();
        try {
            // Validar datos
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'date_start' => 'required|date',
                'date_end' => 'required|date|after_or_equal:date_start',
                'school_name' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'training_skills' => 'nullable|array',
                'training_files' => 'nullable|string', // JSON de archivos del componente Livewire
            ]);
            
            // Actualizar el registro de escuela de entrenamiento
            $trainingSchool->user_driver_detail_id = $request->user_driver_detail_id;
            $trainingSchool->date_start = $request->date_start;
            $trainingSchool->date_end = $request->date_end;
            $trainingSchool->school_name = $request->school_name;
            $trainingSchool->city = $request->city;
            $trainingSchool->state = $request->state;

            $trainingSchool->graduated = $request->has('graduated');
            $trainingSchool->subject_to_safety_regulations = $request->has('subject_to_safety_regulations');
            $trainingSchool->performed_safety_functions = $request->has('performed_safety_functions');
            
            // Guardar habilidades de entrenamiento como JSON
            if ($request->has('training_skills')) {
                $trainingSchool->training_skills = json_encode($request->training_skills);
            } else {
                $trainingSchool->training_skills = null;
            }
            
            $trainingSchool->save();
            
            // Procesar archivos si existen usando Spatie Media Library
            if ($request->filled('training_files')) {
                $filesData = json_decode($request->training_files, true);
                
                if (is_array($filesData)) {
                    foreach ($filesData as $fileData) {
                        if (!empty($fileData['name'])) {
                            try {
                                // Si es un archivo existente, omitirlo ya que no necesitamos procesarlo nuevamente
                                if (isset($fileData['is_existing']) && $fileData['is_existing']) {
                                    continue;
                                }
                                
                                // Ruta del archivo temporal
                                $tempPath = isset($fileData['tempPath']) 
                                    ? $fileData['tempPath'] 
                                    : (isset($fileData['path']) 
                                        ? $fileData['path'] 
                                        : null);
                                
                                if (empty($tempPath)) {
                                    Log::warning('Archivo sin ruta temporal', ['file' => $fileData]);
                                    continue;
                                }
                                
                                // Verificar que el archivo temporal existe
                                if (!Storage::exists($tempPath)) {
                                    // Intentar buscar en la carpeta temp directamente
                                    $tempPath = 'temp/' . basename($tempPath);
                                    
                                    if (!Storage::exists($tempPath)) {
                                        Log::error('Archivo temporal no encontrado (update)', [
                                            'temp_path' => $tempPath,
                                            'original_name' => $fileData['name']
                                        ]);
                                        continue;
                                    }
                                }
                                
                                // Obtener la ruta completa del archivo temporal
                                $fullTempPath = Storage::path($tempPath);
                                
                                // Añadir el archivo a la colección de Spatie Media Library
                                $media = $trainingSchool->addMedia($fullTempPath)
                                    ->usingName($fileData['name'])
                                    ->usingFileName($fileData['name'])
                                    ->withCustomProperties([
                                        'document_type' => 'training_certificate',
                                        'uploaded_by' => Auth::id(),
                                        'description' => 'Training School Document'
                                    ])
                                    ->toMediaCollection('school_certificates');
                                
                                Log::info('Documento guardado correctamente con Spatie Media Library (update)', [
                                    'media_id' => $media->id,
                                    'file_name' => $fileData['name'],
                                    'training_school_id' => $trainingSchool->id
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Error al procesar archivo con Spatie Media Library (update)', [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                    'file' => $fileData
                                ]);
                            }
                        }
                    }
                }
            }
            
            DB::commit();
            
            // Regenerar el PDF de training schools después de actualizar
            $this->regenerateTrainingSchoolsPDF($trainingSchool->user_driver_detail_id);
            
            return redirect()->route('admin.training-schools.index')
                ->with('success', 'Training school updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating training school', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating training school: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una escuela de entrenamiento
     */
    public function destroy(DriverTrainingSchool $trainingSchool)
    {
        try {
            // Obtener documentos asociados
            $documents = DocumentAttachment::where('documentable_type', DriverTrainingSchool::class)
                ->where('documentable_id', $trainingSchool->id)
                ->get();
            
            // Eliminar archivos físicos y registros de documentos
            foreach ($documents as $document) {
                if (Storage::exists($document->file_path)) {
                    Storage::delete($document->file_path);
                }
                $document->delete();
            }
            
            // Eliminar el registro de la escuela
            $trainingSchool->delete();
            
            return redirect()->route('admin.training-schools.index')
                ->with('success', 'Training school deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting training school', [
                'id' => $trainingSchool->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.training-schools.index')
                ->with('error', 'Error deleting training school: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los documentos de una escuela de entrenamiento específica
     * Utilizando Spatie Media Library
     */
    public function showDocuments(DriverTrainingSchool $school)
    {
        $school->load('userDriverDetail.user');
        
        // Obtener documentos asociados usando Spatie Media Library
        $documents = Media::where('model_type', DriverTrainingSchool::class)
            ->where('model_id', $school->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Obtener todas las escuelas y conductores para los filtros
        $schools = DriverTrainingSchool::orderBy('school_name')->get();
        $drivers = UserDriverDetail::with('user')->get();
        
        $debugInfo = [
            'documents_count' => $documents->total(),
            'school_id' => $school->id
        ];
        
        return view('admin.drivers.training-school.documents', compact('school', 'schools', 'drivers', 'documents', 'debugInfo'));
    }

    /**
     * Muestra todos los documentos de escuelas de entrenamiento en una vista resumida
     * Utilizando Spatie Media Library
     */
    public function documents(Request $request)
    {
        try {
            // Usar Spatie Media Library en lugar del antiguo sistema
            $query = Media::where('model_type', DriverTrainingSchool::class);
            
            // Aplicar filtros
            if ($request->filled('search_term')) {
                $searchTerm = '%' . $request->search_term . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                      ->orWhere('file_name', 'like', $searchTerm);
                });
            }
            
            if ($request->filled('driver_filter')) {
                $driverId = $request->driver_filter;
                // Obtener IDs de escuelas asociadas a este conductor
                $schoolIds = DriverTrainingSchool::where('user_driver_detail_id', $driverId)
                    ->pluck('id')
                    ->toArray();
                    
                $query->whereIn('model_id', $schoolIds);
            }
            
            if ($request->filled('school_filter')) {
                $schoolId = $request->school_filter;
                $query->where('model_id', $schoolId);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Ordenar resultados
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            $documents = $query->orderBy('created_at', 'desc')->paginate(15);
            
            // Datos para filtros
            $drivers = UserDriverDetail::with('user')->get();
            $schools = DriverTrainingSchool::orderBy('school_name')->get();
            
            return view('admin.drivers.training-school.all_documents', compact('documents', 'drivers', 'schools'));
        } catch (\Exception $e) {
            Log::error('Error loading training documents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.training-schools.index')
                ->with('error', 'Error loading documents: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un documento mediante AJAX
     * Usa eliminación directa de DB para evitar problemas con Spatie Media Library
     * 
     * @param Request $request La solicitud HTTP
     * @param int $id ID del documento a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyDocument(Request $request, $id)
    {
        try {
            // Verificar que el documento existe en la tabla media
            $media = Media::findOrFail($id);
            
            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($media->model_type !== DriverTrainingSchool::class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type'
                ], 400);
            }
            
            $fileName = $media->file_name;
            $schoolId = $media->model_id;
            $school = DriverTrainingSchool::find($schoolId);
            
            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training school not found'
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
            Log::error('Error deleting document via AJAX', [
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
     * Elimina un documento usando eliminación directa de DB para evitar problemas con Spatie Media Library
     * 
     * @param int $id ID del documento a eliminar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($id)
    {
        try {
            // Verificar que el documento existe en la tabla media
            $media = Media::findOrFail($id);

            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($media->model_type !== DriverTrainingSchool::class) {
                return redirect()->back()->with('error', 'Invalid document type');
            }

            $fileName = $media->file_name;
            $schoolId = $media->model_id;
            $school = DriverTrainingSchool::find($schoolId);

            if (!$school) {
                return redirect()->route('admin.training-schools.index')
                    ->with('error', 'No se encontró la escuela de entrenamiento asociada al documento');
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
            
            // Eliminar el registro directamente de la base de datos para evitar problemas de eliminación en cascada
            $result = DB::table('media')->where('id', $id)->delete();

            if (!$result) {
                return redirect()->back()->with('error', 'No se pudo eliminar el documento');
            }

            // Determinar la URL de retorno según el origen de la solicitud
            $referer = request()->headers->get('referer');
            
            // Si la URL contiene 'documents', redirigir a la página de documentos
            if (strpos($referer, 'documents') !== false) {
                return redirect()->route('admin.training-schools.show.documents', $schoolId)
                    ->with('success', "Documento '{$fileName}' eliminado correctamente");
            }
            
            // Si no, redirigir a la página de edición
            return redirect()->route('admin.training-schools.edit', $schoolId)
                ->with('success', "Documento '{$fileName}' eliminado correctamente");
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }

    public function getDriversByCarrier($carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier)
            ->whereHas('user', function ($query) {
                $query->where('status', 1);
            })
            ->with('user')
            ->get();

        return response()->json($drivers);
    }

    /**
     * Previsualiza o descarga un documento adjunto a una escuela de entrenamiento
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
            $media = Media::findOrFail($id);

            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($media->model_type !== DriverTrainingSchool::class) {
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
            Log::error('Error al previsualizar documento', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al acceder al documento: ' . $e->getMessage());
        }
    }

    /**
     * Regenera el PDF de training_schools.pdf para un conductor específico
     * 
     * @param int $driverId ID del conductor
     * @return bool True si se regeneró exitosamente, false en caso contrario
     */
    private function regenerateTrainingSchoolsPDF($driverId)
    {
        try {
            Log::info('Iniciando regeneración de training_schools.pdf', ['driver_id' => $driverId]);
            
            // Obtener el UserDriverDetail con todas las relaciones necesarias
            $userDriverDetail = UserDriverDetail::with([
                'addresses',
                'trainingSchools',
                'medicalQualification',
                'criminalHistory',
                'carrier',
                'user',
                'application.details',
                'certification'
            ])->find($driverId);
            
            if (!$userDriverDetail) {
                Log::error('UserDriverDetail no encontrado', ['driver_id' => $driverId]);
                return false;
            }
            
            // Obtener la firma desde la certificación
            $signaturePath = null;
            if ($userDriverDetail->certification) {
                $signatureMedia = $userDriverDetail->certification->getMedia('signature')->first();
                if ($signatureMedia) {
                    $signaturePath = $signatureMedia->getPath();
                    Log::info('Signature found for PDF regeneration', [
                        'driver_id' => $driverId,
                        'signature_path' => $signaturePath
                    ]);
                } else {
                    Log::warning('No signature media found for driver', ['driver_id' => $driverId]);
                }
            } else {
                Log::warning('No certification found for driver', ['driver_id' => $driverId]);
            }
            
            // Crear instancia de DriverCertificationStep para acceder a métodos privados
            $certificationStep = new DriverCertificationStep();
            
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
                'title' => 'Training Schools',
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
            $pdf = App::make('dompdf.wrapper')->loadView('pdf.driver.training', $pdfData);
            
            // Guardar PDF
            $pdfContent = $pdf->output();
            $filename = 'training_schools.pdf';
            Storage::disk('public')->put($appSubPath . '/' . $filename, $pdfContent);
            
            Log::info('PDF training_schools.pdf regenerado exitosamente con firma', [
                'driver_id' => $driverId,
                'filename' => $filename,
                'path' => $appSubPath . '/' . $filename,
                'has_signature' => $signaturePath !== null
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error regenerando training_schools.pdf', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
