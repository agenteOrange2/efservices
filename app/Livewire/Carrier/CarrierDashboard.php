<?php

namespace App\Livewire\Carrier;

use Livewire\Component;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\UserDriverDetail;

class CarrierDashboard extends Component
{
    public Carrier $carrier;
    public $documentStats;
    public $driversCount;
    public $vehiclesCount;
    public $recentDrivers;
    public $recentDocuments;
    public $documentTypeCounts;
    public $documentStatusCounts;

    public function mount(Carrier $carrier = null)
    {
        // Si no se proporciona un carrier, obtenemos el carrier del usuario autenticado
        if (!$carrier) {
            $this->carrier = \Illuminate\Support\Facades\Auth::user()->carrierDetails->carrier;
        } else {
            $this->carrier = $carrier;
        }

        $this->loadData();
    }

    protected function loadData()
    {
        // Conteo de conductores y vehículos
        $this->driversCount = $this->carrier->userDrivers()->count();
        $this->vehiclesCount = $this->carrier->vehicles()->count() ?? 0;

        // Estadísticas de documentos
        $this->documentStats = [
            'total' => $this->carrier->documents()->count(),
            'pending' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_PENDING)->count(),
            'approved' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_APPROVED)->count(),
            'rejected' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_REJECTED)->count(),
        ];

        // Conteo de documentos por tipo
        $this->documentTypeCounts = $this->carrier->documents()
            ->join('document_types', 'carrier_documents.document_type_id', '=', 'document_types.id')
            ->selectRaw('document_types.name, count(*) as count')
            ->groupBy('document_types.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        // Conteo de documentos por estado
        $this->documentStatusCounts = [
            'Pendiente' => $this->documentStats['pending'],
            'Aprobado' => $this->documentStats['approved'],
            'Rechazado' => $this->documentStats['rejected'],
            'En Proceso' => $this->carrier->documents()->where('status', CarrierDocument::STATUS_IN_PROCCESS)->count(),
        ];

        // Obtener conductores recientes
        $this->recentDrivers = $this->carrier->userDrivers()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Obtener documentos recientes
        $this->recentDocuments = $this->carrier->documents()
            ->with('documentType')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    // En Livewire 3, podemos usar directamente una propiedad computada con el prefijo "get"
    public function getMembershipLimitsProperty()
    {
        $membership = $this->carrier->membership;
        return [
            'maxDrivers' => $membership ? $membership->max_drivers : 0,
            'maxVehicles' => $membership ? $membership->max_vehicles : 0,
            'driversPercentage' => $membership && $membership->max_drivers > 0 
                ? round(($this->driversCount / $membership->max_drivers) * 100) 
                : 0,
            'vehiclesPercentage' => $membership && $membership->max_vehicles > 0 
                ? round(($this->vehiclesCount / $membership->max_vehicles) * 100) 
                : 0,
        ];
    }

    public function render()
    {
        return view('livewire.carrier.carrier-dashboard');
    }
}
