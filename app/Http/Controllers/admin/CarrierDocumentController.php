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


    public function approveDefaultDocument(Request $request, Carrier $carrier, CarrierDocument $document)
    {
        $request->validate(['approved' => 'required|boolean']);

        $document->status = $request->approved ? CarrierDocument::STATUS_APPROVED : CarrierDocument::STATUS_PENDING;
        $document->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Document status updated successfully.',
            'newStatus' => $document->status,
        ]);
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

        return back()
            ->with('notification', [
                'type' => 'success',
                'message' => 'Documento subido exitosamente.',
                'details' => 'The document data has been saved correctly.',
            ]);
    }

    public function listCarriersForDocuments()
    {
        $carriers = Carrier::with(['userCarriers', 'documents'])->get();

        foreach ($carriers as $carrier) {
            $totalDocuments = DocumentType::count();
            $approvedDocuments = $carrier->documents
                ->where('status', CarrierDocument::STATUS_APPROVED)
                ->count();

            $carrier->completion_percentage = $totalDocuments > 0 ? ($approvedDocuments / $totalDocuments) * 100 : 0;

            // Estado general del carrier
            if ($approvedDocuments === $totalDocuments && $totalDocuments > 0) {
                $carrier->document_status = 'active'; // Verde
            } elseif ($approvedDocuments > 0) {
                $carrier->document_status = 'pending'; // Amarillo
            } else {
                $carrier->document_status = 'inactive'; // Rojo
            }
        }

        return view('admin.carrier.admin_documents_list', compact('carriers'));
    }


    public function refresh()
    {
        // Obtener los carriers con los documentos actualizados
        $carriers = Carrier::with(['documents'])->get();

        // Procesar los datos para enviar el progreso y estado
        $data = $carriers->map(function ($carrier) {
            $totalDocuments = DocumentType::count();
            $approvedDocuments = $carrier->documents
                ->where('status', CarrierDocument::STATUS_APPROVED)
                ->count();

            return [
                'id' => $carrier->id,
                'completion_percentage' => $totalDocuments > 0 ? ($approvedDocuments / $totalDocuments) * 100 : 0,
                'document_status' => $approvedDocuments === $totalDocuments
                    ? 'active'
                    : ($approvedDocuments > 0 ? 'pending' : 'inactive'),
            ];
        });

        // Retornar los datos en formato JSON
        return response()->json($data);
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

        return back()
            ->with('notification', [
                'type' => 'success',
                'message' => 'Document upload successfully!',
                'details' => 'The document data has been saved correctly.',
            ]);
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
