<?php
namespace App\Livewire\Driver;

use App\Models\Carrier;
use Livewire\Component;
use App\Helpers\Constants;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;

class DriverRegistrationManager extends Component
{
    // Carrier model (puede ser null para registro independiente)
    public $carrier;
    
    // Token de referencia (opcional para registro independiente)
    public $token;
    
    // Tipo de registro: 'referred' o 'independent'
    public $registrationType;
    
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
    public function mount($carrier = null, $token = null, $driverId = null, $currentStep = null)
    {
        $this->carrier = $carrier;
        $this->token = $token;
        
        // Si recibimos un driverId, estamos en modo edición/continuación
        if ($driverId) {
            $this->driverId = $driverId;
            $this->userDriverDetail = UserDriverDetail::find($driverId);
            $this->registrationType = $this->userDriverDetail->carrier_id ? 'referred' : 'independent';
        } else {
            // Determinar el tipo de registro para nuevos registros
            $this->registrationType = ($carrier && $token) ? 'referred' : 'independent';
        }
        
        // Si se proporciona un paso específico, usarlo
        if ($currentStep) {
            $this->currentStep = $currentStep;
        }
        
        // Importante: Registrar lo que está sucediendo para depurar
        Log::info('DriverRegistrationManager mounted', [
            'carrierExists' => $carrier ? true : false,
            'carrierId' => $carrier ? $carrier->id : null,
            'carrierId via driverId' => $this->userDriverDetail ? $this->userDriverDetail->carrier_id : null,
            'driverId' => $this->driverId,
            'currentStep' => $this->currentStep,
            'registrationType' => $this->registrationType
        ]);
    }
    
    // Ir al siguiente paso
    // Go to the next step
    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;

            if ($this->driverId) {
                // Actualizar el paso actual en la base de datos
                $this->updateCurrentStep($this->currentStep);
                
                // Si es después del paso 1 y el usuario está autenticado, redirigir a la URL de continuación
                if ($this->currentStep > 1) {
                    // Redirigir a la URL de continuación
                    return redirect()->route('driver.registration.continue', $this->currentStep);
                }
            }
        }
    }

    private function updateCurrentStep($step)
    {
        if ($this->driverId) {
            $driver = UserDriverDetail::find($this->driverId);
            if ($driver && $driver->current_step < $step) {
                $driver->update(['current_step' => $step]);
                Log::info('Current step updated by manager', [
                    'driver_id' => $this->driverId,
                    'step' => $step
                ]);
            }
        }
    }
    
    // Ir al paso anterior
    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            
            // Si el usuario está autenticado (tiene driverId), actualizar la URL
            if ($this->driverId) {
                return redirect()->route('driver.registration.continue', $this->currentStep);
            }
        }
    }
    
    // Cuando un driver es creado en el primer paso
    public function handleDriverCreated($driverId)
    {
        $this->driverId = $driverId;
        $this->userDriverDetail = UserDriverDetail::find($driverId);
        
        // Redirigir a la URL de continuación después de crear el driver
        // Usamos 2 porque estamos avanzando al paso 2 (después de completar el paso 1)
        return redirect()->route('driver.registration.continue', 2);
    }
    
    // Manejar guardar y salir desde cualquier paso
    public function handleSaveAndExit()
    {
        // Si es registro independiente, redirigir a selección de carrier
        if ($this->registrationType === 'independent' && $this->driverId) {
            return redirect()->route('driver.select_carrier');
        }
        
        // Si es registro por referencia, redirigir al índice de drivers
        if ($this->carrier) {
            return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
        }
        
        // Caso por defecto
        return redirect()->route('driver.dashboard');
    }
    
    // Enviar formulario en el paso final
    public function submitForm()
    {
        if ($this->driverId) {
            UserDriverDetail::where('id', $this->driverId)->update([
                'application_completed' => true
            ]);
            
            session()->flash('success', 'Driver registration completed successfully.');
            
            // Redireccionar según tipo de registro
            if ($this->registrationType === 'independent') {
                return redirect()->route('driver.select_carrier');
            } else {
                return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
            }
        }
    }
    
    // Render
    public function render()
    {
        return view('livewire.driver.driver-registration-manager', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources(),
            'isIndependent' => $this->registrationType === 'independent'
        ]);
    }
}