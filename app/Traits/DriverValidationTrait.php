<?php

namespace App\Traits;

use App\Helpers\DateHelper;
use Illuminate\Validation\Rule;

trait DriverValidationTrait
{
    /**
     * Get common date validation rules
     */
    protected function getDateValidationRules($required = true)
    {
        $rules = ['date'];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get date of birth validation rules (must be 18+)
     */
    protected function getDateOfBirthValidationRules()
    {
        return [
            'required',
            'date',
            'before:' . now()->subYears(18)->format('Y-m-d'),
            'after:' . now()->subYears(100)->format('Y-m-d')
        ];
    }
    
    /**
     * Get expiration date validation rules (must be future date)
     */
    protected function getExpirationDateValidationRules($required = true)
    {
        $rules = [
            'date',
            'after:today'
        ];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get image upload validation rules
     */
    protected function getImageValidationRules($required = true)
    {
        $rules = [
            'image',
            'mimes:jpeg,jpg,png,pdf',
            'max:2048' // 2MB
        ];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get phone number validation rules
     */
    protected function getPhoneValidationRules($required = true)
    {
        $rules = [
            'string',
            'regex:/^[\+]?[1-9]?[0-9]{7,15}$/'
        ];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get email validation rules
     */
    protected function getEmailValidationRules($required = true, $unique = false, $ignoreId = null)
    {
        $rules = ['email'];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        if ($unique) {
            $uniqueRule = Rule::unique('drivers', 'email');
            if ($ignoreId) {
                $uniqueRule->ignore($ignoreId);
            }
            $rules[] = $uniqueRule;
        }
        
        return $rules;
    }
    
    /**
     * Get license number validation rules
     */
    protected function getLicenseNumberValidationRules($required = true)
    {
        $rules = [
            'string',
            'max:50',
            'regex:/^[A-Za-z0-9\-]+$/'
        ];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get state validation rules
     */
    protected function getStateValidationRules($required = true)
    {
        $states = [
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
            'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
            'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
            'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
            'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
            'DC'
        ];
        
        $rules = [Rule::in($states)];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get name validation rules
     */
    protected function getNameValidationRules($required = true)
    {
        $rules = [
            'string',
            'max:100',
            'regex:/^[a-zA-Z\s\-\']+$/'
        ];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get password validation rules
     */
    protected function getPasswordValidationRules($required = true, $confirmed = true)
    {
        $rules = [
            'string',
            'min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&+])[A-Za-z\d@$!%*?&+]+$/'
        ];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        if ($confirmed) {
            $rules[] = 'confirmed';
        }
        
        return $rules;
    }
    
    /**
     * Get years of experience validation rules
     */
    protected function getYearsExperienceValidationRules($required = true)
    {
        $rules = [
            'integer',
            'min:0',
            'max:50'
        ];
        
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        
        return $rules;
    }
    
    /**
     * Get common driver registration validation rules
     */
    protected function getDriverRegistrationRules($step = 'general')
    {
        switch ($step) {
            case 'general':
                return [
                    'name' => $this->getNameValidationRules(),
                    'last_name' => $this->getNameValidationRules(),
                    'email' => $this->getEmailValidationRules(true, true),
                    'phone' => $this->getPhoneValidationRules(),
                    'date_of_birth' => $this->getDateOfBirthValidationRules(),
                    'password' => $this->getPasswordValidationRules(),
                    'terms_accepted' => ['required', 'accepted']
                ];
                
            case 'license':
                return [
                    'current_license_number' => $this->getLicenseNumberValidationRules()
                ];
                
            case 'employment':
                return [
                    'start_date' => $this->getDateValidationRules(),
                    'end_date' => $this->getDateValidationRules(false),
                    'company_name' => ['required', 'string', 'max:255'],
                    'position' => ['required', 'string', 'max:255'],
                    'reason_for_leaving' => ['nullable', 'string', 'max:500']
                ];
                
            default:
                return [];
        }
    }
    
    /**
     * Get validation messages
     */
    protected function getValidationMessages()
    {
        return [
            'date_of_birth.before' => 'You must be at least 18 years old to register.',
            'date_of_birth.after' => 'Please enter a valid date of birth.',
            'expiration_date.after' => 'Expiration date must be in the future.',
            'phone.regex' => 'Please enter a valid phone number.',
            'email.unique' => 'This email address is already registered.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'license_number.regex' => 'License number can only contain letters, numbers, and hyphens.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions to continue.'
        ];
    }
}