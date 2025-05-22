<?php

namespace App\Http\Controllers\Admin;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Services\CarrierDocumentService;
use App\Traits\SendsCustomNotifications;

class DocumentTypeController extends Controller
{
    protected $documentService;
    use SendsCustomNotifications;

    public function __construct(CarrierDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Mostrar todos los tipos de documentos.
     */
    public function index()
    {
        $documentTypes = DocumentType::all();

        return view('admin.document_types.index', compact('documentTypes'));
    }

    /**
     * Formulario para crear un nuevo tipo de documento.
     */
    public function create()
    {
        return view('admin.document_types.create');
    }

    /**
     * Guardar un nuevo tipo de documento.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:document_types',
            'requirement' => 'required|boolean',
            'allow_default_file' => 'required|boolean',
            'default_file' => 'nullable|file|mimes:pdf,jpg,png|max:1048',
        ]);

        $documentType = DocumentType::create([
            'name' => $request->name,
            'requirement' => $request->requirement,
        ]);

        if ($request->hasFile('default_file') && $request->allow_default_file) {
            $fileName = strtolower(str_replace(' ', '_', $request->name)) . '.' .
                $request->file('default_file')->getClientOriginalExtension();

            $documentType->addMediaFromRequest('default_file')
                ->usingFileName($fileName)
                ->toMediaCollection('default_documents');

            // Distribuir el documento por default a todos los carriers
            $this->documentService->distributeDefaultDocument($documentType);
        } else {
            // Si no es un documento por default, solo sincronizar el nuevo tipo
            $this->documentService->syncNewDocumentTypes();
        }

        return redirect()
            ->route('admin.document-types.index')
            ->with($this->sendNotification(
                'success',
                'Document Type created successfully!',
                'The Document Type has been created and distributed to all carriers.'
            ));
    }



    /**
     * Formulario para editar un tipo de documento.
     */
    public function edit(DocumentType $documentType)
    {
        return view('admin.document_types.edit', compact('documentType'));
    }

    /**
     * Actualizar un tipo de documento existente.
     */
    public function update(Request $request, DocumentType $documentType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,' . $documentType->id,
            'requirement' => 'required|boolean',
            'allow_default_file' => 'required|boolean',
            'default_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $documentType->fill($request->only(['name', 'requirement']));

        // Guardar el estado anterior para saber si tenía documento por defecto
        $hadDefaultDocument = $documentType->getFirstMedia('default_documents') !== null;
        
        // Caso 1: Se sube un nuevo documento por defecto
        if ($request->hasFile('default_file') && $request->allow_default_file) {
            // Eliminar el archivo anterior si existe
            $documentType->clearMediaCollection('default_documents');

            $fileName = strtolower(str_replace(' ', '_', $documentType->name)) . '.' . $request->file('default_file')->getClientOriginalExtension();

            $documentType->addMediaFromRequest('default_file')
                ->usingFileName($fileName)
                ->toMediaCollection('default_documents');

            // Distribuir el documento actualizado a todos los carriers
            $this->documentService->distributeDefaultDocument($documentType);
        } 
        // Caso 2: Se quita la opción de documento por defecto
        elseif (!$request->allow_default_file && $hadDefaultDocument) {
            // Limpiar la colección si se desactiva
            $documentType->clearMediaCollection('default_documents');
            
            // Actualizar todos los documentos de carriers que estaban usando el documento por defecto
            $this->documentService->distributeDefaultDocument($documentType);
        }
        // Caso 3: Se mantiene el documento por defecto existente
        elseif ($request->allow_default_file && $hadDefaultDocument) {
            // No es necesario hacer nada con el archivo, pero sí distribuir los cambios en el nombre o requisito
            $this->documentService->distributeDefaultDocument($documentType);
        }

        $documentType->save();

        return redirect()
            ->route('admin.document-types.index')
            ->with('notification', [
                'type' => 'success',
                'message' => 'Document Type updated successfully!',
                'details' => 'The Document Type data has been saved correctly.',
            ]);
    }



    /**
     * Eliminar un tipo de documento.
     */
    public function destroy(DocumentType $documentType)
    {
        // Evitar eliminar tipos de documentos si están asociados a carriers
        if ($documentType->carrierDocuments()->exists()) {
            return redirect()->route('admin.document-types.index')
                ->with('error', 'No se puede eliminar un tipo de documento asociado a un carrier.');
        }

        $documentType->delete();

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Tipo de documento eliminado exitosamente.');
    }
}