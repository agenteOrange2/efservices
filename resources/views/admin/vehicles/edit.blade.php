@extends('../themes/' . $activeTheme)
@section('title', 'Edit Vehicle')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
['label' => 'Edit Vehicle', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12 sm:col-span-10 sm:col-start-2">
        <div class="mt-7">
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
                                        <h3 class="text-lg font-semibold mb-4">Vehicle Information</h3>

                                        {{-- Carrier --}}
                                        <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
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
                                                <select id="carrier_id" name="carrier_id"
                                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                    <option value="">Select Carrier</option>
                                                    @foreach ($carriers as $carrier)
                                                    <option value="{{ $carrier->id }}"
                                                        {{ old('carrier_id', $vehicle->carrier_id) == $carrier->id ? 'selected' : '' }}>
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
                                        <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                <div class="text-left">
                                                    <div class="flex items-center">
                                                        <div class="font-medium">Driver Assignment</div>
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
                                                                    <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $vehicle->currentDriverAssignment->assignment_type)) }}<br>
                                                                    @if($vehicle->currentDriverAssignment->user)
                                                                        <strong>Driver:</strong> {{ $vehicle->currentDriverAssignment->user->name }}<br>
                                                                    @endif
                                                                    <strong>Effective:</strong> {{ $vehicle->currentDriverAssignment->effective_date->format('M d, Y') }}
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
                                        <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                <div class="text-left">
                                                    <div class="flex items-center">
                                                        <div class="font-medium">Vehicle Details</div>
                                                        <div
                                                            class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                            Required
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3 w-full flex-1 xl:mt-0">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    {{-- Make (with datalist) --}}
                                                    <div>
                                                        <label class="block text-sm mb-1">Make</label>
                                                        <input type="text" name="make" x-model="make"
                                                            list="vehicle-makes"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="e.g. Freightliner">
                                                        <datalist id="vehicle-makes">
                                                            @foreach ($vehicleMakes as $make)
                                                            <option value="{{ $make->name }}">
                                                                @endforeach
                                                        </datalist>
                                                        @error('make')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    {{-- Model --}}
                                                    <div>
                                                        <label class="block text-sm mb-1">Model</label>
                                                        <input type="text" name="model" x-model="model"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="e.g. Cascadia">
                                                        @error('model')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    {{-- Type (with datalist) --}}
                                                    <div>
                                                        <label class="block text-sm mb-1">Type</label>
                                                        <input type="text" name="type" x-model="type"
                                                            list="vehicle-types"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="e.g. Semi-Truck">
                                                        <datalist id="vehicle-types">
                                                            @foreach ($vehicleTypes as $type)
                                                            <option value="{{ $type->name }}">
                                                                @endforeach
                                                        </datalist>
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
                                        <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                <div class="text-left">
                                                    <div class="flex items-center">
                                                        <div class="font-medium">Technical Details</div>
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
                                        <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
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
                                        <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                <div class="text-left">
                                                    <div class="flex items-center">
                                                        <div class="font-medium">Registration Info</div>
                                                        <div
                                                            class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                            Required
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 w-full flex-1 xl:mt-0">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                                        <input type="date" name="registration_expiration_date"
                                                            x-model="registrationExpirationDate"
                                                            @change="validateDate($event.target.value, 'registration')"
                                                            :class="{'border-red-500': registrationDateError, 'border-green-500': registrationExpirationDate && !registrationDateError}"
                                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        <p class="text-red-500 text-xs mt-1" x-show="registrationDateError" x-text="registrationDateError"></p>
                                                        <p class="text-gray-500 text-xs mt-1" x-show="!registrationDateError">Must be a future date</p>
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
                                        <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-sm">
                                            <div class="flex items-center mb-4">
                                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-semibold text-gray-800">Annual Inspection</h3>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Expiration Date <span class="text-red-500">*</span></label>
                                                    <input type="date" name="annual_inspection_expiration_date"
                                                        value="{{ old('annual_inspection_expiration_date', $vehicle->annual_inspection_expiration_date ? $vehicle->annual_inspection_expiration_date->format('Y-m-d') : '') }}"
                                                        x-model="annualInspectionExpirationDate"
                                                        @change="validateDate($event.target.value, 'inspection')"
                                                        :class="{'border-red-500': inspectionDateError, 'border-green-500': annualInspectionExpirationDate && !inspectionDateError}"
                                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                                    <p class="text-red-500 text-xs mt-1" x-show="inspectionDateError" x-text="inspectionDateError"></p>
                                                    <p class="text-gray-500 text-xs mt-1" x-show="!inspectionDateError">Must be a future date</p>
                                                    @error('annual_inspection_expiration_date')
                                                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Status --}}
                                        <div class="mt-6 bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl p-6 shadow-sm">
                                            <div class="flex items-center mb-4">
                                                <div class="bg-red-100 p-2 rounded-lg mr-3">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-semibold text-gray-800">Status</h3>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                                    <div x-show="outOfService" class="ml-7">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Out of Service Date</label>
                                                        <input type="date" name="out_of_service_date"
                                                            x-model="outOfServiceDate"
                                                            class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-red-500 focus:ring-red-500 transition-colors">
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
                                                    <div x-show="suspended" class="ml-7">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Suspension Date</label>
                                                        <input type="date" name="suspended_date"
                                                            x-model="suspendedDate"
                                                            class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-yellow-500 focus:ring-yellow-500 transition-colors">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Notes --}}
                                        <div class="mt-6 bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200 rounded-xl p-6 shadow-sm">
                                            <div class="flex items-center mb-4">
                                                <div class="bg-gray-100 p-2 rounded-lg mr-3">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-semibold text-gray-800">Notes</h3>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
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

    // Driver assignment functionality has been moved to separate assignment forms
    // No dynamic driver loading needed in edit form anymore
</script>
@endpush