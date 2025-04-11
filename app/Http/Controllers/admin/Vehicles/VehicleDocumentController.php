<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehicleDocumentController extends Controller
{
    /**
     * Display a listing of the vehicle documents.
     */
    public function index(Vehicle $vehicle)
    {
        $vehicle->load(['documents' => function($query) {
            $query->orderBy('expiration_date', 'asc');
        }]);

        // Agrupar documentos por tipo
        $documentsByType = $vehicle->documents->groupBy('document_type');
        
        // Obtener los tipos de documentos para el selector
        $documentTypes = $this->getDocumentTypes();
        
        return view('admin.vehicles.documents.index', compact('vehicle', 'documentsByType', 'documentTypes'));
    }

    /**
     * Show the form for creating a new vehicle document.
     */
    public function create(Vehicle $vehicle)
    {
        $documentTypes = $this->getDocumentTypes();
        
        return view('admin.vehicles.documents.create', compact('vehicle', 'documentTypes'));
    }

    /**
     * Store a newly created vehicle document in storage.
     */
    public function store(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string',
            'document_number' => 'nullable|string|max:255',
            'issued_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:issued_date',
            'notes' => 'nullable|string',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'document_type.required' => 'El tipo de documento es obligatorio',
            'expiration_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión',
            'document_file.required' => 'Debe subir un archivo',
            'document_file.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG',
            'document_file.max' => 'El archivo no debe superar los 10MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Determinar estado inicial basado en la fecha de vencimiento
        $status = VehicleDocument::STATUS_ACTIVE;
        if ($request->filled('expiration_date')) {
            $expirationDate = \Carbon\Carbon::parse($request->expiration_date);
            if ($expirationDate->isPast()) {
                $status = VehicleDocument::STATUS_EXPIRED;
            }
        }

        // Crear documento
        $document = VehicleDocument::create([
            'vehicle_id' => $vehicle->id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'issued_date' => $request->issued_date,
            'expiration_date' => $request->expiration_date,
            'status' => $status,
            'notes' => $request->notes,
        ]);

        // Manejar la carga de archivos con Spatie Media Library
        if ($request->hasFile('document_file')) {
            $document->addMediaFromRequest('document_file')
                ->toMediaCollection('document_files');
        }

        return redirect()->route('admin.vehicles.documents.index', $vehicle->id)
            ->with('success', 'Documento agregado exitosamente');
    }

    /**
     * Display the specified vehicle document.
     */
    public function show(Vehicle $vehicle, VehicleDocument $document)
    {
        if ($document->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        return view('admin.vehicles.documents.show', compact('vehicle', 'document'));
    }

    /**
     * Show the form for editing the specified vehicle document.
     */
    public function edit(Vehicle $vehicle, VehicleDocument $document)
    {
        if ($document->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        $documentTypes = $this->getDocumentTypes();
        
        return view('admin.vehicles.documents.edit', compact('vehicle', 'document', 'documentTypes'));
    }

    /**
     * Update the specified vehicle document in storage.
     */
    public function update(Request $request, Vehicle $vehicle, VehicleDocument $document)
    {
        if ($document->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string',
            'document_number' => 'nullable|string|max:255',
            'issued_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:issued_date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'document_type.required' => 'El tipo de documento es obligatorio',
            'expiration_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión',
            'document_file.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG',
            'document_file.max' => 'El archivo no debe superar los 10MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar documento
        $document->update([
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'issued_date' => $request->issued_date,
            'expiration_date' => $request->expiration_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Manejar la carga de archivos
        if ($request->hasFile('document_file')) {
            // Eliminar archivo anterior
            $document->clearMediaCollection('document_files');
            
            // Subir nuevo archivo
            $document->addMediaFromRequest('document_file')
                ->toMediaCollection('document_files');
        }

        return redirect()->route('admin.vehicles.documents.index', $vehicle->id)
            ->with('success', 'Documento actualizado exitosamente');
    }

    /**
     * Remove the specified vehicle document from storage.
     */
    public function destroy(Vehicle $vehicle, VehicleDocument $document)
    {
        if ($document->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        try {
            // Eliminar archivos asociados
            $document->clearMediaCollection('document_files');
            
            // Eliminar registro
            $document->delete();
            
            return redirect()->route('admin.vehicles.documents.index', $vehicle->id)
                ->with('success', 'Documento eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('admin.vehicles.documents.index', $vehicle->id)
                ->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Download the document file.
     */
    public function download(Vehicle $vehicle, VehicleDocument $document)
    {
        if ($document->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        $media = $document->getFirstMedia('document_files');
        
        if (!$media) {
            return redirect()->back()->with('error', 'El archivo no existe');
        }
        
        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * Preview the document file.
     */
    public function preview(Vehicle $vehicle, VehicleDocument $document)
    {
        if ($document->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        $media = $document->getFirstMedia('document_files');
        
        if (!$media) {
            return redirect()->back()->with('error', 'El archivo no existe');
        }
        
        // Si es un PDF, mostrarlo en el navegador
        if ($media->mime_type === 'application/pdf') {
            return response()->file($media->getPath(), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
            ]);
        }
        
        // Si es una imagen, mostrar vista previa
        if (strpos($media->mime_type, 'image/') === 0) {
            return response()->file($media->getPath(), [
                'Content-Type' => $media->mime_type,
                'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
            ]);
        }
        
        // Para otros tipos, descargar
        return response()->download($media->getPath(), $media->file_name);
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