<?php
namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTesting;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TestingsController extends Controller
{
    // Vista para todos los tests
    public function index(Request $request)
    {
        $query = DriverTesting::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('test_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%');
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
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        // Obtener valores únicos para los filtros de desplegable
        $testTypes = DriverTesting::distinct()->pluck('test_type')->filter()->toArray();
        $testResults = DriverTesting::distinct()->pluck('test_result')->filter()->toArray();

        return view('admin.drivers.testings.index', compact('testings', 'drivers', 'carriers', 'testTypes', 'testResults'));
    }

    // Vista para el historial de pruebas de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
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
            ->distinct()->pluck('test_type')->filter()->toArray();
        $testResults = DriverTesting::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('test_result')->filter()->toArray();

        return view('admin.drivers.testings.driver_history', compact('driver', 'testings', 'testTypes', 'testResults'));
    }

    // Método para almacenar una nueva prueba
    public function store(Request $request)
    {
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

        // Convertir checkboxes a valores booleanos
        $validated['is_random_test'] = isset($request->is_random_test);
        $validated['is_post_accident_test'] = isset($request->is_post_accident_test);
        $validated['is_reasonable_suspicion_test'] = isset($request->is_reasonable_suspicion_test);

        DriverTesting::create($validated);

        Session::flash('success', 'Testing record added successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.testing-history', $validated['user_driver_detail_id']);
        }
        return redirect()->route('admin.testings.index');
    }

    // Método para actualizar una prueba existente
    public function update(DriverTesting $testing, Request $request)
    {
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

        // Convertir checkboxes a valores booleanos
        $validated['is_random_test'] = isset($request->is_random_test);
        $validated['is_post_accident_test'] = isset($request->is_post_accident_test);
        $validated['is_reasonable_suspicion_test'] = isset($request->is_reasonable_suspicion_test);

        $testing->update($validated);

        Session::flash('success', 'Testing record updated successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.testing-history', $testing->user_driver_detail_id);
        }
        return redirect()->route('admin.testings.index');
    }

    // Método para eliminar una prueba
    public function destroy(DriverTesting $testing)
    {
        $driverId = $testing->user_driver_detail_id;
        $testing->delete();

        Session::flash('success', 'Testing record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'testing-history') !== false) {
            return redirect()->route('admin.drivers.testing-history', $driverId);
        }
        return redirect()->route('admin.testings.index');
    }

    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with(['user']) // Asegúrate de incluir la relación con el usuario
            ->get();
        return response()->json($drivers);
    }
}