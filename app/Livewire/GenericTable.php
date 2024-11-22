<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class GenericTable extends Component
{
    use WithPagination;

    public $model;
    public $columns;
    public $search = '';
    public $perPage = 10;
    public $perPageOptions = [10, 20, 30, 100, 200];
    public $searchableFields = [];
    public $selected = [];
    public $selectAll = false;
    public $openMenu = [];

    protected $listeners = ['resetPage'];

    public function updatingSearch()
    {
        $this->resetPage(); // Resetea la paginación al buscar
    }

    public function updatingPerPage($value)
    {
        $this->resetPage(); // Esto asegura que se reinicie a la primera página
        Log::info('Updating perPage:', [$value]); // Confirmación en los logs
    }

    public function toggleMenu($id)
    {
        $this->openMenu[$id] = isset($this->openMenu[$id]) ? !$this->openMenu[$id] : true;
    }

    public function closeAllMenus()
    {
        $this->openMenu = [];
    }

    public function deleteSingle($id)
    {
        $this->model::find($id)->delete();
        $this->closeAllMenus();
    }

    public function render()
    {
        Log::info('Rendering with perPage:', [$this->perPage]);
    
        $query = $this->model::query();
    
        if ($this->search && !empty($this->searchableFields)) {
            $query->where(function (Builder $q) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }
    
        $data = $query->paginate($this->perPage);
    
        return view('livewire.generic-table', [
            'data' => $data,
            'columns' => $this->columns,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
    
    
}
