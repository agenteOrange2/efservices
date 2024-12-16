<?php

namespace App\Livewire;

use App\Models\Carrier;
use Livewire\Component;
use App\Models\Membership;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class CarrierManager extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(history: true)] // Sincroniza con la URL
    public $carrierId = null; // ID para editar o null para crear

    #[Url(history: true)] // Sincroniza con la URL    
    public $isCreating = false; // Modo creación/edición

    public $search = '';
    public $carrier = []; // Datos del carrier
    public $plans; // Planes de membresía
    public $logoFile; // Archivo subido para el logo
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

    public function mount($slug = null)
    {
        $this->plans = Membership::all();
    
        if ($slug) {
            if (request()->routeIs('admin.carrier.edit')) {
                // Modo edición
                $carrier = Carrier::where('slug', $slug)->first();
                if ($carrier) {
                    $this->carrierId = $carrier->id;
                    $this->carrier = $carrier->toArray();
                    $this->isCreating = true;
                } else {
                    session()->flash('error', 'Carrier not found.');
                    return redirect()->route('admin.carrier.index');
                }
            } elseif (request()->routeIs('admin.carrier.create')) {
                // Modo creación
                $this->isCreating = true;
                $this->resetCarrier();
            }
        }
    }
    

    private function loadCarrier($carrierId)
    {
        $carrier = Carrier::find($carrierId);

        if ($carrier) {
            $this->carrier = $carrier->toArray();
        } else {
            session()->flash('error', 'Carrier not found.');
            $this->resetCarrier();
            $this->isCreating = false;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createCarrier()
    {
        $this->isCreating = true;
        $this->carrierId = null;
        $this->resetCarrier();
    }

    public function editCarrier($carrierId)
    {
        $this->carrierId = $carrierId;
        $this->isCreating = true;
        $this->loadCarrier($carrierId);
    }

    public function saveCarrier()
    {
        $this->validate();
    
        $carrier = $this->carrierId
            ? Carrier::findOrFail($this->carrierId)
            : new Carrier();
    
        $carrier->fill($this->carrier);
    
        // Generar slug único para el carrier
        if (!$this->carrierId) {
            $carrier->slug = strtolower(str_replace(' ', '-', $carrier->name)) . '-' . uniqid();
        }
    
        $carrier->save();
    
        if ($this->logoFile) {
            $carrier->clearMediaCollection('logo_carrier');
            $carrier->addMedia($this->logoFile->getRealPath())
                ->usingFileName(strtolower(str_replace(' ', '_', $carrier->name)) . '.webp')
                ->toMediaCollection('logo_carrier');
        }
    
        session()->flash('success', $this->carrierId ? 'Carrier updated successfully!' : 'Carrier created successfully!');
    
        // Redirigir a la URL semántica de edición
        return redirect()->route('admin.carrier.edit', ['slug' => $carrier->slug]);
    }
    
    


    public function getLogoUrl()
    {
        if (isset($this->carrier['id'])) {
            $carrier = Carrier::find($this->carrier['id']);
            if ($carrier) {
                return $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/default_profile.png');
            }
        }
        return asset('build/default_profile.png'); // Retorna un valor predeterminado si no hay imagen
    }
    

    public function deletePhoto()
    {
        if ($this->carrierId) {
            $carrier = Carrier::findOrFail($this->carrierId);
            $carrier->clearMediaCollection('logo_carrier');
            $this->logoFile = null;
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
        $this->carrierId = null;
    }

    public function render()
    {
        return view('livewire.carrier-manager', [
            'carriers' => Carrier::query()
                ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->paginate(10),
            'originalPhotoUrl' => $this->getLogoUrl(), // Aquí se pasa la URL
        ])->layout('livewire.partials.carrier-form'); // Apunta al layout correcto
    }
    

    
}
