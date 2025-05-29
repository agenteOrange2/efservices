<?php
namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTesting;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TestingsController extends Controller
{
    // Vista para todos los tests
    public function index(Request $request)
    {
        $query = DriverTesting::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('test_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('carrier_filter')) {
            $query->whereHas('userDriverDetail', function ($subq) use ($request) {
                $subq->where('carrier_id', $request->carrier_filter);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('test_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('test_date', '<=', $request->date_to);
        }

        if ($request->filled('test_type')) {
            $query->where('test_type', $request->test_type);
        }

        if ($request->filled('test_result')) {
            $query->where('test_result', $request->test_result);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'test_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $testings = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        // Obtener valores únicos para los filtros de desplegable
        $testTypes = DriverTesting::distinct()->pluck('test_type')->filter()->toArray();
        $testResults = DriverTesting::distinct()->pluck('test_result')->filter()->toArray();

        return view('admin.drivers.testings.index', compact('testings', 'drivers', 'carriers', 'testTypes', 'testResults'));
    }

    // Vista para el historial de pruebas de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverTesting::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('test_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('test_type')) {
            $query->where('test_type', $request->test_type);
        }

        if ($request->filled('test_result')) {
            $query->where('test_result', $request->test_result);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'test_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $testings = $query->paginate(10);
        
        // Obtener valores únicos para los filtros de desplegable
        $testTypes = DriverTesting::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('test_type')->filter()->toArray();
        $testResults = DriverTesting::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('test_result')->filter()->toArray();

        return view('admin.drivers.testings.driver_history', compact('driver', 'testings', 'testTypes', 'testResults'));
    }

    // Método para almacenar una nueva prueba
    public function store(Request $request)
    {
        //dd($request->all());
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'test_date' => 'required|date',
                'test_type' => 'required|string|max:255',
                'test_result' => 'required|string|max:255',
                'administered_by' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'next_test_due' => 'nullable|date',
                'is_random_test' => 'boolean',
                'is_post_accident_test' => 'boolean',
                'is_reasonable_suspicion_test' => 'boolean',
            ]);

            // Convertir checkboxes a valores booleanos
            $validated['is_random_test'] = isset($request->is_random_test);
            $validated['is_post_accident_test'] = isset($request->is_post_accident_test);
            $validated['is_reasonable_suspicion_test'] = isset($request->is_reasonable_suspicion_test);

            $testing = DriverTesting::create($validated);

            // Procesar los archivos subidos vía Livewire
            $files = $request->get('test_files');
            $uploadedCount = 0;
            
            Log::info('Procesando archivos en store de testing', [
                'files_data' => $files,
                'testing_id' => $testing->id
            ]);
            
            if (!empty($files)) {
                $filesArray = json_decode($files, true);
                
                if (is_array($filesArray)) {
                    foreach ($filesArray as $file) {
                        // Verificamos que el archivo exista
                        if (!empty($file['path'])) {
                            $filePath = $file['path'];
                            $disk = config('filesystems.default', 'local');
                            
                            // Si la ruta no tiene el formato completo con base (cuando viene de StorageServiceProvider)
                            if (strpos($filePath, '/') !== 0 && strpos($filePath, ':\\') !== 1) {
                                Log::info('Ruta de archivo relativa: ' . $filePath);
                            } else {
                                // Si es una ruta absoluta, ajustamos para usar el disco correcto
                                $basePath = storage_path('app/' . $disk . '/');
                                $filePath = str_replace($basePath, '', $filePath);
                                Log::info('Ruta de archivo absoluta convertida a relativa: ' . $filePath);
                            }
                            
                            // Verificar que el archivo exista en el disco temporal
                            if (Storage::disk($disk)->exists($filePath)) {
                                $driverId = $testing->userDriverDetail->id;
                                
                                try {
                                    $media = $testing->addMediaFromDisk($filePath, $disk)
                                        ->usingName($file['original_name'] ?? 'document')
                                        ->usingFileName($file['original_name'] ?? 'document')
                                        ->withCustomProperties([
                                            'original_filename' => $file['original_name'] ?? 'document',
                                            'mime_type' => $file['mime_type'] ?? 'application/octet-stream',
                                            'testing_id' => $testing->id,
                                            'driver_id' => $driverId,
                                            'size' => $file['size'] ?? 0
                                        ])
                                        ->toMediaCollection('test_documents');
                                        
                                    $uploadedCount++;
                                    
                                    Log::info('Documento de prueba subido correctamente durante creación', [
                                        'testing_id' => $testing->id,
                                        'media_id' => $media->id,
                                        'file_name' => $media->file_name,
                                        'collection' => $media->collection_name,
                                        'driver_id' => $driverId
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error('Error al subir documento de prueba', [
                                        'error' => $e->getMessage(),
                                        'file' => $filePath,
                                        'testing_id' => $testing->id
                                    ]);
                                }
                            } else {
                                Log::error('Archivo no encontrado en disco temporal', [
                                    'path' => $filePath,
                                    'disk' => $disk,
                                    'full_path' => storage_path('app/' . $disk . '/' . $filePath)
                                ]);
                            }
                        }
                    }
                } else {
                    Log::error('JSON inválido en test_files', ['raw_data' => $files]);
                }
            }
            
            // También manejar archivos subidos directamente vía formulario (no Livewire)
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $driverId = $testing->userDriverDetail->id;
                    
                    $media = $testing->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->withCustomProperties([
                            'original_filename' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'testing_id' => $testing->id,
                            'driver_id' => $driverId
                        ])
                        ->toMediaCollection('test_documents');
                        
                    $uploadedCount++;
                    
                    Log::info('Documento de prueba subido directamente durante creación', [
                        'testing_id' => $testing->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                        'collection' => $media->collection_name
                    ]);
                }
            }

            DB::commit();
            Session::flash('success', 'Testing record added successfully!');

            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('admin.drivers.testing-history', $validated['user_driver_detail_id']);
            }
            return redirect()->route('admin.testings.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating testing record: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error creating testing record: ' . $e->getMessage());
        }
    }

    // Método para actualizar una prueba existente
    public function update(DriverTesting $testing, Request $request)
    {
        //dd($request->all());
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'test_date' => 'required|date',
                'test_type' => 'required|string|max:255',
                'test_result' => 'required|string|max:255',
                'administered_by' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'next_test_due' => 'nullable|date',
                'is_random_test' => 'boolean',
                'is_post_accident_test' => 'boolean',
                'is_reasonable_suspicion_test' => 'boolean',
            ]);

            // Convertir checkboxes a valores booleanos
            $validated['is_random_test'] = isset($request->is_random_test);
            $validated['is_post_accident_test'] = isset($request->is_post_accident_test);
            $validated['is_reasonable_suspicion_test'] = isset($request->is_reasonable_suspicion_test);

            $testing->update($validated);
            
            // Si hay documentos nuevos subidos vía Livewire
            $files = $request->get('test_files');
            $uploadedCount = 0;
            
            Log::info('Procesando archivos en update de testing', [
                'files_data' => $files,
                'testing_id' => $testing->id
            ]);
            
            if (!empty($files)) {
                try {
                    $filesArray = json_decode($files, true);
                    
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
                                    'testing_id' => $testing->id
                                ]);
                                continue;
                            }
                            
                            $driverId = $testing->userDriverDetail->id;
                            
                            // Usar addMedia directamente desde la ruta del archivo
                            $media = $testing->addMedia($fullPath)
                                ->usingName($file['original_name'] ?? 'document')
                                ->usingFileName($file['original_name'] ?? 'document')
                                ->withCustomProperties([
                                    'original_filename' => $file['original_name'] ?? 'document',
                                    'mime_type' => $file['mime_type'] ?? 'application/octet-stream',
                                    'testing_id' => $testing->id,
                                    'driver_id' => $driverId,
                                    'size' => $file['size'] ?? 0
                                ])
                                ->toMediaCollection('test_documents');
                            
                            $uploadedCount++;
                            
                            Log::info('Documento de prueba subido correctamente en update', [
                                'testing_id' => $testing->id,
                                'media_id' => $media->id,
                                'file_name' => $media->file_name,
                                'collection' => 'test_documents'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error al procesar documentos vía Livewire en update', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'testing_id' => $testing->id
                    ]);
                }
            }
            
            // También manejar archivos subidos directamente vía formulario (no Livewire)
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $driverId = $testing->userDriverDetail->id;
                    
                    $media = $testing->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->withCustomProperties([
                            'original_filename' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'testing_id' => $testing->id,
                            'driver_id' => $driverId
                        ])
                        ->toMediaCollection('test_documents');
                        
                    $uploadedCount++;
                    
                    Log::info('Documento de prueba subido directamente durante actualización', [
                        'testing_id' => $testing->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                        'collection' => $media->collection_name
                    ]);
                }
            }
            
            if ($uploadedCount > 0) {
                Session::flash('success', "Prueba actualizada y $uploadedCount documentos subidos correctamente");
            } else {
                Session::flash('success', 'Testing record updated successfully!');
            }

            DB::commit();
            
            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('admin.drivers.testing-history', $testing->user_driver_detail_id);
            }
            return redirect()->route('admin.testings.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating testing record: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'testing_id' => $testing->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error updating testing record: ' . $e->getMessage());
        }
    }

    // Método para eliminar una prueba
    public function destroy(DriverTesting $testing)
    {
        $driverId = $testing->user_driver_detail_id;
        $testing->delete();

        Session::flash('success', 'Testing record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'testing-history') !== false) {
            return redirect()->route('admin.drivers.testing-history', $driverId);
        }
        return redirect()->route('admin.testings.index');
    }

    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with(['user']) // Asegúrate de incluir la relación con el usuario
            ->get();
        return response()->json($drivers);
    }
    
    /**
     * Método para mostrar los documentos de una prueba específica
     */
    public function documents(DriverTesting $testing)
    {
        $documents = $testing->getMedia('test_documents');
        return view('admin.drivers.testings.documents', compact('testing', 'documents'));
    }
    
    /**
     * Método para almacenar documentos adicionales para una prueba existente
     */
    public function storeDocuments(Request $request, $testingId)
    {
        $testing = DriverTesting::findOrFail($testingId);
        
        $request->validate([
            'documents.*' => 'required|file|max:10240', // 10MB máximo
        ]);
        
        $uploadedCount = 0;
        
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $driverId = $testing->userDriverDetail->id;
                
                $testing->addMedia($file)
                    ->usingName($file->getClientOriginalName())
                    ->usingFileName($file->getClientOriginalName())
                    ->withCustomProperties([
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'testing_id' => $testing->id,
                        'driver_id' => $driverId
                    ])
                    ->toMediaCollection('test_documents');
                
                $uploadedCount++;
            }
        }
        
        if ($uploadedCount > 0) {
            return redirect()->back()->with('success', "$uploadedCount documentos subidos exitosamente");
        } else {
            return redirect()->back()->with('error', "No se subieron documentos");
        }
    }
    
    /**
     * Método para previsualizar un documento específico
     */
    public function previewDocument($documentId)
    {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($documentId);
        
        // Verificar que el documento pertenezca a una prueba
        $testing = DriverTesting::find($media->model_id);
        if (!$testing) {
            abort(404, 'Document not found');
        }
        
        // Si es una imagen, mostrarla en el navegador
        if (str_starts_with($media->mime_type, 'image/')) {
            return response()->file($media->getPath());
        }
        
        // Para otros tipos de archivo, descargarlos
        return response()->download($media->getPath(), $media->file_name);
    }
    
    /**
     * Método para eliminar un documento específico
     */
    public function destroyDocument($documentId)
    {
        DB::beginTransaction();
        try {
            // Buscar el documento (media)
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($documentId);
            
            // Verificar que el documento pertenece a una prueba
            $testing = DriverTesting::find($media->model_id);
            if (!$testing) {
                return response()->json(['success' => false, 'message' => 'Documento no encontrado'], 404);
            }
            
            // Importante: desasociar el documento antes de eliminarlo para prevenir eliminación en cascada
            // Esto evita que el modelo DriverTesting sea eliminado cuando se elimina el media
            $mediaId = $media->id;
            $fileName = $media->file_name;
            
            // Eliminar la relación en la base de datos primero
            DB::table('media')
                ->where('id', $mediaId)
                ->update(['model_id' => null, 'model_type' => null]);
            
            // Ahora eliminar el archivo físico y el registro de media
            $media->delete();
            
            DB::commit();
            
            Log::info('Documento de prueba eliminado correctamente', [
                'media_id' => $mediaId,
                'file_name' => $fileName,
                'testing_id' => $testing->id
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Documento eliminado correctamente',
                'media_id' => $mediaId
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar documento de prueba', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'document_id' => $documentId
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar documento: ' . $e->getMessage()
            ], 500);
        }
    }
}