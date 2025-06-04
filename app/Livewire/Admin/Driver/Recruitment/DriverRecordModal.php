<?php

namespace App\Livewire\Admin\Driver\Recruitment;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Driver;
use App\Models\Admin\Driver\DriverTrainingSchool;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\Admin\Driver\DriverTrafficConviction as DriverTrafficViolation;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Driver\DriverCourse;
use App\Models\Admin\Driver\DriverTesting as DriverTest;

class DriverRecordModal extends Component
{
    use WithFileUploads;
    
    // Variables principales del modal
    public $showModal = false;
    public $recordType;
    public $recordId;
    public $recordAction; // Cambiado de action a recordAction para mantener consistencia
    public $recordData = [];
    public $recordDocuments = [];
    public $driverId;
    
    // Variables para archivos
    public $documents = [];
    public $tempDocuments = [];
    
    /**
     * Inicialización del componente
     */
    public function mount()
    {
        $this->driverId = session('current_driver_id') ?? 
            (Auth::user() && Auth::user()->currentDriver ? Auth::user()->currentDriver->id : null);
        
        Log::info("DriverRecordModal montado con driverId: {$this->driverId}");
    }
    
    /**
     * Maneja el evento desde Alpine.js
     * Con Alpine.js, cuando envías $dispatch('open-record-modal', {...}),
     * los datos llegan como un primer parámetro único.
     */
    #[On('open-record-modal')]
    public function handleOpenModal($data)
    {
        // Agregar logs detallados para depuración
        Log::info("DriverRecordModal::handleOpenModal recibido", [
            'data_received' => $data,
            'data_type' => gettype($data)
        ]);
        
        // Extraer los valores del objeto de datos
        $type = $data['type'] ?? null;
        $id = $data['id'] ?? null;
        $action = $data['action'] ?? 'view';
        $driverId = $data['driverId'] ?? null;
        
        Log::info("DriverRecordModal::handleOpenModal datos extraídos", [
            'type' => $type,
            'id' => $id,
            'action' => $action,
            'driverId' => $driverId
        ]);
        
        if (!$type) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Tipo de registro no especificado'
            ]);
            return;
        }
        
        // Llamar al método openModal con los parámetros extraídos
        $this->openModal($type, $id, $action, $driverId);
    }
    
    /**
     * Establece la visibilidad del modal
     */
    public function setModalVisibility($visible = true)
    {
        $this->showModal = $visible;
        Log::info("DriverRecordModal::setModalVisibility - Cambiando visibilidad a: {$visible}");
    }
        
    /**
     * Cierra el modal
     */
    public function closeModal()
    {
        $this->setModalVisibility(false);
        $this->reset(['recordType', 'recordId', 'recordAction', 'recordData', 'documents', 'tempDocuments', 'recordDocuments']);
        Log::info("DriverRecordModal::closeModal - Modal cerrado");
    }
    
    // El método openModal ya existe más abajo, por lo que no lo duplicamos
    
    /* Resto de openModal existente abajo */
    
    if ($id !== null && $action !== 'create') {
            $action = 'create';
        }
        
        Log::info("DriverRecordModal: Abriendo modal para tipo={$type}, id={$id}, accion={$action}, driverId={$driverId}");
        
        $this->openModal($type, $id, $action, $driverId);
    }
    
    /**
     * Abre el modal
     */
    public function openModal($recordType, $recordId = null, $action = 'view', $driverId = null)
    {
        $this->reset(['recordData', 'documents', 'tempDocuments', 'recordDocuments']);
        
        $this->recordType = $recordType;
        $this->recordId = $recordId;
        $this->recordAction = $action;
        
        if ($driverId) {
            $this->driverId = $driverId;
        } else if (!$this->driverId) {
            $this->driverId = session('current_driver_id') ?? 
                (Auth::user() && Auth::user()->currentDriver ? Auth::user()->currentDriver->id : null);
        }
        
        if ($recordId && in_array($action, ['edit', 'view'])) {
            $this->loadRecordData();
        }
        
        $this->showModal = true;
        
        Log::info("DriverRecordModal: Modal abierto para tipo={$recordType}, id={$recordId}, accion={$action}, driverId={$this->driverId}");
    }
    
    /**
     * Carga los datos del registro
     */
    private function loadRecordData()
    {
        $record = null;
        
        switch ($this->recordType) {
            case 'school':
            case 'training':
                $record = DriverTrainingSchool::find($this->recordId);
                break;
            case 'accident':
                $record = DriverAccident::find($this->recordId);
                break;
            case 'course':
                $record = DriverCourse::find($this->recordId);
                break;
            case 'inspection':
                $record = DriverInspection::find($this->recordId);
                break;
            case 'testing':
                $record = DriverTest::find($this->recordId);
                break;
            case 'traffic':
                $record = DriverTrafficViolation::find($this->recordId);
                break;
        }
        
        if ($record) {
            $this->recordData = $record->toArray();
            
            if (method_exists($record, 'getMedia')) {
                $mediaCollection = $this->getMediaCollectionName();
                $media = $record->getMedia($mediaCollection);
                
                $this->recordDocuments = $media->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->file_name,
                        'url' => $item->getUrl(),
                        'mime_type' => $item->mime_type
                    ];
                })->toArray();
            }
        }
    }
    
    /**
     * Obtiene el nombre de la colección de medios
     */
    private function getMediaCollectionName()
    {
        switch ($this->recordType) {
            case 'school':
            case 'training':
                return 'school_certificates';
            case 'accident':
                return 'accident_documents';
            case 'course':
                return 'course_certificates';
            case 'inspection':
                return 'inspection_documents';
            case 'testing':
                return 'testing_documents';
            case 'traffic':
                return 'traffic_documents';
            default:
                return 'documents';
        }
    }
    
    /**
     * Reglas de validación
     */
    protected function rules()
    {
        $rules = [
            'recordData' => 'required|array',
        ];
        
        switch ($this->recordType) {
            case 'school':
            case 'training':
                $rules['recordData.school_name'] = 'required|string|max:255';
                $rules['recordData.training_type'] = 'required|string';
                $rules['recordData.city'] = 'required|string|max:255';
                $rules['recordData.state'] = 'required|string|max:255';
                $rules['recordData.date_start'] = 'required|date';
                $rules['recordData.date_end'] = 'required|date|after_or_equal:recordData.date_start';
                break;
                
            case 'accident':
                $rules['recordData.date'] = 'required|date';
                $rules['recordData.location'] = 'required|string|max:255';
                $rules['recordData.description'] = 'required|string';
                break;
                
            case 'course':
                $rules['recordData.course_name'] = 'required|string|max:255';
                $rules['recordData.date'] = 'required|date';
                break;
                
            case 'inspection':
                $rules['recordData.date'] = 'required|date';
                $rules['recordData.location'] = 'required|string|max:255';
                $rules['recordData.result'] = 'required|string';
                break;
                
            case 'testing':
                $rules['recordData.test_type'] = 'required|string';
                $rules['recordData.date'] = 'required|date';
                $rules['recordData.result'] = 'required|string';
                break;
                
            case 'traffic':
                $rules['recordData.date'] = 'required|date';
                $rules['recordData.location'] = 'required|string|max:255';
                $rules['recordData.violation_type'] = 'required|string';
                break;
        }
        
        return $rules;
    }
    
    /**
     * Guarda el registro
     */
    public function saveRecord()
    {
        try {
            $this->validate();
        } catch (\Exception $e) {
            Log::error("Error de validación: " . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error de validación: ' . $e->getMessage()
            ]);
            return;
        }
        
        $modelClass = $this->getModelClass();
        
        if (!$modelClass) {
            Log::error("Tipo de registro inválido: {$this->recordType}");
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Tipo de registro inválido'
            ]);
            return;
        }
        
        DB::beginTransaction();
        
        try {
            if ($this->recordId) {
                $record = $modelClass::find($this->recordId);
                
                if (!$record) {
                    throw new \Exception("No se encontró el registro con ID {$this->recordId}");
                }
                
                $record->update($this->recordData);
                Log::info("Registro actualizado: tipo={$this->recordType}, id={$this->recordId}");
            } else {
                if (!$this->driverId) {
                    throw new \Exception("No se especificó el ID del conductor");
                }
                
                $this->recordData['driver_id'] = $this->driverId;
                $record = $modelClass::create($this->recordData);
                $this->recordId = $record->id;
                Log::info("Nuevo registro creado: tipo={$this->recordType}, id={$record->id}");
            }
            
            $mediaCollection = $this->getMediaCollectionName();
            $this->processFiles($record, $mediaCollection);
            
            DB::commit();
            
            $this->dispatch('recordSaved', [
                'type' => $this->recordType,
                'id' => $record->id
            ]);
            
            $this->closeModal();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro guardado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error al guardar registro: " . $e->getMessage());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene la clase del modelo
     */
    private function getModelClass()
    {
        switch ($this->recordType) {
            case 'school':
            case 'training':
                return DriverTrainingSchool::class;
            case 'accident':
                return DriverAccident::class;
            case 'course':
                return DriverCourse::class;
            case 'inspection':
                return DriverInspection::class;
            case 'testing':
                return DriverTest::class;
            case 'traffic':
                return DriverTrafficViolation::class;
            default:
                return null;
        }
    }
    
    /**
     * Maneja la carga de archivos
     */
    public function handleFileUploaded($data)
    {
        $fileData = [
            'id' => $data['id'] ?? uniqid(),
            'name' => $data['name'] ?? $data['original_name'] ?? 'documento',
            'original_name' => $data['original_name'] ?? $data['name'] ?? 'documento',
            'path' => $data['path'] ?? $data['filename'] ?? '',
            'mime_type' => $data['mime_type'] ?? $data['type'] ?? 'application/octet-stream',
            'size' => $data['size'] ?? 0,
            'temp' => true
        ];
        
        $this->tempDocuments[] = $fileData;
    }
    
    /**
     * Maneja la eliminación de archivos
     */
    public function handleFileRemoved($tempId)
    {
        $this->tempDocuments = array_filter($this->tempDocuments, function($doc) use ($tempId) {
            return $doc['id'] !== $tempId;
        });
    }
    
    /**
     * Elimina un documento existente
     */
    public function removeExistingDocument($documentId)
    {
        try {
            // Usar el modelo Spatie Media en lugar de DB::table directamente
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($documentId);
            
            if ($media) {
                // Esto maneja tanto la eliminación del registro como el archivo físico
                $media->delete();
                
                // Actualiza la lista de documentos en la interfaz
                $this->recordDocuments = array_filter($this->recordDocuments, function($doc) use ($documentId) {
                    return $doc['id'] != $documentId;
                });
                
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Documento eliminado correctamente'
                ]);
                
                // Agregar log para depuración
                Log::info("Documento eliminado correctamente", ['media_id' => $documentId]);
            } else {
                Log::warning("Intento de eliminar media inexistente", ['media_id' => $documentId]);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se encontró el documento a eliminar'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error al eliminar media: " . $e->getMessage(), [
                'media_id' => $documentId,
                'exception' => $e
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Elimina un documento temporal
     */
    public function removeTemporaryDocument($index)
    {
        if (isset($this->tempDocuments[$index])) {
            $tempPath = storage_path('app/temp/' . $this->tempDocuments[$index]['path']);
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            unset($this->tempDocuments[$index]);
            $this->tempDocuments = array_values($this->tempDocuments);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Documento temporal eliminado'
            ]);
        }
    }
    
    /**
     * Procesa los archivos
     */
    private function processFiles($record, $collection)
    {
        if (empty($this->tempDocuments)) {
            return;
        }
        
        foreach ($this->tempDocuments as $tempDoc) {
            $tempPath = storage_path('app/temp/' . $tempDoc['path']);
            
            if (file_exists($tempPath)) {
                $record->addMedia($tempPath)
                       ->withCustomProperties([
                           'original_filename' => $tempDoc['original_name'],
                           'size' => $tempDoc['size'] ?? 0,
                           'mime_type' => $tempDoc['mime_type'] ?? 'application/octet-stream'
                       ])
                       ->toMediaCollection($collection);
            }
        }
    }
    
    /**
     * Cierra el modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['recordData', 'documents', 'tempDocuments']);
    }
    
    /**
     * Renderiza el componente
     */
    public function render()
    {
        $typeMap = [
            'training' => 'school',
            'school' => 'school',
            'accident' => 'accident',
            'traffic' => 'traffic',
            'course' => 'course',
            'testing' => 'testing',
            'inspection' => 'inspection'
        ];
        
        $recordType = 'school';
        
        if ($this->recordType && isset($typeMap[$this->recordType])) {
            $recordType = $typeMap[$this->recordType];
        }
        
        Log::info("Renderizando modal directo para tipo: {$this->recordType} (mapeado a: {$recordType})");
        
        // Simplificado: Renderizamos directamente la vista completa sin vistas parciales
        return view('livewire.admin.driver.recruitment.driver-record-modal', [
            'formType' => $recordType
        ]);
    }
}
