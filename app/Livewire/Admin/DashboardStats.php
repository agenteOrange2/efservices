<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Carrier;
use App\Models\DocumentType;
use App\Models\CarrierDocument;
use App\Models\UserDriverDetail;
use App\Models\UserCarrierDetail;
use Carbon\Carbon;

class DashboardStats extends Component
{
    // Totales
    public $totalSuperAdmins = 0;
    public $totalCarriers = 0;
    public $totalUserCarriers = 0;
    public $totalUserDrivers = 0;
    public $totalDocuments = 0;

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

    // Chart data
    public $chartData = [];

    public function mount()
    {
        $this->loadData();
        $this->prepareChartData();


        logger('Status counts:', [
            'activeCarriers' => $this->activeUserCarriers,
            'pendingCarriers' => $this->pendingUserCarriers,
            'inactiveCarriers' => $this->inactiveUserCarriers,
            'activeDrivers' => $this->activeUserDrivers,
            'pendingDrivers' => $this->pendingUserDrivers,
            'inactiveDrivers' => $this->inactiveUserDrivers,
        ]);
    }

    public function loadData()
    {
        // Totales principales
        $this->totalSuperAdmins = User::role('superadmin')->count();
        $this->totalCarriers = Carrier::count();
        $this->totalUserCarriers = UserCarrierDetail::count();
        $this->totalUserDrivers = UserDriverDetail::count();
        $this->totalDocuments = CarrierDocument::count();

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
                    'role' => 'driver',
                    'carrier' => $userDriver->carrier?->name ?? 'N/A',
                    'status' => $this->getStatusBadge($userDriver->status),
                    'created_at' => $userDriver->created_at->format('d M Y'),
                ];
            });


                    // Al cargar datos nuevos, emitir evento para actualizar la gráfica
        $this->dispatch('refreshChart', [
            'activeUserCarriers' => $this->activeUserCarriers,
            'pendingUserCarriers' => $this->pendingUserCarriers,
            'inactiveUserCarriers' => $this->inactiveUserCarriers,
        ]);
    }

    public function prepareChartData()
    {
        $this->chartData = [
            'label' => 'User Carriers Status',
            'values' => [$this->activeUserCarriers, $this->inactiveUserCarriers,  $this->pendingUserCarriers ]
        ];
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

    public function render()
    {
        return view('livewire.admin.dashboard-stats');
    }
}