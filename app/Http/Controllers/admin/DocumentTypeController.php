<?php

namespace App\Http\Controllers\Admin;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
            'allow_default_file' => 'required|boolean',
            'default_file' => 'nullable|file|mimes:pdf,jpg,png|max:1048',
        ]);
    
        $documentType = new DocumentType($request->only(['name', 'requirement']));
        $documentType->save(); // Guardar para obtener el ID.
    
        if ($request->hasFile('default_file') && $request->allow_default_file) {
            $fileName = strtolower(str_replace(' ', '_', $request->name)) . '.' . $request->file('default_file')->getClientOriginalExtension();
    
            $documentType->addMediaFromRequest('default_file')
                ->usingFileName($fileName)
                ->toMediaCollection('default_documents');
        }
    
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
            'allow_default_file' => 'required|boolean',
            'default_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);
    
        $documentType->fill($request->only(['name', 'requirement']));
    
        if ($request->hasFile('default_file') && $request->allow_default_file) {
            // Eliminar el archivo anterior si existe
            $documentType->clearMediaCollection('default_documents');
    
            $fileName = strtolower(str_replace(' ', '_', $documentType->name)) . '.' . $request->file('default_file')->getClientOriginalExtension();
    
            $documentType->addMediaFromRequest('default_file')
                ->usingFileName($fileName)
                ->toMediaCollection('default_documents');
        } elseif (!$request->allow_default_file) {
            // Limpiar la colección si se desactiva
            $documentType->clearMediaCollection('default_documents');
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
