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
            'country_code.required' => 'El código de país es obligatorio.',
            'country_code.in' => 'Solo se permiten cuentas bancarias de Estados Unidos.',
        ];
    }
}
