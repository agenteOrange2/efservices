<?php
/**
 * Vista para el Wizard de Verificación de Empleo
 * Esta vista maneja el proceso paso a paso para enviar solicitudes de verificación
 */
?>

<div>
    <!-- Indicador de Pasos del Wizard con diseño mejorado -->
    <div class="intro-y">
        <h2 class="text-lg font-medium mr-auto mb-3">New Employment Verification Request</h2>

        <!-- Barra de progreso superior -->
        <div class="bg-slate-100 rounded-md h-1.5 mb-2">
            <div class="bg-primary h-1.5 rounded-md" style="width: {{ ($currentStep / 3) * 100 }}%"></div>
        </div>

        <!-- Steps visuales -->
        <div class="flex justify-between pt-2 pb-5">
            <!-- Paso 1: Seleccionar Conductor -->
            <div class="text-center" wire:key="step-1">
                <div
                    class="w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-2
                            {{ $currentStep == 1
                                ? 'bg-primary text-white shadow-md'
                                : ($currentStep > 1
                                    ? 'bg-success text-white'
                                    : 'bg-slate-100 text-slate-500') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-user-icon lucide-user">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="font-medium text-sm {{ $currentStep >= 1 ? 'text-primary' : 'text-slate-500' }}">Driver
                </div>
                <div class="text-slate-400 text-xs mt-0.5">Step 1</div>
            </div>

            <!-- Línea conectora 1-2 -->
            <div class="flex-1 my-auto mx-3">
                <div class="h-px bg-slate-200 dark:bg-darkmode-400 relative">
                    <div class="absolute left-0 right-0 top-0 bottom-0 h-px transition-all"
                        style="background: linear-gradient(to right, #3b82f6 50%, transparent 50%); 
                                background-size: 200% 100%;
                                background-position: {{ $currentStep > 1 ? 'left' : 'right' }} bottom;">
                    </div>
                </div>
            </div>

            <!-- Paso 2: Seleccionar Empresa -->
            <div class="text-center" wire:key="step-2">
                <div
                    class="w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-2
                            {{ $currentStep == 2
                                ? 'bg-primary text-white shadow-md'
                                : ($currentStep > 2
                                    ? 'bg-success text-white'
                                    : 'bg-slate-100 text-slate-500') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-building-icon lucide-building">
                        <rect width="16" height="20" x="4" y="2" rx="2" ry="2" />
                        <path d="M9 22v-4h6v4" />
                        <path d="M8 6h.01" />
                        <path d="M16 6h.01" />
                        <path d="M12 6h.01" />
                        <path d="M12 10h.01" />
                        <path d="M12 14h.01" />
                        <path d="M16 10h.01" />
                        <path d="M16 14h.01" />
                        <path d="M8 10h.01" />
                        <path d="M8 14h.01" />
                    </svg>
                </div>
                <div class="font-medium text-sm {{ $currentStep >= 2 ? 'text-primary' : 'text-slate-500' }}">Company
                </div>
                <div class="text-slate-400 text-xs mt-0.5">Step 2</div>
            </div>

            <!-- Línea conectora 2-3 -->
            <div class="flex-1 my-auto mx-3">
                <div class="h-px bg-slate-200 dark:bg-darkmode-400 relative">
                    <div class="absolute left-0 right-0 top-0 bottom-0 h-px transition-all"
                        style="background: linear-gradient(to right, #3b82f6 50%, transparent 50%); 
                                background-size: 200% 100%;
                                background-position: {{ $currentStep > 2 ? 'left' : 'right' }} bottom;">
                    </div>
                </div>
            </div>

            <!-- Paso 3: Detalles de Empleo -->
            <div class="text-center" wire:key="step-3">
                <div
                    class="w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-2
                            {{ $currentStep == 3
                                ? 'bg-primary text-white shadow-md'
                                : ($currentStep > 3
                                    ? 'bg-success text-white'
                                    : 'bg-slate-100 text-slate-500') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text">
                        <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                        <path d="M14 8H8" />
                        <path d="M16 12H8" />
                        <path d="M13 16H8" />
                    </svg>
                </div>
                <div class="font-medium text-sm {{ $currentStep >= 3 ? 'text-primary' : 'text-slate-500' }}">Details
                </div>
                <div class="text-slate-400 text-xs mt-0.5">Step 3</div>
            </div>
        </div>
    </div>

    <!-- Contenido del Paso Actual -->
    <div class="box p-5">
        <!-- Paso 1: Selección de Conductor -->
        @if ($currentStep == 1)
            <div class="intro-y">
                <h2 class="text-lg font-medium mb-5">Select Driver</h2>

                <!-- Selección de Carrier con select nativo -->
                <div class="mb-4">
                    <label for="carrier" class="form-label">Carrier <span class="text-danger">*</span></label>
                    <select wire:model.live="selectedCarrierId" id="carrier"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                        <option value="">Select a carrier</option>
                        @foreach ($carriers as $carrier)
                            <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedCarrierId')
                        <span class="text-danger mt-2">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Campo de búsqueda y tabla de conductores con Alpine.js -->
                @if ($selectedCarrierId)
                    <div class="mb-4" x-data="{ updateIcons() { setTimeout(() => { if (typeof feather !== 'undefined') feather.replace(); }, 50); } }" x-init="updateIcons()"
                        @drivers-loaded.window="updateIcons()">                        
                        <div class="relative">
                            <input type="text" wire:model.live="searchTerm" id="searchTerm"
                                class="text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" placeholder="Nombre, email, teléfono...">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i data-feather="search" class="w-5 h-5 text-slate-500"></i>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y overflow-auto mt-5" x-data="{}" x-init="setTimeout(() => { if (typeof feather !== 'undefined') feather.replace(); }, 100)">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3">Driver</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Phone</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($drivers as $driver)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                        <td class="px-6 py-4">
                                            <div class="flex">
                                                <div class="font-medium whitespace-nowrap">
                                                    {{ $driver->user->name }} {{ $driver->last_name ?? '' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">{{ $driver->user->email }}</td>
                                        <td class="px-6 py-4">{{ $driver->phone ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-start">
                                                <button type="button" wire:click="selectDriver({{ $driver->id }})"
                                                    class="btn btn-sm {{ $selectedDriverId == $driver->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                                                    {{ $selectedDriverId == $driver->id ? 'Selected' : 'Select' }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="flex flex-col items-center justify-center py-4">
                                                <i data-feather="users" class="w-16 h-16 text-slate-300"></i>
                                                <p class="text-slate-500 mt-2">No drivers found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($selectedDriverId && $selectedDriver)
                        <div class="bg-slate-50 rounded-md p-4 mt-5 border-l-4 border-primary">
                            <div class="flex items-center">
                                <div class="mr-auto">
                                    <div class="font-medium text-base">{{ $selectedDriver->user->name }}
                                        {{ $selectedDriver->last_name ?? '' }}</div>
                                    <div class="text-slate-500">{{ $selectedDriver->user->email }}</div>
                                </div>
                                <div>
                                    <span class="px-2 py-1 rounded-full bg-success text-white text-xs">
                                        Selected
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @error('selectedDriverId')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                @endif
            </div>

            <!-- Paso 2: Selección de Empresa -->
        @elseif ($currentStep == 2)
            <div class="intro-y">
                <h2 class="text-lg font-medium mb-5">Select or Create Company</h2>

                <!-- Información del conductor seleccionado -->
                @if ($selectedDriver)
                    <div class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary">
                        <p class="text-slate-600">Driver: <strong>{{ $selectedDriver->user->name }}
                            {{ $selectedDriver->last_name ?? '' }}</strong></p>
                    </div>
                @endif

                <!-- Selector de modo: Existente o Nueva -->
                <div class="intro-y mb-5">
                    <div class="flex flex-col md:flex-row border-b border-slate-200 pb-4 mb-4">
                        <div class="flex-1 flex flex-col md:flex-row items-start md:items-center">
                            <div class="flex items-center">
                                <div class="text-primary font-medium mr-5">Select an option:</div>
                            </div>
                            <div
                                class="mt-3 md:mt-0 md:ml-4 space-y-2 md:space-y-0 md:space-x-2 flex flex-col md:flex-row">
                                <button type="button" wire:click="$set('useExistingCompany', true)"
                                    class="btn flex items-center {{ $useExistingCompany ? 'btn-primary' : 'btn-outline-secondary' }}" >                                    
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hotel-icon lucide-hotel"><path d="M10 22v-6.57"/><path d="M12 11h.01"/><path d="M12 7h.01"/><path d="M14 15.43V22"/><path d="M15 16a5 5 0 0 0-6 0"/><path d="M16 11h.01"/><path d="M16 7h.01"/><path d="M8 11h.01"/><path d="M8 7h.01"/><rect x="4" y="2" width="16" height="20" rx="2"/></svg>
                                    <span class="ml-2">Use Existing Company</span>
                                </button>
                                <button type="button" wire:click="$set('useExistingCompany', false)"
                                    class="btn flex items-center {{ !$useExistingCompany ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-plus-icon lucide-house-plus"><path d="M12.662 21H5a2 2 0 0 1-2-2v-9a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v2.475"/><path d="M14.959 12.717A1 1 0 0 0 14 12h-4a1 1 0 0 0-1 1v8"/><path d="M15 18h6"/><path d="M18 15v6"/></svg>
                                    <span class="ml-2">Create New Company</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Directorio de Empresas (si seleccionó usar existente) -->
                @if ($useExistingCompany)
                    <div class="intro-y">
                        <div class="flex items-center mb-3">
                            <h2 class="font-medium text-base mr-auto">Directory of Companies</h2>
                            <div class="ml-auto w-56">
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="companySearchTerm"
                                        class="form-control w-56 box pr-10" placeholder="Search...">
                                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0"
                                        data-feather="search"></i>
                                </div>
                            </div>
                        </div>

                        @if (count($masterCompanies) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mt-5">
                                @foreach ($masterCompanies as $company)
                                    <div class="intro-y">
                                        <div
                                            class="box p-4 {{ $selectedCompanyId == $company->id ? 'border-2 border-primary' : '' }}">
                                            <div class="flex items-center border-b pb-2">
                                                <div class="font-medium text-base truncate">
                                                    {{ $company->company_name }}</div>
                                                <div class="ml-auto">
                                                    @if ($selectedCompanyId == $company->id)
                                                        <div
                                                            class="text-xs px-1.5 py-0.5 bg-primary text-white rounded-full">
                                                            Selected</div>
                                                    @else
                                                        <button type="button"
                                                            wire:click="selectMasterCompany({{ $company->id }})"
                                                            class="btn btn-xs btn-outline-primary">
                                                            Select
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-slate-500 mt-2">
                                                <div><i data-feather="mail" class="w-3.5 h-3.5 mr-1 inline-block"></i>
                                                    {{ $company->email }}</div>
                                                @if ($company->phone)
                                                    <div><i data-feather="phone"
                                                            class="w-3.5 h-3.5 mr-1 inline-block"></i>
                                                        {{ $company->phone }}</div>
                                                @endif
                                                @if ($company->address)
                                                    <div><i data-feather="map-pin"
                                                            class="w-3.5 h-3.5 mr-1 inline-block"></i>
                                                        {{ $company->address }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if (count($masterCompanies) === 0)
                                <div class="text-center p-10">
                                    <i data-feather="search" class="w-12 h-12 text-slate-300 mx-auto"></i>
                                    <div class="mt-2 text-slate-500">No companies found that match your search.</div>
                                </div>
                            @endif

                            @error('selectedCompanyId')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        @else
                            <div class="text-center p-10 bg-slate-50 rounded-md">
                                <i data-feather="briefcase" class="w-12 h-12 text-slate-300 mx-auto"></i>
                                <div class="mt-2 text-slate-500">No companies are available in the directory.</div>
                                <div class="mt-3">
                                    <button type="button" wire:click="$set('useExistingCompany', false)"
                                        class="btn btn-outline-primary">
                                        <i data-feather="plus" class="w-4 h-4 mr-1"></i> Create New Company
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Formulario de Nueva Empresa (si seleccionó crear nueva) -->
                @else
                    <div class="intro-y">
                        <h3 class="font-medium text-base mb-5">New Company Information</h3>

                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="company_name" class="form-label">Company Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model="company_name" id="company_name"
                                    class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="Company ABC">
                                @error('company_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <label for="company_email" class="form-label">Email of the Company <span
                                        class="text-danger">*</span></label>
                                <input type="email" wire:model="company_email" id="company_email"
                                    class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="contacto@empresa.com">
                                @error('company_email')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <label for="company_phone" class="form-label">Phone</label>
                                <input type="text" wire:model="company_phone" id="company_phone"
                                    class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="(555) 123-4567">
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <label for="company_contact" class="form-label">Contact Person</label>
                                <input type="text" wire:model="company_contact" id="company_contact"
                                    class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="John Doe">
                            </div>

                            <div class="col-span-12">
                                <label for="company_address" class="form-label">Address</label>
                                <input type="text" wire:model="company_address" id="company_address"
                                    class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="123 Main St">
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <label for="company_city" class="form-label">Ciudad</label>
                                <input type="text" wire:model="company_city" id="company_city"
                                    class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="Ciudad">
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <label for="company_state" class="form-label">Estado</label>
                                <input type="text" wire:model="company_state" id="company_state"
                                    class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="Estado">
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <label for="company_zip" class="form-label">Código Postal</label>
                                <input type="text" wire:model="company_zip" id="company_zip" class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary"
                                    placeholder="12345">
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="form-check">
                                <input id="add_to_directory" wire:model="addToDirectory" class="form-check-input"
                                    type="checkbox">
                                <label class="form-check-label" for="add_to_directory">
                                    Agregar esta empresa al directorio para uso futuro
                                </label>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Paso 3: Detalles de Empleo -->
        @elseif ($currentStep == 3)
            <div class="intro-y">
                <h2 class="text-lg font-medium mb-5">Details of Employment</h2>

                <!-- Resumen de selección -->
                <div class="grid grid-cols-12 gap-4 mb-5">
                    <div class="col-span-12 sm:col-span-6 bg-slate-50 rounded-md p-3 border-l-4 border-primary">
                        <p class="text-slate-600">Driver: <strong>{{ $selectedDriver->user->name }}
                            {{ $selectedDriver->last_name ?? '' }}</strong></p>
                    </div>
                    <div class="col-span-12 sm:col-span-6 bg-slate-50 rounded-md p-3 border-l-4 border-success">
                        <p class="text-slate-600">Company: <strong>{{ $company_name }}</strong></p>
                    </div>
                </div>

                <!-- Formulario de detalles de empleo -->
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-6">
                        <label for="employed_from" class="form-label">Employed From <span
                                class="text-danger">*</span></label>
                        <input type="date" wire:model="employed_from" id="employed_from" class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary">
                        @error('employed_from')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-span-12 sm:col-span-6">
                        <label for="employed_to" class="form-label">Employed To <span
                                class="text-danger">*</span></label>
                        <input type="date" wire:model="employed_to" id="employed_to" class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary">
                        @error('employed_to')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-span-12">
                        <label for="positions_held" class="form-label">Positions Held <span
                                class="text-danger">*</span></label>
                        <input type="text" wire:model="positions_held" id="positions_held" class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary"
                            placeholder="Driver, Operator, etc.">
                        @error('positions_held')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-span-12">
                        <label for="reason_for_leaving" class="form-label">Reason for Leaving <span
                                class="text-danger">*</span></label>
                        <input type="text" wire:model="reason_for_leaving" id="reason_for_leaving"
                            class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary" placeholder="Resignation, Termination, etc.">
                        @error('reason_for_leaving')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-span-12">
                        <label for="additional_notes" class="form-label">Additional Notes</label>
                        <textarea wire:model="additional_notes" id="additional_notes" class="w-full bg-slate-50 rounded-md p-3 mb-5 border-l-4 border-primary h-20"
                            placeholder="Additional information about the employment..."></textarea>
                    </div>
                </div>

                <!-- Confirmación final -->
                <div class="alert alert-primary show flex items-center mt-5">
                    <i data-feather="alert-circle" class="w-6 h-6 mr-2"></i>
                    <span>An email will be sent to <strong>{{ $company_email }}</strong> requesting
                        verification of employment.</span>
                </div>
            </div>
        @endif

        <!-- Botones de Navegación -->
        <div class="flex justify-between mt-8 pt-5 border-t">
            <!-- Botón Anterior / Cancelar -->
            <div>
                @if ($currentStep > 1)
                    <button type="button" wire:click="previousStep" class="btn btn-outline-secondary">
                        <i data-feather="chevron-left" class="w-4 h-4 mr-1"></i> Previous
                    </button>
                @else
                    <button type="button" wire:click="cancel" class="btn btn-outline-secondary">
                        <i data-feather="x" class="w-4 h-4 mr-1"></i> Cancel
                    </button>
                @endif
            </div>

            <!-- Botones Siguiente y Enviar -->
            <div>
                @if ($currentStep < 3)
                    <button type="button" 
                        wire:click="nextStep" 
                        class="btn btn-primary" 
                        {{ ($currentStep == 1 && !$selectedDriverId) || 
                           ($currentStep == 2 && $useExistingCompany && !$selectedCompanyId) || 
                           ($currentStep == 2 && !$useExistingCompany && (!$company_name || !$company_email)) ? 'disabled' : '' }}>
                        Next <i data-feather="chevron-right" class="w-4 h-4 ml-1"></i>
                    </button>
                @else
                    <button type="button" wire:click="sendVerificationRequest" class="btn btn-success"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendVerificationRequest">
                            <i data-feather="send" class="w-4 h-4 mr-1"></i> Send Request
                        </span>
                        <span wire:loading wire:target="sendVerificationRequest">
                            <i data-feather="loader" class="w-4 h-4 mr-1 animate-spin"></i> Processing...
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', function() {
        // Alpine.js manejará la interacción con TomSelect y los eventos
    });
</script>
