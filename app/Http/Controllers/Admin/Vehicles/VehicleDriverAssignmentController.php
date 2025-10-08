<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleDriverAssignment;
use App\Models\UserDriverDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleDriverAssignmentController extends Controller
{
    /**
     * Show the driver assignment form for a vehicle
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['currentDriverAssignment.user', 'assignmentHistory.user']);
        
        return view('admin.vehicles.driver-assignment.show', compact('vehicle'));
    }

    /**
     * Show the form for assigning a company driver
     */
    public function createCompanyDriver(Vehicle $vehicle)
    {
        // Get available drivers for the vehicle's carrier
        $availableDrivers = UserDriverDetail::with('user')
            ->where('carrier_id', $vehicle->carrier_id)
            ->where('status', 1)
            ->whereDoesntHave('currentVehicleAssignment')
            ->get();

        return view('admin.vehicles.driver-assignment.company-driver', compact('vehicle', 'availableDrivers'));
    }

    /**
     * Assign a company driver to a vehicle
     */
    public function storeCompanyDriver(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // End any current assignment
            $this->endCurrentAssignment($vehicle);

            // Create new assignment
            $assignment = VehicleDriverAssignment::create([
                'vehicle_id' => $vehicle->id,
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'start_date' => $request->assignment_date ?? now(),
                'notes' => $request->notes,
                'status' => 'active'
            ]);

            DB::commit();

            Log::info('Company driver assigned to vehicle', [
                'vehicle_id' => $vehicle->id,
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'assignment_id' => $assignment->id
            ]);

            return redirect()->route('admin.vehicles.show', $vehicle)
                ->with('success', 'Company driver assigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning company driver', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error assigning driver. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for assigning an owner operator
     */
    public function createOwnerOperator(Vehicle $vehicle)
    {
        return view('admin.vehicles.driver-assignment.owner-operator', compact('vehicle'));
    }

    /**
     * Assign an owner operator to a vehicle
     */
    public function storeOwnerOperator(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'required|string|max:20',
            'owner_email' => 'required|email|max:255',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // End any current assignment
            $this->endCurrentAssignment($vehicle);

            // Create new assignment
            $assignment = VehicleDriverAssignment::create([
                'vehicle_id' => $vehicle->id,
                'start_date' => $request->assignment_date ?? now(),
                'notes' => $request->notes,
                'status' => 'active'
            ]);

            DB::commit();

            Log::info('Owner operator assigned to vehicle', [
                'vehicle_id' => $vehicle->id,
                'owner_name' => $request->owner_name,
                'assignment_id' => $assignment->id
            ]);

            return redirect()->route('admin.vehicles.show', $vehicle)
                ->with('success', 'Owner operator assigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning owner operator', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error assigning owner operator. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for assigning a third party driver
     */
    public function createThirdParty(Vehicle $vehicle)
    {
        return view('admin.vehicles.driver-assignment.third-party', compact('vehicle'));
    }

    /**
     * Assign a third party driver to a vehicle
     */
    public function storeThirdParty(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'third_party_name' => 'required|string|max:255',
            'third_party_phone' => 'required|string|max:20',
            'third_party_email' => 'required|email|max:255',
            'third_party_company' => 'nullable|string|max:255',
            'third_party_address' => 'nullable|string|max:500',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // End any current assignment
            $this->endCurrentAssignment($vehicle);

            // Create new assignment
            $assignment = VehicleDriverAssignment::create([
                'vehicle_id' => $vehicle->id,
                'start_date' => $request->assignment_date ?? now(),
                'notes' => $request->notes,
                'status' => 'active'
            ]);

            DB::commit();

            Log::info('Third party driver assigned to vehicle', [
                'vehicle_id' => $vehicle->id,
                'third_party_name' => $request->third_party_name,
                'assignment_id' => $assignment->id
            ]);

            return redirect()->route('admin.vehicles.show', $vehicle)
                ->with('success', 'Third party driver assigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning third party driver', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error assigning third party driver. Please try again.')
                ->withInput();
        }
    }

    /**
     * Unassign the current driver from a vehicle
     */
    public function unassign(Vehicle $vehicle)
    {
        try {
            DB::beginTransaction();

            $currentAssignment = $this->endCurrentAssignment($vehicle);

            if (!$currentAssignment) {
                return redirect()->back()
                    ->with('warning', 'No active driver assignment found for this vehicle.');
            }

            DB::commit();

            Log::info('Driver unassigned from vehicle', [
                'vehicle_id' => $vehicle->id,
                'assignment_id' => $currentAssignment->id
            ]);

            return redirect()->route('admin.vehicles.show', $vehicle)
                ->with('success', 'Driver unassigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error unassigning driver', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error unassigning driver. Please try again.');
        }
    }

    /**
     * Get assignment history for a vehicle (AJAX)
     */
    public function getAssignmentHistory(Vehicle $vehicle)
    {
        $assignments = VehicleDriverAssignment::with('user')
            ->where('vehicle_id', $vehicle->id)
            ->orderBy('assignment_date', 'desc')
            ->get();

        return response()->json([
            'assignments' => $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'driver_type' => $assignment->driver_type,
                    'driver_name' => $assignment->getDriverName(),
                    'assignment_date' => $assignment->assignment_date->format('Y-m-d'),
                    'unassignment_date' => $assignment->unassignment_date ? $assignment->unassignment_date->format('Y-m-d') : null,
                    'status' => $assignment->status,
                    'notes' => $assignment->notes
                ];
            })
        ]);
    }

    /**
     * End the current active assignment for a vehicle
     */
    private function endCurrentAssignment(Vehicle $vehicle)
    {
        $currentAssignment = VehicleDriverAssignment::where('vehicle_id', $vehicle->id)
            ->where('status', 'active')
            ->first();

        if ($currentAssignment) {
            $currentAssignment->update([
                'status' => 'inactive',
                'unassignment_date' => now(),
                'unassigned_by' => auth()->id()
            ]);

            return $currentAssignment;
        }

        return null;
    }
}