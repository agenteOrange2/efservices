<?php

namespace App\Livewire\Carrier;

use App\Models\Carrier;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Auth;

class CarrierDriversList extends Component
{
    use WithPagination;
    
    public $search = '';
    public $statusFilter = '';
    
    public function mount()
    {
        // No necesitamos guardar el carrier como propiedad pública
        // Lo obtendremos cuando sea necesario a través de propiedades computadas
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function getCarrierProperty()
    {
        return Auth::user()->carrierDetails->carrier;
    }
    
    public function getDriversProperty()
    {
        $query = UserDriverDetail::query()
            ->where('carrier_id', $this->carrier->id)
            ->with('user');
            
        // Aplicar filtros de búsqueda
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->whereHas('user', function($userQuery) {
                    $userQuery->where('name', 'like', "%{$this->search}%")
                              ->orWhere('email', 'like', "%{$this->search}%");
                })
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }
        
        // Filtrar por estado
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
    
    public function getMembershipStatsProperty()
    {
        $maxDrivers = $this->carrier->membership->max_drivers ?? 1;
        $currentDrivers = UserDriverDetail::where('carrier_id', $this->carrier->id)->count();
        
        return [
            'maxDrivers' => $maxDrivers,
            'currentDrivers' => $currentDrivers,
            'percentage' => $maxDrivers > 0 ? ($currentDrivers / $maxDrivers) * 100 : 0,
            'exceededLimit' => $currentDrivers >= $maxDrivers
        ];
    }
    
    public function render()
    {
        return view('livewire.carrier.carrier-drivers-list', [
            'drivers' => $this->drivers,
            'membershipStats' => $this->membershipStats
        ]);
    }
}