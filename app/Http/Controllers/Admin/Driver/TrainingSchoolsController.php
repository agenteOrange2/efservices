<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTrainingSchool;
use App\Models\DocumentAttachment;
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
                $files = json_decode($request->training_files, true);
                
                if (is_array($files) && count($files) > 0) {
                    foreach ($files as $file) {
                        if (isset($file['is_temp']) && $file['is_temp'] && isset($file['tmp_path'])) {
                            // Obtener datos del archivo
                            $tempPath = storage_path('app/' . $file['tmp_path']);
                            $originalName = $file['name'];
                            $mimeType = $file['mime_type'] ?? mime_content_type($tempPath);
                            $size = $file['size'] ?? filesize($tempPath);
                            
                            // Verificar que el archivo temporal existe
                            if (!file_exists($tempPath)) {
                                continue;
                            }
                            
                            // Preparar propiedades personalizadas
                            $customProperties = [
                                'original_name' => $originalName,
                                'mime_type' => $mimeType,
                                'size' => $size
                            ];
                            
                            // Mover el archivo a una ubicación permanente (la lógica para esto está en el trait HasDocuments)
                            $destinationDir = 'training-schools/' . $trainingSchool->id;
                            $fileName = uniqid() . '_' . $originalName;
                            $fullPath = $destinationDir . '/' . $fileName;
                            
                            // Usar addDocument del trait HasDocuments
                            $document = $trainingSchool->addDocument($fullPath, 'training_documents', $customProperties);
                            
                            // Mover el archivo de la carpeta temporal a la permanente
                            Storage::makeDirectory($destinationDir);
                            Storage::copy($file['tmp_path'], $fullPath);
                        }
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.training-schools.index')
                ->with('success', 'Training school created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear escuela de entrenamiento', [
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
        return view('admin.drivers.training.show', compact('trainingSchool'));
    }
    
    /**
     * Muestra el formulario para editar una escuela de entrenamiento existente
     */
    public function edit(DriverTrainingSchool $trainingSchool)
    {
        // Obtener el carrier del conductor asociado a esta escuela
        $driverDetail = UserDriverDetail::find($trainingSchool->user_driver_detail_id);
        $carrierId = $driverDetail ? $driverDetail->carrier_id : null;
        
        // Obtener todos los carriers activos para el selector
        // Usando el mismo filtro que en accidents: status=1
        $carriers = \App\Models\Carrier::where('status', 1)->orderBy('name')->get();
        
        // Obtener conductores del carrier para mostrar en el formulario
        $drivers = [];
        if ($carrierId) {
            $drivers = UserDriverDetail::where('carrier_id', $carrierId)
                ->whereHas('user', function($query) {
                    $query->where('status', 1);
                })
                ->with('user')
                ->get();
                
            // Asegurarse que el conductor de la escuela esté en la lista aunque ya no esté activo
            $driverFound = false;
            foreach ($drivers as $driver) {
                if ($driver->id == $trainingSchool->user_driver_detail_id) {
                    $driverFound = true;
                    break;
                }
            }
            
            if (!$driverFound && $driverDetail) {
                // Añadir el conductor manualmente si no está en la lista (podría estar inactivo)
                $driverDetail->load('user');
                $drivers->push($driverDetail);
            }
        }
        
        $trainingSkills = json_decode($trainingSchool->training_skills ?? '[]', true);
        return view('admin.drivers.training.edit', [
            'school' => $trainingSchool, 
            'drivers' => $drivers,
            'carriers' => $carriers,
            'selectedCarrierId' => $carrierId,
            'trainingSkills' => $trainingSkills
        ]);
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
            
            // Actualizar datos básicos
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
                $files = json_decode($request->training_files, true);
                
                if (is_array($files) && count($files) > 0) {
                    foreach ($files as $file) {
                        if (isset($file['is_temp']) && $file['is_temp'] && isset($file['tmp_path'])) {
                            // Obtener datos del archivo
                            $tempPath = storage_path('app/' . $file['tmp_path']);
                            $originalName = $file['name'];
                            $mimeType = $file['mime_type'] ?? mime_content_type($tempPath);
                            $size = $file['size'] ?? filesize($tempPath);
                            
                            // Verificar que el archivo temporal existe
                            if (!file_exists($tempPath)) {
                                continue;
                            }
                            
                            // Preparar propiedades personalizadas
                            $customProperties = [
                                'original_name' => $originalName,
                                'mime_type' => $mimeType,
                                'size' => $size
                            ];
                            
                            // Mover el archivo a una ubicación permanente (la lógica para esto está en el trait HasDocuments)
                            $destinationDir = 'training-schools/' . $trainingSchool->id;
                            $fileName = uniqid() . '_' . $originalName;
                            $fullPath = $destinationDir . '/' . $fileName;
                            
                            // Usar addDocument del trait HasDocuments
                            $document = $trainingSchool->addDocument($fullPath, 'training_documents', $customProperties);
                            
                            // Mover el archivo de la carpeta temporal a la permanente
                            Storage::makeDirectory($destinationDir);
                            Storage::copy($file['tmp_path'], $fullPath);
                        }
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.training-schools.index')
                ->with('success', 'Training school updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar escuela de entrenamiento', [
                'id' => $trainingSchool->id,
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
            // Eliminar los documentos asociados a la escuela
            $documents = DocumentAttachment::where('documentable_type', DriverTrainingSchool::class)
                ->where('documentable_id', $trainingSchool->id)
                ->get();
                
            foreach ($documents as $document) {
                $trainingSchool->deleteDocument($document->id);
            }
            
            // Eliminar la escuela
            $trainingSchool->delete();
            
            return redirect()->route('admin.training-schools.index')
                ->with('success', 'Training school deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error al eliminar escuela de entrenamiento', [
                'id' => $trainingSchool->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error deleting training school record: ' . $e->getMessage());
        }
    }
    
    /**
     * Muestra los documentos de una escuela de entrenamiento específica
     */
    public function showDocuments(DriverTrainingSchool $school)
    {
        $school->load('userDriverDetail.user');
        
        // Cargar documentos usando DocumentAttachment
        $documents = DocumentAttachment::where('documentable_type', DriverTrainingSchool::class)
            ->where('documentable_id', $school->id)
            ->get();
        
        // Información de depuración
        $debugInfo = [
            'school_id' => $school->id,
            'user_driver_detail_id' => $school->user_driver_detail_id,
            'documents_count' => $documents->count(),
            'collection' => 'training_documents'
        ];
        
        return view('admin.drivers.training.documents', compact('school', 'documents', 'debugInfo'));
    }
    
    /**
     * Muestra todos los documentos de escuelas de entrenamiento en una vista resumida
     */
    public function documents(Request $request)
    {
        try {
            // Obtener todos los documentos asociados con escuelas de entrenamiento usando DocumentAttachment
            $query = DocumentAttachment::where('documentable_type', DriverTrainingSchool::class)
                ->with(['documentable' => function($q) {
                    $q->with('userDriverDetail.user');
                }]);

            // Filtro por escuela
            if ($request->has('school') && !empty($request->school)) {
                $query->where('documentable_id', $request->school);
            }

            // Filtro por conductor
            if ($request->has('driver') && !empty($request->driver)) {
                $query->whereHas('documentable', function($q) use ($request) {
                    $q->where('user_driver_detail_id', $request->driver);
                });
            }

            // Filtro por tipo de archivo
            if ($request->has('file_type') && !empty($request->file_type)) {
                switch ($request->file_type) {
                    case 'image':
                        $query->where('mime_type', 'like', 'image/%');
                        break;
                    case 'pdf':
                        $query->where('mime_type', 'like', '%pdf%');
                        break;
                    case 'doc':
                        $query->where(function($q) {
                            $q->where('mime_type', 'like', '%word%')
                              ->orWhere('mime_type', 'like', '%document%')
                              ->orWhere('mime_type', 'like', '%docx%');
                        });
                        break;
                    case 'xls':
                        $query->where(function($q) {
                            $q->where('mime_type', 'like', '%excel%')
                              ->orWhere('mime_type', 'like', '%sheet%')
                              ->orWhere('mime_type', 'like', '%xlsx%');
                        });
                        break;
                    case 'ppt':
                        $query->where(function($q) {
                            $q->where('mime_type', 'like', '%powerpoint%')
                              ->orWhere('mime_type', 'like', '%presentation%');
                        });
                        break;
                }
            }
            
            // Filtro por fecha de subida (desde)
            if ($request->has('upload_from') && !empty($request->upload_from)) {
                $query->whereDate('created_at', '>=', $request->upload_from);
            }

            // Filtro por fecha de subida (hasta)
            if ($request->has('upload_to') && !empty($request->upload_to)) {
                $query->whereDate('created_at', '<=', $request->upload_to);
            }
            
            // Ordenar por fecha de creación (más recientes primero)
            $documents = $query->orderBy('created_at', 'desc')->paginate(15);
            
            // Cargar todos los conductores para el filtro
            $drivers = UserDriverDetail::with('user')->get();
            
            // Cargar todas las escuelas para el filtro
            $schools = DriverTrainingSchool::orderBy('school_name')->get();
            
            return view('admin.drivers.training.documents', compact('documents', 'drivers', 'schools'));
            
        } catch (\Exception $e) {
            Log::error('Error al cargar documentos de escuelas de entrenamiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al cargar documentos: ' . $e->getMessage());
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
            ->whereHas('user', function($query) {
                $query->where('status', 1);
            })
            ->with('user')
            ->get();
            
        return response()->json($drivers);
    }
}
