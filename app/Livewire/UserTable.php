<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $queryString = ['search', 'perPage'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
         $users = User::where('name', 'like', '%' . $this->search . '%')
             ->orWhere('email', 'like', '%' . $this->search . '%')
             ->paginate($this->perPage); // Usar paginate() en lugar de get()

        return view('livewire.user-table', compact('users'));
    }
}
