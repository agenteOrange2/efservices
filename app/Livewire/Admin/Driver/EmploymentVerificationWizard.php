<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Carrier;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\MasterCompany;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\EmploymentVerificationToken;
use App\Mail\EmploymentVerification;

class EmploymentVerificationWizard extends Component
{
    // Control del wizard
    public $currentStep = 1;
    
    // Paso 1: Selección de conductor
    public $selectedCarrierId = null;
    public $searchTerm = '';
    public $drivers = [];
    public $selectedDriverId = null;
    
    // Paso 2: Selección o creación de empresa
    public $useExistingCompany = true;
    public $selectedCompanyId = null;
    public $companySearchTerm = '';
    public $masterCompanies = [];
    public $addToDirectory = true;  // Para nuevas empresas, agregarlas al directorio
    
    // Campos de empresa (tanto para existentes como nuevas)
    public $company_name = '';
    public $company_email = '';
    public $company_address = '';
    public $company_city = '';
    public $company_state = '';
    public $company_zip = '';
    public $company_phone = '';
    public $company_contact = '';
    
    // Paso 3: Detalles de empleo
    public $employed_from = '';
    public $employed_to = '';
    public $positions_held = '';
    public $reason_for_leaving = '';
    public $additional_notes = '';
    
    // Variable para mostrar resultados de la operación
    public $showSuccess = false;
    public $successMessage = '';
    public $showError = false;
    public $errorMessage = '';

    // Reglas de validación por paso
    protected function rules()
    {
        return [
            // Paso 1
            'selectedCarrierId' => 'required',
            'selectedDriverId' => 'required',
            
            // Paso 2
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            
            // Paso 3
            'employed_from' => 'required|date',
            'employed_to' => 'required|date|after_or_equal:employed_from',
            'positions_held' => 'required|string|max:255',
            'reason_for_leaving' => 'required|string|max:255'
        ];
    }

    protected function messages()
    {
        return [
            'selectedCarrierId.required' => 'Debe seleccionar un carrier',
            'selectedDriverId.required' => 'Debe seleccionar un conductor',
            'company_name.required' => 'El nombre de la empresa es obligatorio',
            'company_email.required' => 'El email de la empresa es obligatorio',
            'company_email.email' => 'El formato del email no es válido',
            'employed_from.required' => 'La fecha de inicio es obligatoria',
            'employed_to.required' => 'La fecha de fin es obligatoria',
            'employed_to.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'positions_held.required' => 'Las posiciones ocupadas son obligatorias',
            'reason_for_leaving.required' => 'El motivo de salida es obligatorio'
        ];
    }

    public function mount()
    {
        // Inicializar fechas con valores por defecto
        $this->employed_from = now()->subYears(1)->format('Y-m-d');
        $this->employed_to = now()->format('Y-m-d');
        
        // Cargar listado de empresas para el directorio
        $this->loadMasterCompanies();
    }
    
    // PASO 1: Manejo de conductores
    
    public function updated($propertyName)
    {
        if ($propertyName === 'selectedCarrierId') {
            $this->selectedDriverId = null;
            $this->loadDrivers();
        }
        
        if ($propertyName === 'searchTerm' && !empty($this->selectedCarrierId)) {
            $this->loadDrivers();
        }
        
        if ($propertyName === 'companySearchTerm') {
            $this->loadMasterCompanies();
        }
        
        if ($propertyName === 'useExistingCompany') {
            $this->selectedCompanyId = null;
            $this->resetCompanyFields();
        }
    }
    
    public function loadDrivers()
    {
        if (empty($this->selectedCarrierId)) {
            $this->drivers = [];
            return;
        }
        
        try {
            // Consulta simple para obtener todos los conductores del carrier seleccionado
            $query = UserDriverDetail::with('user')
                ->where('carrier_id', $this->selectedCarrierId);
            
            // Aplicar filtro de búsqueda si existe
            if (!empty($this->searchTerm)) {
                $searchTerm = '%' . $this->searchTerm . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm);
                    })
                    ->orWhere('phone_number', 'like', $searchTerm)
                    ->orWhere('last_name', 'like', $searchTerm);
                });
            }
            
            // Ejecutar la consulta y asignar los resultados
            $this->drivers = $query->take(50)->get();
            
            // Notificar que los conductores se han cargado
            $this->dispatch('drivers-loaded');
            
        } catch (\Exception $e) {
            // En caso de error, registrar y mantener una lista vacía
            Log::error('Error al cargar conductores: ' . $e->getMessage());
            $this->drivers = collect();
        }
    }
    
    public function selectDriver($driverId)
    {
        $this->selectedDriverId = $driverId;
    }
    
    // PASO 2: Manejo de empresas
    
    public function loadMasterCompanies()
    {
        $query = MasterCompany::query();
        
        if (!empty($this->companySearchTerm)) {
            $query->where(function($q) {
                $q->where('company_name', 'like', '%' . $this->companySearchTerm . '%')
                  ->orWhere('email', 'like', '%' . $this->companySearchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $this->companySearchTerm . '%')
                  ->orWhere('address', 'like', '%' . $this->companySearchTerm . '%');
            });
        }
        
        $this->masterCompanies = $query->orderBy('company_name')->take(15)->get();
    }
    
    public function selectMasterCompany($companyId)
    {
        $this->selectedCompanyId = $companyId;
        $company = MasterCompany::find($companyId);
        
        if ($company) {
            $this->company_name = $company->company_name;
            $this->company_email = $company->email;
            $this->company_phone = $company->phone;
            $this->company_address = $company->address;
            $this->company_city = $company->city;
            $this->company_state = $company->state;
            $this->company_zip = $company->zip;
            $this->company_contact = $company->contact_person;
        }
    }
    
    public function resetCompanyFields()
    {
        if (!$this->useExistingCompany) {
            // Si cambia a crear nueva empresa, reiniciar campos
            $this->company_name = '';
            $this->company_email = '';
            $this->company_address = '';
            $this->company_city = '';
            $this->company_state = '';
            $this->company_zip = '';
            $this->company_phone = '';
            $this->company_contact = '';
        }
    }
    
    // Navegación entre pasos
    
    public function nextStep()
    {
        $this->validate($this->getStepValidationRules());
        
        if ($this->currentStep == 1) {
            // Si estamos en paso 1 y pasamos validación, avanzar a paso 2
            $this->currentStep = 2;
        } 
        elseif ($this->currentStep == 2) {
            // Si estamos en paso 2 y pasamos validación, avanzar a paso 3
            $this->currentStep = 3;
        }
    }
    
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    // Obtener las reglas de validación según el paso actual
    protected function getStepValidationRules()
    {
        $rules = [];
        
        switch ($this->currentStep) {
            case 1:
                $rules['selectedCarrierId'] = 'required';
                $rules['selectedDriverId'] = 'required';
                break;
                
            case 2:
                $rules['company_name'] = 'required|string|max:255';
                $rules['company_email'] = 'required|email|max:255';
                break;
                
            case 3:
                $rules['employed_from'] = 'required|date';
                $rules['employed_to'] = 'required|date|after_or_equal:employed_from';
                $rules['positions_held'] = 'required|string|max:255';
                $rules['reason_for_leaving'] = 'required|string|max:255';
                break;
        }
        
        return $rules;
    }
    
    // Enviar solicitud de verificación
    public function sendVerificationRequest()
    {
        // Validar todos los campos
        $this->validate();
        
        DB::beginTransaction();
        try {
            $driver = UserDriverDetail::with('user')->findOrFail($this->selectedDriverId);
            
            // Si se eligió crear nueva empresa y guardarla en directorio
            if (!$this->useExistingCompany && $this->addToDirectory) {
                $masterCompany = MasterCompany::where('company_name', $this->company_name)
                    ->where('email', $this->company_email)
                    ->first();
                    
                if (!$masterCompany) {
                    $masterCompany = new MasterCompany([
                        'company_name' => $this->company_name,
                        'email' => $this->company_email,
                        'phone' => $this->company_phone,
                        'address' => $this->company_address,
                        'city' => $this->company_city,
                        'state' => $this->company_state,
                        'zip' => $this->company_zip,
                        'contact_person' => $this->company_contact,
                        'created_by' => Auth::id()
                    ]);
                    $masterCompany->save();
                }
            }
            
            // Crear el registro de empleo
            $employmentData = [
                'user_driver_detail_id' => $driver->id,
                'company_name' => $this->company_name,
                'email' => $this->company_email,
                'address' => $this->company_address,
                'city' => $this->company_city,
                'state' => $this->company_state,
                'zip' => $this->company_zip,
                'phone' => $this->company_phone,
                'contact_person' => $this->company_contact,
                'employed_from' => $this->employed_from,
                'employed_to' => $this->employed_to,
                'positions_held' => $this->positions_held,
                'reason_for_leaving' => $this->reason_for_leaving,
                'explanation' => $this->additional_notes,
                'email_sent' => true,
                'created_by' => Auth::id()
            ];
            
            // Asignar master_company_id si aplica
            if ($this->useExistingCompany && $this->selectedCompanyId) {
                // Si se seleccionó una empresa existente
                $employmentData['master_company_id'] = $this->selectedCompanyId;
            } elseif (!$this->useExistingCompany && $this->addToDirectory && isset($masterCompany)) {
                // Si se creó una nueva empresa y se guardó en el directorio
                $employmentData['master_company_id'] = $masterCompany->id;
            } else {
                // Valor predeterminado para evitar el error de SQL
                $employmentData['master_company_id'] = null;
            }
            
            $employmentCompany = new DriverEmploymentCompany($employmentData);
            
            $employmentCompany->save();

            // Crear token para verificación
            $token = Str::random(64);
            
            $verificationToken = new EmploymentVerificationToken([
                'token' => $token,
                'driver_id' => $driver->id,
                'employment_company_id' => $employmentCompany->id,
                'email' => $this->company_email,
                'expires_at' => now()->addDays(7),
            ]);
            
            $verificationToken->save();
            
            // Obtener el nombre completo del conductor
            $driverName = $driver->user->name . ' ' . ($driver->last_name ?? '');
            
            // Preparar datos para el correo con formato correcto
            $employmentData = [
                'positions_held' => $this->positions_held,
                'employed_from' => date('d/m/Y', strtotime($this->employed_from)),
                'employed_to' => date('d/m/Y', strtotime($this->employed_to)),
                'reason_for_leaving' => $this->reason_for_leaving,
                'explanation' => $this->additional_notes,
            ];    
            
            // Enviar el correo electrónico
            Mail::to($this->company_email)->send(new EmploymentVerification(
                $this->company_name,
                $driverName,
                $employmentData,
                $token,
                $driver->id,
                $employmentCompany->id
            ));
            
            DB::commit();
            
            Log::info('Solicitud de verificación de empleo enviada correctamente', [
                'driver_id' => $driver->id,
                'company_name' => $this->company_name,
                'email' => $this->company_email
            ]);
            
            // Mostrar mensaje de éxito
            $this->showSuccess = true;
            $this->successMessage = 'Solicitud de verificación enviada correctamente a ' . $this->company_name;
            
            // Reiniciar formulario
            $this->reset([
                'currentStep',
                'selectedDriverId', 
                'company_name', 
                'company_email',
                'company_address',
                'company_city',
                'company_state',
                'company_zip',
                'company_phone',
                'company_contact',
                'employed_from',
                'employed_to',
                'positions_held',
                'reason_for_leaving',
                'additional_notes',
                'selectedCompanyId',
                'useExistingCompany'
            ]);
            
            // Reiniciar a paso 1
            $this->currentStep = 1;
            
            // Recargar conductores
            $this->loadDrivers();
            
            // Emitir evento para actualizar la lista
            $this->dispatch('verification-sent');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al enviar solicitud de verificación de empleo', [
                'driver_id' => $this->selectedDriverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->showError = true;
            $this->errorMessage = 'Error al enviar la solicitud: ' . $e->getMessage();
        }
    }
    
    // Cancelar proceso
    public function cancel()
    {
        // Reiniciar formulario
        $this->reset([
            'currentStep',
            'selectedDriverId', 
            'company_name', 
            'company_email',
            'company_address',
            'company_city',
            'company_state',
            'company_zip',
            'company_phone',
            'company_contact',
            'employed_from',
            'employed_to',
            'positions_held',
            'reason_for_leaving',
            'additional_notes',
            'selectedCompanyId',
            'useExistingCompany'
        ]);
        
        // Reiniciar a paso 1
        $this->currentStep = 1;
        
        // Emitir evento para cerrar el modal
        $this->dispatch('close-wizard');
    }

    // Getters para las propiedades computadas
    
    public function getCarriersProperty()
    {
        return Carrier::orderBy('name')->get();
    }
    
    public function getSelectedDriverProperty()
    {
        if (!$this->selectedDriverId) {
            return null;
        }
        
        return UserDriverDetail::with(['user', 'carrier'])->find($this->selectedDriverId);
    }

    public function render()
    {
        return view('livewire.admin.driver.employment-verification-wizard', [
            'carriers' => $this->getCarriersProperty(),
            'selectedDriver' => $this->getSelectedDriverProperty()
        ]);
    }
}
