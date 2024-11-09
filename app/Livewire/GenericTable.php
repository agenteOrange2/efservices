<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class GenericTable extends Component
{
    use WithPagination;

    public $model;
    public $columns;
    public $search = '';
    public $perPage = 10;
    public $searchableFields = [];
    public $selected = []; // IDs seleccionados
    public $selectAll = false; // Control para seleccionar todos
    public $openMenu = []; // Array para manejar el estado de los menús desplegables por fila

    protected $listeners = ['resetPage'];

    public function updatingSearch()
    {
        $this->resetPage(); // Resetea la paginación, manteniendo el estado
    }

    public function toggleMenu($id)
    {
        // Alterna el estado del menú específico
        $this->openMenu[$id] = isset($this->openMenu[$id]) ? !$this->openMenu[$id] : true;
    }

    public function closeAllMenus()
    {
        // Cierra todos los menús al cambiar la página o al buscar
        $this->openMenu = [];
    }

    public function deleteSingle($id)
    {
        $this->model::find($id)->delete();
        $this->closeAllMenus(); // Cierra todos los menús después de eliminar
    }

    public function render()
    {
        $query = $this->model::query();

        if ($this->search && !empty($this->searchableFields)) {
            $query->where(function (Builder $q) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        $data = $query->paginate($this->perPage);

        // Cierra todos los menús si se cambia de página
        $this->closeAllMenus();

        return view('livewire.generic-table', [
            'data' => $data,
            'columns' => $this->columns,
        ]);
    }
}
