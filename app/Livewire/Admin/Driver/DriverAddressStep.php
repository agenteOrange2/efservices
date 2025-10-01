<?php
namespace App\Livewire\Admin\Driver;

use App\Helpers\Constants;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;

class DriverAddressStep extends Component
{
    // Current Address
    public $address_line1;
    public $address_line2;
    public $city;
    public $state;
    public $zip_code;
    public $from_date;
    public $to_date;
    public $lived_three_years = false;
    
    // Previous Addresses
    public $previous_addresses = [];
    
    // References
    public $driverId;
    
    // Validation rules
    protected function rules()
    {
        return [
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date',
            'previous_addresses.*.address_line1' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.city' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.state' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.zip_code' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.from_date' => 'required_if:lived_three_years,0|date',
            'previous_addresses.*.to_date' => 'required_if:lived_three_years,0|date',
        ];
    }
    
    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ];
    }
    
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        
        if ($this->driverId) {
            $this->loadExistingData();
        }
        
        // Initialize with empty previous address
        if (!$this->lived_three_years && empty($this->previous_addresses)) {
            $this->previous_addresses = [$this->getEmptyPreviousAddress()];
        }
    }
    
    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        $application = $userDriverDetail->application;
        if ($application) {
            // Load primary address
            $mainAddress = $application->addresses()->where('primary', true)->first();
            if ($mainAddress) {
                $this->address_line1 = $mainAddress->address_line1;
                $this->address_line2 = $mainAddress->address_line2;
                $this->city = $mainAddress->city;
                $this->state = $mainAddress->state;
                $this->zip_code = $mainAddress->zip_code;
                $this->from_date = $mainAddress->from_date ? $mainAddress->from_date->format('Y-m-d') : null;
                $this->to_date = $mainAddress->to_date ? $mainAddress->to_date->format('Y-m-d') : null;
                $this->lived_three_years = $mainAddress->lived_three_years;
            }
            
            // Load previous addresses
            $previousAddresses = $application->addresses()->where('primary', false)->get();
            if ($previousAddresses->count() > 0) {
                $this->previous_addresses = [];
                foreach ($previousAddresses as $address) {
                    $this->previous_addresses[] = [
                        'id' => $address->id,
                        'address_line1' => $address->address_line1,
                        'address_line2' => $address->address_line2,
                        'city' => $address->city,
                        'state' => $address->state,
                        'zip_code' => $address->zip_code,
                        'from_date' => $address->from_date ? $address->from_date->format('Y-m-d') : null,
                        'to_date' => $address->to_date ? $address->to_date->format('Y-m-d') : null,
                    ];
                }
            }
        }
    }
    
    // Validate address history for 3-year requirement
    protected function validateAddressHistory()
    {
        if ($this->lived_three_years) {
            return true;
        }
        
        // Calculate years in current address
        $fromDate = Carbon::parse($this->from_date);
        $toDate = $this->to_date ? Carbon::parse($this->to_date) : Carbon::now();
        $currentAddressYears = $fromDate->diffInYears($toDate);
        
        // Calculate years in previous addresses
        $previousAddressesYears = 0;
        foreach ($this->previous_addresses as $address) {
            if (!empty($address['from_date']) && !empty($address['to_date'])) {
                $prevFromDate = Carbon::parse($address['from_date']);
                $prevToDate = Carbon::parse($address['to_date']);
                $previousAddressesYears += $prevFromDate->diffInYears($prevToDate);
            }
        }
        
        $totalYears = $currentAddressYears + $previousAddressesYears;
        
        // Validate coverage of 3 years
        if ($totalYears < 3) {
            $this->addError('address_years', 'Address history must total at least 3 years. Current total: ' . 
                           number_format($totalYears, 1) . ' years.');
            return false;
        }
        
        return true;
    }
    
    // Save address data to database
    protected function saveAddresses()
    {
        try {
            DB::beginTransaction();
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Get or create application
            $application = $userDriverDetail->application;
            if (!$application) {
                $application = DriverApplication::create([
                    'user_id' => $userDriverDetail->user_id,
                    'status' => 'draft'
                ]);
            }
            
            // Normalize date formats to Y-m-d
            $normalizedFromDate = null;
            $normalizedToDate = null;
            
            if (!empty($this->from_date)) {
                try {
                    $normalizedFromDate = \Carbon\Carbon::parse($this->from_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Invalid from_date format', [
                        'from_date' => $this->from_date,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            if (!empty($this->to_date)) {
                try {
                    $normalizedToDate = \Carbon\Carbon::parse($this->to_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Invalid to_date format', [
                        'to_date' => $this->to_date,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Update primary address
            $mainAddress = $application->addresses()->updateOrCreate(
                ['primary' => true],
                [
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip_code' => $this->zip_code,
                    'lived_three_years' => $this->lived_three_years,
                    'from_date' => $normalizedFromDate,
                    'to_date' => $normalizedToDate,
                ]
            );
            
            // Handle previous addresses
            if (!$this->lived_three_years) {
                $existingAddressIds = $application->addresses()->where('primary', false)->pluck('id')->toArray();
                $updatedAddressIds = [];
                
                foreach ($this->previous_addresses as $prevAddressData) {
                    if (empty($prevAddressData['address_line1'])) continue;
                    
                    // Normalize previous address dates
                    $prevFromDate = null;
                    $prevToDate = null;
                    
                    if (!empty($prevAddressData['from_date'])) {
                        try {
                            $prevFromDate = \Carbon\Carbon::parse($prevAddressData['from_date'])->format('Y-m-d');
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::warning('Invalid previous address from_date format', [
                                'from_date' => $prevAddressData['from_date'],
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    if (!empty($prevAddressData['to_date'])) {
                        try {
                            $prevToDate = \Carbon\Carbon::parse($prevAddressData['to_date'])->format('Y-m-d');
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::warning('Invalid previous address to_date format', [
                                'to_date' => $prevAddressData['to_date'],
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $addressId = $prevAddressData['id'] ?? null;
                    if ($addressId) {
                        // Update existing address
                        $address = $application->addresses()->find($addressId);
                        if ($address) {
                            $address->update([
                                'address_line1' => $prevAddressData['address_line1'],
                                'address_line2' => $prevAddressData['address_line2'] ?? null,
                                'city' => $prevAddressData['city'],
                                'state' => $prevAddressData['state'],
                                'zip_code' => $prevAddressData['zip_code'],
                                'from_date' => $prevFromDate,
                                'to_date' => $prevToDate,
                            ]);
                            $updatedAddressIds[] = $address->id;
                        }
                    } else {
                        // Create new address
                        $address = $application->addresses()->create([
                            'primary' => false,
                            'address_line1' => $prevAddressData['address_line1'],
                            'address_line2' => $prevAddressData['address_line2'] ?? null,
                            'city' => $prevAddressData['city'],
                            'state' => $prevAddressData['state'],
                            'zip_code' => $prevAddressData['zip_code'],
                            'from_date' => $prevFromDate,
                            'to_date' => $prevToDate,
                            'lived_three_years' => false
                        ]);
                        $updatedAddressIds[] = $address->id;
                    }
                }
                
                // Delete addresses that are no longer needed
                $addressesToDelete = array_diff($existingAddressIds, $updatedAddressIds);
                if (!empty($addressesToDelete)) {
                    $application->addresses()->whereIn('id', $addressesToDelete)->delete();
                }
            } else {
                // If lived three years, delete all previous addresses
                $application->addresses()->where('primary', false)->delete();
            }
            
            // Update current step
            $userDriverDetail->update(['current_step' => 2]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving address', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving address information: ' . $e->getMessage());
            return false;
        }
    }
    
    // Add previous address
    public function addPreviousAddress()
    {
        $this->previous_addresses[] = $this->getEmptyPreviousAddress();
    }
    
    // Remove previous address
    public function removePreviousAddress($index)
    {
        if (count($this->previous_addresses) > 1) {
            unset($this->previous_addresses[$index]);
            $this->previous_addresses = array_values($this->previous_addresses);
        }
    }
    
    // Get empty previous address structure
    protected function getEmptyPreviousAddress()
    {
        return [
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'from_date' => '',
            'to_date' => ''
        ];
    }
    
    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Additional validation for address history
        if (!$this->lived_three_years && !$this->validateAddressHistory()) {
            return;
        }
        
        // Save to database
        if ($this->driverId) {
            $this->saveAddresses();
        }
        
        // Move to next step
        $this->dispatch('nextStep');
    }
    
    // Previous step
    public function previous()
    {
        // Partial validation
        $this->validate($this->partialRules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveAddresses();
        }
        
        // Move to previous step
        $this->dispatch('prevStep');
    }
    
    // Save and exit
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveAddresses();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-address-step', [
            'usStates' => Constants::usStates(),
        ]);
    }
}