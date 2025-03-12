<?php

namespace App\Services\Admin;

use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;

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

    /**
     * Obtener el estado actual de todos los pasos para un driver específico
     */
    public function getStepsStatus(UserDriverDetail $userDriverDetail): array
    {
        return [
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
        if ($userDriverDetail->application && 
            $userDriverDetail->application->addresses && 
            $userDriverDetail->application->addresses->where('primary', true)->count() > 0) {
            
            $primaryAddress = $userDriverDetail->application->addresses->where('primary', true)->first();
            
            // Si no ha vivido ahí por tres años, verificar que tiene direcciones anteriores
            if (!$primaryAddress->lived_three_years && 
                $userDriverDetail->application->addresses->where('primary', false)->count() == 0) {
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
        if ($userDriverDetail->application && 
            $userDriverDetail->application->details && 
            $userDriverDetail->application->details->applying_position && 
            $userDriverDetail->application->details->applying_location && 
            $userDriverDetail->application->details->eligible_to_work) {
            
            // Verificar campos específicos según las respuestas
            if (($userDriverDetail->application->details->applying_position === 'other' && 
                !$userDriverDetail->application->details->applying_position_other) ||
                ($userDriverDetail->application->details->has_twic_card && 
                !$userDriverDetail->application->details->twic_expiration_date) ||
                ($userDriverDetail->application->details->how_did_hear === 'other' && 
                !$userDriverDetail->application->details->how_did_hear_other) ||
                ($userDriverDetail->application->details->how_did_hear === 'employee_referral' && 
                !$userDriverDetail->application->details->referral_employee_name)) {
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
        // Si respondió explícitamente sobre capacitación (sí o no)
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            isset($userDriverDetail->application->details->has_attended_training_school)
        ) {
            // Si indicó que asistió a capacitación y tiene registros
            if ($userDriverDetail->application->details->has_attended_training_school && 
                $userDriverDetail->trainingSchools()->exists()) {
                return self::STATUS_COMPLETED;
            }

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
        // Si respondió explícitamente sobre infracciones (sí o no)
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            isset($userDriverDetail->application->details->has_traffic_convictions)
        ) {
            // Si indicó que tiene infracciones y tiene registros
            if ($userDriverDetail->application->details->has_traffic_convictions && 
                $userDriverDetail->trafficConvictions()->exists()) {
                return self::STATUS_COMPLETED;
            }

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
        // Si respondió explícitamente sobre accidentes (sí o no)
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            isset($userDriverDetail->application->details->has_accidents)
        ) {
            // Si indicó que tiene accidentes y tiene registros
            if ($userDriverDetail->application->details->has_accidents && 
                $userDriverDetail->accidents()->exists()) {
                return self::STATUS_COMPLETED;
            }

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
        // Si la aplicación existe y se ha marcado como completado el historial
        if (
            $userDriverDetail->application &&
            $userDriverDetail->application->details &&
            $userDriverDetail->application->details->has_completed_employment_history
        ) {
            // Verificar que hay al menos una empresa o un período de desempleo
            $hasEmploymentData = $userDriverDetail->employmentCompanies()->exists() ||
                ($userDriverDetail->application->details->has_unemployment_periods &&
                    $userDriverDetail->unemploymentPeriods()->exists());

            if ($hasEmploymentData) {
                return self::STATUS_COMPLETED;
            }

            return self::STATUS_PENDING;
        }

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