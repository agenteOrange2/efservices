<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Carrier;
use App\Models\DocumentType;
use App\Models\CarrierDocument;
use App\Models\UserDriverDetail;
use Carbon\Carbon;

class DashboardStats extends Component
{
    public $users;
    public $totalCarriers;
    public $activeCarriers;
    public $pendingCarriers;
    public $totalDrivers;
    public $activeDrivers;
    public $pendingDrivers;
    public $totalDocuments;
    public $pendingDocuments;
    public $approvedDocuments;
    public $recentCarriers;
    public $recentDrivers;
    public $recentDocuments;
    public $monthlyStats;

    public function mount()
    {

        $allUsers = User::with(['roles', 'carrierDetails.carrier', 'driverDetails.carrier'])->get();

        $this->users = [];

        foreach ($allUsers as $user) {
            $role = $user->roles->first();
            if ($role) {
                try {
                    $created = $user->created_at ? $user->created_at->format('M d, Y') : 'N/A';
                } catch (\Exception $e) {
                    $created = 'N/A';
                }

                $this->users[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role->name,
                    'created_at' => $created,
                    'details' => $role->name === 'superadmin' ? null : ($user->carrierDetails ?? $user->driverDetails)
                ];
            }
        }

        $this->totalCarriers = Carrier::count();
        $this->activeCarriers = Carrier::where('status', 1)->count();
        $this->pendingCarriers = Carrier::where('status', 0)->count();

        $this->totalDrivers = UserDriverDetail::count();
        $this->activeDrivers = UserDriverDetail::where('status', 1)->count();
        $this->pendingDrivers = UserDriverDetail::where('status', 0)->count();

        $this->totalDocuments = CarrierDocument::count();
        $this->pendingDocuments = CarrierDocument::where('status', 0)->count();
        $this->approvedDocuments = CarrierDocument::where('status', 1)->count();

        $this->recentCarriers = Carrier::latest()->take(5)->get();
        $this->recentDrivers = UserDriverDetail::with(['user', 'carrier'])->latest()->take(5)->get();
        $this->recentDocuments = CarrierDocument::with(['carrier', 'documentType'])->latest()->take(5)->get();
    }

    // In DashboardStats.php, add:
    public function getMonthlyStats()
    {
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('M');
        });

        $carrierStats = $months->map(function ($month) {
            return Carrier::whereMonth('created_at', Carbon::parse($month)->month)->count();
        });

        $driverStats = $months->map(function ($month) {
            return UserDriverDetail::whereMonth('created_at', Carbon::parse($month)->month)->count();
        });

        return [
            'labels' => $months,
            'carriers' => $carrierStats,
            'drivers' => $driverStats
        ];
    }
    public function getDocumentStats()
    {
        $timeframes = collect(range(6, 0))->map(fn($i) => Carbon::now()->subDays($i)->format('M d'));

        $approved = $timeframes->map(function ($date) {
            return CarrierDocument::whereDate('created_at', Carbon::parse($date))
                ->where('status', 1)
                ->count();
        });

        $pending = $timeframes->map(function ($date) {
            return CarrierDocument::whereDate('created_at', Carbon::parse($date))
                ->where('status', 0)
                ->count();
        });

        return [
            'labels' => $timeframes,
            'approved' => $approved,
            'pending' => $pending
        ];
    }

    // Add to DashboardStats.php
    public function getDriverActivityStats()
    {
        return [
            'labels' => ['8am', '10am', '12pm', '2pm', '4pm', '6pm'],
            'active' => [5, 12, 15, 18, 10, 7],
            'inactive' => [20, 15, 10, 8, 15, 18]
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard-stats');
    }
}
