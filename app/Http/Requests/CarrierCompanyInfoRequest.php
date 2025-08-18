<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarrierCompanyInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('user_carrier');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'carrier_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\.\'\'&,]+$/', // Permitir caracteres comunes en nombres de empresas
                Rule::unique('carriers', 'name')
            ],
            'address' => [
                'required',
                'string',
                'min:10',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-\.\'#,]+$/' // Direcciones típicas
            ],
            'state' => [
                'required',
                'string',
                'size:2',
                'in:' . $this->getValidStates()
            ],
            'zipcode' => [
                'required',
                'string',
                'regex:/^\d{5}(-\d{4})?$/' // Formato ZIP US: 12345 o 12345-6789
            ],
            'ein_number' => [
                'required',
                'string',
                'regex:/^\d{2}-\d{7}$/', // Formato EIN: XX-XXXXXXX
                Rule::unique('carriers', 'ein_number')
            ],
            'dot_number' => [
                'nullable',
                'string',
                'regex:/^\d{1,8}$/', // DOT numbers son hasta 8 dígitos
                Rule::unique('carriers', 'dot_number')->whereNotNull('dot_number')
            ],
            'mc_number' => [
                'nullable',
                'string',
                'regex:/^\d{1,8}$/', // MC numbers son hasta 8 dígitos
                Rule::unique('carriers', 'mc_number')->whereNotNull('mc_number')
            ],
            'state_dot_number' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\-]+$/' // Formato variable por estado
            ],
            'ifta_account_number' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\-]+$/' // Formato IFTA variable
            ],
            'membership_id' => [
                'required',
                'integer',
                'exists:memberships,id,status,1'
            ],
            'has_documents' => [
                'required',
                'in:yes,no'
            ],
            'business_type' => [
                'required',
                'string',
                'in:sole_proprietorship,partnership,llc,corporation,other'
            ],
            'years_in_business' => [
                'required',
                'integer',
                'min:0',
                'max:100'
            ],
            'fleet_size' => [
                'required',
                'integer',
                'min:1',
                'max:10000'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'carrier_name.required' => 'Please enter your company name.',
            'carrier_name.min' => 'Company name must be at least 2 characters.',
            'carrier_name.max' => 'Company name cannot exceed 255 characters.',
            'carrier_name.regex' => 'Company name contains invalid characters.',
            'carrier_name.unique' => 'This company name is already registered. Please use a different name.',
            
            'address.required' => 'Please enter your company address.',
            'address.min' => 'Address must be at least 10 characters.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'address.regex' => 'Address contains invalid characters.',
            
            'state.required' => 'Please select your state.',
            'state.in' => 'Please select a valid US state.',
            
            'zipcode.required' => 'Please enter your ZIP code.',
            'zipcode.regex' => 'Please enter a valid ZIP code (e.g., 12345 or 12345-6789).',
            
            'ein_number.required' => 'Please enter your EIN number.',
            'ein_number.regex' => 'Please enter a valid EIN number (format: XX-XXXXXXX).',
            'ein_number.unique' => 'This EIN number is already registered.',
            
            'dot_number.regex' => 'Please enter a valid DOT number (up to 8 digits).',
            'dot_number.unique' => 'This DOT number is already registered.',
            
            'mc_number.regex' => 'Please enter a valid MC number (up to 8 digits).',
            'mc_number.unique' => 'This MC number is already registered.',
            
            'state_dot_number.regex' => 'Please enter a valid State DOT number.',
            'ifta_account_number.regex' => 'Please enter a valid IFTA account number.',
            
            'membership_id.required' => 'Please select a membership plan.',
            'membership_id.exists' => 'Selected membership plan is not available.',
            
            'has_documents.required' => 'Please indicate if you have documents ready to upload.',
            'has_documents.in' => 'Please select yes or no for document readiness.',
            
            'business_type.required' => 'Please select your business type.',
            'business_type.in' => 'Please select a valid business type.',
            
            'years_in_business.required' => 'Please enter years in business.',
            'years_in_business.min' => 'Years in business cannot be negative.',
            'years_in_business.max' => 'Years in business seems too high.',
            
            'fleet_size.required' => 'Please enter your fleet size.',
            'fleet_size.min' => 'Fleet size must be at least 1 vehicle.',
            'fleet_size.max' => 'Fleet size seems too large. Please contact support for large fleets.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'carrier_name' => 'company name',
            'address' => 'address',
            'state' => 'state',
            'zipcode' => 'ZIP code',
            'ein_number' => 'EIN number',
            'dot_number' => 'DOT number',
            'mc_number' => 'MC number',
            'state_dot_number' => 'State DOT number',
            'ifta_account_number' => 'IFTA account number',
            'membership_id' => 'membership plan',
            'has_documents' => 'document readiness',
            'business_type' => 'business type',
            'years_in_business' => 'years in business',
            'fleet_size' => 'fleet size'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'carrier_name' => trim($this->carrier_name),
            'address' => trim($this->address),
            'state' => strtoupper(trim($this->state ?? '')),
            'zipcode' => preg_replace('/[^\d-]/', '', $this->zipcode ?? ''),
            'ein_number' => $this->formatEIN($this->ein_number ?? ''),
            'dot_number' => $this->cleanNumericField($this->dot_number),
            'mc_number' => $this->cleanNumericField($this->mc_number),
            'state_dot_number' => $this->cleanAlphanumericField($this->state_dot_number),
            'ifta_account_number' => $this->cleanAlphanumericField($this->ifta_account_number),
            'years_in_business' => $this->years_in_business ? (int) $this->years_in_business : null,
            'fleet_size' => $this->fleet_size ? (int) $this->fleet_size : null
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::info('Carrier company info validation failed', [
            'user_id' => auth()->id(),
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['ein_number']) // No loggear EIN por seguridad
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Get processed data for carrier creation.
     */
    public function getProcessedData(): array
    {
        $validated = $this->validated();
        
        // Generar slug único para el carrier
        $validated['slug'] = $this->generateUniqueSlug($validated['carrier_name']);
        
        // Mapear campos al modelo Carrier
        return [
            'company_name' => $validated['carrier_name'],
            'address' => $validated['address'],
            'state' => $validated['state'],
            'zipcode' => $validated['zipcode'],
            'ein_number' => $validated['ein_number'],
            'dot_number' => $validated['dot_number'],
            'mc_number' => $validated['mc_number'],
            'state_dot_number' => $validated['state_dot_number'],
            'ifta_account_number' => $validated['ifta_account_number'],
            'membership_id' => $validated['membership_id'],
            'business_type' => $validated['business_type'],
            'years_in_business' => $validated['years_in_business'],
            'fleet_size' => $validated['fleet_size'],
            'slug' => $validated['slug'],
            'status' => 'pending',
            'document_status' => $validated['has_documents'] === 'yes' ? 'in_progress' : 'pending'
        ];
    }

    /**
     * Get valid US states.
     */
    private function getValidStates(): string
    {
        $states = [
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
            'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
            'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
            'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
            'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
            'DC' // District of Columbia
        ];
        
        return implode(',', $states);
    }

    /**
     * Format EIN number.
     */
    private function formatEIN(?string $ein): string
    {
        if (!$ein) return '';
        
        $ein = preg_replace('/[^\d]/', '', $ein);
        
        if (strlen($ein) === 9) {
            return substr($ein, 0, 2) . '-' . substr($ein, 2);
        }
        
        return $ein;
    }

    /**
     * Clean numeric field.
     */
    private function cleanNumericField(?string $value): ?string
    {
        if (!$value) return null;
        
        return preg_replace('/[^\d]/', '', $value) ?: null;
    }

    /**
     * Clean alphanumeric field.
     */
    private function cleanAlphanumericField(?string $value): ?string
    {
        if (!$value) return null;
        
        return preg_replace('/[^a-zA-Z0-9\-]/', '', $value) ?: null;
    }

    /**
     * Generate unique slug for carrier.
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (\App\Models\Carrier::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}