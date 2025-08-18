<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UpdateCarrierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update carriers') || $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $carrierId = $this->route('carrier')->id ?? $this->route('id');
        $userId = $this->route('carrier')->user_id ?? null;

        return [
            // User information (optional for updates)
            'name' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => [
                'sometimes',
                'nullable',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            
            // Carrier company information
            'company_name' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\.,&-]+$/'],
            'address' => ['sometimes', 'required', 'string', 'max:500'],
            'phone' => ['sometimes', 'required', 'string', 'regex:/^[\+]?[1-9]?[0-9]{7,15}$/'],
            'ein_number' => [
                'sometimes',
                'required',
                'string',
                'regex:/^\d{2}-\d{7}$/',
                Rule::unique('carriers', 'ein_number')->ignore($carrierId)
            ],
            'dot_number' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^\d{1,8}$/',
                Rule::unique('carriers', 'dot_number')->ignore($carrierId)
            ],
            'mc_number' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^MC-\d{4,7}$|^\d{4,7}$/',
                Rule::unique('carriers', 'mc_number')->ignore($carrierId)
            ],
            
            // Membership and status
            'id_plan' => ['sometimes', 'required', 'exists:memberships,id'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'document_status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'under_review'])],
            
            // Additional fields
            'job_position' => ['sometimes', 'required', 'string', 'max:100'],
            
            // Admin-only fields
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'verification_date' => ['sometimes', 'nullable', 'date'],
            'suspension_reason' => ['sometimes', 'nullable', 'string', 'max:500']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters and spaces.',
            'email.unique' => 'This email address is already registered.',
            'password.uncompromised' => 'This password has been compromised in a data breach. Please choose a different password.',
            'company_name.regex' => 'The company name contains invalid characters.',
            'ein_number.regex' => 'EIN number must be in format XX-XXXXXXX.',
            'ein_number.unique' => 'This EIN number is already registered.',
            'dot_number.regex' => 'DOT number must contain only digits (1-8 characters).',
            'dot_number.unique' => 'This DOT number is already registered.',
            'mc_number.regex' => 'MC number must be in format MC-XXXXXX or XXXXXX.',
            'mc_number.unique' => 'This MC number is already registered.',
            'phone.regex' => 'Please enter a valid phone number.',
            'admin_notes.max' => 'Admin notes cannot exceed 1000 characters.',
            'suspension_reason.max' => 'Suspension reason cannot exceed 500 characters.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];
        
        if ($this->has('email')) {
            $data['email'] = strtolower(trim($this->email));
        }
        
        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }
        
        if ($this->has('company_name')) {
            $data['company_name'] = trim($this->company_name);
        }
        
        if ($this->has('address')) {
            $data['address'] = trim($this->address);
        }
        
        if ($this->has('phone')) {
            $data['phone'] = preg_replace('/[^\d+]/', '', $this->phone);
        }
        
        if ($this->has('ein_number')) {
            $data['ein_number'] = strtoupper(trim($this->ein_number));
        }
        
        if ($this->has('dot_number')) {
            $data['dot_number'] = $this->dot_number ? trim($this->dot_number) : null;
        }
        
        if ($this->has('mc_number')) {
            $data['mc_number'] = $this->mc_number ? strtoupper(trim($this->mc_number)) : null;
        }
        
        if ($this->has('job_position')) {
            $data['job_position'] = trim($this->job_position);
        }
        
        if ($this->has('admin_notes')) {
            $data['admin_notes'] = $this->admin_notes ? trim($this->admin_notes) : null;
        }
        
        if ($this->has('suspension_reason')) {
            $data['suspension_reason'] = $this->suspension_reason ? trim($this->suspension_reason) : null;
        }
        
        $this->merge($data);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::warning('Carrier update validation failed', [
            'carrier_id' => $this->route('carrier')->id ?? $this->route('id'),
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation']),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent()
        ]);

        parent::failedValidation($validator);
    }
}