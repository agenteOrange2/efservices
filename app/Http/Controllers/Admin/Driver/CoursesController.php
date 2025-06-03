<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverCourse;
use App\Models\Carrier;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CoursesController extends Controller
{
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
        
        // Cargar documentos existentes para mostrarlos
        $documents = DocumentAttachment::where('documentable_type', DriverCourse::class)
            ->where('documentable_id', $course->id)
            ->get();
        
        // Convertir los documentos a un formato que el componente FileUploader pueda entender
        $existingFilesArray = [];
        foreach ($documents as $document) {
            // Formato exactamente igual al que se usa en la vista de training
            $existingFilesArray[] = [
                'id' => $document->id,
                'name' => $document->file_name,
                'file_name' => $document->file_name,  // Campo adicional necesario
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
                'created_at' => $document->created_at->format('Y-m-d H:i:s'),
                'url' => Storage::url($document->file_path),  // URL para descargar/ver el archivo
                'is_temp' => false,  // Usar is_temp en lugar de is_existing
                'file_path' => $document->file_path
            ];
        }
        
        return view('admin.drivers.courses.edit', compact(
            'course',
            'drivers',
            'driver',
            'carriers',
            'existingFilesArray'
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
            'phone' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'certification_date' => 'nullable|date',
            'experience' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear registro de curso
            $course = DriverCourse::create([
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'organization_name' => $request->organization_name,
                'phone' => $request->phone,
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
                    'certificates'
                );
                
                Log::info('Archivos procesados para el curso', [
                    'course_id' => $course->id,
                    'files_processed' => $filesProcessed
                ]);
            }
            
            DB::commit();
            
            Session::flash('success', 'Curso creado correctamente');
            
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
        //dd($request->all());
        // Loguear los datos recibidos para depuración
        Log::info('Datos recibidos en CoursesController.update', [
            'certificate_files' => $request->certificate_files,
            'has_certificate_files' => $request->has('certificate_files'),
            'all_request' => $request->all()
        ]);
        
        $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'organization_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'certification_date' => 'nullable|date',
            'experience' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Actualizar los datos del curso
            $course->update($request->only([
                'user_driver_detail_id',
                'organization_name',
                'phone',
                'city',
                'state',
                'certification_date',
                'experience',
                'expiration_date',
                'status',
            ]));
            
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
                    $this->processLivewireFiles($course, $jsonFiles, 'certificates');
                }
            }
            
            // Procesar archivos desde el request si existen
            if ($request->filled('certificate_files')) {
                Log::info('Procesando archivos del request', [
                    'course_id' => $course->id,
                    'certificate_files' => $request->certificate_files
                ]);
                $this->processLivewireFiles($course, $request->certificate_files, 'certificates');
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
            Session::flash('success', 'Curso eliminado correctamente');
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
     * Elimina un documento mediante una solicitud AJAX usando el trait HasDocuments
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
            
            $documentId = $request->document_id;
            $courseId = $request->course_id;
            
            // Obtener el curso
            $course = DriverCourse::findOrFail($courseId);
            Log::info('Curso encontrado', ['course_id' => $course->id]);
            
            // Obtener el documento
            $document = DocumentAttachment::where('id', $documentId)
                ->first(); // Removido el filtro por documentable_type y documentable_id para diagnosticar
            
            if (!$document) {
                Log::warning('Documento no encontrado', ['document_id' => $documentId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }
            
            Log::info('Documento encontrado', [
                'document_id' => $document->id,
                'documentable_type' => $document->documentable_type,
                'documentable_id' => $document->documentable_id,
                'file_path' => $document->file_path
            ]);
            
            // Eliminar el documento usando el método del trait HasDocuments
            $result = $course->deleteDocument($documentId);
            
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

            // Verificar que el documento pertenece a un curso
            if ($document->documentable_type !== DriverCourse::class) {
                return redirect()->back()->with('error', 'Tipo de documento inválido');
            }

            $fileName = $document->file_name;
            $courseId = $document->documentable_id;
            $course = DriverCourse::find($courseId);

            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'No se encontró el curso asociado al documento');
            }

            // Eliminar el documento usando el método del trait HasDocuments
            $result = $course->deleteDocument($id);

            if (!$result) {
                return redirect()->back()->with('error', 'No se pudo eliminar el documento');
            }

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

    // Método para obtener los documentos de un curso
    public function getFiles(DriverCourse $course)
    {
        $certificates = $course->getMedia('certificates');
        
        return response()->json([
            'certificates' => $certificates,
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