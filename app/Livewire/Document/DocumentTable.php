<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;
use Carbon\Carbon;

class DocumentTable extends Component
{
    use WithPagination;

    public $search = ''; // Campo de búsqueda
    public $filters = [
        'status' => null, // Estado del carrier: active, pending
        'date_range' => ['start' => null, 'end' => null], // Rango de fechas
    ];
    public $perPage = 10; // Resultados por página
    public $sortField = 'id'; // Campo de ordenamiento
    public $sortDirection = 'asc'; // Dirección del ordenamiento

    protected $listeners = ['filtersUpdated', 'updateDateRange']; // Listener para actualizar filtros

    public function updating($property)
    {
        if (in_array($property, ['search', 'filters', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function updateDateRange($dates)
    {
        // Actualizar el rango de fechas en los filtros
        $this->filters['date_range']['start'] = $dates['start'];
        $this->filters['date_range']['end'] = $dates['end'];

        // Emitir los filtros actualizados
        $this->dispatch('filtersUpdated', $this->filters);
        $this->resetPage();
    }

    public function applyFilters($filters)
    {
        $this->filters = $filters; // Aplica los filtros directamente
        $this->resetPage();
    }

    public function resetFilters()
    {

        $this->filters = [
            'status' => null,
            'date_range' => ['start' => null, 'end' => null],
        ];
        
        $this->reset(['search', 'filters']);
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
        $totalDocuments = DocumentType::count();
        $query = Carrier::with(['documents', 'userCarriers']);

        // Filtro de búsqueda
        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Filtro de estado
        if (!empty($this->filters['status'])) {
            $query->whereHas('documents', function ($subQuery) use ($totalDocuments) {
                if ($this->filters['status'] === 'active') {
                    $subQuery->havingRaw('COUNT(*) = ?', [$totalDocuments])
                             ->where('status', CarrierDocument::STATUS_APPROVED);
                } elseif ($this->filters['status'] === 'pending') {
                    $subQuery->where('status', CarrierDocument::STATUS_PENDING);
                }
            });
        }

        // Filtro de rango de fechas
        if (!empty($this->filters['date_range']['start']) && !empty($this->filters['date_range']['end'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['date_range']['start'])->startOfDay(),
                Carbon::parse($this->filters['date_range']['end'])->endOfDay(),
            ]);
        }

        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        // Paginación
        $carriers = $query->paginate($this->perPage);

        // Transformar datos adicionales
        $carriers->getCollection()->transform(function ($carrier) use ($totalDocuments) {
            $approvedDocuments = $carrier->documents->where('status', CarrierDocument::STATUS_APPROVED)->count();

            $carrier->completion_percentage = $totalDocuments > 0
                ? ($approvedDocuments / $totalDocuments) * 100
                : 0;

            $carrier->document_status = $approvedDocuments === $totalDocuments ? 'active' : 'pending';

            return $carrier;
        });

        return view('livewire.document.document-table', [
            'carriers' => $carriers,
        ]);
    }
}
