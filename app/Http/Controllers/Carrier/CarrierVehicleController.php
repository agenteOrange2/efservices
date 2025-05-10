<?php

namespace App\Http\Controllers\Carrier;

use App\Models\Carrier;
use App\Helpers\Constants;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMake;
use App\Models\Admin\Vehicle\VehicleType;
use App\Models\Admin\Vehicle\VehicleDocument;
use App\Models\Admin\Vehicle\VehicleServiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CarrierVehicleController extends Controller
{
    /**
     * Mostrar una lista de todos los vehículos del carrier.
     */
    public function index(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        $query = Vehicle::with(['driver'])
            ->where('carrier_id', $carrier->id);
        
        // Filtros
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('out_of_service', false)->where('suspended', false);
            } elseif ($request->status === 'out_of_service') {
                $query->where('out_of_service', true);
            } elseif ($request->status === 'suspended') {
                $query->where('suspended', true);
            }
        }
        
        if ($request->has('driver_id')) {
            $query->where('user_driver_detail_id', $request->driver_id);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%")
                  ->orWhere('company_unit_number', 'like', "%{$search}%");
            });
        }
        
        $vehicles = $query->paginate(10);
        
        // Obtener los conductores del carrier para el filtro
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
        
        return view('carrier.vehicles.index', compact('vehicles', 'carrier', 'drivers'));
    }

    /**
     * Mostrar el formulario para crear un nuevo vehículo.
     */
    public function create()
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar si se ha alcanzado el límite de vehículos
        $maxVehicles = $carrier->membership->max_vehicles ?? 1;
        $currentVehiclesCount = Vehicle::where('carrier_id', $carrier->id)->count();
        
        if ($currentVehiclesCount >= $maxVehicles) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'Has alcanzado el límite máximo de vehículos para tu plan. Actualiza tu membresía para añadir más vehículos.');
        }
        
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->where('status', 1) // Solo conductores activos
            ->get();
            
        $vehicleMakes = VehicleMake::all();
        $vehicleTypes = VehicleType::all();
        $usStates = Constants::usStates();

        return view('carrier.vehicles.create', compact('carrier', 'drivers', 'vehicleMakes', 'vehicleTypes', 'usStates'));
    }

    /**
     * Almacenar un vehículo recién creado.
     */
    public function store(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        $validator = Validator::make($request->all(), [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'company_unit_number' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vin' => 'required|string|max:255|unique:vehicles,vin',
            'gvwr' => 'nullable|string|max:255',
            'registration_state' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date',
            'permanent_tag' => 'boolean',
            'tire_size' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:255',
            'irp_apportioned_plate' => 'boolean',
            'ownership_type' => 'required|in:owned,leased',
            'location' => 'nullable|string|max:255',
            'user_driver_detail_id' => 'nullable|exists:user_driver_details,id',
            'annual_inspection_expiration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Guardar o crear marca del vehículo si no existe
        if ($request->has('make') && !VehicleMake::where('name', $request->make)->exists()) {
            VehicleMake::create(['name' => $request->make]);
        }
        
        // Guardar o crear tipo de vehículo si no existe
        if ($request->has('type') && !VehicleType::where('name', $request->type)->exists()) {
            VehicleType::create(['name' => $request->type]);
        }

        // Crear el vehículo
        $vehicleData = $request->all();
        $vehicleData['carrier_id'] = $carrier->id;
        $vehicle = Vehicle::create($vehicleData);
        
        return redirect()->route('carrier.vehicles.show', $vehicle->id)
            ->with('success', 'Vehículo creado exitosamente');
    }

    /**
     * Mostrar un vehículo específico.
     */
    public function show(Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        $vehicle->load(['driver', 'serviceItems', 'documents']);
        
        return view('carrier.vehicles.show', compact('vehicle', 'carrier'));
    }

    /**
     * Mostrar el formulario para editar un vehículo.
     */
    public function edit(Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->where('status', 1) // Solo conductores activos
            ->get();
            
        $vehicleMakes = VehicleMake::all();
        $vehicleTypes = VehicleType::all();
        $usStates = Constants::usStates();

        return view('carrier.vehicles.edit', compact('vehicle', 'carrier', 'drivers', 'vehicleMakes', 'vehicleTypes', 'usStates'));
    }

    /**
     * Actualizar un vehículo específico.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        $validator = Validator::make($request->all(), [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'company_unit_number' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vin' => 'required|string|max:255|unique:vehicles,vin,' . $vehicle->id,
            'gvwr' => 'nullable|string|max:255',
            'registration_state' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date',
            'permanent_tag' => 'boolean',
            'tire_size' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:255',
            'irp_apportioned_plate' => 'boolean',
            'ownership_type' => 'required|in:owned,leased',
            'location' => 'nullable|string|max:255',
            'user_driver_detail_id' => 'nullable|exists:user_driver_details,id',
            'annual_inspection_expiration_date' => 'nullable|date',
            'out_of_service' => 'boolean',
            'out_of_service_date' => 'nullable|date|required_if:out_of_service,1',
            'suspended' => 'boolean',
            'suspended_date' => 'nullable|date|required_if:suspended,1',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Guardar o crear marca del vehículo si no existe
        if ($request->has('make') && !VehicleMake::where('name', $request->make)->exists()) {
            VehicleMake::create(['name' => $request->make]);
        }
        
        // Guardar o crear tipo de vehículo si no existe
        if ($request->has('type') && !VehicleType::where('name', $request->type)->exists()) {
            VehicleType::create(['name' => $request->type]);
        }
        
        // Actualizar el vehículo
        $vehicle->update($request->all());
        
        return redirect()->route('carrier.vehicles.show', $vehicle->id)
            ->with('success', 'Vehículo actualizado exitosamente');
    }

    /**
     * Eliminar un vehículo específico.
     */
    public function destroy(Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        try {
            // Eliminar documentos relacionados
            $vehicle->documents()->get()->each(function($document) {
                $document->clearMediaCollection('vehicle_documents');
                $document->delete();
            });
            
            // Eliminar items de servicio relacionados
            $vehicle->serviceItems()->delete();
            
            // Eliminar el vehículo
            $vehicle->delete();
            
            return redirect()->route('carrier.vehicles.index')
                ->with('success', 'Vehículo eliminado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar vehículo', [
                'error' => $e->getMessage(),
                'vehicle_id' => $vehicle->id
            ]);
            
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'Error al eliminar vehículo: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar la lista de documentos de un vehículo.
     */
    public function documents(Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        $documents = $vehicle->documents()->paginate(10);
        
        return view('carrier.vehicles.documents.index', compact('vehicle', 'documents', 'carrier'));
    }
    
    /**
     * Mostrar el formulario para crear un nuevo documento de vehículo.
     */
    public function createDocument(Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        return view('carrier.vehicles.documents.create', compact('vehicle', 'carrier'));
    }
    
    /**
     * Almacenar un nuevo documento de vehículo.
     */
    public function storeDocument(Request $request, Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'document_file' => 'required|file|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Crear el documento
            $document = new VehicleDocument([
                'vehicle_id' => $vehicle->id,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'issue_date' => $request->issue_date,
                'expiration_date' => $request->expiration_date,
                'notes' => $request->notes,
                'status' => 'active',
            ]);
            
            $document->save();
            
            // Procesar el archivo
            if ($request->hasFile('document_file')) {
                $document->addMediaFromRequest('document_file')
                    ->toMediaCollection('vehicle_documents');
            }
            
            return redirect()->route('carrier.vehicles.documents', $vehicle->id)
                ->with('success', 'Documento creado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al crear documento de vehículo', [
                'error' => $e->getMessage(),
                'vehicle_id' => $vehicle->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al crear documento: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Mostrar el formulario para editar un documento de vehículo.
     */
    public function editDocument(Vehicle $vehicle, VehicleDocument $document)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $document->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este documento.');
        }
        
        return view('carrier.vehicles.documents.edit', compact('vehicle', 'document', 'carrier'));
    }
    
    /**
     * Actualizar un documento de vehículo.
     */
    public function updateDocument(Request $request, Vehicle $vehicle, VehicleDocument $document)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $document->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este documento.');
        }
        
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'document_file' => 'nullable|file|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Actualizar el documento
            $document->update([
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'issue_date' => $request->issue_date,
                'expiration_date' => $request->expiration_date,
                'notes' => $request->notes,
            ]);
            
            // Procesar el archivo si se proporcionó uno nuevo
            if ($request->hasFile('document_file')) {
                $document->clearMediaCollection('vehicle_documents');
                $document->addMediaFromRequest('document_file')
                    ->toMediaCollection('vehicle_documents');
            }
            
            return redirect()->route('carrier.vehicles.documents', $vehicle->id)
                ->with('success', 'Documento actualizado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar documento de vehículo', [
                'error' => $e->getMessage(),
                'document_id' => $document->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al actualizar documento: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Eliminar un documento de vehículo.
     */
    public function destroyDocument(Vehicle $vehicle, VehicleDocument $document)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $document->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este documento.');
        }
        
        try {
            // Eliminar archivos adjuntos
            $document->clearMediaCollection('vehicle_documents');
            
            // Eliminar el documento
            $document->delete();
            
            return redirect()->route('carrier.vehicles.documents', $vehicle->id)
                ->with('success', 'Documento eliminado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento de vehículo', [
                'error' => $e->getMessage(),
                'document_id' => $document->id
            ]);
            
            return redirect()->route('carrier.vehicles.documents', $vehicle->id)
                ->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Descargar un documento de vehículo.
     */
    public function downloadDocument(Vehicle $vehicle, VehicleDocument $document)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $document->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este documento.');
        }
        
        $media = $document->getFirstMedia('vehicle_documents');
        
        if (!$media) {
            return redirect()->back()
                ->with('error', 'No se encontró el archivo del documento.');
        }
        
        return $media;
    }
    
    /**
     * Mostrar la lista de mantenimientos de un vehículo.
     */
    public function serviceItems(Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        $serviceItems = $vehicle->serviceItems()->paginate(10);
        
        return view('carrier.vehicles.service-items.index', compact('vehicle', 'serviceItems', 'carrier'));
    }
    
    /**
     * Mostrar el formulario para crear un nuevo item de servicio.
     */
    public function createServiceItem(Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        return view('carrier.vehicles.service-items.create', compact('vehicle', 'carrier'));
    }
    
    /**
     * Almacenar un nuevo item de servicio.
     */
    public function storeServiceItem(Request $request, Vehicle $vehicle)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este vehículo.');
        }
        
        $validator = Validator::make($request->all(), [
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'service_type' => 'required|string|max:255',
            'service_tasks' => 'required|string',
            'vendor_mechanic' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'service_documents.*' => 'nullable|file|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Crear el item de servicio
            $serviceItem = new VehicleServiceItem([
                'vehicle_id' => $vehicle->id,
                'service_date' => $request->service_date,
                'next_service_date' => $request->next_service_date,
                'service_type' => $request->service_type,
                'service_tasks' => $request->service_tasks,
                'vendor_mechanic' => $request->vendor_mechanic,
                'cost' => $request->cost,
                'odometer_reading' => $request->odometer_reading,
                'notes' => $request->notes,
                'status' => 'completed',
            ]);
            
            $serviceItem->save();
            
            // Procesar los archivos si se proporcionaron
            if ($request->hasFile('service_documents')) {
                foreach ($request->file('service_documents') as $file) {
                    $serviceItem->addMedia($file)
                        ->toMediaCollection('service_documents');
                }
            }
            
            return redirect()->route('carrier.vehicles.service-items', $vehicle->id)
                ->with('success', 'Item de servicio creado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al crear item de servicio', [
                'error' => $e->getMessage(),
                'vehicle_id' => $vehicle->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al crear item de servicio: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Mostrar el formulario para editar un item de servicio.
     */
    public function editServiceItem(Vehicle $vehicle, VehicleServiceItem $serviceItem)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $serviceItem->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este item de servicio.');
        }
        
        return view('carrier.vehicles.service-items.edit', compact('vehicle', 'serviceItem', 'carrier'));
    }
    
    /**
     * Actualizar un item de servicio.
     */
    public function updateServiceItem(Request $request, Vehicle $vehicle, VehicleServiceItem $serviceItem)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $serviceItem->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este item de servicio.');
        }
        
        $validator = Validator::make($request->all(), [
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'service_type' => 'required|string|max:255',
            'service_tasks' => 'required|string',
            'vendor_mechanic' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'service_documents.*' => 'nullable|file|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Actualizar el item de servicio
            $serviceItem->update([
                'service_date' => $request->service_date,
                'next_service_date' => $request->next_service_date,
                'service_type' => $request->service_type,
                'service_tasks' => $request->service_tasks,
                'vendor_mechanic' => $request->vendor_mechanic,
                'cost' => $request->cost,
                'odometer_reading' => $request->odometer_reading,
                'notes' => $request->notes,
                'status' => $request->status,
            ]);
            
            // Procesar los archivos si se proporcionaron
            if ($request->hasFile('service_documents')) {
                foreach ($request->file('service_documents') as $file) {
                    $serviceItem->addMedia($file)
                        ->toMediaCollection('service_documents');
                }
            }
            
            return redirect()->route('carrier.vehicles.service-items', $vehicle->id)
                ->with('success', 'Item de servicio actualizado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar item de servicio', [
                'error' => $e->getMessage(),
                'service_item_id' => $serviceItem->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al actualizar item de servicio: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Eliminar un item de servicio.
     */
    public function destroyServiceItem(Vehicle $vehicle, VehicleServiceItem $serviceItem)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $serviceItem->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este item de servicio.');
        }
        
        try {
            // Eliminar archivos adjuntos
            $serviceItem->clearMediaCollection('service_documents');
            
            // Eliminar el item de servicio
            $serviceItem->delete();
            
            return redirect()->route('carrier.vehicles.service-items', $vehicle->id)
                ->with('success', 'Item de servicio eliminado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar item de servicio', [
                'error' => $e->getMessage(),
                'service_item_id' => $serviceItem->id
            ]);
            
            return redirect()->route('carrier.vehicles.service-items', $vehicle->id)
                ->with('error', 'Error al eliminar item de servicio: ' . $e->getMessage());
        }
    }
    
    /**
     * Cambiar el estado de un item de servicio.
     */
    public function toggleServiceItemStatus(Vehicle $vehicle, VehicleServiceItem $serviceItem)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($vehicle->carrier_id !== $carrier->id || $serviceItem->vehicle_id !== $vehicle->id) {
            return redirect()->route('carrier.vehicles.index')
                ->with('error', 'No tienes acceso a este item de servicio.');
        }
        
        try {
            // Cambiar el estado del item de servicio
            $newStatus = $serviceItem->status === 'completed' ? 'pending' : 'completed';
            $serviceItem->update(['status' => $newStatus]);
            
            return redirect()->route('carrier.vehicles.service-items', $vehicle->id)
                ->with('success', 'Estado del item de servicio actualizado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del item de servicio', [
                'error' => $e->getMessage(),
                'service_item_id' => $serviceItem->id
            ]);
            
            return redirect()->route('carrier.vehicles.service-items', $vehicle->id)
                ->with('error', 'Error al cambiar estado del item de servicio: ' . $e->getMessage());
        }
    }
}
