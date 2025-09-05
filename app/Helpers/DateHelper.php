<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DateHelper
{
    const DISPLAY_FORMAT = 'm/d/Y'; // MM/DD/YYYY
    const DATABASE_FORMAT = 'Y-m-d'; // YYYY-MM-DD
    const INPUT_FORMATS = [
        'm/d/Y',    // MM/DD/YYYY
        'm-d-Y',    // MM-DD-YYYY  
        'd/m/Y',    // DD/MM/YYYY
        'd-m-Y',    // DD-MM-YYYY
        'Y-m-d',    // YYYY-MM-DD
        'Y/m/d',    // YYYY/MM/DD
    ];
    
    /**
     * Convert any date format to display format (MM/DD/YYYY)
     */
    public static function toDisplay($date)
    {
        if (!$date) return null;
        
        try {
            if ($date instanceof Carbon) {
                return $date->format(self::DISPLAY_FORMAT);
            }
            
            return Carbon::parse($date)->format(self::DISPLAY_FORMAT);
        } catch (\Exception $e) {
            Log::warning('Failed to parse date for display: ' . $date, ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Convert any date format to database format (YYYY-MM-DD)
     */
    public static function toDatabase($date)
    {
        if (empty($date)) {
            return null;
        }

        // Si ya está en formato YYYY-MM-DD, devolverlo tal como está
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        try {
            // Intentar diferentes formatos
            $formats = [
                'Y-m-d',     // 2023-12-31
                'm/d/Y',     // 12/31/2023 (MM/DD/YYYY)
                'd/m/Y',     // 31/12/2023 (DD/MM/YYYY)
                'Y/m/d',     // 2023/12/31
                'd-m-Y',     // 31-12-2023
                'm-d-Y',     // 12-31-2023
                'd-M-Y',     // 31-Dec-2023
                'M d, Y',    // Dec 31, 2023
                'F j, Y',    // December 31, 2023
            ];

            foreach ($formats as $format) {
                $dateObj = \DateTime::createFromFormat($format, $date);
                if ($dateObj && $dateObj->format($format) === $date) {
                    return $dateObj->format('Y-m-d');
                }
            }

            // Si ningún formato funciona, intentar con strtotime
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('DateHelper::toDatabase - Exception occurred', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Validate date format
     */
    public static function isValid($date)
    {
        if (!$date) return false;
        
        try {
            Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get age from date of birth
     */
    public static function getAge($dateOfBirth)
    {
        if (!$dateOfBirth) return null;
        
        try {
            return Carbon::parse($dateOfBirth)->age;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Check if date is at least 18 years old
     */
    public static function isAtLeast18($dateOfBirth)
    {
        $age = self::getAge($dateOfBirth);
        return $age !== null && $age >= 18;
    }
    
    /**
     * Format date for input fields
     */
    public static function formatForInput($date)
    {
        return self::toDisplay($date);
    }
    
    /**
     * Parse date from input and convert to database format
     */
    public static function parseFromInput($date)
    {
        return self::toDatabase($date);
    }
    
    /**
     * Get minimum date for 18+ validation (18 years ago)
     */
    public static function getMinDateFor18Plus()
    {
        return Carbon::now()->subYears(18)->format(self::DISPLAY_FORMAT);
    }
    
    /**
     * Get maximum date for reasonable age validation (100 years ago)
     */
    public static function getMaxDateForAge()
    {
        return Carbon::now()->subYears(100)->format(self::DISPLAY_FORMAT);
    }
}