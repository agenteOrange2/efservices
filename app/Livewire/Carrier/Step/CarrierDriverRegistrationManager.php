<?php

namespace App\Livewire\Carrier\Step;

use App\Models\Carrier;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Helpers\Constants;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Auth;

class CarrierDriverRegistrationManager extends Component
{
    // Carrier model (obtenido del usuario autenticado)
    public $carrier;
    
    // Current step
    public $currentStep = 1;
    public $totalSteps = 14;
    
    // Driver ID para modo edición
    public $driverId = null;
    public $userDriverDetail = null;
    
    // Event listeners
    protected $listeners = [
        'nextStep',
        'prevStep',
        'driverCreated' => 'handleDriverCreated',
        'saveAndExit' => 'handleSaveAndExit',
    ];
    
    // Mounting the component
    public function mount($driverId = null)
    {
        $this->carrier = Auth::user()->carrierDetails->carrier;
        $this->driverId = $driverId;
        
        if ($this->driverId) {
            $this->userDriverDetail = UserDriverDetail::find($this->driverId);
            
            // Si encuentra el driver, establecer el paso actual según el driver
            if ($this->userDriverDetail) {
                $this->currentStep = $this->userDriverDetail->current_step ?: 1;
            }
        }
        
        // Importante: Registrar lo que está sucediendo para depurar
        Log::info('CarrierDriverRegistrationManager mounted', [
            'carrierId' => $this->carrier->id,
            'carrierName' => $this->carrier->name,
            'driverId' => $this->driverId,
            'currentStep' => $this->currentStep
        ]);
    }
    
    // Ir al siguiente paso
    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }
    
    // Ir al paso anterior
    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    // Cuando un driver es creado en el primer paso
    public function handleDriverCreated($driverId)
    {
        $this->driverId = $driverId;
        $this->userDriverDetail = UserDriverDetail::find($driverId);
    }
    
    // Manejar guardar y salir desde cualquier paso
    public function handleSaveAndExit()
    {
        return redirect()->route('carrier.drivers.index')
            ->with('success', 'Información del conductor guardada correctamente.');
    }
    
    // Enviar formulario en el paso final
    public function submitForm()
    {
        if ($this->driverId) {
            UserDriverDetail::where('id', $this->driverId)->update([
                'application_completed' => true
            ]);
            session()->flash('success', 'Registro de conductor completado exitosamente.');
            return redirect()->route('carrier.drivers.index');
        }
    }
    
    // Render
    public function render()
    {
        return view('livewire.carrier.step.carrier-driver-registration-manager', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources(),
        ]);
    }
}

