@extends('../themes/' . $activeTheme)
@section('title', 'All Drivers Overview')

@push('styles')
<style>
    /* Estilos personalizados para los tabs */
    .ef-tab-button {
        border-bottom: 2px solid transparent;
        cursor: pointer;
        padding: 0.5rem 1rem;
        font-weight: 500;
        display: inline-block;
    }

    .ef-tab-button.active {
        border-bottom: 2px solid rgb(79, 70, 229);
        color: rgb(79, 70, 229);
    }
    
    .ef-tab-content {
        display: none;
    }
    
    .ef-tab-content.active {
        display: block;
    }
</style>
@endpush

@push('styles')
<style>
    /* Estilos para las pestañas de documentos categorizados */
    .ef-tab-button {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
        color: #64748b; /* slate-500 */
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .ef-tab-button:hover {
        color: #334155; /* slate-700 */
    }
    
    .ef-tab-button.active {
        color: #2563eb; /* blue-600 */
        border-bottom-color: #2563eb; /* blue-600 */
    }
    
    .ef-tab-content {
        display: none;
    }
    
    .ef-tab-content.active {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    // Script para manejar los tabs y prevenir errores de JavaScript
    window.addEventListener('load', function() {
        // Sobrescribir funciones de scripts globales que causan errores
        if (window.tab) window.tab = function() { return {}; };
        if (window.dom) window.dom = function() { return {}; };
        
        // Manejar errores de iconos Lucide
        if (window.lucide) {
            // Sobrescribir createIcons para ignorar errores
            const originalCreateIcons = window.lucide.createIcons;
            window.lucide.createIcons = function(attrs) {
                try {
                    // Registrar iconos faltantes
                    if (!window.lucide.icons['circle-check-big']) {
                        window.lucide.icons['circle-check-big'] = window.lucide.icons['check-circle'] || {};
                    }
                    return originalCreateIcons(attrs);
                } catch (e) {
                    console.log('Error controlado en lucide.createIcons:', e);
                    return {};
                }
            };
        }
        
        // Sistema de tabs independiente con vanilla JavaScript
        function initTabs() {
            const tabsContainer = document.getElementById('ef-categorized-docs-tabs');
            if (!tabsContainer) return;
            
            const tabButtons = tabsContainer.querySelectorAll('.ef-tab-button');
            const tabContents = tabsContainer.querySelectorAll('.ef-tab-content');
            
            // Función para activar un tab específico
            function activateTab(tabId) {
                // Activar/desactivar botones
                tabButtons.forEach(btn => {
                    if (btn.getAttribute('data-tab') === tabId) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
                
                // Mostrar/ocultar contenido
                tabContents.forEach(content => {
                    if (content.getAttribute('data-tab-content') === tabId) {
                        content.classList.add('active');
                    } else {
                        content.classList.remove('active');
                    }
                });
            }
            
            // Asignar eventos click a los botones
            tabButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('data-tab');
                    if (tabId) activateTab(tabId);
                });
            });
            
            // Activar el primer tab por defecto
            if (tabButtons.length > 0) {
                const firstTabId = tabButtons[0].getAttribute('data-tab');
                if (firstTabId) activateTab(firstTabId);
            }
        }
        
        // Descativar cualquier inicialización de tabs del DOM global
        document.querySelectorAll('.tab').forEach(function(el) {
            if (el._x_dataStack) {
                try { delete el._x_dataStack; } catch (e) {}
            }
        });
        
        // Inicializar nuestro sistema de tabs
        try {
            initTabs();
        } catch (e) {
            console.log('Error al inicializar tabs:', e);
        }
        
        // Forzar que los scripts problematicos no se ejecuten
        const originalDefineProperty = Object.defineProperty;
        Object.defineProperty = function(obj, prop, descriptor) {
            if (prop === 'on' && obj === undefined) {
                return obj; // Prevenir la asignación que causa el error
            }
            return originalDefineProperty(obj, prop, descriptor);
        };
    });
</script>
@endpush

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers', 'url' => route('admin.drivers.index')],
        ['label' => 'Drivers Overview', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto grid">
        <!-- Page Header -->
        <div class="flex justify-between items-center py-4 mb-4 border-b">
            <div class="flex items-center">
                <a href="{{ route('admin.drivers.index') }}" class="mr-4 text-slate-500 hover:text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <h2 class="text-xl font-medium">Driver Details</h2>
            </div>
            {{-- <div>
            <a href="{{ route('admin.drivers.documents.download', $driver->id) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download Documents
            </a>
        </div> --}}
        </div>

        <!-- Driver Profile -->
        <div class="box box--stacked flex flex-col  p-6 mb-6">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <div class="w-24 h-24 flex-shrink-0">
                    <img src="{{ $driver->getProfilePhotoUrlAttribute() }}" alt="{{ $driver->user->name ?? 'Unknown' }}"
                        class="w-full h-full rounded-full object-cover border-4 border-white shadow">
                </div>
                <div class="flex-grow text-center md:text-left">
                    <h3 class="text-2xl font-bold">
                        {{ $driver->user->name ?? 'Unknown' }} {{ $driver->middle_name }} {{ $driver->last_name }}
                    </h3>
                    <p class="text-slate-500">{{ $driver->user->email ?? 'No email' }}</p>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start mt-2">
                        <div class="flex items-center">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center text-slate-500 text-sm">
                            <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                            Joined {{ $driver->created_at->format('M d, Y') }}
                        </div>
                        <div class="flex items-center text-slate-500 text-sm">
                            <i data-lucide="Building" class="w-4 h-4 mr-1"></i>
                            {{ $driver->carrier->name ?? 'No carrier' }}
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-auto md:ml-auto">
                    <div class="bg-slate-50 rounded p-4 text-center">
                        <p class="text-slate-500 text-sm">Profile Completion</p>
                        <div class="text-2xl font-bold mt-1">{{ $driver->completion_percentage ?? 0 }}%</div>
                        <div class="w-full bg-slate-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full"
                                style="width: {{ $driver->completion_percentage ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Content -->
        <div class="box box--stacked flex flex-col mb-6">
            <div class="border-b border-slate-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="driverTabs" role="tablist">
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-blue-600 rounded-t-lg active" id="general-tab"
                            data-tabs-target="#general" type="button" role="tab" aria-controls="general"
                            aria-selected="true">General Info</button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                            id="licenses-tab" data-tabs-target="#licenses" type="button" role="tab"
                            aria-controls="licenses" aria-selected="false">Licenses</button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                            id="medical-tab" data-tabs-target="#medical" type="button" role="tab"
                            aria-controls="medical" aria-selected="false">Medical</button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                            id="history-tab" data-tabs-target="#history" type="button" role="tab"
                            aria-controls="history" aria-selected="false">Employment</button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                            id="training-tab" data-tabs-target="#training" type="button" role="tab"
                            aria-controls="training" aria-selected="false">Training & Courses</button>
                    </li>

                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                            id="testing-tab" data-tabs-target="#testing" type="button" role="tab"
                            aria-controls="testing" aria-selected="false">Testing</button>
                    </li>

                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                            id="inspections-tab" data-tabs-target="#inspections" type="button" role="tab"
                            aria-controls="inspections" aria-selected="false">Inspections</button>
                    </li>

                    <li role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                            id="documents-tab" data-tabs-target="#documents" type="button" role="tab"
                            aria-controls="documents" aria-selected="false">Documents</button>
                    </li>
                </ul>
            </div>

            <div id="driverTabsContent">
                <!-- General Information Tab -->
                <div class="p-6" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-lg mb-4 pb-2 border-b">Personal Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-slate-500 text-sm">Full Name</p>
                                    <p>{{ $driver->user->name ?? 'Unknown' }} {{ $driver->middle_name }}
                                        {{ $driver->last_name }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Email Address</p>
                                    <p>{{ $driver->user->email ?? 'No email' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Phone Number</p>
                                    <p>{{ $driver->phone ?? 'No phone' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Date of Birth</p>
                                    <p>{{ $driver->date_of_birth ? $driver->date_of_birth->format('M d, Y') : 'Not provided' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Carrier Information -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-lg mb-4 pb-2 border-b">Carrier Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-slate-500 text-sm">Carrier</p>
                                    <p>{{ $driver->carrier->name ?? 'No carrier' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Status</p>
                                    <p
                                        class="{{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'Active' : 'Inactive' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Joined Date</p>
                                    <p>{{ $driver->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Application Status</p>
                                    <p
                                        class="{{ $driver->application
                                            ? ($driver->application->status == 'approved'
                                                ? 'text-green-600'
                                                : ($driver->application->status == 'pending'
                                                    ? 'text-amber-600'
                                                    : 'text-red-600'))
                                            : 'text-slate-500' }}">
                                        {{ $driver->application ? ucfirst($driver->application->status) : 'No Application' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="border rounded-lg p-4 md:col-span-2">
                            <h4 class="font-medium text-lg mb-4 pb-2 border-b">Address Information</h4>
                            @if ($driver->application && $driver->application->addresses->count() > 0)
                                <div class="space-y-4">
                                    @foreach ($driver->application->addresses as $address)
                                        <div class="{{ !$loop->last ? 'pb-4 border-b' : '' }}">
                                            <p class="font-medium">
                                                {{ $address->primary ? 'Current Address' : 'Previous Address' }}</p>
                                            <p>{{ $address->address_line1 }}</p>
                                            @if ($address->address_line2)
                                                <p>{{ $address->address_line2 }}</p>
                                            @endif
                                            <p>{{ $address->city }}, {{ $address->state }} {{ $address->zip_code }}</p>
                                            <p class="text-slate-500 text-xs mt-1">
                                                {{ $address->from_date ? $address->from_date->format('M Y') : '' }}
                                                {{ $address->to_date ? ' - ' . $address->to_date->format('M Y') : ' - Present' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-slate-500">No address information provided</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Licenses Tab -->
                <div class="hidden p-6" id="licenses" role="tabpanel" aria-labelledby="licenses-tab">
                    <!-- License Information -->
                    <div class="border rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">License Information</h4>
                        @if ($driver->licenses && $driver->licenses->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2">License Number</th>
                                            <th class="px-4 py-2">State</th>
                                            <th class="px-4 py-2">Class</th>
                                            <th class="px-4 py-2">Type</th>
                                            <th class="px-4 py-2">Expiration</th>
                                            <th class="px-4 py-2">Images</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($driver->licenses as $license)
                                            <tr class="border-b">
                                                <td class="px-4 py-2">{{ $license->license_number }}</td>
                                                <td class="px-4 py-2">{{ $license->state_of_issue }}</td>
                                                <td class="px-4 py-2">{{ $license->license_class }}</td>
                                                <td class="px-4 py-2">
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium {{ $license->is_cdl ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">
                                                        {{ $license->is_cdl ? 'CDL' : 'Standard' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">
                                                    {{ $license->expiration_date ? $license->expiration_date->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="flex space-x-2">
                                                        @if ($license->getFirstMediaUrl('license_front'))
                                                            <a href="{{ $license->getFirstMediaUrl('license_front') }}"
                                                                target="_blank"
                                                                class="text-blue-600 hover:underline">Front</a>
                                                        @endif
                                                        @if ($license->getFirstMediaUrl('license_back'))
                                                            <a href="{{ $license->getFirstMediaUrl('license_back') }}"
                                                                target="_blank"
                                                                class="text-blue-600 hover:underline">Back</a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Endorsements -->
                            @if ($driver->licenses->first() && $driver->licenses->first()->endorsements->count() > 0)
                                <div class="mt-4 pt-4 border-t">
                                    <h5 class="font-medium mb-2">Endorsements</h5>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($driver->licenses->first()->endorsements as $endorsement)
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $endorsement->code }} - {{ $endorsement->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-slate-500">No license information provided</p>
                        @endif
                    </div>

                    <!-- Driving Experience -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Driving Experience</h4>
                        @if ($driver->experiences && $driver->experiences->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2">Equipment Type</th>
                                            <th class="px-4 py-2">Years Experience</th>
                                            <th class="px-4 py-2">Miles Driven</th>
                                            <th class="px-4 py-2">Requires CDL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($driver->experiences as $experience)
                                            <tr class="border-b">
                                                <td class="px-4 py-2">{{ $experience->equipment_type }}</td>
                                                <td class="px-4 py-2">{{ $experience->years_experience }}</td>
                                                <td class="px-4 py-2">{{ number_format($experience->miles_driven) }}</td>
                                                <td class="px-4 py-2">
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium {{ $experience->requires_cdl ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">
                                                        {{ $experience->requires_cdl ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-slate-500">No driving experience information provided</p>
                        @endif
                    </div>
                </div>

                <!-- Medical Tab -->
                <div class="hidden p-6" id="medical" role="tabpanel" aria-labelledby="medical-tab">
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Medical Qualification</h4>
                        @if ($driver->medicalQualification)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-slate-500 text-sm">Medical Examiner</p>
                                    <p>{{ $driver->medicalQualification->medical_examiner_name }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Registry Number</p>
                                    <p>{{ $driver->medicalQualification->medical_examiner_registry_number }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Medical Card Expiration</p>
                                    <p class="flex items-center">
                                        {{ $driver->medicalQualification->medical_card_expiration_date ? $driver->medicalQualification->medical_card_expiration_date->format('M d, Y') : 'N/A' }}
                                        @if ($driver->medicalQualification->medical_card_expiration_date)
                                            @if ($driver->medicalQualification->medical_card_expiration_date->isPast())
                                                <span
                                                    class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>
                                            @elseif($driver->medicalQualification->medical_card_expiration_date->diffInDays(now()) < 30)
                                                <span
                                                    class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Expiring
                                                    Soon</span>
                                            @else
                                                <span
                                                    class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Valid</span>
                                            @endif
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-sm">Social Security (Last 4)</p>
                                    <p>
                                        @if ($driver->medicalQualification->social_security_number)
                                            XXX-XX-{{ substr($driver->medicalQualification->social_security_number, -4) }}
                                        @else
                                            Not provided
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Medical Card -->
                            <div class="mt-4 pt-4 border-t">
                                <p class="text-slate-500 text-sm mb-2">Medical Card</p>
                                @if ($driver->medicalQualification->getFirstMediaUrl('medical_card'))
                                    <a href="{{ $driver->medicalQualification->getFirstMediaUrl('medical_card') }}"
                                        target="_blank" class="text-blue-600 hover:underline flex items-center">
                                        <i data-lucide="external-link" class="w-4 h-4 mr-1"></i>
                                        View Medical Card
                                    </a>
                                @else
                                    <p class="text-slate-500">No medical card uploaded</p>
                                @endif
                            </div>
                        @else
                            <p class="text-slate-500">No medical qualification information provided</p>
                        @endif
                    </div>
                </div>

                <!-- Employment History Tab -->
                <div class="hidden p-6" id="history" role="tabpanel" aria-labelledby="history-tab">
                    <!-- Employment History -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Employment History</h4>

                        <!-- Pestañas para diferentes tipos de historial -->
                        <div class="mb-4 border-b">
                            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                                <li class="mr-2">
                                    <button type="button" class="inline-block p-2 border-b-2 border-blue-600 text-blue-600 active" 
                                        onclick="toggleEmploymentSection('companies')"
                                        id="companies-tab">Companies</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="inline-block p-2 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" 
                                        onclick="toggleEmploymentSection('unemployment')"
                                        id="unemployment-tab">Unemployment Periods</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="inline-block p-2 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" 
                                        onclick="toggleEmploymentSection('related')"
                                        id="related-tab">Related Employment</button>
                                </li>
                            </ul>
                        </div>

                        <!-- Sección de Compañías de Empleo -->
                        <div id="companies-section" class="employment-section">
                            @if ($driver->employmentCompanies && $driver->employmentCompanies->count() > 0)
                                <div class="space-y-5">
                                    @foreach ($driver->employmentCompanies as $employment)
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h5 class="font-medium text-base">{{ $employment->company ? $employment->company->company_name : 'Company' }}</h5>
                                                    <p class="text-sm text-gray-600">
                                                        {{ $employment->employed_from ? $employment->employed_from->format('M Y') : '' }} - 
                                                        {{ $employment->employed_to ? $employment->employed_to->format('M Y') : 'Present' }}
                                                    </p>
                                                </div>
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ $employment->positions_held }}</span>
                                            </div>

                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @if ($employment->company)
                                                    <div>
                                                        <h6 class="text-xs font-medium text-gray-500 uppercase">Company Information</h6>
                                                        <p class="text-sm mt-1">{{ $employment->company->address ?? '' }}</p>
                                                        <p class="text-sm">{{ $employment->company->city ?? '' }}, {{ $employment->company->state ?? '' }} {{ $employment->company->zip ?? '' }}</p>
                                                        <p class="text-sm">{{ $employment->company->phone ?? '' }}</p>
                                                        <p class="text-sm">{{ $employment->email ?? $employment->company->email ?? '' }}</p>
                                                    </div>
                                                @endif

                                                <div>
                                                    <h6 class="text-xs font-medium text-gray-500 uppercase">Employment Details</h6>
                                                    <p class="text-sm mt-1"><span class="font-medium">Position:</span> {{ $employment->positions_held }}</p>
                                                    <p class="text-sm"><span class="font-medium">Reason for leaving:</span> {{ $employment->reason_for_leaving }}</p>
                                                    @if ($employment->reason_for_leaving == 'other' && $employment->other_reason_description)
                                                        <p class="text-sm"><span class="font-medium">Other reason:</span> {{ $employment->other_reason_description }}</p>
                                                    @endif
                                                    @if ($employment->explanation)
                                                        <p class="text-sm"><span class="font-medium">Explanation:</span> {{ $employment->explanation }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <span class="bg-{{ $employment->subject_to_fmcsr ? 'green' : 'red' }}-100 text-{{ $employment->subject_to_fmcsr ? 'green' : 'red' }}-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                    {{ $employment->subject_to_fmcsr ? 'Subject to FMCSR' : 'Not subject to FMCSR' }}
                                                </span>
                                                <span class="bg-{{ $employment->safety_sensitive_function ? 'green' : 'red' }}-100 text-{{ $employment->safety_sensitive_function ? 'green' : 'red' }}-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                    {{ $employment->safety_sensitive_function ? 'Safety Sensitive Function' : 'No Safety Sensitive Function' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">No employment history available.</p>
                            @endif
                        </div>

                        <!-- Sección de Períodos de Desempleo -->
                        <div id="unemployment-section" class="employment-section hidden">
                            @if ($driver->unemploymentPeriods && $driver->unemploymentPeriods->count() > 0)
                                <div class="space-y-4">
                                    @foreach ($driver->unemploymentPeriods as $period)
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h5 class="font-medium text-base">Unemployment Period</h5>
                                                    <p class="text-sm text-gray-600">
                                                        {{ $period->start_date ? $period->start_date->format('M d, Y') : '' }} - 
                                                        {{ $period->end_date ? $period->end_date->format('M d, Y') : 'Present' }}
                                                    </p>
                                                </div>
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Unemployment</span>
                                            </div>

                                            @if ($period->comments)
                                                <div class="mt-3">
                                                    <h6 class="text-xs font-medium text-gray-500 uppercase">Comments</h6>
                                                    <p class="text-sm mt-1 bg-gray-100 p-2 rounded">{{ $period->comments }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">No unemployment periods recorded.</p>
                            @endif
                        </div>

                        <!-- Sección de Empleos Relacionados con Conducción -->
                        <div id="related-section" class="employment-section hidden">
                            @if ($driver->relatedEmployments && $driver->relatedEmployments->count() > 0)
                                <div class="space-y-4">
                                    @foreach ($driver->relatedEmployments as $related)
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h5 class="font-medium text-base">{{ $related->position }}</h5>
                                                    <p class="text-sm text-gray-600">
                                                        {{ $related->start_date ? $related->start_date->format('M d, Y') : '' }} - 
                                                        {{ $related->end_date ? $related->end_date->format('M d, Y') : 'Present' }}
                                                    </p>
                                                </div>
                                                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded">Driving Related</span>
                                            </div>

                                            @if ($related->comments)
                                                <div class="mt-3">
                                                    <h6 class="text-xs font-medium text-gray-500 uppercase">Comments</h6>
                                                    <p class="text-sm mt-1 bg-gray-100 p-2 rounded">{{ $related->comments }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">No driving-related employment recorded.</p>
                            @endif
                        </div>

                        @if ($driver->getMedia('driving_records')->count() > 0)
                            <div class="mt-4 pt-4 border-t">
                                <h5 class="font-medium">Driving Records</h5>
                                <div class="flex flex-wrap gap-3 mt-2">
                                    @foreach ($driver->getMedia('driving_records') as $record)
                                        <a href="{{ $record->getUrl() }}" target="_blank" class="text-blue-600 hover:underline flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            {{ $record->file_name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Script para manejar las pestañas de empleo -->
                    <script>
                        function toggleEmploymentSection(sectionId) {
                            // Ocultar todas las secciones
                            document.querySelectorAll('.employment-section').forEach(section => {
                                section.classList.add('hidden');
                            });
                            
                            // Mostrar la sección seleccionada
                            document.getElementById(sectionId + '-section').classList.remove('hidden');
                            
                            // Actualizar estilos de las pestañas
                            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                                tab.classList.remove('border-blue-600', 'text-blue-600');
                                tab.classList.add('border-transparent');
                            });
                            
                            document.getElementById(sectionId + '-tab').classList.add('border-blue-600', 'text-blue-600');
                            document.getElementById(sectionId + '-tab').classList.remove('border-transparent');
                        }
                    </script>
                    </div>
                </div>

                <!-- Training & Courses Tab -->
                <div class="hidden p-6" id="training" role="tabpanel" aria-labelledby="training-tab">
                    <!-- Pestañas para Training Schools y Courses -->
                    <div class="mb-4 border-b">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="mr-2">
                                <button type="button" class="inline-block p-2 border-b-2 border-blue-600 text-blue-600 active" 
                                    onclick="toggleTrainingSection('schools')"
                                    id="schools-tab">Training Schools</button>
                            </li>
                            <li class="mr-2">
                                <button type="button" class="inline-block p-2 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" 
                                    onclick="toggleTrainingSection('courses')"
                                    id="courses-tab">Courses</button>
                            </li>
                        </ul>
                    </div>

                    <!-- Sección de Escuelas de Entrenamiento -->
                    <div id="schools-section" class="training-section">
                        <div class="border rounded-lg p-4 mb-6">
                            <h4 class="font-medium text-lg mb-4 pb-2 border-b">Training Schools</h4>

                            @if ($driver->trainingSchools && $driver->trainingSchools->count() > 0)
                                <div class="space-y-6">
                                    @foreach ($driver->trainingSchools as $school)
                                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <h5 class="font-medium text-base">{{ $school->school_name }}</h5>
                                                    <p class="text-slate-500">
                                                        {{ $school->city }}, {{ $school->state }}
                                                        @if ($school->start_date && $school->end_date)
                                                            <span class="mx-1">|</span>
                                                            {{ $school->start_date->format('M d, Y') }} -
                                                            {{ $school->end_date->format('M d, Y') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div>
                                                    @if ($school->completed)
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">In Progress</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if ($school->description)
                                                <div class="mt-3">
                                                    <h6 class="text-xs font-medium text-gray-500 uppercase">Description</h6>
                                                    <p class="text-sm mt-1">{{ $school->description }}</p>
                                                </div>
                                            @endif

                                            <!-- Certificados -->
                                            @if ($school->getDocuments('school_certificates') && $school->getDocuments('school_certificates')->count() > 0)
                                                <div class="mt-3">
                                                    <h6 class="text-xs font-medium text-gray-500 uppercase">Certificates</h6>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        @foreach ($school->getDocuments('school_certificates') as $certificate)
                                                            <a href="{{ $certificate->getUrl() }}" target="_blank"
                                                                class="flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                                {{ Str::limit($certificate->file_name, 20) }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-slate-500">No training schools information available</p>
                            @endif
                        </div>
                    </div>

                    <!-- Sección de Cursos -->
                    <div id="courses-section" class="training-section hidden">
                        <div class="border rounded-lg p-4 mb-6">
                            <h4 class="font-medium text-lg mb-4 pb-2 border-b">Driver Courses</h4>

                            @if ($driver->courses && $driver->courses->count() > 0)
                                <div class="space-y-6">
                                    @foreach ($driver->courses as $course)
                                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <h5 class="font-medium text-base">{{ $course->course_name }}</h5>
                                                    <p class="text-slate-500">
                                                        {{ $course->provider ?? 'No provider specified' }}
                                                        @if ($course->start_date && $course->end_date)
                                                            <span class="mx-1">|</span>
                                                            {{ $course->start_date->format('M d, Y') }} -
                                                            {{ $course->end_date->format('M d, Y') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div>
                                                    @if ($course->completed)
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">In Progress</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if ($course->description)
                                                <div class="mt-3">
                                                    <h6 class="text-xs font-medium text-gray-500 uppercase">Description</h6>
                                                    <p class="text-sm mt-1">{{ $course->description }}</p>
                                                </div>
                                            @endif

                                            <!-- Certificados del curso -->
                                            @if ($course->getMedia('course_certificates') && $course->getMedia('course_certificates')->count() > 0)
                                                <div class="mt-3">
                                                    <h6 class="text-xs font-medium text-gray-500 uppercase">Certificates</h6>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        @foreach ($course->getMedia('course_certificates') as $certificate)
                                                            <a href="{{ $certificate->getUrl() }}" target="_blank"
                                                                class="flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                                {{ Str::limit($certificate->file_name, 20) }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-slate-500">No courses information available</p>
                            @endif
                        </div>
                    </div>

                    <!-- Script para manejar las pestañas de training -->
                    <script>
                        function toggleTrainingSection(sectionId) {
                            // Ocultar todas las secciones
                            document.querySelectorAll('.training-section').forEach(section => {
                                section.classList.add('hidden');
                            });
                            
                            // Mostrar la sección seleccionada
                            document.getElementById(sectionId + '-section').classList.remove('hidden');
                            
                            // Actualizar estilos de las pestañas
                            document.querySelectorAll('#schools-tab, #courses-tab').forEach(tab => {
                                tab.classList.remove('border-blue-600', 'text-blue-600');
                                tab.classList.add('border-transparent');
                            });
                            
                            document.getElementById(sectionId + '-tab').classList.add('border-blue-600', 'text-blue-600');
                            document.getElementById(sectionId + '-tab').classList.remove('border-transparent');
                        }
                    </script>
                </div>

                <!-- Testing Tab -->
                <div class="hidden p-6" id="testing" role="tabpanel" aria-labelledby="testing-tab">
                    <div class="border rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium text-lg pb-2">Drug & Alcohol Testing</h4>
                        </div>

                        @if ($driver->testings && $driver->testings->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="whitespace-nowrap">Date</th>
                                            <th class="whitespace-nowrap">Type</th>
                                            <th class="whitespace-nowrap">Result</th>
                                            <th class="whitespace-nowrap">Status</th>
                                            <th class="whitespace-nowrap">Administrator</th>
                                            <th class="whitespace-nowrap">Documents</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($driver->testings as $test)
                                            <tr>
                                                <td>{{ $test->test_date ? $test->test_date->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td>
                                                    @if (isset(\App\Models\Admin\Driver\DriverTesting::getTestTypes()[$test->test_type]))
                                                        {{ \App\Models\Admin\Driver\DriverTesting::getTestTypes()[$test->test_type] }}
                                                    @else
                                                        {{ $test->test_type }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($test->test_result)
                                                        @if (isset(\App\Models\Admin\Driver\DriverTesting::getTestResults()[$test->test_result]))
                                                            <span
                                                                class="@if ($test->test_result == 'negative') text-green-600 @elseif($test->test_result == 'positive') text-red-600 @else text-yellow-600 @endif font-medium">
                                                                {{ \App\Models\Admin\Driver\DriverTesting::getTestResults()[$test->test_result] }}
                                                            </span>
                                                        @else
                                                            {{ $test->test_result }}
                                                        @endif
                                                    @else
                                                        Pending
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (isset(\App\Models\Admin\Driver\DriverTesting::getStatuses()[$test->status]))
                                                        <span
                                                            class="px-2 py-0.5 rounded-full text-xs font-medium
                                                        @if ($test->status == 'completed' || $test->status == 'approved') bg-green-100 text-green-800
                                                        @elseif($test->status == 'rejected' || $test->status == 'cancelled') bg-rose-100 text-rose-800
                                                        @else bg-yellow-100 text-yellow-800 @endif">
                                                            {{ \App\Models\Admin\Driver\DriverTesting::getStatuses()[$test->status] }}
                                                        </span>
                                                    @else
                                                        {{ $test->status }}
                                                    @endif
                                                </td>
                                                <td>{{ $test->administered_by }}</td>
                                                <td>
                                                    <div class="flex space-x-2">
                                                        @if ($test->getMedia('drug_test_pdf')->count() > 0)
                                                            <a href="{{ $test->getMedia('drug_test_pdf')->first()->getUrl() }}"
                                                                target="_blank" class="btn btn-sm btn-primary">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Report
                                                            </a>
                                                        @endif
                                                        @if ($test->getMedia('test_results')->count() > 0)
                                                            <a href="{{ $test->getMedia('test_results')->first()->getUrl() }}"
                                                                target="_blank" class="btn btn-sm btn-secondary">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                                Results
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-slate-500">No testing records available</p>
                        @endif
                    </div>
                </div>

                <!-- Inspections Tab -->
                <div class="hidden p-6" id="inspections" role="tabpanel" aria-labelledby="inspections-tab">
                    <div class="border rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium text-lg pb-2">Vehicle Inspections</h4>
                        </div>

                        @if ($driver->inspections && $driver->inspections->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="whitespace-nowrap">Date</th>
                                            <th class="whitespace-nowrap">Vehicle</th>
                                            <th class="whitespace-nowrap">Type</th>
                                            <th class="whitespace-nowrap">Inspector</th>
                                            <th class="whitespace-nowrap">Status</th>
                                            <th class="whitespace-nowrap">Safe to Operate</th>
                                            <th class="whitespace-nowrap">Documents</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($driver->inspections as $inspection)
                                            <tr>
                                                <td>{{ $inspection->inspection_date ? $inspection->inspection_date->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td>
                                                    @if ($inspection->vehicle)
                                                        {{ $inspection->vehicle->unit_number }} -
                                                        {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ $inspection->inspection_type }}</td>
                                                <td>{{ $inspection->inspector_name }}</td>
                                                <td>
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium
                                                    @if ($inspection->status == 'passed') bg-green-100 text-green-800
                                                    @elseif($inspection->status == 'failed') bg-rose-100 text-rose-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ ucfirst($inspection->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($inspection->is_vehicle_safe_to_operate)
                                                        <span class="text-green-600">Yes</span>
                                                    @else
                                                        <span class="text-red-600">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="flex space-x-2">
                                                        @if ($inspection->getMedia('inspection_documents')->count() > 0)
                                                            @foreach ($inspection->getMedia('inspection_documents') as $document)
                                                                <a href="{{ $document->getUrl() }}" target="_blank"
                                                                    class="btn btn-sm btn-secondary">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                                    {{ $loop->index + 1 }}
                                                                </a>
                                                            @endforeach
                                                        @else
                                                            <span class="text-slate-500">No documents</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-slate-500">No inspection records available</p>
                        @endif
                    </div>
                </div>

                <!-- Documents Tab -->
                <div class="hidden p-6" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Driver Documents -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-lg mb-4 pb-2 border-b">Driver Documents</h4>

                            <!-- Records específicos -->
                            <div class="mb-4 pb-3 border-b">
                                <h5 class="font-medium mb-2">Driver Records</h5>
                                <div class="grid grid-cols-1 gap-2">
                                    <!-- Driving Record -->
                                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            <span>Driving Record</span>
                                        </div>
                                        <div>
                                            @if ($drivingRecord)
                                                <a href="{{ $drivingRecord->getUrl() }}" target="_blank"
                                                    class="text-blue-600 hover:underline flex items-center">
                                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-sm">Not uploaded</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Medical Record -->
                                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            <span>Medical Record</span>
                                        </div>
                                        <div>
                                            @if ($medicalRecord)
                                                <a href="{{ $medicalRecord->getUrl() }}" target="_blank"
                                                    class="text-blue-600 hover:underline flex items-center">
                                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-sm">Not uploaded</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Criminal Record -->
                                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            <span>Criminal Record</span>
                                        </div>
                                        <div>
                                            @if ($criminalRecord)
                                                <a href="{{ $criminalRecord->getUrl() }}" target="_blank"
                                                    class="text-blue-600 hover:underline flex items-center">
                                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-sm">Not uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <ul class="space-y-3">
                                <!-- License Documents -->
                                @if ($driver->licenses && $driver->licenses->count() > 0)
                                    @foreach ($driver->licenses as $license)
                                        @if ($license->getFirstMediaUrl('license_front') || $license->getFirstMediaUrl('license_back'))
                                            <li class="pb-2 border-b last:border-b-0 last:pb-0">
                                                <p class="font-medium">Driver's License ({{ $license->license_number }})</p>
                                                <div class="flex space-x-4 mt-1">
                                                    @if ($license->getFirstMediaUrl('license_front'))
                                                        <a href="{{ $license->getFirstMediaUrl('license_front') }}"
                                                            target="_blank"
                                                            class="text-blue-600 hover:underline flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                                            Front Image
                                                        </a>
                                                    @endif
    
                                                    @if ($license->getFirstMediaUrl('license_back'))
                                                        <a href="{{ $license->getFirstMediaUrl('license_back') }}"
                                                            target="_blank"
                                                            class="text-blue-600 hover:underline flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                                            Back Image
                                                        </a>
                                                    @endif
                                                </div>
                                            </li>
                                        @endif
                                    @endforeach
                                @endif
    
                                <!-- Medical Card -->
                                @if ($driver->medicalQualification && $driver->medicalQualification->getFirstMediaUrl('medical_card'))
                                    <li class="pb-2 border-b last:border-b-0 last:pb-0">
                                        <p class="font-medium">Medical Card</p>
                                        <a href="{{ $driver->medicalQualification->getFirstMediaUrl('medical_card') }}"
                                            target="_blank" class="text-blue-600 hover:underline flex items-center mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            View Medical Card
                                        </a>
                                    </li>
                                @endif
    
                                <!-- Training Certificates -->
                                @php
                                    $hasCertificates = false;
                                    foreach ($driver->trainingSchools as $school) {
                                        if ($school->getDocuments('school_certificates')->count() > 0) {
                                            $hasCertificates = true;
                                            break;
                                        }
                                    }
                                @endphp
    
                                @if ($hasCertificates)
                                    <li class="pb-2 border-b last:border-b-0 last:pb-0">
                                        <p class="font-medium">Training Certificates</p>
                                        <div class="space-y-2 mt-1">
                                            @foreach ($driver->trainingSchools as $school)
                                                @if ($school->getDocuments('school_certificates')->count() > 0)
                                                    <div>
                                                        <p class="text-slate-500 text-sm">{{ $school->school_name }}</p>
                                                        <div class="flex flex-wrap gap-2 mt-1">
                                                            @foreach ($school->getDocuments('school_certificates') as $certificate)
                                                                <a href="{{ $certificate->getUrl() }}" target="_blank"
                                                                    class="text-blue-600 hover:underline flex items-center text-sm">
                                                                    <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>
                                                                    {{ Str::limit($certificate->file_name, 20) }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
    
                                <!-- Download All -->
                                <li class="pt-2 mt-2 border-t">
                                    <a href="{{ route('admin.drivers.documents.download', $driver->id) }}"
                                        class="text-blue-600 hover:underline flex items-center font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                        Download All Documents (ZIP)
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Application Documents -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-lg mb-4 pb-2 border-b">Application Documents</h4>

                            <div class="space-y-3">
                                @if ($driver->application && $driver->application->hasMedia('application_pdf'))
                                    <div class="pb-3 mb-3 border-b">
                                        <p class="font-medium">Complete Application PDF</p>
                                        <a href="{{ $driver->application->getFirstMediaUrl('application_pdf') }}"
                                            target="_blank" class="text-blue-600 hover:underline flex items-center mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            View Complete Application
                                        </a>
                                    </div>
                                @endif

                                <!-- Lease Agreement Documents -->
                                @php
                                    $basePath = storage_path(
                                        'app/public/driver/' . $driver->id . '/vehicle_verifications/',
                                    );
                                    $leaseAgreementThirdPartyPath = $basePath . 'lease_agreement_third_party.pdf';
                                    $leaseAgreementOwnerPath = $basePath . 'lease_agreement_owner_operator.pdf';
                                    $hasLeaseAgreementThirdParty = file_exists($leaseAgreementThirdPartyPath);
                                    $hasLeaseAgreementOwner = file_exists($leaseAgreementOwnerPath);
                                @endphp

                                @if ($hasLeaseAgreementThirdParty || $hasLeaseAgreementOwner)
                                    <div>
                                        <p class="font-medium">Lease Agreements</p>
                                        <div class="flex flex-col space-y-2 mt-1">
                                            @if ($hasLeaseAgreementThirdParty)
                                                <a href="{{ asset('storage/driver/' . $driver->id . '/vehicle_verifications/lease_agreement_third_party.pdf') }}"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                    Third Party Lease Agreement
                                                </a>
                                            @endif

                                            @if ($hasLeaseAgreementOwner)
                                                <a href="{{ asset('storage/driver/' . $driver->id . '/vehicle_verifications/lease_agreement_owner_operator.pdf') }}"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                    Owner Operator Lease Agreement
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if ($driver->application && $driver->application->hasMedia('signed_application'))
                                    <div>
                                        <p class="font-medium">Signed Application</p>
                                        <a href="{{ $driver->application->getFirstMediaUrl('signed_application') }}"
                                            target="_blank" class="text-blue-600 hover:underline flex items-center mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            View Signed Application
                                        </a>
                                    </div>
                                @endif

                                                            <!-- Application Status -->
                            @if ($driver->application)
                            <div class="mt-4 pt-4 border-t">
                                <p class="font-medium mb-2">Application Status</p>

                                <div class="space-y-2">
                                    <div class="flex">
                                        <span class="text-slate-500 w-1/3">Status:</span>
                                        <span
                                            class="{{ $driver->application->status == 'approved'
                                                ? 'text-green-600'
                                                : ($driver->application->status == 'pending'
                                                    ? 'text-amber-600'
                                                    : 'text-red-600') }}">
                                            {{ ucfirst($driver->application->status) }}
                                        </span>
                                    </div>

                                    <div class="flex">
                                        <span class="text-slate-500 w-1/3">Submitted:</span>
                                        <span>{{ $driver->application->created_at->format('M d, Y') }}</span>
                                    </div>

                                    @if ($driver->application->completed_at)
                                        <div class="flex">
                                            <span class="text-slate-500 w-1/3">Completed:</span>
                                            {{-- <span>{{ $driver->application->completed_at->format('M d, Y') }}</span> --}}
                                        </div>
                                    @endif

                                    @if ($driver->application->status == 'rejected' && $driver->application->rejection_reason)
                                        <div class="mt-2 pt-2 border-t">
                                            <p class="text-red-600 font-medium">Rejection Reason:</p>
                                            <p class="mt-1 text-sm bg-red-50 p-2 rounded">
                                                {{ $driver->application->rejection_reason }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                            </div>
                        </div>

                    </div>
                    <div class="mt-5">
                        <!-- Categorized Documents - Implementación simplificada -->
                        <div class="border rounded-lg p-4 mt-4">
                            <div class="flex justify-between items-center mb-2">
                                <h5 class="font-medium">Categorized Documents</h5>
                                <a href="{{ route('admin.drivers.documents.download', $driver->id) }}"
                                    class="flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    Download All Documents
                                </a>
                            </div>

                            <!-- Vanilla JS Tabs - Sin dependencias ni frameworks -->
                            <div id="ef-categorized-docs-tabs" class="categorized-docs-container">
                                <!-- Pestañas de categorías -->
                                <div class="mb-4 border-b overflow-x-auto">
                                    <div class="flex flex-nowrap -mb-px text-sm font-medium text-center">
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="license-docs">License</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="medical-docs">Medical</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="training-docs">Training Schools</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="courses-docs">Courses</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="accidents-docs">Accidents</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="traffic-docs">Traffic Violations</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="inspections-docs">Inspections</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="testing-docs">Testing</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="records-docs">Records</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="certification-docs">Application Forms</button>
                                        </div>
                                        <div class="mr-2">
                                            <button type="button" class="ef-tab-button" data-tab="other-docs">Other</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contenido de las pestañas -->
                                <div>
                                <!-- License Documents -->
                                <div class="ef-tab-content" data-tab-content="license-docs" role="tabpanel">
                                    @if (count($documentsByCategory['license']) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['license'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No license documents available</p>
                                    @endif
                                </div>

                                <!-- Medical Documents -->
                                <div class="ef-tab-content" data-tab-content="medical-docs" role="tabpanel">
                                    @if (count($documentsByCategory['medical']) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['medical'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No medical documents available</p>
                                    @endif
                                </div>

                                <!-- Training Schools Documents -->
                                <div class="ef-tab-content" data-tab-content="training-docs" role="tabpanel">
                                    @if (count($documentsByCategory['training_schools'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['training_schools'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No training school documents available</p>
                                    @endif
                                </div>

                                <!-- Courses Documents -->
                                <div class="ef-tab-content" data-tab-content="courses-docs" role="tabpanel">
                                    @if (count($documentsByCategory['courses'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['courses'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No course documents available</p>
                                    @endif
                                </div>

                                <!-- Accidents Documents -->
                                <div class="ef-tab-content" data-tab-content="accidents-docs" role="tabpanel">
                                    @if (count($documentsByCategory['accidents'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['accidents'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No accident documents available</p>
                                    @endif
                                </div>

                                <!-- Traffic Documents -->
                                <div class="ef-tab-content" data-tab-content="traffic-docs" role="tabpanel">
                                    @if (count($documentsByCategory['traffic'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['traffic'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No traffic violation documents available</p>
                                    @endif
                                </div>

                                <!-- Inspections Documents -->
                                <div class="ef-tab-content" data-tab-content="inspections-docs" role="tabpanel">
                                    @if (count($documentsByCategory['inspections'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['inspections'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No inspection documents available</p>
                                    @endif
                                </div>

                                <!-- Testing Documents -->
                                <div class="ef-tab-content" data-tab-content="testing-docs" role="tabpanel">
                                    @if (count($documentsByCategory['testing'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['testing'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No testing documents available</p>
                                    @endif
                                </div>

                                <!-- Records Documents -->
                                <div class="ef-tab-content" data-tab-content="records-docs" role="tabpanel">
                                    @if (count($documentsByCategory['records'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['records'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No record documents available</p>
                                    @endif
                                </div>

                                <!-- Application Forms Documents (Certification) -->
                                <div class="ef-tab-content" data-tab-content="certification-docs" role="tabpanel">
                                    @if (count($documentsByCategory['certification'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['certification'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No application forms available</p>
                                    @endif
                                </div>

                                <!-- Other Documents -->
                                <div class="ef-tab-content" data-tab-content="other-docs" role="tabpanel">
                                    @if (count($documentsByCategory['other'] ?? []) > 0)
                                        <ul class="space-y-2">
                                            @foreach ($documentsByCategory['other'] as $doc)
                                                <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                    <div class="flex items-center truncate mr-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-slate-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                        <span class="truncate"
                                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</span>
                                                        @if (isset($doc['related_info']))
                                                            <span
                                                                class="ml-2 text-xs text-slate-500">({{ $doc['related_info'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span
                                                            class="text-xs text-slate-500 mr-2">{{ $doc['size'] }}</span>
                                                        <a href="{{ $doc['url'] }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500 text-sm">No other documents available</p>
                                    @endif
                                </div>


                            </div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>

        <!-- Records Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Traffic Convictions -->
            <div class="box box--stacked flex flex-col p-6">
                <h3 class="font-medium text-lg mb-4 pb-2 border-b flex items-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5 mr-2 text-amber-500"></i>
                    Traffic Convictions
                </h3>

                @if ($driver->trafficConvictions && $driver->trafficConvictions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Location</th>
                                    <th class="px-4 py-2">Charge</th>
                                    <th class="px-4 py-2">Penalty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($driver->trafficConvictions as $conviction)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $conviction->conviction_date ? $conviction->conviction_date->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2">{{ $conviction->location }}</td>
                                        <td class="px-4 py-2">{{ $conviction->charge }}</td>
                                        <td class="px-4 py-2">{{ $conviction->penalty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-slate-500">No traffic convictions reported</p>
                @endif
            </div>

            <!-- Accidents -->
            <div class="box box--stacked flex flex-col p-6">
                <h3 class="font-medium text-lg mb-4 pb-2 border-b flex items-center">
                    <i data-lucide="Car" class="w-5 h-5 mr-2 text-red-500"></i>
                    Accidents
                </h3>

                @if ($driver->accidents && $driver->accidents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Nature</th>
                                    <th class="px-4 py-2">Injuries</th>
                                    <th class="px-4 py-2">Fatalities</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($driver->accidents as $accident)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $accident->accident_date ? $accident->accident_date->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2">{{ $accident->nature_of_accident }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-medium {{ $accident->had_injuries ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $accident->had_injuries ? $accident->number_of_injuries : 'None' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-medium {{ $accident->had_fatalities ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $accident->had_fatalities ? $accident->number_of_fatalities : 'None' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-slate-500">No accidents reported</p>
                @endif
            </div>
        </div>
    </div>

    <!-- JavaScript for Tab Functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('[role="tab"]');
            const tabPanels = document.querySelectorAll('[role="tabpanel"]');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Deactivate all tabs
                    tabButtons.forEach(btn => {
                        btn.classList.remove('border-blue-600');
                        btn.classList.add('border-transparent');
                        btn.setAttribute('aria-selected', 'false');
                    });

                    // Hide all panels
                    tabPanels.forEach(panel => {
                        panel.classList.add('hidden');
                    });

                    // Activate current tab
                    button.classList.remove('border-transparent');
                    button.classList.add('border-blue-600');
                    button.setAttribute('aria-selected', 'true');

                    // Show current panel
                    const panelId = button.getAttribute('data-tabs-target').substring(1);
                    const panel = document.getElementById(panelId);
                    panel.classList.remove('hidden');
                });
            });
        });
    </script>
@endsection

@push('scripts')
    <script>
        // Script para manejar las pestañas de documentos categorizados
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('#license-tab, #medical-tab, #record-tab, #other-tab');
            const tabContents = document.querySelectorAll(
            '#license-docs, #medical-docs, #record-docs, #other-docs');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const target = document.querySelector(button.dataset.tabsTarget);

                    // Ocultar todos los contenidos de pestañas
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        content.classList.remove('block');
                    });

                    // Mostrar el contenido de la pestaña seleccionada
                    target.classList.remove('hidden');
                    target.classList.add('block');

                    // Actualizar estilos de los botones
                    tabButtons.forEach(btn => {
                        btn.classList.remove('border-blue-600');
                        btn.classList.add('border-transparent', 'hover:border-gray-300');
                        btn.setAttribute('aria-selected', 'false');
                    });

                    button.classList.remove('border-transparent', 'hover:border-gray-300');
                    button.classList.add('border-blue-600');
                    button.setAttribute('aria-selected', 'true');
                });
            });
        });
    </script>
@endpush
