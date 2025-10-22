<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\VehicleDriverAssignment;

class VehicleDriverAssignmentHistorySection extends Component
{
    use WithPagination;

    public $vehicle;
    public $showSection = false;
    public $perPage = 10;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    public function toggleSection()
    {
        $this->showSection = !$this->showSection;
    }

    public function render()
    {
        $assignments = collect();
        
        if ($this->showSection) {
            $assignments = VehicleDriverAssignment::where('vehicle_id', $this->vehicle->id)
                ->with(['user.driverDetail', 'ownerOperatorDetail', 'thirdPartyDetail'])
                ->orderBy('start_date', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.vehicle-driver-assignment-history-section', [
            'assignments' => $assignments
        ]);
    }

    public function getDriverName($assignment)
    {
        switch ($assignment->driver_type) {
            case 'company_driver':
                return $assignment->user?->name ?? 'Usuario Desconocido';
            case 'owner_operator':
                return $assignment->ownerOperatorDetail?->owner_name ?? 'Owner Operator Desconocido';
            case 'third_party':
                return $assignment->thirdPartyDetail?->third_party_name ?? 'Third Party Desconocido';
            default:
                return 'Desconocido';
        }
    }

    public function getDriverType($assignment)
    {
        switch ($assignment->driver_type) {
            case 'company_driver':
                return 'Conductor de Empresa';
            case 'owner_operator':
                return 'Owner Operator';
            case 'third_party':
                return 'Third Party';
            default:
                return 'Desconocido';
        }
    }

    public function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'inactive':
                return 'bg-gray-100 text-gray-800';
            case 'terminated':
                return 'bg-red-100 text-red-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    public function getStatusText($status)
    {
        switch ($status) {
            case 'active':
                return 'Activo';
            case 'inactive':
                return 'Inactivo';
            case 'terminated':
                return 'Terminado';
            case 'pending':
                return 'Pendiente';
            default:
                return ucfirst($status);
        }
    }

    public function getDuration($startDate, $endDate)
    {
        if (!$startDate) return 'N/A';
        
        $start = \Carbon\Carbon::parse($startDate);
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : now();
        
        $days = $start->diffInDays($end);
        
        if ($days < 30) {
            return $days . ' días';
        } elseif ($days < 365) {
            $months = floor($days / 30);
            return $months . ' mes' . ($months > 1 ? 'es' : '');
        } else {
            $years = floor($days / 365);
            $remainingMonths = floor(($days % 365) / 30);
            $result = $years . ' año' . ($years > 1 ? 's' : '');
            if ($remainingMonths > 0) {
                $result .= ', ' . $remainingMonths . ' mes' . ($remainingMonths > 1 ? 'es' : '');
            }
            return $result;
        }
    }
}