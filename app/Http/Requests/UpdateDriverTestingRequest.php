<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Rules\ValidateDocumentFile;
use App\Models\Admin\Driver\DriverTesting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverTestingRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $driverTestingId = $this->route('driverTesting')?->id ?? $this->route('id');

        return [
            'carrier_id' => [
                'required',
                'integer',
                'exists:carriers,id'
            ],
            'user_driver_detail_id' => [
                'required',
                'integer',
                'exists:user_driver_details,id'
            ],
            'test_type' => ['required', 'string', Rule::in(array_keys(DriverTesting::getDrugTestTypes()))],
            'test_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'scheduled_time' => 'required|date_format:Y-m-d\TH:i',
            'location' => [
                'required',
                'string',
                'max:255'
            ],
            'administered_by' => [
                'required',
                'string',
                'max:255'
            ],
            'requester_name' => [
                'required',
                'string',
                'max:255'
            ],
            'test_result' => [
                'nullable',
                Rule::in(['Positive', 'Negative', 'Refusal'])
            ],
            'substances_tested' => [
                'nullable',
                'array'
            ],
            'substances_tested.*' => [
                'string',
                'max:100'
            ],
            'mro' => [
                'required',
                'string',
                'max:255'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'status' => [
                'nullable',
                Rule::in(['Schedule', 'In Progress', 'Completed', 'Cancelled'])
            ],
            // Test Details checkboxes
            'is_random_test' => 'nullable|boolean',
            'is_post_accident_test' => 'nullable|boolean',
            'is_reasonable_suspicion_test' => 'nullable|boolean',
            'is_pre_employment_test' => 'nullable|boolean',
            'is_follow_up_test' => 'nullable|boolean',
            'is_return_to_duty_test' => 'nullable|boolean',
            'is_other_reason_test' => 'nullable|boolean',
            'other_reason_description' => 'nullable|string|max:255'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'carrier_id.required' => 'Please select a carrier.',
            'carrier_id.exists' => 'The selected carrier is invalid.',
            'user_driver_detail_id.required' => 'Please select a driver.',
            'user_driver_detail_id.exists' => 'The selected driver is invalid.',
            'test_type.required' => 'Please select a test type.',
            'test_type.in' => 'The selected test type is invalid.',
            'test_date.required' => 'Test date is required.',
            'test_date.date' => 'Please provide a valid test date.',
            'test_date.before_or_equal' => 'Test date cannot be in the future.',
            'test_time.required' => 'Test time is required.',
            'test_time.date_format' => 'Please provide a valid time format (HH:MM).',
            'collection_site.required' => 'Collection site is required.',
            'collector_name.required' => 'Collector name is required.',
            'specimen_id.required' => 'Specimen ID is required.',
            'specimen_id.unique' => 'This specimen ID has already been used.',
            'test_result.required' => 'Test result is required.',
            'test_result.in' => 'Please select a valid test result.',
            'mro_phone.regex' => 'Please provide a valid phone number.',
            'reason_for_test.required_if' => 'Reason for test is required for this test type.',
            'other_reason_description.required_if' => 'Please provide a description when selecting "Other" as reason.',
            'document_attachments.max' => 'You can upload a maximum of 10 files.',
            'document_attachments.*.file' => 'Each attachment must be a valid file.',
            'document_attachments.*.max' => 'Each file must not exceed 20MB.',
            'document_attachments.*.mimes' => 'Only PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF, and TXT files are allowed.',
            'status.in' => 'Please select a valid status.',
            'completed_at.date' => 'Please provide a valid completion date.',
            'completed_at.before_or_equal' => 'Completion date cannot be in the future.',
            'reviewed_by.exists' => 'The selected reviewer is invalid.',
            'reviewed_at.date' => 'Please provide a valid review date.',
            'reviewed_at.before_or_equal' => 'Review date cannot be in the future.',
            // Test Details validation messages
            'is_random_test.boolean' => 'Random test field must be true or false.',
            'is_post_accident_test.boolean' => 'Post accident test field must be true or false.',
            'is_reasonable_suspicion_test.boolean' => 'Reasonable suspicion test field must be true or false.',
            'is_pre_employment_test.boolean' => 'Pre-employment test field must be true or false.',
            'is_follow_up_test.boolean' => 'Follow up test field must be true or false.',
            'is_return_to_duty_test.boolean' => 'Return to duty test field must be true or false.',
            'is_other_reason_test.boolean' => 'Other reason test field must be true or false.',
            'other_reason_description.string' => 'Other reason description must be text.',
            'other_reason_description.max' => 'Other reason description cannot exceed 255 characters.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'carrier_id' => 'carrier',
            'user_driver_detail_id' => 'driver',
            'test_type' => 'test type',
            'test_date' => 'test date',
            'test_time' => 'test time',
            'collection_site' => 'collection site',
            'collector_name' => 'collector name',
            'specimen_id' => 'specimen ID',
            'test_result' => 'test result',
            'mro_name' => 'MRO name',
            'mro_phone' => 'MRO phone',
            'laboratory_name' => 'laboratory name',
            'laboratory_address' => 'laboratory address',
            'chain_of_custody_number' => 'chain of custody number',
            'reason_for_test' => 'reason for test',
            'other_reason_description' => 'other reason description',
            'document_attachments' => 'document attachments',
            'completed_at' => 'completion date',
            'reviewed_by' => 'reviewer',
            'reviewed_at' => 'review date',
            // Test Details attributes
            'is_random_test' => 'random test',
            'is_post_accident_test' => 'post accident test',
            'is_reasonable_suspicion_test' => 'reasonable suspicion test',
            'is_pre_employment_test' => 'pre-employment test',
            'is_follow_up_test' => 'follow up test',
            'is_return_to_duty_test' => 'return to duty test',
            'is_other_reason_test' => 'other reason test',
            'other_reason_description' => 'other reason description'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir substances_tested de string JSON a array si es necesario
        if ($this->has('substances_tested') && is_string($this->substances_tested)) {
            $this->merge([
                'substances_tested' => json_decode($this->substances_tested, true) ?? []
            ]);
        }

        // Limpiar y formatear el teléfono del MRO
        if ($this->has('mro_phone')) {
            $this->merge([
                'mro_phone' => preg_replace('/[^\d\+]/', '', $this->mro_phone)
            ]);
        }

        // Auto-completar campos de revisión si el status cambia a completed
        if ($this->has('status') && $this->status === 'completed') {
            if (!$this->has('completed_at') || !$this->completed_at) {
                $this->merge(['completed_at' => now()]);
            }
        }

        // Procesar checkboxes de Test Details - convertir a booleanos
        $testDetailsFields = [
            'is_random_test',
            'is_post_accident_test', 
            'is_reasonable_suspicion_test',
            'is_pre_employment_test',
            'is_follow_up_test',
            'is_return_to_duty_test',
            'is_other_reason_test'
        ];

        $processedFields = [];
        foreach ($testDetailsFields as $field) {
            // Si el checkbox está presente en el request, convertir a boolean
            if ($this->has($field)) {
                $processedFields[$field] = (bool) $this->input($field);
            } else {
                // Si no está presente (checkbox no marcado), establecer como false
                $processedFields[$field] = false;
            }
        }

        $this->merge($processedFields);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación personalizada: si el test result es positive, debe tener substances_tested
            if ($this->test_result === 'positive' && empty($this->substances_tested)) {
                $validator->errors()->add('substances_tested', 'Substances tested are required when test result is positive.');
            }

            // Validación personalizada: si hay reviewed_at debe haber reviewed_by
            if ($this->reviewed_at && !$this->reviewed_by) {
                $validator->errors()->add('reviewed_by', 'Reviewer is required when review date is provided.');
            }

            // Validación personalizada: completed_at no puede ser anterior a test_date
            if ($this->completed_at && $this->test_date && $this->completed_at < $this->test_date) {
                $validator->errors()->add('completed_at', 'Completion date cannot be before test date.');
            }
        });
    }
}