<?php

namespace App\Livewire\Admin\Vehicle;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Carbon\Carbon;
class MaintenanceForm extends Component
{
    public $maintenance;
    public $vehicles = [];
    public $maintenanceTypes = [
        'Preventive',
        'Corrective',
        'Inspection',
        'Oil Change',
        'Tire Rotation',
        'Brake Service',
        'Engine Service',
        'Transmission Service',
        'Other'
    ];

    // Form fields
    #[Rule('required|exists:vehicles,id')]
    public $vehicle_id;

    #[Rule('required|string|min:3|max:255')]
    public $unit;

    #[Rule('required|string|min:3|max:255')]
    public $service_tasks;

    #[Rule('required|date')]
    public $service_date;

    #[Rule('required|date|after:service_date')]
    public $next_service_date;

    #[Rule('required|string|max:255')]
    public $vendor_mechanic;

    #[Rule('required|numeric|min:0')]
    public $cost = 0;

    #[Rule('nullable|integer|min:0')]
    public $odometer;

    #[Rule('nullable|string')]
    public $description;

    #[Rule('boolean')]
    public $status = false;

    public $isEditing = false;

    public function mount($id = null)
    {
        $this->vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        
        if ($id) {
            $this->maintenance = VehicleMaintenance::findOrFail($id);
            $this->vehicle_id = $this->maintenance->vehicle_id;
            $this->unit = $this->maintenance->unit;
            $this->service_tasks = $this->maintenance->service_tasks;
            $this->service_date = $this->maintenance->service_date->format('Y-m-d\TH:i');
            $this->next_service_date = $this->maintenance->next_service_date?->format('Y-m-d\TH:i') ?? '';
            $this->vendor_mechanic = $this->maintenance->vendor_mechanic;
            $this->cost = $this->maintenance->cost;
            $this->odometer = $this->maintenance->odometer;
            $this->description = $this->maintenance->description;
            $this->status = $this->maintenance->status;
            $this->isEditing = true;
        } else {
            // Valores por defecto para nuevo mantenimiento
            $this->maintenance = new VehicleMaintenance();
            $this->service_date = Carbon::now()->format('Y-m-d\TH:i');
            $this->next_service_date = Carbon::now()->addMonths(3)->format('Y-m-d\TH:i');
        }
    }

    public function save()
    {
        $validated = $this->validate();
        
        if ($this->isEditing) {
            $this->maintenance->update($validated);
            session()->flash('message', 'Mantenimiento actualizado correctamente.');
        } else {
            VehicleMaintenance::create($validated);
            session()->flash('message', 'Mantenimiento registrado correctamente.');
        }
        
        $this->redirectRoute('admin.maintenance.index');
    }

    public function render()
    {
        // Si se ha seleccionado un vehículo, autocompletar el campo unit con el número de unidad
        if ($this->vehicle_id && empty($this->unit)) {
            $vehicle = Vehicle::find($this->vehicle_id);
            if ($vehicle && $vehicle->company_unit_number) {
                $this->unit = $vehicle->company_unit_number;
            }
        }
        
        return view('livewire.admin.vehicle.maintenance-form');
    }
}