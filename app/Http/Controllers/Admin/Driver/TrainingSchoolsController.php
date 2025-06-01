<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverTrainingSchool;
use App\Models\DocumentAttachment;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        return view('admin.drivers.training.index', compact('trainingSchools', 'drivers'));
    }

    /**
     * Muestra el formulario para crear una nueva escuela de entrenamiento
     */
    public function create()
    {
        // No cargar conductores inicialmente, se cargarán vía AJAX después de seleccionar un carrier
        // Usando el mismo filtro que en accidents: status=1
        $carriers = \App\Models\Carrier::where('status', 1)->orderBy('name')->get();
        return view('admin.drivers.training.create', compact('carriers'));
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
                'phone_number' => 'required|string|max:20',
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
            $trainingSchool->phone_number = $request->phone_number;
            $trainingSchool->graduated = $request->has('graduated');
            $trainingSchool->subject_to_safety_regulations = $request->has('subject_to_safety_regulations');
            $trainingSchool->performed_safety_functions = $request->has('performed_safety_functions');

            // Guardar habilidades de entrenamiento como JSON
            if ($request->has('training_skills')) {
                $trainingSchool->training_skills = json_encode($request->training_skills);
            }

            $trainingSchool->save();

            // Procesar archivos si existen
            if ($request->filled('training_files')) {
                $filesData = json_decode($request->training_files, true);
                
                if (is_array($filesData)) {
                    // Obtener el ID del conductor
                    $driverId = $trainingSchool->user_driver_detail_id;
                    
                    // Crear el directorio de destino con la estructura correcta
                    $destinationDir = "public/driver/{$driverId}/training_schools/{$trainingSchool->id}";
                    if (!Storage::exists($destinationDir)) {
                        Storage::makeDirectory($destinationDir);
                    }
                    
                    foreach ($filesData as $fileData) {
                        if (!empty($fileData['name'])) {
                            try {
                                // Ruta del archivo temporal - CORREGIDO para usar las claves correctas
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
                                
                                $fileName = $fileData['name'];
                                $destinationPath = "{$destinationDir}/{$fileName}";
                                
                                // Mover el archivo de temp a la ubicación final
                                if (Storage::move($tempPath, $destinationPath)) {
                                    // Crear registro en la DB
                                    $document = new DocumentAttachment();
                                    $document->documentable_type = DriverTrainingSchool::class;
                                    $document->documentable_id = $trainingSchool->id;
                                    $document->file_path = $destinationPath;
                                    $document->file_name = $fileName;
                                    $document->original_name = $fileData['name'];
                                    $document->mime_type = $fileData['mime_type'] ?? 'application/octet-stream';
                                    $document->size = $fileData['size'] ?? 0;
                                    $document->collection = 'training_files';
                                    $document->custom_properties = json_encode([
                                        'document_type' => 'training_certificate',
                                        'uploaded_by' => Auth::id(),
                                        'description' => 'Training School Document'
                                    ]);
                                    $document->save();
                                    
                                    Log::info('Documento guardado correctamente', [
                                        'document_id' => $document->id,
                                        'file_name' => $fileName,
                                        'training_school_id' => $trainingSchool->id
                                    ]);
                                } else {
                                    Log::error('No se pudo mover el archivo temporal', [
                                        'temp_path' => $tempPath,
                                        'destination_path' => $destinationPath
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Error al procesar archivo', [
                                    'error' => $e->getMessage(),
                                    'file' => $fileData
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
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
        return view('admin.drivers.training.show', compact('school'));
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
        
        // Cargar documentos existentes para mostrarlos
        $documents = DocumentAttachment::where('documentable_type', DriverTrainingSchool::class)
            ->where('documentable_id', $trainingSchool->id)
            ->get();
        
        // Convertir los documentos a un formato que el componente FileUploader pueda entender
        $existingFilesArray = [];
        foreach ($documents as $document) {
            $existingFilesArray[] = [
                'id' => $document->id,
                'name' => $document->file_name,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
                'file_path' => $document->file_path,
                'is_existing' => true,
                'document_id' => $document->id
            ];
        }
        
        return view('admin.drivers.training.edit', compact(
            'trainingSchool', 
            'carriers', 
            'drivers', 
            'carrierId',
            'existingFilesArray'
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
                'phone_number' => 'required|string|max:20',
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
            $trainingSchool->phone_number = $request->phone_number;
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
            
            // Procesar archivos si existen
            if ($request->filled('training_files')) {
                $filesData = json_decode($request->training_files, true);
                
                if (is_array($filesData)) {
                    // Obtener el ID del conductor
                    $driverId = $trainingSchool->user_driver_detail_id;
                    
                    // Crear el directorio de destino con la estructura correcta
                    $destinationDir = "public/driver/{$driverId}/training_schools/{$trainingSchool->id}";
                    if (!Storage::exists($destinationDir)) {
                        Storage::makeDirectory($destinationDir);
                    }
                    
                    foreach ($filesData as $fileData) {
                        if (!empty($fileData['name'])) {
                            try {
                                // Ruta del archivo temporal - CORREGIDO para usar las claves correctas
                                $tempPath = isset($fileData['tempPath']) 
                                    ? $fileData['tempPath'] 
                                    : (isset($fileData['path']) 
                                        ? $fileData['path'] 
                                        : null);
                                
                                if (empty($tempPath)) {
                                    Log::warning('Archivo sin ruta temporal', ['file' => $fileData]);
                                    continue;
                                }
                                
                                // Si es un archivo existente, omitirlo ya que no necesitamos moverlo nuevamente
                                if (isset($fileData['is_existing']) && $fileData['is_existing']) {
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
                                
                                $fileName = $fileData['name'];
                                $destinationPath = "{$destinationDir}/{$fileName}";
                                
                                // Mover el archivo de temp a la ubicación final
                                if (Storage::move($tempPath, $destinationPath)) {
                                    // Crear registro en la DB
                                    $document = new DocumentAttachment();
                                    $document->documentable_type = DriverTrainingSchool::class;
                                    $document->documentable_id = $trainingSchool->id;
                                    $document->file_path = $destinationPath;
                                    $document->file_name = $fileName;
                                    $document->original_name = $fileData['name'];
                                    $document->mime_type = $fileData['mime_type'] ?? 'application/octet-stream';
                                    $document->size = $fileData['size'] ?? 0;
                                    $document->collection = 'training_files';
                                    $document->custom_properties = json_encode([
                                        'document_type' => 'training_certificate',
                                        'uploaded_by' => Auth::id(),
                                        'description' => 'Training School Document'
                                    ]);
                                    $document->save();
                                    
                                    Log::info('Documento guardado correctamente (update)', [
                                        'document_id' => $document->id,
                                        'file_name' => $fileName,
                                        'training_school_id' => $trainingSchool->id
                                    ]);
                                } else {
                                    Log::error('No se pudo mover el archivo temporal (update)', [
                                        'temp_path' => $tempPath,
                                        'destination_path' => $destinationPath
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Error al procesar archivo (update)', [
                                    'error' => $e->getMessage(),
                                    'file' => $fileData
                                ]);
                            }
                        }
                    }
                }
            }
            
            DB::commit();
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
     */
    public function showDocuments(DriverTrainingSchool $school)
    {
        $school->load('userDriverDetail.user');
        
        // Obtener documentos asociados (con paginación) y cargar la relación documentable
        $documents = DocumentAttachment::where('documentable_type', DriverTrainingSchool::class)
            ->where('documentable_id', $school->id)
            ->with('documentable.userDriverDetail.user') // Cargar relaciones necesarias
            ->orderBy('created_at', 'desc')
            ->paginate(15); // Usar paginación en lugar de get()
        
        // Obtener todas las escuelas y conductores para los filtros
        $schools = DriverTrainingSchool::orderBy('school_name')->get();
        $drivers = UserDriverDetail::with('user')->get();
        
        $debugInfo = [
            'documents_count' => $documents->total(), // Usar total() en vez de count() para objetos paginados
            'school_id' => $school->id
        ];
        
        return view('admin.drivers.training.documents', compact('school', 'schools', 'drivers', 'documents', 'debugInfo'));
    }

    /**
     * Muestra todos los documentos de escuelas de entrenamiento en una vista resumida
     */
    public function documents(Request $request)
    {
        try {
            $query = DocumentAttachment::where('documentable_type', DriverTrainingSchool::class)
                ->with('documentable.userDriverDetail.user');
            
            // Aplicar filtros
            if ($request->filled('search_term')) {
                $query->where(function($q) use ($request) {
                    $q->where('file_name', 'like', '%' . $request->search_term . '%')
                      ->orWhere('original_name', 'like', '%' . $request->search_term . '%');
                });
            }
            
            if ($request->filled('driver_filter')) {
                $driverId = $request->driver_filter;
                $query->whereHas('documentable', function ($q) use ($driverId) {
                    $q->where('user_driver_detail_id', $driverId);
                });
            }
            
            if ($request->filled('school_filter')) {
                $schoolId = $request->school_filter;
                $query->where('documentable_id', $schoolId);
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
            
            return view('admin.drivers.training.all_documents', compact('documents', 'drivers', 'schools'));
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
     * Usa el trait HasDocuments para eliminar correctamente
     * 
     * @param Request $request La solicitud HTTP
     * @param int $id ID del documento a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyDocument(Request $request, $id)
    {
        try {
            // Buscar el documento en nuestra tabla document_attachments
            $document = DocumentAttachment::findOrFail($id);

            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($document->documentable_type !== DriverTrainingSchool::class) {
                return response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            }

            $fileName = $document->file_name;
            $schoolId = $document->documentable_id;
            $school = DriverTrainingSchool::find($schoolId);

            if (!$school) {
                return response()->json(['success' => false, 'message' => 'Training school not found'], 404);
            }

            // Eliminar el documento usando el método del trait HasDocuments
            $result = $school->deleteDocument($id);

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Failed to delete document'], 500);
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
     * Elimina un documento usando el trait HasDocuments
     * 
     * @param int $id ID del documento a eliminar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($id)
    {
        try {
            // Buscar el documento en nuestra tabla document_attachments
            $document = DocumentAttachment::findOrFail($id);

            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($document->documentable_type !== DriverTrainingSchool::class) {
                return redirect()->back()->with('error', 'Invalid document type');
            }

            $fileName = $document->file_name;
            $schoolId = $document->documentable_id;
            $school = DriverTrainingSchool::find($schoolId);

            if (!$school) {
                return redirect()->route('admin.training-schools.index')
                    ->with('error', 'No se encontró la escuela de entrenamiento asociada al documento');
            }

            // Eliminar el documento usando el método del trait HasDocuments
            $result = $school->deleteDocument($id);

            if (!$result) {
                return redirect()->back()->with('error', 'No se pudo eliminar el documento');
            }

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
     * 
     * @param int $id ID del documento a previsualizar o descargar
     * @param Request $request La solicitud HTTP con parámetro opcional 'download'
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewDocument($id, Request $request = null)
    {
        try {
            // Buscar el documento en nuestra tabla document_attachments
            $document = DocumentAttachment::findOrFail($id);

            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($document->documentable_type !== DriverTrainingSchool::class) {
                return redirect()->back()->with('error', 'Tipo de documento inválido');
            }

            // Verificar que el archivo existe
            if (!Storage::disk('documents')->exists($document->path)) {
                return redirect()->back()->with('error', 'El archivo no existe en el servidor');
            }

            $file = Storage::disk('documents')->path($document->path);
            $contentType = mime_content_type($file) ?: 'application/octet-stream';

            // Determinar si es descarga o visualización
            $isDownload = $request && $request->has('download');

            $headers = [
                'Content-Type' => $contentType,
            ];

            if ($isDownload) {
                // Si es descarga, agregar headers adicionales
                $headers['Content-Disposition'] = 'attachment; filename="' . $document->file_name . '"';
            } else {
                // Si es visualización, usar 'inline' para mostrar en el navegador si es posible
                $headers['Content-Disposition'] = 'inline; filename="' . $document->file_name . '"';
            }

            return response()->file($file, $headers);
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al acceder al documento: ' . $e->getMessage());
        }
    }
}
