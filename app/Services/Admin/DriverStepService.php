<?php

namespace App\Services\Admin;

use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;

class DriverStepService
{
    const STEP_GENERAL = 1;
    const STEP_LICENSES = 2;
    const STEP_MEDICAL = 3;
    const STEP_TRAINING = 4;
    const STEP_TRAFFIC = 5;
    const STEP_ACCIDENT = 6;
    
    const STATUS_COMPLETED = 'completed';  // Verde
    const STATUS_PENDING = 'pending';      // Naranja
    const STATUS_MISSING = 'missing';      // Rojo
    
    /**
     * Obtener el estado actual de todos los pasos para un driver específico
     */
    public function getStepsStatus(UserDriverDetail $userDriverDetail): array
    {
        return [
            self::STEP_GENERAL => $this->checkGeneralStep($userDriverDetail),
            self::STEP_LICENSES => $this->checkLicensesStep($userDriverDetail),
            self::STEP_MEDICAL => $this->checkMedicalStep($userDriverDetail),
            self::STEP_TRAINING => $this->checkTrainingStep($userDriverDetail),
            self::STEP_TRAFFIC => $this->checkTrafficStep($userDriverDetail),
            self::STEP_ACCIDENT => $this->checkAccidentStep($userDriverDetail),
        ];
    }
    
    /**
     * Verificar si el paso general está completo
     */
    private function checkGeneralStep(UserDriverDetail $userDriverDetail): string
    {
        // Si existe el usuario, tiene detalles básicos y dirección
        if (
            $userDriverDetail->id && 
            $userDriverDetail->user && 
            $userDriverDetail->user->email && 
            $userDriverDetail->phone && 
            $userDriverDetail->application && 
            $userDriverDetail->application->addresses->where('primary', true)->count() > 0
        ) {
            return self::STATUS_COMPLETED;
        }
        
        return self::STATUS_MISSING;
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
        
        return $this->getPreviousStepStatus($userDriverDetail, self::STEP_GENERAL) === self::STATUS_COMPLETED 
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
        if ($userDriverDetail->application && 
            $userDriverDetail->application->details && 
            $userDriverDetail->application->details->has_work_history !== null) {
            
            // Si indicó que asistió a capacitación y tiene registros
            if ($userDriverDetail->trainingSchools()->exists()) {
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
        if ($userDriverDetail->application && 
            $userDriverDetail->application->details && 
            $userDriverDetail->application->details->has_traffic_convictions !== null) {
            
            // Si indicó que tiene infracciones y tiene registros
            if ($userDriverDetail->trafficConvictions()->exists()) {
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
        if ($userDriverDetail->application && 
            $userDriverDetail->application->details && 
            $userDriverDetail->application->details->has_accidents !== null) {
            
            // Si indicó que tiene accidentes y tiene registros
            if ($userDriverDetail->accidents()->exists()) {
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
     * Obtener el estado del paso anterior
     */
// In DriverStepService.php
private function getPreviousStepStatus(UserDriverDetail $userDriverDetail, int $currentStep): string
{
    // This is creating a recursive loop
    // $steps = $this->getStepsStatus($userDriverDetail);
    
    // Instead, directly check the previous step
    $previousStep = $currentStep - 1;
    if ($previousStep < self::STEP_GENERAL) {
        return self::STATUS_COMPLETED; // The first step doesn't have a previous one
    }
    
    // Directly call the appropriate check method based on the step number
    switch($previousStep) {
        case self::STEP_GENERAL:
            return $this->checkGeneralStep($userDriverDetail);
        case self::STEP_LICENSES:
            return $this->checkLicensesStep($userDriverDetail);
        case self::STEP_MEDICAL:
            return $this->checkMedicalStep($userDriverDetail);
        case self::STEP_TRAINING:
            return $this->checkTrainingStep($userDriverDetail);
        case self::STEP_TRAFFIC:
            return $this->checkTrafficStep($userDriverDetail);
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
        return self::STEP_GENERAL; // Si todo está completo, volvemos al principio
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