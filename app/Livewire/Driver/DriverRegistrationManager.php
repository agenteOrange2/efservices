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
    public $totalSteps = 13;
    
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
    public function mount($carrier = null, $token = null)
    {
        $this->carrier = $carrier;
        $this->token = $token;
        
        // Determinar el tipo de registro
        $this->registrationType = ($carrier && $token) ? 'referred' : 'independent';
        
        // NO verificar autenticación aquí, ya que este es un flujo de registro público
        
        // Importante: Registrar lo que está sucediendo para depurar
        Log::info('DriverRegistrationManager mounted', [
            'carrierExists' => $carrier ? true : false,
            'carrierId' => $carrier ? $carrier->id : null,
            'carrierName' => $carrier ? $carrier->name : null, 
            'token' => $token,
            'registrationType' => $this->registrationType
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