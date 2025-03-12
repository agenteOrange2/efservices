<?php

namespace App\Livewire\Admin\Driver\Recruitment;

use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Services\Admin\DriverStepService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DriverRecruitmentReview extends Component
{
    public $driverId;
    public $driver;
    public $application;
    public $steps = [];
    public $stepsStatus = [];
    public $currentTab = 'general';
    public $checklistItems = [];
    public $rejectionReason = '';
    public $completionPercentage = 0;

    public function mount($driverId)
    {
        $this->driverId = $driverId;
        $this->loadDriverData();
        $this->initializeChecklist();
    }

    public function loadDriverData()
    {
        $this->driver = UserDriverDetail::with([
            'user', 
            'carrier', 
            'application', 
            'licenses', 
            'medicalQualification',
            'experiences',
            'trainingSchools',
            'trafficConvictions',
            'accidents',
            'fmcsrData',
            'workHistories',
            'unemploymentPeriods'
        ])->findOrFail($this->driverId);
        
        // Convertir date_of_birth a objeto Carbon si es una string
        if ($this->driver->date_of_birth && is_string($this->driver->date_of_birth)) {
            $this->driver->date_of_birth = Carbon::parse($this->driver->date_of_birth);
        }
        
        // Procesar otras fechas que puedan necesitar conversión a Carbon
        $this->processDateFields();
        
        $this->application = $this->driver->application;
        
        // Cargar estados de los pasos
        $stepService = new DriverStepService();
        $this->stepsStatus = $stepService->getStepsStatus($this->driver);
        $this->completionPercentage = $stepService->calculateCompletionPercentage($this->driver);
    }
    
    /**
     * Procesa las fechas para asegurar que son objetos Carbon
     */
    protected function processDateFields()
    {
        // Procesar fechas en licencias
        if ($this->driver->licenses) {
            foreach ($this->driver->licenses as $license) {
                if (is_string($license->expiration_date)) {
                    $license->expiration_date = Carbon::parse($license->expiration_date);
                }
            }
        }
        
        // Procesar fechas en calificación médica
        if ($this->driver->medicalQualification) {
            $medical = $this->driver->medicalQualification;
            
            if (is_string($medical->medical_card_expiration_date)) {
                $medical->medical_card_expiration_date = Carbon::parse($medical->medical_card_expiration_date);
            }
            
            if ($medical->suspension_date && is_string($medical->suspension_date)) {
                $medical->suspension_date = Carbon::parse($medical->suspension_date);
            }
            
            if ($medical->termination_date && is_string($medical->termination_date)) {
                $medical->termination_date = Carbon::parse($medical->termination_date);
            }
        }
        
        // Procesar fechas en escuelas de capacitación
        if ($this->driver->trainingSchools) {
            foreach ($this->driver->trainingSchools as $school) {
                if (is_string($school->date_start)) {
                    $school->date_start = Carbon::parse($school->date_start);
                }
                if (is_string($school->date_end)) {
                    $school->date_end = Carbon::parse($school->date_end);
                }
            }
        }
        
        // Procesar fechas en infracciones de tráfico
        if ($this->driver->trafficConvictions) {
            foreach ($this->driver->trafficConvictions as $conviction) {
                if (is_string($conviction->conviction_date)) {
                    $conviction->conviction_date = Carbon::parse($conviction->conviction_date);
                }
            }
        }
        
        // Procesar fechas en accidentes
        if ($this->driver->accidents) {
            foreach ($this->driver->accidents as $accident) {
                if (is_string($accident->accident_date)) {
                    $accident->accident_date = Carbon::parse($accident->accident_date);
                }
            }
        }
        
        // Procesar fechas en historial de empleo
        if ($this->driver->workHistories) {
            foreach ($this->driver->workHistories as $history) {
                if (is_string($history->start_date)) {
                    $history->start_date = Carbon::parse($history->start_date);
                }
                if (is_string($history->end_date)) {
                    $history->end_date = Carbon::parse($history->end_date);
                }
            }
        }
        
        // Procesar fechas en periodos de desempleo
        if ($this->driver->unemploymentPeriods) {
            foreach ($this->driver->unemploymentPeriods as $period) {
                if (is_string($period->start_date)) {
                    $period->start_date = Carbon::parse($period->start_date);
                }
                if (is_string($period->end_date)) {
                    $period->end_date = Carbon::parse($period->end_date);
                }
            }
        }
        
        // Procesar fechas en empresas de empleo
        if ($this->driver->employmentCompanies) {
            foreach ($this->driver->employmentCompanies as $company) {
                if (is_string($company->employed_from)) {
                    $company->employed_from = Carbon::parse($company->employed_from);
                }
                if (is_string($company->employed_to)) {
                    $company->employed_to = Carbon::parse($company->employed_to);
                }
            }
        }
    }

    public function initializeChecklist()
    {
        // Define los elementos que el reclutador debe verificar
        $this->checklistItems = [
            'general_info' => [
                'checked' => false,
                'label' => 'Información general completa y válida'
            ],
            'contact_info' => [
                'checked' => false,
                'label' => 'Información de contacto verificada'
            ],
            'address_info' => [
                'checked' => false,
                'label' => 'Dirección actual y historial validados'
            ],
            'license_info' => [
                'checked' => false,
                'label' => 'Licencia de conducir válida y vigente'
            ],
            'license_image' => [
                'checked' => false,
                'label' => 'Imágenes de licencia adjuntas y legibles'
            ],
            'medical_info' => [
                'checked' => false,
                'label' => 'Información médica completa'
            ],
            'medical_image' => [
                'checked' => false,
                'label' => 'Tarjeta médica adjunta y vigente'
            ],
            'experience_info' => [
                'checked' => false,
                'label' => 'Experiencia de conducción verificada'
            ],
            'history_info' => [
                'checked' => false,
                'label' => 'Historial laboral completo (10 años)'
            ]
        ];
    }

    public function changeTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function toggleChecklistItem($item)
    {
        if (isset($this->checklistItems[$item])) {
            $this->checklistItems[$item]['checked'] = !$this->checklistItems[$item]['checked'];
        }
    }

    public function isChecklistComplete()
    {
        foreach ($this->checklistItems as $item) {
            if (!$item['checked']) {
                return false;
            }
        }
        return true;
    }

    public function approveApplication()
    {
        if (!$this->isChecklistComplete()) {
            $this->addError('checklist', 'Debe completar toda la lista de verificación antes de aprobar.');
            return;
        }

        // Actualizar estado de la aplicación a aprobado
        $this->application->update([
            'status' => DriverApplication::STATUS_APPROVED,
            'completed_at' => now()
        ]);

        // Actualizar estado del driver
        $this->driver->update([
            'status' => UserDriverDetail::STATUS_ACTIVE
        ]);

        // Opcional: Enviar notificación al conductor
        // Notification::send($this->driver->user, new DriverApplicationApprovedNotification($this->driver));

        // Actualizar datos locales
        $this->loadDriverData();

        // Notificar a otros componentes
        $this->dispatch('applicationStatusUpdated');
        
        // Mostrar mensaje de éxito
        session()->flash('message', 'La solicitud ha sido aprobada correctamente.');
    }

    public function rejectApplication()
    {
        // Validar razón de rechazo
        $this->validate([
            'rejectionReason' => 'required|min:10'
        ], [
            'rejectionReason.required' => 'Debe proporcionar una razón para el rechazo.',
            'rejectionReason.min' => 'La razón debe tener al menos 10 caracteres.'
        ]);

        // Actualizar estado de la aplicación a rechazado
        $this->application->update([
            'status' => DriverApplication::STATUS_REJECTED,
            'rejection_reason' => $this->rejectionReason,
            'completed_at' => now()
        ]);

        // Opcional: Enviar notificación al conductor
        // Notification::send($this->driver->user, new DriverApplicationRejectedNotification($this->driver, $this->rejectionReason));

        // Actualizar datos locales
        $this->loadDriverData();

        // Notificar a otros componentes
        $this->dispatch('applicationStatusUpdated');
        
        // Limpiar el campo de razón
        $this->rejectionReason = '';
        
        // Mostrar mensaje
        session()->flash('message', 'La solicitud ha sido rechazada.');
    }

    public function render()
    {
        $this->processDateFields();
        return view('livewire.admin.driver.recruitment.driver-recruitment-review');
    }
}