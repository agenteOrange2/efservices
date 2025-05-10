<?php

namespace App\Livewire;

use Livewire\Component;

class FilterPopover extends Component
{
    public $filters = [
        'date_range' => ['start' => null, 'end' => null],
        'status' => null,
    ];
    public $filterOptions = [];
    public $openPopover = false;

    protected $listeners = ['filtersUpdated', 'updateDateRange' => 'updateDateRange'];

    public function mount($filterOptions = [])
    {
        $this->filterOptions = $filterOptions;

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
        $this->dispatch('filtersUpdated', $this->transformFilters());
    }
    

    public function updateDateRange($dates)
    {
        logger()->info('Datos recibidos en updateDateRange:', $dates);

        if (isset($dates['start'], $dates['end'])) {
            $this->filters['date_range']['start'] = $dates['start'];
            $this->filters['date_range']['end'] = $dates['end'];

            // Emitir evento para que la tabla se actualice
            $this->dispatch('filtersUpdated', $this->filters);
        } else {
            $this->addError('date_range', 'Invalid date range provided.');
        }
    }

    public function clearFilters()
    {
        $this->filters = [
            'date_range' => ['start' => null, 'end' => null], // Reiniciar fechas
            'status' => null, // Reiniciar status u otros filtros
        ];

        foreach ($this->filterOptions as $key => $option) {
            $this->filters[$key] = $option['default'] ?? null;
        }

        $this->dispatch('filtersUpdated', $this->filters);
    }

    private function transformFilters()
    {
        $transformed = $this->filters;
    
        if (isset($this->filters['status'])) {
            $transformed['status'] = match ($this->filters['status']) {
                'inactive' => 0,
                'active' => 1,
                'pending' => 2,
                default => null,
            };
        }
    
        return $transformed;
    }
    
    
    public function render()
    {
        return view('livewire.filter-popover');
    }
}
