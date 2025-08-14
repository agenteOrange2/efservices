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

class BulkEmploymentVerification extends Component
{
    // Control del modal
    public $showModal = false;
    public $currentStep = 1; // Nuevo: control de pasos del wizard
    
    // Selección de carrier y driver
    public $selectedCarrierId = null;
    public $searchTerm = '';
    public $drivers = [];
    public $selectedDriverId = null;
    
    // Selección de compañía
    public $useExistingCompany = true; // Nuevo: determina si usar una existente o crear una
    public $selectedCompanyId = null;  // Nuevo: ID de la compañía seleccionada
    public $companies = [];            // Nuevo: listado de compañías
    
    // Datos del formulario de verificación
    public $company_name = '';
    public $company_email = '';
    public $company_address = '';      // Nuevo: campos adicionales para nueva compañía
    public $company_city = '';
    public $company_state = '';
    public $company_zip = '';
    public $company_phone = '';
    public $company_contact = '';
    public $employed_from = '';
    public $employed_to = '';
    public $positions_held = '';
    public $reason_for_leaving = '';
    public $additional_notes = '';
    
    // Lista de verificaciones enviadas
    public $verificaciones = [];

    protected $rules = [
        'selectedDriverId' => 'required',
        'company_name' => 'required|string|max:255',
        'company_email' => 'required|email|max:255',
        'employed_from' => 'required|date',
        'employed_to' => 'required|date|after_or_equal:employed_from',
        'positions_held' => 'required|string|max:255',
        'reason_for_leaving' => 'required|string|max:255'
    ];

    protected $messages = [
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

    public function mount()
    {
        // Inicializar fechas con formatos por defecto
        $this->employed_from = now()->subYears(2)->format('Y-m-d');
        $this->employed_to = now()->format('Y-m-d');
        
        // Cargar historial de verificaciones
        $this->cargarVerificaciones();
    }
    
    public function cargarVerificaciones()
    {
        // Obtener las verificaciones de empleo más recientes
        $this->verificaciones = DriverEmploymentCompany::with(['userDriverDetail.user', 'userDriverDetail.carrier'])
            ->where('email_sent', true)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
    }

    public function updated($propertyName)
    {
        // Si cambia el carrier, reset el driver seleccionado
        if ($propertyName === 'selectedCarrierId') {
            $this->selectedDriverId = null;
            $this->loadDrivers();
        }
        
        // Si cambia el término de búsqueda, recargamos los drivers
        if ($propertyName === 'searchTerm' && !empty($this->selectedCarrierId)) {
            $this->loadDrivers();
        }
    }
    
    public function resetForm()
    {
        $this->reset([
            'currentStep',
            'selectedDriverId',
            'useExistingCompany',
            'selectedCompanyId',
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
            'additional_notes'
        ]);
        $this->resetValidation();
        $this->currentStep = 1;
        $this->employed_from = now()->subYears(2)->format('Y-m-d');
        $this->employed_to = now()->format('Y-m-d');
    }
    
    public function nextStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'selectedCarrierId' => 'required',
                'selectedDriverId' => 'required',
            ]);
            
            // Cargar compañías existentes para este conductor
            $this->loadCompanies();
        }
        
        if ($this->currentStep === 2) {
            if ($this->useExistingCompany) {
                $this->validate([
                    'selectedCompanyId' => 'required',
                ]);
                
                // Cargar datos de la compañía seleccionada
                $this->loadSelectedCompany();
            } else {
                $this->validate([
                    'company_name' => 'required',
                    'company_email' => 'required|email',
                ]);
            }
        }
        
        $this->currentStep++;
    }
    
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    // Cargar compañías existentes para el conductor seleccionado
    public function loadCompanies()
    {
        if ($this->selectedDriverId) {
            // Recuperar todas las compañías registradas para el conductor
            $this->companies = DriverEmploymentCompany::where('user_driver_detail_id', $this->selectedDriverId)
                ->orderBy('company_name')
                ->get();
        }
    }
    
    // Cargar datos de la compañía seleccionada
    public function loadSelectedCompany()
    {
        if ($this->selectedCompanyId) {
            $company = DriverEmploymentCompany::find($this->selectedCompanyId);
            if ($company) {
                $this->company_name = $company->company_name;
                $this->company_email = $company->email;
                // Cargar otros campos según corresponda...
            }
        }
    }
    
    // Cambiar modo de compañía (existente o nueva)
    public function toggleCompanyMode()
    {
        $this->useExistingCompany = !$this->useExistingCompany;
    }
    
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    

    
    public function loadDrivers()
{
    if (empty($this->selectedCarrierId)) {
        $this->drivers = [];
        return;
    }

    // Cargar conductores que tienen información de empleo registrada
    $query = UserDriverDetail::where('carrier_id', $this->selectedCarrierId)
        ->whereHas('driverEmploymentCompanies', function($q) {
            // Asegurar que tengan al menos un registro de empleo
            $q->whereNotNull('id');
        })
        ->with(['user', 'driverEmploymentCompanies']);
    
    // Aplicar filtro de búsqueda si existe
    if (!empty($this->searchTerm)) {
        $searchTerm = '%' . $this->searchTerm . '%';
        $query->whereHas('user', function ($q) use ($searchTerm) {
            $q->where('name', 'like', $searchTerm)
              ->orWhere('email', 'like', $searchTerm);
        });
    }
    
    $this->drivers = $query->get();
}
    
    public function selectDriver($driverId)
    {
        $this->selectedDriverId = $driverId;
    }
    
    // Método para enviar la verificación de empleo
    public function sendVerificationRequest()
    {
        // Validación principal
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Obtener el driver seleccionado
            $driver = UserDriverDetail::with('user')->findOrFail($this->selectedDriverId);
            
            // Crear registro de la compañía de empleo
            $employmentCompany = new DriverEmploymentCompany([
                'user_driver_detail_id' => $driver->id,
                // Nombre de la compañía - asegurarse de que este campo exista
                'company_name' => $this->company_name,
                // Email de la empresa
                'email' => $this->company_email,
                // Fechas de empleo
                'employed_from' => $this->employed_from,
                'employed_to' => $this->employed_to,
                // Detalles del puesto
                'positions_held' => $this->positions_held,
                'reason_for_leaving' => $this->reason_for_leaving,
                // Usar explanation para las notas adicionales
                'explanation' => $this->additional_notes,
                // Estado del email
                'email_sent' => true,
                'created_by' => Auth::id()
            ]);
            
            $employmentCompany->save();

            $token = Str::random(64);
            
            // Crear token de verificación
            $verificationToken = new EmploymentVerificationToken([
                'token' => $token,
                'driver_id' => $driver->id,
                'employment_company_id' => $employmentCompany->id,
                'email' => $this->company_email,
                'expires_at' => now()->addDays(7),
            ]);
            
            $verificationToken->save();
            
            // Obtener el nombre completo del conductor
            $driverName = $driver->user->name . ' ' . $driver->last_name;
            
            // Preparar datos para el correo
            $employmentData = [
                'positions_held' => $this->positions_held,
                'start_date' => $this->employed_from,
                'end_date' => $this->employed_to,
                'reason_for_leaving' => $this->reason_for_leaving,
                'explanation' => $this->additional_notes, // Usar explanation en lugar de additional_notes
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
            
            session()->flash('success', 'Solicitud de verificación enviada correctamente a ' . $this->company_name);
            
            // Recargar el historial de verificaciones - usar el método correcto
            $this->cargarVerificaciones();
            
            // Cerrar el modal y resetear el formulario
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al enviar solicitud de verificación de empleo', [
                'driver_id' => $this->selectedDriverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Error al enviar la solicitud: ' . $e->getMessage());
        }    
    }

    public function getCarriersProperty()
    {
        return Carrier::orderBy('name')->get();
    }

    public function getCompaniesProperty()
    {
        return MasterCompany::orderBy('company_name')->get();
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
        return view('livewire.admin.driver.bulk-employment-verification', [
            'carriers' => $this->getCarriersProperty(),
            'companies' => $this->getCompaniesProperty(),
            'selectedDriver' => $this->getSelectedDriverProperty(),
            'historial' => $this->verificaciones
        ]);
    }

}
