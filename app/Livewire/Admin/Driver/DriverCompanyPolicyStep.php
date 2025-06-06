<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverCompanyPolicy;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;

class DriverCompanyPolicyStep extends Component
{
    // Propiedades
    public $driverId;
    
    public $consent_all_policies_attached = false;
    public $substance_testing_consent = false;
    public $authorization_consent = false;
    public $fmcsa_clearinghouse_consent = false;
    public $company_name = '';
    public $license_number;
    public $license_state;
    public $policyDocumentPath;
    public $carrierId;
    public $isDefaultPolicy = true;

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
            
            // Cargar el nombre del carrier asociado al conductor
            $this->loadCarrierName();
        }
        
        // Cargar datos de licencia si no existe información
        if (empty($this->license_number) || empty($this->license_state)) {
            $this->loadLicenseData();
        }
        
        // Cargar el documento de política correspondiente
        $this->loadPolicyDocument();
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

    // Cargar el documento de política correspondiente
    protected function loadPolicyDocument()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            // Si no hay driver, usar el documento de política por defecto
            $this->loadDefaultPolicyDocument();
            return;
        }
        
        // Obtener el carrier asociado al driver
        $this->carrierId = $userDriverDetail->carrier_id;
        if (!$this->carrierId) {
            // Si no hay carrier, usar el documento de política por defecto
            $this->loadDefaultPolicyDocument();
            return;
        }
        
        // Buscar el tipo de documento de política (normalmente se llama 'Politics')
        $policyDocumentType = DocumentType::where('name', 'Politics')->first();
        
        if (!$policyDocumentType) {
            // Si no existe el tipo de documento de política, intentar con 'Policy Document'
            $policyDocumentType = DocumentType::where('name', 'Policy Document')->first();
            
            if (!$policyDocumentType) {
                // Si no existe ningún tipo de documento de política, usar el documento por defecto
                $this->loadDefaultPolicyDocument();
                return;
            }
        }
        
        // Buscar el documento del carrier para este tipo de documento
        $carrierPolicyDocument = CarrierDocument::where('carrier_id', $this->carrierId)
            ->where('document_type_id', $policyDocumentType->id)
            ->first();
        
        if (!$carrierPolicyDocument) {
            // Si no hay documento para este carrier, usar el documento por defecto
            $this->loadDefaultPolicyDocument();
            return;
        }
        
        // Si el carrier ha subido su propio documento
        if ($carrierPolicyDocument->getFirstMedia('carrier_documents')) {
            $this->policyDocumentPath = $carrierPolicyDocument->getFirstMedia('carrier_documents')->getUrl();
            $this->isDefaultPolicy = false;
            return;
        }
        
        // Si el carrier ha aprobado el documento por defecto (status = STATUS_APPROVED)
        if ($carrierPolicyDocument->status == CarrierDocument::STATUS_APPROVED) {
            // Obtener el documento por defecto del tipo de documento
            $defaultMedia = $policyDocumentType->getFirstMedia('default_documents');
            if ($defaultMedia) {
                $this->policyDocumentPath = $defaultMedia->getUrl();
                $this->isDefaultPolicy = true;
                return;
            }
        }
        
        // Si no hay documento personalizado ni aprobado, usar el documento por defecto genérico
        $this->loadDefaultPolicyDocument();
    }
    
    // Cargar el nombre del carrier asociado al conductor
    protected function loadCarrierName()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail || !$userDriverDetail->carrier_id) {
            // Si no hay conductor o no tiene carrier asociado, usar un valor por defecto
            $this->company_name = 'EF Services';
            return;
        }
        
        // Obtener el carrier asociado al conductor
        $carrier = \App\Models\Carrier::find($userDriverDetail->carrier_id);
        if ($carrier) {
            // Usar el nombre del carrier
            $this->company_name = $carrier->name;
        } else {
            // Si no se encuentra el carrier, usar un valor por defecto
            $this->company_name = 'EF Services';
        }
    }
    
    // Cargar el documento de política por defecto
    protected function loadDefaultPolicyDocument()
    {
        // Intentar obtener el documento por defecto del tipo de documento 'Politics'
        $policyDocumentType = DocumentType::where('name', 'Politics')->first();
        
        if (!$policyDocumentType) {
            // Si no existe, intentar con 'Policy Document'
            $policyDocumentType = DocumentType::where('name', 'Policy Document')->first();
        }
        
        if ($policyDocumentType) {
            $defaultMedia = $policyDocumentType->getFirstMedia('default_documents');
            if ($defaultMedia) {
                $this->policyDocumentPath = $defaultMedia->getUrl();
                $this->isDefaultPolicy = true;
                return;
            }
        }
        
        // Si no hay documento por defecto en el tipo de documento, usar el genérico
        $this->policyDocumentPath = asset('storage/documents/company_policy.pdf');
        $this->isDefaultPolicy = true;
    }
    
    // Renderizar
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-company-policy-step');
    }
}