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
                                // Propiedad
                                ownershipType: '{{ old('ownership_type', $vehicle->ownership_type) }}',
                                // Información del propietario
                                ownerName: '{{ old('owner_name', $ownerDetails ? $ownerDetails->owner_name : '') }}',
                                ownerPhone: '{{ old('owner_phone', $ownerDetails ? $ownerDetails->owner_phone : '') }}',
                                ownerEmail: '{{ old('owner_email', $ownerDetails ? $ownerDetails->owner_email : '') }}',
                                // Información del tercero
                                thirdPartyName: '{{ old('third_party_name', $thirdPartyDetails ? $thirdPartyDetails->third_party_name : '') }}',
                                thirdPartyPhone: '{{ old('third_party_phone', $thirdPartyDetails ? $thirdPartyDetails->third_party_phone : '') }}',
                                thirdPartyEmail: '{{ old('third_party_email', $thirdPartyDetails ? $thirdPartyDetails->third_party_email : '') }}',
                                thirdPartyDba: '{{ old('third_party_dba', $thirdPartyDetails ? $thirdPartyDetails->third_party_dba : '') }}',
                                thirdPartyAddress: '{{ old('third_party_address', $thirdPartyDetails ? $thirdPartyDetails->third_party_address : '') }}',
                                thirdPartyContact: '{{ old('third_party_contact', $thirdPartyDetails ? $thirdPartyDetails->third_party_contact : '') }}',
                                thirdPartyFein: '{{ old('third_party_fein', $thirdPartyDetails ? $thirdPartyDetails->third_party_fein : '') }}',
                                emailSent: {{ old('email_sent', $thirdPartyDetails && $thirdPartyDetails->email_sent ? 'true' : 'false') }},
                                selectedDriverId: '{{ old('user_driver_detail_id', $vehicle->user_driver_detail_id) }}',
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

                                // Método para autocompletar datos del Owner Operator desde el driver seleccionado
                                updateOwnerFromDriver() {
                                    if (this.ownershipType === 'owned' && this.selectedDriverId) {
                                        // Obtener los datos del driver seleccionado mediante AJAX
                                        fetch(`/admin/vehicles/driver-details/${this.selectedDriverId}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    this.ownerName = data.driver.user.name + ' ' + data.driver.last_name;
                                                    this.ownerPhone = data.driver.phone || '';
                                                    this.ownerEmail = data.driver.user.email || '';
                                                }
                                            })
                                            .catch(error => console.error('Error al obtener datos del driver:', error));
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

                                            {{-- Assigned Driver --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Assigned Driver</div>
                                                            <div class="text-xs text-gray-500 ml-2">(Optional)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <select id="user_driver_detail_id" name="user_driver_detail_id"
                                                        x-model="selectedDriverId"
                                                        @change="updateOwnerFromDriver()"
                                                        class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                        <option value="">None (Unassigned)</option>
                                                        <!-- Los drivers se cargarán dinámicamente vía JavaScript -->
                                                        @foreach ($drivers as $driver)
                                                            <option value="{{ $driver->id }}"
                                                                {{ old('user_driver_detail_id', $vehicle->user_driver_detail_id) == $driver->id ? 'selected' : '' }}>
                                                                {{ $driver->user->name }} {{ $driver->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Only active drivers for the selected carrier will be shown
                                                    </div>
                                                    @error('user_driver_detail_id')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
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
                                                                @change="updateOwnerFromDriver()"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                                                                <option value="unassigned" {{ $vehicle->ownership_type == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                                                <option value="owned" {{ $vehicle->ownership_type == 'owned' ? 'selected' : '' }}>Owner Operator</option>
                                                                <option value="leased" {{ $vehicle->ownership_type == 'leased' ? 'selected' : '' }}>Company Driver</option>
                                                                <option value="third-party" {{ $vehicle->ownership_type == 'third-party' ? 'selected' : '' }}>Third Party Company Driver</option>
                                                            </select>
                                                            @error('ownership_type')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        {{-- Location --}}
                                                        <div>
                                                            <label class="block text-sm mb-1">Location</label>
                                                            <input type="text" name="location"
                                                                value="{{ old('location', $vehicle->location) }}"
                                                                class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                                                placeholder="E.g. Main Terminal">
                                                            @error('location')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <!-- Información del propietario (se muestra cuando ownership_type es "owned") -->
                                                    <div class="mt-4" x-show="ownershipType === 'owned'" x-transition>
                                                        <div class="p-4 border border-blue-200 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 shadow-sm">
                                                            <div class="flex items-center mb-4">
                                                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                    </svg>
                                                                </div>
                                                                <h4 class="font-semibold text-blue-800">Información del Owner Operator</h4>
                                                            </div>
                                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                                <!-- Nombre del propietario -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                                                                    <input type="text" name="owner_name"
                                                                        x-model="ownerName"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                                                        placeholder="Nombre del propietario">
                                                                    @error('owner_name')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <!-- Teléfono del propietario -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                                                    <input type="text" name="owner_phone"
                                                                        x-model="ownerPhone"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                                                        placeholder="Teléfono del propietario">
                                                                    @error('owner_phone')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <!-- Email del propietario -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                                                    <input type="email" name="owner_email"
                                                                        x-model="ownerEmail"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                                                        placeholder="Email del propietario">
                                                                    @error('owner_email')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Información del tercero (se muestra cuando ownership_type es "third-party") -->
                                                    <div class="mt-4" x-show="ownershipType === 'third-party'" x-transition>
                                                        <div class="p-4 border border-purple-200 rounded-lg bg-gradient-to-r from-purple-50 to-pink-50 shadow-sm">
                                                            <div class="flex items-center mb-4">
                                                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                                    </svg>
                                                                </div>
                                                                <h4 class="font-semibold text-purple-800">Información de Third Party Company</h4>
                                                            </div>
                                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                                                <!-- Nombre del tercero -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                                                                    <input type="text" name="third_party_name"
                                                                        x-model="thirdPartyName"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                                                        placeholder="Nombre del tercero">
                                                                    @error('third_party_name')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <!-- Teléfono del tercero -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                                                    <input type="text" name="third_party_phone"
                                                                        x-model="thirdPartyPhone"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                                                        placeholder="Teléfono del tercero">
                                                                    @error('third_party_phone')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <!-- Email del tercero -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                                                    <input type="email" name="third_party_email"
                                                                        x-model="thirdPartyEmail"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                                                        placeholder="Email del tercero">
                                                                    @error('third_party_email')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                                <!-- DBA -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">DBA (Doing Business As)</label>
                                                                    <input type="text" name="third_party_dba"
                                                                        x-model="thirdPartyDba"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                                                        placeholder="Nombre comercial">
                                                                    @error('third_party_dba')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <!-- FEIN -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">FEIN (Federal Employer Identification Number)</label>
                                                                    <input type="text" name="third_party_fein"
                                                                        x-model="thirdPartyFein"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                                                        placeholder="XX-XXXXXXX">
                                                                    @error('third_party_fein')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                                <!-- Dirección -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                                                    <input type="text" name="third_party_address"
                                                                        x-model="thirdPartyAddress"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                                                        placeholder="Dirección completa">
                                                                    @error('third_party_address')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <!-- Contacto -->
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contacto</label>
                                                                    <input type="text" name="third_party_contact"
                                                                        x-model="thirdPartyContact"
                                                                        class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                                                        placeholder="Nombre de contacto">
                                                                    @error('third_party_contact')
                                                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Botón de enviar/reenviar correo -->
                                                            <div class="mt-6">
                                                                <div class="flex items-center">
                                                                    <input type="hidden" name="email_sent" x-model="emailSent">
                                                                    <button type="button" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200" @click="emailSent = true">
                                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                                        </svg>
                                                                        <span x-text="emailSent ? 'Reenviar correo al guardar' : 'Enviar correo al guardar'"></span>
                                                                    </button>
                                                                    <div class="ml-3 text-xs text-gray-600">
                                                                        <span x-show="!emailSent">Se enviará un correo al tercero para que complete su información</span>
                                                                        <span x-show="emailSent">Se reenviará un correo al tercero para que complete su información</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
        
        // Script para manejar la carga dinámica de drivers filtrados por carrier
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener referencias a los elementos select
            const carrierSelect = document.getElementById('carrier_id');
            const driverSelect = document.getElementById('user_driver_detail_id');

            // Si no existen los elementos, salir
            if (!carrierSelect || !driverSelect) return;

            // Función para cargar los drivers según el carrier seleccionado
            function loadDriversByCarrier() {
                const carrierId = carrierSelect.value;

                // Limpiar el dropdown de drivers
                driverSelect.innerHTML = '<option value="">None (Unassigned)</option>';

                // Si no hay carrier seleccionado, no hacemos nada más
                if (!carrierId) return;

                // Mostrar indicador de carga
                driverSelect.disabled = true;
                driverSelect.innerHTML = '<option value="">Loading drivers...</option>';

                // Hacer la petición AJAX
                fetch(`/admin/vehicles/drivers-by-carrier/${carrierId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(drivers => {
                        // Limpiar dropdown
                        driverSelect.innerHTML = '<option value="">None (Unassigned)</option>';

                        // Añadir las nuevas opciones
                        drivers.forEach(driver => {
                            const option = document.createElement('option');
                            option.value = driver.id;

                            // Construir el nombre del driver con el formato adecuado
                            let driverName = driver.user.name;
                            if (driver.middle_name) {
                                driverName += ' ' + driver.middle_name;
                            }
                            driverName += ' ' + driver.last_name;

                            option.textContent = driverName;
                            driverSelect.appendChild(option);
                        });

                        // Si no hay drivers, mostrar mensaje
                        if (drivers.length === 0) {
                            const option = document.createElement('option');
                            option.value = "";
                            option.textContent = "No active drivers found for this carrier";
                            driverSelect.appendChild(option);
                        }

                        // Restaurar la selección anterior si existe
                        const oldValue = "{{ old('user_driver_detail_id', $vehicle->user_driver_detail_id) }}";
                        if (oldValue) {
                            driverSelect.value = oldValue;
                        }
                        
                        // Actualizar el Alpine.js selectedDriverId
                        if (typeof Alpine !== 'undefined') {
                            const vehicleForm = document.querySelector('[x-data]');
                            if (vehicleForm && vehicleForm.__x) {
                                vehicleForm.__x.updateData('selectedDriverId', driverSelect.value);
                                // Si el tipo de propiedad es 'owned', actualizar los datos del propietario
                                if (vehicleForm.__x.getUnobservedData().ownershipType === 'owned') {
                                    vehicleForm.__x.updateOwnerFromDriver();
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading drivers:', error);
                        // Opción de error
                        driverSelect.innerHTML = '<option value="">Error loading drivers</option>';
                    })
                    .finally(() => {
                        // Habilitar el select de drivers
                        driverSelect.disabled = false;
                    });
            }

            // Asignar el evento al cambio de carrier
            carrierSelect.addEventListener('change', loadDriversByCarrier);

            // Cargar drivers inicialmente si hay un carrier seleccionado (útil para volver con errores)
            if (carrierSelect.value) {
                loadDriversByCarrier();
            }
        });
    </script>
@endpush
