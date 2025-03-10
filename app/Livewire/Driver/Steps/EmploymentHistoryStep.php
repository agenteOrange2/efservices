<?php

namespace App\Livewire\Driver\Steps;

use App\Helpers\Constants;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\MasterCompany;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\DriverUnemploymentPeriod;

class EmploymentHistoryStep extends Component
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
        'fax' => '',
        'employed_from' => '',
        'employed_to' => '',
        'positions_held' => '',
        'subject_to_fmcsr' => false,
        'safety_sensitive_function' => false,
        'reason_for_leaving' => '',
        'other_reason_description' => '',
        'explanation' => '',
        'is_from_master' => false
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
            'company_form.employed_from' => 'required|date',
            'company_form.employed_to' => 'required|date|after_or_equal:company_form.employed_from',
            'company_form.positions_held' => 'required|string|max:255',
            'company_form.reason_for_leaving' => 'required|string|max:255',
            'company_form.other_reason_description' =>
            'required_if:company_form.reason_for_leaving,other|max:255',
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
            $this->has_unemployment_periods = (bool)(
                $userDriverDetail->application->details->has_unemployment_periods ?? false
            );
            $this->has_completed_employment_history = (bool)(
                $userDriverDetail->application->details->has_completed_employment_history ?? false
            );
        }

        // Load unemployment periods
        $unemploymentPeriods = $userDriverDetail->unemploymentPeriods;
        if ($unemploymentPeriods->count() > 0) {
            $this->has_unemployment_periods = true;
            $this->unemployment_periods = [];
            foreach ($unemploymentPeriods as $period) {
                $this->unemployment_periods[] = [
                    'id' => $period->id,
                    'start_date' => $period->start_date ? $period->start_date->format('Y-m-d') : null,
                    'end_date' => $period->end_date ? $period->end_date->format('Y-m-d') : null,
                    'comments' => $period->comments,
                ];
            }
        }

        // Load employment companies
        $employmentCompanies = $userDriverDetail->employmentCompanies()
            ->with('masterCompany') // Eager load masterCompany relationship
            ->get();

        if ($employmentCompanies->count() > 0) {
            $this->employment_companies = [];
            foreach ($employmentCompanies as $company) {
                $masterCompany = $company->masterCompany;

                $this->employment_companies[] = [
                    'id' => $company->id,
                    'master_company_id' => $masterCompany ? $masterCompany->id : null,
                    'company_name' => $masterCompany ? $masterCompany->company_name : $company->company_name,
                    'address' => $masterCompany ? $masterCompany->address : null,
                    'city' => $masterCompany ? $masterCompany->city : null,
                    'state' => $masterCompany ? $masterCompany->state : null,
                    'zip' => $masterCompany ? $masterCompany->zip : null,
                    'contact' => $masterCompany ? $masterCompany->contact : null,
                    'phone' => $masterCompany ? $masterCompany->phone : null,
                    'fax' => $masterCompany ? $masterCompany->fax : null,
                    'employed_from' => $company->employed_from ? $company->employed_from->format('Y-m-d') : null,
                    'employed_to' => $company->employed_to ? $company->employed_to->format('Y-m-d') : null,
                    'positions_held' => $company->positions_held,
                    'subject_to_fmcsr' => $company->subject_to_fmcsr,
                    'safety_sensitive_function' => $company->safety_sensitive_function,
                    'reason_for_leaving' => $company->reason_for_leaving,
                    'other_reason_description' => $company->other_reason_description,
                    'explanation' => $company->explanation,
                    'status' => 'ACTIVE',
                    'is_from_master' => $masterCompany ? true : false
                ];
            }
        }
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

        // Process unemployment periods
        if ($this->has_unemployment_periods) {
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
    protected function saveEmploymentHistoryData()
    {
        try {
            DB::beginTransaction();

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Update application details
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_unemployment_periods' => $this->has_unemployment_periods,
                    'has_completed_employment_history' => $this->has_completed_employment_history
                ]);
            }

            // Handle unemployment periods
            if ($this->has_unemployment_periods) {
                $existingPeriodIds = $userDriverDetail->unemploymentPeriods()->pluck('id')->toArray();
                $updatedPeriodIds = [];

                foreach ($this->unemployment_periods as $period) {
                    if (empty($period['start_date']) || empty($period['end_date'])) continue;

                    $periodId = $period['id'] ?? null;
                    if ($periodId) {
                        // Update existing period
                        $unemploymentPeriod = $userDriverDetail->unemploymentPeriods()->find($periodId);
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

                // Delete periods that are no longer needed
                $periodsToDelete = array_diff($existingPeriodIds, $updatedPeriodIds);
                if (!empty($periodsToDelete)) {
                    $userDriverDetail->unemploymentPeriods()->whereIn('id', $periodsToDelete)->delete();
                }
            } else {
                // If no unemployment periods, delete all existing records
                $userDriverDetail->unemploymentPeriods()->delete();
            }

            // Handle employment companies
            $existingCompanyIds = $userDriverDetail->employmentCompanies()->pluck('id')->toArray();
            $updatedCompanyIds = [];

            foreach ($this->employment_companies as $company) {
                if (
                    empty($company['employed_from']) ||
                    empty($company['employed_to'])
                ) {
                    continue;
                }

                $companyId = $company['id'] ?? null;
                $master_company_id = $company['master_company_id'] ?? null;

                // Si no hay master_company_id pero tenemos datos de empresa,
                // buscarla o crearla
                if (!$master_company_id && !empty($company['company_name'])) {
                    // Intentar encontrar una master company con esos datos
                    $masterCompany = MasterCompany::firstOrCreate(
                        ['company_name' => $company['company_name']],
                        [
                            'address' => $company['address'] ?? null,
                            'city' => $company['city'] ?? null,
                            'state' => $company['state'] ?? null,
                            'zip' => $company['zip'] ?? null,
                            'contact' => $company['contact'] ?? null,
                            'phone' => $company['phone'] ?? null,
                            'fax' => $company['fax'] ?? null,
                        ]
                    );
                    $master_company_id = $masterCompany->id;
                }

                if ($companyId) {
                    // Update existing company
                    $employmentCompany = $userDriverDetail->employmentCompanies()->find($companyId);
                    if ($employmentCompany) {
                        $employmentCompany->update([
                            'master_company_id' => $master_company_id,
                            'employed_from' => $company['employed_from'],
                            'employed_to' => $company['employed_to'],
                            'positions_held' => $company['positions_held'] ?? null,
                            'subject_to_fmcsr' => $company['subject_to_fmcsr'] ?? false,
                            'safety_sensitive_function' => $company['safety_sensitive_function'] ?? false,
                            'reason_for_leaving' => $company['reason_for_leaving'] ?? null,
                            'other_reason_description' => isset($company['reason_for_leaving']) &&
                                $company['reason_for_leaving'] === 'other'
                                ? $company['other_reason_description']
                                : null,
                            'explanation' => $company['explanation'] ?? null
                        ]);
                        $updatedCompanyIds[] = $employmentCompany->id;
                    }
                } else {
                    // Create new company
                    $employmentCompany = $userDriverDetail->employmentCompanies()->create([
                        'master_company_id' => $master_company_id,
                        'employed_from' => $company['employed_from'],
                        'employed_to' => $company['employed_to'],
                        'positions_held' => $company['positions_held'] ?? null,
                        'subject_to_fmcsr' => $company['subject_to_fmcsr'] ?? false,
                        'safety_sensitive_function' => $company['safety_sensitive_function'] ?? false,
                        'reason_for_leaving' => $company['reason_for_leaving'] ?? null,
                        'other_reason_description' => isset($company['reason_for_leaving']) &&
                            $company['reason_for_leaving'] === 'other'
                            ? $company['other_reason_description']
                            : null,
                        'explanation' => $company['explanation'] ?? null
                    ]);
                    $updatedCompanyIds[] = $employmentCompany->id;
                }
            }

            // Delete companies that are no longer needed
            $companiesToDelete = array_diff($existingCompanyIds, $updatedCompanyIds);
            if (!empty($companiesToDelete)) {
                $userDriverDetail->employmentCompanies()->whereIn('id', $companiesToDelete)->delete();
            }

            // Update current step
            $userDriverDetail->update(['current_step' => 10]);

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
            'fax' => '',
            'employed_from' => '',
            'employed_to' => '',
            'positions_held' => '',
            'subject_to_fmcsr' => false,
            'safety_sensitive_function' => false,
            'reason_for_leaving' => '',
            'other_reason_description' => '',
            'explanation' => '',
            'is_from_master' => false
        ];
        $this->editing_company_index = null;
    }

    // Save company form
    public function saveCompany()
    {
        // Validate the company form
        $this->validate([
            'company_form.company_name' => 'required|string|max:255',
            'company_form.employed_from' => 'required|date',
            'company_form.employed_to' => 'required|date|after_or_equal:company_form.employed_from',
            'company_form.positions_held' => 'required|string|max:255',
            'company_form.reason_for_leaving' => 'required|string|max:255',
            'company_form.other_reason_description' =>
            'required_if:company_form.reason_for_leaving,other|max:255',
        ]);

        $isFromMaster = isset($this->company_form['is_from_master']) && $this->company_form['is_from_master'];

        // Update or add company to list
        if ($this->editing_company_index !== null) {
            $this->employment_companies[$this->editing_company_index] = array_merge(
                $this->company_form,
                ['status' => 'ACTIVE']
            );
        } else {
            $this->employment_companies[] = array_merge(
                $this->company_form,
                ['status' => 'ACTIVE']
            );
        }

        // Close the form and recalculate history
        $this->showCompanyForm = false;
        $this->resetCompanyForm();
        $this->calculateYearsOfHistory();
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
                'is_from_master' => true // Indicar que viene de MasterCompany
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
        if ($this->deleteType === 'employment') {
            // Eliminamos el registro de empleo
            unset($this->employment_companies[$this->deleteIndex]);
            $this->employment_companies = array_values($this->employment_companies);
        } elseif ($this->deleteType === 'unemployment') {
            // Eliminamos el registro de desempleo
            unset($this->unemployment_periods[$this->deleteIndex]);
            $this->unemployment_periods = array_values($this->unemployment_periods);
        }

        // Cerramos el modal y recalculamos el historial
        $this->showDeleteConfirmationModal = false;
        $this->deleteType = null;
        $this->deleteIndex = null;
        $this->calculateYearsOfHistory();
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
            $this->saveEmploymentHistoryData();
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

    // Render
    public function render()
    {
        return view('livewire.driver.steps.employment-history-step', [
            'usStates' => Constants::usStates(),
        ]);
    }
}
