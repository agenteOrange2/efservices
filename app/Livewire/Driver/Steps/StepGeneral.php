<?php

namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use App\Models\Carrier;
use App\Models\UserDriverDetail;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DriverRegistrationCredentials;
use App\Mail\NewDriverNotification;
use App\Models\User;

class StepGeneral extends Component
{
    use WithFileUploads;

    // Propiedades principales
    public $driverId;
    public $isIndependent;
    public $carrier;

    // Campos del formulario
    public $name = '';
    public $email = '';
    public $middle_name = '';
    public $last_name = '';
    public $phone = '';
    public $date_of_birth = '';
    public $password = '';
    public $password_confirmation = '';
    public $status = 2; // Default: Pending
    public $terms_accepted = false;
    public $photo;
    public $photo_preview_url = null;

    // Modal properties
    public $showCredentialsModal = false;
    public $plainPassword = '';

    // Validación para el formulario
    // Validación para el formulario
    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Verificar que la persona sea mayor de 18 años
                    $birthDate = \Carbon\Carbon::parse($value);
                    $minDate = \Carbon\Carbon::now()->subYears(18);

                    if ($birthDate->isAfter($minDate)) {
                        $fail('You must be at least 18 years old to register.');
                    }
                },
            ],
            'terms_accepted' => 'required|accepted',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:1024',
        ];

        // Si es un nuevo registro, requerimos email y password
        if (!$this->driverId) {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['email'] = 'required|email|unique:users,email,' . $this->getUserId();
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }

    // Este método se dispara cuando se actualiza la foto
    public function updatedPhoto()
    {
        if ($this->photo) {
            try {
                // Antes de intentar previsualizar, validar la extensión
                $extension = $this->photo->getClientOriginalExtension();

                if (empty($extension)) {
                    // Si no hay extensión, determinarla a partir del mime type
                    $mime = $this->photo->getMimeType();
                    $extension = $this->getMimeExtension($mime);

                    if (empty($extension)) {
                        // Si no se puede determinar la extensión, rechazar el archivo
                        $this->reset('photo');
                        $this->addError('photo', 'El archivo debe tener una extensión reconocible (jpg, png, etc.)');
                        return;
                    }

                    // Renombrar el archivo con la extensión determinada
                    // Nota: esto no es posible directamente con Livewire,
                    // así que debemos rechazar archivos sin extensión
                    $this->reset('photo');
                    $this->addError('photo', 'Por favor, sube un archivo con extensión (jpg, png, etc.)');
                    return;
                }

                // Validar el tipo de archivo
                $this->validate([
                    'photo' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
                ]);

                // Si llegamos aquí, la foto es válida para previsualización
                $this->photo_preview_url = null;
            } catch (\Exception $e) {
                $this->reset('photo');
                $this->addError('photo', 'Error al procesar la imagen: ' . $e->getMessage());
                Log::error('Error al procesar la imagen', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    private function getMimeExtension($mime)
    {
        $mimeExtensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/svg+xml' => 'svg',
        ];

        if (isset($mimeExtensions[$mime])) {
            return $mimeExtensions[$mime];
        } elseif (strpos($mime, 'image/') === 0) {
            return 'jpg'; // Fallback para imágenes
        }

        return null;
    }

    private function getUserId()
    {
        $driver = UserDriverDetail::find($this->driverId);
        return $driver ? $driver->user_id : null;
    }

    // Inicializar componente
    public function mount($driverId = null, $isIndependent = false, $carrier = null)
    {
        $this->driverId = $driverId;
        $this->isIndependent = $isIndependent;
        $this->carrier = $carrier;

        // Si hay un ID de driver, cargar datos existentes
        if ($this->driverId) {
            $this->loadExistingData();
        }

        // Logging para depuración
        Log::info('StepGeneral montado', [
            'driverId' => $this->driverId,
            'isIndependent' => $this->isIndependent,
            'carrier' => $this->carrier ? $this->carrier->id : null
        ]);
    }

    // Cargar datos existentes
    private function loadExistingData()
    {
        try {
            $driver = UserDriverDetail::with(['user', 'application'])->find($this->driverId);
            if (!$driver) {
                Log::warning('No se pudo cargar el driver', ['id' => $this->driverId]);
                return;
            }

            // Cargar datos del usuario
            if ($driver->user) {
                $this->name = $driver->user->name;
                $this->email = $driver->user->email;
            }

            // Cargar datos del driver
            $this->middle_name = $driver->middle_name;
            $this->last_name = $driver->last_name;
            $this->phone = $driver->phone;
            $this->date_of_birth = $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : null;
            $this->status = $driver->status;
            $this->terms_accepted = $driver->terms_accepted;

            // Verificar si hay un carrier asignado
            if ($driver->carrier_id) {
                $this->carrier = \App\Models\Carrier::find($driver->carrier_id);
                // Si no es independiente y tiene carrier, actualizar bandera
                $this->isIndependent = $driver->carrier_id == 0;
            }

            // También, intentar cargar la previsualización de la foto de perfil
            if ($driver->hasMedia('profile_photo_driver')) {
                $this->photo_preview_url = $driver->getFirstMediaUrl('profile_photo_driver');
            }

            Log::info('Datos de driver cargados', [
                'driver_id' => $driver->id,
                'carrier_id' => $driver->carrier_id,
                'photo_url' => $this->photo_preview_url
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar datos existentes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Método para avanzar al siguiente paso
    public function next()
    {
        Log::info('Método next() llamado en StepGeneral');
        $this->save();
    }

    // Método para guardar y salir
    public function saveAndExit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            if (!$this->driverId) {
                // Crear nuevo usuario con datos mínimos
                $user = \App\Models\User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password || 'password123'),
                ]);

                $user->assignRole('driver');

                // Crear detalles del driver
                $driver = $user->driverDetails()->create([
                    'carrier_id' => $this->carrier ? $this->carrier->id : ($this->isIndependent ? 0 : null),
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'phone' => $this->phone,
                    'date_of_birth' => $this->date_of_birth,
                    'status' => $this->status,
                    'terms_accepted' => $this->terms_accepted,
                    'current_step' => 1,
                    'confirmation_token' => \Illuminate\Support\Str::random(32),
                ]);

                $this->driverId = $driver->id;
                $this->dispatch('driverCreated', $driver->id);
            } else {
                // Actualizar datos existentes
                $this->updateExistingDriver();
            }

            DB::commit();
            session()->flash('success', 'Driver information saved successfully.');
            $this->dispatch('saveAndExit');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en saveAndExit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving driver information: ' . $e->getMessage());
        }
    }

    // Guardar información del driver
    public function save()
    {
        Log::info('Método save() llamado en StepGeneral');
        $validatedData = $this->validate();

        try {
            DB::beginTransaction();

            // Verificar si tenemos un carrier válido para registro independiente
            $carrierId = null;
            if ($this->carrier && $this->carrier->id) {
                $carrierId = $this->carrier->id;
                Log::info('Usando carrier_id del objeto carrier', ['carrier_id' => $carrierId]);
            } else {
                if ($this->isIndependent) {
                    // Para registro independiente sin carrier, usar 0 o algún valor por defecto
                    $carrierId = 0;
                    Log::info('Registro independiente sin carrier, usando valor por defecto');
                } else {
                    throw new \Exception('No carrier ID available and not independent registration');
                }
            }

            // Crear usuario si no existe
            if (!$this->driverId) {

                // Guardar la contraseña en texto plano para el correo
                $this->plainPassword = $this->password;

                $user = \App\Models\User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                ]);

                $user->assignRole('driver');

                // Crear detalles del driver con carrier_id validado
                $driver = $user->driverDetails()->create([
                    'carrier_id' => $carrierId,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'phone' => $this->phone,
                    'date_of_birth' => $this->date_of_birth,
                    'status' => $this->status,
                    'terms_accepted' => $this->terms_accepted,
                    'current_step' => 1,
                    'confirmation_token' => \Illuminate\Support\Str::random(32),
                ]);

                // Crear aplicación vacía para el driver
                \App\Models\Admin\Driver\DriverApplication::create([
                    'user_id' => $user->id,
                    'status' => 'draft'
                ]);

                // Subir foto si existe y es válida
                if ($this->photo && $this->photo->isValid()) {
                    try {
                        // Intentar obtener la extensión del archivo
                        $extension = strtolower($this->photo->getClientOriginalExtension());

                        // Si no hay extensión, intentar determinarla a partir del mime type
                        if (empty($extension)) {
                            $mime = $this->photo->getMimeType();
                            $mimeExtensions = [
                                'image/jpeg' => 'jpg',
                                'image/png' => 'png',
                                'image/gif' => 'gif',
                                'image/webp' => 'webp',
                                'image/bmp' => 'bmp',
                                'image/svg+xml' => 'svg',
                            ];

                            if (isset($mimeExtensions[$mime])) {
                                $extension = $mimeExtensions[$mime];
                            } else if (strpos($mime, 'image/') === 0) {
                                $extension = 'jpg';
                            }
                        }

                        // Si tenemos una extensión válida, guardar el archivo
                        if (!empty($extension)) {
                            $fileName = time() . '.' . $extension;

                            $driver->addMedia($this->photo->getRealPath())
                                ->usingFileName($fileName)
                                ->usingName($user->name)
                                ->toMediaCollection('profile_photo_driver');

                            Log::info('Foto guardada correctamente', [
                                'driver_id' => $driver->id,
                                'extension' => $extension,
                                'file_name' => $fileName
                            ]);
                        } else {
                            Log::warning('No se pudo guardar la foto - sin extensión válida', [
                                'mime' => $this->photo->getMimeType(),
                                'original_name' => $this->photo->getClientOriginalName()
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al guardar la foto', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }

                $this->driverId = $driver->id;

                // Emitir evento de driver creado al componente padre
                $this->dispatch('driverCreated', $driver->id);

                Log::info('Nuevo driver creado', [
                    'driver_id' => $driver->id,
                    'user_id' => $user->id,
                    'carrier_id' => $carrierId
                ]);

                // Después de crear el driver y la aplicación, enviar email con credenciales
                $this->sendCredentialsEmail($user);

                // Mostrar modal con información de credenciales
                $this->showCredentialsModal = true;
            } else {
                // Si ya existe el driver, actualizarlo
                $this->updateExistingDriver();
            }

            DB::commit();
            session()->flash('success', 'Driver information saved successfully.');

            // Ir al siguiente paso
            // $this->dispatch('nextStep');

            if ($this->driverId && !$this->showCredentialsModal) {
                $this->dispatch('nextStep');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardando driver', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'carrier' => $this->carrier ? $this->carrier->id : null,
                'isIndependent' => $this->isIndependent
            ]);
            session()->flash('error', 'Error saving driver information: ' . $e->getMessage());
        }
    }

    private function sendCredentialsEmail($user)
    {
        try {
            $resumeLink = route('login');
            
            Log::info('Iniciando envío de correo de credenciales', [
                'user_id' => $user->id,
                'email' => $user->email,
                'has_password' => !empty($this->plainPassword)
            ]);

            // Crear la instancia del correo
            $mail = new DriverRegistrationCredentials(
                $user->name,
                $user->email,
                $this->plainPassword,
                $resumeLink
            );
            
            // Enviar el correo directamente sin usar la cola
            Mail::to($user->email)->send($mail);

            Log::info('Correo de credenciales enviado correctamente', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            // Enviar notificación a los administradores
            $this->sendAdminNotification($user);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de credenciales', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Envía una notificación por correo electrónico a los administradores
     * cuando se registra un nuevo conductor.
     */
    private function sendAdminNotification($user)
    {
        try {
            // Obtener todos los usuarios con rol de administrador (superadmin)
            $admins = User::whereHas('roles', function($query) {
                $query->where('name', 'superadmin');
            })->get();
            
            if ($admins->isEmpty()) {
                Log::info('No hay administradores para notificar sobre el nuevo conductor');
                return false;
            }
            
            $carrierName = $this->carrier ? $this->carrier->name : 'Independent';
            $carrierId = $this->carrier ? $this->carrier->id : null;
            
            // Crear la instancia del correo de notificación
            $notification = new NewDriverNotification(
                $user->name . ' ' . $user->last_name,
                $user->email,
                $carrierId,
                $carrierName
            );
            
            // Enviar la notificación a cada administrador
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send($notification);
                
                Log::info('Notificación de nuevo conductor enviada al administrador', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'driver_id' => $user->id
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación a los administradores', [
                'driver_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    // Método para continuar después de mostrar el modal
    public function continueToNextStep()
    {
        $this->showCredentialsModal = false;
        $this->dispatch('nextStep');
    }

    // Método para guardar y salir después de mostrar el modal
    public function saveAndExitFromModal()
    {
        $this->showCredentialsModal = false;
        $this->dispatch('saveAndExit');
    }



    // Método separado para actualizar driver existente
    private function updateExistingDriver()
    {
        // Actualizar usuario existente
        $driver = UserDriverDetail::find($this->driverId);
        if (!$driver) {
            throw new \Exception("Driver not found with ID: {$this->driverId}");
        }

        $user = $driver->user;

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        // Actualizar password solo si se proporciona
        if (!empty($this->password)) {
            $user->update([
                'password' => Hash::make($this->password),
            ]);
        }

        // Actualizar driver
        $driver->update([
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'status' => $this->status,
            'terms_accepted' => $this->terms_accepted,
            // No actualizamos carrier_id aquí para evitar cambios inesperados
        ]);

        // Actualizar foto si se carga una nueva
        if ($this->photo && $this->photo->isValid()) {
            $driver->clearMediaCollection('profile_photo_driver');

            $extension = $this->photo->getClientOriginalExtension();
            if (!empty($extension)) {
                $driver->addMedia($this->photo->getRealPath())
                    ->usingFileName(time() . '.' . $extension)
                    ->usingName($user->name)
                    ->toMediaCollection('profile_photo_driver');
            }
        }

        Log::info('Driver actualizado', ['driver_id' => $driver->id]);
    }

    public function render()
    {
        return view('livewire.driver.steps.step-general');
    }
}
