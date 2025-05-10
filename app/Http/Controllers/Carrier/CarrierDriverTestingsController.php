<?php

namespace App\Http\Controllers\Carrier;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTesting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CarrierDriverTestingsController extends Controller
{
    /**
     * Mostrar la lista de pruebas de los conductores del carrier.
     */
    public function index(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        $query = DriverTesting::query()
            ->with(['userDriverDetail.user'])
            ->whereHas('userDriverDetail', function ($q) use ($carrier) {
                $q->where('carrier_id', $carrier->id);
            });

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('test_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
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
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();

        // Obtener valores únicos para los filtros de desplegable
        $testTypes = DriverTesting::whereHas('userDriverDetail', function ($q) use ($carrier) {
                $q->where('carrier_id', $carrier->id);
            })
            ->distinct()
            ->pluck('test_type')
            ->filter()
            ->toArray();
            
        $testResults = DriverTesting::whereHas('userDriverDetail', function ($q) use ($carrier) {
                $q->where('carrier_id', $carrier->id);
            })
            ->distinct()
            ->pluck('test_result')
            ->filter()
            ->toArray();

        return view('carrier.drivers.testings.index', compact('testings', 'drivers', 'carrier', 'testTypes', 'testResults'));
    }

    /**
     * Mostrar el historial de pruebas de un conductor específico.
     */
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.testings.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
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
            ->distinct()
            ->pluck('test_type')
            ->filter()
            ->toArray();
            
        $testResults = DriverTesting::where('user_driver_detail_id', $driver->id)
            ->distinct()
            ->pluck('test_result')
            ->filter()
            ->toArray();

        return view('carrier.drivers.testings.driver_history', compact('driver', 'testings', 'carrier', 'testTypes', 'testResults'));
    }

    /**
     * Mostrar el formulario para crear una nueva prueba.
     */
    public function create()
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
            
        return view('carrier.drivers.testings.create', compact('drivers', 'carrier'));
    }

    /**
     * Almacenar una nueva prueba.
     */
    public function store(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
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
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        $driver = UserDriverDetail::findOrFail($validated['user_driver_detail_id']);
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.testings.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }

        // Convertir checkboxes a valores booleanos
        $validated['is_random_test'] = isset($request->is_random_test);
        $validated['is_post_accident_test'] = isset($request->is_post_accident_test);
        $validated['is_reasonable_suspicion_test'] = isset($request->is_reasonable_suspicion_test);

        try {
            DriverTesting::create($validated);
            
            Session::flash('success', 'Registro de prueba añadido exitosamente.');
            
            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('carrier.drivers.testings.driver_history', $validated['user_driver_detail_id']);
            }
            
            return redirect()->route('carrier.drivers.testings.index');
            
        } catch (\Exception $e) {
            Log::error('Error al crear registro de prueba', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al crear registro de prueba: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario para editar una prueba.
     */
    public function edit(DriverTesting $testing)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($testing->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.testings.index')
                ->with('error', 'No tienes acceso a este registro de prueba.');
        }
        
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
            
        return view('carrier.drivers.testings.edit', compact('testing', 'drivers', 'carrier'));
    }

    /**
     * Actualizar una prueba.
     */
    public function update(Request $request, DriverTesting $testing)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($testing->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.testings.index')
                ->with('error', 'No tienes acceso a este registro de prueba.');
        }
        
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
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        $driver = UserDriverDetail::findOrFail($validated['user_driver_detail_id']);
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.testings.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }

        // Convertir checkboxes a valores booleanos
        $validated['is_random_test'] = isset($request->is_random_test);
        $validated['is_post_accident_test'] = isset($request->is_post_accident_test);
        $validated['is_reasonable_suspicion_test'] = isset($request->is_reasonable_suspicion_test);

        try {
            $testing->update($validated);
            
            Session::flash('success', 'Registro de prueba actualizado exitosamente.');
            
            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('carrier.drivers.testings.driver_history', $testing->user_driver_detail_id);
            }
            
            return redirect()->route('carrier.drivers.testings.index');
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar registro de prueba', [
                'error' => $e->getMessage(),
                'testing_id' => $testing->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al actualizar registro de prueba: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar una prueba.
     */
    public function destroy(DriverTesting $testing)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($testing->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.testings.index')
                ->with('error', 'No tienes acceso a este registro de prueba.');
        }
        
        try {
            $driverId = $testing->user_driver_detail_id;
            $testing->delete();
            
            Session::flash('success', 'Registro de prueba eliminado exitosamente.');
            
            // Determinar la ruta de retorno basado en la URL de referencia
            $referer = request()->headers->get('referer');
            if (strpos($referer, 'driver_history') !== false) {
                return redirect()->route('carrier.drivers.testings.driver_history', $driverId);
            }
            
            return redirect()->route('carrier.drivers.testings.index');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro de prueba', [
                'error' => $e->getMessage(),
                'testing_id' => $testing->id
            ]);
            
            return redirect()->route('carrier.drivers.testings.index')
                ->with('error', 'Error al eliminar registro de prueba: ' . $e->getMessage());
        }
    }
}
