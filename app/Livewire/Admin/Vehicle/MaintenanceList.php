<?php

namespace App\Livewire\Admin\Vehicle;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class MaintenanceList extends Component
{
    use WithPagination;

    // Propiedades para filtros y ordenamiento
    public $search = '';
    public $status = '';
    public $carrierId = '';
    public $dateRange = '';
    
    // Propiedad para rastrear cuando se actualiza un filtro
    // Aseguramos que los filtros se mantengan en la URL
    public $perPage = 10;
    public $sortField = 'service_date';
    public $sortDirection = 'desc';

    // Propiedades para la edición
    public $editing = [
        'id' => '',
        'service_tasks' => '',
        'service_date' => '',
        'next_service_date' => '',
        'vendor_mechanic' => '',
        'cost' => '',
        'status' => false
    ];

    // Para buscar por rango de fechas
    public $startDate = '';
    public $endDate = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'carrierId' => ['except' => ''],
        'dateRange' => ['except' => ''],
        'sortField' => ['except' => 'service_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = ['refresh' => '$refresh'];

    protected $rules = [
        'editing.service_tasks' => 'required|string|max:255',
        'editing.service_date' => 'required|date',
        'editing.next_service_date' => 'nullable|date|after:editing.service_date',
        'editing.vendor_mechanic' => 'required|string|max:255',
        'editing.cost' => 'required|numeric|min:0',
        'editing.status' => 'boolean'
    ];

    // Resetear la paginación cuando se actualiza cualquier filtro
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatus()
    {
        $this->resetPage();
    }
    
    public function updatingCarrierId()
    {
        $this->resetPage();
    }

    // Ordenar por campo
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    // Eliminar un registro de mantenimiento
    public function delete($id)
    {
        $maintenance = VehicleMaintenance::find($id);
        if ($maintenance) {
            $maintenance->delete();
            session()->flash('message', 'Registro de mantenimiento eliminado correctamente.');
        }
    }

    // Marcar como completado/pendiente
    public function toggleStatus($id)
    {
        $maintenance = VehicleMaintenance::find($id);
        if ($maintenance) {
            $maintenance->status = !$maintenance->status;
            $maintenance->save();
        }
    }

    public function edit($id)
    {
        $maintenance = VehicleMaintenance::find($id);
        if ($maintenance) {
            $this->editing = [
                'id' => $maintenance->id,
                'service_tasks' => $maintenance->service_tasks,
                'service_date' => $maintenance->service_date->format('Y-m-d'),
                'next_service_date' => $maintenance->next_service_date ? $maintenance->next_service_date->format('Y-m-d') : null,
                'vendor_mechanic' => $maintenance->vendor_mechanic,
                'cost' => $maintenance->cost,
                'status' => $maintenance->status
            ];
        }
    }

    public function update()
    {
        $this->validate();

        $maintenance = VehicleMaintenance::find($this->editing['id']);
        if ($maintenance) {
            $maintenance->update([
                'service_tasks' => $this->editing['service_tasks'],
                'service_date' => $this->editing['service_date'],
                'next_service_date' => $this->editing['next_service_date'],
                'vendor_mechanic' => $this->editing['vendor_mechanic'],
                'cost' => $this->editing['cost'],
                'status' => $this->editing['status']
            ]);

            $this->dispatchBrowserEvent('close-modal');
            session()->flash('message', 'Maintenance record updated successfully.');
        }
    }

    // Procesar rango de fechas cuando se actualiza
    public function updatedDateRange()
    {
        if ($this->dateRange) {
            $dates = explode(' - ', $this->dateRange);
            if (count($dates) == 2) {
                $this->startDate = $dates[0];
                $this->endDate = $dates[1];
            }
        } else {
            $this->startDate = '';
            $this->endDate = '';
        }
    }

    public function render()
    {
        $query = VehicleMaintenance::query()
            ->with('vehicle')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('description', 'like', '%' . $this->search . '%')
                        ->orWhere('service_tasks', 'like', '%' . $this->search . '%')
                        ->orWhere('vendor_mechanic', 'like', '%' . $this->search . '%')
                        ->orWhere('unit', 'like', '%' . $this->search . '%')
                        ->orWhereHas('vehicle', function ($query) {
                            $query->where('make', 'like', '%' . $this->search . '%')
                                ->orWhere('model', 'like', '%' . $this->search . '%')
                                ->orWhere('vin', 'like', '%' . $this->search . '%')
                                ->orWhere('company_unit_number', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== '', function ($query) {
                return $query->where('status', $this->status == '1' ? 1 : 0);
            })
            ->when($this->carrierId, function ($query) {
                return $query->whereHas('vehicle', function($q) {
                    $q->where('carrier_id', $this->carrierId);
                });
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('service_date', [$this->startDate, $this->endDate]);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $maintenances = $query->paginate($this->perPage);
        
        // Obtener carriers activos para el filtro
        $carriers = \App\Models\Carrier::where('status', \App\Models\Carrier::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.vehicle.maintenance-list', [
            'maintenances' => $maintenances,
            'carriers' => $carriers,
        ]);
    }
}