<?php

namespace App\Http\Controllers;

use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentAttachmentController extends Controller
{
    /**
     * Muestra una vista previa o descarga un documento.
     *
     * @param int $id ID del documento
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function preview($id)
    {
        try {
            $document = DocumentAttachment::findOrFail($id);
            $path = $document->getPath();
            
            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }
            
            $mimeType = $document->mime_type;
            
            // Si es una imagen o PDF, mostrar en el navegador
            if ($document->isImage() || $document->isPdf()) {
                return response()->file($path);
            } 
            
            // Para otros tipos de archivo, forzar descarga
            return response()->download($path, $document->original_name);
            
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Error al previsualizar documento'], 500);
        }
    }
    
    /**
     * Elimina un documento.
     *
     * @param int $id ID del documento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $document = DocumentAttachment::findOrFail($id);
            
            // Guardar información para la redirección
            $modelType = $document->documentable_type;
            $modelId = $document->documentable_id;
            $fileName = $document->original_name;
            
            // Eliminar el archivo físico
            Storage::disk('public')->delete($document->file_path);
            
            // Eliminar el registro
            $document->delete();
            
            // Determinar la ruta de redirección según el tipo de modelo
            $routeName = $this->getRedirectRouteName($modelType);
            
            if ($routeName) {
                return redirect()->route($routeName, $modelId)
                    ->with('success', "Documento '{$fileName}' eliminado correctamente");
            }
            
            // Ruta por defecto si no se puede determinar
            return redirect()->back()
                ->with('success', "Documento '{$fileName}' eliminado correctamente");
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Determina la ruta de redirección según el tipo de modelo.
     *
     * @param string $modelType Tipo de modelo (clase completa)
     * @return string|null
     */
    private function getRedirectRouteName(string $modelType): ?string
    {
        $routeMap = [
            'App\\Models\\Admin\\Driver\\DriverAccident' => 'admin.accidents.edit',
            'App\\Models\\Admin\\Driver\\TrafficConviction' => 'admin.traffic.edit',
            // Añadir más mapeos según sea necesario
        ];
        
        return $routeMap[$modelType] ?? null;
    }
}
