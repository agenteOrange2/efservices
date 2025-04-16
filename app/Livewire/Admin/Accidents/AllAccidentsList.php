<?php

namespace App\Livewire\Admin\Accidents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\UserDriverDetail;
use App\Models\Carrier;

class AllAccidentsList extends Component
{
    use WithPagination;
    
    // Filters and sorting
    public $searchTerm = '';
    public $sortField = 'accident_date';
    public $sortDirection = 'desc';
    public $driverFilter = '';
    public $carrierFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    
    // Add accident modal
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    
    public $selectedCarrierId = null;
    public $driversForCarrier = [];
    public $selectedDriverId = null;
    
    // Form fields
    public $accident_date;
    public $nature_of_accident;
    public $had_injuries = false;
    public $number_of_injuries;
    public $had_fatalities = false;
    public $number_of_fatalities;
    public $comments;
    public $editingAccidentId = null;
    
    protected $rules = [
        'selectedDriverId' => 'required|exists:user_driver_details,id',
        'accident_date' => 'required|date',
        'nature_of_accident' => 'required|string|max:255',
        'had_injuries' => 'boolean',
        'number_of_injuries' => 'nullable|integer|min:0',
        'had_fatalities' => 'boolean',
        'number_of_fatalities' => 'nullable|integer|min:0',
        'comments' => 'nullable|string',
    ];
    
    public function mount()
    {
        $this->resetForm();
    }
    
    public function updatedSelectedCarrierId($value)
    {
        if ($value) {
            $this->driversForCarrier = UserDriverDetail::where('carrier_id', $value)
                ->with('user')
                ->get();
            $this->selectedDriverId = null;
        } else {
            $this->driversForCarrier = [];
            $this->selectedDriverId = null;
        }
    }
    
    public function resetForm()
    {
        $this->selectedCarrierId = null;
        $this->selectedDriverId = null;
        $this->driversForCarrier = [];
        $this->accident_date = date('Y-m-d');
        $this->nature_of_accident = '';
        $this->had_injuries = false;
        $this->number_of_injuries = null;
        $this->had_fatalities = false;
        $this->number_of_fatalities = null;
        $this->comments = '';
        $this->editingAccidentId = null;
        $this->resetValidation();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }
    
    public function openEditModal($accidentId)
    {
        $this->editingAccidentId = $accidentId;
        $accident = DriverAccident::with('userDriverDetail')->findOrFail($accidentId);
        
        $this->selectedDriverId = $accident->user_driver_detail_id;
        $this->selectedCarrierId = $accident->userDriverDetail->carrier_id;
        $this->updatedSelectedCarrierId($this->selectedCarrierId);
        
        $this->accident_date = $accident->accident_date->format('Y-m-d');
        $this->nature_of_accident = $accident->nature_of_accident;
        $this->had_injuries = $accident->had_injuries;
        $this->number_of_injuries = $accident->number_of_injuries;
        $this->had_fatalities = $accident->had_fatalities;
        $this->number_of_fatalities = $accident->number_of_fatalities;
        $this->comments = $accident->comments;
        
        $this->showEditModal = true;
    }
    
    public function openDeleteModal($accidentId)
    {
        $this->editingAccidentId = $accidentId;
        $this->showDeleteModal = true;
    }
    
    public function closeModals()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }
    
    public function save()
    {
        $this->validate();
        
        $accident = new DriverAccident();
        $accident->user_driver_detail_id = $this->selectedDriverId;
        $accident->accident_date = $this->accident_date;
        $accident->nature_of_accident = $this->nature_of_accident;
        $accident->had_injuries = $this->had_injuries;
        $accident->number_of_injuries = $this->had_injuries ? $this->number_of_injuries : null;
        $accident->had_fatalities = $this->had_fatalities;
        $accident->number_of_fatalities = $this->had_fatalities ? $this->number_of_fatalities : null;
        $accident->comments = $this->comments;
        $accident->save();
        
        $this->closeModals();
        session()->flash('success', 'Accident record added successfully!');
    }
    
    public function update()
    {
        $this->validate();
        
        $accident = DriverAccident::findOrFail($this->editingAccidentId);
        $accident->user_driver_detail_id = $this->selectedDriverId;
        $accident->accident_date = $this->accident_date;
        $accident->nature_of_accident = $this->nature_of_accident;
        $accident->had_injuries = $this->had_injuries;
        $accident->number_of_injuries = $this->had_injuries ? $this->number_of_injuries : null;
        $accident->had_fatalities = $this->had_fatalities;
        $accident->number_of_fatalities = $this->had_fatalities ? $this->number_of_fatalities : null;
        $accident->comments = $this->comments;
        $accident->save();
        
        $this->closeModals();
        session()->flash('success', 'Accident record updated successfully!');
    }
    
    public function delete()
    {
        DriverAccident::findOrFail($this->editingAccidentId)->delete();
        $this->closeModals();
        session()->flash('success', 'Accident record deleted successfully!');
    }
    
    public function render()
    {
        $query = DriverAccident::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier'])
            ->when($this->searchTerm, function ($q) {
                return $q->where('nature_of_accident', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('comments', 'like', '%' . $this->searchTerm . '%');
            })
            ->when($this->driverFilter, function ($q) {
                return $q->where('user_driver_detail_id', $this->driverFilter);
            })
            ->when($this->carrierFilter, function ($q) {
                return $q->whereHas('userDriverDetail', function ($subq) {
                    $subq->where('carrier_id', $this->carrierFilter);
                });
            })
            ->when($this->dateFrom, function ($q) {
                return $q->whereDate('accident_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                return $q->whereDate('accident_date', '<=', $this->dateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection);
            
        $accidents = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();
        
        return view('livewire.admin.accidents.all-accidents-list', [
            'accidents' => $accidents,
            'drivers' => $drivers,
            'carriers' => $carriers,
            'allCarriers' => Carrier::where('status', 1)->get()
        ]);
    }
}