<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\CarrierDocument;
use App\Http\Controllers\Controller;

class CarrierDocumentController extends Controller
{
    /**
     * Mostrar todos los documentos de todos los carriers (Solo para Super Admin).
     */
    public function all()
    {
        $documents = CarrierDocument::with(['carrier', 'documentType'])->get();
        return view('admin.carrier_documents.all', compact('documents'));
    }

    /**
     * Mostrar los documentos de un carrier específico (Área del Carrier/User Carrier).
     */
    public function index(Carrier $carrier)
    {
        $documents = CarrierDocument::with('documentType')
            ->where('carrier_id', $carrier->id)
            ->get();

        return view('admin.carrier.documents.index', compact('carrier', 'documents'));
    }

    /**
     * Crear un nuevo documento (Solo para Super Admin).
     */
    public function create(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();
        return view('admin.carrier_documents.create', compact('carrier', 'documentTypes'));
    }

    /**
     * Guardar un nuevo documento (Área del Carrier y Super Admin).
     */
    public function store(Request $request, Carrier $carrier)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'document' => 'required|file|mimes:pdf,jpg,png|max:2048',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $documentType = DocumentType::findOrFail($validated['document_type_id']);
        $validated['date'] = $validated['date'] ?? now();

        $carrierDocument = CarrierDocument::create(array_merge($validated, [
            'carrier_id' => $carrier->id,
            'status' => CarrierDocument::STATUS_PENDING,
        ]));

        if ($request->hasFile('document')) {
            $carrierDocument
                ->addMediaFromRequest('document')
                ->usingFileName(strtolower(str_replace(' ', '_', $documentType->name)) . '.pdf')
                ->toMediaCollection('carrier_documents', 'public');
        }

        return redirect()->route('admin.carrier.documents.index', $carrier->slug)
            ->with('success', 'Documento subido exitosamente.');
    }

    /**
     * Editar un documento existente (Solo para Super Admin).
     */
    public function edit(Carrier $carrier, CarrierDocument $document)
    {
        $documentTypes = DocumentType::all();
        return view('admin.carrier_documents.edit', compact('carrier', 'document', 'documentTypes'));
    }

    /**
     * Actualizar un documento (Solo para Super Admin).
     */
    public function update(Request $request, Carrier $carrier, CarrierDocument $document)
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:0,1,2',
            'document' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        if ($request->hasFile('document')) {
            $document->clearMediaCollection('carrier_documents');

            $document
                ->addMediaFromRequest('document')
                ->usingFileName(strtolower(str_replace(' ', '_', $document->documentType->name)) . '.pdf')
                ->toMediaCollection('carrier_documents', 'public');
        }

        $document->update(array_merge($validated, [
            'date' => $validated['date'] ?? $document->date,
        ]));

        return redirect()->route('admin.carrier.documents.index', $carrier->slug)
            ->with('success', 'Documento actualizado exitosamente.');
    }

    /**
     * Revisar y cambiar el estado de un documento (Solo para Super Admin).
     */
    public function review(Carrier $carrier, CarrierDocument $document)
    {
        return view('admin.carrier_documents.review', compact('carrier', 'document'));
    }

    public function processReview(Request $request, Carrier $carrier, CarrierDocument $document)
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:1,2',
            'notes' => 'nullable|string',
        ]);

        $document->update($validated);

        return redirect()->route('admin.carrier.documents.review', [$carrier->slug, $document->id])
            ->with('success', 'Estado del documento actualizado correctamente.');
    }

    /**
     * Eliminar un documento (Solo para Super Admin).
     */
    public function destroy(Carrier $carrier, CarrierDocument $document)
    {
        if ($document->documentType->requirement) {
            return redirect()->route('admin.carrier.documents.index', $carrier->slug)
                ->with('error', 'No se puede eliminar un documento obligatorio.');
        }

        $document->clearMediaCollection('carrier_documents');
        $document->delete();

        return redirect()->route('admin.carrier.documents.index', $carrier->slug)
            ->with('success', 'Documento eliminado exitosamente.');
    }
}
