<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverCourse;
use App\Models\Carrier;
use App\Models\DocumentAttachment;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CoursesController extends Controller
{
    // Los métodos destroyDocument y previewDocument se han movido más abajo en el controlador
    // Vista para todos los cursos
    public function index(Request $request)
    {
        // Log para depuración - guardar todos los parámetros recibidos
        \Illuminate\Support\Facades\Log::info('Parámetros de filtro recibidos:', [
            'all_parameters' => $request->all(),
            'driver_filter' => $request->driver_filter,
            'carrier_filter' => $request->carrier_filter,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'status' => $request->status,
            'sort_field' => $request->sort_field,
            'sort_direction' => $request->sort_direction,
        ]);
        
        $query = DriverCourse::query()
            ->with(['driverDetail.user', 'driverDetail.carrier']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            // Usar where con paréntesis para agrupar las condiciones OR
            $query->where(function ($q) use ($request) {
                $searchTerm = '%' . $request->search_term . '%';
                $q->where('organization_name', 'like', $searchTerm)
                  ->orWhere('experience', 'like', $searchTerm)
                  ->orWhere('city', 'like', $searchTerm)
                  ->orWhere('state', 'like', $searchTerm);
            });
        }

        if ($request->filled('driver_filter') && $request->driver_filter != '') {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('carrier_filter') && $request->carrier_filter != '') {
            $query->whereHas('driverDetail', function ($subq) use ($request) {
                $subq->where('carrier_id', $request->carrier_filter);
            });
        }

        if ($request->filled('date_from') && $request->date_from != '') {
            $query->whereDate('certification_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to') && $request->date_to != '') {
            $query->whereDate('certification_date', '<=', $request->date_to);
        }

        if ($request->filled('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'certification_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $courses = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        // Obtener valores únicos para los filtros de desplegable
        $statuses = DriverCourse::distinct()->pluck('status')->filter()->toArray();

        return view('admin.drivers.courses.index', compact(
            'courses',
            'drivers',
            'carriers',
            'statuses'
        ));
    }

    // Vista para el historial de cursos de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverCourse::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('organization_name', 'like', '%' . $request->search_term . '%')
                ->orWhere('experience', 'like', '%' . $request->search_term . '%')
                ->orWhere('city', 'like', '%' . $request->search_term . '%')
                ->orWhere('state', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'certification_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $courses = $query->paginate(10);

        // Obtener valores únicos para los filtros de desplegable
        $statuses = DriverCourse::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('status')->filter()->toArray();

        return view('admin.drivers.courses.driver_history', compact(
            'driver',
            'courses',
            'statuses'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo curso
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();
        $driverId = request()->query('driver_id');
        
        return view('admin.drivers.courses.create', compact('drivers', 'carriers', 'driverId'));
    }

    /**
     * Muestra el formulario para editar un curso existente
     * 
     * @param \App\Models\Admin\Driver\DriverCourse $course
     * @return \Illuminate\View\View
     */
    public function edit(DriverCourse $course)
    {
        $drivers = UserDriverDetail::with('user')->get();
        $driver = $course->driverDetail;
        $carriers = Carrier::where('status', 1)->get();
        
        // Los documentos ya se cargan directamente en la vista usando $course->getMedia('course_certificates')
        // No es necesario cargarlos aquí ya que la vista ya está configurada para usar Spatie Media Library
        
        return view('admin.drivers.courses.edit', compact(
            'course',
            'drivers',
            'driver',
            'carriers'
        ));
    }

    // Método para almacenar un nuevo curso
    public function store(Request $request)
    {
        // Loguear los datos recibidos para depuración
        Log::info('Datos recibidos en CoursesController.store', [
            'certificate_files' => $request->certificate_files,
            'has_certificate_files' => $request->has('certificate_files'),
            'all_request' => $request->all()
        ]);
        $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'organization_name' => 'required|string|max:255',
            'organization_name_other' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'certification_date' => 'nullable|date',
            'experience' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Determinar el valor correcto para organization_name
            $organizationName = $request->organization_name;
            if ($organizationName === 'Other' && $request->filled('organization_name_other')) {
                $organizationName = $request->organization_name_other;
            }
            
            // Crear registro de curso
            $course = DriverCourse::create([
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'organization_name' => $organizationName,                
                'city' => $request->city,
                'state' => $request->state,
                'certification_date' => $request->certification_date,
                'experience' => $request->experience,
                'expiration_date' => $request->expiration_date,
                'status' => $request->status ?? 'Active',
            ]);
            
            // Procesar archivos de certificados si existen usando el método optimizado
            if ($request->filled('certificate_files')) {
                // Usar el método processLivewireFiles que ya maneja toda la lógica
                $filesProcessed = $this->processLivewireFiles(
                    $course, 
                    $request->certificate_files, 
                    'course_certificates'
                );
                
                Log::info('Archivos procesados para el curso', [
                    'course_id' => $course->id,
                    'files_processed' => $filesProcessed
                ]);
            }
            
            DB::commit();
            
            session()->flash('success', 'Curso creado correctamente');
            
            // Redirigir a la vista de edición
            return redirect()->route('admin.courses.edit', $course->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear curso', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->withInput()->withErrors(['general' => 'Error al crear el curso: ' . $e->getMessage()]);
        }
    }

    // Método para actualizar un curso existente
    public function update(DriverCourse $course, Request $request)
    {
        // Loguear los datos recibidos para depuración
        Log::info('Datos recibidos en CoursesController.update', [
            'certificate_files' => $request->certificate_files,
            'has_certificate_files' => $request->has('certificate_files'),
            'all_request' => $request->all()
        ]);
        
        $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'organization_name' => 'required|string|max:255',
            'organization_name_other' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'certification_date' => 'nullable|date',
            'experience' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Determinar el valor correcto para organization_name
            $data = $request->only([
                'user_driver_detail_id',
                'city',
                'state',
                'certification_date',
                'experience',
                'expiration_date',
                'status',
            ]);
            
            if ($request->organization_name === 'Other' && $request->filled('organization_name_other')) {
                $data['organization_name'] = $request->organization_name_other;
            } else {
                $data['organization_name'] = $request->organization_name;
            }
            
            // Actualizar los datos del curso
            $course->update($data);
            
            // Buscar archivos temporales que pudieran haber sido subidos por Livewire
            // Usamos Storage directamente ya que los archivos no llegan en el request
            $tempDir = 'temp';
            $tempFiles = Storage::files($tempDir);
            
            Log::info('Buscando archivos temporales para procesar', [
                'course_id' => $course->id,
                'temp_files_count' => count($tempFiles),
                'temp_files' => $tempFiles
            ]);
            
            if (count($tempFiles) > 0) {
                // Preparar formato JSON para procesar con nuestro método existente
                $filesData = [];
                foreach ($tempFiles as $tempFile) {
                    // Solo procesamos los archivos subidos en las últimas 24 horas
                    $lastModified = Storage::lastModified($tempFile);
                    $isRecent = (time() - $lastModified) < (24 * 60 * 60); // 24 horas
                    
                    if ($isRecent) {
                        $fileName = basename($tempFile);
                        $mimeType = Storage::mimeType($tempFile);
                        $fileSize = Storage::size($tempFile);
                        
                        $filesData[] = [
                            'name' => $fileName,
                            'original_name' => $fileName,
                            'mime_type' => $mimeType,
                            'size' => $fileSize,
                            'path' => $tempFile,
                            'is_temp' => true
                        ];
                    }
                }
                
                if (!empty($filesData)) {
                    $jsonFiles = json_encode($filesData);
                    Log::info('Procesando archivos temporales encontrados', [
                        'course_id' => $course->id,
                        'files_count' => count($filesData)
                    ]);
                    $this->processLivewireFiles($course, $jsonFiles, 'course_certificates');
                }
            }
            
            // Procesar archivos desde el request si existen
            if ($request->filled('certificate_files')) {
                Log::info('Procesando archivos del request', [
                    'course_id' => $course->id,
                    'certificate_files' => $request->certificate_files
                ]);
                $this->processLivewireFiles($course, $request->certificate_files, 'course_certificates');
            }
            
            DB::commit();
            
            session()->flash('success', 'Curso actualizado exitosamente');
            return redirect()->route('admin.courses.edit', $course->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar curso', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->withInput()->withErrors(['general' => 'Error al actualizar el curso: ' . $e->getMessage()]);
        }
    }

    // Método para eliminar un curso
    public function destroy(DriverCourse $course)
    {
        try {
            $course->delete();
            session()->flash('success', 'Curso eliminado correctamente');
            return back();
        } catch (\Exception $e) {
            Log::error('Error al eliminar curso', [
                'error' => $e->getMessage(),
                'course_id' => $course->id
            ]);
            
            return back()->withErrors(['general' => 'Error al eliminar el curso: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina un documento mediante una solicitud AJAX usando Spatie Media Library
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyDocument(Request $request)
    {
        try {
            // Logs para depuración
            Log::info('Solicitud de eliminación de documento recibida', [
                'request_all' => $request->all(),
                'document_id' => $request->input('document_id'),
                'course_id' => $request->input('course_id')
            ]);
            
            // Verificar parámetros
            if (!$request->has('document_id') || !$request->has('course_id')) {
                Log::warning('Parámetros incorrectos al eliminar documento', [
                    'params' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros incorrectos: se requiere document_id y course_id'
                ], 400);
            }
            
            $mediaId = $request->document_id;
            $courseId = $request->course_id;
            
            // Obtener el curso
            $course = DriverCourse::findOrFail($courseId);
            Log::info('Curso encontrado', ['course_id' => $course->id]);
            
            // Obtener el documento desde la tabla media
            $media = Media::where('id', $mediaId)
                ->where('model_type', DriverCourse::class)
                ->where('model_id', $courseId)
                ->first();
            
            if (!$media) {
                Log::warning('Documento no encontrado', ['media_id' => $mediaId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }
            
            Log::info('Documento encontrado', [
                'media_id' => $media->id,
                'model_type' => $media->model_type,
                'model_id' => $media->model_id,
                'file_name' => $media->file_name,
                'disk' => $media->disk
            ]);
            
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
            $result = DB::table('media')->where('id', $mediaId)->delete();
            
            Log::info('Resultado de eliminación', ['success' => $result ? 'true' : 'false']);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar el documento'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento de curso', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Previsualiza un documento usando Spatie Media Library
     * 
     * @param int $id ID del documento a previsualizar
     * @return \Illuminate\Http\Response
     */
    public function previewDocument($id)
    {
        try {
            // Buscar el documento en la tabla media
            $media = Media::findOrFail($id);
            
            // Verificar que el documento pertenece a un curso
            if ($media->model_type !== DriverCourse::class) {
                abort(404, 'Tipo de documento inválido');
            }
            
            // Obtener la ruta del archivo
            $filePath = $media->getPath();
            
            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                abort(404, 'Archivo no encontrado');
            }
            
            // Obtener el tipo MIME del archivo
            $mimeType = $media->mime_type;
            
            // Devolver el archivo como respuesta
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error al previsualizar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina un documento usando Spatie Media Library
     * 
     * @param int $id ID del documento a eliminar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($id)
    {
        try {
            // Verificar que el documento existe en la tabla media
            $media = Media::findOrFail($id);
            
            // Verificar que el documento pertenece a un curso
            if ($media->model_type !== DriverCourse::class) {
                return redirect()->back()->with('error', 'Tipo de documento inválido');
            }

            $fileName = $media->file_name;
            $courseId = $media->model_id;
            $course = DriverCourse::find($courseId);

            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'No se encontró el curso asociado al documento');
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
            
            // Si la URL contiene 'files', redirigir a la página de archivos
            if (strpos($referer, 'files') !== false) {
                return redirect()->route('admin.courses.files', $courseId)
                    ->with('success', "Documento '{$fileName}' eliminado correctamente");
            }
            
            // Si no, redirigir a la página de edición
            return redirect()->route('admin.courses.edit', $courseId)
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
    
    /**
     * Muestra los documentos de un curso específico
     *
     * @param DriverCourse $course
     * @return \Illuminate\View\View
     */
    public function getFiles(DriverCourse $course)
    {
        $documents = $course->getMedia('course_certificates');
        
        // Asegurarse de que los documentos se cargan correctamente
        \Illuminate\Support\Facades\Log::info('Documentos cargados para el curso: ' . $course->id, [
            'count' => $documents->count(),
            'course_name' => $course->organization_name,
            'url' => request()->url(),
            'route' => request()->route()->getName()
        ]);
        
        return view('admin.drivers.courses.documents', [
            'course' => $course,
            'documents' => $documents,
        ]);
    }
    
    /**
     * Muestra todos los documentos de todos los cursos
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getAllDocuments(Request $request)
    {
        // Iniciar la consulta para obtener todos los documentos de la colección 'course_certificates'
        $query = DB::table('media')
            ->where('collection_name', 'course_certificates')
            ->where('model_type', DriverCourse::class);
            
        // Log para depuración
        \Illuminate\Support\Facades\Log::info('Iniciando consulta de todos los documentos', [
            'request' => $request->all()
        ]);
        
        // Filtrar por curso si se especifica
        if ($request->filled('course')) {
            $query->where('model_id', $request->course);
        }
        
        // Filtrar por conductor si se especifica
        if ($request->filled('driver')) {
            $driverId = $request->driver;
            $courseIds = DriverCourse::where('user_driver_detail_id', $driverId)->pluck('id');
            $query->whereIn('model_id', $courseIds);
        }
        
        // Filtrar por tipo de archivo
        if ($request->filled('file_type')) {
            $fileType = $request->file_type;
            if ($fileType === 'pdf') {
                $query->where('mime_type', 'application/pdf');
            } elseif ($fileType === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($fileType === 'doc') {
                $query->where(function($q) {
                    $q->where('mime_type', 'like', 'application/msword%')
                      ->orWhere('mime_type', 'like', 'application/vnd.openxmlformats-officedocument.wordprocessingml%');
                });
            }
        }
        
        // Filtrar por fecha de subida
        if ($request->filled('upload_from')) {
            $query->whereDate('created_at', '>=', $request->upload_from);
        }
        
        if ($request->filled('upload_to')) {
            $query->whereDate('created_at', '<=', $request->upload_to);
        }
        
        // Ordenar por fecha de creación descendente (más reciente primero)
        $query->orderBy('created_at', 'desc');
        
        // Paginar los resultados
        $mediaItems = $query->paginate(15);
        
        // Convertir los resultados de DB a objetos Media
        $documents = collect($mediaItems->items())->map(function ($item) {
            $media = Media::find($item->id);
            return $media;
        });
        
        // Mantener la paginación
        $documents = new \Illuminate\Pagination\LengthAwarePaginator(
            $documents,
            $mediaItems->total(),
            $mediaItems->perPage(),
            $mediaItems->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        // Obtener todos los cursos y conductores para los filtros
        $courses = DriverCourse::orderBy('organization_name')->get();
        $drivers = UserDriverDetail::with('user')->get();
        
        // Log para depuración
        \Illuminate\Support\Facades\Log::info('Renderizando vista all_documents', [
            'document_count' => $documents->count(),
            'course_count' => $courses->count(),
            'driver_count' => $drivers->count()
        ]);
        
        return view('admin.drivers.courses.all_documents', [
            'documents' => $documents,
            'courses' => $courses,
            'drivers' => $drivers,
        ]);
    }

    public function getDriversByCarrier($carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier)
            ->whereHas('user', function ($query) {
                $query->where('status', 1);
            })
            ->with('user')
            ->get();

        return response()->json([
            'drivers' => $drivers
        ]);
    }
    
    /**
     * Método privado para procesar archivos subidos vía Livewire
     * 
     * @param DriverCourse $course Curso al que asociar los archivos
     * @param string $filesJson Datos de los archivos en formato JSON
     * @param string $collection Nombre de la colección donde guardar los archivos
     * @return int Número de archivos procesados correctamente
     */
    private function processLivewireFiles(DriverCourse $course, $filesJson, $collection)
    {
        $uploadedCount = 0;
        
        try {
            // Si no hay datos de archivos, salir
            if (empty($filesJson)) {
                return 0;
            }
            
            $filesArray = json_decode($filesJson, true);
            Log::info('Procesando archivos para media', ['files' => $filesArray]);
            
            if (is_array($filesArray)) {
                foreach ($filesArray as $file) {
                    // Verificar si es un archivo existente (ya procesado anteriormente)
                    if (isset($file['is_temp']) && $file['is_temp'] === false) {
                        Log::info('Archivo ya procesado, no requiere acción', ['file' => $file]);
                        continue;
                    }
                    
                    // Verificar si tenemos la ruta del archivo
                    $filePath = null;
                    if (!empty($file['path'])) {
                        $filePath = $file['path'];
                    } elseif (!empty($file['tempPath'])) {
                        $filePath = $file['tempPath'];
                    } else {
                        Log::warning('Archivo sin ruta temporal', ['file' => $file]);
                        continue;
                    }
                    
                    // Verificar si el archivo existe físicamente
                    $fullPath = storage_path('app/' . $filePath);
                    if (!file_exists($fullPath)) {
                        // Intentar buscar en la carpeta temp directamente
                        $filePath = 'temp/' . basename($filePath);
                        $fullPath = storage_path('app/' . $filePath);
                        
                        if (!file_exists($fullPath)) {
                            Log::error('Archivo no encontrado', [
                                'path' => $filePath,
                                'full_path' => $fullPath,
                                'course_id' => $course->id
                            ]);
                            continue;
                        }
                    }
                    
                    // Obtener el nombre del archivo y otros metadatos
                    $fileName = $file['name'] ?? $file['original_name'] ?? basename($fullPath);
                    $mimeType = $file['mime_type'] ?? mime_content_type($fullPath);
                    $fileSize = $file['size'] ?? filesize($fullPath);
                    
                    try {
                        // Usar DIRECTAMENTE el sistema de medios de Spatie (no HasDocuments)
                        $media = $course->addMedia($fullPath)
                            ->usingName($fileName)
                            ->withCustomProperties([
                                'driver_id' => $course->user_driver_detail_id,
                                'course_id' => $course->id,
                                'document_type' => 'course_certificate'
                            ])
                            ->toMediaCollection($collection);
                        
                        $uploadedCount++;
                        
                        Log::info('Documento guardado correctamente en media', [
                            'course_id' => $course->id,
                            'file_name' => $fileName,
                            'collection' => $collection,
                            'media_id' => $media->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al guardar el archivo en media', [
                            'error' => $e->getMessage(),
                            'file' => $fileName,
                            'course_id' => $course->id
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar documentos de curso', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'course_id' => $course->id,
                'collection' => $collection
            ]);
        }
        
        return $uploadedCount;
    }
}