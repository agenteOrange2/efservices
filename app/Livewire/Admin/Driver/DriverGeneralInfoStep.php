<?php

namespace App\Livewire\Admin\Driver;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class DriverGeneralInfoStep extends Component
{
    use WithFileUploads;

    // Driver Information
    public $photo;
    public $name;
    public $middle_name;
    public $last_name;
    public $email;
    public $phone;
    public $date_of_birth;
    public $password;
    public $password_confirmation;
    public $status = 1;
    public $terms_accepted = false;
    public $photo_preview_url;

    // Custom Dates for Historical Drivers
    public $use_custom_dates = false;
    public $custom_created_at;

    // References
    public $driverId;
    public $carrier;

    // Validation rules
    protected function rules()
    {
        $passwordRules = $this->driverId
            ? 'nullable|min:8|confirmed'
            : 'required|min:8|confirmed';

        $emailRule = 'required|email';
        if ($this->driverId) {
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if ($userDriverDetail) {
                $emailRule .= '|unique:users,email,' . $userDriverDetail->user_id;
            }
        } else {
            $emailRule .= '|unique:users,email';
        }

        $rules = [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => $emailRule,
            'phone' => 'required|string|max:15',
            'date_of_birth' => [
                'required',
                'date',
                'before_or_equal:' . \Carbon\Carbon::now()->subYears(18)->format('Y-m-d'),
            ],
            'password' => $passwordRules,
            'password_confirmation' => 'nullable|same:password',
            'terms_accepted' => 'accepted',
            'photo' => 'nullable|image|max:10240',
            'use_custom_dates' => 'boolean',
        ];

        // Add custom dates validation if enabled
        if ($this->use_custom_dates) {
            $rules['custom_created_at'] = 'nullable|date';            
        }

        return $rules;
    }

    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ];
    }

    protected function messages()
    {
        return [
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old to register.',
        ];
    }

    // Initialize
    public function mount($driverId = null, $carrier = null)
    {
        $this->driverId = $driverId;
        $this->carrier = $carrier;

        if ($this->driverId) {
            $this->loadExistingData();
        }
    }



    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail || !$userDriverDetail->user) {
            return;
        }

        $user = $userDriverDetail->user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->middle_name = $userDriverDetail->middle_name;
        $this->last_name = $userDriverDetail->last_name;
        $this->phone = $userDriverDetail->phone;
        $this->date_of_birth = $userDriverDetail->date_of_birth ? $userDriverDetail->date_of_birth->format('Y-m-d') : null;
        $this->status = $userDriverDetail->status;
        $this->terms_accepted = $userDriverDetail->terms_accepted;

        // Load custom dates if they exist
        $this->use_custom_dates = $userDriverDetail->use_custom_dates ?? false;
        $this->custom_created_at = $userDriverDetail->custom_created_at ? $userDriverDetail->custom_created_at->format('Y-m-d\TH:i') : null;        

        // Get profile photo URL
        if ($userDriverDetail->hasMedia('profile_photo_driver')) {
            $this->photo_preview_url = $userDriverDetail->getFirstMediaUrl('profile_photo_driver');
        } else {
            $this->photo_preview_url = null;
        }
    }

    // Create a new driver
    protected function createDriver()
    {
        try {
            DB::beginTransaction();            
            // Create user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => $this->status,
            ]);

            // Assign role
            $user->assignRole('user_driver');

            // Create driver detail
            $driverData = [
                'user_id' => $user->id,
                'carrier_id' => $this->carrier->id,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'date_of_birth' => $this->date_of_birth,
                'status' => $this->status,
                'terms_accepted' => $this->terms_accepted,
                'confirmation_token' => Str::random(60),
                'current_step' => 1,
                'use_custom_dates' => $this->use_custom_dates,
            ];
            
            // Add custom dates if enabled
            if ($this->use_custom_dates) {
                if ($this->custom_created_at) {
                    $driverData['custom_created_at'] = $this->custom_created_at;
                }                
            }
            
            $userDriverDetail = UserDriverDetail::create($driverData);

            // Upload photo if provided
            if ($this->photo) {
                $fileName = strtolower(str_replace(' ', '_', $this->name)) . '.webp';
                $userDriverDetail->addMedia($this->photo->getRealPath())
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Create empty application
            $application = \App\Models\Admin\Driver\DriverApplication::create([
                'user_id' => $user->id,
                'status' => 'draft'
            ]);
            
                    // Send notification to admin users
        try {
            // Get carrier
            $carrier = \App\Models\Carrier::find($this->carrier->id);
            
            // Get superadmins and carrier admins to notify
            $superadmins = User::role('superadmin')->get();
            
            // Combine recipients
            $recipients = $superadmins;
            
            // Send notification
            foreach ($recipients as $recipient) {
                $recipient->notify(new \App\Notifications\Admin\Driver\NewDriverRegisteredNotification($user, $carrier));
            }
                        
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending driver notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

            DB::commit();

            // Notify parent component
            $this->driverId = $userDriverDetail->id;
            $this->dispatch('driverCreated', $this->driverId);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error creating driver: ' . $e->getMessage());
            return false;
        }
    }

    public function updatedPhoto()
    {
        // Validar el archivo antes de intentar previsualizarlo
        if ($this->photo) {
            try {
                // Verificar que el archivo temporal existe y es válido
                if (!$this->photo->isValid()) {
                    $this->photo = null;
                    $this->addError('photo', 'El archivo subido no es válido. Por favor, intente nuevamente.');
                    return;
                }

                // Verificar que el archivo temporal existe físicamente
                if (!file_exists($this->photo->getRealPath())) {
                    $this->photo = null;
                    $this->addError('photo', 'El archivo temporal no se encuentra disponible. Por favor, intente subir el archivo nuevamente.');
                    return;
                }

                $extension = $this->photo->getClientOriginalExtension();
                if (empty($extension)) {
                    $this->photo = null;
                    $this->addError('photo', 'El archivo debe tener una extensión válida (jpg, jpeg, png, webp)');
                    return;
                }

                $this->validate(['photo' => 'image|mimes:jpg,jpeg,png,webp|max:10240']);
                
                // Check if image needs compression with additional validation
                try {
                    // Verificar que el archivo existe y obtener su tamaño de forma robusta
                    $filePath = $this->photo->getRealPath();
                    if (!file_exists($filePath)) {
                        throw new \Exception('El archivo temporal no existe');
                    }
                    
                    $fileSize = filesize($filePath);
                    if ($fileSize === false || $fileSize === null || $fileSize === 0) {
                        throw new \Exception('No se pudo obtener el tamaño del archivo');
                    }

                    if (\App\Helpers\ImageCompressionHelper::needsCompression($this->photo)) {
                        $originalSize = \App\Helpers\ImageCompressionHelper::formatFileSize($fileSize);
                        
                        // Compress the image
                        $compressedFile = \App\Helpers\ImageCompressionHelper::compressImage($this->photo);
                        
                        if ($compressedFile) {
                            // Verificar que el archivo comprimido es válido
                            if (file_exists($compressedFile)) {
                                $this->photo = new \Illuminate\Http\UploadedFile(
                                    $compressedFile,
                                    $this->photo->getClientOriginalName(),
                                    $this->photo->getClientMimeType(),
                                    null,
                                    true
                                );
                                
                                $newSize = \App\Helpers\ImageCompressionHelper::formatFileSize(filesize($compressedFile));
                                
                                // Send flash message about compression
                                session()->flash('photo_compressed', "Imagen optimizada automáticamente de {$originalSize} a {$newSize}");
                                
                                // Dispatch compression event
                                $this->dispatch('photo-compressed', [
                                    'original_size' => $originalSize,
                                    'new_size' => $newSize
                                ]);
                            }
                        }
                    }
                } catch (\Exception $compressionError) {
                    // Si falla la compresión, continuar con el archivo original
                    \Illuminate\Support\Facades\Log::warning('Error en compresión de imagen', [
                        'error' => $compressionError->getMessage(),
                        'file' => $this->photo->getClientOriginalName()
                    ]);
                }
                
            } catch (\Exception $e) {
                $this->photo = null;
                $this->addError('photo', 'Error al procesar la imagen: ' . $e->getMessage());
                \Illuminate\Support\Facades\Log::error('Error en updatedPhoto', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    // Update existing driver
    protected function updateDriver()
    {
        try {
            DB::beginTransaction();

            // We'll handle custom dates by modifying created_at after update

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            $user = $userDriverDetail->user;

            // Update user
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            // Update password if provided
            if (!empty($this->password)) {
                $user->update(['password' => Hash::make($this->password)]);
            }

            // Update driver details
            $updateData = [
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'date_of_birth' => $this->date_of_birth,
                'status' => $this->status,
                'terms_accepted' => $this->terms_accepted,
                'use_custom_dates' => $this->use_custom_dates,
            ];
            
            // Add custom dates if enabled
            if ($this->use_custom_dates) {
                $updateData['custom_created_at'] = $this->custom_created_at;                
            } else {
                // Clear custom dates if not using them
                $updateData['custom_created_at'] = null;                
            }
            
            $userDriverDetail->update($updateData);

            // Update photo if provided
            if ($this->photo) {
                $userDriverDetail->clearMediaCollection('profile_photo_driver');
                $fileName = strtolower(str_replace(' ', '_', $this->name)) . '.webp';
                $userDriverDetail->addMedia($this->photo->getRealPath())
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating driver: ' . $e->getMessage());
            return false;
        }
    }

    // Next step
    public function next()
    {
        // Validate data
        $this->validate($this->rules());

        $success = false;

        // If we have a driver ID, update it
        if ($this->driverId) {
            $success = $this->updateDriver();
        } else {
            // Otherwise create a new one
            $success = $this->createDriver();
        }

        // Only proceed to next step if operation was successful
        if ($success) {
            // Move to next step
            $this->dispatch('nextStep');
        } else {
            // Show error message if not already set
            if (!session()->has('error')) {
                session()->flash('error', 'Error al guardar la información del conductor. Por favor, intente nuevamente.');
            }
        }
    }

    // Save and exit
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());

        $success = false;

        // Create or update
        if ($this->driverId) {
            $success = $this->updateDriver();
        } else {
            $success = $this->createDriver();
        }

        // Only dispatch saveAndExit if operation was successful
        if ($success) {
            $this->dispatch('saveAndExit');
        } else {
            // Show error message if not already set
            if (!session()->has('error')) {
                session()->flash('error', 'Error al guardar la información del conductor. Por favor, intente nuevamente.');
            }
        }
    }

    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-general-info-step');
    }
}
