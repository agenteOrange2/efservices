@extends('../themes/' . $activeTheme)
@section('title', 'Add User Driver')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers', 'url' => route('admin.carrier.user_drivers.index', $carrier->slug)],
        ['label' => 'Create Driver', 'active' => true],
    ];

    // Definir los pasos y sus correspondientes pestañas
    $steps = [
        \App\Services\Admin\DriverStepService::STEP_GENERAL => ['label' => 'general', 'title' => 'General Information'],
        \App\Services\Admin\DriverStepService::STEP_LICENSES => [
            'label' => 'licenses',
            'title' => 'Licenses & Experience',
        ],
        \App\Services\Admin\DriverStepService::STEP_MEDICAL => ['label' => 'medical', 'title' => 'Medical Information'],
        \App\Services\Admin\DriverStepService::STEP_TRAINING => ['label' => 'training', 'title' => 'Training History'],
        \App\Services\Admin\DriverStepService::STEP_TRAFFIC => ['label' => 'traffic', 'title' => 'Traffic Record'],
        \App\Services\Admin\DriverStepService::STEP_ACCIDENT => ['label' => 'accident', 'title' => 'Accident History'],
    ];

    // Si estamos editando, obtener el estado actual de los pasos
    $stepsStatus =
        isset($userDriverDetail) && $userDriverDetail->id
            ? app(\App\Services\Admin\DriverStepService::class)->getStepsStatus($userDriverDetail)
            : array_fill(1, 6, \App\Services\Admin\DriverStepService::STATUS_MISSING);

    // Obtener el paso actual
    $currentStep =
        isset($userDriverDetail) && $userDriverDetail->id
            ? $userDriverDetail->current_step
            : \App\Services\Admin\DriverStepService::STEP_GENERAL;

    // Calcular el porcentaje de completitud
    $completionPercentage =
        isset($userDriverDetail) && $userDriverDetail->id
            ? app(\App\Services\Admin\DriverStepService::class)->calculateCompletionPercentage($userDriverDetail)
            : 0;
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <div class="box-body">
                        <form action="{{ route('admin.carrier.user_drivers.store', $carrier) }}" method="POST"
                            enctype="multipart/form-data" id="driverForm">
                            @csrf
                            <input type="hidden" name="submission_type" value="partial">

                            <x-validation-errors class="my-4" />


                            {{-- Contenedor Alpine con toda la lógica --}}
                            <div x-data="driverForm({
                                initialTab: '{{ old('active_tab', $steps[$currentStep]['label']) }}',
                                hasWorkHistory: {{ old('has_work_history') ? 'true' : 'false' }},
                                fromDate: '{{ old('from_date') }}',
                                toDate: '{{ old('to_date') }}',
                                livedThreeYears: {{ old('lived_three_years') ? 'true' : 'false' }},
                                previousAddresses: {{ json_encode(old('previous_addresses', [])) }},
                                userDriverId: {{ isset($userDriverDetail) ? $userDriverDetail->id : 'null' }},
                                termsAccepted: {{ old('terms_accepted') ? 'true' : 'false' }},
                                hasTwicCard: {{ old('has_twic_card') ? 'true' : 'false' }},
                                applyingPosition: '{{ old('applying_position', '') }}',
                                referralSource: '{{ old('how_did_hear', '') }}',
                                eligibleToWork: '{{ old('eligible_to_work', '') }}'
                            })">
                                {{-- Progress Bar and Steps Indicator --}}
                                <div class="mb-8 p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h2 class="text-lg font-semibold">Driver Registration Progress</h2>
                                        <span class="text-sm text-gray-600">{{ $completionPercentage }}% Complete</span>
                                    </div>

                                    {{-- Progress Bar --}}
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                                        <div class="bg-primary h-2.5 rounded-full"
                                            style="width: {{ $completionPercentage }}%"></div>
                                    </div>

                                    {{-- Steps Indicators --}}
                                    <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                                        @foreach ($steps as $step => $details)
                                            <x-driver.step-indicator :status="$stepsStatus[$step]" :step="$step" :activeStep="$currentStep"
                                                :label="$details['label']" />
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Hidden Inputs for Form Submission --}}
                                <input type="hidden" name="active_tab" :value="activeTab">
                                <input type="hidden" name="submission_type" :value="submissionType">
                                <input type="hidden" name="user_driver_id" :value="userDriverId">
                                @if (isset($userDriverDetail) && $userDriverDetail->id)
                                    <input type="hidden" name="user_id" value="{{ $userDriverDetail->user_id }}">
                                @endif

                                {{-- Tabs Navigation --}}
                                <div class="tabs">
                                    <div class="mb-4 border-b border-gray-200">
                                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                                            @foreach ($steps as $step => $details)
                                                <li class="mr-2">
                                                    <button type="button" @click="activeTab = '{{ $details['label'] }}'"
                                                        class="inline-block p-4"
                                                        :class="{
                                                            'text-primary border-b-2 border-primary': activeTab === '{{ $details['label'] }}',
                                                            'text-gray-500 hover:border-gray-300': activeTab !== '{{ $details['label'] }}'
                                                        }">
                                                        {{ $details['title'] }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                {{-- Tab Contents (los incluyes como ya los tenías) --}}
                                <div x-show="activeTab === 'general'">
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
                                                <div class="mt-3 w-full flex-1 xl:mt-0" x-data="imagePreview()">
                                                    <div class="flex items-center">
                                                        <div class="relative group">
                                                            <input type="file" name="photo" accept="image/*"
                                                                id="photo" class="hidden" @change="handleFileChange">
                                                            <label for="photo" class="cursor-pointer block">
                                                                <img :src="previewUrl ||
                                                                    '{{ $userDriverDetail->profile_photo_url ?? asset('build/default_profile.png') }}'"
                                                                    class="w-24 h-24 rounded-full object-cover"
                                                                    alt="Profile Photo">

                                                                <div
                                                                    class="absolute inset-0 rounded-full bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <span class="text-white text-sm">Change</span>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        {{-- Botón de eliminar --}}
                                                        <button type="button" @click="removeImage" x-show="hasImage"
                                                            class="ml-4 text-red-500 hover:text-red-700">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    @error('photo')
                                                        <span
                                                            class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
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
                                            <button type="button" @click="toggleSection('address')"
                                                class="w-full p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                                <h3 class="text-lg font-semibold">Address Details</h3>
                                                <svg :class="{ 'transform rotate-180': openSections.address }"
                                                    class="w-5 h-5 transition-transform duration-200"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <div x-show="openSections.address"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                                class="p-4 border-t border-gray-100">

                                                {{-- Address principal --}}
                                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                                    <x-base.form-input class="my-3" name="address_line1" type="text"
                                                        placeholder="Address Line 1"
                                                        value="{{ old('address_line1') }}" />
                                                    <x-base.form-input class="my-3" name="address_line2" type="text"
                                                        placeholder="Address Line 2"
                                                        value="{{ old('address_line2') }}" />
                                                    <div class="grid grid-cols-3 gap-4 my-3">
                                                        <x-base.form-input name="city" type="text"
                                                            placeholder="City" value="{{ old('city') }}" />
                                                        <x-base.form-input name="state" type="text"
                                                            placeholder="State" value="{{ old('state') }}" />
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
                                                            @change="validateAndCalculateDates()" data-tw-merge />
                                                    </div>
                                                    <div>
                                                        <label class="text-sm mb-1">To Date</label>
                                                        <x-base.form-input type="date" x-model="toDate"
                                                            x-bind:min="fromDate"
                                                            @change="validateAndCalculateDates()"
                                                            x-bind:class="{ 'border-red-500': dateError }" data-tw-merge />
                                                        <p x-show="dateError" class="text-red-500 text-sm mt-1"
                                                            x-text="dateError"></p>
                                                    </div>
                                                </div>

                                                <div class="flex items-center mt-4">

                                                    <input type="hidden" name="lived_three_years"
                                                        :value="livedThreeYears ? 1 : 0">
                                                    <input type="checkbox"
                                                        class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500"
                                                        x-model="livedThreeYears" :disabled="totalYears >= 3">
                                                    <label class="ms-2 text-sm font-medium text-gray-900">
                                                        <span
                                                            x-text="livedThreeYears ? 'Has lived at this address for 3+ years' : 'Has not lived at this address for 3+ years'"></span>
                                                    </label>
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
                                                                <span
                                                                    x-text="'Total years: ' + totalYears.toFixed(1)"></span>
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
                                                <div class="mt-4">
                                                    <template x-for="(addr, index) in previousAddresses"
                                                        :key="index">
                                                        <div class="border p-4 rounded-lg mb-4">
                                                            <div class="grid grid-cols-2 gap-4 mb-4">
                                                                <div>
                                                                    <label class="text-sm mb-1">From Date</label>
                                                                    <input type="date"
                                                                        class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                                                        x-model="addr.from_date"
                                                                        @change="calculateTotal()">

                                                                </div>
                                                                <div>
                                                                    <label class="text-sm mb-1">To Date</label>
                                                                    <input type="date"
                                                                        class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                                                        x-model="addr.to_date" @change="calculateTotal()">
                                                                </div>
                                                            </div>

                                                            <input type="text"
                                                                class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none mt-2"
                                                                placeholder="Address Line 1" x-model="addr.address_line1">
                                                            <input type="text"
                                                                class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none mt-2"
                                                                placeholder="Address Line 2" x-model="addr.address_line2">

                                                            <div class="grid grid-cols-3 gap-4 mt-2">
                                                                <input type="text"
                                                                    class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                                                    placeholder="City" x-model="addr.city">
                                                                <input type="text"
                                                                    class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                                                    placeholder="State" x-model="addr.state">
                                                                <input type="text"
                                                                    class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                                                    placeholder="ZIP Code" x-model="addr.zip_code">
                                                            </div>
                                                        </div>
                                                        <!-- Botón remove -->
                                                        <button type="button" @click="removeAddress(index)"
                                                            class="btn btn-outline-danger mt-3">
                                                            Remove Address
                                                        </button>
                                                    </template>
                                                    <template x-for="(addr, index) in previousAddresses"
                                                        :key="index">
                                                        <!-- Inputs hidden dentro del mismo div padre -->
                                                        <div class="hidden">
                                                            <input type="hidden"
                                                                :name="`previous_addresses[${index}][address_line1]`"
                                                                :value="addr.address_line1">
                                                            <input type="hidden"
                                                                :name="`previous_addresses[${index}][address_line2]`"
                                                                :value="addr.address_line2">
                                                            <input type="hidden"
                                                                :name="`previous_addresses[${index}][city]`"
                                                                :value="addr.city">
                                                            <input type="hidden"
                                                                :name="`previous_addresses[${index}][state]`"
                                                                :value="addr.state">
                                                            <input type="hidden"
                                                                :name="`previous_addresses[${index}][zip_code]`"
                                                                :value="addr.zip_code">
                                                            <input type="hidden"
                                                                :name="`previous_addresses[${index}][from_date]`"
                                                                :value="addr.from_date">
                                                            <input type="hidden"
                                                                :name="`previous_addresses[${index}][to_date]`"
                                                                :value="addr.to_date">
                                                        </div>
                                                    </template>

                                                    <button type="button"
                                                        class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition"
                                                        :class="{ 'opacity-50 cursor-not-allowed': totalYears >= 3 }"
                                                        :disabled="totalYears >= 3" @click="addAddress"
                                                        x-show="!livedThreeYears">
                                                        Add Previous Address
                                                    </button>

                                                </div>
                                            </div>
                                            {{-- </template> --}}
                                        </div>



                                        {{-- Application Details --}}
                                        <div class="bg-white p-4 rounded-lg shadow mt-6">
                                            <button type="button" @click="toggleSection('application')"
                                                class="w-full p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                                <h3 class="text-lg font-semibold mb-4">Application Details</h3>
                                                <svg :class="{ 'transform rotate-180': openSections.application }"
                                                    class="w-5 h-5 transition-transform duration-200"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <div x-show="openSections.application"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                                class="p-4 border-t border-gray-100">
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
                                                                    <p class="text-red-500 text-sm mt-1">{{ $message }}
                                                                    </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Can speak english --}}
                                                            <div class="flex flex-col">
                                                                <label class="mb-2">Can speak and understand
                                                                    English</label>
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
                                                        <div class="flex items-center mt-4">
                                                            <x-base.form-check.input class="mr-2.5 border" type="checkbox"
                                                                name="has_twic_card" value="1"
                                                                x-model="hasTwicCard" />
                                                            <span class="cursor-pointer select-none">
                                                                I have a TWIC card
                                                            </span>
                                                        </div>

                                                        <template x-if="hasTwicCard">
                                                            <div class="mt-2">
                                                                <x-base.form-input name="twic_expiration_date"
                                                                    type="date" placeholder="Expiration Date"
                                                                    value="{{ old('twic_expiration_date') }}" />
                                                                @error('twic_expiration_date')
                                                                    <span
                                                                        class="text-red-500 text-sm">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </template>
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
                                                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                                            x-model="referralSource">
                                                            <option value="">Select Source</option>
                                                            @foreach ($referralSources as $val => $label)
                                                                <option value="{{ $val }}"
                                                                    {{ old('how_did_hear') == $val ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <template x-if="showEmployeeReferral">
                                                            <div class="mt-2">
                                                                <x-base.form-input name="referral_employee_name"
                                                                    type="text" placeholder="Enter employee name"
                                                                    value="{{ old('referral_employee_name') }}" />
                                                            </div>
                                                        </template>

                                                        <template x-if="showOtherReferral">
                                                            <div class="mt-2">
                                                                <x-base.form-input name="how_did_hear_other"
                                                                    type="text" placeholder="Specify source"
                                                                    value="{{ old('how_did_hear_other') }}" />
                                                            </div>
                                                        </template>

                                                        @error('how_did_hear')
                                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- Work History Section --}}
                                                <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                                    <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                                                        <div class="text-left">
                                                            <div class="flex items-center">
                                                                <div class="font-medium">Work History</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                                        <div class="flex items-center">
                                                            <x-base.form-check.input class="mr-2.5 border" type="checkbox"
                                                                name="has_work_history" value="1"
                                                                x-model="hasWorkHistory" />
                                                            <span class="cursor-pointer select-none">
                                                                I have previous work experience
                                                            </span>
                                                        </div>

                                                        <template x-if="hasWorkHistory">
                                                            <div class="mt-4 border p-4 rounded-md">
                                                                <div class="flex justify-between items-center mb-4">
                                                                    <h4 class="text-lg font-medium">Previous Employment
                                                                    </h4>
                                                                    <button type="button" @click="addWorkHistory()"
                                                                        class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
                                                                        Add Employment
                                                                    </button>
                                                                </div>

                                                                <div x-show="workHistories.length === 0"
                                                                    class="text-gray-500 text-sm italic mb-4">
                                                                    No work history added. Click "Add Employment" to include
                                                                    previous work experience.
                                                                </div>

                                                                <template x-for="(history, index) in workHistories"
                                                                    :key="index">
                                                                    <div class="mb-6 border-b pb-6">
                                                                        <div
                                                                            class="flex justify-between items-center mb-4">
                                                                            <h5 class="font-medium"
                                                                                x-text="`Employment #${index + 1}`"></h5>
                                                                            <button type="button"
                                                                                @click="removeWorkHistory(index)"
                                                                                class="text-red-500 text-sm">
                                                                                <i class="fas fa-trash mr-1"></i> Remove
                                                                            </button>
                                                                        </div>

                                                                        <div
                                                                            class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                                            <div>
                                                                                <label
                                                                                    class="block text-sm font-medium mb-1">Company
                                                                                    Name</label>
                                                                                <input type="text"
                                                                                    x-model="history.previous_company"
                                                                                    :name="`work_histories[${index}][previous_company]`"
                                                                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                                                                    placeholder="Previous employer name"
                                                                                    required>
                                                                            </div>
                                                                            <div>
                                                                                <label
                                                                                    class="block text-sm font-medium mb-1">Position</label>
                                                                                <input type="text"
                                                                                    x-model="history.position"
                                                                                    :name="`work_histories[${index}][position]`"
                                                                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                                                                    placeholder="Your job title" required>
                                                                            </div>
                                                                        </div>

                                                                        <div
                                                                            class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                                            <div>
                                                                                <label
                                                                                    class="block text-sm font-medium mb-1">Start
                                                                                    Date</label>
                                                                                <input type="date"
                                                                                    x-model="history.start_date"
                                                                                    :name="`work_histories[${index}][start_date]`"
                                                                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                                                                    required>
                                                                            </div>
                                                                            <div>
                                                                                <label
                                                                                    class="block text-sm font-medium mb-1">End
                                                                                    Date</label>
                                                                                <input type="date"
                                                                                    x-model="history.end_date"
                                                                                    :name="`work_histories[${index}][end_date]`"
                                                                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                                                                    required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-4">
                                                                            <label
                                                                                class="block text-sm font-medium mb-1">Location</label>
                                                                            <input type="text"
                                                                                x-model="history.location"
                                                                                :name="`work_histories[${index}][location]`"
                                                                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                                                                placeholder="City, State" required>
                                                                        </div>

                                                                        <div class="mb-4">
                                                                            <label
                                                                                class="block text-sm font-medium mb-1">Reason
                                                                                for Leaving</label>
                                                                            <textarea x-model="history.reason_for_leaving" :name="`work_histories[${index}][reason_for_leaving]`"
                                                                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" rows="2"
                                                                                placeholder="Why did you leave this position?" required></textarea>
                                                                        </div>

                                                                        <div>
                                                                            <label
                                                                                class="block text-sm font-medium mb-1">Reference
                                                                                Contact (Optional)</label>
                                                                            <input type="text"
                                                                                x-model="history.reference_contact"
                                                                                :name="`work_histories[${index}][reference_contact]`"
                                                                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                                                                placeholder="Name and phone number">
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="activeTab === 'licenses'" x-data="{
                                    licenses: {{ json_encode(
                                        old('licenses', [
                                            [
                                                'license_number' => '',
                                                'state_of_issue' => '',
                                                'license_class' => '',
                                                'expiration_date' => '',
                                                'is_cdl' => false,
                                                'endorsements' => [],
                                                'temp_front_token' => '',
                                                'front_preview' => '',
                                                'front_filename' => '',
                                                'temp_back_token' => '',
                                                'back_preview' => '',
                                                'back_filename' => '',
                                            ],
                                        ]),
                                    ) }},
                                    experiences: {{ json_encode(
                                        old('experiences', [
                                            [
                                                'equipment_type' => '',
                                                'years_experience' => '',
                                                'miles_driven' => '',
                                                'requires_cdl' => false,
                                            ],
                                        ]),
                                    ) }},
                                
                                    addLicense() {
                                        this.licenses.push({
                                            license_number: '',
                                            state_of_issue: '',
                                            license_class: '',
                                            expiration_date: '',
                                            is_cdl: false,
                                            endorsements: []
                                        });
                                    },
                                
                                    removeLicense(index) {
                                        if (this.licenses.length > 1) {
                                            this.licenses.splice(index, 1);
                                        }
                                    },
                                
                                    toggleEndorsements(index) {
                                        // La propiedad is_cdl ya se actualiza automáticamente con x-model
                                    },
                                
                                    addExperience() {
                                        this.experiences.push({
                                            equipment_type: '',
                                            years_experience: '',
                                            miles_driven: '',
                                            requires_cdl: false
                                        });
                                    },
                                
                                    removeExperience(index) {
                                        if (this.experiences.length > 1) {
                                            this.experiences.splice(index, 1);
                                        }
                                    },
                                
                                    checkEndorsement(licenseIndex, endorsement) {
                                        if (!this.licenses[licenseIndex].endorsements) {
                                            this.licenses[licenseIndex].endorsements = [];
                                        }
                                
                                        return this.licenses[licenseIndex].endorsements.includes(endorsement);
                                    },
                                
                                    toggleEndorsement(licenseIndex, endorsement) {
                                        console.log(`Toggle endorsement ${endorsement} for license ${licenseIndex}`);
                                
                                        // Asegúrate de que la licencia tiene un array de endorsements
                                        if (!this.licenses[licenseIndex].endorsements) {
                                            this.licenses[licenseIndex].endorsements = [];
                                        }
                                
                                        // Encuentra si el endorsement ya está en el array
                                        const index = this.licenses[licenseIndex].endorsements.indexOf(endorsement);
                                
                                        // Cambia el estado
                                        if (index === -1) {
                                            // Si no está, añádelo
                                            this.licenses[licenseIndex].endorsements.push(endorsement);
                                        } else {
                                            // Si ya está, quítalo
                                            this.licenses[licenseIndex].endorsements.splice(index, 1);
                                        }
                                
                                        console.log(`Endorsements for license ${licenseIndex}:`, this.licenses[licenseIndex].endorsements);
                                    }
                                }">
                                    @include('admin.user_driver.tabs.licenses_create')
                                </div>

                                <div x-show="activeTab === 'medical'" x-data="{
                                    isSuspended: {{ old('is_suspended') ? 'true' : 'false' }},
                                    isTerminated: {{ old('is_terminated') ? 'true' : 'false' }},
                                    selectedFile: null,
                                
                                    setFilePreview(event) {
                                        const file = event.target.files[0];
                                        if (file) {
                                            this.selectedFile = file.name;
                                        }
                                    },
                                
                                    clearFile() {
                                        this.selectedFile = null;
                                        document.getElementById('medical_card_file').value = '';
                                    }
                                }">
                                    @include('admin.user_driver.tabs.medical_create')
                                </div>

                                <div x-show="activeTab === 'training'">
                                    @include('admin.user_driver.tabs.training_create')
                                </div>

                                <div x-show="activeTab === 'traffic'">
                                    @include('admin.user_driver.tabs.traffic_create')
                                </div>

                                <div x-show="activeTab === 'accident'">
                                    @include('admin.user_driver.tabs.accident_create')
                                </div>

                                {{-- Botones Submit/Cancel con opciones de guardado --}}
                                <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-between mt-6">
                                    <div>
                                        <button type="button" @click="activeTab = getPreviousTab()"
                                            x-show="!isFirstTab()"
                                            class="border border-gray-300 px-4 py-2 rounded text-gray-600 hover:bg-gray-100">
                                            <i class="fas fa-arrow-left mr-1"></i> Previous
                                        </button>
                                    </div>

                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.carrier.user_drivers.index', $carrier) }}"
                                            class="border border-gray-300 px-4 py-2 rounded text-gray-600 hover:bg-gray-100">
                                            Cancel
                                        </a>

                                        <button type="button" @click="moveToNextTab()"
                                            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition"
                                            x-show="!isLastTab()">
                                            Continue <i class="fas fa-arrow-right ml-1"></i>
                                        </button>

                                        <button type="submit"
                                            class="border border-success px-4 py-2 rounded text-success hover:text-white hover:bg-success transition">
                                            Save & Finish
                                        </button>

                                        {{-- <button type="button" @click="saveAndFinish($event)"
                                            class="border border-success px-4 py-2 rounded text-success hover:text-white hover:bg-success transition"
                                            :disabled="isSubmitting">
                                            <span x-show="!isSubmitting">Save & Finish</span>
                                            <span x-show="isSubmitting" class="flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-primary"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2
                                                           5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Guardando...
                                            </span>
                                        </button> --}}
                                    </div>
                                </div>
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
    <!-- Tus scripts actuales -->
    <script defer src="https://unpkg.com/@alpinejs/validate@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script>
        function imagePreview() {
            return {
                previewUrl: null,
                hasImage: false,
                originalSrc: '{{ $userDriverDetail->profile_photo_url ?? asset('build/default_profile.png') }}',

                handleFileChange(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validar tipo de archivo
                    if (!file.type.startsWith('image/')) {
                        alert('Please select an image file');
                        e.target.value = '';
                        return;
                    }

                    // Crear URL de previsualización
                    this.previewUrl = URL.createObjectURL(file);
                    this.hasImage = true;
                },

                removeImage() {
                    // Limpiar input file
                    const input = document.getElementById('photo');
                    input.value = '';

                    // Restaurar imagen original o default
                    this.previewUrl = this.originalSrc;
                    this.hasImage = false;

                    // Si es edición y hay una foto existente, puedes hacer una llamada AJAX para eliminarla
                    @if (isset($userDriverDetail) && $userDriverDetail->id)
                        if (confirm('Are you sure you want to remove the profile photo?')) {
                            fetch(`{{ route('admin.driver.delete-photo', ['driver' => $userDriverDetail->id]) }}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        this.originalSrc = '{{ asset('build/default_profile.png') }}';
                                        this.previewUrl = this.originalSrc;
                                    }
                                });
                        }
                    @endif
                }
            }
        }

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
    @vite('resources/js/admin/driverRegistration.js')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
@endPushOnce
