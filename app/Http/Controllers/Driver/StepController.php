<?php

namespace App\Http\Controllers\Driver;

use Carbon\Carbon;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverApplication;

class StepController extends Controller
{
    public function showStep($step)
    {
    // Check if user has started registration
    if (!auth()->user() || !auth()->user()->driverDetails) {
        return redirect()->route('login')
            ->with('error', 'Please complete initial registration first.');
    }

    $driver = auth()->user()->driverDetails;

        return view("driver.applications.step{$step}", compact('driver'));
    }

    public function processStep1(Request $request)
    {
        $validated = $request->validate([
            'social_security_number' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        $driver = auth()->user()->driverDetails;
        $driver->application()->updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        $driver->current_step = 2;
        $driver->save();

        return redirect()->route('driver.step', 2);
    }

    public function processStep2(Request $request) 
    {
        $validated = $request->validate([
            'address_line1' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
        ]);

        $driver = auth()->user()->driverDetails;
        $driver->application->addresses()->create($validated);
        
        $driver->current_step = 3;
        $driver->save();

        return redirect()->route('driver.step', 3);
    }

    public function processStep3(Request $request)
    {
        $validated = $request->validate([
            'applying_position' => 'required|string',
            'eligible_to_work' => 'required|boolean',
            'can_speak_english' => 'required|boolean',
        ]);

        $driver = auth()->user()->driverDetails;
        $driver->application->details()->create($validated);
        
        $driver->current_step = 1;
        $driver->application_completed = true;
        $driver->save();

        return redirect()->route('driver.dashboard')
            ->with('success', 'Application completed successfully!');
    }
}
