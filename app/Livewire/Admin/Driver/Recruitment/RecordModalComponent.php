<?php

namespace App\Livewire\Admin\Driver\Recruitment;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Driver;
// Importamos los modelos con las rutas correctas según la estructura del proyecto
use App\Models\Admin\Driver\DriverTrainingSchool;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\Admin\Driver\DriverTrafficConviction as DriverTrafficViolation;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Driver\DriverCourse;
use App\Models\Admin\Driver\DriverTesting as DriverTest;

class RecordModalComponent extends Component
{
    use WithFileUploads;
    
    // Variables principales del modal
    public $showRecordModal = false;
    public $recordType; // school, accident, course, inspection, testing, traffic
    public $recordId;
    public $recordAction; // create, edit, view
    public $recordData = [];
    public $recordDocuments = [];
    public $driverId;
    
    // Variable para la carga de documentos
    public $documents = [];
    public $tempDocuments = [];
    
    /**
     * Constructor explícito sin dependencias
     */
    public function __construct()
    {
        // Llamada al constructor padre sin pasar argumentos
        parent::__construct();
    }
    
    /**
     * Método de inicialización del componente - no recibe parámetros
     */
    public function mount()
    {
        $this->driverId = session('current_driver_id') ?? (Auth::user() && Auth::user()->currentDriver ? Auth::user()->currentDriver->id : null);
        Log::info("RecordModalComponent montado con driverId: {$this->driverId}");
    }
    
    // Listeners para eventos
    protected $listeners = [
        'openRecordModal' => 'openModal',
        'closeRecordModal' => 'closeModal',
        'fileUploaded' => 'handleFileUploaded',
        'fileRemoved' => 'handleFileRemoved',
        'open-record-modal' => 'handleOpenModal'
    ];
    
    // Método para manejar el evento desde Alpine.js
    public function handleOpenModal($data)
    {
        $type = $data['type'] ?? null;
        $id = $data['id'] ?? null;
        $action = $data['action'] ?? 'view';
        $driverId = $data['driverId'] ?? null;
        
        // Asegurar que tenemos valores válidos
        if (!$type) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Tipo de registro no especificado'
            ]);
            return;
        }
        
        // Si id es null y action no es create, cambiamos a create
        if ($id === null && $action !== 'create') {
            $action = 'create';
        }
        
        // Registramos la apertura para debug
        Log::info("Abriendo modal: tipo={$type}, id={$id}, accion={$action}, driverId={$driverId}");
        
        $this->openModal($type, $id, $action, $driverId);
    }

    // Reglas de validación
    protected function rules()
    {
        $rules = [
            'recordData' => 'required|array',
        ];
        
        // Reglas específicas según el tipo de registro
        switch ($this->recordType) {
            case 'school':
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
                $rules['recordData.inspection_type'] = 'required|string';
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

    // Método para abrir el modal - simplificado para evitar problemas de dependencias
    public function openModal($recordType, $recordId = null, $action = 'view', $driverId = null)
    {
        // Resetear todas las propiedades antes de abrir el modal
        $this->reset(['recordData', 'documents', 'tempDocuments', 'recordDocuments']);
        
        // Establecer las propiedades básicas
        $this->recordType = $recordType;
        $this->recordId = $recordId;
        $this->recordAction = $action;
        
        // Establecer el ID del conductor
        if ($driverId) {
            $this->driverId = $driverId;
        } else if (!$this->driverId) {
            $this->driverId = session('current_driver_id') ?? 
                (Auth::user() && Auth::user()->currentDriver ? Auth::user()->currentDriver->id : null);
        }
        
        // Si estamos editando o viendo un registro existente, cargamos sus datos
        if ($recordId && in_array($action, ['edit', 'view'])) {
            $this->loadRecordData();
        }
        
        // Mostrar el modal
        $this->showRecordModal = true;
        
        // Logging
        Log::info("Modal abierto: tipo={$recordType}, id={$recordId}, accion={$action}, driverId={$this->driverId}");
    }

    // Cargar datos del registro
    private function loadRecordData()
    {
        switch ($this->recordType) {
            case 'school':
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
                $record = DriverTrafficViolation::find($this->recordId); // Usando el alias DriverTrafficViolation para DriverTrafficConviction
                break;
            default:
                $record = null;
        }

        if ($record) {
            $this->recordData = $record->toArray();
            
            // Cargamos los documentos relacionados
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
    
    // Obtener el nombre de la colección de medios según el tipo de registro
    private function getMediaCollectionName()
    {
        switch ($this->recordType) {
            case 'school':
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
     * Guardar el registro - método refactorizado para evitar problemas de inyección de dependencias
     */
    public function saveRecord()
    {
        // Validar los datos del formulario
        try {
            $this->validate();
        } catch (\Exception $e) {
            Log::error("Error de validación al guardar registro: " . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error en la validación: ' . $e->getMessage()
            ]);
            return;
        }
        
        // Obtener la clase del modelo
        $modelClass = $this->getModelClass();
        
        if (!$modelClass) {
            Log::error("Tipo de registro inválido: {$this->recordType}");
            $this->addError('recordType', 'Tipo de registro inválido');
            return;
        }

        // Iniciar transacción
        DB::beginTransaction();
        
        try {
            // Crear o actualizar el registro
            if ($this->recordId) {
                // Actualización
                $record = $modelClass::find($this->recordId);
                
                if (!$record) {
                    throw new \Exception("No se encontró el registro con ID {$this->recordId}");
                }
                
                $record->update($this->recordData);
                Log::info("Registro actualizado: tipo={$this->recordType}, id={$this->recordId}");
            } else {
                // Creación
                if (!$this->driverId) {
                    throw new \Exception("No se especificó el ID del conductor");
                }
                
                $this->recordData['driver_id'] = $this->driverId;
                $record = $modelClass::create($this->recordData);
                $this->recordId = $record->id;
                Log::info("Nuevo registro creado: tipo={$this->recordType}, id={$record->id}");
            }

            // Procesar archivos temporales
            $mediaCollection = $this->getMediaCollectionName();
            $this->processLivewireFiles($record, $mediaCollection);
            
            // Confirmar transacción
            DB::commit();
            
            // Emitir evento
            $this->dispatch('recordSaved', [
                'type' => $this->recordType,
                'id' => $record->id
            ]);
            
            // Cerrar modal
            $this->closeModal();
            
            // Notificar éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro guardado correctamente'
            ]);
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollback();
            Log::error("Error al guardar registro: " . $e->getMessage());
            $this->addError('save', 'Error al guardar: ' . $e->getMessage());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    // Obtener la clase del modelo según el tipo de registro
    private function getModelClass()
    {
        switch ($this->recordType) {
            case 'school':
                return DriverTrainingSchool::class;
            case 'accident':
                return DriverAccident::class;
            case 'course':
                return DriverCourse::class;
            case 'inspection':
                return DriverInspection::class;
            case 'testing':
                return DriverTest::class; // DriverTesting es la clase real
            case 'traffic':
                return DriverTrafficViolation::class; // DriverTrafficConviction es la clase real
            default:
                return null;
        }
    }

    // Manejar la carga de archivos (evento fileUploaded del componente FileUploader)
    public function handleFileUploaded($data)
    {
        // Adaptar a la estructura esperada por el sistema
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

    // Manejar la eliminación de archivos (evento fileRemoved del componente FileUploader)
    public function handleFileRemoved($tempId)
    {
        $this->tempDocuments = array_filter($this->tempDocuments, function($doc) use ($tempId) {
            return $doc['id'] !== $tempId;
        });
    }

    // Eliminar un documento existente
    public function removeExistingDocument($documentId)
    {
        // Usamos el método seguro para eliminar documentos que mencionamos en las memorias
        DB::table('media')->where('id', $documentId)->delete();
        
        // Actualizamos la lista de documentos
        $this->recordDocuments = array_filter($this->recordDocuments, function($doc) use ($documentId) {
            return $doc['id'] != $documentId;
        });
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Document removed successfully'
        ]);
    }

    // Método para eliminar documentos temporales
    public function removeTemporaryDocument($index)
    {
        if (isset($this->tempDocuments[$index])) {
            // Eliminar el archivo temporal del sistema de archivos si existe
            $tempPath = storage_path('app/temp/' . $this->tempDocuments[$index]['path']);
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            // Eliminar el registro del array
            unset($this->tempDocuments[$index]);
            $this->tempDocuments = array_values($this->tempDocuments); // Reindexar el array
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Documento temporal eliminado'
            ]);
        }
    }
    
    // Procesar los archivos cargados por Livewire
    private function processLivewireFiles($record, $collection)
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

    // Cerrar el modal
    public function closeModal()
    {
        $this->showRecordModal = false;
        $this->reset(['recordData', 'documents', 'tempDocuments']);
    }

    /**
     * Renderiza el componente
     * Método simplificado para evitar problemas de inyección de dependencias
     */
    public function render()
    {
        // Vista principal del modal
        $viewName = 'livewire.admin.driver.recruitment.record-modal';
        
        // Mapear los tipos de registros a los nombres de vista correctos
        $typeMap = [
            'training' => 'school', // Para compatibilidad con el código existente
            'school' => 'school',
            'accident' => 'accident',
            'traffic' => 'traffic',
            'course' => 'course',
            'testing' => 'testing',
            'inspection' => 'inspection'
        ];
        
        // Determinar el tipo de plantilla a cargar
        $recordType = 'school'; // Valor por defecto para evitar errores
        
        if ($this->recordType && isset($typeMap[$this->recordType])) {
            $recordType = $typeMap[$this->recordType];
        }
        
        // Log para debugging
        Log::info("Renderizando modal para tipo: {$this->recordType} (mapeado a: {$recordType})");
        
        // Pasar solo lo necesario a la vista
        return view($viewName, [
            'viewTemplate' => "livewire.admin.driver.recruitment.record-modal-{$recordType}"
        ]);
    }
}
