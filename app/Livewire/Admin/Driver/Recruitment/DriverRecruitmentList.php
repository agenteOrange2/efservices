<?php

namespace App\Livewire\Admin\Driver\Recruitment;

use App\Models\UserDriverDetail;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Carrier;
use App\Models\Admin\Driver\DriverApplication;

class DriverRecruitmentList extends Component
{
    use WithPagination;
    
    // Propiedades para filtros
    public $search = '';
    public $statusFilter = '';
    public $carrierFilter = '';
    
    

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

    /**
     * Calcula el porcentaje de verificación para un conductor usando la misma lógica que en la vista de detalle
     */
    public function getChecklistPercentage($driver)
    {                
        try {
            if (!$driver->application) {                
                return ['percentage' => 0, 'checked' => 0, 'total' => 0];
            }
            
            // Si la aplicación está aprobada, devolver 100% inmediatamente
            if ($driver->application && $driver->application->status === 'approved') {                
                return ['percentage' => 100, 'checked' => 1, 'total' => 1];
            }
            
            $verification = \App\Models\Admin\Driver\DriverRecruitmentVerification::where('driver_application_id', $driver->application->id)
                ->latest('verified_at')
                ->first();            
            
            $checklistItems = [];
            if ($verification && !empty($verification->verification_items)) {
                $checklistItems = $verification->verification_items;                
            } else {
                Log::info("No hay elementos de verificación");
            }
            
            $totalItems = count($checklistItems);
            $checkedItems = 0;
            
            foreach ($checklistItems as $key => $item) {
                // Manejar ambos formatos de datos
                if (is_array($item) && isset($item['checked']) && $item['checked'] === true) {
                    // Formato nuevo: {"key": {"checked": true, "label": "..."}}
                    $checkedItems++;
                } elseif ($item === true) {
                    // Formato antiguo: {"key": true}
                    $checkedItems++;
                }
            }
            
            $percentage = $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0;            
            
            return ['percentage' => $percentage, 'checked' => $checkedItems, 'total' => $totalItems];
        } catch (\Exception $e) {            
            return ['percentage' => 0, 'checked' => 0, 'total' => 0];
        }
    }

    /**
     * Escucha el evento verification_updated y refresca el componente
     */
    #[On('verification_updated')]
    public function refreshList($driverApplicationId = null)
    {                
        
        // Forzar una recarga completa para mostrar los datos actualizados
        $this->dispatch('$refresh');
    }
    
    /**
     * Define el intervalo de polling para este componente
     */
    public function poolInterval()
    {
        return 3000; // 3 segundos
    }

    public function render()
    {
        // Consulta base de conductores con sus relaciones
        // Usamos select al inicio y distinct para forzar una consulta fresca
        $query = UserDriverDetail::query()
            ->select('user_driver_details.*')
            ->with([
                'user', 
                'carrier'
            ])
            // Forzamos carga fresca - sin cache
            ->withoutGlobalScopes()
            ->orderBy('user_driver_details.created_at', 'desc');
            
        // Muy importante: cargar la aplicación y sus verificaciones más recientes
        // usando subquery para garantizar que obtenemos los datos más actualizados
        $query->with([
            'application' => function($q) {
                $q->withoutGlobalScopes();
            },
            'application.verifications' => function($q) {
                $q->withoutGlobalScopes()
                  ->latest('verified_at')
                  ->limit(1);
            }
        ]);

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
                DriverApplication::STATUS_DRAFT => 'Draft',
                DriverApplication::STATUS_PENDING => 'Pending',
                DriverApplication::STATUS_APPROVED => 'Approved',
                DriverApplication::STATUS_REJECTED => 'Rejected',
            ]
        ]);
    }
}