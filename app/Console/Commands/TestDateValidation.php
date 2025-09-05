<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TestDateValidation extends Command
{
    protected $signature = 'test:date-validation {date=09/01/1990}';
    protected $description = 'Test date of birth validation';

    public function handle()
    {
        $testDate = $this->argument('date');
        $this->info("Testing date: $testDate");

        // Convert the date using the same logic as AdminDriverForm
        $convertedDate = $this->convertDateOfBirthForValidation($testDate);
        $this->info("Converted date: $convertedDate");

        // Calculate age limits
        $minAgeDate = now()->subYears(18)->format('Y-m-d');
        $maxAgeDate = now()->subYears(100)->format('Y-m-d');

        $this->info("Minimum age date (18 years ago): $minAgeDate");
        $this->info("Maximum age date (100 years ago): $maxAgeDate");

        // Test validation rules
        $rules = [
            'date_of_birth' => [
                'required',
                'date',
                'before:' . $minAgeDate,
                'after:' . $maxAgeDate
            ]
        ];

        $data = ['date_of_birth' => $convertedDate];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->error("Validation FAILED:");
            foreach ($validator->errors()->all() as $error) {
                $this->error("- $error");
            }
        } else {
            $this->info("Validation PASSED! ✅");
        }

        // Calculate actual age
        $birthDate = Carbon::parse($convertedDate);
        $age = $birthDate->diffInYears(now());
        $this->info("Actual age: $age years");
        $this->info("Is older than 18? " . ($age >= 18 ? 'YES ✅' : 'NO ❌'));
        $this->info("Date is before $minAgeDate? " . ($convertedDate < $minAgeDate ? 'YES ✅' : 'NO ❌'));

        return 0;
    }

    private function convertDateOfBirthForValidation($dateOfBirth)
    {
        if ($dateOfBirth) {
            try {
                // Try to parse MM/DD/YYYY format
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateOfBirth, $matches)) {
                    $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year = $matches[3];
                    return "$year-$month-$day";
                }
                
                // If already in Y-m-d format, return as is
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)) {
                    return $dateOfBirth;
                }
            } catch (\Exception $e) {
                // Return original value if conversion fails
                return $dateOfBirth;
            }
        }
        
        return $dateOfBirth;
    }
}