<?php

namespace App\Livewire;

use Livewire\Component;

class FilterPopover extends Component
{
    public $filters = [
        'date_range' => ['start' => null, 'end' => null],        
        'status' => null, // Status inicial
    ];

    public $filterOptions = []; // Opciones personalizadas de filtros

    public function mount($filterOptions = [])
    {
        $this->filterOptions = $filterOptions;

        // Inicializar filtros personalizados con valores predeterminados
        foreach ($filterOptions as $key => $option) {
            $this->filters[$key] = $option['default'] ?? null;
        }
    }

    public function updated($propertyName)
    {
        // Emitir los filtros cada vez que se actualice uno
        $this->dispatch('filtersUpdated', $this->transformFilters());
    }

    public function applyFilters()
    {
        $this->dispatch('filtersUpdated', $this->transformFilters()); // Emitir los filtros al componente padre
    }

    /**
     * Transformar los filtros para enviarlos al componente padre
     */
    private function transformFilters()
    {
        $transformed = $this->filters;

        // Convertir el filtro de status si existe
        if (isset($this->filters['status'])) {
            if ($this->filters['status'] === 'active') {
                $transformed['status'] = 1;
            } elseif ($this->filters['status'] === 'inactive') {
                $transformed['status'] = 0;
            } else {
                $transformed['status'] = null; // Sin filtro
            }
        }

        return $transformed;
    }

    public function render()
    {
        return view('livewire.filter-popover');
    }
}
