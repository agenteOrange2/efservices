<?php
namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;

class DriverFMCSRStep extends Component
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
    public $consent_to_release = false;
    public $has_duty_offenses = false;
    public $recent_conviction_date;
    public $offense_details;
    public $consent_driving_record = false;
    
    // References
    public $driverId;
    
    // Validation rules
    protected function rules()
    {
        return [
            'is_disqualified' => 'sometimes|boolean',
            'disqualified_details' => 'required_if:is_disqualified,true',
            'is_license_suspended' => 'sometimes|boolean',
            'suspension_details' => 'required_if:is_license_suspended,true',
            'is_license_denied' => 'sometimes|boolean',
            'denial_details' => 'required_if:is_license_denied,true',
            'has_positive_drug_test' => 'sometimes|boolean',
            'substance_abuse_professional' => 'required_if:has_positive_drug_test,true',
            'sap_phone' => 'required_if:has_positive_drug_test,true',
            'return_duty_agency' => 'required_if:has_positive_drug_test,true',
            'consent_to_release' => 'required_if:has_positive_drug_test,true|accepted_if:has_positive_drug_test,true',
            'has_duty_offenses' => 'sometimes|boolean',
            'recent_conviction_date' => 'required_if:has_duty_offenses,true|date|nullable',
            'offense_details' => 'required_if:has_duty_offenses,true',
            'consent_driving_record' => 'required|accepted',
        ];
    }
    
    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'is_disqualified' => 'sometimes|boolean',
            'is_license_suspended' => 'sometimes|boolean',
            'is_license_denied' => 'sometimes|boolean',
            'has_positive_drug_test' => 'sometimes|boolean',
            'has_duty_offenses' => 'sometimes|boolean',
        ];
    }
    
    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        
        if ($this->driverId) {
            $this->loadExistingData();
        }
    }
    
    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        $fmcsrData = $userDriverDetail->fmcsrData;
        if ($fmcsrData) {
            $this->is_disqualified = $fmcsrData->is_disqualified;
            $this->disqualified_details = $fmcsrData->disqualified_details;
            $this->is_license_suspended = $fmcsrData->is_license_suspended;
            $this->suspension_details = $fmcsrData->suspension_details;
            $this->is_license_denied = $fmcsrData->is_license_denied;
            $this->denial_details = $fmcsrData->denial_details;
            $this->has_positive_drug_test = $fmcsrData->has_positive_drug_test;
            $this->substance_abuse_professional = $fmcsrData->substance_abuse_professional;
            $this->sap_phone = $fmcsrData->sap_phone;
            $this->return_duty_agency = $fmcsrData->return_duty_agency;
            $this->consent_to_release = $fmcsrData->consent_to_release;
            $this->has_duty_offenses = $fmcsrData->has_duty_offenses;
            $this->recent_conviction_date = $fmcsrData->recent_conviction_date ? 
                                          $fmcsrData->recent_conviction_date->format('Y-m-d') : null;
            $this->offense_details = $fmcsrData->offense_details;
            $this->consent_driving_record = $fmcsrData->consent_driving_record;
        }
    }
    
    // Save FMCSR data to database
    protected function saveFMCSRData()
    {
        try {
            DB::beginTransaction();
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Update or create FMCSR data
            $userDriverDetail->fmcsrData()->updateOrCreate(
                [],
                [
                    'is_disqualified' => $this->is_disqualified,
                    'disqualified_details' => $this->is_disqualified ? $this->disqualified_details : null,
                    'is_license_suspended' => $this->is_license_suspended,
                    'suspension_details' => $this->is_license_suspended ? $this->suspension_details : null,
                    'is_license_denied' => $this->is_license_denied,
                    'denial_details' => $this->is_license_denied ? $this->denial_details : null,
                    'has_positive_drug_test' => $this->has_positive_drug_test,
                    'substance_abuse_professional' => $this->has_positive_drug_test ? 
                                                    $this->substance_abuse_professional : null,
                    'sap_phone' => $this->has_positive_drug_test ? $this->sap_phone : null,
                    'return_duty_agency' => $this->has_positive_drug_test ? $this->return_duty_agency : null,
                    'consent_to_release' => $this->has_positive_drug_test ? $this->consent_to_release : false,
                    'has_duty_offenses' => $this->has_duty_offenses,
                    'recent_conviction_date' => $this->has_duty_offenses ? $this->recent_conviction_date : null,
                    'offense_details' => $this->has_duty_offenses ? $this->offense_details : null,
                    'consent_driving_record' => $this->consent_driving_record,
                ]
            );
            
            // Update current step
            $userDriverDetail->update(['current_step' => 9]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving FMCSR information: ' . $e->getMessage());
            return false;
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