<?php

namespace App\Http\Controllers\Admin;


use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CarrierDocumentController extends Controller
{

    public function all()
    {
        $documents = CarrierDocument::with(['carrier', 'documentType'])->get();

        return view('admin.carrier_documents.all', compact('documents'));
    }

    public function index(Carrier $carrier)
    {
        $documents = $carrier->documents()->with('documentType')->get();

        return view('admin.carrier_documents.index', compact('carrier', 'documents'));
    }

    public function create(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();

        return view('admin.carrier_documents.create', compact('carrier', 'documentTypes'));
    }

    public function store(Request $request, Carrier $carrier)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        CarrierDocument::create([
            'carrier_id' => $carrier->id,
            'document_type_id' => $request->document_type_id,
            'status' => CarrierDocument::STATUS_PENDING,
        ]);

        return redirect()->route('admin.carriers.documents.index', $carrier)
            ->with('success', 'Documento creado exitosamente.');
    }

    public function edit(CarrierDocument $document)
    {
        return view('admin.carrier_documents.edit', compact('document'));
    }

    public function update(Request $request, CarrierDocument $document)
    {
        $request->validate([
            'status' => 'required|integer',
            'document' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('carrier-documents');
            $document->update(['filename' => $path]);
        }

        $document->update(['status' => $request->status]);

        return redirect()->route('admin.carriers.documents.index', $document->carrier_id)
            ->with('success', 'Documento actualizado exitosamente.');
    }

    public function destroy(CarrierDocument $document)
    {
        if (!$document->documentType->requirement) {
            $document->delete();

            return redirect()->back()->with('success', 'Documento eliminado exitosamente.');
        }

        return redirect()->back()->with('error', 'No se puede eliminar un documento obligatorio.');
    }
}
