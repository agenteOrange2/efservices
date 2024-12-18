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

    /**
     * Mostrar los documentos de un carrier específico (Área del Carrier/User Carrier).
     */
    public function index(Carrier $carrier)
    {
        // Mostrar documentos de un carrier específico
        $carrierDocuments = CarrierDocument::where('carrier_id', $carrier->id)
            ->with('documentType')
            ->get();

        return view('admin.carrier.documents.index', compact('carrier', 'carrierDocuments'));
    }

    public function all()
    {
        // Mostrar todos los documentos para el superadmin
        $documents = CarrierDocument::with(['carrier', 'documentType'])->get();
        return view('admin.carrier_documents.all', compact('documents'));
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

        return back()->with('success', 'Documento subido exitosamente.');
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

        return back()->with('success', 'Documento subido exitosamente.');
    }



    public function listCarriersForDocuments()
    {
        // Obtenemos los carriers con los documentos y primer user_carrier
        $carriers = Carrier::with(['userCarriers', 'documents'])->get();

        // Calculamos el estado general de los archivos (verde, amarillo, rojo)
        foreach ($carriers as $carrier) {
            $approved = $carrier->documents->where('status', CarrierDocument::STATUS_APPROVED)->count();
            $totalDocuments = DocumentType::count();

            // Semáforo de estados
            if ($approved == $totalDocuments && $totalDocuments > 0) {
                $carrier->document_status = 'active'; // Verde
            } elseif ($approved > 0) {
                $carrier->document_status = 'pending'; // Amarillo
            } else {
                $carrier->document_status = 'inactive'; // Rojo
            }

            // Obtener el primer User Carrier si existe
            $carrier->first_user_carrier = $carrier->userCarriers->first();
        }

        return view('admin.carrier.admin_documents_list', compact('carriers'));
    }


    public function reviewDocuments(Carrier $carrier)
    {
        $documents = CarrierDocument::with('documentType')->where('carrier_id', $carrier->id)->get();
        $documentTypes = DocumentType::all(); // Necesario para cargar tipos de documentos
    
        return view('admin.carrier.review_documents', compact('carrier', 'documents', 'documentTypes'));
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
    }

    public function upload(Request $request, Carrier $carrier, DocumentType $documentType)
    {
        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);
    
        // Crear o actualizar el documento
        $carrierDocument = CarrierDocument::updateOrCreate(
            [
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'status' => CarrierDocument::STATUS_PENDING,
            ]
        );
    
        // Subir el archivo usando Media Library
        if ($request->hasFile('document')) {
            $carrierDocument->clearMediaCollection('carrier_documents');
            $carrierDocument
                ->addMediaFromRequest('document')
                ->usingFileName(strtolower(str_replace(' ', '_', $documentType->name)) . '.pdf')
                ->toMediaCollection('carrier_documents', 'public');
        }
    
        return back()->with('success', 'Documento subido exitosamente.');
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
