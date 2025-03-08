<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverCompanyPolicy;

class DriverCompanyPolicyStep extends Component
{
    // Propiedades
    public $driverId;
    
    public $consent_all_policies_attached = false;
    public $substance_testing_consent = false;
    public $authorization_consent = false;
    public $fmcsa_clearinghouse_consent = false;
    public $company_name = 'EF Services';
    public $license_number;
    public $license_state;

    // Validación
    protected function rules()
    {
        return [
            'consent_all_policies_attached' => 'accepted',
            'substance_testing_consent' => 'accepted',
            'authorization_consent' => 'accepted',
            'fmcsa_clearinghouse_consent' => 'accepted'
        ];
    }

    // Inicialización
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        
        if ($this->driverId) {
            $this->loadExistingData();
        }
        
        // Cargar datos de licencia si no existe información
        if (empty($this->license_number) || empty($this->license_state)) {
            $this->loadLicenseData();
        }
    }

    // Cargar datos existentes
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        $policy = $userDriverDetail->companyPolicy;
        if ($policy) {
            
            $this->consent_all_policies_attached = $policy->consent_all_policies_attached;
            $this->substance_testing_consent = $policy->substance_testing_consent;
            $this->authorization_consent = $policy->authorization_consent;
            $this->fmcsa_clearinghouse_consent = $policy->fmcsa_clearinghouse_consent;
            $this->company_name = $policy->company_name;
        }
    }

    // Cargar datos de licencia
    protected function loadLicenseData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        // Obtener la primera licencia
        $license = $userDriverDetail->licenses()->first();
        if ($license) {
            $this->license_number = $license->license_number;
            $this->license_state = $license->state_of_issue;
        }
    }

    // Guardar datos
    protected function saveCompanyPolicy()
    {
        try {
            DB::beginTransaction();
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Actualizar o crear policy
            $userDriverDetail->companyPolicy()->updateOrCreate(
                [],
                [
                    'consent_all_policies_attached' => $this->consent_all_policies_attached,
                    'substance_testing_consent' => $this->substance_testing_consent,
                    'authorization_consent' => $this->authorization_consent,
                    'fmcsa_clearinghouse_consent' => $this->fmcsa_clearinghouse_consent,
                    'company_name' => $this->company_name
                ]
            );

            // Actualizar paso actual
            $userDriverDetail->update(['current_step' => 11]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving company policy information: ' . $e->getMessage());
            return false;
        }
    }

    // Métodos de navegación
    public function next()
    {
        $this->validate();
        
        if ($this->driverId) {
            $this->saveCompanyPolicy();
        }
        
        $this->dispatch('nextStep');
    }

    public function previous()
    {
        $this->dispatch('prevStep');
    }

    public function saveAndExit()
    {
        if ($this->driverId) {
            $this->saveCompanyPolicy();
        }
        
        $this->dispatch('saveAndExit');
    }

    // Renderizar
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-company-policy-step');
    }
}