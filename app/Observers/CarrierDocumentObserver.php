<?php

namespace App\Observers;

use App\Models\CarrierDocument;

class CarrierDocumentObserver
{
    /**
     * Handle the CarrierDocument "created" event.
     */
    public function created(CarrierDocument $carrierDocument): void
    {
        $this->checkCarrierDocumentsCompletion($carrierDocument);
    }

    /**
     * Handle the CarrierDocument "updated" event.
     */
    public function updated(CarrierDocument $carrierDocument): void
    {
        // Solo verificar si cambió el status
        if ($carrierDocument->wasChanged('status')) {
            $this->checkCarrierDocumentsCompletion($carrierDocument);
        }
    }

    /**
     * Handle the CarrierDocument "deleted" event.
     */
    public function deleted(CarrierDocument $carrierDocument): void
    {
        $this->checkCarrierDocumentsCompletion($carrierDocument);
    }

    /**
     * Verificar el estado de completado de documentos del carrier
     */
    private function checkCarrierDocumentsCompletion(CarrierDocument $carrierDocument): void
    {
        if ($carrierDocument->carrier) {
            $carrierDocument->carrier->checkDocumentsCompletion();
        }
    }

    /**
     * Handle the CarrierDocument "restored" event.
     */
    public function restored(CarrierDocument $carrierDocument): void
    {
        //
    }

    /**
     * Handle the CarrierDocument "force deleted" event.
     */
    public function forceDeleted(CarrierDocument $carrierDocument): void
    {
        //
    }
}
