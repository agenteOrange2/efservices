<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverCertification;

class DriverCertificationStep extends Component
{
    // Propiedades
    public $driverId;
    public $employmentHistory = [];
    public $signature = '';
    public $certificationAccepted = false;
    
    // Validación
    protected function rules()
    {
        return [
            'signature' => 'required|string',
            'certificationAccepted' => 'accepted'
        ];
    }

    // Inicialización
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        
        if ($this->driverId) {
            $this->loadEmploymentData();
        }
    }

    // Cargar datos de empleo
    protected function loadEmploymentData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        // Cargar historial de empleo
        $this->employmentHistory = $userDriverDetail->employmentCompanies()
            ->with('masterCompany')
            ->orderBy('employed_from', 'desc')
            ->get()
            ->map(function($company) {
                return [
                    'company_name' => $company->masterCompany ? $company->masterCompany->company_name : 'N/A',
                    'address' => $company->masterCompany ? $company->masterCompany->address : 'N/A',
                    'city' => $company->masterCompany ? $company->masterCompany->city : 'N/A',
                    'state' => $company->masterCompany ? $company->masterCompany->state : 'N/A',
                    'zip' => $company->masterCompany ? $company->masterCompany->zip : 'N/A',
                    'employed_from' => $company->employed_from ? $company->employed_from->format('M d, Y') : 'N/A',
                    'employed_to' => $company->employed_to ? $company->employed_to->format('M d, Y') : 'Present'
                ];
            })
            ->toArray();
        
        // Cargar certificación previa si existe
        $certification = $userDriverDetail->certification;
        if ($certification) {
            $this->signature = $certification->signature;
            $this->certificationAccepted = (bool)$certification->is_accepted;
        }
    }

    // Guardar certificación
    public function saveCertification()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Guardar certificación
            $userDriverDetail->certification()->updateOrCreate(
                [],
                [
                    'signature' => $this->signature,
                    'is_accepted' => $this->certificationAccepted,
                    'signed_at' => now()
                ]
            );

            // Marcar la aplicación como completada
            $userDriverDetail->update([
                'current_step' => 13,
                'application_completed' => true
            ]);
            
            DB::commit();
            session()->flash('success', 'Application completed successfully!');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving certification: ' . $e->getMessage());
            return false;
        }
    }

    // Actualizar firma
    public function updateSignature($value)
    {
        $this->signature = $value;
    }

    // Métodos de navegación
    public function next()
    {
        $this->validate();
        
        if ($this->driverId) {
            if ($this->saveCertification()) {
                return redirect()->route('admin.carrier.user_drivers.index', ['carrier' => request()->carrier]);
            }
        }
    }

    public function previous()
    {
        $this->dispatch('prevStep');
    }

    public function saveAndExit()
    {
        if ($this->driverId) {
            $this->saveCertification();
        }
        
        $this->dispatch('saveAndExit');
    }

    // Renderizar
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-certification-step');
    }
}