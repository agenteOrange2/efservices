<?php

namespace App\Helpers;

class Constants
{
    public static function usStates()
    {
        return [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District Of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];    
    }

    public static function driverPositions()
    {
        return [
            'driver' => 'Company Driver',
            'owner_operator' => 'Owner Operator',
            'third_party_driver' => 'Third Party Company Driver',
            'other' => 'Other'
        ];
    }

    public static function referralSources()
    {
        return [
            'employee_referral' => 'Employee Referral',
            'recruiter' => 'Recruiter',
            'job_board' => 'Job Board',
            'other' => 'Other'
        ];
    }

    /**
     * Vehicle ownership types for admin panel
     */
    public static function ownershipTypes()
    {
        return [
            'unassigned' => 'Unassigned',
            'leased' => 'Company Driver',
            'owned' => 'Owner Operator',
            'third_party' => 'Third Party Company Driver'
        ];
    }

    /**
     * Map applying_position to ownership_type
     */
    public static function mapApplyingPositionToOwnership($applyingPosition)
    {
        $mapping = [
            'driver' => 'leased',
            'owner_operator' => 'owned',
            'third_party_driver' => 'third_party',
            'other' => 'unassigned'
        ];

        return $mapping[$applyingPosition] ?? 'unassigned';
    }

    /**
     * Map ownership_type to applying_position
     */
    public static function mapOwnershipToApplyingPosition($ownershipType)
    {
        $mapping = [
            'leased' => 'driver',
            'owned' => 'owner_operator',
            'third_party' => 'third_party_driver',
            'unassigned' => 'other'
        ];

        return $mapping[$ownershipType] ?? 'other';
    }

    /**
     * Get standardized mapping between both systems
     */
    public static function getPositionOwnershipMapping()
    {
        return [
            'driver' => [
                'ownership_type' => 'leased',
                'label' => 'Company Driver'
            ],
            'owner_operator' => [
                'ownership_type' => 'owned',
                'label' => 'Owner Operator'
            ],
            'third_party_driver' => [
                'ownership_type' => 'third_party',
                'label' => 'Third Party Company Driver'
            ],
            'other' => [
                'ownership_type' => 'unassigned',
                'label' => 'Other/Unassigned'
            ]
        ];
    }

}