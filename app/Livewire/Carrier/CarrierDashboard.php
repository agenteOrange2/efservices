<?php

namespace App\Livewire\Carrier;

use Livewire\Component;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\UserDriverDetail;
use Carbon\Carbon;

class CarrierDashboard extends Component
{
    public Carrier $carrier;
    public $documentStats;
    public $driversCount;
    public $vehiclesCount;
    public $recentDrivers;
    public $recentDocuments;
    public $documentTypeCounts;
    public $documentStatusCounts;
    
    // Nuevas propiedades para métricas avanzadas
    public $advancedMetrics;
    public $alertsData;
    public $trendsData;
    public $chartData;
    public $quickActions;

    public function mount(Carrier $carrier = null)
    {
        // Si no se proporciona un carrier, obtenemos el carrier del usuario autenticado
        if (!$carrier) {
            $this->carrier = \Illuminate\Support\Facades\Auth::user()->carrierDetails->carrier;
        } else {
            $this->carrier = $carrier;
        }

        $this->loadData();
    }

    protected function loadData()
    {
        // Conteo de conductores y vehículos
        $this->driversCount = $this->carrier->userDrivers()->count();
        $this->vehiclesCount = $this->carrier->vehicles()->count() ?? 0;

        // Estadísticas de documentos
        $this->documentStats = [
            'total' => $this->carrier->documents()->count(),
            'pending' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_PENDING)->count(),
            'approved' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_APPROVED)->count(),
            'rejected' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_REJECTED)->count(),
        ];

        // Conteo de documentos por tipo
        $this->documentTypeCounts = $this->carrier->documents()
            ->join('document_types', 'carrier_documents.document_type_id', '=', 'document_types.id')
            ->selectRaw('document_types.name, count(*) as count')
            ->groupBy('document_types.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        // Conteo de documentos por estado
        $this->documentStatusCounts = [
            'Pendiente' => $this->documentStats['pending'],
            'Aprobado' => $this->documentStats['approved'],
            'Rechazado' => $this->documentStats['rejected'],
            'En Proceso' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_IN_PROCESS)->count(),
        ];

        // Obtener conductores recientes
        $this->recentDrivers = $this->carrier->userDrivers()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Obtener documentos recientes
        $this->recentDocuments = $this->carrier->documents()
            ->with('documentType')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Cargar métricas avanzadas
        $this->loadAdvancedMetrics();
        $this->loadAlertsData();
        $this->loadTrendsData();
        $this->loadChartData();
        $this->loadQuickActions();
    }

    // En Livewire 3, podemos usar directamente una propiedad computada con el prefijo "get"
    public function getMembershipLimitsProperty()
    {
        $membership = $this->carrier->membership;
        return [
            'maxDrivers' => $membership ? $membership->max_drivers : 0,
            'maxVehicles' => $membership ? $membership->max_vehicles : 0,
            'driversPercentage' => $membership && $membership->max_drivers > 0 
                ? round(($this->driversCount / $membership->max_drivers) * 100) 
                : 0,
            'vehiclesPercentage' => $membership && $membership->max_vehicles > 0 
                ? round(($this->vehiclesCount / $membership->max_vehicles) * 100) 
                : 0,
        ];
    }

    protected function loadAdvancedMetrics()
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        $lastWeek = $now->copy()->subWeek();

        // Métricas de documentos avanzadas
        $documentsThisMonth = $this->carrier->documents()->where('created_at', '>=', $lastMonth)->count();
        $documentsLastMonth = $this->carrier->documents()
            ->where('created_at', '>=', $lastMonth->copy()->subMonth())
            ->where('created_at', '<', $lastMonth)
            ->count();

        // Tiempo promedio de aprobación
        $avgApprovalTime = $this->carrier->documents()
            ->where('status', CarrierDocument::STATUS_APPROVED)
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->first();

        // Conductores activos vs inactivos
        $activeDrivers = $this->carrier->userDrivers()->where('status', 1)->count();
        $inactiveDrivers = $this->driversCount - $activeDrivers;

        // Documentos próximos a vencer (simulado - necesitarías un campo expiration_date)
        $expiringDocuments = $this->carrier->documents()
            ->where('status', CarrierDocument::STATUS_APPROVED)
            ->where('created_at', '<=', $now->copy()->subMonths(11)) // Documentos de hace 11+ meses
            ->count();

        $this->advancedMetrics = [
            'documentsThisMonth' => $documentsThisMonth,
            'documentsGrowth' => $documentsLastMonth > 0 
                ? round((($documentsThisMonth - $documentsLastMonth) / $documentsLastMonth) * 100, 1)
                : 0,
            'avgApprovalDays' => $avgApprovalTime ? round($avgApprovalTime->avg_days, 1) : 0,
            'activeDrivers' => $activeDrivers,
            'inactiveDrivers' => $inactiveDrivers,
            'expiringDocuments' => $expiringDocuments,
            'completionRate' => $this->documentStats['total'] > 0 
                ? round(($this->documentStats['approved'] / $this->documentStats['total']) * 100, 1)
                : 0,
            'pendingRate' => $this->documentStats['total'] > 0 
                ? round(($this->documentStats['pending'] / $this->documentStats['total']) * 100, 1)
                : 0,
        ];
    }

    protected function loadAlertsData()
    {
        $alerts = [];

        // Alerta de documentos pendientes
        if ($this->documentStats['pending'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'AlertTriangle',
                'title' => 'Documentos Pendientes',
                'message' => "Tienes {$this->documentStats['pending']} documentos pendientes de revisión.",
                'action' => 'Ver Documentos',
                'url' => route('carrier.documents.index', $this->carrier)
            ];
        }

        // Alerta de límite de conductores
        $membership = $this->carrier->membership;
        if ($membership && $this->driversCount >= $membership->max_drivers * 0.9) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'Users',
                'title' => 'Límite de Conductores',
                'message' => "Estás cerca del límite de conductores ({$this->driversCount}/{$membership->max_drivers}).",
                'action' => 'Actualizar Plan',
                'url' => '#'
            ];
        }

        // Alerta de documentos rechazados
        if ($this->documentStats['rejected'] > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'XCircle',
                'title' => 'Documentos Rechazados',
                'message' => "Tienes {$this->documentStats['rejected']} documentos rechazados que requieren atención.",
                'action' => 'Revisar',
                'url' => route('carrier.documents.index', $this->carrier)
            ];
        }

        // Alerta de documentos próximos a vencer
        if ($this->advancedMetrics['expiringDocuments'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'Clock',
                'title' => 'Documentos por Vencer',
                'message' => "Tienes {$this->advancedMetrics['expiringDocuments']} documentos que podrían necesitar renovación.",
                'action' => 'Revisar',
                'url' => route('carrier.documents.index', $this->carrier)
            ];
        }

        $this->alertsData = $alerts;
    }

    protected function loadTrendsData()
    {
        $last6Months = [];
        $now = Carbon::now();

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $documentsCount = $this->carrier->documents()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $driversCount = $this->carrier->userDrivers()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $last6Months[] = [
                'month' => $month->format('M Y'),
                'documents' => $documentsCount,
                'drivers' => $driversCount,
            ];
        }

        $this->trendsData = $last6Months;
    }

    protected function loadChartData()
    {
        // Datos para gráfico de dona de estados de documentos
        $this->chartData = [
            'documentStatus' => [
                'labels' => ['Aprobados', 'Pendientes', 'Rechazados', 'En Proceso'],
                'data' => [
                    $this->documentStats['approved'],
                    $this->documentStats['pending'],
                    $this->documentStats['rejected'],
                    $this->documentStatusCounts['En Proceso']
                ],
                'colors' => ['#10B981', '#F59E0B', '#EF4444', '#3B82F6']
            ],
            'driversStatus' => [
                'labels' => ['Activos', 'Inactivos'],
                'data' => [
                    $this->advancedMetrics['activeDrivers'],
                    $this->advancedMetrics['inactiveDrivers']
                ],
                'colors' => ['#10B981', '#6B7280']
            ]
        ];
    }

    protected function loadQuickActions()
    {
        $this->quickActions = [
            [
                'title' => 'Agregar Conductor',
                'icon' => 'UserPlus',
                // 'url' => route('carrier.user_drivers.create', $this->carrier),
                'color' => 'primary'
            ],
            [
                'title' => 'Subir Documento',
                'icon' => 'Upload',
                // 'url' => route('carrier.documents.create', $this->carrier),
                'color' => 'success'
            ],
            [
                'title' => 'Ver Reportes',
                'icon' => 'BarChart3',
                'url' => '#',
                'color' => 'info'
            ],
            [
                'title' => 'Configuración',
                'icon' => 'Settings',
                'url' => route('carrier.profile.edit'),
                'color' => 'secondary'
            ]
        ];
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('dataRefreshed');
    }

    public function render()
    {
        return view('livewire.carrier.carrier-dashboard');
    }
}
