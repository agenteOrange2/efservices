<?php

namespace App\Livewire;

use Livewire\Component;

class SearchBar extends Component
{
    public $search = ''; // Campo de búsqueda
    public $placeholder = 'Search...'; // Placeholder para personalización

    public function updatingSearch($value)
    {
        // Enviar los cambios al componente principal usando dispatch
        $this->dispatch('search-updated', $value);
    }

    public function render()
    {
        return view('livewire.search-bar');
    }
}