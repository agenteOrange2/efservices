<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TestDateValidationFixed extends Command
{
    protected $signature = 'test:date-validation-fixed';
    protected $description = 'Test date of birth validation with MM/DD/YYYY format';

    public function handle()
    {
        $this->info('Testing date of birth validation with MM/DD/YYYY format...');
        
        // Test date: 09/01/1990 (MM/DD/YYYY format)
        $testDate = '09/01/1990';
        
        $this->info("Testing date: {$testDate}");
        
        // Create validation rules (same as AdminDriverForm)
        $rules = [
            'date_of_birth' => [
                'required',
                'date_format:m/d/Y',
                'before:' . now()->subYears(18)->format('m/d/Y'),
                'after:' . now()->subYears(100)->format('m/d/Y')
            ]
        ];
        
        // Test data
        $data = [
            'date_of_birth' => $testDate
        ];
        
        // Create validator
        $validator = Validator::make($data, $rules);
        
        // Check validation result
        if ($validator->passes()) {
            $this->info('✅ Validation PASSED!');
            
            // Calculate age
            $birthDate = Carbon::createFromFormat('m/d/Y', $testDate);
            $age = $birthDate->age;
            $this->info("Age calculated: {$age} years old");
            
            // Show age limits
            $minAgeDate = now()->subYears(18)->format('m/d/Y');
            $maxAgeDate = now()->subYears(100)->format('m/d/Y');
            $this->info("Minimum age date (18 years): {$minAgeDate}");
            $this->info("Maximum age date (100 years): {$maxAgeDate}");
            
        } else {
            $this->error('❌ Validation FAILED!');
            foreach ($validator->errors()->all() as $error) {
                $this->error("Error: {$error}");
            }
        }
        
        return 0;
    }
}