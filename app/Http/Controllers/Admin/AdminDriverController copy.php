<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDriverController extends Controller
{
    /**
     * Show the driver creation form
     */
    public function create(Carrier $carrier)
    {
        return view('admin.user_driver.form.create', compact('carrier'));
    }

    /**
     * Show the driver editing form
     */
    public function edit(Carrier $carrier, $driverId)
    {
        try {
            $driver = UserDriverDetail::with([
                'user',
                'addresses',
                'application',
                'carrier'
            ])->findOrFail($driverId);

            // Verify driver belongs to the carrier
            if ($driver->carrier_id !== $carrier->id) {
                abort(404, 'Driver not found for this carrier');
            }

            return view('admin.user_driver.form.edit', compact('carrier', 'driver'));

        } catch (\Exception $e) {
            Log::error('Error loading driver for edit', [
                'carrier_id' => $carrier->id,
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.carriers.drivers.index', $carrier)
                ->with('error', 'Error al cargar el conductor para edición');
        }
    }

    /**
     * Show specific step for driver creation
     */
    public function createStep(Carrier $carrier, $step = 'personal-info')
    {
        $validSteps = ['personal-info', 'address', 'application-details'];
        
        if (!in_array($step, $validSteps)) {
            abort(404, 'Invalid step');
        }

        return view('admin.user_driver.form.steps.' . str_replace('-', '_', $step), [
            'carrier' => $carrier,
            'step' => $step,
            'mode' => 'create'
        ]);
    }

    /**
     * Show specific step for driver editing
     */
    public function editStep(Carrier $carrier, $driverId, $step = 'personal-info')
    {
        $validSteps = ['personal-info', 'address', 'application-details'];
        
        if (!in_array($step, $validSteps)) {
            abort(404, 'Invalid step');
        }

        try {
            $driver = UserDriverDetail::with([
                'user',
                'addresses',
                'application',
                'carrier'
            ])->findOrFail($driverId);

            // Verify driver belongs to the carrier
            if ($driver->carrier_id !== $carrier->id) {
                abort(404, 'Driver not found for this carrier');
            }

            return view('admin.user_driver.form.steps.' . str_replace('-', '_', $step), [
                'carrier' => $carrier,
                'driver' => $driver,
                'step' => $step,
                'mode' => 'edit'
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading driver step for edit', [
                'carrier_id' => $carrier->id,
                'driver_id' => $driverId,
                'step' => $step,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.carriers.drivers.index', $carrier)
                ->with('error', 'Error al cargar el paso del conductor');
        }
    }

    /**
     * Get US states for dropdowns
     */
    public function getStates()
    {
        $states = [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming'
        ];

        return response()->json($states);
    }
}