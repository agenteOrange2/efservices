<?php

namespace App\Livewire\Admin\Driver;

use App\Helpers\Constants;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\MasterCompany;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\DriverUnemploymentPeriod;
use App\Models\Admin\Driver\DriverRelatedEmployment;
use App\Models\Admin\Driver\EmploymentVerificationToken;
use App\Mail\EmploymentVerification;

class DriverEmploymentHistoryStep extends Component
{
    // Unemployment Periods
    public $has_unemployment_periods = false;
    public $unemployment_periods = [];
    public $combinedEmploymentHistory = [];

    // Unemployment Form Modal
    public $showUnemploymentForm = false;
    public $editing_unemployment_index = null;
    public $unemployment_form = [
        'id' => null,
        'start_date' => '',
        'end_date' => '',
        'comments' => '',
    ];

    // Employment Companies
    public $employment_companies = [];
    public $has_completed_employment_history = false;
    public $years_of_history = 0;

    // Related Employment (driving-related jobs like taxi driver, forklift operator, etc.)
    public $related_employments = [];
    public $showRelatedEmploymentForm = false;
    public $editing_related_employment_index = null;
    public $related_employment_form = [
        'id' => null,
        'start_date' => '',
        'end_date' => '',
        'position' => '',
        'comments' => '',
    ];

    // Company Form
    public $showCompanyForm = false;
    public $editing_company_index = null;
    public $company_form = [
        'id' => null,
        'master_company_id' => null,
        'company_name' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'contact' => '',
        'phone' => '',
        'email' => '',
        'fax' => '',
        'employed_from' => '',
        'employed_to' => '',
        'positions_held' => '',
        'subject_to_fmcsr' => false,
        'safety_sensitive_function' => false,
        'reason_for_leaving' => '',
        'other_reason_description' => '',
        'explanation' => '',
        'is_from_master' => false,
        'email_sent' => false
    ];

    // Search Company Modal
    public $showSearchCompanyModal = false;
    public $companySearchTerm = '';
    public $searchResults = [];


    // Propiedades para la confirmación de eliminación
    public $showDeleteConfirmationModal = false;
    public $deleteType = null; // 'employment' o 'unemployment'
    public $deleteIndex = null;

    // References
    public $driverId;

    // Correos de verificación pendientes
    protected $pendingVerificationEmails = [];

    // Validation rules
    protected function rules()
    {
        return [
            'has_unemployment_periods' => 'sometimes|boolean',
            'has_completed_employment_history' => 'accepted',
            'unemployment_form.start_date' => 'required|date',
            'unemployment_form.end_date' => 'required|date|after_or_equal:unemployment_form.start_date',
            'unemployment_form.comments' => 'nullable|string',
            'company_form.company_name' => 'required|string|max:255',
            'company_form.email' => 'nullable|email|max:255',
            'company_form.employed_from' => 'required|date',
            'company_form.employed_to' => 'required|date|after_or_equal:company_form.employed_from',
            'company_form.positions_held' => 'required|string|max:255',
            'company_form.reason_for_leaving' => 'required|string|max:255',
            'company_form.other_reason_description' =>
            'required_if:company_form.reason_for_leaving,other|max:255',
            'related_employment_form.start_date' => 'required|date',
            'related_employment_form.end_date' => 'required|date|after_or_equal:related_employment_form.start_date',
            'related_employment_form.position' => 'required|string|max:255',
            'related_employment_form.comments' => 'nullable|string',
        ];
    }

    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'has_unemployment_periods' => 'sometimes|boolean',
        ];
    }

    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;

        if ($this->driverId) {
            $this->loadExistingData();
        }

        // Calculate years of history
        $this->calculateYearsOfHistory();
    }

    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        // Default values
        $this->has_unemployment_periods = false;
        $this->has_completed_employment_history = false;

        // Check if has unemployment periods from application details
        if ($userDriverDetail->application && $userDriverDetail->application->details) {
            $this->has_unemployment_periods = (bool)($userDriverDetail->application->details->has_unemployment_periods ?? false);
        }
        
        $this->has_completed_employment_history = (bool)($userDriverDetail->has_completed_employment_history ?? false);

        // Load unemployment periods
        $this->unemployment_periods = [];
        $unemploymentPeriods = DriverUnemploymentPeriod::where('user_driver_detail_id', $this->driverId)->get();

        foreach ($unemploymentPeriods as $period) {
            $this->unemployment_periods[] = [
                'id' => $period->id,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'comments' => $period->comments,
            ];
        }

        // Si hay períodos de desempleo registrados, asegurarse de que has_unemployment_periods sea true
        if (count($this->unemployment_periods) > 0) {
            $this->has_unemployment_periods = true;
        }

        // Load employment companies
        $this->employment_companies = [];
        $employmentCompanies = DriverEmploymentCompany::with('masterCompany')
            ->where('user_driver_detail_id', $this->driverId)
            ->get();

        foreach ($employmentCompanies as $company) {
            $masterCompany = $company->masterCompany;

            $this->employment_companies[] = [
                'id' => $company->id,
                'master_company_id' => $company->master_company_id,
                'company_name' => $masterCompany ? $masterCompany->company_name : '',
                'address' => $masterCompany ? $masterCompany->address : '',
                'city' => $masterCompany ? $masterCompany->city : '',
                'state' => $masterCompany ? $masterCompany->state : '',
                'zip' => $masterCompany ? $masterCompany->zip : '',
                'contact' => $masterCompany ? $masterCompany->contact : '',
                'phone' => $masterCompany ? $masterCompany->phone : '',
                'email' => $masterCompany ? $masterCompany->email : ($company->email ?? ''),
                'fax' => $masterCompany ? $masterCompany->fax : '',
                'employed_from' => $company->employed_from->format('Y-m-d'),
                'employed_to' => $company->employed_to->format('Y-m-d'),
                'positions_held' => $company->positions_held,
                'subject_to_fmcsr' => $company->subject_to_fmcsr,
                'safety_sensitive_function' => $company->safety_sensitive_function,
                'reason_for_leaving' => $company->reason_for_leaving,
                'other_reason_description' => $company->other_reason_description,
                'explanation' => $company->explanation,
                'is_from_master' => true,
            ];
        }

        // Load related employments
        $this->related_employments = [];
        $relatedEmployments = DriverRelatedEmployment::where('user_driver_detail_id', $this->driverId)->get();

        foreach ($relatedEmployments as $employment) {
            $this->related_employments[] = [
                'id' => $employment->id,
                'start_date' => $employment->start_date->format('Y-m-d'),
                'end_date' => $employment->end_date->format('Y-m-d'),
                'position' => $employment->position,
                'comments' => $employment->comments,
            ];
        }

        // Calculate years of history
        $this->calculateYearsOfHistory();
    }

    // Calculate years of employment history
    public function calculateYearsOfHistory()
    {
        $totalYears = 0;
        $combinedHistory = [];

        // Process employment periods
        foreach ($this->employment_companies as $index => $company) {
            if (!empty($company['employed_from']) && !empty($company['employed_to'])) {
                $from = Carbon::parse($company['employed_from']);
                $to = Carbon::parse($company['employed_to']);
                $years = $from->diffInDays($to) / 365.25;
                $totalYears += $years;
                $combinedHistory[] = [
                    'type' => 'employed',
                    'status' => 'EMPLOYED',
                    'note' => $company['company_name'],
                    'from_date' => $company['employed_from'],
                    'to_date' => $company['employed_to'],
                    'index' => $index,
                    'original_index' => $index,
                    'years' => $years
                ];
            }
        }

        // Process unemployment periods - siempre incluir si existen, independientemente del checkbox
        if (count($this->unemployment_periods) > 0) {
            foreach ($this->unemployment_periods as $index => $period) {
                if (!empty($period['start_date']) && !empty($period['end_date'])) {
                    $from = Carbon::parse($period['start_date']);
                    $to = Carbon::parse($period['end_date']);
                    $years = $from->diffInDays($to) / 365.25;
                    $totalYears += $years;
                    $combinedHistory[] = [
                        'type' => 'unemployed',
                        'status' => 'UNEMPLOYED',
                        'note' => $period['comments'] ?? 'Unemployment Period',
                        'from_date' => $period['start_date'],
                        'to_date' => $period['end_date'],
                        'index' => $index,
                        'original_index' => $index,
                        'years' => $years
                    ];
                }
            }

            // Si hay períodos de desempleo, asegurar que el checkbox esté marcado
            if (!$this->has_unemployment_periods) {
                $this->has_unemployment_periods = true;
            }
        }

        // Process related employment periods (taxi driver, forklift operator, etc.)
        foreach ($this->related_employments as $index => $employment) {
            if (!empty($employment['start_date']) && !empty($employment['end_date'])) {
                $from = Carbon::parse($employment['start_date']);
                $to = Carbon::parse($employment['end_date']);
                $years = $from->diffInDays($to) / 365.25;
                $totalYears += $years;
                $combinedHistory[] = [
                    'type' => 'related',
                    'status' => 'RELATED EMPLOYMENT',
                    'note' => $employment['position'] . (empty($employment['comments']) ? '' : ' - ' . $employment['comments']),
                    'from_date' => $employment['start_date'],
                    'to_date' => $employment['end_date'],
                    'index' => $index,
                    'original_index' => $index,
                    'years' => $years
                ];
            }
        }

        // Sort by date, most recent first
        usort($combinedHistory, function ($a, $b) {
            return strtotime($b['to_date']) - strtotime($a['to_date']);
        });

        // Save combined history for view
        $this->combinedEmploymentHistory = $combinedHistory;

        // Update total years
        $this->years_of_history = round($totalYears, 1);
        return $this->years_of_history;
    }

    // Save employment history data to database
    // Save employment history data to database
    protected function saveEmploymentHistoryData($updateStep = true)
    {
        DB::beginTransaction();
        try {
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Update application details
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_unemployment_periods' => $this->has_unemployment_periods,
                ]);
            }
            
            // Update user driver details
            $userDriverDetail->update([
                'has_completed_employment_history' => $this->has_completed_employment_history,
            ]);

            // Save unemployment periods
            $existingPeriodIds = $userDriverDetail->unemploymentPeriods()->pluck('id')->toArray();
            $updatedPeriodIds = [];

            foreach ($this->unemployment_periods as $period) {
                if (!empty($period['start_date']) && !empty($period['end_date'])) {
                    if (!empty($period['id'])) {
                        // Update existing period
                        $unemploymentPeriod = DriverUnemploymentPeriod::find($period['id']);
                        if ($unemploymentPeriod) {
                            $unemploymentPeriod->update([
                                'start_date' => $period['start_date'],
                                'end_date' => $period['end_date'],
                                'comments' => $period['comments'] ?? null
                            ]);
                            $updatedPeriodIds[] = $unemploymentPeriod->id;
                        }
                    } else {
                        // Create new period
                        $unemploymentPeriod = $userDriverDetail->unemploymentPeriods()->create([
                            'start_date' => $period['start_date'],
                            'end_date' => $period['end_date'],
                            'comments' => $period['comments'] ?? null
                        ]);
                        $updatedPeriodIds[] = $unemploymentPeriod->id;
                    }
                }
            }

            // Delete periods that are no longer needed
            $periodsToDelete = array_diff($existingPeriodIds, $updatedPeriodIds);
            if (!empty($periodsToDelete)) {
                $userDriverDetail->unemploymentPeriods()->whereIn('id', $periodsToDelete)->delete();
            }

            // Save employment companies
            $existingCompanyIds = $userDriverDetail->employmentCompanies()->pluck('id')->toArray();
            $updatedCompanyIds = [];

            foreach ($this->employment_companies as $company) {
                if (!empty($company['employed_from']) && !empty($company['employed_to'])) {
                    // Determine if we need to create or update a master company
                    $masterCompanyId = null;

                    if (!empty($company['master_company_id'])) {
                        // Use existing master company
                        $masterCompanyId = $company['master_company_id'];
                    } else {
                        // Verificar si ya existe una empresa con el mismo nombre
                        $existingCompany = MasterCompany::where('company_name', $company['company_name'])->first();

                        if ($existingCompany) {
                            // Usar la empresa existente en lugar de crear una nueva
                            $masterCompanyId = $existingCompany->id;

                            // Registrar que estamos usando una empresa existente
                            Log::info('Usando master_company existente en lugar de crear duplicada', [
                                'company_name' => $company['company_name'],
                                'master_company_id' => $masterCompanyId
                            ]);
                        } else {
                            // Create new master company
                            $masterCompany = MasterCompany::create([
                                'company_name' => $company['company_name'],
                                'address' => $company['address'] ?? null,
                                'city' => $company['city'] ?? null,
                                'state' => $company['state'] ?? null,
                                'zip' => $company['zip'] ?? null,
                                'contact' => $company['contact'] ?? null,
                                'phone' => $company['phone'] ?? null,
                                'email' => $company['email'] ?? null,
                                'fax' => $company['fax'] ?? null,
                            ]);
                            $masterCompanyId = $masterCompany->id;
                        }
                    } // <- LLAVE FALTANTE AGREGADA AQUÍ

                    // Create or update employment company
                    if (!empty($company['id'])) {
                        // Update existing employment company
                        $employmentCompany = DriverEmploymentCompany::find($company['id']);
                        if ($employmentCompany) {
                            $employmentCompany->update([
                                'master_company_id' => $masterCompanyId,
                                'employed_from' => $company['employed_from'],
                                'employed_to' => $company['employed_to'],
                                'positions_held' => $company['positions_held'],
                                'subject_to_fmcsr' => $company['subject_to_fmcsr'] ?? false,
                                'safety_sensitive_function' => $company['safety_sensitive_function'] ?? false,
                                'reason_for_leaving' => $company['reason_for_leaving'] ?? null,
                                'other_reason_description' => $company['reason_for_leaving'] === 'other' ?
                                    $company['other_reason_description'] : null,
                                'email' => $company['email'] ?? null,
                                'explanation' => $company['explanation'] ?? null
                            ]);
                            $updatedCompanyIds[] = $employmentCompany->id;
                        }
                    } else {
                        // Create new employment company
                        $employmentCompany = $userDriverDetail->employmentCompanies()->create([
                            'master_company_id' => $masterCompanyId,
                            'employed_from' => $company['employed_from'],
                            'employed_to' => $company['employed_to'],
                            'positions_held' => $company['positions_held'],
                            'subject_to_fmcsr' => $company['subject_to_fmcsr'] ?? false,
                            'safety_sensitive_function' => $company['safety_sensitive_function'] ?? false,
                            'reason_for_leaving' => $company['reason_for_leaving'] ?? null,
                            'other_reason_description' => $company['reason_for_leaving'] === 'other' ?
                                $company['other_reason_description'] : null,
                            'email' => $company['email'] ?? null,
                            'explanation' => $company['explanation'] ?? null
                        ]);
                        $updatedCompanyIds[] = $employmentCompany->id;
                    }
                }
            }

            // Delete companies that are no longer needed
            $companiesToDelete = array_diff($existingCompanyIds, $updatedCompanyIds);
            if (!empty($companiesToDelete)) {
                $userDriverDetail->employmentCompanies()->whereIn('id', $companiesToDelete)->delete();
            }

            // Save related employments
            $existingRelatedEmploymentIds = DriverRelatedEmployment::where('user_driver_detail_id', $this->driverId)
                ->pluck('id')
                ->toArray();
            $updatedRelatedEmploymentIds = [];

            foreach ($this->related_employments as $employment) {
                if (!empty($employment['start_date']) && !empty($employment['end_date']) && !empty($employment['position'])) {
                    if (!empty($employment['id'])) {
                        // Update existing related employment
                        $relatedEmployment = DriverRelatedEmployment::find($employment['id']);
                        if ($relatedEmployment) {
                            $relatedEmployment->update([
                                'start_date' => $employment['start_date'],
                                'end_date' => $employment['end_date'],
                                'position' => $employment['position'],
                                'comments' => $employment['comments'] ?? null
                            ]);
                            $updatedRelatedEmploymentIds[] = $relatedEmployment->id;
                        }
                    } else {
                        // Create new related employment
                        $relatedEmployment = DriverRelatedEmployment::create([
                            'user_driver_detail_id' => $this->driverId,
                            'start_date' => $employment['start_date'],
                            'end_date' => $employment['end_date'],
                            'position' => $employment['position'],
                            'comments' => $employment['comments'] ?? null
                        ]);
                        $updatedRelatedEmploymentIds[] = $relatedEmployment->id;
                    }
                }
            }

            // Delete related employments that are no longer needed
            $relatedEmploymentsToDelete = array_diff($existingRelatedEmploymentIds, $updatedRelatedEmploymentIds);
            if (!empty($relatedEmploymentsToDelete)) {
                DriverRelatedEmployment::whereIn('id', $relatedEmploymentsToDelete)->delete();
            }

            // Update current step solo si se solicita
            if ($updateStep) {
                $userDriverDetail->update(['current_step' => 10]);
                Log::info('Current step updated by manager', [
                    'driver_id' => $this->driverId,
                    'step' => 10
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving employment history: ' . $e->getMessage());
            return false;
        }
    }

    // Add unemployment period (abre el modal)
    public function addUnemploymentPeriod()
    {
        $this->resetUnemploymentForm();
        $this->showUnemploymentForm = true;
        $this->editing_unemployment_index = null;
    }

    // Edit unemployment period (abre el modal)
    public function editUnemploymentPeriod($index)
    {
        if (isset($this->unemployment_periods[$index])) {
            $this->editing_unemployment_index = $index;
            $this->unemployment_form = $this->unemployment_periods[$index];
            $this->showUnemploymentForm = true;
        }
    }

    // Close unemployment form
    public function closeUnemploymentForm()
    {
        $this->showUnemploymentForm = false;
        $this->resetUnemploymentForm();
    }

    // Reset unemployment form
    public function resetUnemploymentForm()
    {
        $this->unemployment_form = [
            'id' => null,
            'start_date' => '',
            'end_date' => '',
            'comments' => '',
        ];
        $this->editing_unemployment_index = null;
    }

    // Save unemployment period
    public function saveUnemploymentPeriod()
    {
        // Validate the unemployment form
        $this->validate([
            'unemployment_form.start_date' => 'required|date',
            'unemployment_form.end_date' => 'required|date|after_or_equal:unemployment_form.start_date',
        ]);

        // Update or add unemployment period to list
        if ($this->editing_unemployment_index !== null) {
            $this->unemployment_periods[$this->editing_unemployment_index] = $this->unemployment_form;
        } else {
            $this->unemployment_periods[] = $this->unemployment_form;
        }

        // Close the form and recalculate history
        $this->showUnemploymentForm = false;
        $this->resetUnemploymentForm();
        $this->calculateYearsOfHistory();
    }

    // Remove unemployment period
    public function removeUnemploymentPeriod($index)
    {
        if (count($this->unemployment_periods) > 1) {
            unset($this->unemployment_periods[$index]);
            $this->unemployment_periods = array_values($this->unemployment_periods);
            $this->calculateYearsOfHistory();
        }
    }

    // Add employment company
    public function addEmploymentCompany()
    {
        $this->resetCompanyForm();
        $this->showCompanyForm = true;
        $this->editing_company_index = null;
    }

    // Edit employment company
    public function editEmploymentCompany($index)
    {
        $this->editing_company_index = $index;
        $this->company_form = $this->employment_companies[$index];
        $this->showCompanyForm = true;
    }

    // Close company form
    public function closeCompanyForm()
    {
        $this->showCompanyForm = false;
        $this->resetCompanyForm();
    }

    // Reset company form
    public function resetCompanyForm()
    {
        $this->company_form = [
            'id' => null,
            'master_company_id' => null,
            'company_name' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'contact' => '',
            'phone' => '',
            'email' => '', // Añadimos el campo email
            'fax' => '',
            'employed_from' => '',
            'employed_to' => '',
            'positions_held' => '',
            'subject_to_fmcsr' => false,
            'safety_sensitive_function' => false,
            'reason_for_leaving' => '',
            'other_reason_description' => '',
            'explanation' => '',
            'is_from_master' => false,
            'email_sent' => false // Inicializamos email_sent
        ];
        $this->editing_company_index = null;
    }

    // Save company form
    public function saveCompany()
    {
        // Validar el formulario
        $this->validate([
            'company_form.company_name' => 'required|string|max:255',
            'company_form.employed_from' => 'required|date',
            'company_form.employed_to' => 'required|date|after_or_equal:company_form.employed_from',
            'company_form.positions_held' => 'required|string|max:255',
            'company_form.reason_for_leaving' => 'required|string|max:255',
            'company_form.other_reason_description' => 'required_if:company_form.reason_for_leaving,other|max:255',
            'company_form.email' => 'nullable|email|max:255',
        ]);

        // Si estamos editando, actualizamos el registro existente
        if ($this->editing_company_index !== null) {
            $this->employment_companies[$this->editing_company_index] = $this->company_form;
        } else {
            // Si es nuevo, lo agregamos al array
            $this->employment_companies[] = $this->company_form;
        }

        // Recalcular años de historial
        $this->calculateYearsOfHistory();

        // Guardar siempre primero, independientemente de si hay correo o no
        $this->saveEmploymentHistoryData(false);

        // Cerrar formulario y limpiar
        $this->closeCompanyForm();
        $this->resetCompanyForm();

        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Company saved successfully!'
        ]);
    }

    // Open search company modal
    public function openSearchCompanyModal()
    {
        $this->showSearchCompanyModal = true;
        $this->searchCompanies();
    }

    // Close search company modal
    public function closeSearchCompanyModal()
    {
        $this->showSearchCompanyModal = false;
        $this->companySearchTerm = '';
        $this->searchResults = [];
    }

    // Search companies
    public function searchCompanies()
    {
        // If search term is empty, show recent companies
        if (empty($this->companySearchTerm)) {
            $this->searchResults = MasterCompany::orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->toArray();
            return;
        }

        // Search companies by term
        $this->searchResults = MasterCompany::where('company_name', 'like', '%' . $this->companySearchTerm . '%')
            ->orWhere('city', 'like', '%' . $this->companySearchTerm . '%')
            ->orWhere('state', 'like', '%' . $this->companySearchTerm . '%')
            ->take(20)
            ->get()
            ->toArray();
    }

    // Handle company search term update
    public function updatedCompanySearchTerm()
    {
        $this->searchCompanies();
    }

    // Select company from search
    public function selectCompany($companyId)
    {
        $masterCompany = MasterCompany::find($companyId);
        if ($masterCompany) {
            $this->company_form = [
                'master_company_id' => $masterCompany->id,
                'company_name' => $masterCompany->company_name,
                'address' => $masterCompany->address,
                'city' => $masterCompany->city,
                'state' => $masterCompany->state,
                'zip' => $masterCompany->zip,
                'contact' => $masterCompany->contact,
                'phone' => $masterCompany->phone,
                'email' => $masterCompany->email ?? '', // Añadimos el campo email
                'fax' => $masterCompany->fax,
                // Campos editables para el periodo de empleo
                'employed_from' => '',
                'employed_to' => '',
                'positions_held' => '',
                'subject_to_fmcsr' => false,
                'safety_sensitive_function' => false,
                'reason_for_leaving' => '',
                'other_reason_description' => '',
                'explanation' => '',
                'is_from_master' => true, // Indicar que viene de MasterCompany
                'email_sent' => false, // Inicializamos email_sent
                'id' => null // Inicializamos id
            ];
            $this->closeSearchCompanyModal();
            $this->showCompanyForm = true;
        }
    }

    // Get empty unemployment period structure
    protected function getEmptyUnemploymentPeriod()
    {
        return [
            'id' => null,
            'start_date' => '',
            'end_date' => '',
            'comments' => '',
        ];
    }

    // Get empty related employment structure
    protected function getEmptyRelatedEmployment()
    {
        return [
            'id' => null,
            'start_date' => '',
            'end_date' => '',
            'position' => '',
            'comments' => '',
        ];
    }

    // Add related employment
    public function addRelatedEmployment()
    {
        $this->resetRelatedEmploymentForm();
        $this->showRelatedEmploymentForm = true;
        $this->editing_related_employment_index = null;
    }

    // Edit related employment
    public function editRelatedEmployment($index)
    {
        if (isset($this->related_employments[$index])) {
            $this->related_employment_form = $this->related_employments[$index];
            $this->showRelatedEmploymentForm = true;
            $this->editing_related_employment_index = $index;
        }
    }

    // Close related employment form
    public function closeRelatedEmploymentForm()
    {
        $this->showRelatedEmploymentForm = false;
        $this->resetRelatedEmploymentForm();
    }

    // Reset related employment form
    public function resetRelatedEmploymentForm()
    {
        $this->related_employment_form = $this->getEmptyRelatedEmployment();
        $this->editing_related_employment_index = null;
    }

    // Save related employment
    public function saveRelatedEmployment()
    {
        $this->validate([
            'related_employment_form.start_date' => 'required|date',
            'related_employment_form.end_date' => 'required|date|after_or_equal:related_employment_form.start_date',
            'related_employment_form.position' => 'required|string|max:255',
        ]);

        if ($this->editing_related_employment_index !== null) {
            // Update existing
            $this->related_employments[$this->editing_related_employment_index] = $this->related_employment_form;
        } else {
            // Add new
            $this->related_employments[] = $this->related_employment_form;
        }

        $this->showRelatedEmploymentForm = false;
        $this->resetRelatedEmploymentForm();
        $this->calculateYearsOfHistory();
    }

    // Eliminar empleo relacionado (sin confirmación)
    public function removeRelatedEmployment($index)
    {
        try {
            // Registrar el inicio de la operación
            Log::info('Iniciando eliminación de empleo relacionado', [
                'index' => $index,
                'related_employments_count' => count($this->related_employments),
                'related_employment' => isset($this->related_employments[$index]) ? $this->related_employments[$index] : 'No existe'
            ]);

            // Verificar si el índice existe
            if (!isset($this->related_employments[$index])) {
                Log::error('El índice no existe en el array de empleos relacionados', ['index' => $index]);
                session()->flash('error', 'No se encontró el empleo relacionado para eliminar.');
                return;
            }

            // Si el empleo relacionado tiene ID, eliminarlo de la base de datos
            if (!empty($this->related_employments[$index]['id'])) {
                $id = $this->related_employments[$index]['id'];

                // Verificar si el registro existe en la base de datos
                $exists = DB::table('driver_related_employments')->where('id', $id)->exists();
                Log::info('Verificando existencia del registro en la base de datos', [
                    'id' => $id,
                    'exists' => $exists
                ]);

                if ($exists) {
                    // Intentar eliminar usando consulta directa
                    $deleted = DB::table('driver_related_employments')->where('id', $id)->delete();
                    Log::info('Resultado de la eliminación en la base de datos', [
                        'id' => $id,
                        'deleted' => $deleted
                    ]);

                    // Verificar si se eliminó correctamente
                    $stillExists = DB::table('driver_related_employments')->where('id', $id)->exists();
                    Log::info('Verificando si el registro sigue existiendo después de eliminarlo', [
                        'id' => $id,
                        'still_exists' => $stillExists
                    ]);

                    // Si sigue existiendo, intentar con otro método
                    if ($stillExists) {
                        Log::warning('El registro sigue existiendo, intentando con otro método', ['id' => $id]);
                        DB::statement('DELETE FROM driver_related_employments WHERE id = ?', [$id]);

                        // Verificar nuevamente
                        $finalCheck = DB::table('driver_related_employments')->where('id', $id)->exists();
                        Log::info('Verificación final después del segundo intento', [
                            'id' => $id,
                            'still_exists' => $finalCheck
                        ]);
                    }
                } else {
                    Log::warning('El registro no existe en la base de datos', ['id' => $id]);
                }
            } else {
                Log::info('El registro no tiene ID, solo se eliminará del array en memoria');
            }

            // Eliminar del array
            unset($this->related_employments[$index]);
            $this->related_employments = array_values($this->related_employments);
            Log::info('Registro eliminado del array en memoria', [
                'new_count' => count($this->related_employments)
            ]);

            // Recalcular años de historial
            $this->calculateYearsOfHistory();

            // Mostrar mensaje de éxito
            session()->flash('success', 'Empleo relacionado eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar empleo relacionado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al eliminar el empleo relacionado: ' . $e->getMessage());
        }
    }

    // Confirmar eliminación de empleo relacionado
    public function confirmDeleteRelatedEmployment($index)
    {
        $this->deleteType = 'related_employment';
        $this->deleteIndex = $index;
        $this->showDeleteConfirmationModal = true;
    }

    // Método para eliminar directamente desde la tabla
    public function forceDeleteRelatedEmployment($id)
    {
        try {
            if (empty($id) || $id == 0) {
                session()->flash('error', 'ID de empleo relacionado inválido');
                return;
            }

            // Registrar información para depuración
            Log::info('Intentando eliminar empleo relacionado', ['id' => $id]);

            // Verificar si el registro existe
            $employment = DriverRelatedEmployment::find($id);

            if (!$employment) {
                Log::warning('Empleo relacionado no encontrado', ['id' => $id]);
                session()->flash('error', 'Empleo relacionado no encontrado');
                return;
            }

            // Intentar eliminar usando el modelo directamente
            $deleted = $employment->delete();

            Log::info('Resultado de eliminación', [
                'id' => $id,
                'deleted' => $deleted
            ]);

            // Si no se pudo eliminar con el modelo, intentar con consulta directa
            if (!$deleted) {
                Log::warning('Fallida eliminación con modelo, intentando con consulta directa', ['id' => $id]);
                $deleted = DB::table('driver_related_employments')->where('id', $id)->delete();

                Log::info('Resultado de eliminación con consulta directa', [
                    'id' => $id,
                    'deleted' => $deleted
                ]);
            }

            // Actualizar el array en memoria
            foreach ($this->related_employments as $index => $employment) {
                if (!empty($employment['id']) && $employment['id'] == $id) {
                    unset($this->related_employments[$index]);
                    $this->related_employments = array_values($this->related_employments);
                    break;
                }
            }

            // Recalcular años de historial
            $this->calculateYearsOfHistory();

            // Mostrar mensaje de éxito
            session()->flash('success', 'Empleo relacionado eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar empleo relacionado', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    // Confirmar eliminación de empleo
    public function confirmDeleteEmploymentCompany($index)
    {
        $this->deleteType = 'employment';
        $this->deleteIndex = $index;
        $this->showDeleteConfirmationModal = true;
    }

    // Confirmar eliminación de desempleo
    public function confirmDeleteUnemploymentPeriod($index)
    {
        $this->deleteType = 'unemployment';
        $this->deleteIndex = $index;
        $this->showDeleteConfirmationModal = true;
    }

    // Cancelar eliminación
    public function cancelDelete()
    {
        $this->showDeleteConfirmationModal = false;
        $this->deleteType = null;
        $this->deleteIndex = null;
    }

    // Confirmar y ejecutar eliminación
    public function confirmDelete()
    {
        DB::beginTransaction();
        try {
            if ($this->deleteType === 'employment') {
                // Si tiene ID, eliminar de la base de datos
                if (!empty($this->employment_companies[$this->deleteIndex]['id'])) {
                    DriverEmploymentCompany::where('id', $this->employment_companies[$this->deleteIndex]['id'])->delete();
                }
                // Eliminamos el registro de empleo del array
                unset($this->employment_companies[$this->deleteIndex]);
                $this->employment_companies = array_values($this->employment_companies);
            } elseif ($this->deleteType === 'unemployment') {
                // Si tiene ID, eliminar de la base de datos
                if (!empty($this->unemployment_periods[$this->deleteIndex]['id'])) {
                    DriverUnemploymentPeriod::where('id', $this->unemployment_periods[$this->deleteIndex]['id'])->delete();
                }
                // Eliminamos el registro de desempleo del array
                unset($this->unemployment_periods[$this->deleteIndex]);
                $this->unemployment_periods = array_values($this->unemployment_periods);
            } elseif ($this->deleteType === 'related_employment') {
                // Si tiene ID, eliminar de la base de datos
                if (!empty($this->related_employments[$this->deleteIndex]['id'])) {
                    DriverRelatedEmployment::where('id', $this->related_employments[$this->deleteIndex]['id'])->delete();
                }
                // Eliminamos el registro de empleo relacionado del array
                unset($this->related_employments[$this->deleteIndex]);
                $this->related_employments = array_values($this->related_employments);
            }

            DB::commit();

            // Cerramos el modal y recalculamos el historial
            $this->showDeleteConfirmationModal = false;
            $this->deleteType = null;
            $this->deleteIndex = null;
            $this->calculateYearsOfHistory();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al eliminar el registro: ' . $e->getMessage());
        }
    }

    // Next step (or complete)
    public function next()
    {
        // Validar que se haya marcado el checkbox de confirmación
        $this->validate([
            'has_completed_employment_history' => 'accepted',
        ], [
            'has_completed_employment_history.accepted' => 'You must confirm that the employment history information is correct and complete.'
        ]);

        // Validar los años de historial
        if ($this->years_of_history < 10) {
            $this->addError(
                'employment_history',
                'You must have at least 10 years of employment history. Current total: ' .
                    $this->years_of_history . ' years.'
            );
            return;
        }

        // Guardar en la base de datos
        if ($this->driverId) {
            // Primero guardar todos los datos
            $this->saveEmploymentHistoryData();

            // Luego enviar los correos pendientes
            $this->sendPendingVerificationEmails();
        }

        // Avanzar al siguiente paso
        $this->dispatch('nextStep');
    }

    // Previous step
    public function previous()
    {
        // Basic save before going back
        if ($this->driverId) {
            $this->saveEmploymentHistoryData();
        }

        $this->dispatch('prevStep');
    }

    // Save and exit
    public function saveAndExit()
    {
        // Save to database
        if ($this->driverId) {
            $this->saveEmploymentHistoryData();
        }

        $this->dispatch('saveAndExit');
    }

    /**
     * Envía correos de verificación para todas las compañías que tienen correo y no se ha enviado aún
     * Versión simplificada para garantizar que los correos se envíen correctamente
     */
    protected function sendPendingVerificationEmails()
    {
        if (!$this->driverId) {
            return;
        }

        Log::info('Iniciando envío de correos de verificación pendientes', [
            'driver_id' => $this->driverId
        ]);

        // Guardar estado actual para restaurarlo después
        $currentForm = $this->company_form;
        $currentIndex = $this->editing_company_index;

        $successCount = 0;
        $failCount = 0;

        // Buscar directamente en la base de datos las compañías que necesitan correo
        $driverCompanies = DriverEmploymentCompany::where('user_driver_detail_id', $this->driverId)
            ->where(function ($query) {
                $query->where('email_sent', false)
                    ->orWhereNull('email_sent');
            })
            ->whereNotNull('email')
            ->get();

        Log::info('Compañías que necesitan correo', [
            'cantidad' => count($driverCompanies)
        ]);

        foreach ($driverCompanies as $dbCompany) {
            // Obtener la master company
            $masterCompany = MasterCompany::find($dbCompany->master_company_id);
            if (!$masterCompany) {
                Log::warning('No se encontró la master company', [
                    'master_company_id' => $dbCompany->master_company_id,
                    'company_id' => $dbCompany->id
                ]);
                continue;
            }

            // Preparar datos para el envío
            $this->company_form = [
                'id' => $dbCompany->id,
                'master_company_id' => $dbCompany->master_company_id,
                'company_name' => $masterCompany->company_name,
                'address' => $masterCompany->address,
                'city' => $masterCompany->city,
                'state' => $masterCompany->state,
                'zip' => $masterCompany->zip,
                'email' => $dbCompany->email,
                'employed_from' => $dbCompany->employed_from,
                'employed_to' => $dbCompany->employed_to,
                'positions_held' => $dbCompany->positions_held,
                'subject_to_fmcsr' => $dbCompany->subject_to_fmcsr,
                'safety_sensitive_function' => $dbCompany->safety_sensitive_function,
                'reason_for_leaving' => $dbCompany->reason_for_leaving,
            ];

            $this->editing_company_index = null; // No estamos editando en memoria

            try {
                // Intentar enviar el correo
                $this->sendEmploymentVerificationEmail();

                // Actualizar directamente en la base de datos
                $dbCompany->update([
                    'email_sent' => true
                ]);

                // Actualizar también en memoria si existe
                foreach ($this->employment_companies as $index => $company) {
                    if (!empty($company['id']) && $company['id'] == $dbCompany->id) {
                        $this->employment_companies[$index]['email_sent'] = true;
                        break;
                    }
                }

                $successCount++;

                Log::info('Correo de verificación enviado correctamente', [
                    'company_id' => $dbCompany->id,
                    'email' => $dbCompany->email
                ]);
            } catch (\Exception $e) {
                $failCount++;
                Log::error('Error al enviar correo de verificación', [
                    'error' => $e->getMessage(),
                    'company_email' => $dbCompany->email,
                    'company_id' => $dbCompany->id
                ]);
            }
        }

        // Restaurar el estado original
        $this->company_form = $currentForm;
        $this->editing_company_index = $currentIndex;

        // Notificar al usuario sobre el resultado
        if ($successCount > 0) {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "$successCount verification emails were sent successfully" . ($failCount > 0 ? " ($failCount failed)" : "")
            ]);
        } elseif ($failCount > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Failed to send $failCount verification emails. Check logs for details."
            ]);
        }
    }

    /**
     * Envía un correo electrónico a la empresa para verificación de empleo
     */
    public function sendEmploymentVerificationEmail()
    {
        Log::info('Iniciando envío de correo de verificación de empleo', [
            'company_email' => $this->company_form['email'],
            'driver_id' => $this->driverId
        ]);

        // Validar que tengamos un correo electrónico
        if (empty($this->company_form['email'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Email address is required to send verification email'
            ]);
            return;
        }

        // Validar que tengamos un ID de empresa (debe haberse guardado previamente)
        if (empty($this->company_form['id'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Company must be saved before sending verification email'
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            // Obtener el usuario y detalles del conductor
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Preparar los datos de empleo para el correo
            $employmentData = [
                'positions_held' => $this->company_form['positions_held'],
                'employed_from' => $this->company_form['employed_from'],
                'employed_to' => $this->company_form['employed_to'],
                'reason_for_leaving' => $this->company_form['reason_for_leaving'],
                'subject_to_fmcsr' => $this->company_form['subject_to_fmcsr'],
                'safety_sensitive_function' => $this->company_form['safety_sensitive_function'],
            ];

            // Generar token de verificación
            $token = \Illuminate\Support\Str::random(64);
            $expiresAt = now()->addDays(7);

            // Guardar el token de verificación
            $verification = EmploymentVerificationToken::create([
                'token' => $token,
                'driver_id' => $this->driverId,
                'employment_company_id' => $this->company_form['id'],
                'email' => $this->company_form['email'],
                'expires_at' => $expiresAt,
            ]);

            // Enviar correo electrónico
            Mail::to($this->company_form['email'])
                ->send(new EmploymentVerification(
                    $this->company_form['company_name'],
                    $userDriverDetail->user->name . ' ' . $userDriverDetail->last_name,
                    $employmentData,
                    $token,
                    $this->driverId,
                    $this->company_form['id']
                ));

            // Actualizar el estado de envío de correo en el formulario
            if ($this->editing_company_index !== null) {
                $this->employment_companies[$this->editing_company_index]['email_sent'] = true;
            } else {
                // Buscar la empresa recién agregada por su ID
                foreach ($this->employment_companies as $index => $company) {
                    if ($company['id'] == $this->company_form['id']) {
                        $this->employment_companies[$index]['email_sent'] = true;
                        break;
                    }
                }
            }

            // Actualizar el registro en la base de datos
            $employmentCompany = DriverEmploymentCompany::find($this->company_form['id']);
            if ($employmentCompany) {
                $employmentCompany->update([
                    'email_sent' => true
                ]);
            }

            DB::commit();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Employment verification email sent successfully to ' . $this->company_form['email']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al enviar correo de verificación de empleo', [
                'error' => $e->getMessage(),
                'email' => $this->company_form['email'],
                'driver_id' => $this->driverId,
                'company_id' => $this->company_form['id'],
                'trace' => $e->getTraceAsString()
            ]);

            // Mostrar mensaje de error más detallado para facilitar la depuración
            $errorMessage = 'Error sending email: ' . $e->getMessage();
            if (app()->environment('local', 'development', 'staging')) {
                $errorMessage .= ' (Check logs for more details)';
            }

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $errorMessage
            ]);
        }
    }

    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-employment-history-step', [
            'usStates' => Constants::usStates(),
        ]);
    }
}
