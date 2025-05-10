<?php

namespace App\Http\Controllers\Carrier;

use App\Models\Carrier;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\CarrierDocument;
use App\Http\Controllers\Controller;
use App\Services\CarrierDocumentService;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(CarrierDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(Carrier $carrier)
    {
        // Verificar que el usuario autenticado pertenece a este carrier
        if (auth()->user()->carrierDetails->carrier_id !== $carrier->id) {
            abort(403);
        }

        $documentTypes = DocumentType::all();
        $uploadedDocuments = CarrierDocument::where('carrier_id', $carrier->id)->get();

        $documents = $documentTypes->map(function ($type) use ($uploadedDocuments) {
            $uploaded = $uploadedDocuments->firstWhere('document_type_id', $type->id);
            return [
                'type' => $type,
                'document' => $uploaded,
                'status_name' => $uploaded ? $uploaded->status_name : 'Not Uploaded',
                'file_url' => $uploaded ? $uploaded->getFirstMediaUrl('carrier_documents') : null,
            ];
        });

        return view('auth.user_carrier.documents.index', compact('carrier', 'documents'));
    }

    // DocumentController.php

    public function toggleDefaultDocument(Request $request, Carrier $carrier, DocumentType $documentType)
    {
        $request->validate(['approved' => 'required|boolean']);
    
        $document = CarrierDocument::firstOrCreate(
            [
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]
        );
    
        $document->status = $request->approved ? 
            CarrierDocument::STATUS_APPROVED : 
            CarrierDocument::STATUS_PENDING;
        $document->save();
    
        return response()->json(['success' => true]);
    }

    public function upload(Request $request, Carrier $carrier, DocumentType $documentType)
    {
        // Verificar que el usuario autenticado pertenece a este carrier
        if (auth()->user()->carrierDetails->carrier_id !== $carrier->id) {
            abort(403);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,png|max:2048'
        ]);

        $carrierDocument = CarrierDocument::firstOrCreate(
            [
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]
        );

        if ($request->hasFile('document')) {
            $carrierDocument->clearMediaCollection('carrier_documents');
            $carrierDocument->addMediaFromRequest('document')
                ->toMediaCollection('carrier_documents');
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function skipDocuments(Carrier $carrier)
    {
        // Verificar que el usuario autenticado pertenece a este carrier
        if (auth()->user()->carrierDetails->carrier_id !== $carrier->id) {
            abort(403);
        }

        $carrier->update(['document_status' => 'skipped']);

        return redirect()->route('carrier.confirmation')
            ->with('status', 'You can upload your documents later from your dashboard.');
    }

    public function complete(Carrier $carrier)
    {
        // Verificar que el usuario autenticado pertenece a este carrier
        if (auth()->user()->carrierDetails->carrier_id !== $carrier->id) {
            abort(403);
        }

        $carrier->update(['document_status' => 'completed']);

        return redirect()->route('carrier.confirmation')
            ->with('status', 'Your documents have been submitted for review.');
    }
}
