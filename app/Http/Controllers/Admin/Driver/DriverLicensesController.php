<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverLicense;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverLicensesController extends Controller
{
    /**
     * Muestra la lista de licencias de conductores
     */
    public function index(Request $request)
    {
        try {
            $query = DriverLicense::with(['driverDetail.user', 'driverDetail.carrier']);
            
            // Aplicar filtros
            if ($request->filled('search_term')) {
                $searchTerm = '%' . $request->search_term . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('current_license_number', 'like', $searchTerm)
                      ->orWhere('license_class', 'like', $searchTerm)
                      ->orWhere('state_issued', 'like', $searchTerm);
                });
            }
            
            if ($request->filled('driver_filter')) {
                $query->where('user_driver_detail_id', $request->driver_filter);
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
            
            if (in_array($sortField, ['created_at', 'current_license_number', 'expiration_date'])) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $licenses = $query->paginate(15);
            
            // Obtener datos para filtros
            $drivers = UserDriverDetail::with('user')->get();
            
            // Obtener conteos de documentos para cada licencia
            $licenseIds = $licenses->pluck('id')->toArray();
            $documentCounts = [];
            
            if (!empty($licenseIds)) {
                $counts = Media::where('model_type', DriverLicense::class)
                    ->whereIn('model_id', $licenseIds)
                    ->select('model_id', DB::raw('count(*) as count'))
                    ->groupBy('model_id')
                    ->pluck('count', 'model_id')
                    ->toArray();
                    
                $documentCounts = $counts;
            }
            
            return view('admin.drivers.licenses.index', compact('licenses', 'drivers', 'documentCounts'));
        } catch (\Exception $e) {
            Log::error('Error loading licenses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Procesar imágenes de licencia
            if ($request->hasFile('license_front_image')) {
                // Eliminar imagen anterior si existe
                $license->clearMediaCollection('license_front');
                $license->addMediaFromRequest('license_front_image')
                    ->usingName('License Front Image')
                    ->toMediaCollection('license_front');
            }
            
            if ($request->hasFile('license_back_image')) {
                // Eliminar imagen anterior si existe
                $license->clearMediaCollection('license_back');
                $license->addMediaFromRequest('license_back_image')
                    ->usingName('License Back Image')
                    ->toMediaCollection('license_back');
            }
            return redirect()->back()->with('error', 'Error loading licenses: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para crear una nueva licencia
     */
    public function create()
    {
        $carriers = Carrier::where('status', 1)->orderBy('name')->get();
        $drivers = UserDriverDetail::with('user')->get();
        
        return view('admin.drivers.licenses.create', compact('carriers', 'drivers'));
    }

    /**
     * Almacena una nueva licencia en la base de datos
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'current_license_number' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'license_class' => 'required|string|max:255',
            'state_of_issue' => 'required|string|max:255',
            'expiration_date' => 'required|date|after:today',            
            'is_cdl' => 'nullable|boolean',
            'endorsement_n' => 'nullable|boolean',
            'endorsement_h' => 'nullable|boolean',
            'endorsement_x' => 'nullable|boolean',
            'endorsement_t' => 'nullable|boolean',
            'endorsement_p' => 'nullable|boolean',
            'endorsement_s' => 'nullable|boolean',
            'license_front_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'license_back_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();
            
            $license = DriverLicense::create([
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'current_license_number' => $request->current_license_number,
                'license_number' => $request->license_number,
                'license_class' => $request->license_class,
                'state_of_issue' => $request->state_of_issue,
                'expiration_date' => $request->expiration_date,                
                'is_cdl' => $request->boolean('is_cdl')
            ]);
            
            // Manejar endorsements a través de la relación many-to-many
            if ($request->boolean('is_cdl')) {
                $endorsementCodes = [];
                if ($request->boolean('endorsement_n')) $endorsementCodes[] = 'N';
                if ($request->boolean('endorsement_h')) $endorsementCodes[] = 'H';
                if ($request->boolean('endorsement_x')) $endorsementCodes[] = 'X';
                if ($request->boolean('endorsement_t')) $endorsementCodes[] = 'T';
                if ($request->boolean('endorsement_p')) $endorsementCodes[] = 'P';
                if ($request->boolean('endorsement_s')) $endorsementCodes[] = 'S';
                
                if (!empty($endorsementCodes)) {
                    $endorsementIds = \App\Models\Admin\Driver\LicenseEndorsement::whereIn('code', $endorsementCodes)->pluck('id');
                    $license->endorsements()->sync($endorsementIds);
                }
            }
            
            // Procesar imágenes de licencia
            if ($request->hasFile('license_front_image')) {
                $license->addMediaFromRequest('license_front_image')
                    ->usingName('License Front Image')
                    ->toMediaCollection('license_front');
            }
            
            if ($request->hasFile('license_back_image')) {
                $license->addMediaFromRequest('license_back_image')
                    ->usingName('License Back Image')
                    ->toMediaCollection('license_back');
            }
            
            // Procesar archivos subidos usando Spatie Media Library
            if ($request->has('uploaded_files') && !empty($request->uploaded_files)) {
                foreach ($request->uploaded_files as $fileData) {
                    if (isset($fileData['path']) && Storage::disk('temp')->exists($fileData['path'])) {
                        $tempPath = Storage::disk('temp')->path($fileData['path']);
                        
                        $license->addMedia($tempPath)
                            ->usingName($fileData['name'] ?? 'Document')
                            ->usingFileName($fileData['name'] ?? 'document.pdf')
                            ->toMediaCollection('licenses');
                        
                        // Limpiar archivo temporal
                        Storage::disk('temp')->delete($fileData['path']);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.licenses.index')
                ->with('success', 'License created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating license', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating license: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los detalles de una licencia específica
     */
    public function show(DriverLicense $license)
    {
        $license->load(['driverDetail.user', 'driverDetail.carrier']);
        
        // Obtener documentos asociados usando Spatie Media Library
        $documents = $license->getMedia('licenses');
        
        return view('admin.drivers.licenses.show', compact('license', 'documents'));
    }

    /**
     * Método temporal de depuración para verificar valores de endorsements
     */
    public function debugEndorsements(DriverLicense $license)
    {
        $license->load(['driverDetail.user', 'driverDetail.carrier']);
        
        $debugData = [
            'license_id' => $license->id,
            'current_license_number' => $license->current_license_number,
            'is_cdl' => $license->is_cdl,
            'is_cdl_raw' => $license->getRawOriginal('is_cdl'),
            'endorsements' => [
                'endorsement_n' => [
                    'value' => $license->endorsement_n,
                    'raw' => $license->getRawOriginal('endorsement_n'),
                    'type' => gettype($license->endorsement_n)
                ],
                'endorsement_h' => [
                    'value' => $license->endorsement_h,
                    'raw' => $license->getRawOriginal('endorsement_h'),
                    'type' => gettype($license->endorsement_h)
                ],
                'endorsement_x' => [
                    'value' => $license->endorsement_x,
                    'raw' => $license->getRawOriginal('endorsement_x'),
                    'type' => gettype($license->endorsement_x)
                ],
                'endorsement_t' => [
                    'value' => $license->endorsement_t,
                    'raw' => $license->getRawOriginal('endorsement_t'),
                    'type' => gettype($license->endorsement_t)
                ],
                'endorsement_p' => [
                    'value' => $license->endorsement_p,
                    'raw' => $license->getRawOriginal('endorsement_p'),
                    'type' => gettype($license->endorsement_p)
                ],
                'endorsement_s' => [
                    'value' => $license->endorsement_s,
                    'raw' => $license->getRawOriginal('endorsement_s'),
                    'type' => gettype($license->endorsement_s)
                ]
            ],
            'old_values_simulation' => [
                'is_cdl' => old('is_cdl', $license->is_cdl),
                'endorsement_n' => old('endorsement_n', $license->endorsement_n),
                'endorsement_h' => old('endorsement_h', $license->endorsement_h),
                'endorsement_x' => old('endorsement_x', $license->endorsement_x),
                'endorsement_t' => old('endorsement_t', $license->endorsement_t),
                'endorsement_p' => old('endorsement_p', $license->endorsement_p),
                'endorsement_s' => old('endorsement_s', $license->endorsement_s)
            ],
            'driver_info' => [
                'driver_name' => $license->driverDetail->user->name ?? 'N/A',
                'carrier_name' => $license->driverDetail->carrier->name ?? 'N/A'
            ]
        ];
        
        return response()->json($debugData, 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Muestra el formulario para editar una licencia
     */
    public function edit(DriverLicense $license)
    {        
        
        $license->load(['driverDetail.user', 'driverDetail.carrier']);
        
        $carriers = Carrier::where('status', 1)->orderBy('name')->get();
        $drivers = UserDriverDetail::with('user')->get();
        
        // Obtener documentos existentes y convertirlos al formato esperado por FileUploader
        $existingDocuments = $license->getMedia('licenses')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'size' => $media->size,
                'mime_type' => $media->mime_type,
                'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                'preview_url' => route('admin.licenses.preview-document', $media->id),
                'download_url' => route('admin.licenses.preview-document', [$media->id, 'download' => true]),
            ];
        })->toArray();
        
        return view('admin.drivers.licenses.edit', compact('license', 'carriers', 'drivers', 'existingDocuments'));
    }

    /**
     * Actualiza una licencia en la base de datos
     */
    public function update(Request $request, DriverLicense $license)
    {
        $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'current_license_number' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'license_class' => 'required|string|max:255',
            'state_of_issue' => 'required|string|max:255',
            'expiration_date' => 'required|date|after:today',            
            'is_cdl' => 'nullable|boolean',
            'endorsement_n' => 'nullable|boolean',
            'endorsement_h' => 'nullable|boolean',
            'endorsement_x' => 'nullable|boolean',
            'endorsement_t' => 'nullable|boolean',
            'endorsement_p' => 'nullable|boolean',
            'endorsement_s' => 'nullable|boolean',
            'license_front_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'license_back_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();
            
            $license->update([
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'current_license_number' => $request->current_license_number,
                'license_number' => $request->license_number,
                'license_class' => $request->license_class,
                'state_of_issue' => $request->state_of_issue,
                'expiration_date' => $request->expiration_date,                
                'is_cdl' => $request->boolean('is_cdl')
            ]);
            
            // Manejar endorsements a través de la relación many-to-many
            if ($request->boolean('is_cdl')) {
                $endorsementCodes = [];
                if ($request->boolean('endorsement_n')) $endorsementCodes[] = 'N';
                if ($request->boolean('endorsement_h')) $endorsementCodes[] = 'H';
                if ($request->boolean('endorsement_x')) $endorsementCodes[] = 'X';
                if ($request->boolean('endorsement_t')) $endorsementCodes[] = 'T';
                if ($request->boolean('endorsement_p')) $endorsementCodes[] = 'P';
                if ($request->boolean('endorsement_s')) $endorsementCodes[] = 'S';
                
                if (!empty($endorsementCodes)) {
                    $endorsementIds = \App\Models\Admin\Driver\LicenseEndorsement::whereIn('code', $endorsementCodes)->pluck('id');
                    $license->endorsements()->sync($endorsementIds);
                } else {
                    $license->endorsements()->detach();
                }
            } else {
                $license->endorsements()->detach();
            }
            
            // Procesar imágenes de licencia
            if ($request->hasFile('license_front_image')) {
                // Eliminar imagen anterior si existe
                $license->clearMediaCollection('license_front');
                $license->addMediaFromRequest('license_front_image')
                    ->usingName('License Front Image')
                    ->toMediaCollection('license_front');
            }
            
            if ($request->hasFile('license_back_image')) {
                // Eliminar imagen anterior si existe
                $license->clearMediaCollection('license_back');
                $license->addMediaFromRequest('license_back_image')
                    ->usingName('License Back Image')
                    ->toMediaCollection('license_back');
            }
            
            // Procesar nuevos archivos subidos usando Spatie Media Library
            if ($request->has('uploaded_files') && !empty($request->uploaded_files)) {
                foreach ($request->uploaded_files as $fileData) {
                    if (isset($fileData['path']) && Storage::disk('temp')->exists($fileData['path'])) {
                        $tempPath = Storage::disk('temp')->path($fileData['path']);
                        
                        $license->addMedia($tempPath)
                            ->usingName($fileData['name'] ?? 'Document')
                            ->usingFileName($fileData['name'] ?? 'document.pdf')
                            ->toMediaCollection('licenses');
                        
                        // Limpiar archivo temporal
                        Storage::disk('temp')->delete($fileData['path']);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.licenses.index')
                ->with('success', 'License updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating license', [
                'id' => $license->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating license: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una licencia de la base de datos
     */
    public function destroy(DriverLicense $license)
    {
        try {
            DB::beginTransaction();
            
            // Eliminar documentos asociados usando Spatie Media Library
            $license->clearMediaCollection('licenses');
            
            // Eliminar la licencia
            $license->delete();
            
            DB::commit();
            
            return redirect()->route('admin.licenses.index')
                ->with('success', 'License deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting license', [
                'id' => $license->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.licenses.index')
                ->with('error', 'Error deleting license: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los documentos de una licencia específica
     * Utilizando Spatie Media Library
     */
    public function showDocuments(DriverLicense $license)
    {
        $license->load('userDriverDetail.user');
        
        // Obtener documentos asociados usando Spatie Media Library
        $documents = Media::where('model_type', DriverLicense::class)
            ->where('model_id', $license->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Obtener todas las licencias y conductores para los filtros
        $licenses = DriverLicense::orderBy('current_license_number')->get();
        $drivers = UserDriverDetail::with('user')->get();
        
        $debugInfo = [
            'documents_count' => $documents->total(),
            'license_id' => $license->id
        ];
        
        return view('admin.drivers.licenses.documents', compact('license', 'licenses', 'drivers', 'documents', 'debugInfo'));
    }

    /**
     * Muestra todos los documentos de licencias en una vista resumida
     * Utilizando Spatie Media Library
     */
    public function documents(Request $request)
    {
        try {
            // Usar Spatie Media Library en lugar del antiguo sistema
            $query = Media::where('model_type', DriverLicense::class);
            
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
                // Obtener IDs de licencias asociadas a este conductor
                $licenseIds = DriverLicense::where('user_driver_detail_id', $driverId)
                    ->pluck('id')
                    ->toArray();
                    
                $query->whereIn('model_id', $licenseIds);
            }
            
            if ($request->filled('license_filter')) {
                $licenseId = $request->license_filter;
                $query->where('model_id', $licenseId);
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
            $licenses = DriverLicense::orderBy('current_license_number')->get();
            
            return view('admin.drivers.licenses.all_documents', compact('documents', 'drivers', 'licenses'));
        } catch (\Exception $e) {
            Log::error('Error loading license documents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.licenses.index')
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
            
            // Verificar que el documento pertenece a una licencia
            if ($media->model_type !== DriverLicense::class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type'
                ], 400);
            }
            
            $fileName = $media->file_name;
            $licenseId = $media->model_id;
            $license = DriverLicense::find($licenseId);
            
            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'License not found'
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

            // Verificar que el documento pertenece a una licencia
            if ($media->model_type !== DriverLicense::class) {
                return redirect()->back()->with('error', 'Invalid document type');
            }

            $fileName = $media->file_name;
            $licenseId = $media->model_id;
            $license = DriverLicense::find($licenseId);

            if (!$license) {
                return redirect()->route('admin.licenses.index')
                    ->with('error', 'No se encontró la licencia asociada al documento');
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
                return redirect()->route('admin.licenses.show.documents', $licenseId)
                    ->with('success', "Documento '{$fileName}' eliminado correctamente");
            }
            
            // Si no, redirigir a la página de edición
            return redirect()->route('admin.licenses.edit', $licenseId)
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
     * Previsualiza o descarga un documento adjunto a una licencia
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

            // Verificar que el documento pertenece a una licencia
            if ($media->model_type !== DriverLicense::class) {
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
}