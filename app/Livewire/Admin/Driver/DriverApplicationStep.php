<?php

namespace App\Livewire\Admin\Driver;

use App\Helpers\Constants;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;

class DriverApplicationStep extends Component
{
    // Application Details
    public $applying_position;
    public $applying_position_other;
    public $applying_location;
    public $eligible_to_work = true;
    public $can_speak_english = true;
    public $has_twic_card = false;
    public $twic_expiration_date;
    public $expected_pay;
    public $how_did_hear = 'internet';
    public $how_did_hear_other;
    public $referral_employee_name;

    // Work History
    public $has_work_history = false;
    public $work_histories = [];

    // References
    public $driverId;
    public $application;

    // Validation rules
    protected function rules()
    {
        return [
            'applying_position' => 'required|string',
            'applying_position_other' => 'required_if:applying_position,other',
            'applying_location' => 'required|string',
            'eligible_to_work' => 'accepted',
            'twic_expiration_date' => 'nullable|required_if:has_twic_card,true|date',
            'how_did_hear' => 'required|string',
            'how_did_hear_other' => 'required_if:how_did_hear,other',
            'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
            'work_histories.*.previous_company' => 'required_if:has_work_history,true|string|max:255',
            'work_histories.*.start_date' => 'required_if:has_work_history,true|date',
            'work_histories.*.end_date' =>
            'required_if:has_work_history,true|date|after_or_equal:work_histories.*.start_date',
            'work_histories.*.location' => 'required_if:has_work_history,true|string|max:255',
            'work_histories.*.position' => 'required_if:has_work_history,true|string|max:255',
        ];
    }

    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'applying_position' => 'required|string',
            'applying_location' => 'required|string',
            'eligible_to_work' => 'accepted',
        ];
    }

    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;

        if ($this->driverId) {
            $this->loadExistingData();
        }

        // Initialize with empty work history
        if ($this->has_work_history && empty($this->work_histories)) {
            $this->work_histories = [$this->getEmptyWorkHistory()];
        }
    }

    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        $this->application = $userDriverDetail->application;
        if ($this->application && $this->application->details) {
            $details = $this->application->details;

            $this->applying_position = $details->applying_position;
            $this->applying_position_other = $details->applying_position_other;
            $this->applying_location = $details->applying_location;
            $this->eligible_to_work = $details->eligible_to_work;
            $this->can_speak_english = $details->can_speak_english;
            $this->has_twic_card = $details->has_twic_card;
            $this->twic_expiration_date = $details->twic_expiration_date ? $details->twic_expiration_date->format('Y-m-d') : null;
            $this->expected_pay = $details->expected_pay;
            $this->how_did_hear = $details->how_did_hear;
            $this->how_did_hear_other = $details->how_did_hear_other;
            $this->referral_employee_name = $details->referral_employee_name;
            $this->has_work_history = (bool)($details->has_work_history ?? false);            
        }

        // Load work histories
        $workHistories = $userDriverDetail->workHistories;
        if ($workHistories->count() > 0) {

            $this->has_work_history = true;
            $this->work_histories = [];
            foreach ($workHistories as $history) {
                $this->work_histories[] = [
                    'id' => $history->id,
                    'previous_company' => $history->previous_company,
                    'start_date' => $history->start_date ? $history->start_date->format('Y-m-d') : null,
                    'end_date' => $history->end_date ? $history->end_date->format('Y-m-d') : null,
                    'location' => $history->location,
                    'position' => $history->position,
                    'reason_for_leaving' => $history->reason_for_leaving,
                    'reference_contact' => $history->reference_contact,
                ];
            }

                    // También actualiza el campo en los detalles de la aplicación si es necesario
        if ($this->application && $this->application->details && !$this->application->details->has_work_history) {
            $this->application->details->update(['has_work_history' => true]);
        }
        }
    }

    
    // Modifica el método saveApplicationDetails() en el archivo DriverApplicationStep.php

    protected function saveApplicationDetails()
    {
        try {
            DB::beginTransaction();

            Log::info('Guardando detalles de aplicación', ['driverId' => $this->driverId]);

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                Log::error('Driver no encontrado', ['driverId' => $this->driverId]);
                throw new \Exception('Driver not found');
            }

            // Get or create application
            $application = $userDriverDetail->application;
            if (!$application) {
                Log::info('Creando nueva aplicación para el driver', ['userId' => $userDriverDetail->user_id]);
                $application = DriverApplication::create([
                    'user_id' => $userDriverDetail->user_id,
                    'status' => 'draft'
                ]);
            }

            // Update application details
            Log::info('Actualizando detalles de aplicación', [
                'position' => $this->applying_position,
                'location' => $this->applying_location
            ]);

            $applicationDetails = $application->details()->updateOrCreate(
                [],
                [
                    'applying_position' => $this->applying_position,
                    'applying_position_other' => $this->applying_position === 'other' ? $this->applying_position_other : null,
                    'applying_location' => $this->applying_location,
                    'eligible_to_work' => $this->eligible_to_work,
                    'can_speak_english' => $this->can_speak_english,
                    'has_twic_card' => $this->has_twic_card,
                    'twic_expiration_date' => $this->has_twic_card ? $this->twic_expiration_date : null,
                    'expected_pay' => $this->expected_pay,
                    'how_did_hear' => $this->how_did_hear,
                    'how_did_hear_other' => $this->how_did_hear === 'other' ? $this->how_did_hear_other : null,
                    'referral_employee_name' => $this->how_did_hear === 'employee_referral' ? $this->referral_employee_name : null,
                    'has_work_history' => $this->has_work_history,
                ]
            );

            // Handle work histories
            if ($this->has_work_history) {
                Log::info('Procesando historiales de trabajo', ['count' => count($this->work_histories)]);

                $existingWorkHistoryIds = $userDriverDetail->workHistories()->pluck('id')->toArray();
                $updatedWorkHistoryIds = [];

                foreach ($this->work_histories as $historyData) {
                    if (empty($historyData['previous_company'])) continue;

                    $historyId = $historyData['id'] ?? null;

                    if ($historyId) {
                        // Update existing history
                        $history = $userDriverDetail->workHistories()->find($historyId);
                        if ($history) {
                            $history->update([
                                'previous_company' => $historyData['previous_company'],
                                'start_date' => $historyData['start_date'],
                                'end_date' => $historyData['end_date'],
                                'location' => $historyData['location'],
                                'position' => $historyData['position'],
                                'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                                'reference_contact' => $historyData['reference_contact'] ?? null,
                            ]);
                            $updatedWorkHistoryIds[] = $history->id;
                            Log::info('Actualizado historial de trabajo existente', ['id' => $history->id]);
                        }
                    } else {
                        // Create new history
                        $history = $userDriverDetail->workHistories()->create([
                            'previous_company' => $historyData['previous_company'],
                            'start_date' => $historyData['start_date'],
                            'end_date' => $historyData['end_date'],
                            'location' => $historyData['location'],
                            'position' => $historyData['position'],
                            'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                            'reference_contact' => $historyData['reference_contact'] ?? null,
                        ]);
                        $updatedWorkHistoryIds[] = $history->id;
                        Log::info('Creado nuevo historial de trabajo', ['id' => $history->id]);
                    }
                }

                // Delete histories that are no longer needed
                $historiesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
                if (!empty($historiesToDelete)) {
                    $userDriverDetail->workHistories()->whereIn('id', $historiesToDelete)->delete();
                    Log::info('Eliminados historiales de trabajo no necesarios', ['ids' => $historiesToDelete]);
                }
            } else {
                // If no work history, delete all existing records
                $userDriverDetail->workHistories()->delete();
                Log::info('Eliminados todos los historiales de trabajo (no hay historial)');
            }

            // Update current step
            $userDriverDetail->update(['current_step' => 3]);

            Log::info('Actualización de aplicación completada con éxito');
            DB::commit();

            session()->flash('message', 'Información de aplicación guardada correctamente.');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardando aplicación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving application details: ' . $e->getMessage());
            return false;
        }
    }

    // Add work history
    public function addWorkHistory()
    {
        $this->work_histories[] = $this->getEmptyWorkHistory();
    }

    // Remove work history
    public function removeWorkHistory($index)
    {
        if (count($this->work_histories) > 1) {
            unset($this->work_histories[$index]);
            $this->work_histories = array_values($this->work_histories);
        }
    }

    // Get empty work history structure
    protected function getEmptyWorkHistory()
    {
        return [
            'previous_company' => '',
            'start_date' => '',
            'end_date' => '',
            'location' => '',
            'position' => '',
            'reason_for_leaving' => '',
            'reference_contact' => '',
        ];
    }

    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());

        // Save to database
        if ($this->driverId) {
            $this->saveApplicationDetails();
        }

        // Move to next step
        $this->dispatch('nextStep');
    }

    // Previous step
    public function previous()
    {
        // Basic save before going back
        if ($this->driverId) {
            $this->validate($this->partialRules());
            $this->saveApplicationDetails();
        }

        $this->dispatch('prevStep');
    }

    // Save and exit
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());

        // Save to database
        if ($this->driverId) {
            $this->saveApplicationDetails();
        }

        $this->dispatch('saveAndExit');
    }

    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-application-step', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources()
        ]);
    }
}
