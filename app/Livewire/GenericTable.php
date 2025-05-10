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
    public $searchableFields = []; // Campos permitidos para búsqueda
    public $perPage = 10; // Resultados por página
    public $perPageOptions = [10, 20, 30, 100, 200]; // Opciones de paginación
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
        'clearFilters' => 'clearFilters',
        'exportToExcel',
        'exportToPdf',
        'search-updated' => 'updateSearch'
    ];

    public function mount($model, $columns, $searchableFields = [], $customFilters = [])
    {
        $this->model = $model;
        $this->columns = $columns;
        $this->searchableFields = $searchableFields;
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

    public function updateSearch($search)
    {
        $this->search = $search;
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

        // Emitir evento al hijo para reiniciar filtros
        $this->dispatch('filtersUpdated', $this->filters);

        $this->resetPage();
    }


    public function applyFilters($filters)
    {
        logger()->info('Filtros aplicados:', $filters);

        $this->filters = $filters;
        $this->resetPage(); // Reiniciar paginación
    }


    public function sortBy($field)
    {
        $this->sortField = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function deleteSingle($id)
    {
        logger()->info('deleteSingle called', ['id' => $id]); // Log antes de eliminar
        
        $this->model::findOrFail($id)->delete();
        
        logger()->info('Dispatching notify event for single delete', [
            'type' => 'success',
            'message' => 'Record deleted successfully!',
            'details' => "The record with ID $id has been removed.",
        ]); // Log antes del dispatch
    
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Record deleted successfully!',
            'details' => "The record with ID $id has been removed.",
        ]);
        
        logger()->info('Notify event dispatched for single delete'); // Log después del dispatch
    }

    public function closeAllMenus()
    {
        $this->openMenu = [];
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

    public function deleteSelected()
    {
        logger()->info('deleteSelected called', ['selected' => $this->selected]); // Log inicial
    
        if (count($this->selected) > 0) {
            foreach ($this->selected as $id) {
                logger()->info('Deleting record', ['id' => $id]); // Log antes de cada eliminación
                $this->model::findOrFail($id)->delete();
            }
    
            $deletedCount = count($this->selected);
            $this->selected = [];
            $this->selectAll = false;
    
            logger()->info('Dispatching notify event for multiple delete', [
                'type' => 'success',
                'message' => "$deletedCount records deleted successfully!",
                'details' => 'The selected records have been removed.',
            ]); // Log antes del dispatch
    
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "$deletedCount records deleted successfully!",
                'details' => 'The selected records have been removed.',
            ]);
    
            logger()->info('Notify event dispatched for multiple delete'); // Log después del dispatch
        } else {
            logger()->info('No records selected for deletion'); // Log en caso de no haber selección
    
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'No records selected!',
                'details' => 'Please select at least one record to delete.',
            ]);
    
            logger()->info('Notify event dispatched for no records selected'); // Log después del dispatch
        }
    }
    
    
    public function render()
    {
        $query = $this->model::query();
        $modelInstance = new $this->model;

        // Aplicar búsqueda
        if (!empty($this->search) && !empty($this->searchableFields)) {
            $query->where(function (Builder $q) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        // Aplicar filtros de rango de fechas
        if (!empty($this->filters['date_range']['start']) && !empty($this->filters['date_range']['end'])) {
            $query->whereBetween('created_at', [
                $this->filters['date_range']['start'],
                $this->filters['date_range']['end'],
            ]);
        }

        // Aplicar otros filtros personalizados
        foreach ($this->filters as $key => $value) {
            if ($key !== 'date_range' && !is_null($value)) {
                $query->where($key, $value);
            }
        }

        // Aplicar filtros personalizados si existen
        foreach ($this->customFilters as $key => $filter) {
            if (
                !is_null($this->filters[$key]) &&
                $modelInstance->getConnection()->getSchemaBuilder()->hasColumn($modelInstance->getTable(), $key)
            ) {
                // Si es el filtro de `status`, convertir a 0 o 1
                if ($key === 'status' && in_array($this->filters[$key], [0, 1, 3], true)) {
                    $query->where($key, $this->filters[$key]);
                } else {
                    $query->where($key, $this->filters[$key]);
                }
            }
        }

        $this->dispatch('resetFilters', $this->filters);

        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        $data = $query->paginate($this->perPage);

        return view('livewire.generic-table', [
            'data' => $data,
            'columns' => $this->columns,
        ]);
    }
}
