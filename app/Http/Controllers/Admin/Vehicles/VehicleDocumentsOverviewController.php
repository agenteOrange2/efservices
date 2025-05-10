<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleDocument;
use App\Models\Carrier;
use Illuminate\Http\Request;

class VehicleDocumentsOverviewController extends Controller
{
    /**
     * Display an overview of all vehicles and their document statuses.
     */
    public function index(Request $request)
    {
        $query = Vehicle::with([
            'carrier',
            'documents' => function ($query) {
                $query->orderBy('expiration_date', 'asc');
            }
        ]);
        
        // Filtros
        if ($request->has('carrier_id') && $request->carrier_id) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('out_of_service', false)->where('suspended', false);
            } elseif ($request->status === 'out_of_service') {
                $query->where('out_of_service', true);
            } elseif ($request->status === 'suspended') {
                $query->where('suspended', true);
            }
        }

        if ($request->has('document_status') && $request->document_status) {
            $documentStatus = $request->document_status;
            
            $query->whereHas('documents', function ($query) use ($documentStatus) {
                $query->where('status', $documentStatus);
            });
        }

        if ($request->has('document_type') && $request->document_type) {
            $documentType = $request->document_type;
            
            $query->whereHas('documents', function ($query) use ($documentType) {
                $query->where('document_type', $documentType);
            });
        }
        
        $vehicles = $query->paginate(10);
        
        // Obtener carriers para el filtro
        $carriers = Carrier::where('status', 1)->get();
        
        // Obtener tipos de documentos para el filtro
        $documentTypes = $this->getDocumentTypes();
        
        // Obtener estados de documentos para el filtro
        $documentStatuses = [
            VehicleDocument::STATUS_ACTIVE => 'Active',
            VehicleDocument::STATUS_EXPIRED => 'Expired',
            VehicleDocument::STATUS_PENDING => 'Pending',
            VehicleDocument::STATUS_REJECTED => 'Rejected',
        ];
        
        return view('admin.vehicles.documents.overview', compact(
            'vehicles', 
            'carriers', 
            'documentTypes', 
            'documentStatuses'
        ));
    }

    /**
     * Get array of document types for dropdowns.
     */
    private function getDocumentTypes(): array
    {
        return [
            VehicleDocument::DOC_TYPE_REGISTRATION => 'Registration',
            VehicleDocument::DOC_TYPE_INSURANCE => 'Insurance',
            VehicleDocument::DOC_TYPE_ANNUAL_INSPECTION => 'Annual Inspection',
            VehicleDocument::DOC_TYPE_IRP_PERMIT => 'IRP Permit',
            VehicleDocument::DOC_TYPE_IFTA => 'IFTA',
            VehicleDocument::DOC_TYPE_TITLE => 'Title',
            VehicleDocument::DOC_TYPE_LEASE_AGREEMENT => 'Lease Agreement',
            VehicleDocument::DOC_TYPE_MAINTENANCE_RECORD => 'Maintenance Record',
            VehicleDocument::DOC_TYPE_EMISSIONS_TEST => 'Emissions Test',
            VehicleDocument::DOC_TYPE_OTHER => 'Other',
        ];
    }
}
