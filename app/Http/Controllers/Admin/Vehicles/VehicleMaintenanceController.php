<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VehicleMaintenanceController extends Controller
{
    /**
     * Mostrar todos los mantenimientos para un vehículo.
     */
    public function index(Vehicle $vehicle)
    {
        // Usamos el modelo VehicleMaintenance para obtener los mantenimientos
        $maintenances = VehicleMaintenance::where('vehicle_id', $vehicle->id)
            ->orderBy('service_date', 'desc')
            ->paginate(10);

        return view('admin.vehicles.maintenances.index', compact('vehicle', 'maintenances'));
    }

    /**
     * Mostrar el formulario para crear un nuevo item de servicio.
     */
    public function create(Vehicle $vehicle)
    {
        return view('admin.vehicles.maintenances.create', compact('vehicle'));
    }

    /**
     * Almacenar un nuevo item de servicio.
     */
    public function store(Request $request, Vehicle $vehicle)
    {
        Log::info('Iniciando creación de mantenimiento para vehículo', [
            'vehicle_id' => $vehicle->id,
            'request_data' => $request->except(['_token']),
            'request_has_files' => $request->hasFile('maintenance_files')
        ]);

        $validator = Validator::make($request->all(), [
            'unit' => 'required|string|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'service_tasks' => 'required|string|max:255',
            'vendor_mechanic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            Log::warning('Validación fallida al crear mantenimiento', [
                'vehicle_id' => $vehicle->id,
                'errors' => $validator->errors()->toArray()
            ]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Crear un nuevo mantenimiento usando VehicleMaintenance
            $serviceItem = new VehicleMaintenance([
                'vehicle_id' => $vehicle->id,
                'unit' => $request->unit,
                'service_date' => $request->service_date,
                'next_service_date' => $request->next_service_date,
                'service_tasks' => $request->service_tasks,
                'vendor_mechanic' => $request->vendor_mechanic,
                'description' => $request->description,
                'cost' => $request->cost,
                'odometer' => $request->odometer,
                'status' => false, // Por defecto, no completado
                'created_by' => Auth::id(), // Asegurar que se guarde quién lo creó
            ]);

            $result = $serviceItem->save();

            Log::info('Resultado de guardar mantenimiento', [
                'maintenance_id' => $serviceItem->id,
                'save_result' => $result,
                'data_saved' => $serviceItem->toArray()
            ]);

            // Procesar archivos de mantenimiento si existen
            if ($request->hasFile('maintenance_files')) {
                Log::info('Archivos de mantenimiento encontrados', [
                    'file_count' => count($request->file('maintenance_files'))
                ]);

                foreach ($request->file('maintenance_files') as $file) {
                    Log::info('Procesando archivo', [
                        'name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);

                    try {
                        $media = $serviceItem->addMedia($file)
                            ->toMediaCollection('maintenance_files');

                        Log::info('Archivo guardado correctamente', [
                            'media_id' => $media->id,
                            'file_name' => $media->file_name
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al guardar archivo', [
                            'error' => $e->getMessage(),
                            'file_name' => $file->getClientOriginalName()
                        ]);
                    }
                }
            } else {
                Log::info('No se encontraron archivos adjuntos', [
                    'all_files' => $request->allFiles(),
                    'file_keys' => array_keys($request->allFiles())
                ]);
            }

            DB::commit();

            // Redireccionar tanto a la vista de vehículo como a la vista general de mantenimiento
            return redirect()->route('admin.vehicles.show', $vehicle->id)
                ->with('success', 'Servicio de mantenimiento creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar mantenimiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'vehicle_id' => $vehicle->id
            ]);

            return redirect()->back()
                ->with('error', 'Error al guardar el mantenimiento: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar un item de servicio específico.
     */
    public function show(Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);

        // Convertir ambos valores a enteros antes de comparar para evitar problemas con tipos de datos
        if ((int)$serviceItem->vehicle_id !== (int)$vehicle->id) {
            Log::warning('Inconsistencia en IDs de vehículo', [
                'vehicle_id' => $vehicle->id,
                'vehicle_id_type' => gettype($vehicle->id),
                'serviceItem_vehicle_id' => $serviceItem->vehicle_id,
                'serviceItem_vehicle_id_type' => gettype($serviceItem->vehicle_id)
            ]);
            abort(404);
        }

        return view('admin.vehicles.service-items.show', compact('vehicle', 'serviceItem'));
    }

    /**
     * Mostrar el formulario para editar un item de servicio.
     */
    public function edit(Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);

        // Verificar que el service item pertenece a este vehículo
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        return view('admin.vehicles.service-items.edit', compact('vehicle', 'serviceItem'));
    }

    /**
     * Actualizar un item de servicio específico.
     */
    public function update(Request $request, Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);

        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'unit' => 'required|string|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'service_tasks' => 'required|string|max:255',
            'vendor_mechanic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar los campos - incluido status
        $serviceItem->update([
            'unit' => $request->unit,
            'service_date' => $request->service_date,
            'next_service_date' => $request->next_service_date,
            'service_tasks' => $request->service_tasks,
            'vendor_mechanic' => $request->vendor_mechanic,
            'description' => $request->description,
            'cost' => $request->cost,
            'odometer' => $request->odometer,
            // Conservamos el valor actual de status
        ]);

        // Procesar archivos de mantenimiento si existen
        if ($request->hasFile('maintenance_files')) {
            Log::info('Archivos de mantenimiento encontrados en update: ' . count($request->file('maintenance_files')));

            foreach ($request->file('maintenance_files') as $file) {
                Log::info('Procesando archivo en update: ' . $file->getClientOriginalName() . ' - ' . $file->getMimeType());

                try {
                    $media = $serviceItem->addMedia($file)
                        ->toMediaCollection('maintenance_files');

                    Log::info('Archivo actualizado correctamente: ' . $media->id);
                } catch (\Exception $e) {
                    Log::error('Error al guardar archivo en update: ' . $e->getMessage());
                }
            }
        } else {
            Log::info('No se encontraron archivos de mantenimiento en la solicitud de update');
            Log::info('Todos los archivos en la solicitud de update: ' . json_encode($request->allFiles()));
        }

        return redirect()->route('admin.vehicles.maintenances.index', $vehicle->id)
            ->with('success', 'Mantenimiento actualizado exitosamente');
    }

    /**
     * Eliminar un item de servicio específico.
     */
    public function destroy(Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);

        // Convertir ambos valores a enteros antes de comparar para evitar problemas con tipos de datos
        if ((int)$serviceItem->vehicle_id !== (int)$vehicle->id) {
            Log::warning('Inconsistencia en IDs de vehículo', [
                'vehicle_id' => $vehicle->id,
                'vehicle_id_type' => gettype($vehicle->id),
                'serviceItem_vehicle_id' => $serviceItem->vehicle_id,
                'serviceItem_vehicle_id_type' => gettype($serviceItem->vehicle_id)
            ]);
            abort(404);
        }

        // Eliminar todos los archivos asociados
        $serviceItem->clearMediaCollection('maintenance_files');

        $serviceItem->delete();

        return redirect()->route('admin.vehicles.show', $vehicle->id)
            ->with('success', 'Mantenimiento eliminado exitosamente');
    }

    /**
     * Cambiar el estado del mantenimiento (completado/pendiente)
     */
    function toggleStatus(Vehicle $vehicle, $serviceItemId)
    {
        Log::info('toggleStatus llamado', [
            'vehicle_id' => $vehicle->id,
            'service_item_id' => $serviceItemId,
            'url' => request()->fullUrl(),
            'route_name' => request()->route()->getName(),
            'route_parameters' => request()->route()->parameters()
        ]);

        try {
            $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);

            Log::info('ServiceItem encontrado', [
                'serviceItem_id' => $serviceItem->id,
                'serviceItem_vehicle_id' => $serviceItem->vehicle_id
            ]);

            // CORRECCIÓN: Convertir ambos valores a enteros antes de comparar
            if ((int)$serviceItem->vehicle_id !== (int)$vehicle->id) {
                Log::warning('Inconsistencia en IDs de vehículo', [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_id_type' => gettype($vehicle->id),
                    'serviceItem_vehicle_id' => $serviceItem->vehicle_id,
                    'serviceItem_vehicle_id_type' => gettype($serviceItem->vehicle_id)
                ]);
                abort(404);
            }

            $serviceItem->status = !$serviceItem->status;
            $serviceItem->save();

            Log::info('Estado actualizado correctamente', [
                'new_status' => $serviceItem->status
            ]);

            return back()->with('success', 'Estado del mantenimiento actualizado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en toggleStatus', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    function deleteFile(Vehicle $vehicle, $serviceItemId, $mediaId)
    {
        Log::info('deleteFile llamado', [
            'vehicle_id' => $vehicle->id,
            'service_item_id' => $serviceItemId,
            'media_id' => $mediaId,
            'url' => request()->fullUrl(),
            'route_name' => request()->route()->getName(),
            'route_parameters' => request()->route()->parameters()
        ]);

        try {
            $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);

            Log::info('ServiceItem encontrado', [
                'serviceItem_id' => $serviceItem->id,
                'serviceItem_vehicle_id' => $serviceItem->vehicle_id
            ]);

            // CORRECCIÓN: Convertir ambos valores a enteros antes de comparar
            if ((int)$serviceItem->vehicle_id !== (int)$vehicle->id) {
                Log::warning('Inconsistencia en IDs de vehículo', [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_id_type' => gettype($vehicle->id),
                    'serviceItem_vehicle_id' => $serviceItem->vehicle_id,
                    'serviceItem_vehicle_id_type' => gettype($serviceItem->vehicle_id)
                ]);
                abort(404);
            }

            // Verificamos que el archivo pertenezca al mantenimiento
            $media = $serviceItem->media()->where('id', $mediaId)->first();

            if (!$media) {
                Log::warning('Media no encontrado', [
                    'serviceItem_id' => $serviceItem->id,
                    'media_id' => $mediaId
                ]);
                abort(404, 'Archivo no encontrado');
            }

            Log::info('Media encontrado, eliminando', [
                'media_id' => $media->id,
                'media_model_id' => $media->model_id,
                'media_model_type' => $media->model_type
            ]);

            // Eliminamos directamente de la tabla media para evitar problemas de eliminación en cascada
            DB::table('media')->where('id', $mediaId)->delete();

            Log::info('Archivo eliminado correctamente');

            return back()->with('success', 'Archivo eliminado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en deleteFile', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
