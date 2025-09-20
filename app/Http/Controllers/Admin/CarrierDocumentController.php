<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use App\Services\CarrierDocumentService;
use App\Traits\SendsCustomNotifications;
use App\Repositories\CarrierDocumentRepository;
use App\Notifications\Admin\Carrier\NewDocumentUploadedNotification;

class CarrierDocumentController extends Controller
{

    use SendsCustomNotifications;
    protected $documentService;
    protected $documentRepository;

    public function __construct(
        CarrierDocumentService $documentService,
        CarrierDocumentRepository $documentRepository
    ) {
        $this->documentService = $documentService;
        $this->documentRepository = $documentRepository;
    }
    /**
     * Mostrar todos los documentos de todos los carriers (Solo para Super Admin).
     */

    /**
     * Mostrar los documentos de un carrier específico (Área del Carrier/User Carrier).
     */
    public function index(Carrier $carrier)
    {
        $documents = $this->documentRepository->getDocumentsWithStatus($carrier);
        $progress = $this->documentRepository->calculateDocumentProgress($carrier);

        return view('admin.carrier.documents.index', compact('carrier', 'documents', 'progress'));
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
        
        // Refrescar el documento para obtener el status_name actualizado
        $document->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Document status updated successfully.',
            'newStatus' => $document->status,
            'statusName' => $document->status_name,
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

        // Enviar notificación al administrador
        $adminEmail = env('ADMIN_NOTIFICATION_EMAIL');
        Notification::route('mail', $adminEmail)->notify(new NewDocumentUploadedNotification($carrierDocument));

        if ($request->hasFile('document')) {
            $carrierDocument
                ->addMediaFromRequest('document')
                ->usingFileName(strtolower(str_replace(' ', '_', $documentType->name)) . '.pdf')
                ->toMediaCollection('carrier_documents', 'public');
        }



        return back()->with($this->sendNotification(
            'success',
            'Documento subido exitosamente.',
            'El documento ha sido procesado y almacenado correctamente.'
        ));
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
            'status' => 'required|integer|in:0,1,2,3',
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

        return back()->with($this->sendNotification(
            'success',
            'Documento actualizado exitosamente.',
            'The document data has been saved correctly.'
        ));
    }

    public function listCarriersForDocuments()
    {
        $carriers = Carrier::with(['documents'])->get()->map(function ($carrier) {
            $progress = $this->documentRepository->calculateDocumentProgress($carrier);
            $carrier->completion_percentage = $progress['percentage'];
            $carrier->document_status = $progress['status'];
            return $carrier;
        });

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
            'notes' => 'nullable|string',
        ]);

        $document = $this->documentRepository->createOrUpdateDocument(
            $carrier,
            $documentType,
            $request->file('document'),
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Document uploaded successfully.');
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
