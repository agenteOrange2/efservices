<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Driver\Training;
use App\Models\Admin\Driver\DriverTraining;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrainingsController extends Controller
{
    
    /**
     * Display a listing of the trainings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Training::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('content_type')) {
            $query->where('content_type', $request->input('content_type'));
        }

        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        
        // Ensure we're using the correct field for ID sorting
        if ($sortField === 'id') {
            $query->orderBy('id', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $trainings = $query->withCount('driverAssignments')->paginate(10);

        return view('admin.drivers.trainings.index', compact('trainings'));
    }

    /**
     * Show the form for creating a new training.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.drivers.trainings.create');
    }

    /**
     * Store a newly created training in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content_type' => 'required|in:file,video,url',
            'status' => 'required|in:active,inactive',
            'video_url' => 'nullable|url|required_if:content_type,video',
            'url' => 'nullable|url|required_if:content_type,url',
            'files_data' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $training = Training::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'content_type' => $validated['content_type'],
                'status' => $validated['status'],
                'video_url' => $validated['content_type'] === 'video' ? $validated['video_url'] : null,
                'url' => $validated['content_type'] === 'url' ? $validated['url'] : null,
                'created_by' => Auth::id(),
            ]);

            // Procesar archivos del componente Livewire FileUploader
            if ($request->filled('files_data')) {
                Log::info('Procesando files_data', ['data' => $request->input('files_data')]);
                
                try {
                    $filesData = json_decode($request->input('files_data'), true);
                    
                    if (is_array($filesData)) {
                        Log::info('Archivos encontrados', ['count' => count($filesData)]);
                        
                        foreach ($filesData as $fileData) {
                            // Verificar si es un archivo temporal subido por Livewire
                            if (isset($fileData['path']) || isset($fileData['tempPath'])) {
                                // Obtener la ruta del archivo temporal
                                $tempPath = isset($fileData['path']) ? $fileData['path'] : $fileData['tempPath'];
                                $fullPath = storage_path('app/' . $tempPath);
                                
                                Log::info('Procesando archivo', [
                                    'tempPath' => $tempPath,
                                    'fullPath' => $fullPath,
                                    'exists' => file_exists($fullPath)
                                ]);
                                
                                if (file_exists($fullPath)) {
                                    try {
                                        // Añadir el archivo a la colección de medios
                                        $media = $training->addMedia($fullPath)
                                            ->withCustomProperties([
                                                'original_name' => $fileData['original_name'] ?? $fileData['name'] ?? basename($fullPath),
                                                'mime_type' => $fileData['mime_type'] ?? null,
                                                'size' => $fileData['size'] ?? null
                                            ])
                                            ->toMediaCollection('training_files');
                                        
                                        Log::info('Archivo añadido a la colección', [
                                            'media_id' => $media->id,
                                            'path' => $media->getPath(),
                                            'url' => $media->getUrl()
                                        ]);
                                    } catch (\Exception $mediaException) {
                                        Log::error('Error al añadir archivo a la colección', [
                                            'message' => $mediaException->getMessage(),
                                            'tempPath' => $fullPath
                                        ]);
                                    }
                                } else {
                                    Log::warning('Archivo temporal no encontrado', ['path' => $fullPath]);
                                }
                            } else {
                                Log::warning('Datos de archivo incompletos', ['fileData' => $fileData]);
                            }
                        }
                    } else {
                        Log::warning('files_data no es un array válido', ['filesData' => $filesData]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error al procesar files_data', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.trainings.index')
                ->with('success', 'Training created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating training: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Error creating training: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified training.
     *
     * @param  \App\Models\Training  $training
     * @return \Illuminate\Http\Response
     */
    public function show(Training $training)
    {
        $training->load('media');
        $assignmentStats = [
            'total' => $training->driverAssignments()->count(),
            'completed' => $training->driverAssignments()->where('status', 'completed')->count(),
            'in_progress' => $training->driverAssignments()->where('status', 'in_progress')->count(),
            'pending' => $training->driverAssignments()->where('status', 'pending')->count(),
        ];

        return view('admin.drivers.trainings.show', compact('training', 'assignmentStats'));
    }

    /**
     * Show the form for editing the specified training.
     *
     * @param  \App\Models\Training  $training
     * @return \Illuminate\Http\Response
     */
    public function edit(Training $training)
    {
        $training->load('media');
        return view('admin.drivers.trainings.edit', compact('training'));
    }

    /**
     * Update the specified training in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Training  $training
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Training $training)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:file,video,url',
            'status' => 'required|in:active,inactive',
            'video_url' => 'nullable|url|required_if:content_type,video',
            'url' => 'nullable|url|required_if:content_type,url',
        ]);

        try {
            DB::beginTransaction();

            $training->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'content_type' => $validated['content_type'],
                'status' => $validated['status'],
                'video_url' => $validated['content_type'] === 'video' ? $validated['video_url'] : null,
                'url' => $validated['content_type'] === 'url' ? $validated['url'] : null,
            ]);

            // Procesar archivos del componente Livewire FileUploader
            if ($request->filled('files_data')) {
                Log::info('Procesando files_data en update', ['data' => $request->input('files_data')]);
                
                try {
                    $filesData = json_decode($request->input('files_data'), true);
                    
                    if (is_array($filesData)) {
                        Log::info('Archivos encontrados en update', ['count' => count($filesData)]);
                        
                        foreach ($filesData as $fileData) {
                            // Verificar si es un archivo temporal subido por Livewire
                            if (isset($fileData['path']) || isset($fileData['tempPath'])) {
                                // Obtener la ruta del archivo temporal
                                $tempPath = isset($fileData['path']) ? $fileData['path'] : $fileData['tempPath'];
                                $fullPath = storage_path('app/' . $tempPath);
                                
                                Log::info('Procesando archivo en update', [
                                    'tempPath' => $tempPath,
                                    'fullPath' => $fullPath,
                                    'exists' => file_exists($fullPath)
                                ]);
                                
                                if (file_exists($fullPath)) {
                                    try {
                                        // Añadir el archivo a la colección de medios
                                        $media = $training->addMedia($fullPath)
                                            ->withCustomProperties([
                                                'original_name' => $fileData['original_name'] ?? $fileData['name'] ?? basename($fullPath),
                                                'mime_type' => $fileData['mime_type'] ?? null,
                                                'size' => $fileData['size'] ?? null
                                            ])
                                            ->toMediaCollection('training_files');
                                        
                                        Log::info('Archivo añadido a la colección en update', [
                                            'media_id' => $media->id,
                                            'path' => $media->getPath(),
                                            'url' => $media->getUrl()
                                        ]);
                                    } catch (\Exception $mediaException) {
                                        Log::error('Error al añadir archivo a la colección en update', [
                                            'message' => $mediaException->getMessage(),
                                            'tempPath' => $fullPath
                                        ]);
                                    }
                                } else {
                                    Log::warning('Archivo temporal no encontrado en update', ['path' => $fullPath]);
                                }
                            } else {
                                Log::warning('Datos de archivo incompletos en update', ['fileData' => $fileData]);
                            }
                        }
                    } else {
                        Log::warning('files_data no es un array válido en update', ['filesData' => $filesData]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error al procesar files_data en update', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.trainings.index')
                ->with('success', 'Entrenamiento actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar entrenamiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Error al actualizar entrenamiento: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified training from storage.
     *
     * @param  \App\Models\Training  $training
     * @return \Illuminate\Http\Response
     */
    /**
     * Delete a document attached to a training.
     *
     * @param  int  $document
     * @return \Illuminate\Http\Response
     */
    public function deleteDocument($document)
    {
        try {
            // Buscar el media por ID
            $media = DB::table('media')->where('id', $document)->first();
            
            if (!$media) {
                return back()->with('error', 'Documento no encontrado.');
            }
            
            // Verificar si el archivo existe físicamente y eliminarlo
            $filePath = storage_path('app/public/' . $media->id . '/' . $media->file_name);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Eliminar el registro de la tabla media directamente para evitar eliminación en cascada
            DB::table('media')->where('id', $document)->delete();
            
            return back()->with('success', 'Documento eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'media_id' => $document,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Preview a document attached to a training.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $document
     * @return \Illuminate\Http\Response
     */
    public function previewDocument(Request $request, $document)
    {
        try {
            // Buscar el media por ID usando el modelo Media
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($document);
            
            if (!$media) {
                return back()->with('error', 'Document not found.');
            }
            
            // Verificar si se debe descargar o mostrar
            if ($request->has('download')) {
                return response()->download($media->getPath(), $media->file_name);
            }
            
            // Devolver el archivo para visualización
            return response()->file($media->getPath(), [
                'Content-Type' => $media->mime_type
            ]);
        } catch (\Exception $e) {
            Log::error('Error previewing document', [
                'media_id' => $document,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error al visualizar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a training.
     *
     * @param  \App\Models\Admin\Driver\Training  $training
     * @return \Illuminate\Http\Response
     */
    public function destroy(Training $training)
    {
        try {
            // Check if there are any assignments
            $assignmentsCount = $training->driverAssignments()->count();
            if ($assignmentsCount > 0) {
                return back()->with('error', "Cannot delete training. It has {$assignmentsCount} assignments.");
            }

            // Delete all media
            $training->clearMediaCollection('training_files');
            
            // Delete the training
            $training->delete();

            return redirect()->route('admin.trainings.index')
                ->with('success', 'Training deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting training: ' . $e->getMessage());
            
            return back()->with('error', 'Error deleting training: ' . $e->getMessage());
        }
    }

    // El método showAssignForm ha sido movido a TrainingAssignmentsController
    
    /**
     * Show the training selection page for assignments.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignSelect()
    {
        $trainings = Training::where('status', 'active')->get();
        return view('admin.drivers.trainings.assign-select', compact('trainings'));
    }

    // El método assign ha sido movido a TrainingAssignmentsController

    /**
     * Remove a document from a training.
     *
     * @param  \App\Models\Media  $document
     * @return \Illuminate\Http\Response
     */
    public function destroyDocument($document)
    {
        try {
            // Find the media item
            $media = Media::findOrFail($document);
            
            // Verify it belongs to a training
            if ($media->model_type !== Training::class) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            // Delete the media directly from the database to avoid cascade issues
            DB::table('media')->where('id', $media->id)->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage());
            return response()->json(['error' => 'Error deleting document'], 500);
        }
    }
}
