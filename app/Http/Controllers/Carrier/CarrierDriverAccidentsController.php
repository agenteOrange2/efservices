<?php

namespace App\Http\Controllers\Carrier;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverAccident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CarrierDriverAccidentsController extends Controller
{
    /**
     * Mostrar la lista de accidentes de los conductores del carrier.
     */
    public function index(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        $query = DriverAccident::query()
            ->with(['userDriverDetail.user'])
            ->whereHas('userDriverDetail', function ($q) use ($carrier) {
                $q->where('carrier_id', $carrier->id);
            });

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('nature_of_accident', 'like', '%' . $request->search_term . '%')
                ->orWhere('comments', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
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
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();

        return view('carrier.drivers.accidents.index', compact('accidents', 'drivers', 'carrier'));
    }

    /**
     * Mostrar el historial de accidentes de un conductor específico.
     */
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.accidents.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
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

        return view('carrier.drivers.accidents.driver_history', compact('driver', 'accidents', 'carrier'));
    }

    /**
     * Mostrar el formulario para crear un nuevo registro de accidente.
     */
    public function create()
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
            
        return view('carrier.drivers.accidents.create', compact('drivers', 'carrier'));
    }

    /**
     * Almacenar un nuevo registro de accidente.
     */
    public function store(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'accident_date' => 'required|date',
            'nature_of_accident' => 'required|string|max:255',
            'had_injuries' => 'boolean',
            'number_of_injuries' => 'nullable|integer|min:0',
            'had_fatalities' => 'boolean',
            'number_of_fatalities' => 'nullable|integer|min:0',
            'comments' => 'nullable|string',
        ]);
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        $driver = UserDriverDetail::findOrFail($validated['user_driver_detail_id']);
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.accidents.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }

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

        try {
            DriverAccident::create($validated);
            
            Session::flash('success', 'Registro de accidente añadido exitosamente.');
            
            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('carrier.drivers.accidents.driver_history', $validated['user_driver_detail_id']);
            }
            
            return redirect()->route('carrier.drivers.accidents.index');
            
        } catch (\Exception $e) {
            Log::error('Error al crear registro de accidente', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al crear registro de accidente: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario para editar un registro de accidente.
     */
    public function edit(DriverAccident $accident)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($accident->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.accidents.index')
                ->with('error', 'No tienes acceso a este registro de accidente.');
        }
        
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
            
        return view('carrier.drivers.accidents.edit', compact('accident', 'drivers', 'carrier'));
    }

    /**
     * Actualizar un registro de accidente.
     */
    public function update(Request $request, DriverAccident $accident)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($accident->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.accidents.index')
                ->with('error', 'No tienes acceso a este registro de accidente.');
        }
        
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'accident_date' => 'required|date',
            'nature_of_accident' => 'required|string|max:255',
            'had_injuries' => 'boolean',
            'number_of_injuries' => 'nullable|integer|min:0',
            'had_fatalities' => 'boolean',
            'number_of_fatalities' => 'nullable|integer|min:0',
            'comments' => 'nullable|string',
        ]);
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        $driver = UserDriverDetail::findOrFail($validated['user_driver_detail_id']);
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.accidents.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }

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

        try {
            $accident->update($validated);
            
            Session::flash('success', 'Registro de accidente actualizado exitosamente.');
            
            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('carrier.drivers.accidents.driver_history', $accident->user_driver_detail_id);
            }
            
            return redirect()->route('carrier.drivers.accidents.index');
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar registro de accidente', [
                'error' => $e->getMessage(),
                'accident_id' => $accident->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al actualizar registro de accidente: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar un registro de accidente.
     */
    public function destroy(DriverAccident $accident)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($accident->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.accidents.index')
                ->with('error', 'No tienes acceso a este registro de accidente.');
        }
        
        try {
            $driverId = $accident->user_driver_detail_id;
            $accident->delete();
            
            Session::flash('success', 'Registro de accidente eliminado exitosamente.');
            
            // Determinar la ruta de retorno basado en la URL de referencia
            $referer = request()->headers->get('referer');
            if (strpos($referer, 'driver_history') !== false) {
                return redirect()->route('carrier.drivers.accidents.driver_history', $driverId);
            }
            
            return redirect()->route('carrier.drivers.accidents.index');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro de accidente', [
                'error' => $e->getMessage(),
                'accident_id' => $accident->id
            ]);
            
            return redirect()->route('carrier.drivers.accidents.index')
                ->with('error', 'Error al eliminar registro de accidente: ' . $e->getMessage());
        }
    }
}
