<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Driver\DriverCriminalHistory;

class CriminalHistoryStep extends Component
{
    // Propiedades
    public $driverId;
    public $has_criminal_charges = false;
    public $has_felony_conviction = false;
    public $has_minister_permit = false;
    public $fcra_consent = false;
    public $background_info_consent = false;
    
    // Datos adicionales de otras tablas (solo para mostrar)
    public $full_name;
    public $middle_name;
    public $last_name;
    public $ssn_last_four;
    public $date_of_birth;
    public $license_number;
    public $license_state;
    public $addresses = [];
    
    // Validación
    protected function rules()
    {
        $rules = [
            'has_criminal_charges' => 'required|accepted',
            'has_felony_conviction' => 'required|accepted',
            'has_minister_permit' => 'required|boolean',
            'fcra_consent' => 'accepted',
            'background_info_consent' => 'accepted'
        ];
        
        return $rules;
    }
    
    // Mensajes de validación personalizados
    protected function messages()
    {
        return [
            'has_criminal_charges.accepted' => 'You must mark "Yes" in the criminal charges field to consent.',
            'has_felony_conviction.accepted' => 'You must mark "Yes" in the felony convictions field to consent.',
            'fcra_consent.accepted' => 'You must accept the FCRA consent to continue.',
            'background_info_consent.accepted' => 'You must accept the background information consent to continue.'
        ];
    }
    
    // Inicialización
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        if ($this->driverId) {
            $this->loadExistingData();
            $this->loadReferenceData();
        }
    }
    
    // Cargar datos existentes
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        $criminalHistory = $userDriverDetail->criminalHistory;
        if ($criminalHistory) {
            $this->has_criminal_charges = $criminalHistory->has_criminal_charges;
            $this->has_felony_conviction = $criminalHistory->has_felony_conviction;
            $this->has_minister_permit = $criminalHistory->has_minister_permit;
            $this->fcra_consent = $criminalHistory->fcra_consent;
            $this->background_info_consent = $criminalHistory->background_info_consent;
        }
    }
    
    // Cargar datos de referencia de otras tablas
    protected function loadReferenceData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        // Datos personales
        $user = $userDriverDetail->user;
        if ($user) {
            $this->full_name = $user->name;
        }
        
        $this->middle_name = $userDriverDetail->middle_name;
        $this->last_name = $userDriverDetail->last_name;
        $this->date_of_birth = $userDriverDetail->date_of_birth;
        
        // Datos de SSN
        $medicalQualification = $userDriverDetail->medicalQualification;
        if ($medicalQualification && $medicalQualification->social_security_number) {
            $ssn = $medicalQualification->social_security_number;
            $this->ssn_last_four = substr($ssn, -4);
        }
        
        // Datos de licencia
        $license = $userDriverDetail->licenses()->first();
        if ($license) {
            $this->license_number = $license->license_number;
            $this->license_state = $license->state_of_issue;
        }
        
        // Direcciones
        if ($userDriverDetail->application) {
            $this->addresses = $userDriverDetail->application->addresses()->get()->toArray();
        }
    }
    
    // Guardar datos
    protected function saveCriminalHistory()
    {
        try {
            DB::beginTransaction();
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Para depuración
            Log::info('Criminal history values before save', [
                'has_criminal_charges' => $this->has_criminal_charges,
                'has_felony_conviction' => $this->has_felony_conviction,
                'has_minister_permit' => $this->has_minister_permit,
                'fcra_consent' => $this->fcra_consent,
                'background_info_consent' => $this->background_info_consent
            ]);
            
            // Actualizar o crear criminal history
            $userDriverDetail->criminalHistory()->updateOrCreate(
                [],
                [
                    'has_criminal_charges' => $this->has_criminal_charges,
                    'has_felony_conviction' => $this->has_felony_conviction,
                    'has_minister_permit' => $this->has_minister_permit,
                    'fcra_consent' => $this->fcra_consent,
                    'background_info_consent' => $this->background_info_consent
                ]
            );
            
            // Actualizar paso actual
            $userDriverDetail->update(['current_step' => 12]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving criminal history', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving criminal history: ' . $e->getMessage());
            return false;
        }
    }
    
    // Métodos de navegación
    public function next()
    {
        $this->validate();
        
        if ($this->driverId) {
            $this->saveCriminalHistory();
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
            $this->saveCriminalHistory();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    // Renderizar
    public function render()
    {
        return view('livewire.driver.steps.criminal-history-step');
    }
}