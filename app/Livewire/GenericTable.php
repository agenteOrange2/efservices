<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GenericTable extends Component
{
    use WithPagination;

    public $model; // Modelo dinámico
    public $columns; // Columnas de la tabla
    public $search = ''; // Campo de búsqueda
    public $perPage = 10; // Resultados por página
    public $perPageOptions = [10, 20, 30, 100, 200]; // Opciones de paginación
    public $searchableFields = []; // Campos permitidos para búsqueda
    public $filters = []; // Filtros dinámicos
    public $customFilters = []; // Configuración de filtros personalizados
    public $sortField = 'id'; // Campo para ordenamiento
    public $sortDirection = 'desc'; // Dirección del ordenamiento
    public $selected = []; // Elementos seleccionados
    public $selectAll = false;
    public $openMenu = [];
    public $editRoute; // Ruta para edición
    public $deleteMethod = 'delete'; // Método de eliminación
    public $exportExcelRoute;
    public $exportPdfRoute;

    protected $listeners = [
        'resetPage',
        'filtersUpdated' => 'applyFilters',
        'exportToExcel',
        'exportToPdf'
    ];

    public function mount($model, $columns, $customFilters = [])
    {
        $this->model = $model;
        $this->columns = $columns;
        $this->customFilters = $customFilters;

        $modelInstance = new $this->model;

        // Ordenamiento inicial por id de forma descendente
        $this->sortField = 'id';
        $this->sortDirection = 'desc';

        // Inicializar valores predeterminados para filtros personalizados
        foreach ($customFilters as $key => $filter) {
            $this->filters[$key] = $filter['default'] ?? null;
        }

        // Inicializar el filtro de rango de fechas si el modelo tiene `created_at`
        if ($modelInstance->getConnection()->getSchemaBuilder()->hasColumn($modelInstance->getTable(), 'created_at')) {
            $this->filters['date_range'] = ['start' => null, 'end' => null];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage($value)
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        // Reiniciar filtros dinámicos personalizados
        foreach ($this->customFilters as $key => $filter) {
            $this->filters[$key] = $filter['default'] ?? null;
        }

        // Reiniciar filtro de rango de fechas si existe
        if (isset($this->filters['date_range'])) {
            $this->filters['date_range'] = ['start' => null, 'end' => null];
        }

        $this->dispatch('filtersUpdated', $this->filters);
        $this->resetPage();
    }

    public function applyFilters($filters)
    {
        $this->filters = array_merge($this->filters, $filters);
        $this->resetPage();
    }

    public function sortBy($field)
    {
        $this->sortField = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function deleteSingle($id)
    {
        if ($this->deleteMethod) {
            $this->model::findOrFail($id)->{$this->deleteMethod}();
        }
        $this->closeAllMenus();
    }

    public function exportToExcel()
    {
        $data = $this->model::all($this->columns);

        return Excel::download(new class($data, $this->columns) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;
            protected $columns;

            public function __construct($data, $columns)
            {
                $this->data = $data;
                $this->columns = $columns;
            }

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->columns;
            }
        }, 'export.xlsx');
    }

    public function exportToPdf()
    {
        $data = $this->model::all($this->columns);
        $title = ucfirst(class_basename($this->model)) . ' Export';

        $pdf = Pdf::loadView('admin.exports.export', [
            'data' => $data,
            'columns' => $this->columns,
            'title' => $title,
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            strtolower(class_basename($this->model)) . '.pdf'
        );
    }

    public function render()
    {
        $query = $this->model::query();
        $modelInstance = new $this->model;

        // Aplicar búsqueda
        if ($this->search && !empty($this->searchableFields)) {
            $query->where(function (Builder $q) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        // Aplicar filtro de rango de fechas si está disponible
        if (
            !empty($this->filters['date_range']['start']) &&
            !empty($this->filters['date_range']['end']) &&
            $modelInstance->getConnection()->getSchemaBuilder()->hasColumn($modelInstance->getTable(), 'created_at')
        ) {
            $query->whereBetween('created_at', [
                $this->filters['date_range']['start'],
                $this->filters['date_range']['end'],
            ]);
        }

        // Aplicar filtros personalizados si existen
        foreach ($this->customFilters as $key => $filter) {
            if (
                !empty($this->filters[$key]) &&
                $modelInstance->getConnection()->getSchemaBuilder()->hasColumn($modelInstance->getTable(), $key)
            ) {
                $query->where($key, $this->filters[$key]);
            }
        }

        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        $data = $query->paginate($this->perPage);

        return view('livewire.generic-table', [
            'data' => $data,
            'columns' => $this->columns,
        ]);
    }
}
