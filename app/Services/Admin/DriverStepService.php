<?php

namespace App\Services\Admin;

use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverRecruitmentVerification;

class DriverStepService
{
    // Cambiamos los pasos para agrupar los 3 primeros como "Información General"
    const STEP_GENERAL_INFO = 1;  // Combina General, Address y Application
    const STEP_LICENSES = 2;
    const STEP_MEDICAL = 3;
    const STEP_TRAINING = 4;
    const STEP_TRAFFIC = 5;
    const STEP_ACCIDENT = 6;
    const STEP_FMCSR = 7;
    const STEP_EMPLOYMENT_HISTORY = 8;
    const STEP_COMPANY_POLICIES = 9;
    const STEP_CRIMINAL_HISTORY = 10;
    const STEP_APPLICATION_CERTIFICATION = 11;

    const STATUS_COMPLETED = 'completed';  // Verde
    const STATUS_PENDING = 'pending';      // Naranja
    const STATUS_MISSING = 'missing';      // Rojo

    // Pasos que requieren verificación manual antes de marcarlos como completados
    private $stepsRequiringVerification = [
        self::STEP_TRAINING,
        self::STEP_TRAFFIC,
        self::STEP_ACCIDENT
    ];

    // Mapeo de pasos a elementos del checklist
    // Mapeo de pasos a elementos del checklist
    private $stepToChecklistMapping = [
        self::STEP_GENERAL_INFO => ['general_info', 'contact_info', 'address_info'],
        self::STEP_LICENSES => ['license_info', 'license_image'],
        self::STEP_MEDICAL => ['medical_info', 'medical_image'],
        self::STEP_TRAINING => ['experience_info', 'training_verified'],  // Añadir el nuevo elemento
        self::STEP_TRAFFIC => ['experience_info', 'traffic_verified'],    // Añadir el nuevo elemento
        self::STEP_ACCIDENT => ['experience_info', 'accident_verified'],  // Añadir el nuevo elemento
        self::STEP_FMCSR => ['experience_info'],
        self::STEP_EMPLOYMENT_HISTORY => ['history_info'],
        self::STEP_COMPANY_POLICIES => ['experience_info'],
        self::STEP_CRIMINAL_HISTORY => ['criminal_check'],
        self::STEP_APPLICATION_CERTIFICATION => ['documents_checked'],
    ];
    /**
     * Obtener el estado actual de todos los pasos para un driver específico
     */
    public function getStepsStatus(UserDriverDetail $userDriverDetail, $checklistItems = null): array
    {

        if ($userDriverDetail->application && $userDriverDetail->application->status === DriverApplication::STATUS_APPROVED) {
            return [
                self::STEP_GENERAL_INFO => self::STATUS_COMPLETED,
                self::STEP_LICENSES => self::STATUS_COMPLETED,
                self::STEP_MEDICAL => self::STATUS_COMPLETED,
                self::STEP_TRAINING => self::STATUS_COMPLETED,
                self::STEP_TRAFFIC => self::STATUS_COMPLETED,
                self::STEP_ACCIDENT => self::STATUS_COMPLETED,
                self::STEP_FMCSR => self::STATUS_COMPLETED,
                self::STEP_EMPLOYMENT_HISTORY => self::STATUS_COMPLETED,
                self::STEP_COMPANY_POLICIES => self::STATUS_COMPLETED,
                self::STEP_CRIMINAL_HISTORY => self::STATUS_COMPLETED,
                self::STEP_APPLICATION_CERTIFICATION => self::STATUS_COMPLETED,
            ];
        }

        // Obtener verificación más reciente si no se proporcionó checklist
        if ($checklistItems === null && $userDriverDetail->application) {
            $verification = DriverRecruitmentVerification::where('driver_application_id', $userDriverDetail->application->id)
                ->latest('verified_at')
                ->first();
                
            $checklistItems = $verification ? $verification->verification_items : [];
        }

        // Obtener los estados basados solo en los datos
        $dataBasedStatuses = [
            self::STEP_GENERAL_INFO => $this->checkGeneralInfoStep($userDriverDetail),
            self::STEP_LICENSES => $this->checkLicensesStep($userDriverDetail),
            self::STEP_MEDICAL => $this->checkMedicalStep($userDriverDetail),
            self::STEP_TRAINING => $this->checkTrainingStep($userDriverDetail),
            self::STEP_TRAFFIC => $this->checkTrafficStep($userDriverDetail),
            self::STEP_ACCIDENT => $this->checkAccidentStep($userDriverDetail),
            self::STEP_FMCSR => $this->checkFmcsrStep($userDriverDetail),
            self::STEP_EMPLOYMENT_HISTORY => $this->checkEmploymentHistoryStep($userDriverDetail),
            self::STEP_COMPANY_POLICIES => $this->checkCompanyPoliciesStep($userDriverDetail),
            self::STEP_CRIMINAL_HISTORY => $this->checkCriminalHistoryStep($userDriverDetail),
            self::STEP_APPLICATION_CERTIFICATION => $this->checkApplicationCertificationStep($userDriverDetail),
        ];

        // Aplicar la regla para todos los pasos: si tienen datos completos 
        // pero no están verificados, mostrar como pending
        $combinedStatuses = [];
        foreach ($dataBasedStatuses as $step => $status) {
            // Si el paso está "MISSING", lo dejamos así
            if ($status === self::STATUS_MISSING) {
                $combinedStatuses[$step] = self::STATUS_MISSING;
                continue;
            }
            
            // Si el paso está completado según los datos:
            if ($status === self::STATUS_COMPLETED) {
                // Solo lo marcamos como completado si está verificado en el checklist
                if ($this->isStepVerifiedInChecklist($step, $checklistItems)) {
                    $combinedStatuses[$step] = self::STATUS_COMPLETED;
                } else {
                    // Si no está verificado, lo marcamos como pendiente
                    $combinedStatuses[$step] = self::STATUS_PENDING;
                }
            } else {
                // Si no está completado según los datos, mantener el estado original
                $combinedStatuses[$step] = $status;
            }
        }

        return $combinedStatuses;
    }


    /**
     * Verifica si un paso ha sido marcado como verificado en el checklist
     */
    private function isStepVerifiedInChecklist($step, $checklistItems)
    {
        // Si el paso no tiene items de checklist asociados, considerar como no verificado
        if (!isset($this->stepToChecklistMapping[$step])) {
            return false;
        }

        // Verificar si al menos uno de los items asociados a este paso están marcados
        foreach ($this->stepToChecklistMapping[$step] as $checklistItem) {
            if (isset($checklistItems[$checklistItem]) && $checklistItems[$checklistItem] === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si el paso general está completo
     */
    private function checkGeneralInfoStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar información general
        $generalComplete = $userDriverDetail->id &&
            $userDriverDetail->user &&
            $userDriverDetail->user->email &&
            $userDriverDetail->phone;

        if (!$generalComplete) {
            return self::STATUS_MISSING;
        }

        // Verificar dirección
        $addressComplete = false;
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->addresses &&
            $userDriverDetail->application->addresses->where('primary', true)->count() > 0
        ) {

            $primaryAddress = $userDriverDetail->application->addresses->where('primary', true)->first();

            // Si no ha vivido ahí por tres años, verificar que tiene direcciones anteriores
            if (
                !$primaryAddress->lived_three_years &&
                $userDriverDetail->application->addresses->where('primary', false)->count() == 0
            ) {
                // Falta historial de direcciones
            } else {
                $addressComplete = true;
            }
        }

        if (!$addressComplete) {
            return self::STATUS_PENDING;
        }

        // Verificar detalles de aplicación
        $applicationComplete = false;
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            $userDriverDetail->application->details->applying_position &&
            $userDriverDetail->application->details->applying_location &&
            $userDriverDetail->application->details->eligible_to_work
        ) {

            // Verificar campos específicos según las respuestas
            if (($userDriverDetail->application->details->applying_position === 'other' &&
                    !$userDriverDetail->application->details->applying_position_other) ||
                ($userDriverDetail->application->details->has_twic_card &&
                    !$userDriverDetail->application->details->twic_expiration_date) ||
                ($userDriverDetail->application->details->how_did_hear === 'other' &&
                    !$userDriverDetail->application->details->how_did_hear_other) ||
                ($userDriverDetail->application->details->how_did_hear === 'employee_referral' &&
                    !$userDriverDetail->application->details->referral_employee_name)
            ) {
                // Hay campos pendientes
            } else {
                $applicationComplete = true;
            }
        }

        if (!$applicationComplete) {
            return self::STATUS_PENDING;
        }

        // Si llegamos aquí, todos los pasos están completos
        return self::STATUS_COMPLETED;
    }



    /**
     * Verificar el estado del paso de licencias
     */
    private function checkLicensesStep(UserDriverDetail $userDriverDetail): string
    {
        // Si al menos tiene una licencia, está pendiente, si tiene todas completas está completado
        if ($userDriverDetail->licenses()->exists()) {
            // Verificar si también tiene experiencia de conducción
            if ($userDriverDetail->experiences()->exists()) {
                return self::STATUS_COMPLETED;
            }
            return self::STATUS_PENDING;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_GENERAL_INFO) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }
    /**
     * Verificar el estado del paso médico
     */
    private function checkMedicalStep(UserDriverDetail $userDriverDetail): string
    {
        // Si tiene información médica se considera completo
        if ($userDriverDetail->medicalQualification()->exists()) {
            return self::STATUS_COMPLETED;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_LICENSES) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Verificar el estado del paso de capacitación
     */
    private function checkTrainingStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar si el paso ha sido verificado manualmente en el checklist
        if ($userDriverDetail->application) {
            $verification = DriverRecruitmentVerification::where('driver_application_id', $userDriverDetail->application->id)
                ->latest('verified_at')
                ->first();
                
            $checklistItems = $verification ? $verification->verification_items : [];
            
            // Si ha sido verificado manualmente en el checklist, retornar completado
            if ($this->isStepVerifiedInChecklist(self::STEP_TRAINING, $checklistItems)) {
                return self::STATUS_COMPLETED;
            }
        }
        
        // Primero verificar si hay datos directamente
        if ($userDriverDetail->trainingSchools()->exists()) {
            return self::STATUS_COMPLETED;
        }

        // Si no hay datos, entonces verificar los flags
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            isset($userDriverDetail->application->details->has_attended_training_school)
        ) {
            // Si indicó que no asistió a capacitación
            if ($userDriverDetail->application->details->has_attended_training_school === false) {
                return self::STATUS_COMPLETED;
            }

            return self::STATUS_PENDING;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_MEDICAL) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Verificar el estado del paso de infracciones de tráfico
     */
    private function checkTrafficStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar si el paso ha sido verificado manualmente en el checklist
        if ($userDriverDetail->application) {
            $verification = DriverRecruitmentVerification::where('driver_application_id', $userDriverDetail->application->id)
                ->latest('verified_at')
                ->first();
                
            $checklistItems = $verification ? $verification->verification_items : [];
            
            // Si ha sido verificado manualmente en el checklist, retornar completado
            if ($this->isStepVerifiedInChecklist(self::STEP_TRAFFIC, $checklistItems)) {
                return self::STATUS_COMPLETED;
            }
        }
        
        // Primero verificar si hay datos directamente
        if ($userDriverDetail->trafficConvictions()->exists()) {
            return self::STATUS_COMPLETED;
        }

        // Si no hay datos, entonces verificar los flags
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            isset($userDriverDetail->application->details->has_traffic_convictions)
        ) {
            // Si indicó que no tiene infracciones
            if ($userDriverDetail->application->details->has_traffic_convictions === false) {
                return self::STATUS_COMPLETED;
            }

            return self::STATUS_PENDING;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_TRAINING) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Verificar el estado del paso de accidentes
     */
    private function checkAccidentStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar si el paso ha sido verificado manualmente en el checklist
        if ($userDriverDetail->application) {
            $verification = DriverRecruitmentVerification::where('driver_application_id', $userDriverDetail->application->id)
                ->latest('verified_at')
                ->first();
                
            $checklistItems = $verification ? $verification->verification_items : [];
            
            // Si ha sido verificado manualmente en el checklist, retornar completado
            if ($this->isStepVerifiedInChecklist(self::STEP_ACCIDENT, $checklistItems)) {
                return self::STATUS_COMPLETED;
            }
        }
        
        // Primero verificar si hay datos directamente
        if ($userDriverDetail->accidents()->exists()) {
            return self::STATUS_COMPLETED;
        }

        // Si no hay datos, entonces verificar los flags
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            isset($userDriverDetail->application->details->has_accidents)
        ) {
            // Si indicó que no tiene accidentes
            if ($userDriverDetail->application->details->has_accidents === false) {
                return self::STATUS_COMPLETED;
            }

            return self::STATUS_PENDING;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_TRAFFIC) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }
    /**
     * Verificar el estado del paso FMCSR
     */
    private function checkFmcsrStep(UserDriverDetail $userDriverDetail): string
    {
        $fmcsrData = $userDriverDetail->fmcsrData;

        if ($fmcsrData) {
            // Si tiene datos FMCSR con consentimiento al registro de conducción, está completado
            if ($fmcsrData->consent_driving_record) {
                return self::STATUS_COMPLETED;
            }

            return self::STATUS_PENDING;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_ACCIDENT) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Verificar el estado del paso de historial de empleo
     */
    private function checkEmploymentHistoryStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar si existen datos de historial de empleo en cualquiera de las relaciones posibles
        $hasWorkHistories = $userDriverDetail->workHistories()->exists();
        $hasRelatedEmployments = $userDriverDetail->relatedEmployments()->exists();
        $hasUnemploymentPeriods = $userDriverDetail->unemploymentPeriods()->exists();
        $hasEmploymentCompanies = $userDriverDetail->employmentCompanies()->exists();
        
        // Si tiene datos en cualquiera de estas relaciones, consideramos que tiene historial de empleo
        if ($hasWorkHistories || $hasRelatedEmployments || $hasUnemploymentPeriods || $hasEmploymentCompanies) {
            // Contar el número total de registros de historial
            $totalRecords = 
                ($hasWorkHistories ? $userDriverDetail->workHistories()->count() : 0) +
                ($hasRelatedEmployments ? $userDriverDetail->relatedEmployments()->count() : 0) +
                ($hasUnemploymentPeriods ? $userDriverDetail->unemploymentPeriods()->count() : 0) +
                ($hasEmploymentCompanies ? $userDriverDetail->employmentCompanies()->count() : 0);
                
            // Si tiene al menos un registro de historial, consideramos que está completado
            if ($totalRecords > 0) {
                // Marcar como completado automáticamente
                return self::STATUS_COMPLETED;
            }
            
            return self::STATUS_PENDING;
        }
        
        // Si la aplicación existe y se ha marcado como completado el historial
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            $userDriverDetail->application->details->has_completed_employment_history
        ) {
            // Si se marcó como completado pero no hay datos, está pendiente
            return self::STATUS_PENDING;
        }

        // Si no tiene datos y no se ha marcado como completado, está missing
        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_FMCSR) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Verificar el estado del paso de políticas de la compañía
     */
    private function checkCompanyPoliciesStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar si existe la política de la compañía y todos los consentimientos
        if ($userDriverDetail->companyPolicy) {
            $policy = $userDriverDetail->companyPolicy;
            if (
                $policy->consent_all_policies_attached &&
                $policy->substance_testing_consent &&
                $policy->authorization_consent &&
                $policy->fmcsa_clearinghouse_consent
            ) {
                return self::STATUS_COMPLETED;
            }
            return self::STATUS_PENDING;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_EMPLOYMENT_HISTORY) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Verificar el estado del paso de historial criminal
     */
    private function checkCriminalHistoryStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar si existe el historial criminal y los consentimientos
        if ($userDriverDetail->criminalHistory) {
            $criminal = $userDriverDetail->criminalHistory;
            if (
                $criminal->fcra_consent &&
                $criminal->background_info_consent
            ) {
                return self::STATUS_COMPLETED;
            }
            return self::STATUS_PENDING;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_COMPANY_POLICIES) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Verificar el estado del paso de certificación de la aplicación
     */
    private function checkApplicationCertificationStep(UserDriverDetail $userDriverDetail): string
    {
        // Verificar si existe la certificación
        if ($userDriverDetail->certification && $userDriverDetail->certification->is_accepted) {
            return self::STATUS_COMPLETED;
        }

        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_CRIMINAL_HISTORY) === self::STATUS_COMPLETED
            ? self::STATUS_MISSING
            : self::STATUS_MISSING;
    }

    /**
     * Obtener el estado del paso anterior
     */
    private function getPreviousStepStatus(UserDriverDetail $userDriverDetail, int $currentStep): string
    {
        // Directamente verificar el paso anterior
        $previousStep = $currentStep - 1;
        if ($previousStep < self::STEP_GENERAL_INFO) {
            return self::STATUS_COMPLETED; // El primer paso no tiene anterior
        }

        // Llamar directamente al método de verificación adecuado según el número de paso
        switch ($previousStep) {
            case self::STEP_GENERAL_INFO:
                return $this->checkGeneralInfoStep($userDriverDetail);
            case self::STEP_LICENSES:
                return $this->checkLicensesStep($userDriverDetail);
            case self::STEP_MEDICAL:
                return $this->checkMedicalStep($userDriverDetail);
            case self::STEP_TRAINING:
                return $this->checkTrainingStep($userDriverDetail);
            case self::STEP_TRAFFIC:
                return $this->checkTrafficStep($userDriverDetail);
            case self::STEP_ACCIDENT:
                return $this->checkAccidentStep($userDriverDetail);
            case self::STEP_FMCSR:
                return $this->checkFmcsrStep($userDriverDetail);
            case self::STEP_EMPLOYMENT_HISTORY:
                return $this->checkEmploymentHistoryStep($userDriverDetail);
            case self::STEP_COMPANY_POLICIES:
                return $this->checkCompanyPoliciesStep($userDriverDetail);
            case self::STEP_CRIMINAL_HISTORY:
                return $this->checkCriminalHistoryStep($userDriverDetail);
            case self::STEP_APPLICATION_CERTIFICATION:
                return $this->checkApplicationCertificationStep($userDriverDetail);
            default:
                return self::STATUS_MISSING;
        }
    }

    /**
     * Obtener el próximo paso recomendado
     */
    public function getNextStep(UserDriverDetail $userDriverDetail): int
    {
        $steps = $this->getStepsStatus($userDriverDetail);
        foreach ($steps as $step => $status) {
            if ($status !== self::STATUS_COMPLETED) {
                return $step;
            }
        }
        return self::STEP_GENERAL_INFO; // Si todo está completo, volvemos al principio
    }

    /**
     * Calcular el porcentaje de completitud general
     */
    public function calculateCompletionPercentage(UserDriverDetail $userDriverDetail): int
    {
        // Si la aplicación está aprobada, retornar 100%
        if ($userDriverDetail->application && $userDriverDetail->application->status === DriverApplication::STATUS_APPROVED) {
            return 100;
        }
        
        $steps = $this->getStepsStatus($userDriverDetail);
        $completedSteps = 0;
    
        foreach ($steps as $status) {
            if ($status === self::STATUS_COMPLETED) {
                $completedSteps++;
            } else if ($status === self::STATUS_PENDING) {
                $completedSteps += 0.5; // Pasos pendientes cuentan la mitad
            }
        }
    
        return round(($completedSteps / count($steps)) * 100);
    }

    /**
     * Actualizar el paso actual del driver
     */
    public function updateCurrentStep(UserDriverDetail $userDriverDetail, int $step): void
    {
        $userDriverDetail->update(['current_step' => $step]);
        Log::info('Paso actual actualizado para el driver', [
            'driver_id' => $userDriverDetail->id,
            'current_step' => $step
        ]);
    }
}
