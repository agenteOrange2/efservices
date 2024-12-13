<?php

namespace App\Livewire;

use App\Models\Carrier;
use Livewire\Component;
use App\Models\Membership;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class CarrierManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $carrier; // Datos del carrier
    public $plans; // Planes de membresía
    public $logoFile; // Archivo subido para el logo
    public $isCreating = false; // Modo creación/edición
    public $activeTab = 'carrier'; // Tab activo

    protected $rules = [
        'carrier.name' => 'required|string|max:255',
        'carrier.address' => 'required|string|max:255',
        'carrier.state' => 'required|string|max:255',
        'carrier.zipcode' => 'required|string|max:10',
        'carrier.ein_number' => 'required|string|max:255',
        'carrier.dot_number' => 'required|string|max:255',
        'carrier.mc_number' => 'nullable|string|max:255',
        'carrier.state_dot' => 'nullable|string|max:255',
        'carrier.ifta_account' => 'nullable|string|max:255',
        'carrier.id_plan' => 'required|exists:memberships,id',
        'carrier.status' => 'required|integer|in:0,1,3',
        'logoFile' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $this->resetCarrier();
        $this->plans = Membership::all();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createCarrier()
    {
        $this->resetCarrier();
        $this->isCreating = true;
        $this->activeTab = 'carrier';
    }

    public function editCarrier(Carrier $carrier)
    {
        $this->carrier = $carrier->toArray();
        $this->isCreating = true;
        $this->activeTab = 'carrier';
    }

    public function saveCarrier()
    {
        $this->validate();

        $carrier = isset($this->carrier['id'])
            ? Carrier::findOrFail($this->carrier['id'])
            : new Carrier();

        $carrier->fill($this->carrier);
        $carrier->save();

        if ($this->logoFile) {
            $carrier->clearMediaCollection('logo_carrier');
            $fileName = strtolower(str_replace(' ', '_', $carrier->name)) . '.webp';
            $carrier->addMedia($this->logoFile->getRealPath())
                ->usingFileName($fileName)
                ->toMediaCollection('logo_carrier');
        }

        Log::info('Current Carrier:', ['carrier' => $this->carrier]);
        Log::info('Logo File:', ['logoFile' => $this->logoFile ? $this->logoFile->getRealPath() : null]);

        session()->flash('success', isset($this->carrier['id']) ? 'Carrier updated successfully!' : 'Carrier created successfully!');
        $this->resetCarrier();
        $this->isCreating = false;
    }


    public function getLogoUrl()
    {
        if (isset($this->carrier['id'])) {
            $carrier = Carrier::find($this->carrier['id']);
            if ($carrier) {
                return $carrier->getFirstMediaUrl('logo_carrier') ?: null;
            }
        }
        return null;
    }

    public function deletePhoto()
    {
        if (isset($this->carrier['id'])) {
            $carrier = Carrier::findOrFail($this->carrier['id']);
    
            // Eliminar la colección de imágenes
            $carrier->clearMediaCollection('logo_carrier');
    
            session()->flash('success', 'Logo deleted successfully!');
        }
    }


    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    private function resetCarrier()
    {
        $this->carrier = [
            'name' => '',
            'address' => '',
            'state' => '',
            'zipcode' => '',
            'ein_number' => '',
            'dot_number' => '',
            'mc_number' => '',
            'state_dot' => '',
            'ifta_account' => '',
            'id_plan' => null,
            'status' => Carrier::STATUS_PENDING,
        ];
        $this->logoFile = null;
    }

    public function render()
    {
        return view('livewire.carrier-manager', [
            'carriers' => Carrier::query()
                ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->paginate(10),
        ]);
    }
}
