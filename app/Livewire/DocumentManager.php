<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Carrier;

class DocumentManager extends Component
{
    public $carrier;

    public function mount(Carrier $carrier)
    {
        $this->carrier = $carrier->load('documents'); // Relación con documentos
    }

    public function render()
    {
        return view('livewire.document-manager', [
            'documents' => $this->carrier->documents,
        ]);
    }
}
