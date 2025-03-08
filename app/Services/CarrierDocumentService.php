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

            $carrierDocument->update(['status' => CarrierDocument::STATUS_IN_PROCCESS]);
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
        // Procesar carriers en chunks para evitar problemas de memoria
        Carrier::chunk(100, function ($carriers) use ($documentType) {
            foreach ($carriers as $carrier) {
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

                $defaultMedia = $documentType->getFirstMedia('default_documents');
                if ($defaultMedia && !$carrierDocument->getFirstMedia('carrier_documents')) {
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
}