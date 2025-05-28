<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Driver\DriverAccident;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileUploaderController extends Controller
{
    /**
     * Sube documentos a un modelo usando Spatie Media Library
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {
        dd($request->all());
        try {
            // Log inicial para verificar que la ruta está siendo accedida
            Log::info('FileUploaderController@upload iniciado', [
                'request_all' => $request->all(),
                'has_file' => $request->hasFile('documents')
            ]);
            
            // Validar los datos
            $validator = Validator::make($request->all(), [
                'model_type' => 'required|string',
                'model_id' => 'required|integer',
                'collection' => 'required|string',
                'documents' => 'required|array',
                'documents.*' => 'required|file|max:10240', // 10MB máximo por defecto
            ]);

            if ($validator->fails()) {
                Log::error('Validación fallida en FileUploaderController', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Obtener los datos validados
            $modelType = $request->input('model_type');
            $modelId = $request->input('model_id');
            $collection = $request->input('collection');
            
            // Mapeo de tipos a modelos
            $modelMap = [
                'accident' => \App\Models\Admin\Driver\DriverAccident::class,
                // Verificando si existen las clases antes de usarlas para evitar errores
                // Si las clases no existen, dejaremos que el código continúe y solo
                // generará un error si realmente se intenta usar un tipo no soportado
            ];
            
            // Verificar y añadir otros modelos si existen las clases
            if (class_exists('\App\Models\Admin\Driver\TrafficConviction')) {
                $modelMap['traffic'] = '\App\Models\Admin\Driver\TrafficConviction';
            }
            
            if (class_exists('\App\Models\Admin\Driver\DrugTest')) {
                $modelMap['testing'] = '\App\Models\Admin\Driver\DrugTest';
            }
            
            if (class_exists('\App\Models\Admin\Vehicle\Inspection')) {
                $modelMap['inspection'] = '\App\Models\Admin\Vehicle\Inspection';
            }
            
            if (class_exists('\App\Models\Admin\Training\Training')) {
                $modelMap['training'] = '\App\Models\Admin\Training\Training';
            }
            
            if (class_exists('\App\Models\Admin\Training\Course')) {
                $modelMap['course'] = '\App\Models\Admin\Training\Course';
            }
            
            // Verificar que el tipo de modelo es válido
            if (!isset($modelMap[$modelType])) {
                return redirect()->back()
                    ->with('error', 'Tipo de modelo no válido')
                    ->withInput();
            }
            
            $modelClass = $modelMap[$modelType];
            
            // Buscar el modelo
            $model = $modelClass::find($modelId);
            if (!$model) {
                return redirect()->back()
                    ->with('error', 'Modelo no encontrado')
                    ->withInput();
            }
            
            // Verificar si hay archivos
            if(!$request->hasFile('documents')) {
                Log::error('No se encontraron archivos en la solicitud', [
                    'request_files' => $request->files->all()
                ]);
                return redirect()->back()->with('error', 'No se encontraron archivos para subir');
            }

            Log::info('Procesando archivos', [
                'files_count' => count($request->file('documents')),
                'model_class' => $modelClass,
                'model_id' => $modelId,
                'collection' => $collection
            ]);

            // Subir cada documento
            foreach ($request->file('documents') as $document) {
                try {
                    Log::info('Procesando archivo individual', [
                        'filename' => $document->getClientOriginalName(),
                        'size' => $document->getSize(),
                        'mime' => $document->getMimeType()
                    ]);
                    
                    $media = $model->addMedia($document)
                        ->toMediaCollection($collection);
                        
                    Log::info('Archivo subido correctamente', [
                        'media_id' => $media->id,
                        'filename' => $media->file_name,
                        'collection' => $collection
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al subir documento individual', [
                        'error' => $e->getMessage(),
                        'model_type' => $modelType,
                        'model_id' => $modelId,
                        'document' => $document->getClientOriginalName(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            return redirect()->back()
                ->with('success', 'Documentos subidos correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error al subir documentos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al subir documentos: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Elimina un documento
     * 
     * @param int $mediaId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($mediaId)
    {
        try {
            // Buscar el media
            $mediaItem = Media::findOrFail($mediaId);
            
            // Guardar referencia al modelo antes de eliminar
            $modelType = $mediaItem->model_type;
            $modelId = $mediaItem->model_id;
            $fileName = $mediaItem->file_name;
            
            // Verificar que el modelo aún existe
            $model = $modelType::find($modelId);
            if (!$model) {
                return redirect()->back()
                    ->with('error', 'El modelo asociado no existe');
            }
            
            // Usar el método oficial de Spatie para eliminar el media
            $mediaItem->delete();
            
            // Verificar si el modelo sigue existiendo después de eliminar el documento
            $modelStillExists = $modelType::find($modelId);
            
            if (!$modelStillExists) {
                // Si el modelo fue eliminado, recrearlo
                Log::warning('El modelo fue eliminado al borrar el último documento. Recreando...', [
                    'model_type' => $modelType,
                    'model_id' => $modelId
                ]);
                
                // Recrear el modelo usando los datos originales
                $newModel = new $modelType($model->getAttributes());
                $newModel->id = $modelId;
                $newModel->save(['timestamps' => false]);
                
                return redirect()->back()
                    ->with('success', "Documento eliminado. Se recreó el registro automáticamente.");
            }
            
            return redirect()->back()
                ->with('success', "Documento {$fileName} eliminado correctamente");
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Vista previa de un documento
     * 
     * @param int $mediaId
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function preview($mediaId)
    {
        try {
            $mediaItem = Media::findOrFail($mediaId);
            
            // Obtener la ruta del archivo
            $path = $mediaItem->getPath();
            
            // Verificar que el archivo existe
            if (!file_exists($path)) {
                return redirect()->back()
                    ->with('error', 'El archivo no existe');
            }
            
            // Devolver el archivo para visualización
            return response()->file($path);
            
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento', [
                'media_id' => $mediaId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al previsualizar documento');
        }
    }
}
