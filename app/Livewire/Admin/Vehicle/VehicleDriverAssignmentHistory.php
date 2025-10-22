<?php

namespace App\Livewire\Admin\Vehicle;

use Livewire\Component;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleDriverAssignment;

class VehicleDriverAssignmentHistory extends Component
{
    public $vehicle;
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
        // Cargar asignación actual con relaciones
        $this->currentAssignment = $this->vehicle->currentDriverAssignment()
            ->with(['user.driverDetail', 'ownerOperatorDetail', 'thirdPartyDetail'])
            ->first();
        
        // Cargar historial de asignaciones (excluyendo la actual) con relaciones
        $this->assignmentHistory = $this->vehicle->driverAssignments()
            ->with(['user.driverDetail', 'ownerOperatorDetail', 'thirdPartyDetail'])
            ->where('id', '!=', $this->currentAssignment?->id)
            ->orderBy('start_date', 'desc')
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
                session()->flash('driver_assignment_error', 'Assignment not found.');
                return;
            }

            // Establecer fecha de fin para la asignación
            $assignment->update([
                'end_date' => now(),
                'status' => 'inactive'
            ]);

            // Recargar asignaciones
            $this->loadAssignments();
            
            session()->flash('driver_assignment_success', 'Driver assignment removed successfully.');
        } catch (\Exception $e) {
            session()->flash('driver_assignment_error', 'Error removing driver assignment: ' . $e->getMessage());
        }
    }

    public function getDriverDisplayName($assignment)
    {
        switch ($assignment->driver_type) {
            case 'company_driver':
                return $assignment->user?->name ?? 'Unknown Driver';
            case 'owner_operator':
                return $assignment->ownerOperatorDetail?->owner_name ?? 'Unknown Owner Operator';
            case 'third_party':
                $thirdPartyName = $assignment->thirdPartyDetail?->third_party_name ?? 'Unknown Third Party';
                $driverName = $assignment->user?->name ?? null;
                
                if ($driverName) {
                    return $driverName . ' (' . $thirdPartyName . ')';
                }
                return $thirdPartyName;
            default:
                return 'Unknown';
        }
    }

    public function getDriverDetails($assignment)
    {
        // Default structure to ensure all keys exist
        $defaultDetails = [
            'phone' => null,
            'email' => null,
            'type' => ucfirst(str_replace('_', ' ', $assignment->driver_type ?? 'Unknown')),
            'profile_url' => null,
            'contract_status' => null,
            'contact' => null,
            'fein' => null,
            'email_sent' => null
        ];

        switch ($assignment->driver_type) {
            case 'company_driver':
                if ($assignment->user && $assignment->user->driverDetail) {
                    return array_merge($defaultDetails, [
                        'phone' => $assignment->user->driverDetail->phone,
                        'email' => $assignment->user->email,
                        'profile_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $this->vehicle->carrier->slug,
                            'userDriverDetail' => $assignment->user->driverDetail->id
                        ])
                    ]);
                }
                break;
            case 'owner_operator':
                if ($assignment->ownerOperatorDetail) {
                    return array_merge($defaultDetails, [
                        'phone' => $assignment->ownerOperatorDetail->owner_phone,
                        'email' => $assignment->ownerOperatorDetail->owner_email,
                        'contract_status' => $assignment->ownerOperatorDetail->contract_agreed ? 'Accepted' : 'Pending'
                    ]);
                }
                break;
            case 'third_party':
                $details = $defaultDetails;
                
                // Información de la empresa third party
                if ($assignment->thirdPartyDetail) {
                    $details = array_merge($details, [
                        'company_phone' => $assignment->thirdPartyDetail->third_party_phone,
                        'company_email' => $assignment->thirdPartyDetail->third_party_email,
                        'contact' => $assignment->thirdPartyDetail->third_party_contact,
                        'fein' => $assignment->thirdPartyDetail->third_party_fein,
                        'email_sent' => $assignment->thirdPartyDetail->email_sent ? 'Sent' : 'Pending',
                        'company_name' => $assignment->thirdPartyDetail->third_party_name
                    ]);
                }
                
                // Información del conductor real
                if ($assignment->user) {
                    $details = array_merge($details, [
                        'driver_name' => $assignment->user->name,
                        'driver_email' => $assignment->user->email,
                        'driver_phone' => $assignment->user->driverDetail?->phone,
                        'profile_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $this->vehicle->carrier->slug,
                            'userDriverDetail' => $assignment->user->driverDetail?->id
                        ])
                    ]);
                }
                
                return $details;
        }
        
        return $defaultDetails;
    }

    public function render()
    {
        return view('livewire.admin.vehicle.vehicle-driver-assignment-history');
    }
}
