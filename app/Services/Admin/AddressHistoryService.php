<?php

namespace App\Services\Admin;

use Carbon\Carbon;

class AddressHistoryService
{
    public function validateAddressHistory($fromDate, $toDate, $previousAddresses)
    {
        $totalMonths = 0;
        $summary = [
            'totalYears' => 0,
            'remainingYears' => 3,
            'currentDuration' => '',
            'currentYears' => 0,
            'isValid' => false,
            'previousAddresses' => $previousAddresses
        ];

        // Calcular meses de la dirección actual
        if ($fromDate) {
            try {
                $from = Carbon::parse($fromDate);
                $to = $toDate ? Carbon::parse($toDate) : Carbon::now();

                // Asegurarse de que la fecha final no sea anterior a la inicial
                if ($to->gt($from)) {
                    $currentMonths = $from->diffInMonths($to);
                    $totalMonths += $currentMonths;
                    $summary['currentDuration'] = $this->formatDuration($currentMonths);
                    $summary['currentYears'] = round($currentMonths / 12, 1);
                }
            } catch (\Exception $e) {
                logger()->error('Error calculating current address duration', [
                    'error' => $e->getMessage(),
                    'from_date' => $fromDate,
                    'to_date' => $toDate
                ]);
            }
        }

        // Procesar direcciones previas
        if (is_array($previousAddresses)) {
            foreach ($previousAddresses as &$address) {
                if (!empty($address['from_date']) && !empty($address['to_date'])) {
                    try {
                        $from = Carbon::parse($address['from_date']);
                        $to = Carbon::parse($address['to_date']);

                        if ($to->gt($from)) {
                            $months = $from->diffInMonths($to);
                            $totalMonths += $months;
                            $address['duration'] = $this->formatDuration($months);
                        }
                    } catch (\Exception $e) {
                        logger()->error('Error calculating previous address duration', [
                            'error' => $e->getMessage(),
                            'address' => $address
                        ]);
                    }
                }
            }
        }

        $totalYears = round($totalMonths / 12, 1);
        $summary['totalYears'] = $totalYears;
        $summary['remainingYears'] = max(0, round(3 - $totalYears, 1));
        $summary['isValid'] = $totalYears >= 3;

        // Debug
        logger()->info('Address History Summary', $summary);

        return $summary;
    }

    private function calculateMonths($fromDate, $toDate = null)
    {
        if (!$fromDate) return 0;

        try {
            $from = Carbon::parse($fromDate);
            $to = $toDate ? Carbon::parse($toDate) : Carbon::now();
            return $from->diffInMonths($to);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function formatDuration($months)
    {
        $years = floor($months / 12);
        $remainingMonths = $months % 12;

        $duration = $years > 0 ? "$years year(s)" : "";
        $duration .= $remainingMonths > 0 ? ($years > 0 ? " and " : "") . "$remainingMonths month(s)" : "";

        return $duration;
    }
}
