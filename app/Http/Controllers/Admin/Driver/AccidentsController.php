<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
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
            $validated['number_of_injuries'] = null;
        }
        if (!$validated['had_fatalities']) {
            $validated['number_of_fatalities'] = null;
        }

        // Crear el registro de accidente
        $accident = DriverAccident::create($validated);

        // Procesar los archivos subidos
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                try {
                    // Subir el archivo al accidente
                    $media = $accident->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('accident_documents');
                    
                    Log::info('Archivo de accidente subido correctamente', [
                        'accident_id' => $accident->id,
                        'media_id' => $media->id,
                        'file_name' => $file->getClientOriginalName(),
                        'collection' => 'accident_documents'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al subir archivo de accidente: ' . $e->getMessage(), [
                        'accident_id' => $accident->id,
                        'file_name' => $file->getClientOriginalName()
                    ]);
                }
            }
        }

        Session::flash('success', 'Accident record added successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.accident-history', $validated['user_driver_detail_id']);
        }

        return redirect()->route('admin.accidents.index');
    }

    // Método para actualizar un accidente existente
    public function update(DriverAccident $accident, Request $request)
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
            $validated['number_of_injuries'] = null;
        }
        if (!$validated['had_fatalities']) {
            $validated['number_of_fatalities'] = null;
        }

        $accident->update($validated);
        
        // Procesar los archivos subidos
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                try {
                    // Subir el archivo al accidente
                    $media = $accident->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('accident_documents');
                    
                    Log::info('Archivo de accidente actualizado correctamente', [
                        'accident_id' => $accident->id,
                        'media_id' => $media->id,
                        'file_name' => $file->getClientOriginalName(),
                        'collection' => 'accident_documents'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al subir archivo de accidente: ' . $e->getMessage(), [
                        'accident_id' => $accident->id,
                        'file_name' => $file->getClientOriginalName()
                    ]);
                }
            }
        }

        Session::flash('success', 'Accident record updated successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.accident-history', $accident->user_driver_detail_id);
        }

        return redirect()->route('admin.accidents.index');
    }

    // Método para eliminar un accidente
    public function destroy(DriverAccident $accident)
    {
        $driverId = $accident->user_driver_detail_id;
        $accident->delete();

        Session::flash('success', 'Accident record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'accident-history') !== false) {
            return redirect()->route('admin.drivers.accident-history', $driverId);
        }

        return redirect()->route('admin.accidents.index');
    }

    /**
     * Obtiene los conductores activos asociados a un carrier específico.
     * 
     * @param int $carrier El ID del carrier cuyos conductores se desean obtener
     * @return \Illuminate\Http\JsonResponse Lista de conductores activos en formato JSON
     */
    public function getDriversByCarrier($carrier)
    {
        // Encontrar el carrier por ID
        $carrierModel = Carrier::find($carrier);
        
        if (!$carrierModel) {
            return response()->json([
                'drivers' => [],
                'count' => 0,
                'error' => 'Carrier no encontrado'
            ], 404);
        }
        
        // Filtramos por carrier_id y solo conductores con usuarios activos
        $drivers = UserDriverDetail::where('carrier_id', $carrierModel->id)
            ->whereHas('user', function($query) {
                $query->where('status', 1); // Solo usuarios activos
            })
            ->with(['user' => function($query) {
                $query->select('id', 'name', 'email', 'status');
            }])
            ->get()
            ->map(function($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->user->name . ' ' . $driver->last_name,
                    'email' => $driver->user->email,
                    'status' => $driver->status
                ];
            });

        return response()->json([
            'drivers' => $drivers,
            'count' => $drivers->count()
        ]);
    }
    
    /**
     * Muestra la vista de documentos para un accidente específico.
     * 
     * @param DriverAccident $accident El accidente del que se mostrarán los documentos
     * @return \Illuminate\View\View Vista con los documentos del accidente
     */
    public function showDocuments(DriverAccident $accident)
    {
        // Obtener todos los documentos asociados con este accidente
        $accidentDocuments = $accident->getMedia('accident_documents');
        
        // Obtener el conductor relacionado con el accidente
        $driver = $accident->userDriverDetail;
        
        // Obtener documentos relacionados con el conductor
        $driverDocuments = collect([]);
        $driverRegistrationDocuments = collect([]);
        $allDriverMedia = collect([]);
        
        if ($driver) {
            // Intentar obtener todos los documentos asociados al conductor sin importar la colección
            $driverMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', get_class($driver))
                ->where('model_id', $driver->id)
                ->get();
                
            \Illuminate\Support\Facades\Log::info('Documentos del conductor encontrados:', [
                'driver_id' => $driver->id,
                'count' => $driverMedia->count(),
                'collections' => $driverMedia->pluck('collection_name')->unique()->toArray()
            ]);
            
            $allDriverMedia = $allDriverMedia->merge($driverMedia);
            
            // Documentos asociados directamente al conductor en colecciones específicas
            $driverDocuments = $driver->getMedia('driver_documents');
            if ($driverDocuments->isEmpty()) {
                // Intentar otras colecciones comunes
                $driverDocuments = $driver->getMedia('documents');
            }
            
            // Si hay un usuario asociado, verificar todos sus documentos
            if ($driver->user) {
                $userMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', get_class($driver->user))
                    ->where('model_id', $driver->user->id)
                    ->get();
                    
                \Illuminate\Support\Facades\Log::info('Documentos del usuario encontrados:', [
                    'user_id' => $driver->user->id,
                    'count' => $userMedia->count(),
                    'collections' => $userMedia->pluck('collection_name')->unique()->toArray()
                ]);
                
                $allDriverMedia = $allDriverMedia->merge($userMedia);
                
                // Intentar colecciones específicas del usuario
                $driverRegistrationDocuments = $driver->user->getMedia('registration_documents');
                if ($driverRegistrationDocuments->isEmpty()) {
                    // Intentar otras colecciones comunes
                    $driverRegistrationDocuments = $driver->user->getMedia('documents');
                    
                    if ($driverRegistrationDocuments->isEmpty()) {
                        $driverRegistrationDocuments = $driver->user->getMedia('user_documents');
                    }
                }
            }
        }
        
        // Combinar todos los documentos
        $allDocuments = $accidentDocuments->merge($driverDocuments)->merge($driverRegistrationDocuments)->merge($allDriverMedia);
        
        // Eliminar duplicados (si un documento aparece en varias colecciones)
        $allDocuments = $allDocuments->unique('id');
        
        // Agrupar documentos por tipo para mejor organización
        $groupedDocuments = [
            'images' => [],
            'pdfs' => [],
            'documents' => []
        ];
        
        foreach ($allDocuments as $document) {
            $mime = $document->mime_type;
            
            if (strpos($mime, 'image/') === 0) {
                $groupedDocuments['images'][] = $document;
            } elseif ($mime === 'application/pdf') {
                $groupedDocuments['pdfs'][] = $document;
            } else {
                $groupedDocuments['documents'][] = $document;
            }
        }
        
        // Obtener colección para cada documento para determinar su origen
        $documentSources = [];
        foreach ($allDocuments as $document) {
            switch ($document->collection_name) {
                case 'accident_documents':
                    $documentSources[$document->id] = 'Documento de accidente';
                    break;
                case 'driver_documents':
                    $documentSources[$document->id] = 'Documento del conductor';
                    break;
                case 'registration_documents':
                    $documentSources[$document->id] = 'Documento de registro';
                    break;
                default:
                    $documentSources[$document->id] = 'Otro documento';
            }
        }
        
        return view('admin.drivers.accidents.documents', [
            'accident' => $accident,
            'documents' => $allDocuments,
            'accidentDocuments' => $accidentDocuments,
            'driverDocuments' => $driverDocuments,
            'driverRegistrationDocuments' => $driverRegistrationDocuments,
            'groupedDocuments' => $groupedDocuments,
            'documentSources' => $documentSources,
            'totalDocuments' => $allDocuments->count(),
            'driver' => $driver
        ]);
    }
    
    /**
     * Previsualiza un documento relacionado con accidentes o conductores.
     * 
     * @param int $documentId ID del documento a previsualizar
     * @return \Illuminate\Http\Response Respuesta con la previsualización o descarga
     */
    public function previewDocument($documentId)
    {
        try {
            // Encontrar el documento por ID
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($documentId);
            
            // Registramos el acceso para análisis y depuración
            \Illuminate\Support\Facades\Log::info('Acceso a documento', [
                'media_id' => $documentId,
                'collection' => $media->collection_name,
                'file_name' => $media->file_name,
                'model_type' => $media->model_type,
                'model_id' => $media->model_id,
                'user_id' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : 'guest',
                'ip' => request()->ip()
            ]);
            
            // Lista negra de colecciones que no deberían ser accesibles (opcional)
            $blockedCollections = [
                'private_documents',      // Documentos privados/confidenciales
                'system_backups',        // Copias de seguridad del sistema
                'admin_files'            // Archivos de administración restringidos
            ];
            
            if (in_array($media->collection_name, $blockedCollections)) {
                // Registrar intento de acceso no autorizado
                \Illuminate\Support\Facades\Log::warning('Intento de acceso a documento no permitido', [
                    'media_id' => $documentId,
                    'collection' => $media->collection_name,
                    'user_id' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : 'guest',
                    'ip' => request()->ip()
                ]);
                
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
            \Illuminate\Support\Facades\Log::error('Error al previsualizar documento', [
                'media_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(404, 'El documento solicitado no se pudo encontrar: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina un documento (media) asociado a un accidente.
     *
     * @param int $mediaId ID del documento a eliminar
     * @return \Illuminate\Http\RedirectResponse Redirección a la página anterior con mensaje de éxito o error
     */
    public function destroyDocument($mediaId)
    {
        try {
            // Encontrar el documento por ID
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
            
            // Verificar que el documento pertenece a una colección de accidentes
            if ($media->collection_name !== 'accident_documents') {
                return back()->with('error', 'El documento solicitado no es un documento de accidente');
            }
            
            // Guardar referencia al accidente antes de eliminar el documento
            $accident = DriverAccident::find($media->model_id);
            
            // Eliminar el documento
            $fileName = $media->file_name; // Guardar nombre para el mensaje
            $media->delete();
            
            // Registrar en log
            \Illuminate\Support\Facades\Log::info('Documento de accidente eliminado', [
                'media_id' => $mediaId,
                'file_name' => $fileName,
                'accident_id' => $accident ? $accident->id : null,
                'user_id' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null
            ]);
            
            return back()->with('success', "El documento '$fileName' ha sido eliminado exitosamente");
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al eliminar documento de accidente', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
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
                // Subir documento usando la colección 'accident_documents' para consistencia
                $media = $accident->addMedia($file)
                    ->usingName($file->getClientOriginalName())
                    ->usingFileName($file->getClientOriginalName())
                    ->withCustomProperties([
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'accident_id' => $accident->id
                    ])
                    ->toMediaCollection('accident_documents');
                
                $uploadedCount++;
                
                Log::info('Documento de accidente subido correctamente', [
                    'accident_id' => $accident->id,
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection' => $media->collection_name,
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
    
    /**
     * Eliminar un documento de un accidente
     */
    public function deleteDocument($documentId)
    {
        try {
            $media = Media::findOrFail($documentId);
            $accidentId = $media->model_id;
            
            // Verificar que el documento pertenece a un accidente
            if ($media->model_type !== DriverAccident::class) {
                return redirect()->back()->with('error', 'Invalid document type');
            }
            
            // Eliminar el documento
            $media->delete();
            
            return redirect()->back()->with('success', 'Document deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting document');
        }
    }
}
