<?php

namespace App\Livewire\Admin\Driver;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Carrier;
use Livewire\Component;
use App\Helpers\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Services\Admin\AddressHistoryService;
use App\Models\Admin\Driver\DriverApplication;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class CreateDriver extends Component
{
    use WithFileUploads;

    public Carrier $carrier;
    public $activeTab = 'general';
    public $photo;

    // User fields
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $status = 1;

    // Driver details fields
    public $middle_name;
    public $last_name;
    public $license_number;
    public $state_of_issue;
    public $phone;
    public $date_of_birth;

    // Application fields
    public $social_security_number;


    // Address fields
    public $address_line1;
    public $address_line2;
    public $city;
    public $state;
    public $zip_code;
    public $from_date;
    public $to_date;
    public $usStates;
    public $previous_addresses = [];
    public $lived_three_years = false;

    // Calculados
    public $totalAddressYears = 0;
    public $remainingYearsNeeded = 3;
    public $currentAddressDuration = '';
    public $totalYears = 0;
    public $remainingYears = 3;
    public $isAddressValid = false;


    // Application details fields
    public $driverPositions = [];
    public $referralSources = [];
    public $applying_position;
    public $applying_position_other; // Para "Other" position
    public $applying_location;
    public $eligible_to_work = false;
    public $can_speak_english = false;
    public $has_twic_card = false;
    public $twic_expiration_date = null;
    public $how_did_hear;
    public $how_did_hear_other; // Para "Other" referral source
    public $referral_employee_name;
    public $expected_pay;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'state_of_issue' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'photo' => 'nullable|image|max:2048',
            'applying_position' => 'required|string',
            'applying_position_other' => 'required_if:applying_position,other',
            'applying_location' => 'required|string|max:255',
            'eligible_to_work' => 'required|boolean',
            'twic_expiration_date' => 'required_if:has_twic_card,true|nullable|date',
            'how_did_hear' => 'required|string',
            'how_did_hear_other' => 'required_if:how_did_hear,other',
            'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
            'date_of_birth' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $age = Carbon::parse($value)->age;
                    if ($age < 18) {
                        $fail('You must be at least 18 years old.');
                    }
                }
            ],
        ];
    }

    protected $messages = [
        'date_of_birth.before_or_equal' => 'You must be at least 18 years old.',
    ];

    // Marcamos como nullable, con un valor inicial de null
    protected ?AddressHistoryService $addressHistoryService = null;


    public function mount(Carrier $carrier, AddressHistoryService $addressHistoryService)
    {
        $this->carrier = $carrier;
        $this->addressHistoryService = $addressHistoryService;

        // Cargar las opciones de los selects
        $this->usStates = Constants::usStates();
        $this->driverPositions = Constants::driverPositions();
        $this->referralSources = Constants::referralSources();
    }

    private function validateAddressHistory()
    {
        // Nos aseguramos de que no sea null antes de llamar al método
        if (! $this->addressHistoryService) {
            return false;
        }

        $summary = $this->addressHistoryService->validateAddressHistory(
            $this->from_date,
            $this->to_date,
            $this->previous_addresses
        );

        $this->totalAddressYears = $summary['totalYears'];
        $this->remainingYearsNeeded = $summary['remainingYears'];
        $this->currentAddressDuration = $summary['currentDuration'];
        $this->previous_addresses = $summary['previousAddresses'];

        return $summary['isValid'];
    }


    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }


    public function validateAddressYears()
    {
        $totalYears = $this->calculateAddressYears();

        if ($totalYears < 3 && !$this->lived_three_years) {
            $this->addError('address_years', sprintf('You need %.1f more years of address history', 3 - $totalYears));
            return false;
        }
        return true;
    }

    public function getAddressTimeAttribute($from_date, $to_date = null)
    {
        if (!$from_date) return '';

        $from = Carbon::parse($from_date);
        $to = $to_date ? Carbon::parse($to_date) : Carbon::now();

        return $to_date ?
            'Lived here for ' . $this->formatDuration($from->diff($to)) :
            'Current residence since ' . $from->format('M Y');
    }

    private function formatDuration($interval)
    {
        $parts = [];
        if ($interval->y > 0) $parts[] = $interval->y . ' year(s)';
        if ($interval->m > 0) $parts[] = $interval->m . ' month(s)';
        return implode(' and ', $parts);
    }

    public function addAddress()
    {

        if ($this->isAddressValid || $this->lived_three_years) {
            return;
        }

        // Lógica para añadir una address
        $this->previous_addresses[] = [
            'address_line1' => '',
            'city'          => '',
            'state'         => '',
            'zip_code'      => '',
            'from_date'     => '',
            'to_date'       => '',
            'duration'      => '',
        ];
    }

    // Validate when dates are updated
    public function updatedFromDate()
    {
        if (!$this->addressHistoryService) return;
        $this->calculateTotalYears();
    }
    public function updatedToDate()
    {
        if (!$this->addressHistoryService) return;
        $this->calculateTotalYears();
    }

    public function updatedPreviousAddresses($value, $key)
    {
        if (!$this->addressHistoryService) return;

        if (str_contains($key, 'from_date') || str_contains($key, 'to_date')) {
            $this->calculateTotalYears();
        }
    }

    private function calculateTotalYears()
    {
        if (!$this->from_date) return;

        $summary = $this->addressHistoryService->validateAddressHistory(
            $this->from_date,
            $this->to_date,
            $this->previous_addresses
        );

        // Actualizar todas las propiedades del componente
        $this->totalYears = $summary['totalYears'];
        $this->remainingYears = $summary['remainingYears'];
        $this->currentAddressDuration = $summary['currentDuration'];
        $this->isAddressValid = $summary['isValid'];

        // Debug
        logger()->info('Address Summary', [
            'totalYears' => $this->totalYears,
            'remainingYears' => $this->remainingYears,
            'currentDuration' => $this->currentAddressDuration,
            'isValid' => $this->isAddressValid
        ]);
    }

    public function updatedLivedThreeYears($value)
    {
        if (!$this->addressHistoryService) {
            return;
        }

        if ($value) {
            $summary = $this->validateAddressHistory();

            // Verificar que tengamos un array y la propiedad currentYears
            if (is_array($summary) && isset($summary['currentYears']) && $summary['currentYears'] >= 3) {
                $this->isAddressValid = true;
                $this->totalYears = 3;
                $this->remainingYears = 0;
                $this->previous_addresses = [];
            } else {
                // Si no cumple con los 3 años, revertir
                $this->lived_three_years = false;
                $this->addError('lived_three_years', 'You must have lived at this address for at least 3 years to check this box');
            }
        } else {
            $this->updateAddressHistory();
        }
    }

    private function updateAddressHistory()
    {
        if (!$this->addressHistoryService) {
            return;
        }

        $summary = $this->addressHistoryService->validateAddressHistory(
            $this->from_date,
            $this->to_date,
            $this->previous_addresses
        );

        $this->totalYears = $summary['totalYears'];
        $this->remainingYears = $summary['remainingYears'];
        $this->currentAddressDuration = $summary['currentDuration'];
        $this->isAddressValid = $summary['isValid'];

        // Actualizar automáticamente lived_three_years si la dirección actual califica
        if ($summary['currentYears'] >= 3) {
            $this->lived_three_years = true;
            $this->isAddressValid = true;
            $this->previous_addresses = [];
        }

        if (isset($summary['previousAddresses'])) {
            $this->previous_addresses = $summary['previousAddresses'];
        }
    }

    private function formatAddressDuration($from, $to, $isCurrent = false)
    {
        $years = $from->diffInYears($to);
        $months = $from->copy()->addYears($years)->diffInMonths($to);

        $duration = [];
        if ($years > 0) $duration[] = "{$years} year(s)";
        if ($months > 0) $duration[] = "{$months} month(s)";

        $timeString = implode(' and ', $duration);
        return $isCurrent ? "Current residence ($timeString)" : "Lived here for $timeString";
    }

    public function removeAddress($index)
    {
        unset($this->previous_addresses[$index]);
        $this->previous_addresses = array_values($this->previous_addresses);

        // Revalidar
        $this->validateAddressHistory();
    }

    // Método para manejar el cambio en eligible_to_work
    public function updatedEligibleToWork($value)
    {
        if ($value === false) {
            session()->flash('error', 'According to U.S. law, you must be eligible to work in the United States to continue with this application.');
            return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
        }
    }

    // Método para manejar el cambio en has_twic_card
    public function updatedHasTwicCard($value)
    {
        if (!$value) {
            $this->twic_expiration_date = null;
        }
    }


    // Método para limpiar campos relacionados cuando cambia how_did_hear
    public function updatedHowDidHear($value)
    {
        if ($value !== 'employee_referral') {
            $this->referral_employee_name = null;
        }
        if ($value !== 'other') {
            $this->how_did_hear_other = null;
        }
    }

    // Método para limpiar el campo other cuando cambia applying_position
    public function updatedApplyingPosition($value)
    {
        if ($value !== 'other') {
            $this->applying_position_other = null;
        }
    }
    public function save()
    {

        // Log inicial con todos los datos del formulario
        Log::info('CreateDriver: Iniciando proceso de guardado', [
            'form_data' => [
                'name' => $this->name,
                'email' => $this->email,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'license_number' => $this->license_number,
                'state_of_issue' => $this->state_of_issue,
                'phone' => $this->phone,
                'social_security_number' => $this->social_security_number,
                'date_of_birth' => $this->date_of_birth,
                'has_twic_card' => $this->has_twic_card,
                'twic_expiration_date' => $this->twic_expiration_date,
                'address_data' => [
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip_code' => $this->zip_code,
                    'lived_three_years' => $this->lived_three_years,
                    'previous_addresses' => $this->previous_addresses
                ],
                'application_data' => [
                    'applying_position' => $this->applying_position,
                    'applying_location' => $this->applying_location,
                    'eligible_to_work' => $this->eligible_to_work,
                    'can_speak_english' => $this->can_speak_english,
                    'expected_pay' => $this->expected_pay,
                    'how_did_hear' => $this->how_did_hear, // Agregar el valor real
                    'referral_employee_name' => $this->referral_employee_name
                ],
            ]
        ]);



        try {
            // Log antes de la validación
            Log::info('CreateDriver: Iniciando validación');
            $validated = $this->validate();
            Log::info('CreateDriver: Validación exitosa', ['validated_data' => $validated]);

            // DESPUÉS validar la historia de direcciones
            $isValid = $this->validateAddressHistory();
            Log::info('CreateDriver: Validación de historial de direcciones', [
                'is_valid' => $isValid,
                'lived_three_years' => $this->lived_three_years,
                'total_years' => $this->totalAddressYears,
                'remaining_years' => $this->remainingYearsNeeded
            ]);

            if (!$isValid && !$this->lived_three_years) {
                Log::warning('CreateDriver: Validación de dirección fallida - Se requieren 3 años de historial');
                $this->addError('address_years', 'You need 3 years of address history');
                return;
            }
            // Verificar la validez de las direcciones
            if (!$this->lived_three_years && !$this->isAddressValid) {
                $this->addError('address_history', 'You must provide at least 3 years of address history');
                return;
            }

            try {
                DB::beginTransaction();
                Log::info('CreateDriver: Iniciando transacción DB');

                // Crear usuario
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'status' => $this->status,
                ]);

                Log::info('CreateDriver: Usuario creado', ['user_id' => $user->id]);

                // Asignar rol
                $user->assignRole('driver');
                Log::info('CreateDriver: Rol asignado', ['role' => 'driver']);

                // Crear detalles del conductor
                $driverDetail = $user->driverDetails()->create([
                    'carrier_id' => $this->carrier->id,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'license_number' => $this->license_number,
                    'state_of_issue' => $this->state_of_issue,
                    'phone' => $this->phone,
                    'date_of_birth' => $this->date_of_birth,
                    'status' => $this->status,
                ]);

                Log::info('CreateDriver: Detalles del conductor creados', ['driver_detail_id' => $driverDetail->id]);

                // Manejar la foto si existe
                if ($this->photo) {
                    Log::info('CreateDriver: Procesando foto de perfil');
                    $user->addMedia($this->photo)
                        ->usingFileName(strtolower(str_replace(' ', '_', $user->name)) . '.webp')
                        ->toMediaCollection('profile_photo_driver');
                }

                // Crear aplicación
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'social_security_number' => $this->social_security_number,
                    'status' => 'draft'
                ]);

                Log::info('CreateDriver: Aplicación creada', ['application_id' => $application->id]);

                // Crear dirección
                $address = $application->addresses()->create([
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip_code' => $this->zip_code,
                    'lived_three_years' => $this->lived_three_years,
                    'from_date' => $this->from_date,
                    'to_date' => $this->to_date,
                ]);

                Log::info('CreateDriver: Dirección creada', ['address_id' => $address->id]);

                // Crear detalles de la aplicación
                $applicationDetails = $application->details()->create([
                    'applying_position' => $this->applying_position === 'other'
                        ? $this->applying_position_other
                        : $this->applying_position,
                    'applying_location' => $this->applying_location,
                    'eligible_to_work' => $this->eligible_to_work,
                    'can_speak_english' => $this->can_speak_english,
                    'has_twic_card' => $this->has_twic_card,
                    'twic_expiration_date' => $this->twic_expiration_date,
                    'expected_pay' => $this->expected_pay,
                    'how_did_hear' => $this->how_did_hear === 'other'
                        ? $this->how_did_hear_other
                        : $this->how_did_hear,
                    'referral_employee_name' => $this->how_did_hear === 'employee_referral'
                        ? $this->referral_employee_name
                        : null
                ]);

                Log::info('CreateDriver: Detalles de aplicación creados', ['details_id' => $applicationDetails->id]);

                DB::commit();
                Log::info('CreateDriver: Transacción completada exitosamente');

                session()->flash('success', 'Driver created successfully');
                return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('CreateDriver: Error en la transacción DB', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                session()->flash('error', 'Error creating driver: ' . $e->getMessage());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('CreateDriver: Error de validación', [
                'errors' => $e->validator->errors()->toArray(),
                'failed_rules' => $e->validator->failed()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('CreateDriver: Error general', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.driver.create-driver');
    }
}
