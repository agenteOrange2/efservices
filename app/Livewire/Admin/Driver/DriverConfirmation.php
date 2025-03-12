<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use App\Models\UserDriverDetail;

class DriverConfirmation extends Component
{
    // Driver ID para referencia a pasos anteriores
    public $driverId;
    
    // Estado del botón
    public $loading = false;
    
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
    }
    
    // Método para finalizar el registro y redirigir
    public function finish()
    {
        $this->loading = true;
        
        // Obtenemos el driver y redirectionamos según su carrier
        $userDriverDetail = UserDriverDetail::with('carrier')->find($this->driverId);
        
        if ($userDriverDetail && $userDriverDetail->carrier) {
            $carrierSlug = $userDriverDetail->carrier->slug;
            return redirect()->route('admin.carrier.user_drivers.index', ['carrier' => $carrierSlug])
                ->with('success', 'La solicitud ha sido enviada para revisión.');
        }

        // Si no tenemos carrier en el modelo, intentamos obtenerlo de la ruta
        $carrierSlug = request()->route('carrier');
        return redirect()->route('admin.carrier.user_drivers.index', ['carrier' => $carrierSlug])
            ->with('success', 'La solicitud ha sido enviada para revisión.');
    }
    
    // Método para volver al paso anterior
    public function previous()
    {
        $this->dispatch('prevStep');
    }
    
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-confirmation');
    }
}