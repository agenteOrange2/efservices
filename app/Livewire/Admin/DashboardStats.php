<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Carrier;
use App\Models\DocumentType;
use App\Models\CarrierDocument;
use App\Models\UserDriverDetail;
use App\Models\UserCarrierDetail;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DashboardStats extends Component
{
    // Parámetros de filtro
    public $dateRange = 'daily';
    public $customDateStart = null;
    public $customDateEnd = null;
    public $showCustomDateFilter = false;
    
    // Totales
    public $totalSuperAdmins = 0;
    public $totalCarriers = 0;
    public $totalUserCarriers = 0;
    public $totalUserDrivers = 0;
    public $totalDocuments = 0;
    public $totalVehicles = 0;
    public $totalMaintenance = 0;
    public $activeVehicles = 0;
    public $suspendedVehicles = 0;
    public $outOfServiceVehicles = 0;
    public $pendingMaintenance = 0;
    public $completedMaintenance = 0;
    public $overdueMaintenance = 0;
    public $upcomingMaintenance = 0;

    // Status totals for UserCarrier
    public $activeUserCarriers = 0;
    public $pendingUserCarriers = 0;
    public $inactiveUserCarriers = 0;

    // Status totals for UserDriver
    public $activeUserDrivers = 0;
    public $pendingUserDrivers = 0;
    public $inactiveUserDrivers = 0;

    // Recent data
    public $recentCarriers = [];
    public $recentUserCarriers = [];
    public $recentUserDrivers = [];
    public $recentVehicles = [];
    public $recentMaintenance = [];

    // Chart data
    public $chartData = [];

    public function mount()
    {
        // Inicializar fechas por defecto
        $this->customDateStart = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->customDateEnd = Carbon::now()->format('Y-m-d');
        
        $this->loadData();
        $this->prepareChartData();
    }
    
    public function updatedDateRange()
    {
        if ($this->dateRange === 'custom') {
            $this->showCustomDateFilter = true;
        } else {
            $this->showCustomDateFilter = false;
            $this->loadData();
            $this->prepareChartData();
        }
    }
    
    public function applyCustomDateFilter()
    {
        $this->loadData();
        $this->prepareChartData();
    }
    
    public function exportPdf()
    {
        $data = [
            'dateRange' => $this->getDateRangeLabel(),
            'totalVehicles' => $this->totalVehicles,
            'activeVehicles' => $this->activeVehicles,
            'suspendedVehicles' => $this->suspendedVehicles,
            'outOfServiceVehicles' => $this->outOfServiceVehicles,
            'totalMaintenance' => $this->totalMaintenance,
            'pendingMaintenance' => $this->pendingMaintenance,
            'completedMaintenance' => $this->completedMaintenance,
            'overdueMaintenance' => $this->overdueMaintenance,
            'upcomingMaintenance' => $this->upcomingMaintenance,
            'recentVehicles' => $this->recentVehicles,
            'recentMaintenance' => $this->recentMaintenance,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        
        $pdf = PDF::loadView('admin.reports.dashboard-pdf', $data);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "dashboard-report-{$this->dateRange}.pdf"
        );
    }
    
    private function getDateRangeLabel()
    {
        return match($this->dateRange) {
            'daily' => 'Daily - ' . Carbon::now()->format('Y-m-d'),
            'weekly' => 'Weekly - ' . Carbon::now()->startOfWeek()->format('Y-m-d') . ' to ' . Carbon::now()->endOfWeek()->format('Y-m-d'),
            'monthly' => 'Monthly - ' . Carbon::now()->startOfMonth()->format('Y-m-d') . ' to ' . Carbon::now()->endOfMonth()->format('Y-m-d'),
            'yearly' => 'Yearly - ' . Carbon::now()->startOfYear()->format('Y-m-d') . ' to ' . Carbon::now()->endOfYear()->format('Y-m-d'),
            'custom' => 'Custom - ' . $this->customDateStart . ' to ' . $this->customDateEnd,
            default => 'All Time',
        };
    }

    public function loadData()
    {
        // Establecer fechas según el filtro seleccionado
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        
        // Totales principales
        $this->totalSuperAdmins = User::role('superadmin')->count();
        $this->totalCarriers = Carrier::count();
        $this->totalUserCarriers = UserCarrierDetail::count();
        $this->totalUserDrivers = UserDriverDetail::count();
        $this->totalDocuments = CarrierDocument::count();
        
        // Totales de vehículos y mantenimiento
        $vehicleQuery = Vehicle::query();
        $maintenanceQuery = VehicleMaintenance::query();
        
        // Aplicar filtros de fecha si corresponde
        if ($startDate && $endDate) {
            $vehicleQuery->whereBetween('created_at', [$startDate, $endDate]);
            $maintenanceQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $this->totalVehicles = $vehicleQuery->count();
        $this->totalMaintenance = $maintenanceQuery->count();
        
        // Estados de vehículos
        $this->activeVehicles = Vehicle::active()->count();
        $this->suspendedVehicles = Vehicle::suspended()->count();
        $this->outOfServiceVehicles = Vehicle::outOfService()->count();
        
        // Estados de mantenimiento
        $this->pendingMaintenance = VehicleMaintenance::pending()->count();
        $this->completedMaintenance = VehicleMaintenance::completed()->count();
        $this->overdueMaintenance = VehicleMaintenance::overdue()->count();
        $this->upcomingMaintenance = VehicleMaintenance::upcoming()->count();

        // Status totals for UserCarrier
        $this->inactiveUserCarriers = UserCarrierDetail::where('status', 0)->count();
        $this->activeUserCarriers = UserCarrierDetail::where('status', 1)->count();
        $this->pendingUserCarriers = UserCarrierDetail::where('status', 2)->count();

        // Status totals for UserDriver
        $this->inactiveUserDrivers = UserDriverDetail::where('status', 0)->count();
        $this->activeUserDrivers = UserDriverDetail::where('status', 1)->count();
        $this->pendingUserDrivers = UserDriverDetail::where('status', 2)->count();

        // Recent Carriers
        $this->recentCarriers = Carrier::with(['membership'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($carrier) {
                return [
                    'id' => $carrier->id,
                    'name' => $carrier->name,
                    'membership' => $carrier->membership?->name ?? 'N/A',
                    'status' => $this->getStatusBadge($carrier->status),
                    'created_at' => $carrier->created_at->format('d M Y'),
                ];
            });
            
        // Recent Vehicles
        $this->recentVehicles = Vehicle::with(['carrier'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'vin' => $vehicle->vin,
                    'carrier' => $vehicle->carrier?->name ?? 'N/A',
                    'status' => $this->getVehicleStatusBadge($vehicle->status),
                    'created_at' => $vehicle->created_at->format('d M Y'),
                ];
            });
            
        // Recent Maintenance
        $this->recentMaintenance = VehicleMaintenance::with(['vehicle'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'vehicle' => $maintenance->vehicle ? ($maintenance->vehicle->make . ' ' . $maintenance->vehicle->model) : 'N/A',
                    'service_date' => $maintenance->service_date ? $maintenance->service_date->format('d M Y') : 'N/A',
                    'next_service_date' => $maintenance->next_service_date ? $maintenance->next_service_date->format('d M Y') : 'N/A',
                    'status' => $this->getMaintenanceStatusBadge($maintenance),
                    'cost' => '$' . number_format($maintenance->cost, 2),
                ];
            });

        // Recent User Carriers
        $this->recentUserCarriers = UserCarrierDetail::with(['user', 'carrier'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($userCarrier) {
                return [
                    'id' => $userCarrier->id,
                    'name' => $userCarrier->user?->name ?? 'N/A',
                    'email' => $userCarrier->user?->email ?? 'N/A',
                    'role' => 'user_carrier',
                    'carrier' => $userCarrier->carrier?->name ?? 'N/A',
                    'status' => $this->getStatusBadge($userCarrier->status),
                    'created_at' => $userCarrier->created_at->format('d M Y'),
                ];
            });

        // Recent User Drivers
        $this->recentUserDrivers = UserDriverDetail::with(['user', 'carrier'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($userDriver) {
                return [
                    'id' => $userDriver->id,
                    'name' => $userDriver->user?->name ?? 'N/A',
                    'email' => $userDriver->user?->email ?? 'N/A',
                    'role' => 'user_driver',
                    'carrier' => $userDriver->carrier?->name ?? 'N/A',
                    'status' => $this->getStatusBadge($userDriver->status),
                    'created_at' => $userDriver->created_at->format('d M Y'),
                ];
            });


        // Al cargar datos nuevos, emitir evento para actualizar las gráficas
        $this->dispatch('refreshChart', [
            'activeUserCarriers' => $this->activeUserCarriers,
            'pendingUserCarriers' => $this->pendingUserCarriers,
            'inactiveUserCarriers' => $this->inactiveUserCarriers,
        ]);
        
        $this->dispatch('refreshVehicleChart', [
            'activeVehicles' => $this->activeVehicles,
            'suspendedVehicles' => $this->suspendedVehicles,
            'outOfServiceVehicles' => $this->outOfServiceVehicles,
        ]);
        
        $this->dispatch('refreshMaintenanceChart', [
            'pendingMaintenance' => $this->pendingMaintenance,
            'completedMaintenance' => $this->completedMaintenance,
            'overdueMaintenance' => $this->overdueMaintenance,
            'upcomingMaintenance' => $this->upcomingMaintenance,
        ]);
    }

    public function prepareChartData()
    {
        $this->chartData = [
            'label' => 'User Carriers Status',
            'values' => [$this->activeUserCarriers, $this->inactiveUserCarriers, $this->pendingUserCarriers]
        ];
    }
    
    private function getStartDate()
    {
        return match($this->dateRange) {
            'daily' => Carbon::today(),
            'weekly' => Carbon::now()->startOfWeek(),
            'monthly' => Carbon::now()->startOfMonth(),
            'yearly' => Carbon::now()->startOfYear(),
            'custom' => Carbon::parse($this->customDateStart),
            default => null,
        };
    }
    
    private function getEndDate()
    {
        return match($this->dateRange) {
            'daily' => Carbon::today()->endOfDay(),
            'weekly' => Carbon::now()->endOfWeek(),
            'monthly' => Carbon::now()->endOfMonth(),
            'yearly' => Carbon::now()->endOfYear(),
            'custom' => Carbon::parse($this->customDateEnd)->endOfDay(),
            default => null,
        };
    }
    private function getStatusBadge($status)
    {
        return match ($status) {
            0 => ['label' => 'Inactive', 'class' => 'bg-danger/20 text-danger rounded-full px-2 py-1'],
            1 => ['label' => 'Active', 'class' => 'bg-success/20 text-success rounded-full px-2 py-1'],
            2 => ['label' => 'Pending', 'class' => 'bg-warning/20 text-warning rounded-full px-2 py-1'],
            default => ['label' => 'Unknown', 'class' => 'bg-slate-200 text-slate-600 rounded-full px-2 py-1'],
        };
    }
    
    private function getVehicleStatusBadge($status)
    {
        return match ($status) {
            'active' => ['label' => 'Active', 'class' => 'bg-success/20 text-success rounded-full px-2 py-1'],
            'out_of_service' => ['label' => 'Out of Service', 'class' => 'bg-danger/20 text-danger rounded-full px-2 py-1'],
            'suspended' => ['label' => 'Suspended', 'class' => 'bg-warning/20 text-warning rounded-full px-2 py-1'],
            default => ['label' => 'Unknown', 'class' => 'bg-slate-200 text-slate-600 rounded-full px-2 py-1'],
        };
    }
    
    private function getMaintenanceStatusBadge($maintenance)
    {
        if ($maintenance->isCompleted()) {
            return ['label' => 'Completed', 'class' => 'bg-success/20 text-success rounded-full px-2 py-1'];
        } elseif ($maintenance->isOverdue()) {
            return ['label' => 'Overdue', 'class' => 'bg-danger/20 text-danger rounded-full px-2 py-1'];
        } elseif ($maintenance->isUpcoming()) {
            return ['label' => 'Upcoming', 'class' => 'bg-warning/20 text-warning rounded-full px-2 py-1'];
        } else {
            return ['label' => 'Pending', 'class' => 'bg-primary/20 text-primary rounded-full px-2 py-1'];
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard-stats');
    }
}