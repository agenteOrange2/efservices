<?php

namespace App\Livewire\Admin\Accidents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\UserDriverDetail;

class AccidentsManager extends Component
{
    use WithPagination;
    
    public $driverId;
    public $driver;
    public $searchTerm = '';
    public $sortField = 'accident_date';
    public $sortDirection = 'desc';
    
    // Variables para el modal
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingAccidentId = null;
    
    // Campos del formulario
    public $accident_date;
    public $nature_of_accident;
    public $had_injuries = false;
    public $number_of_injuries;
    public $had_fatalities = false;
    public $number_of_fatalities;
    public $comments;
    
    protected $rules = [
        'accident_date' => 'required|date',
        'nature_of_accident' => 'required|string|max:255',
        'had_injuries' => 'boolean',
        'number_of_injuries' => 'nullable|integer|min:0',
        'had_fatalities' => 'boolean',
        'number_of_fatalities' => 'nullable|integer|min:0',
        'comments' => 'nullable|string',
    ];
    
    public function mount($driverId)
    {
        $this->driverId = $driverId;
        $this->driver = UserDriverDetail::findOrFail($driverId);
        $this->resetForm();
    }
    
    public function resetForm()
    {
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
        $accident = DriverAccident::findOrFail($accidentId);
        
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
        $accident->user_driver_detail_id = $this->driverId;
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
        $accidents = DriverAccident::where('user_driver_detail_id', $this->driverId)
            ->when($this->searchTerm, function ($query) {
                return $query->where('nature_of_accident', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('comments', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.admin.accidents.accidents-manager', [
            'accidents' => $accidents,
        ]);
    }
}