<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Vehicle;
use App\Models\VehicleDriverAssignment;

class VehicleDriverAssignmentHistory extends Component
{
    public Vehicle $vehicle;
    public $showHistory = false;
    public $currentAssignment;
    public $assignmentHistory;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
        $this->loadAssignments();
    }

    public function loadAssignments()
    {
        // Cargar asignación actual
        $this->currentAssignment = $this->vehicle->currentDriverAssignment;
        
        // Cargar historial de asignaciones (excluyendo la actual)
        $this->assignmentHistory = $this->vehicle->driverAssignments()
            ->where('id', '!=', $this->currentAssignment?->id)
            ->orderBy('effective_date', 'desc')
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
            
            // Verificar que la asignación pertenece a este vehículo
            if ($assignment->vehicle_id !== $this->vehicle->id) {
                session()->flash('error', 'Assignment not found.');
                return;
            }

            // Establecer fecha de fin para la asignación
            $assignment->update([
                'end_date' => now(),
                'is_active' => false
            ]);

            // Recargar asignaciones
            $this->loadAssignments();
            
            session()->flash('success', 'Driver assignment removed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error removing driver assignment: ' . $e->getMessage());
        }
    }

    public function getDriverDisplayName($assignment)
    {
        switch ($assignment->assignment_type) {
            case 'company_driver':
                return $assignment->user?->name ?? 'Unknown Driver';
            case 'owner_operator':
                return $assignment->ownerOperatorDetail?->owner_name ?? 'Unknown Owner Operator';
            case 'third_party':
                return $assignment->thirdPartyDetail?->third_party_name ?? 'Unknown Third Party';
            default:
                return 'Unknown';
        }
    }

    public function getDriverDetails($assignment)
    {
        switch ($assignment->assignment_type) {
            case 'company_driver':
                if ($assignment->user && $assignment->user->userDriverDetail) {
                    return [
                        'phone' => $assignment->user->userDriverDetail->phone,
                        'email' => $assignment->user->email,
                        'profile_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $this->vehicle->carrier->slug,
                            'userDriverDetail' => $assignment->user->userDriverDetail->id
                        ])
                    ];
                }
                break;
            case 'owner_operator':
                if ($assignment->ownerOperatorDetail) {
                    return [
                        'phone' => $assignment->ownerOperatorDetail->owner_phone,
                        'email' => $assignment->ownerOperatorDetail->owner_email,
                        'contract_status' => $assignment->ownerOperatorDetail->contract_agreed ? 'Accepted' : 'Pending'
                    ];
                }
                break;
            case 'third_party':
                if ($assignment->thirdPartyDetail) {
                    return [
                        'phone' => $assignment->thirdPartyDetail->third_party_phone,
                        'email' => $assignment->thirdPartyDetail->third_party_email,
                        'contact' => $assignment->thirdPartyDetail->third_party_contact,
                        'fein' => $assignment->thirdPartyDetail->third_party_fein,
                        'email_sent' => $assignment->thirdPartyDetail->email_sent ? 'Sent' : 'Pending'
                    ];
                }
                break;
        }
        return [];
    }

    public function render()
    {
        return view('livewire.vehicle-driver-assignment-history');
    }
}
