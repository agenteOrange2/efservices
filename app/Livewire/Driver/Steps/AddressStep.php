<?php
namespace App\Livewire\Driver\Steps;

use App\Helpers\Constants;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;

class AddressStep extends Component
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
            'from_date' => ['required', function ($attribute, $value, $fail) {
                if (!$this->isValidDate($value)) {
                    $fail('The from date field must be a valid date in MM-DD-YYYY format.');
                }
            }],
            'to_date' => ['nullable', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->isValidDate($value)) {
                    $fail('The to date field must be a valid date in MM-DD-YYYY format.');
                }
            }],
            'previous_addresses.*.address_line1' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.city' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.state' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.zip_code' => 'required_if:lived_three_years,0|string|max:255',
            'previous_addresses.*.from_date' => ['required_if:lived_three_years,0', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->isValidDate($value)) {
                    $fail('The previous address from date field must be a valid date in MM-DD-YYYY format.');
                }
            }],
            'previous_addresses.*.to_date' => ['required_if:lived_three_years,0', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->isValidDate($value)) {
                    $fail('The previous address to date field must be a valid date in MM-DD-YYYY format.');
                }
            }],
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
        
        // Process temporary photo if available when mounting step 2
        $this->processTemporaryPhoto();
        
        Log::info('AddressStep mounted', [
            'driver_id' => $this->driverId,
            'temp_photo_in_session' => session('temp_photo_file') ? 'yes' : 'no'
        ]);
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
                // Cambiando el formato de fecha a m-d-Y
                // Convertir fechas al formato m-d-Y para mostrarlas en la vista
                $this->from_date = $mainAddress->from_date ? $mainAddress->from_date->format('m-d-Y') : null;
                $this->to_date = $mainAddress->to_date ? $mainAddress->to_date->format('m-d-Y') : null;
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
                        'from_date' => $address->from_date ? $address->from_date->format('m-d-Y') : null,
                        'to_date' => $address->to_date ? $address->to_date->format('m-d-Y') : null,
                    ];
                }
            }
        }
    }
    
    /**
     * Convierte una fecha a formato Y-m-d para almacenarla en la base de datos
     * Maneja múltiples formatos de entrada posibles
     */
    protected function formatDateForDatabase($date)
    {
        if (empty($date)) return null;
        
        // Intentar diferentes formatos de fecha
        $formats = ['m-d-Y', 'Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d', 'd/m/Y'];
        
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Exception $e) {
                // Intentar con el siguiente formato
                continue;
            }
        }
        
        // Último intento: usar el parser genérico de Carbon
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            // Si todo falla, devolver la fecha tal cual
            Log::warning('No se pudo convertir la fecha al formato de base de datos', [
                'fecha' => $date,
                'error' => $e->getMessage()
            ]);
            return $date;
        }
    }

    /**
     * Convierte una fecha al formato m-d-Y para mostrarla en la vista
     */
    protected function formatDateForDisplay($date)
    {
        if (empty($date)) return null;
        
        try {
            if ($date instanceof Carbon) {
                return $date->format('m-d-Y');
            } else {
                return Carbon::parse($date)->format('m-d-Y');
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo convertir la fecha al formato de visualización', [
                'fecha' => $date,
                'error' => $e->getMessage()
            ]);
            return $date;
        }
    }
    
    /**
     * Verifica si una fecha es válida en cualquiera de los formatos aceptados
     */
    protected function isValidDate($date)
    {
        if (empty($date)) return false;
        
        // Intentar diferentes formatos de fecha
        $formats = ['m-d-Y', 'Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d', 'd/m/Y'];
        
        foreach ($formats as $format) {
            try {
                $parsedDate = Carbon::createFromFormat($format, $date);
                return $parsedDate && $parsedDate->format($format) === $date;
            } catch (\Exception $e) {
                // Intentar con el siguiente formato
                continue;
            }
        }
        
        // Último intento: usar el parser genérico de Carbon
        try {
            return Carbon::parse($date) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    // Validate address history for 3-year requirement
    protected function validateAddressHistory()
    {
        if ($this->lived_three_years) {
            return true;
        }
        
        // Calculate years in current address
        $fromDate = Carbon::parse($this->formatDateForDatabase($this->from_date));
        $toDate = $this->to_date ? Carbon::parse($this->formatDateForDatabase($this->to_date)) : Carbon::now();
        $currentAddressYears = $fromDate->diffInYears($toDate);
        
        // Calculate years in previous addresses
        $previousAddressesYears = 0;
        foreach ($this->previous_addresses as $address) {
            if (!empty($address['from_date']) && !empty($address['to_date'])) {
                $prevFromDate = Carbon::parse($this->formatDateForDatabase($address['from_date']));
                $prevToDate = Carbon::parse($this->formatDateForDatabase($address['to_date']));
                $previousAddressesYears += $prevFromDate->diffInYears($prevToDate);
            }
        }
        
        $totalYears = $currentAddressYears + $previousAddressesYears;
        
        // Validate coverage of 3 years
        if ($totalYears < 3) {
            $this->addError('address_years', 'Address history must total at least 3 years. Current total: ' . number_format($totalYears, 1) . ' years.');
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
            
            // Update primary address - convertir fechas al formato de la base de datos
            $mainAddress = $application->addresses()->updateOrCreate(
                ['primary' => true],
                [
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip_code' => $this->zip_code,
                    'lived_three_years' => $this->lived_three_years,
                    'from_date' => $this->formatDateForDatabase($this->from_date),
                    'to_date' => $this->formatDateForDatabase($this->to_date),
                ]
            );
            
            // Handle previous addresses
            if (!$this->lived_three_years) {
                $existingAddressIds = $application->addresses()->where('primary', false)->pluck('id')->toArray();
                $updatedAddressIds = [];
                
                foreach ($this->previous_addresses as $prevAddressData) {
                    if (empty($prevAddressData['address_line1'])) continue;
                    
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
                                'from_date' => $this->formatDateForDatabase($prevAddressData['from_date']),
                                'to_date' => $this->formatDateForDatabase($prevAddressData['to_date']),
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
                            'from_date' => $this->formatDateForDatabase($prevAddressData['from_date']),
                            'to_date' => $this->formatDateForDatabase($prevAddressData['to_date']),
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
        
        // Emitir evento para que el JavaScript sepa que se agregó una nueva dirección
        // Este evento será capturado por nuestro script de Pikaday
        $this->dispatch('previousAddressAdded');
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
            
            // Process temporary photo upload after user registration is complete
            $this->processTemporaryPhoto();
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
            $this->saveAddresses();
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
            $this->saveAddresses();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    // Process temporary photo upload after user registration is complete
    private function processTemporaryPhoto()
    {
        try {
            Log::info('processTemporaryPhoto called', [
                'driver_id' => $this->driverId,
                'session_temp_file' => session('temp_photo_file'),
                'session_data' => [
                    'temp_photo_file' => session('temp_photo_file'),
                    'temp_photo_original_name' => session('temp_photo_original_name'),
                    'temp_photo_extension' => session('temp_photo_extension')
                ]
            ]);
            
            // Check if we have temporary photo data in session
            $tempFileName = session('temp_photo_file');
            $originalName = session('temp_photo_original_name');
            $extension = session('temp_photo_extension');
            
            if ($tempFileName && $this->driverId) {
                $tempPath = storage_path('app/temp/' . $tempFileName);
                
                Log::info('Procesando foto temporal desde archivo', [
                    'temp_file' => $tempFileName,
                    'temp_path' => $tempPath,
                    'file_exists' => file_exists($tempPath),
                    'driver_id' => $this->driverId
                ]);
                
                if (file_exists($tempPath)) {
                    // Get the driver detail
                    $driverDetail = UserDriverDetail::find($this->driverId);
                    
                    if ($driverDetail) {
                        // Clear existing photos
                        $driverDetail->clearMediaCollection('profile_photo_driver');
                        
                        // Add new photo to collection
                        $driverDetail->addMedia($tempPath)
                            ->usingName($originalName ?? 'profile_photo')
                            ->toMediaCollection('profile_photo_driver');
                        
                        Log::info('Foto de perfil guardada exitosamente', [
                            'driver_id' => $driverDetail->id,
                            'original_name' => $originalName
                        ]);
                        
                        // Clean up temporary file and session data
                        unlink($tempPath);
                        session()->forget(['temp_photo_file', 'temp_photo_original_name', 'temp_photo_extension']);
                        
                        Log::info('Archivo temporal limpiado y datos de sesión eliminados');
                    } else {
                        Log::error('Driver detail no encontrado', ['driver_id' => $this->driverId]);
                    }
                } else {
                    Log::warning('Archivo temporal no existe', ['temp_path' => $tempPath]);
                }
            } else {
                Log::info('No hay foto temporal para procesar o falta driver_id', [
                    'temp_file' => $tempFileName,
                    'driver_id' => $this->driverId
                ]);
                
                // Still emit event to frontend as fallback
                $this->dispatch('process-temporary-photo');
            }
            
        } catch (\Exception $e) {
            Log::error('Error in processTemporaryPhoto', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function uploadTemporaryPhoto($photoData)
    {
        try {
            if (!$photoData || !isset($photoData['data'])) {
                return;
            }

            // Get the driver detail
            $driverDetail = UserDriverDetail::where('user_id', auth()->id())->first();
            
            if (!$driverDetail) {
                Log::error('Driver detail not found for user: ' . auth()->id());
                return;
            }

            // Decode base64 image data
            $imageData = $photoData['data'];
            if (strpos($imageData, 'data:') === 0) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            }
            $decodedImage = base64_decode($imageData);

            // Create temporary file
            $tempPath = tempnam(sys_get_temp_dir(), 'driver_photo_');
            file_put_contents($tempPath, $decodedImage);

            // Clear existing photos
            $driverDetail->clearMediaCollection('profile_photo_driver');
            
            // Add new photo to collection
            $driverDetail->addMedia($tempPath)
                ->usingName($photoData['name'] ?? 'profile_photo')
                ->toMediaCollection('profile_photo_driver');
            
            // Clean up temporary file
            unlink($tempPath);
            
            Log::info('Profile photo uploaded successfully for driver: ' . $driverDetail->id);
            
            return ['success' => true, 'message' => 'Photo uploaded successfully'];
            
        } catch (Exception $e) {
            Log::error('Error uploading temporary photo: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error uploading photo: ' . $e->getMessage()];
        }
    }
    
    // Render
    public function render()
    {
        return view('livewire.driver.steps.address-step', [
            'usStates' => Constants::usStates(),
        ]);
    }
}