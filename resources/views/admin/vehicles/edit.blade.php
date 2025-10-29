@extends('../themes/' . $activeTheme)
@section('title', 'Edit Vehicle')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
['label' => 'Edit Vehicle', 'active' => true],
];
@endphp

{{-- Script para funciones globales antes de Alpine.js --}}
<script>
    // Global functions for Make and Type modals - must be defined before Alpine.js
    function addNewMake() {
        const modal = document.getElementById('addMakeModal');
        const input = document.getElementById('makeName');
        if (modal) modal.classList.remove('hidden');
        if (input) input.focus();
    }

    function addNewType() {
        const modal = document.getElementById('addTypeModal');
        const input = document.getElementById('typeName');
        if (modal) modal.classList.remove('hidden');
        if (input) input.focus();
    }

    // Modal functions for Make
    function openMakeModal() {
        const modal = document.getElementById('addMakeModal');
        const input = document.getElementById('makeName');
        if (modal) modal.classList.remove('hidden');
        if (input) input.focus();
    }

    function closeMakeModal() {
        const modal = document.getElementById('addMakeModal');
        const input = document.getElementById('makeName');
        if (modal) modal.classList.add('hidden');
        if (input) input.value = '';
        hideError('makeError');
    }

    // Modal functions for Type
    function openTypeModal() {
        const modal = document.getElementById('addTypeModal');
        const input = document.getElementById('typeName');
        if (modal) modal.classList.remove('hidden');
        if (input) input.focus();
    }

    function closeTypeModal() {
        const modal = document.getElementById('addTypeModal');
        const input = document.getElementById('typeName');
        if (modal) modal.classList.add('hidden');
        if (input) input.value = '';
        hideError('typeError');
    }

    // Error handling functions
    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }

    function hideError(elementId) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.classList.add('hidden');
        }
    }

    // Initialize event listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Form submission handlers
        const addMakeForm = document.getElementById('addMakeForm');
        if (addMakeForm) {
            addMakeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const makeNameInput = document.getElementById('makeName');
                const makeName = makeNameInput ? makeNameInput.value.trim() : '';
            
            if (!makeName) {
                showError('makeError', 'Make name is required');
                return;
            }

            fetch('/api/vehicles/makes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: makeName })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    // Add new option to select
                    const makeSelect = document.querySelector('select[name="make"]');
                    const newOption = document.createElement('option');
                    newOption.value = data.data.name;
                    newOption.textContent = data.data.name;
                    newOption.selected = true;
                    
                    // Insert before the "Add New Make" option
                    const addNewOption = makeSelect.querySelector('option[value="__add_new__"]');
                    makeSelect.insertBefore(newOption, addNewOption);
                    
                    // Update Alpine.js model if available
                    if (window.Alpine && window.Alpine.store) {
                        const alpineData = makeSelect.closest('[x-data]');
                        if (alpineData && alpineData._x_dataStack) {
                            alpineData._x_dataStack[0].make = data.data.name;
                        }
                    }
                    
                    closeMakeModal();
                } else {
                    showError('makeError', data.message || 'Error adding make');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.errors) {
                    // Handle validation errors
                    const errorMessages = Object.values(error.errors).flat().join(', ');
                    showError('makeError', errorMessages);
                } else {
                    showError('makeError', error.message || 'Network error occurred');
                }
            });
            });
        }

        const addTypeForm = document.getElementById('addTypeForm');
        if (addTypeForm) {
            addTypeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const typeNameInput = document.getElementById('typeName');
                const typeName = typeNameInput ? typeNameInput.value.trim() : '';
            
            if (!typeName) {
                showError('typeError', 'Type name is required');
                return;
            }

            fetch('/api/vehicles/types', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: typeName })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    // Add new option to select
                    const typeSelect = document.querySelector('select[name="type"]');
                    const newOption = document.createElement('option');
                    newOption.value = data.data.name;
                    newOption.textContent = data.data.name;
                    newOption.selected = true;
                    
                    // Insert before the "Add New Type" option
                    const addNewOption = typeSelect.querySelector('option[value="__add_new__"]');
                    typeSelect.insertBefore(newOption, addNewOption);
                    
                    // Update Alpine.js model if available
                    if (window.Alpine && window.Alpine.store) {
                        const alpineData = typeSelect.closest('[x-data]');
                        if (alpineData && alpineData._x_dataStack) {
                            alpineData._x_dataStack[0].type = data.data.name;
                        }
                    }
                    
                    closeTypeModal();
                } else {
                    showError('typeError', data.message || 'Error adding type');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.errors) {
                    // Handle validation errors
                    const errorMessages = Object.values(error.errors).flat().join(', ');
                    showError('typeError', errorMessages);
                } else {
                    showError('typeError', error.message || 'Network error occurred');
                }
                });
            });
        }

        // Close modals when clicking outside
        const addMakeModal = document.getElementById('addMakeModal');
        if (addMakeModal) {
            addMakeModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMakeModal();
                }
            });
        }

        const addTypeModal = document.getElementById('addTypeModal');
        if (addTypeModal) {
            addTypeModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeTypeModal();
                }
            });
        }
    });
</script>

@section('subcontent')
<div>
    <div class="box box--stacked flex flex-col">
        <div class="box-body">
            <form action="{{ route('admin.vehicles.update', $vehicle->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <x-validation-errors class="my-4" />

                {{-- Contenedor Alpine con toda la lógica --}}
                <div x-data="{
                                activeTab: 'general',
                                // Datos del vehículo
                                carrier_id: '{{ old('carrier_id', $vehicle->carrier_id) }}',
                                make: '{{ old('make', $vehicle->make) }}',
                                model: '{{ old('model', $vehicle->model) }}',
                                type: '{{ old('type', $vehicle->type) }}',
                                year: '{{ old('year', $vehicle->year) }}',
                                vin: '{{ old('vin', $vehicle->vin) }}',
                                // Campos de registro
                                registrationState: '{{ old('registration_state', $vehicle->registration_state) }}',
                                registrationNumber: '{{ old('registration_number', $vehicle->registration_number) }}',
                                registrationExpirationDate: '{{ old('registration_expiration_date', $vehicle->registration_expiration_date ? $vehicle->registration_expiration_date->format('Y-m-d') : '') }}',
                                permanentTag: {{ old('permanent_tag', $vehicle->permanent_tag ? 'true' : 'false') }},
                                // Estado
                                outOfService: {{ old('out_of_service', $vehicle->out_of_service ? 'true' : 'false') }},
                                outOfServiceDate: '{{ old('out_of_service_date', $vehicle->out_of_service_date ? $vehicle->out_of_service_date->format('Y-m-d') : '') }}',
                                suspended: {{ old('suspended', $vehicle->suspended ? 'true' : 'false') }},
                                suspendedDate: '{{ old('suspended_date', $vehicle->suspended_date ? $vehicle->suspended_date->format('Y-m-d') : '') }}',


                                // Validation variables
                                vinError: '',
                                registrationDateError: '',
                                inspectionDateError: '',
                                annualInspectionExpirationDate: '{{ old('annual_inspection_expiration_date', $vehicle->annual_inspection_expiration_date ? $vehicle->annual_inspection_expiration_date->format('Y-m-d') : '') }}',

                                // Modal functions removed - now global functions

                                // VIN Validation
                                validateVin(vin) {
                                    if (!vin) {
                                        this.vinError = '';
                                        return;
                                    }
                                    if (vin.length !== 17) {
                                        this.vinError = 'VIN must be exactly 17 characters';
                                        return;
                                    }
                                    if (/[IOQ]/i.test(vin)) {
                                        this.vinError = 'VIN cannot contain letters I, O, or Q';
                                        return;
                                    }
                                    this.vinError = '';
                                },
                                // Date Validation
                                validateDate(dateValue, type) {
                                    if (!dateValue) return;
                                    const selectedDate = new Date(dateValue);
                                    const today = new Date();
                                    today.setHours(0, 0, 0, 0);
                                    
                                    if (type === 'registration') {
                                        if (selectedDate <= today) {
                                            this.registrationDateError = 'Registration expiration date must be in the future';
                                        } else {
                                            this.registrationDateError = '';
                                        }
                                    } else if (type === 'inspection') {
                                        if (selectedDate <= today) {
                                            this.inspectionDateError = 'Annual inspection expiration date must be in the future';
                                        } else {
                                            this.inspectionDateError = '';
                                        }
                                    }
                                },


                                // Service Items 
                                /*
                                serviceItems: [{!! $vehicle->service_items ? json_encode($vehicle->service_items) : '[]' !!}][0].length > 0 
                                    ? [{!! $vehicle->service_items ? json_encode($vehicle->service_items) : '[]' !!}][0] 
                                    : [{
                                        unit: '',
                                        service_date: '',
                                        next_service_date: '',
                                        service_tasks: '',
                                        vendor_mechanic: '',
                                        description: '',
                                        cost: '',
                                        odometer: ''
                                    }],
                                    */
                                // Métodos
                                addServiceItem() {
                                    this.serviceItems.push({
                                        unit: '',
                                        service_date: '',
                                        next_service_date: '',
                                        service_tasks: '',
                                        vendor_mechanic: '',
                                        description: '',
                                        cost: '',
                                        odometer: ''
                                    });
                                },
                                removeServiceItem(index) {
                                    if (this.serviceItems.length > 1) {
                                        this.serviceItems.splice(index, 1);
                                    }
                                },
                                validateServiceDate(index) {
                                    const item = this.serviceItems[index];
                                    if (item.service_date && item.next_service_date) {
                                        const serviceDate = new Date(item.service_date);
                                        const nextDate = new Date(item.next_service_date);
                                        if (nextDate <= serviceDate) {
                                            item.dateError = 'Next service date must be after service date';
                                            return false;
                                        } else {
                                            item.dateError = '';
                                            return true;
                                        }
                                    }
                                    return true;
                                }
                            }" id="vehicle-form">

                    {{-- Tabs en Blade --}}
                    <div class="tabs">
                        <div class="mb-4 border-b border-gray-200">
                            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                                <li class="mr-2">
                                    <button type="button" @click="activeTab = 'general'"
                                        class="inline-block p-4"
                                        :class="{
                                                        'text-primary border-b-2 border-primary': activeTab === 'general',
                                                        'text-gray-500 hover:border-gray-300': activeTab !== 'general'
                                                    }">
                                        General Information
                                    </button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" @click="activeTab = 'service'"
                                        class="inline-block p-4"
                                        :class="{
                                                        'text-primary border-b-2 border-primary': activeTab === 'service',
                                                        'text-gray-500 hover:border-gray-300': activeTab !== 'service'
                                                    }">
                                        Service Items
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- TAB: GENERAL --}}
                    <div x-show="activeTab === 'general'">
                        <div>
                            <!-- Vehicle Basic Information -->
                            <div class="bg-white p-4 rounded-lg shadow">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Vehicle Information</h3>
                                <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
                                    {{-- Carrier --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 xl:w-60">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium">Carrier</div>
                                                    <div
                                                        class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">                                            
                                            <select id="carrier_id" name="carrier_id" x-model="carrier_id"
                                                x-init="$nextTick(() => { if(carrier_id) { $el.value = carrier_id; } })"
                                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                <option value="">Select Carrier</option>
                                                @foreach ($carriers as $carrier)
                                                <option value="{{ $carrier->id }}">
                                                    {{ $carrier->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('carrier_id')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Current Driver Assignment --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-4 mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <h3 class="text-lg font-semibold">Driver Assignment</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            @if($vehicle->currentDriverAssignment)
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h4 class="font-medium text-green-800">Currently Assigned</h4>
                                                        <p class="text-sm text-green-700 mt-1">                                                            
                                                            @if($vehicle->currentDriverAssignment->driver && $vehicle->currentDriverAssignment->driver->user)
                                                            <strong>Driver:</strong> {{ $vehicle->currentDriverAssignment->driver->user->name }}<br>
                                                            @elseif($vehicle->currentDriverAssignment->user)
                                                            <strong>Driver:</strong> {{ $vehicle->currentDriverAssignment->user->name }}<br>
                                                            @endif
                                                            <strong>Status:</strong> {{ ucfirst($vehicle->currentDriverAssignment->status) }}<br>
                                                            <strong>Start Date:</strong> {{ $vehicle->currentDriverAssignment->start_date ? $vehicle->currentDriverAssignment->start_date->format('M d, Y') : 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('admin.vehicles.assign-driver-type', $vehicle->id) }}"
                                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-xs">
                                                            Change Driver
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h4 class="font-medium text-yellow-800">No Driver Assigned</h4>
                                                        <p class="text-sm text-yellow-700 mt-1">
                                                            This vehicle does not have a current driver assignment.
                                                        </p>
                                                    </div>
                                                    <a href="{{ route('admin.vehicles.assign-driver-type', $vehicle->id) }}"
                                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-xs">
                                                        Assign Driver
                                                    </a>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Make/Model/Type --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-4 mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <h3 class="text-lg font-semibold">Vehicle Details</h3>
                                                    <div
                                                        class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                {{-- Make --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Make</label>
                                                    <div class="flex gap-2">
                                                        <select name="make" x-model="make" @change="if($event.target.value === '__add_new__') { addNewMake(); $event.target.value = ''; }"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm flex-1">
                                                            <option value="">Select a make</option>
                                                            @foreach($vehicleMakes as $vehicleMake)
                                                                <option value="{{ $vehicleMake->name }}" {{ old('make', $vehicle->make) == $vehicleMake->name ? 'selected' : '' }}>
                                                                    {{ $vehicleMake->name }}
                                                                </option>
                                                            @endforeach
                                                            <option value="__add_new__">+ Add New Make</option>
                                                        </select>

                                                    </div>
                                                    @error('make')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Model --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Model</label>
                                                    <input type="text" name="model" x-model="model"
                                                        value="{{ old('model', $vehicle->model) }}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. Cascadia">
                                                    @error('model')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Type --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Type</label>
                                                    <div class="flex gap-2">
                                                        <select name="type" x-model="type" @change="if($event.target.value === '__add_new__') { addNewType(); $event.target.value = ''; }"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm flex-1">
                                                            <option value="">Select a type</option>
                                                            @foreach($vehicleTypes as $vehicleType)
                                                                <option value="{{ $vehicleType->name }}" {{ old('type', $vehicle->type) == $vehicleType->name ? 'selected' : '' }}>
                                                                    {{ $vehicleType->name }}
                                                                </option>
                                                            @endforeach
                                                            <option value="__add_new__">+ Add New Type</option>
                                                        </select>

                                                    </div>
                                                    @error('type')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                                {{-- Year --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Year</label>
                                                    <input type="number" name="year" x-model="year"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        min="1900" max="{{ date('Y') + 1 }}"
                                                        placeholder="e.g. 2023">
                                                    @error('year')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Unit Number --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Company Unit Number</label>
                                                    <input type="text" name="company_unit_number"
                                                        value="{{ old('company_unit_number', $vehicle->company_unit_number) }}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. 12345">
                                                    @error('company_unit_number')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- VIN --}}
                                                <div>
                                                    <label class="block text-sm mb-1">VIN <span class="text-red-500">*</span></label>
                                                    <input type="text" name="vin" x-model="vin"
                                                        maxlength="17" minlength="17"
                                                        @input="validateVin($event.target.value)"
                                                        :class="{'border-red-500': vinError, 'border-green-500': vin && vin.length === 17 && !vinError}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. 1HGBH41JXMN109186">
                                                    <p class="text-red-500 text-xs mt-1" x-show="vinError" x-text="vinError"></p>
                                                    <p class="text-green-500 text-xs mt-1" x-show="!vinError && vin && vin.length === 17">✓ Valid VIN format</p>
                                                    <p class="text-gray-500 text-xs mt-1" x-show="!vin || (vin.length !== 17 && !vinError)">VIN must be exactly 17 characters</p>
                                                    @error('vin')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- GVWR and Tire Size --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <h3 class="text-lg font-semibold">Technical Details</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {{-- GVWR --}}
                                                <div>
                                                    <label class="block text-sm mb-1">GVWR</label>
                                                    <input type="text" name="gvwr"
                                                        value="{{ old('gvwr', $vehicle->gvwr) }}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. 26,000 lbs">
                                                    @error('gvwr')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Tire Size --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Tire Size</label>
                                                    <input type="text" name="tire_size"
                                                        value="{{ old('tire_size', $vehicle->tire_size) }}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. 295/75R22.5">
                                                    @error('tire_size')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Fuel Type and IRP --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-5 sm:mr-5 xl:mr-14 xl:w-60">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium">Fuel & Registration</div>
                                                    <div
                                                        class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {{-- Fuel Type --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Fuel Type</label>
                                                    <select name="fuel_type"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        <option value="">Select Fuel Type</option>
                                                        <option value="Diesel"
                                                            {{ old('fuel_type', $vehicle->fuel_type) == 'Diesel' ? 'selected' : '' }}>
                                                            Diesel</option>
                                                        <option value="Gasoline"
                                                            {{ old('fuel_type', $vehicle->fuel_type) == 'Gasoline' ? 'selected' : '' }}>
                                                            Gasoline</option>
                                                        <option value="CNG"
                                                            {{ old('fuel_type', $vehicle->fuel_type) == 'CNG' ? 'selected' : '' }}>
                                                            CNG
                                                        </option>
                                                        <option value="LNG"
                                                            {{ old('fuel_type', $vehicle->fuel_type) == 'LNG' ? 'selected' : '' }}>
                                                            LNG
                                                        </option>
                                                        <option value="Electric"
                                                            {{ old('fuel_type', $vehicle->fuel_type) == 'Electric' ? 'selected' : '' }}>
                                                            Electric</option>
                                                        <option value="Hybrid"
                                                            {{ old('fuel_type', $vehicle->fuel_type) == 'Hybrid' ? 'selected' : '' }}>
                                                            Hybrid</option>
                                                    </select>
                                                    @error('fuel_type')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- IRP Checkbox --}}
                                                <div class="flex items-center pt-5">
                                                    <input type="checkbox" name="irp_apportioned_plate"
                                                        value="1"
                                                        class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary"
                                                        {{ old('irp_apportioned_plate', $vehicle->irp_apportioned_plate) ? 'checked' : '' }}>
                                                    <label class="ms-2 text-sm font-medium text-gray-900">
                                                        IRP (Apportioned Plate)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Registration Information --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <h3 class="text-lg font-semibold">Registration Info</h3>
                                                    <div
                                                        class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {{-- Registration State --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Registration State</label>
                                                    <select name="registration_state" x-model="registrationState"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        <option value="">Select State</option>
                                                        @foreach ($usStates as $code => $name)
                                                        <option value="{{ $code }}"
                                                            {{ old('registration_state', $vehicle->registration_state) == $code ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @error('registration_state')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Location --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Location</label>
                                                    <input type="text" name="location"
                                                        value="{{ old('location', $vehicle->location) }}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="Ex. Main Terminal">
                                                    @error('location')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Registration Number --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Registration Number</label>
                                                    <input type="text" name="registration_number" x-model="registrationNumber"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. ABC1234">
                                                    @error('registration_number')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Registration Expiration --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Expiration Date <span class="text-red-500">*</span></label>
                                                    <x-base.litepicker name="registration_expiration_date" value="{{ old('registration_expiration_date', $vehicle->registration_expiration_date) }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('registration_expiration_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
                                                    @error('registration_expiration_date')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            {{-- Permanent Tag --}}
                                            <div class="flex items-center mt-4">
                                                <input type="checkbox" name="permanent_tag" value="1"
                                                    x-model="permanentTag"
                                                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary">
                                                <label class="ms-2 text-sm font-medium text-gray-900">
                                                    Permanent Tag
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Annual Inspection --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <h3 class="text-lg font-semibold">Annual Inspection</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div>
                                                <label class="block text-sm mb-1">Expiration Date <span class="text-red-500">*</span></label>
                                                <x-base.litepicker name="annual_inspection_expiration_date" value="{{ old('annual_inspection_expiration_date', $vehicle->annual_inspection_expiration_date) }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('annual_inspection_expiration_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
                                                @error('annual_inspection_expiration_date')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Status --}}
                                    <div class="my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 xl:w-60">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium text-lg">Status</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0 space-y-4">
                                            {{-- Vehicle Status --}}
                                            <div>
                                                <label class="block text-sm mb-1">Vehicle Status</label>
                                                <select name="status" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('status') border-red-500 @enderror">
                                                    <option value="active" {{ old('status', $vehicle->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status', $vehicle->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="pending" {{ old('status', $vehicle->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="suspended" {{ old('status', $vehicle->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                    <option value="out_of_service" {{ old('status', $vehicle->status) == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                                                </select>
                                                @error('status')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            
                                            {{-- Out of Service --}}
                                            <div>
                                                <div class="flex items-center mb-3">
                                                    <input type="checkbox" name="out_of_service" value="1"
                                                        x-model="outOfService"
                                                        class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500">
                                                    <label class="ml-3 text-sm font-medium text-gray-700">
                                                        Out of Service
                                                    </label>
                                                </div>
                                                <div x-show="outOfService" class="mt-2 ml-6">
                                                    <label class="block text-sm mb-1">Out of Service Date</label>
                                                    <x-base.litepicker name="out_of_service_date" value="{{ old('out_of_service_date', $vehicle->out_of_service_date) }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('out_of_service_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
                                                </div>
                                            </div>
                                            {{-- Suspended --}}
                                            <div>
                                                <div class="flex items-center mb-3">
                                                    <input type="checkbox" name="suspended" value="1"
                                                        x-model="suspended"
                                                        class="w-4 h-4 text-yellow-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500">
                                                    <label class="ml-3 text-sm font-medium text-gray-700">
                                                        Suspended
                                                    </label>
                                                </div>
                                                <div x-show="suspended" class="mt-2 ml-6">
                                                    <label class="block text-sm mb-1">Suspension Date</label>
                                                    <x-base.litepicker name="suspended_date" value="{{ old('suspended_date', $vehicle->suspended_date) }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('suspended_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Notes --}}
                                <div class="my-3">
                                    <div class="mb-4 sm:mr-5 text-gray-800 border-b pb-2">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <h3 class="text-lg font-semibold text-gray-800">Notes</h3>
                                            </div>
                                        </div>
                                    </div>
                                        <div>
                                            <textarea name="notes" rows="4" class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-gray-500 focus:ring-gray-500 transition-colors resize-none"
                                                placeholder="Enter any additional notes about this vehicle">{{ old('notes', $vehicle->notes) }}</textarea>
                                            @error('notes')
                                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB: SERVICE ITEMS --}}
                    <div x-show="activeTab === 'service'">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">Maintenance History</h3>
                            <p class="text-sm text-gray-600 mb-6">View maintenance records for this vehicle. Select a record to view details and navigate to edit it.</p>

                            @if(isset($maintenanceHistory) && $maintenanceHistory->count() > 0)
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-2">Select Maintenance Record</label>
                                <select id="maintenanceSelect" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm" onchange="showMaintenanceDetails(this.value)">
                                    <option value="">-- Select a maintenance record --</option>
                                    @foreach($maintenanceHistory as $maintenance)
                                    <option value="{{ $maintenance->id }}"
                                        data-service-date="{{ $maintenance->service_date ? $maintenance->service_date->format('Y-m-d') : '' }}"
                                        data-next-service="{{ $maintenance->next_service_date ? $maintenance->next_service_date->format('Y-m-d') : '' }}"
                                        data-service-type="{{ $maintenance->service_type ?? '' }}"
                                        data-description="{{ $maintenance->description ?? '' }}"
                                        data-cost="{{ $maintenance->cost ?? '' }}"
                                        data-odometer="{{ $maintenance->odometer_reading ?? '' }}"
                                        data-vendor="{{ $maintenance->vendor_name ?? '' }}"
                                        data-status="{{ $maintenance->status ?? '' }}">
                                        {{ $maintenance->service_date ? $maintenance->service_date->format('M d, Y') : 'No Date' }} -
                                        {{ $maintenance->service_type ?? 'General Maintenance' }}
                                        @if($maintenance->cost)
                                        (${{ number_format($maintenance->cost, 2) }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="maintenanceDetails" class="hidden border p-4 rounded-lg bg-gray-50">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-semibold text-lg">Maintenance Details</h4>
                                    <button type="button" id="editMaintenanceBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm" onclick="editMaintenance()">
                                        Edit Record
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Service Date</label>
                                        <p id="detailServiceDate" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Next Service Date</label>
                                        <p id="detailNextService" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Service Type</label>
                                        <p id="detailServiceType" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Cost</label>
                                        <p id="detailCost" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Odometer Reading</label>
                                        <p id="detailOdometer" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vendor</label>
                                        <p id="detailVendor" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                        <p id="detailStatus" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <p id="detailDescription" class="text-sm text-gray-900 bg-white p-2 rounded border">-</p>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-4">
                                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Maintenance Records</h3>
                                <p class="text-gray-500 mb-4">This vehicle doesn't have any maintenance records yet.</p>
                                <a href="{{ route('admin.maintenance.create', ['vehicle_id' => $vehicle->id]) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Add First Maintenance Record
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Botones Submit/Cancel --}}
                <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end mt-6">
                    <button type="submit"
                        class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
                        Update Vehicle
                    </button>
                    <a href="{{ route('admin.vehicles.index') }}"
                        class="border border-gray-300 ml-2 px-4 py-2 rounded text-gray-600 hover:bg-gray-100">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal para agregar nueva marca -->
<div id="addMakeModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="addMakeForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Agregar Nueva Marca</h3>
                            <div class="mt-4">
                                <label for="makeName" class="block text-sm font-medium text-gray-700">Nombre de la Marca</label>
                                <input type="text" id="makeName" name="makeName" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Ingrese el nombre de la marca">
                                <div id="makeError" class="mt-2 text-sm text-red-600 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Agregar
                    </button>
                    <button type="button" onclick="closeMakeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para agregar nuevo tipo -->
<div id="addTypeModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="addTypeForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Agregar Nuevo Tipo</h3>
                            <div class="mt-4">
                                <label for="typeName" class="block text-sm font-medium text-gray-700">Nombre del Tipo</label>
                                <input type="text" id="typeName" name="typeName" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    placeholder="Ingrese el nombre del tipo">
                                <div id="typeError" class="mt-2 text-sm text-red-600 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Agregar
                    </button>
                    <button type="button" onclick="closeTypeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
    console.log('Listo el JS')
    
    // Función para mostrar detalles del mantenimiento seleccionado
    function showMaintenanceDetails() {
        const select = document.getElementById('maintenanceSelect');
        const detailsDiv = document.getElementById('maintenanceDetails');
        const editBtn = document.getElementById('editMaintenanceBtn');

        if (!select.value) {
            detailsDiv.classList.add('hidden');
            return;
        }

        const selectedOption = select.options[select.selectedIndex];

        // Mostrar el div de detalles
        detailsDiv.classList.remove('hidden');

        // Llenar los campos con los datos
        document.getElementById('detailServiceDate').textContent = selectedOption.dataset.serviceDate || '-';
        document.getElementById('detailNextService').textContent = selectedOption.dataset.nextService || '-';
        document.getElementById('detailServiceType').textContent = selectedOption.dataset.serviceType || '-';
        document.getElementById('detailCost').textContent = selectedOption.dataset.cost ? '$' + parseFloat(selectedOption.dataset.cost).toFixed(2) : '-';
        document.getElementById('detailOdometer').textContent = selectedOption.dataset.odometer || '-';
        document.getElementById('detailVendor').textContent = selectedOption.dataset.vendor || '-';
        document.getElementById('detailStatus').textContent = selectedOption.dataset.status || '-';
        document.getElementById('detailDescription').textContent = selectedOption.dataset.description || '-';

        // Configurar el botón de editar
        editBtn.dataset.maintenanceId = select.value;
    }

    // Función para navegar a la edición del mantenimiento
    function editMaintenance() {
        const editBtn = document.getElementById('editMaintenanceBtn');
        const maintenanceId = editBtn.dataset.maintenanceId;

        if (maintenanceId) {
            // Navegar a la página de edición del mantenimiento
            window.location.href = `/admin/maintenance/${maintenanceId}/edit`;
        }
    }

    // Modal functions - copied exactly from create.blade.php
    function openMakeModal() {
        document.getElementById('addMakeModal').classList.remove('hidden');
        document.getElementById('newMakeName').focus();
    }
    
    function closeMakeModal() {
        document.getElementById('addMakeModal').classList.add('hidden');
        document.getElementById('newMakeName').value = '';
        document.getElementById('makeError').classList.add('hidden');
    }
    
    function openTypeModal() {
        document.getElementById('addTypeModal').classList.remove('hidden');
        document.getElementById('newTypeName').focus();
    }
    
    function closeTypeModal() {
        document.getElementById('addTypeModal').classList.add('hidden');
        document.getElementById('newTypeName').value = '';
        document.getElementById('typeError').classList.add('hidden');
    }
    
    // Función para mostrar errores en modales
    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
    
    // Función para ocultar errores
    function hideError(elementId) {
        document.getElementById(elementId).classList.add('hidden');
    }
    
    // Función principal para agregar nueva marca
    function addNewMake() {
        openMakeModal();
    }
    
    // Función principal para agregar nuevo tipo
    function addNewType() {
        openTypeModal();
    }
    
    // Event listeners para los formularios
    document.addEventListener('DOMContentLoaded', function() {
        // Formulario de marca
        const addMakeFormSecond = document.getElementById('addMakeForm');
        if (addMakeFormSecond) {
            addMakeFormSecond.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const makeNameInput = document.getElementById('makeName');
                const makeName = makeNameInput ? makeNameInput.value.trim() : '';
                if (!makeName) {
                    showError('makeError', 'El nombre de la marca es requerido.');
                    return;
                }
            
            hideError('makeError');
            
            // Usar API endpoint
            fetch('/api/vehicles/makes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: makeName
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Agregar nueva opción al select
                    const makeSelect = document.querySelector('select[name="make"]');
                    if (makeSelect) {
                        const newOption = document.createElement('option');
                        newOption.value = data.data.name;
                        newOption.textContent = data.data.name;
                        
                        // Insertar antes de la opción "Add New..."
                        const addNewOption = makeSelect.querySelector('option[value="__add_new__"]');
                        if (addNewOption) {
                            makeSelect.insertBefore(newOption, addNewOption);
                        } else {
                            makeSelect.appendChild(newOption);
                        }
                        
                        // Seleccionar la nueva opción y actualizar Alpine.js
                        makeSelect.value = data.data.name;
                        
                        // Actualizar el modelo de Alpine.js directamente
                        const alpineComponent = Alpine.$data(makeSelect.closest('[x-data]'));
                        if (alpineComponent) {
                            alpineComponent.make = data.data.name;
                        }
                        
                        // Disparar evento change para Alpine.js
                        makeSelect.dispatchEvent(new Event('change'));
                    }
                    
                    closeMakeModal();
                    
                    // Mostrar notificación de éxito (sin alert)
                    console.log('Marca agregada correctamente: ' + data.data.name);
                } else {
                    showError('makeError', data.message || 'Error al agregar la marca.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.errors && error.errors.name) {
                    showError('makeError', error.errors.name[0]);
                } else {
                    showError('makeError', error.message || 'Error de conexión. Por favor, inténtelo de nuevo.');
                }
                });
            });
        }
        
        // Formulario de tipo
        const addTypeFormSecond = document.getElementById('addTypeForm');
        if (addTypeFormSecond) {
            addTypeFormSecond.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const typeNameInput = document.getElementById('typeName');
                const typeName = typeNameInput ? typeNameInput.value.trim() : '';
                if (!typeName) {
                    showError('typeError', 'El nombre del tipo es requerido.');
                    return;
                }
            
            hideError('typeError');
            
            // Usar API endpoint
            fetch('/api/vehicles/types', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: typeName
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Agregar nueva opción al select
                    const typeSelect = document.querySelector('select[name="type"]');
                    if (typeSelect) {
                        const newOption = document.createElement('option');
                        newOption.value = data.data.name;
                        newOption.textContent = data.data.name;
                        
                        // Insertar antes de la opción "Add New..."
                        const addNewOption = typeSelect.querySelector('option[value="__add_new__"]');
                        if (addNewOption) {
                            typeSelect.insertBefore(newOption, addNewOption);
                        } else {
                            typeSelect.appendChild(newOption);
                        }
                        
                        // Seleccionar la nueva opción y actualizar Alpine.js
                        typeSelect.value = data.data.name;
                        
                        // Actualizar el modelo de Alpine.js directamente
                        const alpineComponent = Alpine.$data(typeSelect.closest('[x-data]'));
                        if (alpineComponent) {
                            alpineComponent.type = data.data.name;
                        }
                        
                        // Disparar evento change para Alpine.js
                        typeSelect.dispatchEvent(new Event('change'));
                    }
                    
                    closeTypeModal();
                    
                    // Mostrar notificación de éxito (sin alert)
                    console.log('Tipo agregado correctamente: ' + data.data.name);
                } else {
                    showError('typeError', data.message || 'Error al agregar el tipo.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.errors && error.errors.name) {
                    showError('typeError', error.errors.name[0]);
                } else {
                    showError('typeError', error.message || 'Error de conexión. Por favor, inténtelo de nuevo.');
                }
                });
            });
        }
        
        // Cerrar modales al hacer clic fuera de ellos
        const addMakeModalSecond = document.getElementById('addMakeModal');
        if (addMakeModalSecond) {
            addMakeModalSecond.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMakeModal();
                }
            });
        }
        
        const addTypeModalSecond = document.getElementById('addTypeModal');
        if (addTypeModalSecond) {
            addTypeModalSecond.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeTypeModal();
                }
            });
        }
    });
</script>
@endpush