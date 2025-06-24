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
    public $showRoute; // Ruta para visualización de detalles (usando ID)
    public $showSlugRoute; // Ruta para visualización de detalles (usando slug)
    public $deleteRoute; // Ruta para eliminación
    public $deleteMethod = 'delete'; // Método de eliminación
    public $exportExcelRoute;
    public $exportPdfRoute;
    
    // Variables para confirmación de eliminación
    public $showDeleteConfirmation = false;
    public $recordToDelete = null;

    protected $listeners = [
        'resetPage',
        'filtersUpdated' => 'applyFilters',
        'clearFilters' => 'clearFilters',
        'exportToExcel',
        'exportToPdf',
        'search-updated' => 'updateSearch',
        'confirmDeleteSingle' => 'confirmDeleteSingle',
        'confirmDeleteSelected' => 'confirmDeleteSelected',
        'updateDateRange' => 'updateDateRange'
    ];

    public function mount($model, $columns, $searchableFields = [], $customFilters = [], $showRoute = null, $editRoute = null, $deleteRoute = null, $exportExcelRoute = null, $exportPdfRoute = null)
    {
        $this->model = $model;
        $this->columns = $columns;
        $this->searchableFields = $searchableFields;
        $this->customFilters = $customFilters;
        
        // Inicializar rutas
        $this->showRoute = $showRoute;
        $this->editRoute = $editRoute;
        $this->deleteRoute = $deleteRoute;
        $this->exportExcelRoute = $exportExcelRoute;
        $this->exportPdfRoute = $exportPdfRoute;

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
        
        // Enviar los filtros iniciales al componente MenuExport
        $this->dispatch('updateExportFilters', [
            'filters' => $this->filters,
            'search' => $this->search
        ]);
    }

    public function updateSearch($search)
    {
        $this->search = $search;
        $this->resetPage();
        
        // Enviar los filtros actualizados al componente MenuExport
        $this->dispatch('updateExportFilters', [
            'filters' => $this->filters,
            'search' => $this->search
        ]);
    }

    public function updatingPerPage($value)
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        logger()->info('Limpiando todos los filtros');
        
        // Reiniciar filtros dinámicos personalizados
        foreach ($this->customFilters as $key => $filter) {
            $this->filters[$key] = $filter['default'] ?? null;
        }

        // Reiniciar filtro de rango de fechas si existe
        if (isset($this->filters['date_range'])) {
            $this->filters['date_range'] = ['start' => null, 'end' => null];
        }
        
        // Reiniciar filtro de status
        if (isset($this->filters['status'])) {
            $this->filters['status'] = null;
        }
        
        // Emitir evento para reiniciar filtros en componentes hijos
        $this->dispatch('resetFilters', $this->filters);
        
        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Filters cleared',
            'details' => 'All filters have been reset to their default values.'
        ]);
        
        // Enviar los filtros actualizados al componente MenuExport
        $this->dispatch('updateExportFilters', [
            'filters' => $this->filters,
            'search' => $this->search
        ]);

        $this->resetPage();
    }


    public function updateDateRange($dates)
    {
        logger()->info('Actualizando rango de fechas:', $dates);
        
        if (isset($dates['dates']['start'], $dates['dates']['end'])) {
            $this->filters['date_range'] = [
                'start' => $dates['dates']['start'],
                'end' => $dates['dates']['end']
            ];
            
            logger()->debug('Filtros actualizados con nuevo rango de fechas:', $this->filters);
            $this->resetPage();
            
            // Notificar al usuario que el filtro se ha aplicado
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Date filter applied',
                'details' => 'Showing records from ' . $dates['dates']['start'] . ' to ' . $dates['dates']['end'],
            ]);
        } else {
            logger()->warning('Formato de fecha incorrecto recibido:', $dates);
        }
    }
    
    public function applyFilters($filters)
    {
        logger()->info('Filtros aplicados:', $filters);
        
        // Manejar filtro de status
        if (isset($filters['status']) && !is_null($filters['status'])) {
            $this->filters['status'] = $filters['status'];
        }
        
        // Manejar otros filtros personalizados
        if (isset($filters['filters']) && is_array($filters['filters'])) {
            foreach ($filters['filters'] as $key => $value) {
                if (!is_null($value)) {
                    $this->filters[$key] = $value;
                }
            }
        }
        
        $this->resetPage(); // Reiniciar paginación
        
        // Enviar los filtros actualizados al componente MenuExport
        $this->dispatch('updateExportFilters', [
            'filters' => $this->filters,
            'search' => $this->search
        ]);
    }


    public function sortBy($field)
    {
        $this->sortField = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function showRecord($id)
    {
        if ($this->showRoute) {
            return redirect()->route($this->showRoute, $id);
        } else {
            // Si no hay ruta definida, mostrar el registro en un modal
            $record = $this->model::find($id);
            
            if ($record) {
                $recordData = $record->toArray();
                
                // Si es un usuario, incluir información adicional como roles
                if (class_basename($this->model) === 'User' && method_exists($record, 'roles')) {
                    try {
                        // Obtener roles del usuario
                        $roles = $record->roles()->pluck('name')->toArray();
                        $recordData['roles'] = implode(', ', $roles);
                        
                        // Agregar roles a las columnas si no existe
                        if (!in_array('roles', $this->columns)) {
                            $columns = $this->columns;
                            $columns[] = 'roles';
                        } else {
                            $columns = $this->columns;
                        }
                        
                        // Formatear fechas para mejor legibilidad
                        if (isset($recordData['created_at'])) {
                            $recordData['created_at'] = date('Y-m-d H:i:s', strtotime($recordData['created_at']));
                        }
                        if (isset($recordData['updated_at'])) {
                            $recordData['updated_at'] = date('Y-m-d H:i:s', strtotime($recordData['updated_at']));
                        }
                        
                        // Formatear estado si existe
                        if (isset($recordData['status'])) {
                            $recordData['status'] = $recordData['status'] ? 'Active' : 'Inactive';
                        }
                    } catch (\Exception $e) {
                        logger()->error('Error obteniendo roles del usuario: ' . $e->getMessage());
                        $columns = $this->columns;
                    }
                } else {
                    $columns = $this->columns;
                }
                
                $this->dispatch('showRecordDetail', [
                    'record' => $recordData,
                    'columns' => $columns,
                    'modelName' => class_basename($this->model)
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Record not found',
                    'details' => 'The requested record could not be found.',
                ]);
            }
        }
    }

    public function showSlugRecord($slug)
    {
        if ($this->showSlugRoute) {
            return redirect()->route($this->showSlugRoute, $slug);
        } else {
            // Si no hay ruta definida, mostrar una notificación
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Navigation Error',
                'details' => 'Show route with slug is not defined.',
            ]);
        }
    }

    public function confirmDeleteSingle($id)
    {
        $this->recordToDelete = $id;
        $this->dispatch('opendeleteconfirmation');
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

    public function confirmDeleteSelected()
    {
        if (count($this->selected) > 0) {
            $this->dispatch('opendeleteconfirmationmultiple');
        } else {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'No records selected!',
                'details' => 'Please select at least one record to delete.',
            ]);
        }
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

        $modelInstance = new $this->model;
        $table = $modelInstance->getTable();
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        
        // Depurar filtros actuales
        logger()->debug('Filtros actuales en render:', $this->filters);
        
        // Aplicar filtros de rango de fechas
        if (isset($this->filters['date_range']) && 
            !empty($this->filters['date_range']['start']) && 
            !empty($this->filters['date_range']['end'])) {
            
            // Verificar que la columna created_at existe
            $dateColumn = 'created_at';
            if ($schema->hasColumn($table, $dateColumn)) {
                $startDate = $this->filters['date_range']['start'];
                $endDate = $this->filters['date_range']['end'];
                
                // Asegurar que las fechas están en formato correcto
                if (!$startDate instanceof \DateTime) {
                    $startDate = date('Y-m-d', strtotime($startDate));
                }
                
                if (!$endDate instanceof \DateTime) {
                    // Añadir 23:59:59 al final del día para incluir todo el día final
                    $endDate = date('Y-m-d', strtotime($endDate)) . ' 23:59:59';
                }
                
                logger()->debug('Aplicando filtro de fecha:', [
                    'start' => $startDate,
                    'end' => $endDate
                ]);
                
                $query->whereBetween($dateColumn, [$startDate, $endDate]);
            } else {
                logger()->warning('La columna created_at no existe en la tabla ' . $table);
            }
        }

        // Aplicar filtros personalizados
        foreach ($this->filters as $key => $value) {
            // Saltarse el filtro de rango de fechas que ya se procesó
            if ($key === 'date_range' || is_null($value)) {
                continue;
            }
            
            // Verificar que la columna existe en la tabla
            if ($schema->hasColumn($table, $key)) {
                logger()->debug('Aplicando filtro:', ['campo' => $key, 'valor' => $value]);
                
                // Si es el filtro de `status`, manejar valores específicos
                if ($key === 'status') {
                    // Convertir texto a valores numéricos si es necesario
                    if ($value === 'active') {
                        $query->where($key, 1);
                    } elseif ($value === 'inactive') {
                        $query->where($key, 0);
                    } else {
                        $query->where($key, $value);
                    }
                } else {
                    $query->where($key, $value);
                }
            } else {
                logger()->warning('Columna no encontrada en la tabla:', [
                    'tabla' => $table,
                    'columna' => $key
                ]);
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
