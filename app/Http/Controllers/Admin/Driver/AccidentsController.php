<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Admin\Driver\DriverAccident;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AccidentsController extends Controller
{
    // Vista para todos los accidentes
    public function index(Request $request)
    {
        $query = DriverAccident::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('nature_of_accident', 'like', '%' . $request->search_term . '%')
                ->orWhere('comments', 'like', '%' . $request->search_term . '%');
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
            $query->whereDate('accident_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('accident_date', '<=', $request->date_to);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'accident_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $accidents = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.accidents.index', compact('accidents', 'drivers', 'carriers'));
    }

    // Vista para el historial de accidentes de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverAccident::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('nature_of_accident', 'like', '%' . $request->search_term . '%')
                ->orWhere('comments', 'like', '%' . $request->search_term . '%');
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'accident_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $accidents = $query->paginate(10);

        return view('admin.drivers.accidents.driver_history', compact('driver', 'accidents'));
    }
    
    /**
     * Muestra el formulario para crear un nuevo accidente
     */
    public function create()
    {
        $carriers = Carrier::where('status', 1)->get();
        return view('admin.drivers.accidents.create', compact('carriers'));
    }

    // Método para almacenar un nuevo accidente
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'accident_date' => 'required|date',
            'nature_of_accident' => 'required|string|max:255',
            'had_injuries' => 'boolean',
            'number_of_injuries' => 'nullable|integer|min:0',
            'had_fatalities' => 'boolean',
            'number_of_fatalities' => 'nullable|integer|min:0',
            'comments' => 'nullable|string',
            'documents.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        // Convertir checkboxes a valores booleanos
        $validated['had_injuries'] = isset($request->had_injuries);
        $validated['had_fatalities'] = isset($request->had_fatalities);

        // Solo incluir el número de lesiones/fatalidades si se marcó el checkbox
        if (!$validated['had_injuries']) {
            $validated['number_of_injuries'] = 0;
        }

        if (!$validated['had_fatalities']) {
            $validated['number_of_fatalities'] = 0;
        }

        // Crear el accidente
        $accident = DriverAccident::create($validated);

        // Procesar documentos si los hay
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                // Obtener el ID del conductor asociado al accidente
                $driverId = $accident->userDriverDetail->id;
                
                // Subir usando Media Library
                $accident->addMedia($file)
                    ->usingName($file->getClientOriginalName())
                    ->usingFileName($file->getClientOriginalName())
                    ->withCustomProperties([
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'accident_id' => $accident->id,
                        'driver_id' => $driverId
                    ])
                    ->toMediaCollection('accident_documents');
            }
        }

        Session::flash('success', 'Accidente creado correctamente');
        return redirect()->route('admin.accidents.index');
    }

    // Muestra el formulario para editar un accidente existente
    public function edit(DriverAccident $accident)
    {
        // Obtener los documentos del accidente usando la colección definida
        $documents = $accident->getMedia('accident_documents');
        
        return view('admin.drivers.accidents.edit', compact('accident', 'documents'));
    }

    // Método para actualizar un accidente existente
    public function update(DriverAccident $accident, Request $request)
    {
        $validated = $request->validate([
            'accident_date' => 'required|date',
            'nature_of_accident' => 'required|string|max:255',
            'had_injuries' => 'boolean',
            'number_of_injuries' => 'nullable|integer|min:0',
            'had_fatalities' => 'boolean',
            'number_of_fatalities' => 'nullable|integer|min:0',
            'comments' => 'nullable|string',
            'documents.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $validated['had_injuries'] = isset($request->had_injuries);
        $validated['had_fatalities'] = isset($request->had_fatalities);

        // Si no hay lesiones/fatalidades, establecer el número a 0
        if (!$validated['had_injuries']) {
            $validated['number_of_injuries'] = 0;
        }

        if (!$validated['had_fatalities']) {
            $validated['number_of_fatalities'] = 0;
        }

        $accident->update($validated);
        
        // Procesar documentos si existen
        if ($request->hasFile('documents')) {
            $uploadedCount = 0;
            
            foreach ($request->file('documents') as $file) {
                // Obtener el ID del conductor asociado al accidente
                $driverId = $accident->userDriverDetail->id;
                
                // Añadir el archivo a la colección 'accident_documents'
                $media = $accident->addMedia($file)
                    ->usingName($file->getClientOriginalName())
                    ->usingFileName($file->getClientOriginalName())
                    ->withCustomProperties([
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'accident_id' => $accident->id,
                        'driver_id' => $driverId
                    ])
                    ->toMediaCollection('accident_documents');
                
                $uploadedCount++;
                
                // Registrar la subida exitosa
                Log::info('Documento de accidente subido correctamente', [
                    'accident_id' => $accident->id,
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection' => $media->collection_name
                ]);
            }
            
            // Añadir mensaje de éxito específico para los documentos
            return redirect()->route('admin.accidents.edit', $accident->id)
                ->with('success', "Accidente actualizado correctamente. $uploadedCount documentos subidos.");
        }

        return redirect()->route('admin.accidents.index')
            ->with('success', 'Accidente actualizado correctamente');
        return redirect()->route('admin.accidents.edit', $accident);
    }

    // Método para eliminar un accidente
    public function destroy(DriverAccident $accident)
    {
        try {
            // Eliminar los documentos relacionados
            $accident->clearMediaCollection('accident_documents');
            
            // Eliminar el accidente
            $accident->delete();
            
            return redirect()->route('admin.accidents.index')
                ->with('success', 'Accidente eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar accidente: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'No se pudo eliminar el accidente: ' . $e->getMessage());
        }
    }

    // Obtiene los conductores activos asociados a un carrier específico.
    // 
    // @param int $carrier El ID del carrier cuyos conductores se desean obtener
    // @return \Illuminate\Http\JsonResponse Lista de conductores activos en formato JSON
    public function getDriversByCarrier($carrier)
    {
        try {
            // Obtener conductores activos asociados al carrier
            $drivers = UserDriverDetail::whereHas('user', function ($query) {
                $query->where('status', 1); // Solo usuarios activos
            })
            ->where('carrier_id', $carrier)
            ->with('user') // Cargar relación de usuario para acceder a nombre, etc.
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->user->name . ' ' . $driver->user->lastname,
                    'email' => $driver->user->email
                ];
            });
            
            return response()->json([
                'success' => true,
                'drivers' => $drivers
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener conductores por carrier', [
                'carrier_id' => $carrier,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conductores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra la vista de documentos para un accidente específico.
     *
     * @param DriverAccident $accident El accidente del que se mostrarán los documentos
     * @return \Illuminate\View\View Vista con los documentos del accidente
     */
    public function showDocuments(DriverAccident $accident)
    {
        // Obtener los documentos del accidente usando la colección definida
        $documents = $accident->getMedia('accident_documents');
        $totalDocuments = count($documents);
        
        // Agrupar documentos por tipo
        $groupedDocuments = [
            'images' => [],
            'pdfs' => [],
            'documents' => []
        ];
        
        foreach ($documents as $document) {
            $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $groupedDocuments['images'][] = $document;
            } elseif ($extension === 'pdf') {
                $groupedDocuments['pdfs'][] = $document;
            } else {
                $groupedDocuments['documents'][] = $document;
            }
        }
        
        return view('admin.drivers.accidents.documents', compact(
            'accident', 
            'documents', 
            'totalDocuments', 
            'groupedDocuments'
        ));
    }

    // Previsualiza un documento relacionado con accidentes.
    // 
    // @param int $documentId ID del documento a previsualizar
    // @return \Illuminate\Http\Response Respuesta con la previsualización o descarga
    public function previewDocument($documentId)
    {
        try {
            $media = Media::findOrFail($documentId);
            
            // Verificar que el documento pertenece a un accidente
            if ($media->model_type !== DriverAccident::class) {
                abort(403, 'No tienes permiso para ver este documento');
            }
            
            // Verificar si el archivo existe físicamente
            if (!file_exists($media->getPath())) {
                throw new \Exception("El archivo '{$media->file_name}' no se encuentra en el servidor");
            }
            
            // Servir archivo según tipo
            $mime = $media->mime_type;
            
            if (strpos($mime, 'image/') === 0) {
                // Imágenes pueden visualizarse directamente
                return response()->file($media->getPath());
            } elseif ($mime === 'application/pdf') {
                // PDFs se pueden previsualizar en el navegador
                return response()->file($media->getPath(), [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
                ]);
            } else {
                // Otros tipos de archivos se descargan
                return response()->download($media->getPath(), $media->file_name);
            }
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento', [
                'media_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(404, 'El documento solicitado no se pudo encontrar: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un documento (media) asociado a un accidente
     * 
     * @param int $media ID del documento a eliminar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($media)
    {
        try {
            // Iniciar una transacción de base de datos para controlar la operación
            DB::beginTransaction();
            
            // 1. Buscar el media primero
            $mediaItem = Media::findOrFail($media);
            $fileName = $mediaItem->file_name;
            $filePath = $mediaItem->getPath();
            
            // 2. Verificar que pertenezca a un accidente
            if ($mediaItem->model_type !== DriverAccident::class) {
                DB::rollBack();
                return redirect()->back()->with('error', 'El documento no pertenece a un accidente');
            }
            
            // 3. Obtener datos del accidente antes de cualquier operación
            $accidentId = $mediaItem->model_id;
            $accident = DriverAccident::findOrFail($accidentId);
            $accidentData = $accident->getAttributes();
            
            // 4. SOLUCIÓN DEFINITIVA: Romper completamente el vínculo antes de eliminar
            // Esto evita que cualquier evento de Spatie pueda encontrar el modelo padre
            // IMPORTANTE: Esto es lo que previene la eliminación en cascada
            DB::table('media')
                ->where('id', $mediaItem->id)
                ->update([
                    'model_type' => 'App\\TempDeletedModel', 
                    'model_id' => 0
                ]);
            
            // 5. Ahora que hemos roto la conexión, eliminamos el archivo físico manualmente
            if (file_exists($filePath)) {
                @unlink($filePath);
                Log::info('Archivo físico eliminado: ' . $filePath);
            }
            
            // 6. Eliminar el registro de media de forma segura (ya no tiene vínculo con el accidente)
            DB::table('media')->where('id', $mediaItem->id)->delete();
            Log::info('Registro de media eliminado: ' . $media);
            
            // 7. VERIFICACIÓN CRÍTICA: Asegurar que el accidente sigue existiendo
            $accidentStillExists = DriverAccident::find($accidentId);
            
            if (!$accidentStillExists) {
                // Este caso no debería ocurrir, pero por si acaso, recreamos el accidente
                $newAccident = new DriverAccident();
                foreach ($accidentData as $key => $value) {
                    $newAccident->$key = $value;
                }
                $newAccident->save(['timestamps' => false]);
                
                Log::error('RECUPERACIÓN DE EMERGENCIA: Se recreó el accidente ' . $accidentId . ' que fue eliminado inesperadamente');
            }
            
            // 8. Confirmar transacción y redireccionar
            DB::commit();
            
            Log::info('Documento eliminado exitosamente sin afectar al accidente', [
                'media_id' => $media,
                'accident_id' => $accidentId,
                'accident_exists' => DriverAccident::find($accidentId) ? true : false
            ]);
            
            return redirect()->route('admin.accidents.edit', $accidentId)
                ->with('success', "Documento {$fileName} eliminado correctamente");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar documento', [
                'media_id' => $media,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Subir documentos para un accidente específico
     * 
     * @param DriverAccident $accident El accidente al que se subirán los documentos
     * @param Request $request Solicitud con los documentos a subir
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function storeDocuments(DriverAccident $accident, Request $request)
    {
        try {
            $request->validate([
                'documents' => 'required|array',
                'documents.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
            ]);
            
            $uploadedCount = 0;
            
            foreach ($request->file('documents') as $file) {
                // Obtener el ID del conductor asociado al accidente
                $driverId = $accident->userDriverDetail->id;
                
                // Configurar el disco y la ruta de almacenamiento correcta
                $media = $accident->addMedia($file)
                    ->usingName($file->getClientOriginalName())
                    ->usingFileName($file->getClientOriginalName())
                    ->withCustomProperties([
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'accident_id' => $accident->id,
                        'driver_id' => $driverId
                    ])
                    ->toMediaCollection('accident_documents');
                
                $uploadedCount++;
                
                Log::info('Documento de accidente subido correctamente', [
                    'accident_id' => $accident->id,
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection' => $media->collection_name,
                    'driver_id' => $driverId
                ]);
            }
            
            return redirect()->back()->with('success', "$uploadedCount documentos subidos correctamente");
            
        } catch (\Exception $e) {
            Log::error('Error al subir documentos de accidente', [
                'accident_id' => $accident->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al subir documentos: ' . $e->getMessage());
        }
    }
}
