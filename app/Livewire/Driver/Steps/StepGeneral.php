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
use App\Helpers\DateHelper;
use App\Traits\DriverValidationTrait;

class StepGeneral extends Component
{
    use WithFileUploads, DriverValidationTrait;

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
    public $status = 1; // Default: Active
    public $terms_accepted = false;
    public $photo;
    public $photo_preview_url = null;

    // Modal properties
    public $showCredentialsModal = false;
    public $plainPassword = '';

    /**
     * Convierte una fecha a formato Y-m-d para almacenarla en la base de datos
     * Utiliza el DateHelper unificado
     */
    protected function formatDateForDatabase($date)
    {
        return DateHelper::toDatabase($date);
    }

    /**
     * Convierte una fecha del formato de base de datos a m/d/Y para mostrarla
     * Utiliza el DateHelper unificado
     */
    protected function formatDateForDisplay($date)
    {
        return DateHelper::toDisplay($date);
    }
    
    // Validación para el formulario usando el trait unificado
    protected function rules()
    {
        $baseRules = $this->getDriverRegistrationRules('general');
        
        // Personalizar reglas específicas para este paso
        $baseRules['date_of_birth'] = $this->getDateOfBirthValidationRules();
        $baseRules['photo'] = $this->getImageValidationRules(false); // Foto opcional
        
        // Si es un nuevo registro, requerimos email y password
        if (!$this->driverId) {
            $baseRules['email'] = $this->getEmailValidationRules();
            $baseRules['password'] = $this->getPasswordValidationRules();
        } else {
            $baseRules['email'] = $this->getEmailValidationRules($this->getUserId());
            $baseRules['password'] = $this->getPasswordValidationRules(false); // No requerido para actualización
        }

        return $baseRules;
    }
    
    /**
     * Mensajes de validación personalizados
     */
    protected function messages()
    {
        return $this->getValidationMessages();
    }

    // Este método se dispara cuando se actualiza la foto
    public function updatedPhoto()
    {
        Log::info('=== INICIO updatedPhoto() ===', [
            'photo_exists' => !is_null($this->photo),
            'photo_type' => $this->photo ? get_class($this->photo) : 'null',
            'session_id' => session()->getId()
        ]);
        
        if ($this->photo) {
            try {
                Log::info('Procesando foto subida', [
                    'original_name' => $this->photo->getClientOriginalName(),
                    'size' => $this->photo->getSize(),
                    'mime_type' => $this->photo->getMimeType(),
                    'is_valid' => $this->photo->isValid(),
                    'path' => $this->photo->path()
                ]);

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
                        Log::warning('Archivo rechazado: sin extensión reconocible', ['mime' => $mime]);
                        return;
                    }

                    // Renombrar el archivo con la extensión determinada
                    // Nota: esto no es posible directamente con Livewire,
                    // así que debemos rechazar archivos sin extensión
                    $this->reset('photo');
                    $this->addError('photo', 'Por favor, sube un archivo con extensión (jpg, png, etc.)');
                    Log::warning('Archivo rechazado: sin extensión en nombre', ['mime' => $mime]);
                    return;
                }

                // Validar el tipo de archivo
                $this->validate([
                    'photo' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
                ]);

                Log::info('Foto validada correctamente', ['extension' => $extension]);

                // Guardar archivo temporalmente en el sistema de archivos
                $tempFileName = 'temp_photo_' . uniqid() . '.' . $extension;
                $tempPath = storage_path('app/temp/' . $tempFileName);
                
                Log::info('Preparando guardado temporal', [
                    'temp_filename' => $tempFileName,
                    'temp_path' => $tempPath,
                    'storage_temp_dir' => storage_path('app/temp'),
                    'temp_dir_exists' => file_exists(storage_path('app/temp'))
                ]);
                
                // Crear directorio temporal si no existe
                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                    Log::info('Directorio temporal creado', [
                        'path' => storage_path('app/temp'),
                        'created' => file_exists(storage_path('app/temp'))
                    ]);
                }
                
                // Mover archivo a ubicación temporal
                $storedPath = $this->photo->storeAs('temp', $tempFileName);
                
                Log::info('Foto guardada temporalmente', [
                    'temp_file' => $tempFileName,
                    'temp_path' => $tempPath,
                    'stored_path' => $storedPath,
                    'file_exists' => file_exists($tempPath),
                    'file_size' => file_exists($tempPath) ? filesize($tempPath) : 'N/A'
                ]);

                // Generar URL temporal para previsualización
                $this->photo_preview_url = $this->photo->temporaryUrl();
                
                // Guardar información del archivo temporal en sesión para procesamiento posterior
                session([
                    'temp_photo_file' => $tempFileName,
                    'temp_photo_original_name' => $this->photo->getClientOriginalName(),
                    'temp_photo_extension' => $extension
                ]);
                
                // Disparar evento para que el componente frontend maneje la persistencia
                $this->dispatch('photo-uploaded', [
                    'url' => $this->photo_preview_url,
                    'name' => $this->photo->getClientOriginalName(),
                    'temp_file' => $tempFileName
                ]);
                
            } catch (\Exception $e) {
                $this->reset('photo');
                $this->addError('photo', 'Error al procesar la imagen: ' . $e->getMessage());
                Log::error('Error procesando foto', [
                    'error' => $e->getMessage()
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
            // Usar formato m/d/Y para mostrar en la vista
            $this->date_of_birth = $driver->date_of_birth ? DateHelper::toDisplay($driver->date_of_birth) : null;
            $this->status = $driver->status;
            $this->terms_accepted = $driver->terms_accepted;

            // Verificar si hay un carrier asignado
            if ($driver->carrier_id) {
                $this->carrier = \App\Models\Carrier::find($driver->carrier_id);
                // Si no es independiente y tiene carrier, actualizar bandera
                $this->isIndependent = false;
            } else {
                // Si no tiene carrier_id (null), es independiente
                $this->isIndependent = true;
            }

            // También, intentar cargar la previsualización de la foto de perfil
            if ($driver->hasMedia('profile_photo_driver')) {
                $this->photo_preview_url = $driver->getFirstMediaUrl('profile_photo_driver');
            }

            // Datos de driver cargados correctamente
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

                $user->assignRole('user_driver');

                // Crear detalles del driver - convertir fecha al formato de base de datos
                $driver = $user->driverDetails()->create([
                    'carrier_id' => $this->carrier ? $this->carrier->id : ($this->isIndependent ? null : null),
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'phone' => $this->phone,
                    'date_of_birth' => $this->formatDateForDatabase($this->date_of_birth),
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
        $validatedData = $this->validate();

        try {
            DB::beginTransaction();

            // Verificar si tenemos un carrier válido para registro independiente
            $carrierId = null;
            if ($this->carrier && $this->carrier->id) {
                $carrierId = $this->carrier->id;
            } else {
                if ($this->isIndependent) {
                    // Para registro independiente sin carrier, usar null
                    $carrierId = null;
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

                $user->assignRole('user_driver');

                $formattedDate = $this->formatDateForDatabase($this->date_of_birth);
                
                // Crear detalles del driver con carrier_id validado
                $driver = $user->driverDetails()->create([
                    'carrier_id' => $carrierId,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'phone' => $this->phone,
                    'date_of_birth' => $formattedDate,
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

                // Nota: La foto se procesará después del registro cuando tengamos el ID del driver

                // Restablecer el driverId y despachar evento
                $this->driverId = $driver->id;

                // Autenticar al usuario inmediatamente después de crearlo
                \Illuminate\Support\Facades\Auth::login($user);

                // Emitir evento de driver creado al componente padre
                $this->dispatch('driverCreated', $driver->id);

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

        // Actualizar driver - convertir fecha al formato de base de datos
        $driver->update([
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'date_of_birth' => $this->formatDateForDatabase($this->date_of_birth),
            'status' => $this->status,
            'terms_accepted' => $this->terms_accepted,
            // No actualizamos carrier_id aquí para evitar cambios inesperados
        ]);

        // Nota: La foto se procesará en un paso posterior del registro

        Log::info('Driver actualizado', ['driver_id' => $driver->id]);
    }

    /**
     * Método para manejar eventos de archivos removidos desde el frontend
     */
    public function fileRemoved($fieldName, $index = null)
    {
        if ($fieldName === 'photo') {
            $this->photo = null;
            $this->photo_preview_url = null;
            
            // Limpiar también de la sesión
            session()->forget('step_general_photo_preview_url');
            
            // Disparar evento para limpiar el frontend
            $this->dispatch('photo-removed');
        }
    }

    /**
     * Método para restaurar datos desde sessionStorage
     */
    public function restoreFromSession($data)
    {
        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            $this->date_of_birth = $data['date_of_birth'];
        }
        
        // Para la foto, solo restauramos la URL de previsualización si existe
        if (isset($data['photo_preview_url']) && !empty($data['photo_preview_url'])) {
            $this->photo_preview_url = $data['photo_preview_url'];
        }
    }

    /**
     * Método llamado después de cada actualización de Livewire
     * para mantener la sincronización con el frontend
     */
    public function dehydrate()
    {
        // Disparar evento para sincronizar datos con sessionStorage
        $this->dispatch('sync-form-data', [
            'date_of_birth' => $this->date_of_birth,
            'photo_preview_url' => $this->photo_preview_url,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone
        ]);
    }

    public function render()
    {
        return view('livewire.driver.steps.step-general');
    }
}
