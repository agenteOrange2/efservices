<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;

class AccidentStep extends Component
{
    // Accident Records
    public $has_accidents = false;
    public $accidents = [];
    
    // References
    public $driverId;
    
    // Validation rules
    protected function rules()
    {
        $rules = [
            'has_accidents' => 'sometimes|boolean',
        ];
        
        if ($this->has_accidents) {
            foreach (range(0, count($this->accidents) - 1) as $index) {
                $rules["accidents.{$index}.accident_date"] = 'required|date';
                $rules["accidents.{$index}.nature_of_accident"] = 'required|string|max:255';
                $rules["accidents.{$index}.number_of_injuries"] = 
                    "required_if:accidents.{$index}.had_injuries,true|nullable|integer|min:0";
                $rules["accidents.{$index}.number_of_fatalities"] = 
                    "required_if:accidents.{$index}.had_fatalities,true|nullable|integer|min:0";
            }
        }
        
        return $rules;
    }
    
    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'has_accidents' => 'sometimes|boolean',
        ];
    }
    
    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        if ($this->driverId) {
            $this->loadExistingData();
        }
        
        // Initialize with empty accident
        if ($this->has_accidents && empty($this->accidents)) {
            $this->accidents = [$this->getEmptyAccident()];
        }
    }
    
    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        // Default value
        $this->has_accidents = false;
        
        // Check if has accidents from application details
        if ($userDriverDetail->application && $userDriverDetail->application->details) {
            $this->has_accidents = (bool)(
                $userDriverDetail->application->details->has_accidents ?? false
            );
        }
        
        // Load accidents
        $accidents = $userDriverDetail->accidents;
        if ($accidents->count() > 0) {
            $this->has_accidents = true;
            $this->accidents = [];
            
            foreach ($accidents as $accident) {
                $this->accidents[] = [
                    'id' => $accident->id,
                    'accident_date' => $accident->accident_date ? 
                        $accident->accident_date->format('Y-m-d') : null,
                    'nature_of_accident' => $accident->nature_of_accident,
                    'had_injuries' => $accident->had_injuries,
                    'number_of_injuries' => $accident->number_of_injuries,
                    'had_fatalities' => $accident->had_fatalities,
                    'number_of_fatalities' => $accident->number_of_fatalities,
                    'comments' => $accident->comments,
                ];
            }
        }
        
        // Initialize with empty accident if needed
        if ($this->has_accidents && empty($this->accidents)) {
            $this->accidents = [$this->getEmptyAccident()];
        }
    }
    
    // Save accident data to database
    protected function saveAccidentData()
    {
        try {
            DB::beginTransaction();
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Update application details with accident flag
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_accidents' => $this->has_accidents
                ]);
            }
            
            if (!$this->has_accidents) {
                // If no accidents, delete all existing records
                $userDriverDetail->accidents()->delete();
            } else {
                // Handle accidents
                $existingAccidentIds = $userDriverDetail->accidents()->pluck('id')->toArray();
                $updatedAccidentIds = [];
                
                foreach ($this->accidents as $accidentData) {
                    if (empty($accidentData['accident_date'])) continue;
                    
                    $accidentId = $accidentData['id'] ?? null;
                    if ($accidentId) {
                        // Update existing accident
                        $accident = $userDriverDetail->accidents()->find($accidentId);
                        if ($accident) {
                            $accident->update([
                                'accident_date' => $accidentData['accident_date'],
                                'nature_of_accident' => $accidentData['nature_of_accident'],
                                'had_injuries' => $accidentData['had_injuries'] ?? false,
                                'number_of_injuries' => $accidentData['had_injuries'] ? 
                                    ($accidentData['number_of_injuries'] ?? 0) : 0,
                                'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                                'number_of_fatalities' => $accidentData['had_fatalities'] ? 
                                    ($accidentData['number_of_fatalities'] ?? 0) : 0,
                                'comments' => $accidentData['comments'] ?? null,
                            ]);
                            $updatedAccidentIds[] = $accident->id;
                        }
                    } else {
                        // Create new accident
                        $accident = $userDriverDetail->accidents()->create([
                            'accident_date' => $accidentData['accident_date'],
                            'nature_of_accident' => $accidentData['nature_of_accident'],
                            'had_injuries' => $accidentData['had_injuries'] ?? false,
                            'number_of_injuries' => $accidentData['had_injuries'] ? 
                                ($accidentData['number_of_injuries'] ?? 0) : 0,
                            'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                            'number_of_fatalities' => $accidentData['had_fatalities'] ? 
                                ($accidentData['number_of_fatalities'] ?? 0) : 0,
                            'comments' => $accidentData['comments'] ?? null,
                        ]);
                        $updatedAccidentIds[] = $accident->id;
                    }
                }
                
                // Delete accidents that are no longer needed
                $accidentsToDelete = array_diff($existingAccidentIds, $updatedAccidentIds);
                if (!empty($accidentsToDelete)) {
                    $userDriverDetail->accidents()->whereIn('id', $accidentsToDelete)->delete();
                }
            }
            
            // Update current step
            $userDriverDetail->update(['current_step' => 8]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving accident information: ' . $e->getMessage());
            return false;
        }
    }
    
    // Add accident
    public function addAccident()
    {
        $this->accidents[] = $this->getEmptyAccident();
    }
    
    // Remove accident
    public function removeAccident($index)
    {
        if (count($this->accidents) > 1) {
            unset($this->accidents[$index]);
            $this->accidents = array_values($this->accidents);
        }
    }
    
    // Get empty accident structure
    protected function getEmptyAccident()
    {
        return [
            'accident_date' => '',
            'nature_of_accident' => '',
            'had_injuries' => false,
            'number_of_injuries' => 0,
            'had_fatalities' => false,
            'number_of_fatalities' => 0,
            'comments' => '',
        ];
    }
    
    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveAccidentData();
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
            $this->saveAccidentData();
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
            $this->saveAccidentData();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    // Render
    public function render()
    {
        return view('livewire.driver.steps.accident-step');
    }
}