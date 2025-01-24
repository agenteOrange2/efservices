<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use Livewire\Component;

class DriversTable extends Component
{
    public Carrier $carrier;
    public $maxDrivers;
    public $currentDrivers;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $listeners = ['driverSaved' => '$refresh'];

    public function mount(Carrier $carrier)
    {
        $this->carrier = $carrier;
        $this->maxDrivers = $carrier->membership->max_drivers ?? 1;
        $this->currentDrivers = UserDriverDetail::where('carrier_id', $carrier->id)->count();
    }

    public function create()
    {
        return $this->redirect(route('admin.carrier.user_drivers.create', $this->carrier));
    }

    public function edit($driverId)
    {
        return $this->redirect(route('admin.carrier.user_drivers.edit', [
            'carrier' => $this->carrier,
            'userDriverDetail' => $driverId
        ]));
    }

    public function delete($driverId)
    {
        UserDriverDetail::find($driverId)->delete();
        $this->dispatch('driverDeleted');
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';
        $this->sortField = $field;
    }

    public function render()
    {
        $drivers = UserDriverDetail::where('carrier_id', $this->carrier->id)
            ->when($this->search, fn($query) =>
                $query->whereHas('user', fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->with(['user', 'assignedVehicle'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    
        return view('livewire.admin.driver.drivers-table', compact('drivers'));
    }
}
