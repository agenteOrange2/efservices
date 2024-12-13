<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserCarrier;
use App\Models\Carrier;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public $carrier; // Mantendrá el modelo Carrier
    public $search = '';
    public $userCarrier;
    public $isCreating = false;

    protected $rules = [
        'userCarrier.name' => 'required|string|max:255',
        'userCarrier.email' => 'required|email|max:255',
        'userCarrier.phone' => 'required|string|max:15',
        'userCarrier.status' => 'required|in:1,2,3',
        'userCarrier.carrier_id' => 'required|exists:carriers,id',
    ];

    public function mount($carrier)
    {
        $this->carrier = is_array($carrier) ? Carrier::findOrFail($carrier['id']) : Carrier::findOrFail($carrier);
        $this->resetUserCarrier();
    }
    

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createUser()
    {
        $this->resetUserCarrier();
        $this->isCreating = true;
    }

    public function editUser(UserCarrier $userCarrier)
    {
        $this->userCarrier = $userCarrier;
        $this->isCreating = true;
    }

    public function saveUser()
    {
        $this->validate();

        $this->userCarrier->save();

        $this->isCreating = false;
        $this->resetUserCarrier();
        session()->flash('success', 'User saved successfully!');
    }

    private function resetUserCarrier()
    {
        $this->userCarrier = new UserCarrier(['carrier_id' => $this->carrier->id]);
    }

    public function render()
    {
        return view('livewire.user-manager', [
            'users' => UserCarrier::where('carrier_id', $this->carrier->id)
                ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->paginate(10),
        ]);
    }
}
