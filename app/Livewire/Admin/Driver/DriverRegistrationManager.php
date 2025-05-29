<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Carrier;
use Livewire\Component;
use App\Helpers\Constants;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use Livewire\Livewire;

class DriverRegistrationManager extends Component
{
    // No se necesita registro manual
    // Carrier model
    public Carrier $carrier;

    // Current step
    public $currentStep = 1;
    public $totalSteps = 14;

    // Driver ID for edit mode
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
    public function mount(Carrier $carrier, $userDriverDetail = null)
    {
        $this->carrier = $carrier;

        // Check if we're in edit mode
        if ($userDriverDetail) {
            $this->driverId = $userDriverDetail->id;
            $this->userDriverDetail = $userDriverDetail;

            // If the driver has a current step, use it
            if ($userDriverDetail->current_step) {
                $this->currentStep = $userDriverDetail->current_step;
            }
        }
    }

    // Go to the next step
    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;


            if ($this->driverId) {
                $this->updateCurrentStep($this->currentStep);
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

    // Go to the previous step
    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    // When a driver is created in first step
    public function handleDriverCreated($driverId)
    {
        $this->driverId = $driverId;
        $this->userDriverDetail = UserDriverDetail::find($driverId);
        
        // Redireccionar a la página de edición del conductor
        if ($this->userDriverDetail) {
            // Usar el método redirectRoute de Livewire para redireccionar correctamente
            // Aquí pasamos el carrier directamente (sin ->id) para que use el modelo completo
            return $this->redirectRoute('admin.carrier.user_drivers.edit', [
                'carrier' => $this->carrier,
                'userDriverDetail' => $this->userDriverDetail
            ]);
        }
    }

    // Handle save and exit from any step
    public function handleSaveAndExit()
    {
        return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
    }

    // Submit form on the final step
    public function submitForm()
    {
        if ($this->driverId) {
            $driver = UserDriverDetail::find($this->driverId);
            
            if ($driver) {
                // Actualizar driver como completado
                $driver->update([
                    'application_completed' => true,
                    'current_step' => $this->totalSteps // Asegurar que está en el último paso
                ]);
                
                // Actualizar la aplicación si existe
                if ($driver->application) {
                    $driver->application->update([
                        'status' => 'pending',
                        'completed_at' => now()
                    ]);
                }
                
                session()->flash('success', 'Driver registration completed successfully.');
                return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
            }
        }
    }

    // Render
    public function render()
    {
        return view('livewire.admin.driver.driver-registration-manager', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources()
        ]);
    }
}
