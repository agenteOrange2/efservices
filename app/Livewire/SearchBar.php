<?php

namespace App\Livewire;

use Livewire\Component;

class SearchBar extends Component
{
    public $search = ''; // Campo de búsqueda
    public $placeholder = 'Search...'; // Placeholder para personalización
    public $debounce = 250; // Tiempo de debounce en ms
    public $buttonText = ''; // Texto para el botón opcional de búsqueda
    public $showSearchButton = false; // Mostrar botón de búsqueda
    public $searchIconClass = ''; // Clase CSS para el ícono de búsqueda
    public $inputClass = ''; // Clase CSS adicional para el input

    public function mount($placeholder = 'Search...', $debounce = 250, $showSearchButton = false, $buttonText = 'Search')
    {
        $this->placeholder = $placeholder;
        $this->debounce = $debounce;
        $this->showSearchButton = $showSearchButton;
        $this->buttonText = $buttonText;
    }
    
    public function updatingSearch($value)
    {
        // Enviar los cambios al componente principal usando dispatch
        $this->dispatch('search-updated', $value);
    }
    
    public function search()
    {
        // Método para búsqueda manual cuando se presiona el botón
        $this->dispatch('search-updated', $this->search);
    }
    
    public function clearSearch()
    {
        $this->search = '';
        $this->dispatch('search-updated', '');
    }

    public function render()
    {
        return view('livewire.search-bar');
    }
}
