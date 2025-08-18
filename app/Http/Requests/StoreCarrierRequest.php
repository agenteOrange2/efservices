<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreCarrierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create carriers') || $this->user()->hasRole('admin');
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
            
            // Carrier company information
            'company_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\.,&-]+$/'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'regex:/^[\+]?[1-9]?[0-9]{7,15}$/'],
            'ein_number' => [
                'required',
                'string',
                'regex:/^\d{2}-\d{7}$/',
                'unique:carriers,ein_number'
            ],
            'dot_number' => [
                'nullable',
                'string',
                'regex:/^\d{1,8}$/',
                'unique:carriers,dot_number'
            ],
            'mc_number' => [
                'nullable',
                'string',
                'regex:/^MC-\d{4,7}$|^\d{4,7}$/',
                'unique:carriers,mc_number'
            ],
            
            // Membership and status
            'id_plan' => ['required', 'exists:memberships,id'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'pending'])],
            'document_status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            
            // Additional fields
            'job_position' => ['required', 'string', 'max:100'],
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
            'company_name.required' => 'The company name is required.',
            'company_name.regex' => 'The company name contains invalid characters.',
            'ein_number.regex' => 'EIN number must be in format XX-XXXXXXX.',
            'ein_number.unique' => 'This EIN number is already registered.',
            'dot_number.regex' => 'DOT number must contain only digits (1-8 characters).',
            'dot_number.unique' => 'This DOT number is already registered.',
            'mc_number.regex' => 'MC number must be in format MC-XXXXXX or XXXXXX.',
            'mc_number.unique' => 'This MC number is already registered.',
            'phone.regex' => 'Please enter a valid phone number.',
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
            'company_name' => trim($this->company_name),
            'address' => trim($this->address),
            'phone' => preg_replace('/[^\d+]/', '', $this->phone),
            'ein_number' => strtoupper(trim($this->ein_number)),
            'dot_number' => $this->dot_number ? trim($this->dot_number) : null,
            'mc_number' => $this->mc_number ? strtoupper(trim($this->mc_number)) : null,
            'job_position' => trim($this->job_position),
            'status' => $this->status ?? 'pending',
            'document_status' => $this->document_status ?? 'pending'
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::warning('Carrier creation validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation']),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent()
        ]);

        parent::failedValidation($validator);
    }
}