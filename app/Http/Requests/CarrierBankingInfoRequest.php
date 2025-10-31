<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarrierBankingInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->carrierDetails && auth()->user()->carrierDetails->carrier;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_number' => [
                'required',
                'string',
                'min:8',
                'max:17',
                'regex:/^[0-9]+$/', // Solo números
            ],
            'account_holder_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.]+$/', // Solo letras, espacios, guiones y puntos
            ],
            'banking_routing_number' => [
                'required',
                'string',
                'size:9',
                'regex:/^[0-9]{9}$/', // Exactamente 9 dígitos
            ],
            'zip_code' => [
                'required',
                'string',
                'regex:/^[0-9]{5}(-[0-9]{4})?$/', // Formato ZIP: 12345 o 12345-6789
            ],
            'security_code' => [
                'required',
                'string',
                'min:3',
                'max:4',
                'regex:/^[0-9]{3,4}$/', // 3 o 4 dígitos
            ],
            'country_code' => [
                'required',
                'string',
                'in:US', // Solo Estados Unidos por ahora
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'account_number.required' => 'El número de cuenta es obligatorio.',
            'account_number.regex' => 'El número de cuenta debe contener solo números.',
            'account_number.min' => 'El número de cuenta debe tener al menos 8 dígitos.',
            'account_number.max' => 'El número de cuenta no puede tener más de 17 dígitos.',
            'account_holder_name.required' => 'El nombre del titular es obligatorio.',
            'account_holder_name.regex' => 'El nombre del titular solo puede contener letras, espacios, guiones y puntos.',
            'account_holder_name.min' => 'El nombre del titular debe tener al menos 2 caracteres.',
            'account_holder_name.max' => 'El nombre del titular no puede tener más de 100 caracteres.',
            'banking_routing_number.required' => 'El número de routing bancario es obligatorio.',
            'banking_routing_number.size' => 'El número de routing bancario debe tener exactamente 9 dígitos.',
            'banking_routing_number.regex' => 'El número de routing bancario debe contener solo números.',
            'zip_code.required' => 'El código postal es obligatorio.',
            'zip_code.regex' => 'El código postal debe tener el formato 12345 o 12345-6789.',
            'security_code.required' => 'El código de seguridad es obligatorio.',
            'security_code.min' => 'El código de seguridad debe tener al menos 3 dígitos.',
            'security_code.max' => 'El código de seguridad no puede tener más de 4 dígitos.',
            'security_code.regex' => 'El código de seguridad debe contener solo números.',
            'country_code.required' => 'El código de país es obligatorio.',
            'country_code.in' => 'Solo se permiten cuentas bancarias de Estados Unidos.',
        ];
    }
}
