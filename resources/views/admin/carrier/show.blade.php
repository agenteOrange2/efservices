@extends('../themes/' . $activeTheme)
@section('title', 'Carrier Details')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Carrier Details', 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i data-lucide="truck" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Carrier Details</h1>
                    <p class="text-gray-600">{{ $carrier->name }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.carrier.index') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to list
                </a>
                <a href="{{ route('admin.carrier.edit', $carrier) }}"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                    Edit Carrier
                </a>
            </div>
        </div>
    </div>

    .
    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- Columna Izquierda - Información Principal -->
        <div class="col-span-12 lg:col-span-4">
            <div class="box box--stacked flex flex-col p-6 h-fit">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Main Information</h2>
                </div>

                <!-- Logo Section -->
                <div class="flex justify-center mb-6">
                    @if ($carrier->hasMedia('logo_carrier'))
                        <img src="{{ $carrier->getFirstMediaUrl('logo_carrier') }}" alt="Logo"
                            class="w-full h-32 object-contain border-2 border-dashed border-blue-200 rounded-xl p-2">
                    @else
                        <div
                            class="w-32 h-32 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center border-2 border-dashed border-blue-200">
                            <i data-lucide="image" class="w-12 h-12 text-blue-400"></i>
                        </div>
                    @endif
                </div>

                <!-- Information Grid -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Name</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->name }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Address</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->address }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">State</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->state }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Zipcode</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->zipcode }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">EIN Number</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->ein_number }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">DOT Number</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->dot_number ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">MC Number</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->mc_number ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">DOT State</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->state_dot ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Plan</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                {{ $carrier->membership->name ?? 'No Plan' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</label>
                            @if ($carrier->status == 1)
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-green-600 mt-1">
                                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                                    Active
                                </p>
                            @else
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-yellow-600 mt-1">
                                    <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></span>
                                    Pending
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Central - Estadísticas y Pestañas -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
            <!-- Estadísticas Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total de usuarios -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Users</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $userCarriers->count() }}</h3>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Total de conductores -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Drivers</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $drivers->count() }}</h3>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <i data-lucide="user-check" class="w-6 h-6 text-orange-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Total de documentos -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Documents</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $documents->count() }}</h3>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i data-lucide="file-text" class="w-6 h-6 text-yellow-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Documentación -->
            <div class="box box--stacked flex flex-col p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-blue-600"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Document Status</h2>
                </div>

                <div class="space-y-6">
                    <!-- Documentos Aprobados -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <p class="text-sm font-medium text-gray-700"> Approved Documents</p>
                            <p class="text-sm font-medium text-gray-700">{{ $approvedDocuments->count() }} de
                                {{ $documents->count() }}</p>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-green-500 h-2.5 rounded-full"
                                style="width: {{ $documents->count() > 0 ? ($approvedDocuments->count() / $documents->count()) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>

                    <!-- Documentos Pendientes -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <p class="text-sm font-medium text-gray-700">Pending Documents</p>
                            <p class="text-sm font-medium text-gray-700">{{ $pendingDocuments->count() }} de
                                {{ $documents->count() }}</p>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-500 h-2.5 rounded-full"
                                style="width: {{ $documents->count() > 0 ? ($pendingDocuments->count() / $documents->count()) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>

                    <!-- Documentos Rechazados -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <p class="text-sm font-medium text-gray-700">Rejected Documents</p>
                            <p class="text-sm font-medium text-gray-700">{{ $rejectedDocuments->count() }} de
                                {{ $documents->count() }}</p>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-red-500 h-2.5 rounded-full"
                                style="width: {{ $documents->count() > 0 ? ($rejectedDocuments->count() / $documents->count()) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestañas -->
            <div class="box box--stacked flex flex-col p-6 mt-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="layout-grid" class="w-5 h-5 text-blue-600"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Detailed Information</h2>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-4 overflow-x-auto" aria-label="Tabs">
                        <button id="tab-users"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-blue-600 text-blue-600 hover:text-blue-800 hover:border-blue-800 whitespace-nowrap flex items-center gap-2 active"
                            data-tw-toggle="tab" data-target="#tab-content-users" aria-controls="tab-content-users"
                            aria-selected="true">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span>Users</span>
                        </button>
                        <button id="tab-drivers"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-600 whitespace-nowrap flex items-center gap-2"
                            data-tw-toggle="tab" data-target="#tab-content-drivers" aria-controls="tab-content-drivers"
                            aria-selected="false">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            <span>Drivers</span>
                        </button>
                        <button id="tab-documents"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-600 whitespace-nowrap flex items-center gap-2"
                            data-tw-toggle="tab" data-target="#tab-content-documents"
                            aria-controls="tab-content-documents" aria-selected="false">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            <span>Documents</span>
                        </button>
                        <button id="tab-banking"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-600 whitespace-nowrap flex items-center gap-2"
                            data-tw-toggle="tab" data-target="#tab-content-banking"
                            aria-controls="tab-content-banking" aria-selected="false">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                            <span>Banking Info</span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="tab-content mt-6">
                    <!-- Tab Usuarios -->
                    <div id="tab-content-users" class="tab-pane active" role="tabpanel" aria-labelledby="tab-users">
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($userCarriers as $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $user->user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->user->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->user->getRoleNames()->first() ?? 'Sin rol' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($user->status == 1)
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                        Active
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.carrier.user_carriers.edit', ['carrier' => $carrier, 'userCarrierDetails' => $user]) }}"
                                                    class="text-blue-600 hover:text-blue-900 flex items-center gap-1">
                                                    <i data-lucide="edit" class="w-4 h-4"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Conductores -->
                    <div id="tab-content-drivers" class="tab-pane hidden" role="tabpanel" aria-labelledby="tab-drivers">
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            License</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($drivers as $driver)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $driver->user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $driver->licenses->first()->license_number ?? 'Sin license' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($driver->status == 1)
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                        Active
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $carrier, 'userDriverDetail' => $driver]) }}"
                                                    class="text-blue-600 hover:text-blue-900 flex items-center gap-1">
                                                    <i data-lucide="edit" class="w-4 h-4"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Documentos -->
                    <div id="tab-content-documents" class="tab-pane hidden" role="tabpanel"
                        aria-labelledby="tab-documents">
                        <!-- Upload Documents Button -->
                        <div class="mb-4 flex justify-end">
                            <a href="{{ route('admin.carrier.documents', $carrier->slug) }}" class="btn btn-primary">
                                <i data-lucide="upload" class="w-4 h-4 mr-1"></i>
                                Upload Documents
                            </a>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Expiration Date</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($documents as $document)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $document->documentType->name ?? 'Sin tipo' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $document->expiration_date ? date('m/d/Y', strtotime($document->expiration_date)) : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($document->status == 'approved')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                        Approved
                                                    </span>
                                                @elseif($document->status == 'rejected')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                        Rejected
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    @if ($document->hasMedia('carrier_documents'))
                                                        <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}"
                                                            class="text-blue-600 hover:text-blue-900" target="_blank">
                                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                                        </a>
                                                        <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}"
                                                            class="text-green-600 hover:text-green-900" download>
                                                            <i data-lucide="download" class="w-5 h-5"></i>
                                                        </a>
                                                    @else
                                                        <p>Empty</p>
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banking Info Tab -->
            <div id="banking-info" class="tab-pane hidden">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-5 h-5 text-blue-600"></i>
                            <h2 class="text-lg font-semibold text-gray-900">Banking Information</h2>
                        </div>
                        @if($carrier->bankingDetails && $carrier->bankingDetails->status === 'pending')
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.carrier.banking.approve', $carrier->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i data-lucide="check" class="w-4 h-4 mr-1"></i>
                                        Approve
                                    </button>
                                </form>
                                <button type="button" onclick="openRejectModal()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                                    Reject
                                </button>
                            </div>
                        @endif
                        
                        @if($carrier->bankingDetails && $carrier->bankingDetails->status === 'rejected' && $carrier->bankingDetails->rejection_reason)
                            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm font-medium text-red-800 mb-1">Rejection Reason:</p>
                                <p class="text-sm text-red-700">{{ $carrier->bankingDetails->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>

                    @if($carrier->bankingDetails)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Holder Name</label>
                                    <div class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                                        {{ $carrier->bankingDetails->account_holder_name }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                    <div class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                                        {{ substr($carrier->bankingDetails->account_number, 0, 4) }}****{{ substr($carrier->bankingDetails->account_number, -4) }}
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <div class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                                        {{ $carrier->bankingDetails->country_code === 'US' ? 'United States' : $carrier->bankingDetails->country_code }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <div class="px-3 py-2">
                                        @if($carrier->bankingDetails->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                Approved
                                            </span>
                                        @elseif($carrier->bankingDetails->status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                Pending Validation
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Submitted Date</label>
                                    <div class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                                        {{ $carrier->bankingDetails->created_at->format('M d, Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500">
                                <i data-lucide="credit-card" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">No Banking Information</p>
                                <p class="text-sm">This carrier has not submitted banking details yet.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- <!-- Columna Derecha - Documentos Faltantes -->
    <div class="col-span-12 lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-fit">
            <div class="flex items-center gap-2 mb-6">
                <i data-lucide="file-plus" class="w-5 h-5 text-blue-600"></i>
                <h2 class="text-lg font-semibold text-gray-900">Missing Documents</h2>
            </div>
            
            @if ($missingDocumentTypes->count() > 0)
                <div class="grid gap-3">
                                @foreach ($missingDocumentTypes as $documentType)
                                    <div class="flex items-center p-3 border rounded-md">
                                        <div class="mr-auto">
                                            <div class="font-medium">{{ $documentType->name }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">Required: {{ $documentType->is_required ? 'Yes' : 'No' }}</div>
                                        </div>
                                        <a href="{{ route('admin.carrier.documents', $carrier->slug) }}" class="btn btn-sm btn-primary">
                                            <i data-lucide="upload" class="w-4 h-4 mr-1"></i> Upload
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-success font-medium">¡All document types have been registered!</div>
                            </div>
                        @endif
                        
                        <div class="mt-4">
                            <a href="{{ route('admin.carrier.documents', $carrier->slug) }}" class="btn btn-outline-primary w-full">
                                <i data-lucide="file-plus" class="w-4 h-4 mr-2"></i> Manage Documents
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
        </div>



        @push('scripts')
            <script>
                // Inicializar los íconos de Lucide, las pestañas y el modal después de que el DOM esté listo
                document.addEventListener("DOMContentLoaded", function() {
                    // Inicializar los íconos de Lucide
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    // Inicializar las pestañas
                    const tabButtons = document.querySelectorAll('.tab-button');

                    if (tabButtons.length > 0) {
                        // Función para activar una pestaña
                        function activateTab(tabButton) {
                            // Desactivar todas las pestañas
                            tabButtons.forEach(function(btn) {
                                btn.classList.remove('active');
                                btn.classList.remove('border-blue-600');
                                btn.classList.remove('text-blue-600');
                                btn.classList.add('border-transparent');
                                btn.classList.add('text-gray-500');
                                btn.setAttribute('aria-selected', 'false');
                            });

                            // Activar la pestaña seleccionada
                            tabButton.classList.add('active');
                            tabButton.classList.add('border-blue-600');
                            tabButton.classList.add('text-blue-600');
                            tabButton.classList.remove('border-transparent');
                            tabButton.classList.remove('text-gray-500');
                            tabButton.setAttribute('aria-selected', 'true');

                            // Obtener el target del tab
                            const target = tabButton.getAttribute('data-target');

                            // Ocultar todos los contenidos de las pestañas
                            document.querySelectorAll('.tab-pane').forEach(function(tabPane) {
                                tabPane.classList.remove('active');
                                tabPane.classList.add('hidden');
                            });

                            // Mostrar el contenido de la pestaña seleccionada
                            const targetPane = document.querySelector(target);
                            if (targetPane) {
                                targetPane.classList.add('active');
                                targetPane.classList.remove('hidden');
                            }
                        }

                        // Agregar evento click a cada pestaña
                        tabButtons.forEach(function(tabButton) {
                            tabButton.addEventListener('click', function(event) {
                                event.preventDefault();
                                activateTab(this);
                            });
                        });

                        // Activar la primera pestaña por defecto (Usuarios)
                        const firstTab = document.querySelector('#tab-users');
                        if (firstTab) {
                            activateTab(firstTab);
                        }
                    }

                    const deleteDocumentBtns = document.querySelectorAll('.delete-document-btn');

                    // Eliminar documento
                    deleteDocumentBtns.forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            const documentId = this.getAttribute('data-document-id');
                            if (confirm('Are you sure you want to delete this document?')) {
                                // Crear un formulario para enviar la solicitud DELETE
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action =
                                    '{{ route('admin.carriers.documents.index', ['carrier' => $carrier->id]) }}/' +
                                    documentId;
                                form.style.display = 'none';

                                const csrfToken = document.createElement('input');
                                csrfToken.type = 'hidden';
                                csrfToken.name = '_token';
                                csrfToken.value = '{{ csrf_token() }}';

                                const methodField = document.createElement('input');
                                methodField.type = 'hidden';
                                methodField.name = '_method';
                                methodField.value = 'DELETE';

                                form.appendChild(csrfToken);
                                form.appendChild(methodField);
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                    });

                    // Inicializar los iconos de Lucide
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
                
                // Función para abrir el modal de rechazo
                function openRejectModal() {
                    document.getElementById('rejectModal').classList.remove('hidden');
                }
                
                // Función para cerrar el modal de rechazo
                function closeRejectModal() {
                    document.getElementById('rejectModal').classList.add('hidden');
                    document.getElementById('rejectionReason').value = '';
                }
            </script>
        @endpush
        
        <!-- Modal de Rechazo -->
        <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Reject Banking Information</h3>
                        <button type="button" onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    
                    <form action="{{ route('admin.carrier.banking.reject', $carrier) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Rejection <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="rejectionReason" 
                                name="rejection_reason" 
                                rows="4" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                                placeholder="Please provide a detailed reason for rejecting the banking information..."
                                required
                            ></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors duration-200">
                                <i data-lucide="x" class="w-4 h-4 mr-1 inline"></i>
                                Reject Banking Info
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    @endsection
