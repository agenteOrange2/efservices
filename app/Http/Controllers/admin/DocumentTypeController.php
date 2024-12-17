<?php

namespace App\Http\Controllers\Admin;

use App\Models\DocumentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
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
        ]);

        DocumentType::create($request->all());

        // Mensaje dinámico para la notificación
        return redirect()
        ->route('admin.document-types.index')
        ->with('notification', [
            'type' => 'success',
            'message' => 'Document Type created successfully!',
            'details' => 'The Document Type data has been saved correctly.',
        ]);
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
        ]);

        $documentType->update($request->all());


        
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
