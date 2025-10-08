<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleDriverAssignment;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\User;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\CompanyDriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class VehicleDriverAssignmentController extends Controller
{
    /**
     * Display a listing of vehicle driver assignments
     */
    public function index(Request $request)
    {
        $query = VehicleDriverAssignment::with(['vehicle', 'user', 'assignedBy']);
        
        if ($request->has('type')) {
            $query->where('assignment_type', $request->type);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $assignments = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.vehicle-driver-assignments.index', compact('assignments'));
    }

    /**
     * Display the specified assignment
     */
    public function show(VehicleDriverAssignment $assignment)
    {
        $assignment->load(['vehicle', 'user', 'assignedBy', 'ownerOperatorDetail', 'thirdPartyDetail', 'companyDriverDetail']);
        
        return view('admin.vehicle-driver-assignments.show', compact('assignment'));
    }

    /**
     * Update the specified assignment
     */
    public function update(Request $request, VehicleDriverAssignment $assignment)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'termination_date' => 'nullable|date',
            // Company Driver fields
            'driver_application_id' => 'nullable|exists:driver_applications,id',
            'employee_id' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'salary_type' => 'nullable|in:hourly,salary,commission,per_mile',
            'base_rate' => 'nullable|numeric|min:0|max:999999.99',
            'overtime_rate' => 'nullable|numeric|min:0|max:999999.99',
            'benefits_eligible' => 'nullable|boolean',
            // Owner Operator fields
            'owner_name' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email|max:255',
            'contract_agreed' => 'nullable|boolean',
            // Third Party fields
            'third_party_name' => 'nullable|string|max:255',
            'third_party_phone' => 'nullable|string|max:20',
            'third_party_email' => 'nullable|email|max:255',
            'third_party_dba' => 'nullable|string|max:255',
            'third_party_address' => 'nullable|string|max:500',
            'third_party_contact' => 'nullable|string|max:255',
            'third_party_fein' => 'nullable|string|max:50'
        ]);
        
        // Update assignment fields
        $assignment->update([
            'notes' => $validated['notes'] ?? $assignment->notes,
            'termination_date' => $validated['termination_date'] ?? $assignment->termination_date
        ]);
        
        // Update type-specific details
        switch ($assignment->assignment_type) {
            case 'company_driver':
                if ($assignment->companyDriverDetail) {
                    $assignment->companyDriverDetail->update([
                        'employee_id' => $validated['employee_id'] ?? $assignment->companyDriverDetail->employee_id,
                        'department' => $validated['department'] ?? $assignment->companyDriverDetail->department,
                        'supervisor_name' => $validated['supervisor_name'] ?? $assignment->companyDriverDetail->supervisor_name,
                        'supervisor_phone' => $validated['supervisor_phone'] ?? $assignment->companyDriverDetail->supervisor_phone,
                        'salary_type' => $validated['salary_type'] ?? $assignment->companyDriverDetail->salary_type,
                        'base_rate' => $validated['base_rate'] ?? $assignment->companyDriverDetail->base_rate,
                        'overtime_rate' => $validated['overtime_rate'] ?? $assignment->companyDriverDetail->overtime_rate,
                        'benefits_eligible' => $validated['benefits_eligible'] ?? $assignment->companyDriverDetail->benefits_eligible
                    ]);
                }
                break;
            case 'owner_operator':
                if ($assignment->ownerOperatorDetail) {
                    $assignment->ownerOperatorDetail->update([
                        'owner_name' => $validated['owner_name'] ?? $assignment->ownerOperatorDetail->owner_name,
                        'owner_phone' => $validated['owner_phone'] ?? $assignment->ownerOperatorDetail->owner_phone,
                        'owner_email' => $validated['owner_email'] ?? $assignment->ownerOperatorDetail->owner_email,
                        'contract_agreed' => $validated['contract_agreed'] ?? $assignment->ownerOperatorDetail->contract_agreed
                    ]);
                }
                break;
            case 'third_party':
                if ($assignment->thirdPartyDetail) {
                    $assignment->thirdPartyDetail->update([
                        'third_party_name' => $validated['third_party_name'] ?? $assignment->thirdPartyDetail->third_party_name,
                        'third_party_phone' => $validated['third_party_phone'] ?? $assignment->thirdPartyDetail->third_party_phone,
                        'third_party_email' => $validated['third_party_email'] ?? $assignment->thirdPartyDetail->third_party_email,
                        'third_party_dba' => $validated['third_party_dba'] ?? $assignment->thirdPartyDetail->third_party_dba,
                        'third_party_address' => $validated['third_party_address'] ?? $assignment->thirdPartyDetail->third_party_address,
                        'third_party_contact' => $validated['third_party_contact'] ?? $assignment->thirdPartyDetail->third_party_contact,
                        'third_party_fein' => $validated['third_party_fein'] ?? $assignment->thirdPartyDetail->third_party_fein
                    ]);
                }
                break;
        }
        
        return redirect()->back()->with('success', 'Assignment updated successfully.');
    }

    /**
     * Bulk terminate assignments
     */
    public function bulkTerminate(Request $request)
    {
        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'exists:vehicle_driver_assignments,id',
            'termination_date' => 'required|date',
            'termination_reason' => 'nullable|string|max:500'
        ]);
        
        VehicleDriverAssignment::whereIn('id', $validated['assignment_ids'])
            ->update([
                'status' => 'terminated',
                'termination_date' => $validated['termination_date'],
                'notes' => 'Bulk termination: ' . ($validated['termination_reason'] ?? 'No reason provided')
            ]);
        
        return redirect()->back()->with('success', 'Assignments terminated successfully.');
    }

    /**
     * Store a new vehicle driver assignment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'user_id' => 'required|exists:users,id',
            'assignment_type' => 'required|in:company_driver,owner_operator,third_party',
            'effective_date' => 'required|date',
            'termination_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string|max:1000',
            
            // Owner Operator fields
            'owner_name' => 'required_if:assignment_type,owner_operator|string|max:255',
            'owner_phone' => 'required_if:assignment_type,owner_operator|string|max:20',
            'owner_email' => 'required_if:assignment_type,owner_operator|email|max:255',
            'contract_agreed' => 'required_if:assignment_type,owner_operator|boolean',
            
            // Third Party fields
            'third_party_name' => 'required_if:assignment_type,third_party|string|max:255',
            'third_party_phone' => 'required_if:assignment_type,third_party|string|max:20',
            'third_party_email' => 'required_if:assignment_type,third_party|email|max:255',
            'third_party_dba' => 'nullable|string|max:255',
            'third_party_address' => 'nullable|string|max:500',
            'third_party_contact' => 'nullable|string|max:255',
            'third_party_fein' => 'nullable|string|max:50',
            
            // Company Driver fields
            'employee_id' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'salary_type' => 'nullable|in:hourly,salary,commission,per_mile',
            'base_rate' => 'nullable|numeric|min:0|max:999999.99',
            'overtime_rate' => 'nullable|numeric|min:0|max:999999.99',
            'benefits_eligible' => 'nullable|boolean'
        ]);

        try {
            $assignment = null;

            DB::transaction(function () use ($validated, $request, &$assignment) {
                // Terminate any existing active assignment for this vehicle
                VehicleDriverAssignment::where('vehicle_id', $validated['vehicle_id'])
                    ->where('status', 'active')
                    ->update([
                        'status' => 'terminated',
                        'termination_date' => now()->toDateString()
                    ]);

                // Create new assignment
                $assignment = VehicleDriverAssignment::create([
                    'vehicle_id' => $validated['vehicle_id'],
                    'user_id' => $validated['user_id'],
                    'assignment_type' => $validated['assignment_type'],
                    'status' => 'active',
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'effective_date' => $validated['effective_date'],
                    'notes' => $validated['notes'] ?? null
                ]);

                // Create type-specific details
                $this->createTypeSpecificDetails($assignment, $request);

                Log::info('Vehicle driver assignment created', [
                    'assignment_id' => $assignment->id,
                    'vehicle_id' => $validated['vehicle_id'],
                    'user_id' => $validated['user_id'],
                    'assignment_type' => $validated['assignment_type'],
                    'assigned_by' => auth()->id()
                ]);
            });

            return redirect()->back()->with('success', 'Conductor asignado exitosamente al vehículo.');

        } catch (Exception $e) {
            Log::error('Error creating vehicle driver assignment', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Error al asignar conductor: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove/terminate a driver assignment
     */
    public function destroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'assignment_id' => 'required|exists:vehicle_driver_assignments,id',
                'termination_reason' => 'nullable|string|max:500'
            ]);

            $assignment = VehicleDriverAssignment::findOrFail($validated['assignment_id']);

            $assignment->update([
                'status' => 'terminated',
                'termination_date' => now()->toDateString(),
                'notes' => $assignment->notes . '\n\nTermination reason: ' . ($validated['termination_reason'] ?? 'No reason provided')
            ]);

            Log::info('Vehicle driver assignment terminated', [
                'assignment_id' => $assignment->id,
                'terminated_by' => auth()->id(),
                'reason' => $validated['termination_reason'] ?? 'No reason provided'
            ]);

            return redirect()->back()->with('success', 'Asignación de conductor terminada exitosamente.');

        } catch (Exception $e) {
            Log::error('Error terminating vehicle driver assignment', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Error al terminar asignación: ' . $e->getMessage()]);
        }
    }

    /**
     * Create type-specific details based on assignment type
     */
    private function createTypeSpecificDetails(VehicleDriverAssignment $assignment, Request $request)
    {
        switch ($assignment->assignment_type) {
            case 'owner_operator':
                $assignment->ownerOperatorDetails()->create([
                    'owner_name' => $request->owner_name,
                    'owner_phone' => $request->owner_phone,
                    'owner_email' => $request->owner_email,
                    'contract_agreed' => $request->boolean('contract_agreed'),
                    'vehicle_id' => $assignment->vehicle_id // Keep for backward compatibility
                ]);
                break;
                
            case 'third_party':
                $assignment->thirdPartyDetails()->create([
                    'third_party_name' => $request->third_party_name,
                    'third_party_phone' => $request->third_party_phone,
                    'third_party_email' => $request->third_party_email,
                    'third_party_dba' => $request->third_party_dba,
                    'third_party_address' => $request->third_party_address,
                    'third_party_contact' => $request->third_party_contact,
                    'third_party_fein' => $request->third_party_fein,
                    'vehicle_id' => $assignment->vehicle_id // Keep for backward compatibility
                ]);
                break;
                
            case 'company_driver':
                $assignment->companyDriverDetails()->create([
                    'driver_application_id' => $request->driver_application_id,
                    'employee_id' => $request->employee_id,
                    'department' => $request->department,
                    'supervisor_name' => $request->supervisor_name,
                    'supervisor_phone' => $request->supervisor_phone,
                    'salary_type' => $request->salary_type,
                    'base_rate' => $request->base_rate,
                    'overtime_rate' => $request->overtime_rate,
                    'benefits_eligible' => $request->boolean('benefits_eligible', false)
                ]);
                break;
        }
    }

    /**
     * Get assignment history for a vehicle
     */
    public function history(Vehicle $vehicle)
    {
        $assignments = $vehicle->driverAssignments()
            ->with(['user', 'assignedBy', 'ownerOperatorDetails', 'thirdPartyDetails', 'companyDriverDetails'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.vehicles.assignment-history', compact('vehicle', 'assignments'));
    }

    /**
     * Get active assignments for a user
     */
    public function userAssignments(User $user)
    {
        $assignments = $user->vehicleAssignments()
            ->with(['vehicle', 'assignedBy'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.vehicle-assignments', compact('user', 'assignments'));
    }
}