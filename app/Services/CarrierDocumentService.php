<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\DocumentType;
use App\Models\CarrierDocument;

class CarrierDocumentService
{
    /**
     * Genera documentos base para un carrier
     */
    public function generateBaseDocuments(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();

        foreach ($documentTypes as $type) {
            $carrierDocument = CarrierDocument::firstOrCreate(
                [
                    'carrier_id' => $carrier->id,
                    'document_type_id' => $type->id,
                ],
                [
                    'status' => CarrierDocument::STATUS_PENDING,
                    'date' => now(),
                ]
            );

            $defaultMedia = $type->getFirstMedia('default_documents');

            if ($defaultMedia && !$carrierDocument->getFirstMedia('carrier_documents')) {
                $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
            }
        }
    }

    /**
     * Sube un documento para un carrier
     */
    public function uploadDocument(Carrier $carrier, DocumentType $documentType, $file)
    {
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

        if ($file) {
            $carrierDocument->clearMediaCollection('carrier_documents');
            $carrierDocument
                ->addMedia($file)
                ->usingFileName(strtolower(str_replace(' ', '_', $documentType->name)) . '.pdf')
                ->toMediaCollection('carrier_documents');

            // Mantener el estado como PENDING para que el admin pueda revisar y aprobar
            $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
        }

        return $carrierDocument;
    }

    /**
     * Actualiza el estado de un documento
     */
    public function updateDocumentStatus(CarrierDocument $document, int $status, ?string $notes = null)
    {
        return $document->update([
            'status' => $status,
            'notes' => $notes
        ]);
    }

        /**
     * Distribuye un documento por default a todos los carriers
     */
    public function distributeDefaultDocument(DocumentType $documentType)
    {
        // Verificar si el tipo de documento tiene un archivo por defecto
        $hasDefaultDocument = $documentType->getFirstMedia('default_documents') !== null;
        
        // Procesar carriers en chunks para evitar problemas de memoria
        Carrier::chunk(100, function ($carriers) use ($documentType, $hasDefaultDocument) {
            foreach ($carriers as $carrier) {
                // Buscar si ya existe un documento para este carrier y tipo
                $carrierDocument = CarrierDocument::where([
                    'carrier_id' => $carrier->id,
                    'document_type_id' => $documentType->id,
                ])->first();
                
                // Si no existe, crear uno nuevo
                if (!$carrierDocument) {
                    $carrierDocument = CarrierDocument::create([
                        'carrier_id' => $carrier->id,
                        'document_type_id' => $documentType->id,
                        'status' => CarrierDocument::STATUS_PENDING,
                        'date' => now(),
                    ]);
                }
                
                // Si hay un documento por defecto disponible y el carrier no ha subido su propio documento
                if ($hasDefaultDocument && !$carrierDocument->getFirstMedia('carrier_documents')) {
                    // Asegurarse de que el estado sea pendiente para que el administrador pueda aprobarlo
                    if ($carrierDocument->status !== CarrierDocument::STATUS_PENDING) {
                        $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
                    }
                }
                // Si no hay documento por defecto y el carrier no ha subido su propio documento
                elseif (!$hasDefaultDocument && !$carrierDocument->getFirstMedia('carrier_documents')) {
                    // Asegurarse de que el estado sea pendiente
                    $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
                }
            }
        });
    }

        /**
     * Sincroniza nuevos tipos de documentos con carriers existentes
     */
    public function syncNewDocumentTypes()
    {
        $documentTypes = DocumentType::all();
        
        Carrier::chunk(100, function ($carriers) use ($documentTypes) {
            foreach ($carriers as $carrier) {
                foreach ($documentTypes as $type) {
                    $this->generateBaseDocuments($carrier);
                }
            }
        });
    }

    /**
     * Acepta un documento por defecto para un carrier
     */
    public function toggleDefaultDocument(Carrier $carrier, int $documentTypeId)
    {
        $documentType = DocumentType::findOrFail($documentTypeId);
        
        // Verificar si existe un documento por defecto
        $defaultMedia = $documentType->getFirstMedia('default_documents');
        if (!$defaultMedia) {
            throw new \Exception('No default document available for this document type.');
        }
        
        // Buscar o crear el documento del carrier
        $carrierDocument = CarrierDocument::firstOrCreate(
            [
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentTypeId,
            ],
            [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]
        );
        
        // Verificar si el carrier ya tiene un documento personalizado
        $hasCustomDocument = $carrierDocument->getFirstMedia('carrier_documents');
        
        if ($hasCustomDocument) {
            // Si ya tiene un documento personalizado, no permitir aceptar el predeterminado
            throw new \Exception('You already have a custom document uploaded. Please delete it first if you want to use the default document.');
        }
        
        // Copiar el documento por defecto al carrier
        $carrierDocument->copyMedia($defaultMedia->getPath())
            ->usingFileName($defaultMedia->file_name)
            ->toMediaCollection('carrier_documents');
        
        // Actualizar el estado a pendiente para revisión del admin
        $carrierDocument->update([
            'status' => CarrierDocument::STATUS_PENDING,
            'notes' => 'Default document accepted by carrier on ' . now()->format('Y-m-d H:i:s')
        ]);
        
        return [
            'success' => true,
            'message' => 'Default document accepted successfully. It will be reviewed by the administrator.',
            'document' => $carrierDocument
        ];
    }

    /**
     * Elimina un documento de un carrier
     */
    public function deleteDocument(Carrier $carrier, int $mediaId)
    {
        // Buscar el media en los documentos del carrier
        $media = null;
        foreach ($carrier->documents as $document) {
            $media = $document->media()->where('id', $mediaId)->first();
            if ($media) {
                break;
            }
        }
        
        if (!$media) {
            throw new \Exception('Document not found or you do not have permission to delete it.');
        }
        
        // Eliminar el archivo
        $media->delete();
        
        // Actualizar el estado del documento a pendiente si no tiene más archivos
        $carrierDocument = $media->model;
        if ($carrierDocument && !$carrierDocument->getFirstMedia('carrier_documents')) {
            $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
        }
        
        return true;
    }

    /**
     * Obtiene el progreso de documentos de un carrier
     */
    public function getDocumentProgress(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();
        $carrierDocuments = $carrier->documents()->with('documentType')->get();
        
        $totalDocuments = $documentTypes->count();
        $completedDocuments = 0;
        $pendingDocuments = 0;
        $approvedDocuments = 0;
        $rejectedDocuments = 0;
        
        foreach ($documentTypes as $type) {
            $carrierDocument = $carrierDocuments->where('document_type_id', $type->id)->first();
            
            if ($carrierDocument) {
                switch ($carrierDocument->status) {
                    case CarrierDocument::STATUS_APPROVED:
                        $approvedDocuments++;
                        $completedDocuments++;
                        break;
                    case CarrierDocument::STATUS_REJECTED:
                        $rejectedDocuments++;
                        break;
                    case CarrierDocument::STATUS_IN_PROCCESS:
                    case CarrierDocument::STATUS_PENDING:
                    default:
                        $pendingDocuments++;
                        break;
                }
            } else {
                $pendingDocuments++;
            }
        }
        
        $progressPercentage = $totalDocuments > 0 ? round(($completedDocuments / $totalDocuments) * 100, 2) : 0;
        
        return [
            'total' => $totalDocuments,
            'completed' => $completedDocuments,
            'pending' => $pendingDocuments,
            'approved' => $approvedDocuments,
            'rejected' => $rejectedDocuments,
            'progress_percentage' => $progressPercentage
        ];
    }

    /**
     * Obtiene los documentos mapeados de un carrier
     */
    public function getMappedDocuments(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();
        $carrierDocuments = $carrier->documents()->with('documentType')->get();
        
        $mappedDocuments = [];
        
        foreach ($documentTypes as $type) {
            $carrierDocument = $carrierDocuments->where('document_type_id', $type->id)->first();
            
            $mappedDocuments[] = [
                'type' => $type,
                'document' => $carrierDocument,
                'status' => $carrierDocument ? $carrierDocument->status : 'pending',
                'has_file' => $carrierDocument && $carrierDocument->file_path,
                'has_default' => $type->default_document_path !== null,
            ];
        }
        
        return $mappedDocuments;
    }
}