<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Carrier;
use Livewire\Component;
use App\Helpers\Constants;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Services\Admin\DriverStepService;
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
    
    // Edit mode flag
    public $isEditMode = false;
    
    // Step service
    protected $stepService;

    // Event listeners
    protected $listeners = [
        'driverCreated' => 'handleDriverCreated',
        'saveAndExit' => 'handleSaveAndExit',
        'nextStep' => 'nextStep',
        'prevStep' => 'prevStep',
    ];

    // Mounting the component
    public function mount(Carrier $carrier, UserDriverDetail $userDriverDetail = null)
    {
        Log::info('DriverRegistrationManager::mount - Componente iniciado', [
            'carrier_id' => $carrier->id,
            'carrier_name' => $carrier->name ?? 'N/A',
            'driver_id' => $userDriverDetail->id ?? 'null',
            'is_edit_mode' => !is_null($userDriverDetail)
        ]);
        
        $this->carrier = $carrier;
        $this->userDriverDetail = $userDriverDetail;
        $this->isEditMode = !is_null($userDriverDetail);
        
        Log::info('DriverRegistrationManager::mount - Modo de edición detectado', [
            'is_edit_mode' => $this->isEditMode,
            'driver_user_id' => $userDriverDetail->user_id ?? 'null'
        ]);
        
        // Set current step based on edit mode
        $this->currentStep = $this->isEditMode ? 1 : 1;
        
        Log::info('DriverRegistrationManager::mount - Step actual configurado', [
            'current_step' => $this->currentStep
        ]);
        
        // Initialize step service
        $this->stepService = app(DriverStepService::class);
        
        // If editing, load existing data
        if ($this->isEditMode) {
            Log::info('DriverRegistrationManager::mount - Cargando datos existentes para edición');
            $this->loadExistingData();
            Log::info('DriverRegistrationManager::mount - Datos existentes cargados exitosamente');
        }
        
        Log::info('DriverRegistrationManager::mount - Componente montado exitosamente');
    }



    public function goToTab($tabNumber)
    {
        if ($tabNumber >= 1 && $tabNumber <= $this->totalSteps) {
            $this->currentStep = $tabNumber;
        }
    }

    // Navigate to next step with validation
    public function nextStep()
    {
        // Check if we can move to next step
        if ($this->canMoveToNextStep()) {
            if ($this->currentStep < $this->totalSteps) {
                $this->currentStep++;
            }
        }
    }

    // Navigate to previous step
    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    // Check if we can move to next step (with intelligent validation)
    private function canMoveToNextStep()
    {
        // Always allow navigation if no content or in edit mode
        if ($this->isEditMode || !$this->hasContentInCurrentStep()) {
            return true;
        }

        // Validate current step if it has content
        return $this->validateCurrentStep();
    }

    // Check if current step has content that needs validation
    private function hasContentInCurrentStep()
    {
        // This is a simplified check - you can expand this based on your needs
        // For now, we'll assume steps always allow navigation unless there are validation errors
        return false; // Allow free navigation by default
    }

    // Validate current step
    private function validateCurrentStep()
    {
        // Emit validation event to current step component
        $this->dispatch('validateStep', $this->currentStep);
        
        // For now, return true to allow navigation
        // You can implement specific validation logic here
        return true;
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

    // Load existing data for edit mode
    private function loadExistingData()
    {
        if ($this->userDriverDetail) {
            $this->driverId = $this->userDriverDetail->id;
            // Load any additional data needed for editing
            // This method can be expanded as needed
        }
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
