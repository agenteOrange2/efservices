<?php

namespace App\Livewire\Admin\Driver\Recruitment;

use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Carrier;
use Livewire\Component;
use Livewire\WithPagination;

class DriverRecruitmentList extends Component
{
    use WithPagination;

    // Propiedades para filtros
    public $search = '';
    public $statusFilter = '';
    public $carrierFilter = '';
    
    // Escuchar eventos desde otros componentes
    protected $listeners = ['applicationStatusUpdated' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCarrierFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Consulta base de conductores con sus relaciones
        $query = UserDriverDetail::with(['user', 'carrier', 'application'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtro de búsqueda si existe
        if (!empty($this->search)) {
            $query->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orWhere('last_name', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%');
        }

        // Filtrar por estado de aplicación
        if (!empty($this->statusFilter)) {
            $query->whereHas('application', function($q) {
                $q->where('status', $this->statusFilter);
            });
        }

        // Filtrar por carrier
        if (!empty($this->carrierFilter)) {
            $query->where('carrier_id', $this->carrierFilter);
        }

        // Obtener conductores paginados
        $drivers = $query->paginate(10);
        
        // Obtener lista de carriers para el selector de filtros
        $carriers = Carrier::orderBy('name')->get();

        return view('livewire.admin.driver.recruitment.driver-recruitment-list', [
            'drivers' => $drivers,
            'carriers' => $carriers,
            'applicationStatuses' => [
                DriverApplication::STATUS_DRAFT => 'Borrador',
                DriverApplication::STATUS_PENDING => 'Pendiente',
                DriverApplication::STATUS_APPROVED => 'Aprobado',
                DriverApplication::STATUS_REJECTED => 'Rechazado'
            ]
        ]);
    }
}