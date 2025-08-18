<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CarrierBasicInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/' // Solo letras, espacios, guiones y apostrofes
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'not_regex:/\+.*@/' // No permitir emails con + (alias)
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:20',
                'regex:/^[\+]?[1-9]?[0-9]{7,15}$/' // Formato internacional básico
            ],
            'country_code' => [
                'required',
                'string',
                'size:2',
                'in:US,CA,MX' // Países soportados inicialmente
            ],
            'job_position' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'in:Owner,Manager,Dispatcher,Safety Manager,Operations Manager,Other'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'terms_accepted' => [
                'required',
                'accepted'
            ],
            'marketing_consent' => [
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter your full name.',
            'full_name.min' => 'Full name must be at least 2 characters.',
            'full_name.max' => 'Full name cannot exceed 100 characters.',
            'full_name.regex' => 'Full name can only contain letters, spaces, hyphens, and apostrophes.',
            
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered. Please use a different email or sign in.',
            'email.not_regex' => 'Email aliases with + are not allowed.',
            
            'phone.required' => 'Please enter your phone number.',
            'phone.min' => 'Phone number must be at least 10 digits.',
            'phone.max' => 'Phone number cannot exceed 20 digits.',
            'phone.regex' => 'Please enter a valid phone number.',
            
            'country_code.required' => 'Please select your country.',
            'country_code.in' => 'Selected country is not supported yet.',
            
            'job_position.required' => 'Please select your job position.',
            'job_position.in' => 'Please select a valid job position from the list.',
            
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.mixed_case' => 'Password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one symbol.',
            'password.uncompromised' => 'This password has been compromised in a data breach. Please choose a different password.',
            
            'terms_accepted.required' => 'You must accept the terms and conditions to continue.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions to continue.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'full name',
            'email' => 'email address',
            'phone' => 'phone number',
            'country_code' => 'country',
            'job_position' => 'job position',
            'password' => 'password',
            'terms_accepted' => 'terms and conditions',
            'marketing_consent' => 'marketing consent'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'full_name' => trim($this->full_name),
            'phone' => preg_replace('/[^\d+]/', '', $this->phone), // Limpiar formato
            'marketing_consent' => $this->boolean('marketing_consent')
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log validation failures for analytics
        \Illuminate\Support\Facades\Log::info('Carrier registration validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation'])
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Get validated data with additional processing.
     */
    public function getProcessedData(): array
    {
        $validated = $this->validated();
        
        // Formatear teléfono para almacenamiento
        if (isset($validated['phone'])) {
            $validated['phone'] = $this->formatPhoneForStorage($validated['phone'], $validated['country_code']);
        }
        
        // Capitalizar nombre
        if (isset($validated['full_name'])) {
            $validated['full_name'] = $this->formatName($validated['full_name']);
        }
        
        return $validated;
    }

    /**
     * Format phone number for storage.
     */
    private function formatPhoneForStorage(string $phone, string $countryCode): string
    {
        // Remover todos los caracteres no numéricos excepto +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Agregar código de país si no está presente
        if (!str_starts_with($phone, '+')) {
            $countryCodes = [
                'US' => '+1',
                'CA' => '+1',
                'MX' => '+52'
            ];
            
            $phone = ($countryCodes[$countryCode] ?? '+1') . $phone;
        }
        
        return $phone;
    }

    /**
     * Format name for storage.
     */
    private function formatName(string $name): string
    {
        // Capitalizar cada palabra correctamente
        return ucwords(strtolower(trim($name)));
    }
}