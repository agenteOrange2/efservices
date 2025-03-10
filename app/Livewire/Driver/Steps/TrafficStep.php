<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;

class TrafficStep extends Component
{
    // Traffic Convictions
    public $has_traffic_convictions = false;
    public $traffic_convictions = [];
    
    // References
    public $driverId;
    
    // Validation rules
    protected function rules()
    {
        $rules = [
            'has_traffic_convictions' => 'sometimes|boolean',
        ];
        
        if ($this->has_traffic_convictions) {
            foreach (range(0, count($this->traffic_convictions) - 1) as $index) {
                $rules["traffic_convictions.{$index}.conviction_date"] = 'required|date';
                $rules["traffic_convictions.{$index}.location"] = 'required|string|max:255';
                $rules["traffic_convictions.{$index}.charge"] = 'required|string|max:255';
                $rules["traffic_convictions.{$index}.penalty"] = 'required|string|max:255';
            }
        }
        
        return $rules;
    }
    
    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'has_traffic_convictions' => 'sometimes|boolean',
        ];
    }
    
    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        if ($this->driverId) {
            $this->loadExistingData();
        }
        
        // Initialize with empty traffic conviction
        if ($this->has_traffic_convictions && empty($this->traffic_convictions)) {
            $this->traffic_convictions = [$this->getEmptyTrafficConviction()];
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
        $this->has_traffic_convictions = false;
        
        // Check if has traffic convictions from application details
        if ($userDriverDetail->application && $userDriverDetail->application->details) {
            $this->has_traffic_convictions = (bool)(
                $userDriverDetail->application->details->has_traffic_convictions ?? false
            );
        }
        
        // Load traffic convictions
        $trafficConvictions = $userDriverDetail->trafficConvictions;
        if ($trafficConvictions->count() > 0) {
            $this->has_traffic_convictions = true;
            $this->traffic_convictions = [];
            
            foreach ($trafficConvictions as $conviction) {
                $this->traffic_convictions[] = [
                    'id' => $conviction->id,
                    'conviction_date' => $conviction->conviction_date ? 
                        $conviction->conviction_date->format('Y-m-d') : null,
                    'location' => $conviction->location,
                    'charge' => $conviction->charge,
                    'penalty' => $conviction->penalty,
                ];
            }
        }
        
        // Initialize with empty traffic conviction if needed
        if ($this->has_traffic_convictions && empty($this->traffic_convictions)) {
            $this->traffic_convictions = [$this->getEmptyTrafficConviction()];
        }
    }
    
    // Save traffic data to database
    protected function saveTrafficData()
    {
        try {
            DB::beginTransaction();
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Update application details with traffic conviction flag
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_traffic_convictions' => $this->has_traffic_convictions
                ]);
            }
            
            if (!$this->has_traffic_convictions) {
                // If no traffic convictions, delete all existing records
                $userDriverDetail->trafficConvictions()->delete();
            } else {
                // Handle traffic convictions
                $existingConvictionIds = $userDriverDetail->trafficConvictions()->pluck('id')->toArray();
                $updatedConvictionIds = [];
                
                foreach ($this->traffic_convictions as $convictionData) {
                    if (empty($convictionData['conviction_date'])) continue;
                    
                    $convictionId = $convictionData['id'] ?? null;
                    if ($convictionId) {
                        // Update existing conviction
                        $conviction = $userDriverDetail->trafficConvictions()->find($convictionId);
                        if ($conviction) {
                            $conviction->update([
                                'conviction_date' => $convictionData['conviction_date'],
                                'location' => $convictionData['location'],
                                'charge' => $convictionData['charge'],
                                'penalty' => $convictionData['penalty'],
                            ]);
                            $updatedConvictionIds[] = $conviction->id;
                        }
                    } else {
                        // Create new conviction
                        $conviction = $userDriverDetail->trafficConvictions()->create([
                            'conviction_date' => $convictionData['conviction_date'],
                            'location' => $convictionData['location'],
                            'charge' => $convictionData['charge'],
                            'penalty' => $convictionData['penalty'],
                        ]);
                        $updatedConvictionIds[] = $conviction->id;
                    }
                }
                
                // Delete convictions that are no longer needed
                $convictionsToDelete = array_diff($existingConvictionIds, $updatedConvictionIds);
                if (!empty($convictionsToDelete)) {
                    $userDriverDetail->trafficConvictions()->whereIn('id', $convictionsToDelete)->delete();
                }
            }
            
            // Update current step
            $userDriverDetail->update(['current_step' => 7]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving traffic conviction information: ' . $e->getMessage());
            return false;
        }
    }
    
    // Add traffic conviction
    public function addTrafficConviction()
    {
        $this->traffic_convictions[] = $this->getEmptyTrafficConviction();
    }
    
    // Remove traffic conviction
    public function removeTrafficConviction($index)
    {
        if (count($this->traffic_convictions) > 1) {
            unset($this->traffic_convictions[$index]);
            $this->traffic_convictions = array_values($this->traffic_convictions);
        }
    }
    
    // Get empty traffic conviction structure
    protected function getEmptyTrafficConviction()
    {
        return [
            'conviction_date' => '',
            'location' => '',
            'charge' => '',
            'penalty' => '',
        ];
    }
    
    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveTrafficData();
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
            $this->saveTrafficData();
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
            $this->saveTrafficData();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    // Render
    public function render()
    {
        return view('livewire.driver.steps.traffic-step');
    }
}