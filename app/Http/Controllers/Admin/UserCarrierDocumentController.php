<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\CarrierDocument;
use App\Http\Controllers\Controller;

class UserCarrierDocumentController extends Controller
{
    /**
     * Mostrar los documentos del Carrier para que el usuario los suba.
     */
    public function index(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();
        $uploadedDocuments = CarrierDocument::where('carrier_id', $carrier->id)->get();
    
        $documents = $documentTypes->map(function ($type) use ($uploadedDocuments) {
            $uploaded = $uploadedDocuments->firstWhere('document_type_id', $type->id);
    
            return [
                'type' => $type,
                'document' => $uploaded,
                'status_name' => $uploaded?->status_name ?? 'Not Uploaded',
                'notes' => $uploaded?->notes,
                'file_url' => $uploaded?->getFirstMediaUrl('document'),
            ];
        });
    
        return view('admin.user_documents.index', compact('carrier', 'documents'));
    }
    
    

    /**
     * Subir un archivo para un documento específico.
     */
    public function upload(Request $request, Carrier $carrier, DocumentType $documentType)
    {
        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);
    
        // Buscar o crear el documento base
        $carrierDocument = CarrierDocument::firstOrCreate(
            [
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(), // Fecha actual
            ]
        );
    
        // Subir el archivo
        if ($request->hasFile('document')) {
            $carrierDocument->clearMediaCollection('document');
            $carrierDocument->addMediaFromRequest('document')
                ->usingName($documentType->name)
                ->toMediaCollection('document');

            // Actualizar el estado a "In Process"
            $carrierDocument->update(['status' => CarrierDocument::STATUS_IN_PROCESS]);
        }
    
        // Redirigir al listado de documentos (index)
        return redirect()
            ->route('admin.carrier.user_documents.index', $carrier->slug)
            ->with('success', 'Documento subido correctamente.');
    }
    
    
}
