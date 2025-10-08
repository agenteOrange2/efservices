<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\VehicleDriverAssignment;

class VehicleDriverAssignmentHistory extends Component
{
    public $vehicle;
    public $showHistory = false;
    public $assignments;
    public $currentAssignment;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
        $this->loadAssignments();
    }

    public function loadAssignments()
    {
        // Get current assignment
        $this->currentAssignment = $this->vehicle->driverAssignments()
            ->whereNull('end_date')
            ->with(['user', 'companyDriverDetails', 'ownerOperatorDetails', 'thirdPartyDetails'])
            ->first();

        // Get assignment history (ended assignments)
        $this->assignments = $this->vehicle->driverAssignments()
            ->whereNotNull('end_date')
            ->with(['user', 'companyDriverDetails', 'ownerOperatorDetails', 'thirdPartyDetails'])
            ->orderBy('end_date', 'desc')
            ->get();
    }

    public function toggleHistory()
    {
        $this->showHistory = !$this->showHistory;
    }

    public function removeAssignment($assignmentId)
    {
        try {
            $assignment = VehicleDriverAssignment::findOrFail($assignmentId);
            
            if ($assignment->vehicle_id !== $this->vehicle->id) {
                session()->flash('error', 'Assignment not found for this vehicle.');
                return;
            }

            // End the assignment
            $assignment->update([
                'end_date' => now(),
                'notes' => ($assignment->notes ? $assignment->notes . ' | ' : '') . 'Assignment ended on ' . now()->format('Y-m-d H:i:s')
            ]);

            // Reload assignments
            $this->loadAssignments();
            
            session()->flash('success', 'Driver assignment removed successfully.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error removing assignment: ' . $e->getMessage());
        }
    }

    public function getDriverDisplayName($assignment)
    {
        switch ($assignment->assignment_type) {
            case 'company_driver':
                return $assignment->user ? $assignment->user->name : 'Unknown Driver';
            
            case 'owner_operator':
                if ($assignment->ownerOperatorDetails) {
                    return $assignment->ownerOperatorDetails->first_name . ' ' . $assignment->ownerOperatorDetails->last_name;
                }
                return $assignment->user ? $assignment->user->name : 'Unknown Owner Operator';
            
            case 'third_party':
                if ($assignment->thirdPartyDetails) {
                    return $assignment->thirdPartyDetails->company_name;
                }
                return 'Unknown Third Party';
            
            default:
                return 'Unknown';
        }
    }

    public function getDriverDetails($assignment)
    {
        switch ($assignment->assignment_type) {
            case 'company_driver':
                return [
                    'type' => 'Company Driver',
                    'email' => $assignment->user ? $assignment->user->email : null,
                    'phone' => $assignment->companyDriverDetails ? $assignment->companyDriverDetails->phone : null
                ];
            
            case 'owner_operator':
                $details = $assignment->ownerOperatorDetails;
                return [
                    'type' => 'Owner Operator',
                    'email' => $details ? $details->email : null,
                    'phone' => $details ? $details->phone : null,
                    'license' => $details ? $details->license_number : null
                ];
            
            case 'third_party':
                $details = $assignment->thirdPartyDetails;
                return [
                    'type' => 'Third Party',
                    'email' => $details ? $details->email : null,
                    'phone' => $details ? $details->phone : null,
                    'contact_person' => $details ? $details->contact_person : null
                ];
            
            default:
                return ['type' => 'Unknown'];
        }
    }

    public function render()
    {
        return view('livewire.vehicle-driver-assignment-history');
    }
}