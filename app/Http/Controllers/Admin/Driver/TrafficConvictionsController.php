<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTrafficConviction;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrafficConvictionsController extends Controller
{
    // Vista para todas las infracciones de tráfico
    public function index(Request $request)
    {
        $query = DriverTrafficConviction::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where(function ($q) use ($request) {
                $q->where('charge', 'like', '%' . $request->search_term . '%')
                    ->orWhere('location', 'like', '%' . $request->search_term . '%')
                    ->orWhere('penalty', 'like', '%' . $request->search_term . '%');
            });
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
            $query->whereDate('conviction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('conviction_date', '<=', $request->date_to);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'conviction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $convictions = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.traffic.index', compact('convictions', 'drivers', 'carriers'));
    }

    // Vista para el historial de infracciones de tráfico de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverTrafficConviction::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('charge', 'like', '%' . $request->search_term . '%')
                ->orWhere('location', 'like', '%' . $request->search_term . '%')
                ->orWhere('penalty', 'like', '%' . $request->search_term . '%');
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'conviction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $convictions = $query->paginate(10);

        return view('admin.drivers.traffic.driver_history', compact('driver', 'convictions'));
    }

    /**
     * Mostrar el formulario para crear una nueva infracción de tráfico
     */
    public function create()
    {
        // Inicialmente no cargamos conductores, se cargarán vía AJAX cuando se seleccione un carrier
        $drivers = collect(); // Colección vacía
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.traffic.create', compact('drivers', 'carriers'));
    }

    /**
     * Método para almacenar una nueva infracción de tráfico
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'conviction_date' => 'required|date',
                'location' => 'required|string|max:255',
                'charge' => 'required|string|max:255',
                'penalty' => 'required|string|max:255',
            ]);

            $conviction = new DriverTrafficConviction($validated);
            $conviction->save();

            // Procesar archivos si se han subido
            $files = json_decode($request->input('traffic_files'), true) ?? [];
            foreach ($files as $file) {
                if (isset($file['path'])) {
                    $conviction->addMediaFromDisk($file['path'], 'livewire-tmp')
                        ->usingName($file['original_name'])
                        ->withCustomProperties([
                            'original_name' => $file['original_name'],
                            'mime_type' => $file['mime_type'],
                            'size' => $file['size']
                        ])
                        ->preservingOriginal()
                        ->toMediaCollection('traffic-tickets', 'public');
                }
            }

            DB::commit();
            
            Log::info('Traffic conviction created successfully', [
                'conviction_id' => $conviction->id,
                'driver_id' => $conviction->user_driver_detail_id
            ]);

            return redirect()
                ->route('admin.traffic.index')
                ->with('success', 'Traffic conviction created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating traffic conviction: ' . $e->getMessage(), [
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error creating traffic conviction: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar el formulario para editar una infracción de tráfico
     */
    public function edit(DriverTrafficConviction $conviction)
    {
        // Obtenemos el carrier del conductor asociado a la infracción
        $carrierId = $conviction->userDriverDetail->carrier_id;

        // Obtenemos todos los conductores activos del carrier seleccionado
        $drivers = UserDriverDetail::where('carrier_id', $carrierId)
            ->where('status', UserDriverDetail::STATUS_ACTIVE)
            ->with('user')
            ->get();

        // Si el conductor de la infracción no está en la lista (podría estar inactivo),
        // lo añadimos manualmente para que aparezca en el formulario
        $driverExists = $drivers->contains('id', $conviction->user_driver_detail_id);
        if (!$driverExists) {
            $convictionDriver = UserDriverDetail::with('user')->find($conviction->user_driver_detail_id);
            if ($convictionDriver) {
                $drivers->push($convictionDriver);
            }
        }

        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.traffic.edit', compact('conviction', 'drivers', 'carriers'));
    }

    /**
     * Método para actualizar una infracción de tráfico existente
     */
    public function update(DriverTrafficConviction $conviction, Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'conviction_date' => 'required|date',
                'location' => 'required|string|max:255',
                'charge' => 'required|string|max:255',
                'penalty' => 'required|string|max:255',
            ]);

            $conviction->update($validated);

            // Procesar archivos si se han subido
            $files = json_decode($request->input('traffic_files'), true) ?? [];
            foreach ($files as $file) {
                if (isset($file['path'])) {
                    $conviction->addMediaFromDisk($file['path'], 'livewire-tmp')
                        ->usingName($file['original_name'])
                        ->withCustomProperties([
                            'original_name' => $file['original_name'],
                            'mime_type' => $file['mime_type'],
                            'size' => $file['size']
                        ])
                        ->preservingOriginal()
                        ->toMediaCollection('traffic-tickets', 'public');
                }
            }

            DB::commit();
            
            Log::info('Traffic conviction updated successfully', [
                'conviction_id' => $conviction->id,
                'driver_id' => $conviction->user_driver_detail_id
            ]);

            return redirect()
                ->route('admin.traffic.index')
                ->with('success', 'Traffic conviction updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating traffic conviction: ' . $e->getMessage(), [
                'conviction_id' => $conviction->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error updating traffic conviction: ' . $e->getMessage());
        }
    }

    // Método para eliminar una infracción de tráfico
    public function destroy(DriverTrafficConviction $conviction)
    {
        $driverId = $conviction->user_driver_detail_id;
        $conviction->delete();

        Session::flash('success', 'Traffic conviction record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'traffic-history') !== false) {
            return redirect()->route('admin.drivers.traffic-history', $driverId);
        }

        return redirect()->route('admin.traffic.index');
    }

    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->where('status', UserDriverDetail::STATUS_ACTIVE)
            ->with(['user'])
            ->get();

        return response()->json($drivers);
    }

    /**
     * Mostrar los documentos de una infracción de tráfico
     */
    public function showDocuments(DriverTrafficConviction $conviction)
    {
        // Recuperar todos los archivos de media asociados con esta infracción de tráfico
        $documents = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', DriverTrafficConviction::class)
            ->where('model_id', $conviction->id)
            ->get();

        // Información de depuración
        $debugInfo = [
            'conviction_id' => $conviction->id,
            'user_driver_detail_id' => $conviction->user_driver_detail_id,
            'documents_count' => $documents->count(),
            'collections' => [
                'traffic-tickets' => $conviction->getMedia('traffic-tickets')->count(),
                'traffic_documents' => $conviction->getMedia('traffic_documents')->count(),
                'all_media' => $documents->count(),
            ],
            'media_info' => $documents->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection_name' => $media->collection_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ];
            }),
        ];

        return view('admin.drivers.traffic.documents', compact('conviction', 'documents', 'debugInfo'));
    }

    /**
     * Eliminar un documento de una infracción de tráfico
     */
    public function deleteDocument($documentId)
    {
        try {
            $media = Media::findOrFail($documentId);
            if ($media->model_type !== DriverTrafficConviction::class) {
                return response()->json(['error' => 'Invalid document type'], 400);
            }

            $conviction = $media->model;
            $media->delete();

            // Obtener los archivos actualizados
            $updatedFiles = $conviction->getMedia('traffic-tickets')->map(function($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'created_at' => $media->created_at->toDateTimeString()
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
                'files' => $updatedFiles
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage(), [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error deleting document'], 500);
        }
    }

}



