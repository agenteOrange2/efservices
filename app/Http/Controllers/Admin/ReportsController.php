<?php

namespace App\Http\Controllers\Admin;

use ZipArchive;
use App\Models\User;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Vehicle\Vehicle;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;

class ReportsController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    /**
     * Mostrar la página principal de reportes con estadísticas.
     */
    public function index()
    {
        $startTime = microtime(true);
        
        try {
            // Cache key para estadísticas generales
            $cacheKey = 'reports_general_stats';
            
            // Obtener estadísticas del caché o generar nuevas
            $stats = Cache::remember($cacheKey, 600, function () {
                return $this->reportService->getSystemOverviewReport();
            });
            
            // Log performance
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Reports index loaded', [
                'execution_time_ms' => $executionTime,
                'cache_hit' => Cache::has($cacheKey)
            ]);
            
            return view('admin.reports.index', compact('stats'));
        } catch (\Exception $e) {
            Log::error('Error loading reports index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            
            return back()->with('error', 'Error loading reports');
        }
    }
    
    // Método eliminado - ahora se usa ReportService::getGeneralReport()
    
    // Métodos eliminados - ahora se usan en ReportService

    /**
     * Generar reporte de conductores activos.
     */
    public function activeDrivers(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Preparar filtros para el servicio
            $filters = [
                'search' => $request->input('search', ''),
                'carrier' => $request->input('carrier', ''),
                'tab' => $request->input('tab', 'all'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'per_page' => $request->input('per_page', 10)
            ];

            // Cache key basado en filtros (excluyendo paginación para mejor hit rate)
            $cacheKey = 'active_drivers_' . md5(serialize(Arr::except($filters, ['per_page'])));
            
            // Obtener datos del caché o generar nuevos
            $reportData = Cache::remember($cacheKey, 300, function () use ($filters) {
                return $this->reportService->getDriverReport($filters);
            });
            
            // Log performance
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Active drivers report loaded', [
                'execution_time_ms' => $executionTime,
                'cache_hit' => Cache::has($cacheKey),
                'filters' => $filters
            ]);

            return view('admin.reports.active-drivers', $reportData);
        } catch (\Exception $e) {
            Log::error('Error loading active drivers report', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            
            return back()->with('error', 'Error loading drivers report');
        }
    }

    /**
     * Exportar a PDF el reporte de conductores activos.
     */
    public function activeDriversPdf(Request $request)
    {
        // Get filter parameters - usando exactamente los mismos filtros que en la vista
        $search = $request->input('search', '');
        $carrierFilter = $request->input('carrier', '');
        $tab = $request->input('tab', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $month = $request->input('month');

        // Base query for all approved drivers
        $query = UserDriverDetail::with(['user', 'carrier', 'application', 'primaryLicense'])
            ->whereHas('application', function ($q) {
                $q->where('status', DriverApplication::STATUS_APPROVED);
            });

        // Apply tab filters - igual que en activeDrivers
        switch ($tab) {
            case 'active':
                $query->where('status', UserDriverDetail::STATUS_ACTIVE);
                break;
            case 'inactive':
                $query->where('status', UserDriverDetail::STATUS_INACTIVE);
                break;
            case 'new':
                $query->whereDate('created_at', '>=', now()->subDays(30));
                break;
                // Default 'all' tab doesn't need additional filtering
        }

        $query->orderBy('created_at', 'desc');

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Apply carrier filter if provided
        if (!empty($carrierFilter)) {
            $query->where('carrier_id', $carrierFilter);
            $carrier = Carrier::find($carrierFilter);
            $carrierName = $carrier ? $carrier->name : 'Unknown';
        } else {
            $carrierName = 'All';
        }

        // Filtrar por rango de fechas si se proporcionan
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Filtro de mes eliminado

        // Obtener todos los conductores sin paginación para el PDF
        $drivers = $query->get();

        // Configurar información para el PDF
        $date = now()->format('m/d/Y H:i');
        $filtros = [];

        // Agregar información de filtros aplicados
        if (!empty($tab) && $tab != 'all') {
            $tabNames = [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'new' => 'New (30 days)'
            ];
            $filtros[] = "Tab: " . ($tabNames[$tab] ?? ucfirst($tab));
        }

        if (!empty($search)) {
            $filtros[] = "Search: {$search}";
        }

        if (!empty($carrierFilter)) {
            $filtros[] = "Carrier: {$carrierName}";
        }

        if (!empty($dateFrom)) {
            $formattedDate = date('m/d/Y', strtotime($dateFrom));
            $filtros[] = "From : {$formattedDate}";
        }

        if (!empty($dateTo)) {
            $formattedDate = date('m/d/Y', strtotime($dateTo));
            $filtros[] = "To : {$formattedDate}";
        }

        // Filtro de mes eliminado

        $pdf = PDF::loadView('admin.reports.pdf.active-drivers-pdf', [
            'drivers' => $drivers,
            'date' => $date,
            'carrierName' => $carrierName,
            'totalDrivers' => $drivers->count(),
            'filtros' => $filtros
        ]);

        // Personalizar el PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true
        ]);

        // Retornar el PDF para descarga
        return $pdf->download('conductores_activos_' . now()->format('Y-m-d_H-i') . '.pdf');
    }

    /**
     * Generar reporte de equipamiento/vehículos.
     */
    public function equipmentList(Request $request)
    {
        $startTime = microtime(true);
        
        // Get filter parameters
        $search = $request->input('search', '');
        $carrierFilter = $request->input('carrier', '');
        $tab = $request->input('tab', 'all');
        $perPage = $request->input('per_page', 10);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        // Cache key para carriers (datos estáticos)
        $carriersData = Cache::remember('equipment_carriers_list', 1800, function () {
            return Carrier::orderBy('name')->get();
        });
        
        // Cache key para conteos de vehículos (datos que cambian menos frecuentemente)
        $vehicleCounts = Cache::remember('vehicle_counts', 600, function () {
            return [
                'total' => Vehicle::count(),
                'active' => Vehicle::active()->count(),
                'out_of_service' => Vehicle::outOfService()->count(),
                'suspended' => Vehicle::suspended()->count()
            ];
        });

        // Base query for all vehicles
        $query = Vehicle::with(['carrier', 'driver', 'vehicleType', 'vehicleMake']);

        // Apply tab filters
        switch ($tab) {
            case 'active':
                // Solo vehículos con registro activo (no expirado)
                $query->where(function ($q) {
                    $q->whereNull('registration_expiration_date')
                        ->orWhere('registration_expiration_date', '>=', now()->format('Y-m-d'));
                });
                break;
            case 'out_of_service':
                // Solo vehículos con registro expirado
                $query->whereNotNull('registration_expiration_date')
                    ->where('registration_expiration_date', '<', now()->format('Y-m-d'));
                break;
            case 'suspended':
                // Mantener suspended usando el campo suspended
                $query->suspended();
                break;
                // Default 'all' tab doesn't need additional filtering
        }

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', '%' . $search . '%')
                    ->orWhere('model', 'like', '%' . $search . '%')
                    ->orWhere('vin', 'like', '%' . $search . '%')
                    ->orWhere('company_unit_number', 'like', '%' . $search . '%');
            });
        }

        // Apply carrier filter if provided
        if (!empty($carrierFilter)) {
            $query->where('carrier_id', $carrierFilter);
        }

        // Aplicar filtro de fecha inicial si se proporciona
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        // Aplicar filtro de fecha final si se proporciona
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get paginated results with ordering
        $query->orderBy('created_at', 'desc');
        $vehicles = $query->paginate($perPage);

        // Log performance
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::info('Equipment list loaded', [
            'execution_time_ms' => $executionTime,
            'filters' => compact('search', 'carrierFilter', 'tab', 'dateFrom', 'dateTo'),
            'total_vehicles' => $vehicles->total()
        ]);

        return view('admin.reports.equipment-list', [
            'vehicles' => $vehicles,
            'carriers' => $carriersData,
            'search' => $search,
            'carrierFilter' => $carrierFilter,
            'tab' => $tab,
            'perPage' => $perPage,
            'totalVehiclesCount' => $vehicleCounts['total'],
            'activeVehiclesCount' => $vehicleCounts['active'],
            'outOfServiceVehiclesCount' => $vehicleCounts['out_of_service'],
            'suspendedVehiclesCount' => $vehicleCounts['suspended']
        ]);
    }

    /**
     * Exportar a PDF el listado de equipamiento/vehículos.
     */
    public function equipmentListPdf(Request $request)
    {
        // Get filter parameters - usando los mismos filtros que en la vista
        $search = $request->input('search', '');
        $carrierFilter = $request->input('carrier', '');
        $tab = $request->input('tab', 'all');
        $dateFrom = $request->input('date_from'); // Filtro de fecha inicial
        $dateTo = $request->input('date_to');     // Filtro de fecha final

        // Base query for all vehicles
        $query = Vehicle::with(['carrier', 'driver', 'vehicleType', 'vehicleMake']);

        // Apply tab filters
        switch ($tab) {
            case 'active':
                $query->active();
                break;
            case 'out_of_service':
                $query->outOfService();
                break;
            case 'suspended':
                $query->suspended();
                break;
                // Default 'all' tab doesn't need additional filtering
        }

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', '%' . $search . '%')
                    ->orWhere('model', 'like', '%' . $search . '%')
                    ->orWhere('vin', 'like', '%' . $search . '%')
                    ->orWhere('company_unit_number', 'like', '%' . $search . '%');
            });
        }

        // Apply carrier filter if provided
        if (!empty($carrierFilter)) {
            $query->where('carrier_id', $carrierFilter);
            $carrier = Carrier::find($carrierFilter);
            $carrierName = $carrier ? $carrier->name : 'Unknown';
        } else {
            $carrierName = 'All';
        }

        // Aplicar filtro de fecha inicial si se proporciona
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        // Aplicar filtro de fecha final si se proporciona
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Obtener todos los vehículos sin paginación para el PDF
        $vehicles = $query->orderBy('created_at', 'desc')->get();

        // Configurar información para el PDF
        $date = now()->format('m/d/Y H:i');
        $filtros = [];

        // Agregar información de filtros aplicados
        if (!empty($tab) && $tab != 'all') {
            $tabNames = [
                'active' => 'Active',
                'out_of_service' => 'Out of Service',
                'suspended' => 'Suspended'
            ];
            $filtros[] = "Tab: " . ($tabNames[$tab] ?? ucfirst($tab));
        }

        if (!empty($search)) {
            $filtros[] = "Search: {$search}";
        }

        if (!empty($carrierFilter)) {
            $filtros[] = "Carrier: {$carrierName}";
        }

        if (!empty($dateFrom)) {
            $filtros[] = "Date From: {$dateFrom}";
        }

        if (!empty($dateTo)) {
            $filtros[] = "Date To: {$dateTo}";
        }

        $pdf = PDF::loadView('admin.reports.pdf.equipment-list-pdf', [
            'vehicles' => $vehicles,
            'date' => $date,
            'carrierName' => $carrierName,
            'totalVehicles' => $vehicles->count(),
            'filtros' => $filtros
        ]);

        // Personalizar el PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true
        ]);

        // Retornar el PDF para descarga
        return $pdf->download('listado_equipamiento_' . now()->format('Y-m-d_H-i') . '.pdf');
    }

    /**
     * Mostrar reporte de accidentes.
     */
    public function accidents(Request $request)
    {
        // Filtros
        $search = $request->get('search', '');
        $carrierFilter = $request->get('carrier');
        $driverId = $request->get('driver');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Query base
        $query = DriverAccident::with(['userDriverDetail.user', 'carrier'])
                ->select('driver_accidents.*');

        // Aplicar filtros
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('location', 'like', '%' . $search . '%')
                  ->orWhere('nature_of_accident', 'like', '%' . $search . '%')
                  ->orWhere('damage_description', 'like', '%' . $search . '%');
            });
        }

        if (!empty($carrierFilter)) {
            $query->where('carrier_id', $carrierFilter);
        }

        if (!empty($driverId)) {
            $query->where('driver_id', $driverId);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('accident_date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('accident_date', '<=', $dateTo);
        }

        // Obtener accidentes paginados
        $accidents = $query->orderBy('accident_date', 'desc')->paginate(10);

        // Para filtros y estadísticas
        $carriers = Carrier::orderBy('name')->get();
        $drivers = UserDriverDetail::with('user')->get();

        // Estadísticas
        $totalAccidents = DriverAccident::count();
        
        // Nota: Estas columnas no existen en la tabla, usamos valores por defecto
        $preventableAccidents = 0;
        $nonPreventableAccidents = 0;
        $withCitations = 0;

        // Verificar si es exportación a PDF
        if ($request->has('export') && $request->get('export') === 'pdf') {
            return $this->exportAccidentsPDF($query->get(), $search, $carrierFilter, $driverId, $dateFrom, $dateTo);
        }

        return view('admin.reports.accidents', compact(
            'accidents',
            'carriers',
            'drivers',
            'search',
            'carrierFilter',
            'driverId',
            'dateFrom', 
            'dateTo',   
            'totalAccidents',
            'preventableAccidents',
            'nonPreventableAccidents',
            'withCitations'
        ));
    }

    /**
     * Exportar reporte de accidentes a PDF
     *
     * @param Collection $accidents Colección de accidentes filtrados
     * @param string $search Término de búsqueda
     * @param int|null $carrierFilter ID del transportista
     * @param int|null $driverId ID del conductor
     * @param string|null $dateFrom Fecha de inicio
     * @param string|null $dateTo Fecha de fin
     * @return Response PDF para descarga
     */
    private function exportAccidentsPDF($accidents, $search, $carrierFilter, $driverId, $dateFrom, $dateTo)
    {
        // Obtener datos relacionados para mostrar nombres en vez de IDs
        $carrier = $carrierFilter ? Carrier::find($carrierFilter) : null;
        $driver = $driverId ? UserDriverDetail::with('user')->find($driverId) : null;

        // Preparar filtros para incluir en el PDF
        $filtros = [];
        if (!empty($search)) {
            $filtros[] = "Search: {$search}";
        }

        if (!empty($carrierFilter)) {
            $carrier = Carrier::find($carrierFilter);
            $filtros[] = "Carrier: " . ($carrier ? $carrier->name : 'Unknown');
        }

        if (!empty($driverId)) {
            $driver = UserDriverDetail::with('user')->find($driverId);
            $filtros[] = "Driver: " . ($driver && $driver->user ? $driver->user->name : 'Unknown');
        }

        if (!empty($dateFrom)) {
            $filtros[] = "From: {$dateFrom}";
        }

        if (!empty($dateTo)) {
            $filtros[] = "To: {$dateTo}";
        }

        // Cargar vista PDF
        $pdf = PDF::loadView('admin.reports.pdf.accidents-pdf', [
            'accidents' => $accidents,
            'date' => now()->format('m/d/Y H:i'),
            'totalAccidents' => $accidents->count(),
            'filtros' => $filtros
        ]);

        // Personalizar el PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true
        ]);

        // Descargar PDF
        return $pdf->download('accidents_report_' . now()->format('Y-m-d_H-i') . '.pdf');
    }
    
    /**
     * Mostrar el formulario para registrar un nuevo accidente.
     */
    public function registerAccident()
    {
        $drivers = UserDriverDetail::with(['user', 'carrier'])->get();
        $carriers = Carrier::where('status', Carrier::STATUS_ACTIVE)->get();

        return view('admin.reports.register-accident', compact('drivers', 'carriers'));
    }

    /**
     * Guardar un nuevo accidente.
     */
    public function storeAccident(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'carrier_id' => 'required|exists:carriers,id',
            'driver_id' => 'required|exists:user_driver_details,id',
            'accident_date' => 'required|date',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'damage_description' => 'nullable|string',
            'injury_description' => 'nullable|string',
            'police_report_number' => 'nullable|string|max:255',
            'citation_issued' => 'boolean',
            'preventable' => 'boolean',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);
        
        // Crear el accidente
        $accident = DriverAccident::create([
            'carrier_id' => $validated['carrier_id'],
            'driver_id' => $validated['driver_id'],
            'accident_date' => $validated['accident_date'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'damage_description' => $validated['damage_description'] ?? null,
            'injury_description' => $validated['injury_description'] ?? null,
            'police_report_number' => $validated['police_report_number'] ?? null,
            'citation_issued' => isset($validated['citation_issued']),
            'preventable' => isset($validated['preventable']),
        ]);

        // Manejar documentos subidos
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $accident->addMedia($file)
                    ->toMediaCollection('accident_documents');
            }
        }

        return redirect()->route('admin.reports.register-accident')
            ->with('success', 'Accidente registrado exitosamente.');
    }

    /**
     * Lista todos los accidentes.
     */
    public function accidentsList(Request $request)
    {
        $query = DriverAccident::with(['userDriverDetail.user', 'carrier']);

        // Filtrar por carrier si se proporciona un ID
        if ($request->has('carrier_id') && $request->carrier_id) {
            $query->where('carrier_id', $request->carrier_id);
        }

        // Filtrar por driver si se proporciona un ID
        if ($request->has('driver_id') && $request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        // Ordenar por fecha (más reciente primero)
        $query->orderBy('accident_date', 'desc');

        $accidents = $query->paginate(20);
        $carriers = Carrier::where('status', Carrier::STATUS_ACTIVE)->get();
        $drivers = UserDriverDetail::whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->get();

        return view('admin.reports.accidents-list', compact('accidents', 'carriers', 'drivers'));
    }

    /**
     * Obtener conductores activos por carrier (API)
     */
    public function getActiveDriversByCarrier($carrierId)
    {
        $drivers = UserDriverDetail::with('user')
            ->where('carrier_id', $carrierId)
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })
            ->get();

        return response()->json($drivers);
    }

    /**
     * Mostrar reporte de documentos de carriers con filtros avanzados.
     */
    public function carrierDocuments(Request $request)
    {
        // Obtener parámetros de filtro
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = $request->input('per_page', 20);

        // Consulta base para carriers con sus documentos
        $query = Carrier::with(['documents.documentType', 'membership'])
            ->withCount([
                'documents',
                'documents as approved_documents_count' => function ($q) {
                    $q->where('status', CarrierDocument::STATUS_APPROVED);
                },
                'documents as pending_documents_count' => function ($q) {
                    $q->where('status', CarrierDocument::STATUS_PENDING);
                },
                'documents as rejected_documents_count' => function ($q) {
                    $q->where('status', CarrierDocument::STATUS_REJECTED);
                }
            ]);

        // Aplicar filtro de búsqueda
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('dot_number', 'like', '%' . $search . '%')
                    ->orWhere('mc_number', 'like', '%' . $search . '%')
                    ->orWhere('ein_number', 'like', '%' . $search . '%');
            });
        }

        // Aplicar filtro de estado
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        // Aplicar filtro de fecha
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Ordenar por nombre
        $query->orderBy('name');

        // Obtener resultados paginados
        $carriers = $query->paginate($perPage);

        // Calcular estadísticas generales
        $totalCarriers = Carrier::count();
        $activeCarriers = Carrier::where('status', Carrier::STATUS_ACTIVE)->count();
        $pendingCarriers = Carrier::where('status', Carrier::STATUS_PENDING)->count();
        $inactiveCarriers = Carrier::where('status', Carrier::STATUS_INACTIVE)->count();

        // Estadísticas de documentos
        $totalDocuments = CarrierDocument::count();
        $approvedDocuments = CarrierDocument::where('status', CarrierDocument::STATUS_APPROVED)->count();
        $pendingDocuments = CarrierDocument::where('status', CarrierDocument::STATUS_PENDING)->count();
        $rejectedDocuments = CarrierDocument::where('status', CarrierDocument::STATUS_REJECTED)->count();

        // Obtener tipos de documentos para estadísticas adicionales
        $documentTypes = DocumentType::withCount('carrierDocuments')->get();

        return view('admin.reports.carrier-documents', compact(
            'carriers',
            'search',
            'statusFilter',
            'dateFrom',
            'dateTo',
            'perPage',
            'totalCarriers',
            'activeCarriers',
            'pendingCarriers',
            'inactiveCarriers',
            'totalDocuments',
            'approvedDocuments',
            'pendingDocuments',
            'rejectedDocuments',
            'documentTypes'
        ));
    }

    /**
     * Exportar reporte de documentos de carriers a PDF.
     */
    public function carrierDocumentsPdf(Request $request)
    {
        // Obtener parámetros de filtro
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Consulta base para carriers con sus documentos
        $query = Carrier::with(['documents.documentType', 'membership'])
            ->withCount([
                'documents',
                'documents as approved_documents_count' => function ($q) {
                    $q->where('status', CarrierDocument::STATUS_APPROVED);
                },
                'documents as pending_documents_count' => function ($q) {
                    $q->where('status', CarrierDocument::STATUS_PENDING);
                },
                'documents as rejected_documents_count' => function ($q) {
                    $q->where('status', CarrierDocument::STATUS_REJECTED);
                }
            ]);

        // Aplicar los mismos filtros que en la vista
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('dot_number', 'like', '%' . $search . '%')
                    ->orWhere('mc_number', 'like', '%' . $search . '%')
                    ->orWhere('ein_number', 'like', '%' . $search . '%');
            });
        }

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Obtener todos los carriers (sin paginación para PDF)
        $carriers = $query->orderBy('name')->get();

        // Preparar datos para los filtros aplicados
        $filtros = [];
        if (!empty($search)) {
            $filtros[] = 'Search: ' . $search;
        }
        if (!empty($statusFilter)) {
            $statusLabels = [
                Carrier::STATUS_ACTIVE => 'Active',
                Carrier::STATUS_PENDING => 'Pending',
                Carrier::STATUS_INACTIVE => 'Inactive'
            ];
            $filtros[] = 'Status: ' . ($statusLabels[$statusFilter] ?? $statusFilter);
        }
        if (!empty($dateFrom)) {
            $filtros[] = 'From: ' . $dateFrom;
        }
        if (!empty($dateTo)) {
            $filtros[] = 'To: ' . $dateTo;
        }

        // Get document types for progress calculation
        $documentTypes = DocumentType::all();
        
        $pdf = PDF::loadView('admin.reports.pdf.carrier-documents-pdf', [
            'carriers' => $carriers,
            'date' => date('d/m/Y H:i'),
            'totalCarriers' => $carriers->count(),
            'filtros' => $filtros,
            'documentTypes' => $documentTypes
        ]);

        // Establecer opciones para el PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 10,
            'margin_bottom' => 10
        ]);

        // Generar el nombre del archivo
        $filename = 'documentos_carriers_' . date('YmdHis') . '.pdf';

        // Devolver el PDF para descarga
        return $pdf->download($filename);
    }

    /**
     * Generar reporte de prospectos de conductores.
     */
    public function driverProspects(Request $request)
    {
        // Obtener todos los carriers ACTIVOS para el filtro, ordenados por nombre
        $carriers = Carrier::where('status', Carrier::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        // Construir la consulta base incluyendo las relaciones necesarias
        $query = DriverApplication::with(['user', 'userDriverDetail', 'userDriverDetail.carrier', 'verifications'])
            ->where('status', '!=', DriverApplication::STATUS_APPROVED); // No incluir los ya aprobados

        // Aplicar filtro de búsqueda si se proporciona
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                    // Eliminada la búsqueda en phone de users ya que esta columna no existe
                })
                    ->orWhereHas('userDriverDetail', function ($detailQuery) use ($searchTerm) {
                        $detailQuery->where('last_name', 'like', $searchTerm)
                            ->orWhere('phone', 'like', $searchTerm);
                    });
            });
        }

        // Filtrar por carrier si se proporciona un ID
        if ($request->has('carrier_id') && $request->carrier_id) {
            $query->whereHas('userDriverDetail', function ($q) use ($request) {
                $q->where('carrier_id', $request->carrier_id);
            });
        }

        // Filtrar por estado si se proporciona
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Si se especifica un año, filtrar por ese año
        if ($request->has('year') && $request->year) {
            $query->whereYear('created_at', $request->year);
        }

        // Filtrar por rango de fechas si se proporcionan
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Obtener los años disponibles para el filtro
        $years = DriverApplication::select(DB::raw('YEAR(created_at) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Obtener los prospectos paginados
        $prospects = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reports.driver-prospects', compact('prospects', 'carriers', 'years'));
    }

    /**
     * Exportar a PDF el reporte de prospectos de conductores.
     */
    public function driverProspectsPdf(Request $request)
    {
        // Construir la consulta base
        $query = DriverApplication::with(['user', 'userDriverDetail', 'userDriverDetail.carrier', 'verifications'])
            ->where('status', '!=', DriverApplication::STATUS_APPROVED); // No incluir los ya aprobados

        // Filtrar por carrier si se proporciona un ID
        $carrierName = 'All carriers';
        if ($request->has('carrier_id') && $request->carrier_id) {
            $carrier = Carrier::find($request->carrier_id);
            if ($carrier) {
                $carrierName = $carrier->name;
            }

            $query->whereHas('userDriverDetail', function ($q) use ($request) {
                $q->where('carrier_id', $request->carrier_id);
            });
        }

        // Filtrar por estado si se proporciona
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtrar por año si se proporciona
        if ($request->has('year') && $request->year) {
            $query->whereYear('created_at', $request->year);
        }

        // Filtrar por fechas si se proporcionan
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Obtener todos los prospectos
        $prospects = $query->orderBy('created_at', 'desc')->get();

        // Preparar datos para los filtros aplicados
        $filtros = [];
        if ($request->has('status') && $request->status) {
            $statusLabels = [
                'draft' => 'Borrador',
                'pending' => 'Pendiente',
                'rejected' => 'Rechazado'
            ];
            $filtros[] = 'Status: ' . ($statusLabels[$request->status] ?? $request->status);
        }

        if ($request->has('year') && $request->year) {
            $filtros[] = 'Year: ' . $request->year;
        }

        if ($request->has('date_from') && $request->date_from) {
            $filtros[] = 'From: ' . $request->date_from;
        }

        if ($request->has('date_to') && $request->date_to) {
            $filtros[] = 'To: ' . $request->date_to;
        }

        $pdf = PDF::loadView('admin.reports.pdf.driver-prospects-pdf', [
            'prospects' => $prospects,
            'carrierName' => $carrierName,
            'date' => date('d/m/Y H:i'),
            'totalProspects' => $prospects->count(),
            'filtros' => $filtros
        ]);

        // Establecer opciones para el PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 10,
            'margin_bottom' => 10
        ]);

        // Generar el nombre del archivo
        $filename = 'prospectos_conductores_' . date('YmdHis') . '.pdf';

        // Devolver el PDF para descarga
        return $pdf->download($filename);
    }

    /**
     * Descargar documentos de un carrier específico.
     */
    public function downloadCarrierDocuments(Carrier $carrier)
    {
        // Verificar que el carrier existe y está activo
        if (!$carrier || $carrier->status !== Carrier::STATUS_ACTIVE) {
            return redirect()->back()->with('error', 'Carrier no encontrado o inactivo.');
        }

        // Obtener todos los documentos del carrier desde la relación documents
        $carrierDocuments = $carrier->documents()->get();

        if ($carrierDocuments->isEmpty()) {
            return redirect()->back()->with('warning', 'No se encontraron documentos para este carrier.');
        }

        // Recopilar los archivos de media asociados a cada documento
        $mediaFiles = collect();
        foreach ($carrierDocuments as $document) {
            // Obtener directamente los media items desde la base de datos para asegurar que tenemos acceso a todos
            $media = Media::where('model_type', CarrierDocument::class)
                          ->where('model_id', $document->id)
                          ->where('collection_name', 'carrier_documents')
                          ->first();
            
            if ($media && file_exists($media->getPath())) {
                $mediaFiles->push($media);
            }
        }

        if ($mediaFiles->isEmpty()) {
            return redirect()->back()->with('warning', 'No se encontraron archivos para este carrier.');
        }

        // Si solo hay un documento, descargarlo directamente
        if ($mediaFiles->count() === 1) {
            $media = $mediaFiles->first();
            return Response::download($media->getPath(), $media->file_name);
        }

        // Si hay múltiples documentos, crear un ZIP
        $zipFileName = 'documentos_' . $carrier->name . '_' . now()->format('Y-m-d_H-i') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Crear directorio temporal si no existe
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($mediaFiles as $media) {
                // Verificar nuevamente que el archivo existe
                if (file_exists($media->getPath())) {
                    // Buscar el documento asociado para obtener el tipo de documento
                    $document = $carrierDocuments->firstWhere('id', $media->model_id);
                    
                    // Crear un nombre de archivo descriptivo
                    $documentType = $document && $document->documentType ? $document->documentType->name : 'Documento';
                    $fileName = $documentType . ' - ' . $media->file_name;
                    
                    $zip->addFile($media->getPath(), $fileName);
                }
            }
            $zip->close();

            // Descargar el ZIP y eliminarlo después
            return Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Error al crear el archivo ZIP.');
    }
}
