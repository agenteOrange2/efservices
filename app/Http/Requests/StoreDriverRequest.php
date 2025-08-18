<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StoreDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create drivers') || 
               $this->user()->hasRole(['admin', 'carrier']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // User information
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            
            // Driver personal information
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'date_of_birth' => [
                'required',
                'date',
                'before:' . Carbon::now()->subYears(18)->format('Y-m-d'),
                'after:' . Carbon::now()->subYears(80)->format('Y-m-d')
            ],
            'ssn' => [
                'required',
                'string',
                'regex:/^\d{3}-\d{2}-\d{4}$/',
                'unique:user_driver_details,ssn'
            ],
            'phone' => ['required', 'string', 'regex:/^[\+]?[1-9]?[0-9]{7,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'state' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'zip_code' => ['required', 'string', 'regex:/^\d{5}(-\d{4})?$/'],
            
            // License information
            'license_number' => [
                'required',
                'string',
                'max:50',
                'unique:user_driver_details,license_number'
            ],
            'license_state' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'license_expiration' => [
                'required',
                'date',
                'after:today'
            ],
            'license_class' => [
                'required',
                Rule::in(['A', 'B', 'C', 'CDL-A', 'CDL-B', 'CDL-C'])
            ],
            
            // Medical certificate
            'medical_cert_number' => ['nullable', 'string', 'max:50'],
            'medical_cert_expiration' => ['nullable', 'date', 'after:today'],
            
            // Employment information
            'carrier_id' => ['required', 'exists:carriers,id'],
            'hire_date' => ['required', 'date', 'before_or_equal:today'],
            'employment_type' => [
                'required',
                Rule::in(['full_time', 'part_time', 'contractor', 'temporary'])
            ],
            'job_title' => ['required', 'string', 'max:100'],
            'salary' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            
            // Emergency contact
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'regex:/^[\+]?[1-9]?[0-9]{7,15}$/'],
            'emergency_contact_relationship' => ['required', 'string', 'max:100'],
            
            // Status and additional fields
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms_accepted' => ['required', 'accepted']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The full name is required.',
            'name.regex' => 'The name may only contain letters and spaces.',
            'email.unique' => 'This email address is already registered.',
            'password.uncompromised' => 'This password has been compromised in a data breach. Please choose a different password.',
            'first_name.regex' => 'First name may only contain letters and spaces.',
            'last_name.regex' => 'Last name may only contain letters and spaces.',
            'middle_name.regex' => 'Middle name may only contain letters and spaces.',
            'date_of_birth.before' => 'Driver must be at least 18 years old.',
            'date_of_birth.after' => 'Please enter a valid date of birth.',
            'ssn.regex' => 'SSN must be in format XXX-XX-XXXX.',
            'ssn.unique' => 'This SSN is already registered.',
            'phone.regex' => 'Please enter a valid phone number.',
            'city.regex' => 'City name may only contain letters and spaces.',
            'state.regex' => 'State must be a valid 2-letter code.',
            'zip_code.regex' => 'ZIP code must be in format XXXXX or XXXXX-XXXX.',
            'license_number.unique' => 'This license number is already registered.',
            'license_state.regex' => 'License state must be a valid 2-letter code.',
            'license_expiration.after' => 'License must not be expired.',
            'medical_cert_expiration.after' => 'Medical certificate must not be expired.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'salary.numeric' => 'Salary must be a valid number.',
            'salary.max' => 'Salary cannot exceed $999,999.99.',
            'emergency_contact_phone.regex' => 'Please enter a valid emergency contact phone number.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'name' => trim($this->name),
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name),
            'middle_name' => $this->middle_name ? trim($this->middle_name) : null,
            'phone' => preg_replace('/[^\d+]/', '', $this->phone),
            'address' => trim($this->address),
            'city' => trim($this->city),
            'state' => strtoupper(trim($this->state)),
            'zip_code' => trim($this->zip_code),
            'license_number' => strtoupper(trim($this->license_number)),
            'license_state' => strtoupper(trim($this->license_state)),
            'license_class' => strtoupper(trim($this->license_class)),
            'medical_cert_number' => $this->medical_cert_number ? strtoupper(trim($this->medical_cert_number)) : null,
            'job_title' => trim($this->job_title),
            'emergency_contact_name' => trim($this->emergency_contact_name),
            'emergency_contact_phone' => preg_replace('/[^\d+]/', '', $this->emergency_contact_phone),
            'emergency_contact_relationship' => trim($this->emergency_contact_relationship),
            'notes' => $this->notes ? trim($this->notes) : null,
            'status' => $this->status ?? 'pending'
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::warning('Driver creation validation failed', [
            'carrier_id' => $this->carrier_id,
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation', 'ssn']),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent()
        ]);

        parent::failedValidation($validator);
    }
}