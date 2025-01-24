@extends('../themes/' . $activeTheme)
@section('title', 'Add Driver for Carrier: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Create Driver', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <div class="box-body">

                        <form action="{{ route('admin.carrier.user_drivers.store', $carrier) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            {{-- Tabs en Blade --}}
                            <div class="mb-4 border-b border-gray-200">
                                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                                    <li class="mr-2">
                                        <button type="button"
                                            class="inline-block p-4 text-blue-600 border-b-2 border-blue-600"
                                            x-on:click="activeTab = 'general'"
                                            :class="{
                                                'text-blue-600 border-blue-600': activeTab === 'general',
                                                'hover:text-gray-600 hover:border-gray-300': activeTab !== 'general'
                                            }">
                                            General Information
                                        </button>
                                    </li>
                                    <li class="mr-2">
                                        <button type="button"
                                            class="inline-block p-4 hover:text-gray-600 hover:border-gray-300"
                                            x-on:click="activeTab = 'licenses'"
                                            :class="{
                                                'text-blue-600 border-blue-600': activeTab === 'licenses',
                                                'hover:text-gray-600 hover:border-gray-300': activeTab !== 'licenses'
                                            }">
                                            Licenses
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            {{-- Contenedor Alpine con toda la lógica --}}
                            <div x-data="{
                                activeTab: 'general',
                            
                            
                            
                                // Address/History
                                fromDate: '{{ old('from_date') }}',
                                toDate: '{{ old('to_date') }}',
                                livedThreeYears: {{ old('lived_three_years') ? 'true' : 'false' }},
                                previousAddresses: {{ json_encode(old('previous_addresses', [])) }},
                                isAddressValid: false,
                                totalYears: 0,
                            
                                // Términos
                                termsAccepted: {{ old('terms_accepted') ? 'true' : 'false' }},
                            
                                // TWIC
                                hasTwicCard: {{ old('has_twic_card') ? 'true' : 'false' }},
                            
                                // Position
                                applyingPosition: '{{ old('applying_position', '') }}',
                                showOtherPosition: false,
                            
                                // Elegibilidad
                                eligibleToWork: '{{ old('eligible_to_work', '') }}',
                            
                                init() {
                                    // Inicialización de address
                                    this.calculateTotal();
                                    this.$watch('fromDate', () => this.calculateTotal());
                                    this.$watch('toDate', () => this.calculateTotal());
                                    this.$watch('previousAddresses', () => this.calculateTotal(), { deep: true });
                                    this.$watch('livedThreeYears', value => {
                                        if (value) {
                                            this.totalYears = 3;
                                            this.isAddressValid = true;
                                            // No limpiamos previousAddresses
                                        } else {
                                            this.calculateTotal();
                                        }
                                    });
                            
                                    // Mostrar/ocultar input 'other position'
                                    this.showOtherPosition = (this.applyingPosition === 'other');
                                    this.$watch('applyingPosition', val => {
                                        this.showOtherPosition = (val === 'other');
                                    });
                                },
                            
                                calculateDuration(from, to) {
                                    if (!from) return 0;
                                    const fromD = new Date(from);
                                    const toD = to ? new Date(to) : new Date();
                                    const diffMonths = (toD.getFullYear() - fromD.getFullYear()) * 12 +
                                        (toD.getMonth() - fromD.getMonth());
                                    return diffMonths / 12;
                                },
                            
                                calculateTotal() {
                                    if (this.livedThreeYears) {
                                        this.totalYears = 3;
                                        this.isAddressValid = true;
                                        return;
                                    }
                                    let total = this.calculateDuration(this.fromDate, this.toDate);
                                    this.previousAddresses.forEach(addr => {
                                        if (addr.from_date && addr.to_date) {
                                            total += this.calculateDuration(addr.from_date, addr.to_date);
                                        }
                                    });
                                    this.totalYears = total;
                                    this.isAddressValid = (this.totalYears >= 3);
                                },
                            
                                addAddress() {
                                    if (this.isAddressValid || this.livedThreeYears) return;
                                    this.previousAddresses.push({
                                        address_line1: '',
                                        address_line2: '',
                                        city: '',
                                        state: '',
                                        zip_code: '',
                                        from_date: '',
                                        to_date: ''
                                    });
                                },
                            
                                removeAddress(index) {
                                    this.previousAddresses.splice(index, 1);
                                    this.calculateTotal();
                                }
                            
                            }">

                                {{-- TAB: GENERAL --}}
                                <template x-if="activeTab === 'general'">
                                    <div>
                                        <!-- User Information -->
                                        <div class="bg-white p-4 rounded-lg shadow">
                                            <h3 class="text-lg font-semibold mb-4">User Information</h3>

                                            {{-- Photo --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Profile Photo</div>
                                                        </div>
                                                        <div class="mt-1.5 text-xs text-slate-500/80 xl:mt-3">
                                                            Upload a clear and recent driver photo.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <div class="flex items-center">
                                                        <input type="file" name="photo" accept="image/*" id="photo"
                                                            class="hidden">
                                                        <label for="photo" class="cursor-pointer">
                                                            <img src="{{ asset('build/default_profile.png') }}"
                                                                class="w-24 h-24 rounded-full object-cover"
                                                                alt="Profile Photo">
                                                        </label>
                                                    </div>
                                                    @error('photo')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Full Name --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Full Name</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <div class="grid grid-cols-2 gap-4">
                                                        {{-- name --}}
                                                        <x-base.form-input name="name" type="text"
                                                            placeholder="Enter Name" value="{{ old('name') }}" />
                                                        @error('name')
                                                            <span
                                                                class="text-red-500 text-sm col-span-2">{{ $message }}</span>
                                                        @enderror

                                                        {{-- middle_name --}}
                                                        <x-base.form-input name="middle_name" type="text"
                                                            placeholder="Enter Middle name"
                                                            value="{{ old('middle_name') }}" />
                                                        @error('middle_name')
                                                            <span
                                                                class="text-red-500 text-sm col-span-2">{{ $message }}</span>
                                                        @enderror

                                                        {{-- last_name --}}
                                                        <div class="col-span-2">
                                                            <x-base.form-input name="last_name" type="text"
                                                                placeholder="Enter Last name"
                                                                value="{{ old('last_name') }}" />
                                                            @error('last_name')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Email --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center"
                                                x-data>
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Email</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                        <div class="mt-1.5 text-xs text-slate-500/80 xl:mt-3">
                                                            Please provide a valid email address.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <x-base.form-input name="email" type="email"
                                                        placeholder="Enter email" value="{{ old('email') }}" />
                                                    @error('email')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Phone --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Phone</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0" x-data="{ mask: null }"
                                                    x-init="if ($refs.phone) {
                                                        mask = IMask($refs.phone, { mask: '(000) 000-0000' });
                                                    }">
                                                    <x-base.form-input x-ref="phone" name="phone" type="text"
                                                        placeholder="(555) 555-5555" value="{{ old('phone') }}" />
                                                    @error('phone')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Birth Date --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Birth Date</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <x-base.form-input name="date_of_birth" type="date"
                                                        value="{{ old('date_of_birth') }}" />
                                                    @error('date_of_birth')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- State of Issue --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">State of Issue</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                        <div class="mt-1.5 text-xs text-slate-500/80 xl:mt-3">
                                                            Enter your complete State of Issue
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <select name="state_of_issue"
                                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-primary focus:ring-opacity-20">
                                                        <option value="">Select State</option>
                                                        @foreach ($usStates as $code => $name)
                                                            <option value="{{ $code }}"
                                                                {{ old('state_of_issue') == $code ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('state_of_issue')
                                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Password / Confirm --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">New Password</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                        <div class="mt-1.5 text-xs text-slate-500/80 xl:mt-3">
                                                            Create a new password for your account.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <x-base.form-input name="password" type="password"
                                                        placeholder="Enter password" />
                                                    @error('password')
                                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Confirm Password</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                        <div class="mt-1.5 text-xs text-slate-500/80 xl:mt-3">
                                                            Confirm the password you entered above.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <x-base.form-input name="password_confirmation" type="password"
                                                        placeholder="Confirm password" />
                                                    <div class="mt-4 text-slate-500">
                                                        <div class="font-medium">Password requirements:</div>
                                                        <ul class="mt-2.5 list-disc pl-3 text-slate-500">
                                                            <li>Passwords must be at least 8 characters long.</li>
                                                            <li>Include at least one numeric digit (0-9).</li>
                                                        </ul>
                                                    </div>
                                                    @error('password_confirmation')
                                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Status --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="flex items-center">
                                                        <div class="font-medium">Status</div>
                                                        <div
                                                            class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                            Required
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <select name="status"
                                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                        <option value="{{ App\Models\UserDriverDetail::STATUS_PENDING }}"
                                                            {{ old('status') == App\Models\UserDriverDetail::STATUS_PENDING ? 'selected' : '' }}>
                                                            Pending
                                                        </option>
                                                        <option value="{{ App\Models\UserDriverDetail::STATUS_ACTIVE }}"
                                                            {{ old('status') == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'selected' : '' }}>
                                                            Active
                                                        </option>
                                                        <option value="{{ App\Models\UserDriverDetail::STATUS_INACTIVE }}"
                                                            {{ old('status') == App\Models\UserDriverDetail::STATUS_INACTIVE ? 'selected' : '' }}>
                                                            Inactive
                                                        </option>
                                                    </select>
                                                    @error('status')
                                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Terms & Conditions (con Alpine) --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Terms & Conditions</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center mt-4">
                                                    <x-base.form-check.input class="mr-2.5 border" type="checkbox"
                                                        name="terms_accepted" value="1" x-model="termsAccepted" />
                                                    <span class="cursor-pointer select-none">
                                                        I accept the terms and conditions
                                                    </span>
                                                </div>
                                                {{-- Ejemplo: puedes deshabilitar el botón si no se aceptan términos
                                                     en la sección de botones, usando :disabled="!termsAccepted"
                                                --}}
                                            </div>
                                        </div>

                                        {{-- Address Section --}}
                                        <div class="bg-white p-4 rounded-lg shadow mt-6">
                                            <h3 class="text-lg font-semibold mb-4">Address Details</h3>

                                            {{-- Address principal --}}
                                            <div class="mt-3 w-full flex-1 xl:mt-0">
                                                <x-base.form-input name="address_line1" type="text"
                                                    placeholder="Address Line 1" value="{{ old('address_line1') }}" />
                                                <x-base.form-input name="address_line2" type="text"
                                                    placeholder="Address Line 2" value="{{ old('address_line2') }}" />
                                                <div class="grid grid-cols-3 gap-4">
                                                    <x-base.form-input name="city" type="text" placeholder="City"
                                                        value="{{ old('city') }}" />
                                                    <x-base.form-input name="state" type="text" placeholder="State"
                                                        value="{{ old('state') }}" />
                                                    <x-base.form-input name="zip_code" type="text"
                                                        placeholder="ZIP Code" value="{{ old('zip_code') }}" />
                                                </div>
                                                @error('address_line1')
                                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                                @enderror
                                                @error('city')
                                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                                @enderror
                                                @error('state')
                                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                                @enderror
                                                @error('zip_code')
                                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- From/To Dates con Alpine --}}
                                            <div class="mt-4 grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-sm mb-1">From Date</label>
                                                    <x-base.form-input type="date" x-model="fromDate"
                                                        @change="calculateTotal()" />
                                                </div>
                                                <div>
                                                    <label class="text-sm mb-1">To Date</label>
                                                    <x-base.form-input type="date" x-model="toDate"
                                                        @change="calculateTotal()" />
                                                </div>
                                            </div>

                                            <div class="flex items-center mt-4">

                                                <input type="checkbox" name="lived_three_years" :value="livedThreeYears"
                                                    x-model="livedThreeYears">

                                                <span>I have lived at this address for 3+ years</span>
                                            </div>

                                            {{-- Hidden inputs para enviar estos valores al servidor --}}
                                            <input type="hidden" name="from_date" :value="fromDate">
                                            <input type="hidden" name="to_date" :value="toDate">
                                            <input type="hidden" name="lived_three_years"
                                                :value="livedThreeYears ? 1 : 0">

                                            {{-- Address Duration Info --}}
                                            <div class="mt-4">
                                                <template x-if="fromDate">
                                                    <div class="text-sm">
                                                        Current residence:
                                                        <span
                                                            x-text="(calculateDuration(fromDate, toDate)).toFixed(1)"></span>
                                                        years
                                                    </div>
                                                </template>

                                                <div class="text-sm font-semibold"
                                                    :class="{
                                                        'text-green-600': isAddressValid || livedThreeYears,
                                                        'text-amber-600': !isAddressValid && !livedThreeYears
                                                    }">
                                                    <template x-if="livedThreeYears">
                                                        <span>Total Years: 3.0 (Complete)</span>
                                                    </template>
                                                    <template x-if="!livedThreeYears">
                                                        <div>
                                                            <span x-text="'Total years: ' + totalYears.toFixed(1)"></span>
                                                            <template x-if="!isAddressValid">
                                                                <span
                                                                    x-text="' (' + (3 - totalYears).toFixed(1) + ' more years needed)'"></span>
                                                            </template>
                                                            <template x-if="isAddressValid">
                                                                <span> (Successful)</span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Previous Addresses --}}
                                            {{-- <template x-if="!livedThreeYears && !isAddressValid"> --}}
                                            <div class="mt-4">
                                                {{-- <template x-for="(addr, index) in previousAddresses" :key="index"> --}}
                                                <template x-for="(addr, index) in previousAddresses"
                                                    :key="index">
                                                    <div class="border p-4 rounded-lg mb-4">
                                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                                            <div>
                                                                <label class="text-sm mb-1">From Date</label>
                                                                <input type="date" class="form-input w-full"
                                                                    x-model="addr.from_date" @change="calculateTotal()">
                                                            </div>
                                                            <div>
                                                                <label class="text-sm mb-1">To Date</label>
                                                                <input type="date" class="form-input w-full"
                                                                    x-model="addr.to_date" @change="calculateTotal()">
                                                            </div>
                                                        </div>

                                                        <input type="text" class="form-input w-full mt-2"
                                                            placeholder="Address Line 1" x-model="addr.address_line1">
                                                        <input type="text" class="form-input w-full mt-2"
                                                            placeholder="Address Line 2" x-model="addr.address_line2">
                                                        <div class="grid grid-cols-3 gap-4 mt-2">
                                                            <input type="text" class="form-input w-full"
                                                                placeholder="City" x-model="addr.city">
                                                            <input type="text" class="form-input w-full"
                                                                placeholder="State" x-model="addr.state">
                                                            <input type="text" class="form-input w-full"
                                                                placeholder="ZIP Code" x-model="addr.zip_code">
                                                        </div>

                                                        <button type="button" @click="removeAddress(index)"
                                                            class="btn btn-outline-danger mt-3">
                                                            Remove Address
                                                        </button>

                                                        {{-- Hidden inputs --}}
                                                        <input type="hidden"
                                                            :name="'previous_addresses[' + index + '][address_line1]'"
                                                            :value="addr.address_line1">
                                                        <input type="hidden"
                                                            :name="'previous_addresses[' + index + '][address_line2]'"
                                                            :value="addr.address_line2">
                                                        <input type="hidden"
                                                            :name="'previous_addresses[' + index + '][city]'"
                                                            :value="addr.city">
                                                        <input type="hidden"
                                                            :name="'previous_addresses[' + index + '][state]'"
                                                            :value="addr.state">
                                                        <input type="hidden"
                                                            :name="'previous_addresses[' + index + '][zip_code]'"
                                                            :value="addr.zip_code">
                                                        <input type="hidden"
                                                            :name="'previous_addresses[' + index + '][from_date]'"
                                                            :value="addr.from_date">
                                                        <input type="hidden"
                                                            :name="'previous_addresses[' + index + '][to_date]'"
                                                            :value="addr.to_date">
                                                    </div>
                                                    {{-- </template> --}}
                                                </template>

                                                <button type="button" class="btn btn-outline-primary"
                                                    :class="{ 'opacity-50': isAddressValid || livedThreeYears }"
                                                    :disabled="isAddressValid || livedThreeYears"
                                                    x-show="!isAddressValid && !livedThreeYears" @click="addAddress">
                                                    Add Previous Address
                                                </button>
                                            </div>
                                            {{-- </template> --}}
                                        </div>

                                        {{-- Driver Details --}}
                                        <div class="bg-white p-4 rounded-lg shadow mt-6">
                                            <h3 class="text-lg font-semibold mb-4">Driver Details</h3>

                                            {{-- SSN --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center"
                                                x-data="{ mask: null }" x-init="mask = IMask($refs.ssn, { mask: '000-00-0000' })">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Social Security Number</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                        <div class="mt-1.5 text-xs text-slate-500/80 xl:mt-3">
                                                            Enter your SSN for identification
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <x-base.form-input x-ref="ssn" name="social_security_number"
                                                        type="text" placeholder="XXX-XX-XXXX"
                                                        class="form-input w-full"
                                                        value="{{ old('social_security_number') }}" />
                                                    @error('social_security_number')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- License Number --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">License Number</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <x-base.form-input name="license_number" type="text"
                                                        placeholder="Enter license number"
                                                        value="{{ old('license_number') }}" />
                                                    @error('license_number')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- TWIC Card (con Alpine) --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">TWIC Card</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" class="mr-2" name="has_twic_card"
                                                            value="1" x-model="hasTwicCard">
                                                        <span>I have a TWIC card</span>
                                                    </label>

                                                    <template x-if="hasTwicCard">
                                                        <div class="mt-2">
                                                            <x-base.form-input name="twic_expiration_date" type="date"
                                                                placeholder="Expiration Date"
                                                                value="{{ old('twic_expiration_date') }}" />
                                                            @error('twic_expiration_date')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Application Details --}}
                                        <div class="bg-white p-4 rounded-lg shadow mt-6">
                                            <h3 class="text-lg font-semibold mb-4">Application Details</h3>

                                            {{-- Position Applied For (con Alpine) --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Position Applied For</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <select name="applying_position"
                                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                                        x-model="applyingPosition">
                                                        <option value="">Select Position</option>
                                                        @foreach ($driverPositions as $val => $label)
                                                            <option value="{{ $val }}"
                                                                {{ old('applying_position') == $val ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('applying_position')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror

                                                    {{-- Campo “other” con Alpine --}}
                                                    <template x-if="showOtherPosition">
                                                        <div class="mt-2">
                                                            <x-base.form-input name="applying_position_other"
                                                                type="text" placeholder="Specify position"
                                                                value="{{ old('applying_position_other') }}" />
                                                            @error('applying_position_other')
                                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Location --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Location Preference</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <select name="applying_location"
                                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                        <option value="">Select Location</option>
                                                        @foreach ($usStates as $code => $name)
                                                            <option value="{{ $code }}"
                                                                {{ old('applying_location') == $code ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('applying_location')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Eligibility / English con Alpine --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="font-medium">Eligibility Information</div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <div class="space-y-3">
                                                        {{-- Eligible to work --}}
                                                        <div class="flex flex-col">
                                                            <label class="mb-2">Eligible to work in the United
                                                                States</label>
                                                            <select name="eligible_to_work" x-model="eligibleToWork"
                                                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                                <option value="">Select</option>
                                                                <option value="1"
                                                                    {{ old('eligible_to_work') == '1' ? 'selected' : '' }}>
                                                                    Yes
                                                                </option>
                                                                <option value="0"
                                                                    {{ old('eligible_to_work') == '0' ? 'selected' : '' }}>
                                                                    No
                                                                </option>
                                                            </select>
                                                            <template x-if="eligibleToWork === '0'">
                                                                <p class="text-red-600 text-sm mt-1">
                                                                    According to U.S. law, you must be eligible to work
                                                                    in the United States.
                                                                </p>
                                                            </template>
                                                            @error('eligible_to_work')
                                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                            @enderror
                                                        </div>

                                                        {{-- Can speak english --}}
                                                        <div class="flex flex-col">
                                                            <label class="mb-2">Can speak and understand English</label>
                                                            <select name="can_speak_english"
                                                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                                <option value="">Select</option>
                                                                <option value="1"
                                                                    {{ old('can_speak_english') == '1' ? 'selected' : '' }}>
                                                                    Yes
                                                                </option>
                                                                <option value="0"
                                                                    {{ old('can_speak_english') == '0' ? 'selected' : '' }}>
                                                                    No
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Expected Pay --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Expected Pay Rate</div>
                                                            <div
                                                                class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                                Required
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <div class="flex items-center">
                                                        <span class="mr-2">$</span>
                                                        <x-base.form-input name="expected_pay" type="number"
                                                            step="0.01" min="0" placeholder="0.00"
                                                            value="{{ old('expected_pay') }}" />
                                                        <span class="ml-2">per hour</span>
                                                    </div>
                                                    @error('expected_pay')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Referral Source --}}
                                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                    <div class="text-left">
                                                        <div class="flex items-center">
                                                            <div class="font-medium">Referral Source</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <select name="how_did_hear"
                                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                                        <option value="">Select Source</option>
                                                        @foreach ($referralSources as $val => $label)
                                                            <option value="{{ $val }}"
                                                                {{ old('how_did_hear') == $val ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @if (old('how_did_hear') === 'other')
                                                        <div class="mt-2">
                                                            <x-base.form-input name="how_did_hear_other" type="text"
                                                                placeholder="Specify source"
                                                                value="{{ old('how_did_hear_other') }}" />
                                                        </div>
                                                    @endif

                                                    @if (old('how_did_hear') === 'employee_referral')
                                                        <div class="mt-2">
                                                            <x-base.form-input name="referral_employee_name"
                                                                type="text" placeholder="Employee name"
                                                                value="{{ old('referral_employee_name') }}" />
                                                        </div>
                                                    @endif

                                                    @error('how_did_hear')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </template>

                                {{-- TAB: LICENSES --}}
                                <template x-if="activeTab === 'licenses'">
                                    <div class="bg-white p-4 rounded-lg shadow">
                                        <h3 class="text-lg font-semibold mb-4">Licenses Information</h3>
                                        <p>License management functionality coming soon.</p>
                                    </div>
                                </template>

                                {{-- Botones Submit/Cancel --}}
                                <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end mt-6">
                                    <button type="submit"
                                        class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition"
                                        :disabled="!termsAccepted">
                                        Save Driver
                                    </button>
                                    <a href="{{ route('admin.carrier.user_drivers.index', $carrier) }}"
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
    <!-- Incluir IMask para las máscaras -->
    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para el teléfono
            const phoneMask = IMask(document.querySelector('input[name="phone"]'), {
                mask: '(000) 000-0000'
            });

            // Máscara para la licencia (si la necesitas; ajustar formato)
            const licInput = document.querySelector('input[name="license_number"]');
            if (licInput) {
                IMask(licInput, {
                    // Ajusta la máscara según tu formato real
                    mask: 'AA-000000'
                });
            }
        });
    </script>
@endpush

@pushOnce('scripts')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
@endPushOnce
