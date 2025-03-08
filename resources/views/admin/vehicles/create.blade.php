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
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
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
                                // Propiedad
                                ownershipType: '{{ old('ownership_type', 'owned') }}',
                                // Servicio
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
                            }">

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
                                                    <select name="carrier_id"
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
                                                                value="{{ old('company_unit_number') }}"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                                placeholder="e.g. 12345">
                                                            @error('company_unit_number')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>

                                                        {{-- VIN --}}
                                                        <div>
                                                            <label class="block text-sm mb-1">VIN</label>
                                                            <input type="text" name="vin" x-model="vin"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                                placeholder="e.g. 1HGBH41JXMN109186">
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
                                                                        {{ old('registration_state') == $code ? 'selected' : '' }}>
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
                                                            <label class="block text-sm mb-1">Expiration Date</label>
                                                            <input type="date" name="registration_expiration_date"
                                                                x-model="registrationExpirationDate"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
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

                                            {{-- Ownership and Location --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Ownership & Location</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        {{-- Ownership Type --}}
                                                        <div>
                                                            <label class="block text-sm mb-1">Ownership Type</label>
                                                            <select name="ownership_type" x-model="ownershipType"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                                <option value="owned"
                                                                    {{ old('ownership_type') == 'owned' ? 'selected' : '' }}>
                                                                    Owned</option>
                                                                <option value="leased"
                                                                    {{ old('ownership_type') == 'leased' ? 'selected' : '' }}>
                                                                    Leased</option>
                                                            </select>
                                                            @error('ownership_type')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>

                                                        {{-- Location --}}
                                                        <div>
                                                            <label class="block text-sm mb-1">Location</label>
                                                            <input type="text" name="location"
                                                                value="{{ old('location') }}"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                                placeholder="e.g. Main Terminal">
                                                            @error('location')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Assigned Driver --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Assigned Driver</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <select name="user_driver_detail_id"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        <option value="">None (Unassigned)</option>
                                                        @foreach ($drivers as $driver)
                                                            <option value="{{ $driver->id }}"
                                                                {{ old('user_driver_detail_id') == $driver->id ? 'selected' : '' }}>
                                                                {{ $driver->user->name }} {{ $driver->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('user_driver_detail_id')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Annual Inspection --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Annual Inspection</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <div>
                                                        <label class="block text-sm mb-1">Expiration Date</label>
                                                        <input type="date" name="annual_inspection_expiration_date"
                                                            value="{{ old('annual_inspection_expiration_date') }}"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        @error('annual_inspection_expiration_date')
                                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Status --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Status</div>
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
                                                            <input type="date" name="out_of_service_date"
                                                                x-model="outOfServiceDate"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
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
                                                            <input type="date" name="suspended_date"
                                                                x-model="suspendedDate"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Notes --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Notes</div>
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

                                {{-- TAB: SERVICE ITEMS --}}
                                <div x-show="activeTab === 'service'">
                                    <div class="bg-white p-4 rounded-lg shadow">
                                        <h3 class="text-lg font-semibold mb-4">Service History</h3>
                                        <p class="text-sm text-gray-600 mb-6">Add any maintenance or service records for
                                            this vehicle.</p>

                                        {{-- Service Items with Alpine Loop --}}
                                        <template x-for="(item, index) in serviceItems" :key="index">
                                            <div class="border p-4 rounded-lg mb-6">
                                                <div class="flex justify-between items-center mb-4">
                                                    <h4 class="font-semibold" x-text="`Service Item ${index + 1}`"></h4>
                                                    <button type="button" @click="removeServiceItem(index)"
                                                        class="text-red-500 hover:text-red-700"
                                                        x-show="serviceItems.length > 1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                {{-- Service Dates --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label class="block text-sm mb-1">Service Date</label>
                                                        <input type="date"
                                                            :name="`service_items[${index}][service_date]`"
                                                            x-model="item.service_date"
                                                            @change="validateServiceDate(index)"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm mb-1">Next Service Date</label>
                                                        <input type="date"
                                                            :name="`service_items[${index}][next_service_date]`"
                                                            x-model="item.next_service_date"
                                                            @change="validateServiceDate(index)"
                                                            :class="{ 'border-red-500': item.dateError }"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        <p class="text-red-500 text-xs mt-1" x-show="item.dateError"
                                                            x-text="item.dateError"></p>
                                                    </div>
                                                </div>

                                                {{-- Unit and Tasks --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label class="block text-sm mb-1">Unit</label>
                                                        <input type="text" :name="`service_items[${index}][unit]`"
                                                            x-model="item.unit"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="Unit number or identifier">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm mb-1">Service Tasks</label>
                                                        <input type="text"
                                                            :name="`service_items[${index}][service_tasks]`"
                                                            x-model="item.service_tasks"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="e.g. Oil change, brake inspection">
                                                    </div>
                                                </div>

                                                {{-- Vendor and Cost --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label class="block text-sm mb-1">Vendor/Mechanic</label>
                                                        <input type="text"
                                                            :name="`service_items[${index}][vendor_mechanic]`"
                                                            x-model="item.vendor_mechanic"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="e.g. ABC Auto Shop">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm mb-1">Cost ($)</label>
                                                        <input type="number" :name="`service_items[${index}][cost]`"
                                                            x-model="item.cost" step="0.01" min="0"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="0.00">
                                                    </div>
                                                </div>

                                                {{-- Odometer and Description --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label class="block text-sm mb-1">Odometer Reading</label>
                                                        <input type="number" :name="`service_items[${index}][odometer]`"
                                                            x-model="item.odometer" min="0"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="e.g. 50000">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm mb-1">Description</label>
                                                        <textarea :name="`service_items[${index}][description]`" x-model="item.description" rows="2"
                                                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                            placeholder="Additional details about the service"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Add Service Item Button --}}
                                        <div class="flex justify-center mt-4">
                                            <button type="button" @click="addServiceItem"
                                                class="py-2 px-4 border border-primary text-primary hover:bg-primary hover:text-white transition rounded">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Add Service Item
                                            </button>
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Este script se puede usar para inicializar cualquier componente JS adicional que necesites
        document.addEventListener('DOMContentLoaded', function() {
            // Aquí puedes inicializar componentes como Select2, Datepicker, etc.
        });
    </script>
@endpush
