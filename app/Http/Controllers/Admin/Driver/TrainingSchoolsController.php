<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTrainingSchool;
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
        $drivers = UserDriverDetail::with('user')->get();
        return view('admin.drivers.training.create', compact('drivers'));
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
            
            // Procesar archivos adjuntos si existen
            if ($request->has('training_files')) {
                $filesData = json_decode($request->training_files, true);
                
                if (is_array($filesData) && count($filesData) > 0) {
                    foreach ($filesData as $fileData) {
                        // Verificar que tiene los datos necesarios
                        if (!isset($fileData['tempPath']) || !isset($fileData['originalName'])) {
                            continue;
                        }
                        
                        $tempPath = storage_path('app/' . $fileData['tempPath']);
                        
                        // Verificar que el archivo existe
                        if (!file_exists($tempPath)) {
                            Log::warning('Archivo temporal no encontrado al guardar documento de escuela de entrenamiento', [
                                'temp_path' => $tempPath,
                                'original_name' => $fileData['originalName']
                            ]);
                            continue;
                        }
                        
                        // Añadir el archivo a la colección de medios usando Spatie MediaLibrary
                        try {
                            $trainingSchool->addMedia($tempPath)
                                ->usingName($fileData['originalName'])
                                ->usingFileName($fileData['originalName'])
                                ->toMediaCollection('training_files');
                                
                            Log::info('Documento de escuela de entrenamiento guardado con éxito', [
                                'training_school_id' => $trainingSchool->id,
                                'file_name' => $fileData['originalName']
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error al guardar documento de escuela de entrenamiento', [
                                'training_school_id' => $trainingSchool->id,
                                'file_name' => $fileData['originalName'],
                                'error' => $e->getMessage()
                            ]);
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
        $school = $trainingSchool->load('userDriverDetail.user');
        return view('admin.drivers.training.show', compact('school'));
    }
    
    /**
     * Muestra el formulario para editar una escuela de entrenamiento existente
     */
    public function edit(DriverTrainingSchool $trainingSchool)
    {
        $drivers = UserDriverDetail::with('user')->get();
        $school = $trainingSchool->load('userDriverDetail.user');
        
        return view('admin.drivers.training.edit', compact('school', 'drivers'));
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
            
            // Actualizar el registro
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
            
            // Procesar archivos adjuntos si existen
            if ($request->has('training_files')) {
                $filesData = json_decode($request->training_files, true);
                
                if (is_array($filesData) && count($filesData) > 0) {
                    foreach ($filesData as $fileData) {
                        // Verificar que tiene los datos necesarios
                        if (!isset($fileData['tempPath']) || !isset($fileData['originalName'])) {
                            continue;
                        }
                        
                        $tempPath = storage_path('app/' . $fileData['tempPath']);
                        
                        // Verificar que el archivo existe
                        if (!file_exists($tempPath)) {
                            Log::warning('Archivo temporal no encontrado al actualizar documento de escuela de entrenamiento', [
                                'temp_path' => $tempPath,
                                'original_name' => $fileData['originalName']
                            ]);
                            continue;
                        }
                        
                        // Añadir el archivo a la colección de medios usando Spatie MediaLibrary
                        try {
                            $trainingSchool->addMedia($tempPath)
                                ->usingName($fileData['originalName'])
                                ->usingFileName($fileData['originalName'])
                                ->toMediaCollection('training_files');
                                
                            Log::info('Documento de escuela de entrenamiento actualizado con éxito', [
                                'training_school_id' => $trainingSchool->id,
                                'file_name' => $fileData['originalName']
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error al actualizar documento de escuela de entrenamiento', [
                                'training_school_id' => $trainingSchool->id,
                                'file_name' => $fileData['originalName'],
                                'error' => $e->getMessage()
                            ]);
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
            // Eliminar todos los documentos asociados
            $trainingSchool->getMedia('training_files')->each->delete();
            
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
     * Muestra todos los documentos de escuelas de entrenamiento en una vista resumida
     */
    public function documents(Request $request)
    {
        try {
            // Obtener todos los documentos asociados con escuelas de entrenamiento
            $query = Media::where('model_type', DriverTrainingSchool::class)
                ->with(['model' => function($q) {
                    $q->with('userDriverDetail.user');
                }]);

            // Filtro por escuela
            if ($request->has('school') && !empty($request->school)) {
                $query->where('model_id', $request->school);
            }

            // Filtro por conductor
            if ($request->has('driver') && !empty($request->driver)) {
                $query->whereHas('model', function($q) use ($request) {
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
                        $query->where('mime_type', 'application/pdf');
                        break;
                    case 'doc':
                        $query->where(function($q) {
                            $q->where('mime_type', 'like', '%word%')
                              ->orWhere('mime_type', 'like', '%excel%')
                              ->orWhere('mime_type', 'like', '%sheet%')
                              ->orWhere('mime_type', 'like', '%csv%')
                              ->orWhere('mime_type', 'like', '%powerpoint%')
                              ->orWhere('mime_type', 'like', '%presentation%');
                        });
                        break;
                }
            }

            // Filtro por fecha de subida
            if ($request->has('upload_from') && !empty($request->upload_from)) {
                $query->whereDate('created_at', '>=', $request->upload_from);
            }

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
     * Muestra los documentos de una escuela de entrenamiento específica
     */
    public function showDocuments(DriverTrainingSchool $school)
    {
        $school->load('userDriverDetail.user');
        return view('admin.drivers.training.show', compact('school'));
    }
    
    /**
     * Muestra o descarga un documento
     */
    public function previewDocument($id, Request $request)
    {
        try {
            $media = Media::findOrFail($id);
            
            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($media->model_type !== DriverTrainingSchool::class) {
                return redirect()->back()->with('error', 'Invalid document type');
            }
            
            // Si se solicita descarga
            if ($request->has('download')) {
                return response()->download($media->getPath(), $media->file_name);
            }
            
            // Si es una imagen, mostrar directamente
            if (str_starts_with($media->mime_type, 'image/')) {
                return response()->file($media->getPath());
            }
            
            // Para PDF, mostrar en el navegador
            if ($media->mime_type === 'application/pdf') {
                return response()->file($media->getPath(), [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
                ]);
            }
            
            // Para otros tipos, descargar
            return response()->download($media->getPath(), $media->file_name);
            
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al previsualizar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina un documento mediante AJAX
     */
    public function ajaxDestroyDocument(Request $request, $id)
    {
        try {
            $media = Media::findOrFail($id);
            
            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($media->model_type !== DriverTrainingSchool::class) {
                return response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            }
            
            $fileName = $media->file_name;
            $schoolId = $media->model_id;
            
            // Eliminar el archivo
            $media->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Document '{$fileName}' deleted successfully"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento vía AJAX', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Error deleting document: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Elimina un documento
     */
    public function destroyDocument($id)
    {
        try {
            $media = Media::findOrFail($id);
            
            // Verificar que el documento pertenece a una escuela de entrenamiento
            if ($media->model_type !== DriverTrainingSchool::class) {
                return redirect()->back()->with('error', 'Invalid document type');
            }
            
            $fileName = $media->file_name;
            $schoolId = $media->model_id;
            
            // Eliminar el archivo
            $media->delete();
            
            return redirect()->route('admin.training-schools.show', $schoolId)
                ->with('success', "Document '{$fileName}' deleted successfully");
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }
}
