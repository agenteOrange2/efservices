@extends('../themes/' . $activeTheme)
@section('title', 'Add Vehicle')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
['label' => 'Create Vehicle', 'active' => true],
];
@endphp
@section('subcontent')
<div>
    <div class="box box--stacked flex flex-col">
        <div class="box-body">
            <form action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <x-validation-errors class="my-4" />
                {{-- Contenedor Alpine con toda la lógica --}}
                <div x-data="{
                                activeTab: 'general',
                                // Datos del vehículo
                                make: '{{ old('make', '') }}',
                                model: '{{ old('model', '') }}',
                                type: '{{ old('type', '') }}',
                                year: '{{ old('year', '') }}',
                                vin: '{{ old('vin', '') }}',
                                // Campos de registro
                                registrationState: '{{ old('registration_state', '') }}',
                                registrationNumber: '{{ old('registration_number', '') }}',
                                registrationExpirationDate: '{{ old('registration_expiration_date', '') }}',
                                permanentTag: {{ old('permanent_tag', 'false') }},
                                // Estado
                                outOfService: {{ old('out_of_service', 'false') }},
                                outOfServiceDate: '{{ old('out_of_service_date', '') }}',
                                suspended: {{ old('suspended', 'false') }},
                                suspendedDate: '{{ old('suspended_date', '') }}',
                                
                                // Service Items
                                serviceItems: [{
                                    unit: '',
                                    service_date: '',
                                    next_service_date: '',
                                    service_tasks: '',
                                    vendor_mechanic: '',
                                    description: '',
                                    cost: '',
                                    odometer: ''
                                }],
                                
                                // Métodos para service items
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
                                },
                                
                                // VIN validation
                                vinError: '',
                                validateVin() {
                                    if (this.vin.length === 0) {
                                        this.vinError = '';
                                        return true;
                                    }
                                    if (this.vin.length !== 17) {
                                        this.vinError = 'VIN must be exactly 17 characters';
                                        return false;
                                    }
                                    // Check for invalid characters (I, O, Q are not allowed in VIN)
                                    if (/[IOQ]/.test(this.vin.toUpperCase())) {
                                        this.vinError = 'VIN cannot contain letters I, O, or Q';
                                        return false;
                                    }
                                    this.vinError = '';
                                    return true;
                                },
                                
                                // Date validation
                                registrationDateError: '',
                                inspectionDateError: '',
                                validateDate(field) {
                                    const today = new Date();
                                    today.setHours(0, 0, 0, 0);
                                    
                                    if (field === 'registration') {
                                        if (!this.registrationExpirationDate) {
                                            this.registrationDateError = '';
                                            return true;
                                        }
                                        const selectedDate = new Date(this.registrationExpirationDate);
                                        if (selectedDate <= today) {
                                            this.registrationDateError = 'Registration expiration date must be in the future';
                                            return false;
                                        }
                                        this.registrationDateError = '';
                                        return true;
                                    }
                                    
                                    if (field === 'inspection') {
                                        if (!this.annualInspectionExpirationDate) {
                                            this.inspectionDateError = '';
                                            return true;
                                        }
                                        const selectedDate = new Date(this.annualInspectionExpirationDate);
                                        if (selectedDate <= today) {
                                            this.inspectionDateError = 'Annual inspection expiration date must be in the future';
                                            return false;
                                        }
                                        this.inspectionDateError = '';
                                        return true;
                                    }
                                },
                                
                                // Add annual inspection date to data
                                annualInspectionExpirationDate: '{{ old('annual_inspection_expiration_date', '') }}',

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
                                            <select id="carrier_id" name="carrier_id"
                                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                <option value="">Select Carrier</option>
                                                @foreach ($carriers as $carrier)
                                                <option value="{{ $carrier->id }}"
                                                    {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                                    {{ $carrier->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('carrier_id')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Driver Assignment Note --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-5 sm:mr-5 xl:mr-14 xl:w-60">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium text-lg">Driver Assignment</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <div>
                                                        <h4 class="font-medium text-blue-800">Driver Assignment</h4>
                                                        <p class="text-sm text-blue-700 mt-1">
                                                            Drivers can be assigned to this vehicle after creation using the new assignment system.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Make/Model/Type --}}
                                    <div class="flex flex-col my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <h3 class="font-medium text-lg  ">Vehicle Details</h3>
                                                    <div
                                                        class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                {{-- Make (with dropdown) --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Make</label>
                                                    <div class="flex gap-2">
                                                        <select name="make" x-model="make" @change="if($event.target.value === '__add_new__') { addNewMake(); $event.target.value = ''; }"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm flex-1">
                                                            <option value="">Select a make</option>
                                                            @foreach ($vehicleMakes as $make)
                                                            <option value="{{ $make->name }}">{{ $make->name }}</option>
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
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. Cascadia">
                                                    @error('model')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Type (with dropdown) --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Type</label>
                                                    <div class="flex gap-2">
                                                        <select name="type" x-model="type" @change="if($event.target.value === '__add_new__') { addNewType(); $event.target.value = ''; }"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm flex-1">
                                                            <option value="">Select a type</option>
                                                            @foreach ($vehicleTypes as $type)
                                                            <option value="{{ $type->name }}">{{ $type->name }}</option>
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
                                                        value="{{ old('company_unit_number') }}"
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
                                                        @input="validateVin()"
                                                        :class="{'border-red-500': vinError, 'border-green-500': vin && vin.length === 17 && !vinError}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. 1HGBH41JXMN109186">
                                                    <p class="text-red-500 text-xs mt-1" x-show="vinError" x-text="vinError"></p>
                                                    <p class="text-green-500 text-xs mt-1" x-show="!vinError && vin && vin.length === 17">✓ Valid VIN format</p>
                                                    <p class="text-gray-500 text-xs mt-1" x-show="!vinError && (!vin || vin.length !== 17)">VIN must be exactly 17 characters</p>
                                                    @error('vin')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- GVWR and Tire Size --}}
                                    <div class="my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium text-lg">Technical Details</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                {{-- GVWR --}}
                                                <div>
                                                    <label class="block text-sm mb-1">GVWR</label>
                                                    <input type="text" name="gvwr"
                                                        value="{{ old('gvwr') }}"
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
                                                        value="{{ old('tire_size') }}"
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
                                    <div class="my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium text-lg">Fuel & Registration</div>
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
                                                            {{ old('fuel_type') == 'Diesel' ? 'selected' : '' }}>
                                                            Diesel</option>
                                                        <option value="Gasoline"
                                                            {{ old('fuel_type') == 'Gasoline' ? 'selected' : '' }}>
                                                            Gasoline</option>
                                                        <option value="CNG"
                                                            {{ old('fuel_type') == 'CNG' ? 'selected' : '' }}>CNG
                                                        </option>
                                                        <option value="LNG"
                                                            {{ old('fuel_type') == 'LNG' ? 'selected' : '' }}>LNG
                                                        </option>
                                                        <option value="Electric"
                                                            {{ old('fuel_type') == 'Electric' ? 'selected' : '' }}>
                                                            Electric</option>
                                                        <option value="Hybrid"
                                                            {{ old('fuel_type') == 'Hybrid' ? 'selected' : '' }}>
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
                                                        {{ old('irp_apportioned_plate') ? 'checked' : '' }}>
                                                    <label class="ms-2 text-sm font-medium text-gray-900">
                                                        IRP (Apportioned Plate)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Registration Information --}}
                                    <div class="my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium text-lg">Registration Info</div>
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
                                                            {{ old('registration_state') == $code ? 'selected' : '' }}>
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
                                                        value="{{ old('location') }}"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="Ex. Main Terminal">
                                                    @error('location')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Registration Number --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Registration Number</label>
                                                    <input type="text" name="registration_number"
                                                        x-model="registrationNumber"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                        placeholder="e.g. ABC1234">
                                                    @error('registration_number')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Registration Expiration --}}
                                                <div>
                                                    <label class="block text-sm mb-1">Expiration Date <span class="text-red-500">*</span></label>
                                                    <x-base.litepicker name="registration_expiration_date" value="{{ old('registration_expiration_date') }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('registration_expiration_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
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
                                    <div class="my-3">
                                        <div class="mb-4 sm:mr-5 xl:mr-14 text-gray-800 border-b pb-2">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <h3 class="font-medium text-lg">Annual Inspection
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <label class="block text-sm mb-1">Expiration Date <span class="text-red-500">*</span></label>
                                            <x-base.litepicker name="annual_inspection_expiration_date" value="{{ old('annual_inspection_expiration_date') }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('annual_inspection_expiration_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
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
                                        {{-- Out of Service --}}
                                        <div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="out_of_service" value="1"
                                                    x-model="outOfService"
                                                    class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500">
                                                <label class="ms-2 text-sm font-medium text-gray-900">
                                                    Out of Service
                                                </label>
                                            </div>
                                            <div x-show="outOfService" class="mt-2 ml-6">
                                                <label class="block text-sm mb-1">Out of Service Date</label>
                                                <x-base.litepicker name="out_of_service_date" value="{{ old('out_of_service_date') }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('out_of_service_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
                                            </div>
                                        </div>
                                        {{-- Suspended --}}
                                        <div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="suspended" value="1"
                                                    x-model="suspended"
                                                    class="w-4 h-4 text-yellow-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500">
                                                <label class="ms-2 text-sm font-medium text-gray-900">
                                                    Suspended
                                                </label>
                                            </div>
                                            <div x-show="suspended" class="mt-2 ml-6">
                                                <label class="block text-sm mb-1">Suspension Date</label>
                                                <x-base.litepicker name="suspended_date" value="{{ old('suspended_date') }}" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm @error('suspended_date') border-red-500 @enderror" placeholder="MM/DD/YYYY" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div class="my-3">
                                    <div class="mb-4 sm:mr-5 text-gray-800 border-b pb-2">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium text-lg">Notes</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <textarea name="notes" rows="4" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                            placeholder="Enter any additional notes about this vehicle">{{ old('notes') }}</textarea>
                                        @error('notes')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Botones Submit/Cancel --}}
                <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end mt-6">
                    <button type="submit"
                        class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
                        Save Vehicle
                    </button>
                    <a href="{{ route('admin.vehicles.index') }}"
                        class="border border-gray-300 ml-2 px-4 py-2 rounded text-gray-600 hover:bg-gray-100">
                        Cancel
                    </a>
                </div>
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

@push('scripts')
<script>
    console.log('Vehicle form JS loaded');
    
    // Funciones para manejar modales
    function openMakeModal() {
        document.getElementById('addMakeModal').classList.remove('hidden');
        document.getElementById('makeName').focus();
    }
    
    function closeMakeModal() {
        document.getElementById('addMakeModal').classList.add('hidden');
        document.getElementById('makeName').value = '';
        document.getElementById('makeError').classList.add('hidden');
    }
    
    function openTypeModal() {
        document.getElementById('addTypeModal').classList.remove('hidden');
        document.getElementById('typeName').focus();
    }
    
    function closeTypeModal() {
        document.getElementById('addTypeModal').classList.add('hidden');
        document.getElementById('typeName').value = '';
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
        document.getElementById('addMakeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const makeName = document.getElementById('makeName').value.trim();
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
        
        // Formulario de tipo
        document.getElementById('addTypeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const typeName = document.getElementById('typeName').value.trim();
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
        
        // Cerrar modales al hacer clic fuera de ellos
        document.getElementById('addMakeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMakeModal();
            }
        });
        
        document.getElementById('addTypeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTypeModal();
            }
        });
        
        // Cerrar modales con la tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMakeModal();
                closeTypeModal();
            }
        });
    });
</script>
@endpush

{{-- Driver assignment functionality has been moved to separate forms after vehicle creation --}}