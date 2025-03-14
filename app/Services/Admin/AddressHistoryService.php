<?php

namespace App\Services\Admin;

use Carbon\Carbon;

class AddressHistoryService
{
    public function validateAddressHistory($fromDate, $toDate, $previousAddresses)
    {
        // Retornamos estructura básica para mantener compatibilidad
        return [
            'totalYears' => 0,
            'remainingYears' => 3,
            'currentDuration' => '',
            'currentYears' => 0,
            'isValid' => false,
            'previousAddresses' => $previousAddresses
        ];
    }
}
