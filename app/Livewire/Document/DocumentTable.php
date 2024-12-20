<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;

class DocumentTable extends Component
{

    use WithPagination;

    public $search = '';
    public $filters = [
        'status' => null, // Filtro por estado (approved, pending, etc.)
        'carrier' => null, // Filtro por nombre del carrier
        'date_range' => ['start' => null, 'end' => null], // Rango de fechas
    ];
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        // Obtener documentos con filtros
        $query = CarrierDocument::with(['carrier', 'documentType'])
            ->when($this->search, function ($q) {
                $q->whereHas('carrier', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], function ($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when($this->filters['carrier'], function ($q) {
                $q->whereHas('carrier', function ($query) {
                    $query->where('id', $this->filters['carrier']);
                });
            })
            ->when($this->filters['date_range']['start'] && $this->filters['date_range']['end'], function ($q) {
                $q->whereBetween('created_at', [
                    $this->filters['date_range']['start'],
                    $this->filters['date_range']['end']
                ]);
            });

        $documents = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Calcular el progreso de los carriers
        $documents->getCollection()->transform(function ($document) {
            $totalDocuments = DocumentType::count();
            $approvedDocuments = CarrierDocument::where('carrier_id', $document->carrier_id)
                ->where('status', CarrierDocument::STATUS_APPROVED)
                ->count();

            $document->progress = $totalDocuments > 0 ? ($approvedDocuments / $totalDocuments) * 100 : 0;

            return $document;
        });

        return view('livewire.document.document-table', [
            'documents' => $documents,
            'carriers' => Carrier::all(), // Opcional: para filtros relacionados
        ]);
    }
}
