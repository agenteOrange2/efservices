<?php

namespace App\Livewire;

use Livewire\Component;

class FilterPopover extends Component
{
    // Configuración de filtros
    public $filters = [];
    public $dateRange = ['start' => null, 'end' => null];
    public $status = null;
    public $filterOptions = [];
    public $customFilters = [];
    
    // Estados UI
    public $openPopover = false;
    
    // Opciones configurables
    public $buttonLabel = 'Filter';
    public $showClearButton = true;
    public $clearButtonLabel = 'Clear Filters';
    public $datePickerFormat = 'YYYY-MM-DD';
    public $showDateRange = true;
    public $showStatus = true;
    public $dateRangeLabel = 'Date Range';
    public $statusLabel = 'Status';
    public $buttonClass = '';
    public $popoverClass = '';
    public $applyFilterImmediately = true;

    protected $listeners = ['filtersUpdated', 'updateDateRange'];

    public function mount($filterOptions = [], $customFilters = [])
    {
        $this->filterOptions = $filterOptions;
        $this->customFilters = $customFilters;

        // Inicializar filtros con valores por defecto
        foreach ($filterOptions as $key => $option) {
            $this->filters[$key] = $option['default'] ?? null;
        }
    }

    public function togglePopover()
    {
        $this->openPopover = !$this->openPopover;
    }

    public function updated($propertyName)
    {
        if ($this->applyFilterImmediately) {
            $this->applyFilters();
        }
    }
    
    public function updateDateRange($dates)
    {
        logger()->info('Recibiendo datos de fecha en FilterPopover:', $dates);
        
        if (isset($dates['dates']['start'], $dates['dates']['end'])) {
            $this->dateRange['start'] = $dates['dates']['start'];
            $this->dateRange['end'] = $dates['dates']['end'];
            
            logger()->info('Fechas actualizadas en FilterPopover:', $this->dateRange);
            
            if ($this->applyFilterImmediately) {
                $this->applyFilters();
            }
        }
    }

    public function applyFilters()
    {
        // Preparar los filtros en el formato correcto para GenericTable
        $filters = [];
        
        // Filtro de fecha
        if (!empty($this->dateRange['start']) && !empty($this->dateRange['end'])) {
            $filters['date_range'] = [
                'start' => $this->dateRange['start'],
                'end' => $this->dateRange['end']
            ];
        }
        
        // Filtro de estado
        if (!empty($this->status)) {
            $filters['status'] = $this->status;
        }
        
        // Otros filtros personalizados
        foreach ($this->filters as $key => $value) {
            if (!empty($value)) {
                $filters[$key] = $value;
            }
        }
        
        // Registrar en el log para depuración
        logger()->info('Enviando filtros desde FilterPopover:', $filters);
        
        // Enviar los filtros al componente padre
        $this->dispatch('filtersUpdated', $filters);
    }

    public function clearFilters()
    {
        // Reiniciar filtros de fecha y estado
        $this->dateRange = ['start' => null, 'end' => null];
        $this->status = null;
        
        // Reiniciar todos los valores de filtros personalizados
        foreach ($this->filters as $key => $value) {
            $this->filters[$key] = null;
        }

        $this->dispatch('clearFilters');
        $this->dispatch('showNotification', [
            'type' => 'success',
            'message' => 'Filters cleared successfully'
        ]);
    }

    protected function transformFilters($filterParams)
    {
        // Aquí puedes transformar los filtros si es necesario
        // Por ejemplo, convertir strings a integers, formatear fechas, etc.
        return $filterParams;
    }

    public function getActiveFiltersCount()
    {
        $count = 0;
        
        // Contar filtros de fecha activos
        if (!empty($this->dateRange['start']) || !empty($this->dateRange['end'])) {
            $count++;
        }
        
        // Contar filtro de estado activo
        if (!empty($this->status)) {
            $count++;
        }
        
        // Contar otros filtros activos
        foreach ($this->filters as $key => $value) {
            if (!empty($value)) {
                $count++;
            }
        }
        
        return $count;
    }

    public function render()
    {
        return view('livewire.filter-popover', [
            'activeFiltersCount' => $this->getActiveFiltersCount(),
        ]);
    }
}
