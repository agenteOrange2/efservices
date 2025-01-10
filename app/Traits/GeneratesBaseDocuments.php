<?php

namespace App\Traits;

use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;

trait GeneratesBaseDocuments
{
    /**
     * Genera documentos base para un Carrier.
     */
    public function generateBaseDocuments(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();

        foreach ($documentTypes as $type) {
            // Crear o encontrar un documento base para el carrier
            $carrierDocument = CarrierDocument::firstOrCreate([
                'carrier_id' => $carrier->id,
                'document_type_id' => $type->id,
            ], [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]);

            // Asociar medios si existen documentos predeterminados
            $defaultMedia = $type->getFirstMedia('default_documents');
            if ($defaultMedia && !$carrierDocument->getFirstMedia('carrier_documents')) {
                $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
            }
        }
    }
}
