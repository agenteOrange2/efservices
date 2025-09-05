<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $driverId = $this->route('userDriverDetail')?->id;
        $carrierId = $this->route('carrier')?->id ?? $this->input('carrier_id');

        return [
            // Personal Information
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($driverId ? $this->getDriverUserId($driverId) : null)
            ],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed'
            ],
            'password_confirmation' => [
                $this->filled('password') ? 'required' : 'nullable',
                'string'
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'pending'])],
            'terms_accepted' => ['boolean'],
            'application_completed' => ['boolean'],

            // Address Information
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'address_type' => ['required', Rule::in(['home', 'work', 'other'])],

            // Work Information
            'company_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'supervisor_phone' => ['nullable', 'string', 'max:20'],
            'reason_for_leaving' => ['nullable', 'string', 'max:500'],

            // License Information
            'license_number' => ['required', 'string', 'max:50'],
            'license_class' => ['required', 'string', 'max:10'],
            'license_state' => ['required', 'string', 'max:50'],
            'license_expiration_date' => ['required', 'date', 'after:today'],
            'license_front_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'license_back_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'endorsements' => ['nullable', 'array'],
            'endorsements.*' => ['string', 'max:10'],
            'restrictions' => ['nullable', 'array'],
            'restrictions.*' => ['string', 'max:10'],

            // Vehicle Assignment
            'assigned_vehicle_id' => [
                'nullable',
                'integer',
                Rule::exists('vehicles', 'id')->where(function ($query) use ($carrierId) {
                    return $query->where('carrier_id', $carrierId);
                })
            ],

            // System fields
            'carrier_id' => ['required', 'integer', Rule::exists('carriers', 'id')],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'phone.required' => 'El teléfono es obligatorio.',
            'date_of_birth.required' => 'La fecha de nacimiento es obligatoria.',
            'date_of_birth.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'profile_photo.image' => 'El archivo debe ser una imagen.',
            'profile_photo.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg.',
            'profile_photo.max' => 'La imagen no debe ser mayor a 2MB.',
            'address_line_1.required' => 'La dirección es obligatoria.',
            'city.required' => 'La ciudad es obligatoria.',
            'state.required' => 'El estado es obligatorio.',
            'zip_code.required' => 'El código postal es obligatorio.',
            'country.required' => 'El país es obligatorio.',
            'license_number.required' => 'El número de licencia es obligatorio.',
            'license_class.required' => 'La clase de licencia es obligatoria.',
            'license_state.required' => 'El estado de la licencia es obligatorio.',
            'license_expiration_date.required' => 'La fecha de expiración de la licencia es obligatoria.',
            'license_expiration_date.after' => 'La licencia debe estar vigente.',
            'license_front_photo.image' => 'La foto frontal de la licencia debe ser una imagen.',
            'license_back_photo.image' => 'La foto trasera de la licencia debe ser una imagen.',
            'assigned_vehicle_id.exists' => 'El vehículo seleccionado no es válido.',
            'carrier_id.required' => 'El transportista es obligatorio.',
            'carrier_id.exists' => 'El transportista seleccionado no es válido.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'middle_name' => 'segundo nombre',
            'last_name' => 'apellido',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'date_of_birth' => 'fecha de nacimiento',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'profile_photo' => 'foto de perfil',
            'status' => 'estado',
            'address_line_1' => 'dirección línea 1',
            'address_line_2' => 'dirección línea 2',
            'city' => 'ciudad',
            'state' => 'estado',
            'zip_code' => 'código postal',
            'country' => 'país',
            'address_type' => 'tipo de dirección',
            'company_name' => 'nombre de la empresa',
            'position' => 'posición',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'supervisor_name' => 'nombre del supervisor',
            'supervisor_phone' => 'teléfono del supervisor',
            'reason_for_leaving' => 'razón para dejar el trabajo',
            'license_number' => 'número de licencia',
            'license_class' => 'clase de licencia',
            'license_state' => 'estado de la licencia',
            'license_expiration_date' => 'fecha de expiración de la licencia',
            'license_front_photo' => 'foto frontal de la licencia',
            'license_back_photo' => 'foto trasera de la licencia',
            'assigned_vehicle_id' => 'vehículo asignado',
            'carrier_id' => 'transportista',
        ];
    }

    /**
     * Get the user ID for the driver being updated.
     */
    private function getDriverUserId(int $driverId): ?int
    {
        $driver = \App\Models\UserDriverDetail::find($driverId);
        return $driver?->user_id;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert boolean strings to actual booleans
        $this->merge([
            'terms_accepted' => $this->boolean('terms_accepted'),
            'application_completed' => $this->boolean('application_completed'),
            'is_current' => $this->boolean('is_current'),
        ]);

        // Clean phone number
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^\d+\-\(\)\s]/', '', $this->input('phone'))
            ]);
        }

        // Clean supervisor phone
        if ($this->filled('supervisor_phone')) {
            $this->merge([
                'supervisor_phone' => preg_replace('/[^\d+\-\(\)\s]/', '', $this->input('supervisor_phone'))
            ]);
        }
    }
}