<?php
namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;

class DriverFMCSRStepAlt extends Component
{
    // FMCSR Data
    public $is_disqualified = false;
    public $disqualified_details;
    public $is_license_suspended = false;
    public $suspension_details;
    public $is_license_denied = false;
    public $denial_details;
    public $has_positive_drug_test = false;
    public $substance_abuse_professional;
    public $sap_phone;
    public $return_duty_agency;
    
    // Driver ID
    public $driverId;
    
    // Validation rules
    protected function rules()
    {
        return [
            'is_disqualified' => 'boolean',
            'disqualified_details' => 'nullable|required_if:is_disqualified,true|string|max:1000',
            'is_license_suspended' => 'boolean',
            'suspension_details' => 'nullable|required_if:is_license_suspended,true|string|max:1000',
            'is_license_denied' => 'boolean',
            'denial_details' => 'nullable|required_if:is_license_denied,true|string|max:1000',
            'has_positive_drug_test' => 'boolean',
            'substance_abuse_professional' => 'nullable|required_if:has_positive_drug_test,true|string|max:255',
            'sap_phone' => 'nullable|required_if:has_positive_drug_test,true|string|max:20',
            'return_duty_agency' => 'nullable|required_if:has_positive_drug_test,true|string|max:255'
        ];
    }
    
    // Partial validation for save and exit
    protected function partialRules()
    {
        return [
            'is_disqualified' => 'boolean',
            'disqualified_details' => 'nullable|string|max:1000',
            'is_license_suspended' => 'boolean',
            'suspension_details' => 'nullable|string|max:1000',
            'is_license_denied' => 'boolean',
            'denial_details' => 'nullable|string|max:1000',
            'has_positive_drug_test' => 'boolean',
            'substance_abuse_professional' => 'nullable|string|max:255',
            'sap_phone' => 'nullable|string|max:20',
            'return_duty_agency' => 'nullable|string|max:255'
        ];
    }
    
    // Mount
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        
        if ($this->driverId) {
            $this->loadFMCSRData();
        }
    }
    
    // Load FMCSR data
    protected function loadFMCSRData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        // Load FMCSR data from user_driver_details
        $this->is_disqualified = (bool)$userDriverDetail->is_disqualified;
        $this->disqualified_details = $userDriverDetail->disqualified_details;
        $this->is_license_suspended = (bool)$userDriverDetail->is_license_suspended;
        $this->suspension_details = $userDriverDetail->suspension_details;
        $this->is_license_denied = (bool)$userDriverDetail->is_license_denied;
        $this->denial_details = $userDriverDetail->denial_details;
        $this->has_positive_drug_test = (bool)$userDriverDetail->has_positive_drug_test;
        $this->substance_abuse_professional = $userDriverDetail->substance_abuse_professional;
        $this->sap_phone = $userDriverDetail->sap_phone;
        $this->return_duty_agency = $userDriverDetail->return_duty_agency;
    }
    
    // Save FMCSR data
    public function saveFMCSRData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Update FMCSR data
            $userDriverDetail->update([
                'is_disqualified' => $this->is_disqualified,
                'disqualified_details' => $this->is_disqualified ? $this->disqualified_details : null,
                'is_license_suspended' => $this->is_license_suspended,
                'suspension_details' => $this->is_license_suspended ? $this->suspension_details : null,
                'is_license_denied' => $this->is_license_denied,
                'denial_details' => $this->is_license_denied ? $this->denial_details : null,
                'has_positive_drug_test' => $this->has_positive_drug_test,
                'substance_abuse_professional' => $this->has_positive_drug_test ? $this->substance_abuse_professional : null,
                'sap_phone' => $this->has_positive_drug_test ? $this->sap_phone : null,
                'return_duty_agency' => $this->has_positive_drug_test ? $this->return_duty_agency : null
            ]);
            
            // Update current step if needed
            if ($userDriverDetail->current_step < 9) {
                $userDriverDetail->update(['current_step' => 9]);
                Log::info('Current step updated by manager', ['driver_id' => $this->driverId, 'step' => 9]);
            }
            
            DB::commit();
            
            session()->flash('success', 'FMCSR information saved successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving FMCSR data', [
                'driver_id' => $this->driverId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error saving FMCSR information. Please try again.');
        }
    }
    
    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveFMCSRData();
        }
        
        // Move to next step
        $this->dispatch('nextStep');
    }
    
    // Previous step
    public function previous()
    {
        // Basic save before going back
        if ($this->driverId) {
            $this->validate($this->partialRules());
            $this->saveFMCSRData();
        }
        
        $this->dispatch('prevStep');
    }
    
    // Save and exit
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveFMCSRData();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-f-m-c-s-r-step');
    }
}
