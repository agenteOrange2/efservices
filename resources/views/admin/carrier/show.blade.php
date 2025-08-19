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
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">IFTA Account</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->ifta_account ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Business Type</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->business_type ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Years in Business</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->years_in_business ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fleet Size</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $carrier->fleet_size ?? 'N/A' }}</p>
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
                            @elseif ($carrier->status == 0)
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-red-600 mt-1">
                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                    Inactive
                                </p>
                            @elseif ($carrier->status == 2)
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-yellow-600 mt-1">
                                    <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></span>
                                    Pending
                                </p>
                            @elseif ($carrier->status == 3)
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 mt-1">
                                    <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                                    Pending Validation
                                </p>
                            @else
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-600 mt-1">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    Unknown
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
                    <nav class="flex space-x-2 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                        <button id="tab-users"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-blue-600 text-blue-600 hover:text-blue-800 hover:border-blue-800 whitespace-nowrap flex items-center gap-2 active transition-all duration-200 ease-in-out"
                            data-target="#tab-content-users" aria-controls="tab-content-users"
                            aria-selected="true">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Users</span>
                            <span class="sm:hidden">Users</span>
                            @if($userCarriers && $userCarriers->count() > 0)
                                <span class="ml-1 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $userCarriers->count() }}</span>
                            @endif
                        </button>
                        <button id="tab-drivers"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-600 whitespace-nowrap flex items-center gap-2 transition-all duration-200 ease-in-out"
                            data-target="#tab-content-drivers" aria-controls="tab-content-drivers"
                            aria-selected="false">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Drivers</span>
                            <span class="sm:hidden">Drivers</span>
                            @if($drivers && $drivers->count() > 0)
                                <span class="ml-1 px-2 py-0.5 text-xs bg-orange-100 text-orange-800 rounded-full">{{ $drivers->count() }}</span>
                            @endif
                        </button>
                        <button id="tab-documents"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-600 whitespace-nowrap flex items-center gap-2 transition-all duration-200 ease-in-out"
                            data-target="#tab-content-documents"
                            aria-controls="tab-content-documents" aria-selected="false">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Documents</span>
                            <span class="sm:hidden">Docs</span>
                            @if($documents && $documents->count() > 0)
                                <span class="ml-1 px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full">{{ $documents->count() }}</span>
                            @endif
                        </button>
                        <button id="tab-banking"
                            class="tab-button px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-600 whitespace-nowrap flex items-center gap-2 transition-all duration-200 ease-in-out"
                            data-target="#tab-content-banking"
                            aria-controls="tab-content-banking" aria-selected="false">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Banking</span>
                            <span class="sm:hidden">Bank</span>
                            @if($carrier->bankingDetails)
                                <span class="ml-1 w-2 h-2 rounded-full {{ $carrier->bankingDetails->status === 'approved' ? 'bg-green-400' : ($carrier->bankingDetails->status === 'rejected' ? 'bg-red-400' : 'bg-yellow-400') }}"></span>
                            @endif
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="tab-content mt-6">
                    <!-- Loading Indicator -->
                    <div id="tab-loading" class="hidden flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="ml-2 text-gray-600">Loading...</span>
                    </div>

                    <!-- Tab Usuarios -->
                    <div id="tab-content-users" class="tab-pane active transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-users">
                        @if($userCarriers && $userCarriers->count() > 0)
                            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                                <table class="w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th scope="col" class="hidden md:table-cell px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th scope="col" class="hidden lg:table-cell px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($userCarriers as $user)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-blue-600">{{ substr($user->user->name ?? 'N/A', 0, 1) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="text-sm font-medium text-gray-900">{{ $user->user->name ?? 'N/A' }}</div>
                                                            <div class="text-sm text-gray-500 md:hidden">{{ $user->user->email ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="hidden md:table-cell px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->user->email ?? 'N/A' }}</td>
                                                <td class="hidden lg:table-cell px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $user->user->getRoleNames()->first() ?? 'No Role' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                    @if ($user->status == 1)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route('admin.carrier.user_carriers.edit', ['carrier' => $carrier, 'userCarrierDetails' => $user]) }}"
                                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 flex items-center gap-1 tooltip"
                                                            title="Edit user details">
                                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                                            <span class="hidden sm:inline">Edit</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="text-gray-400 mb-4">
                                    <i data-lucide="users" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Users Found</h3>
                                <p class="text-gray-500 mb-4">This carrier doesn't have any associated users yet.</p>
                                <a href="{{ route('admin.carrier.user_carriers.create', $carrier) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-150">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                    Add First User
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Tab Conductores -->
                    <div id="tab-content-drivers" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-drivers">
                        @if(isset($drivers) && $drivers && $drivers->count() > 0)
                            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                                    <h3 class="text-lg font-medium text-gray-900">Drivers Management</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $drivers->count() }} {{ $drivers->count() === 1 ? 'Driver' : 'Drivers' }}
                                    </span>
                                </div>
                                <a href="{{ route('admin.carrier.user_drivers.create', $carrier) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                    Add Driver
                                </a>
                            </div>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver Info</th>
                                            <th scope="col" class="hidden lg:table-cell px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                            <th scope="col" class="hidden md:table-cell px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($drivers as $driver)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            @if($driver->hasMedia('driver_photo'))
                                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $driver->getFirstMediaUrl('driver_photo') }}" alt="{{ $driver->user->name ?? 'Driver' }}">
                                                            @else
                                                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-orange-400 to-orange-600 flex items-center justify-center">
                                                                    <span class="text-sm font-medium text-white">{{ substr($driver->user->name ?? 'N', 0, 1) }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $driver->user->name ?? 'N/A' }}</div>
                                                            <div class="text-sm text-gray-500">
                                                                @if($driver->user && $driver->user->email)
                                                                    <span class="lg:hidden">{{ $driver->user->email }}</span>
                                                                @endif
                                                                <span class="md:hidden">{{ $driver->license_number ?? 'No License' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="hidden lg:table-cell px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <div>
                                                        <div>{{ $driver->user->email ?? 'N/A' }}</div>
                                                        @if($driver->phone)
                                                            <div class="text-xs text-gray-400">{{ $driver->phone }}</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="hidden md:table-cell px-3 sm:px-6 py-4 whitespace-nowrap">
                                                    @if($driver->license_number)
                                                        <div class="text-sm text-gray-900">{{ $driver->license_number }}</div>
                                                        @if($driver->license_state)
                                                            <div class="text-xs text-gray-500">{{ $driver->license_state }}</div>
                                                        @endif
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                                            No License
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                    @if ($driver->status == 1)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                            Active
                                                        </span>
                                                    @elseif($driver->status == 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                            Inactive
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $carrier, 'userDriverDetail' => $driver]) }}"
                                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 flex items-center gap-1 tooltip"
                                                            title="Edit driver details">
                                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                                            <span class="hidden sm:inline">Edit</span>
                                                        </a>
                                                        <button type="button" onclick="viewDriverDetails({{ $driver->id }})"
                                                            class="text-green-600 hover:text-green-900 transition-colors duration-150 flex items-center gap-1 tooltip"
                                                            title="View driver details">
                                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                                            <span class="hidden sm:inline">View</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                                <div class="text-center py-12">
                                    <div class="mx-auto h-20 w-20 text-gray-300 mb-6">
                                        <i data-lucide="users" class="w-20 h-20 mx-auto"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay conductores registrados</h3>
                                    <p class="text-gray-500 mb-8 max-w-md mx-auto">Este transportista aún no tiene conductores registrados. Agrega el primer conductor para comenzar.</p>
                                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                        <a href="{{ route('admin.carrier.user_drivers.create', $carrier) }}"
                                           class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150 transform hover:scale-105">
                                            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                                            Agregar Primer Conductor
                                        </a>
                                        <button class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                            <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                                            Ver Requisitos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Tab Documentos -->
                    <div id="tab-content-documents" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel"
                        aria-labelledby="tab-documents">
                        <!-- Upload Documents Button -->
                        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                                <h3 class="text-lg font-medium text-gray-900">Document Management</h3>
                            </div>
                            <a href="{{ route('admin.carrier.documents', $carrier->slug) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                                Upload Documents
                            </a>
                        </div>
                @if($documents && $documents->count() > 0)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-blue-100 rounded-lg">
                                                <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">Documentos Cargados</h3>
                                                <p class="text-sm text-gray-600">{{ $documents->count() }} {{ $documents->count() === 1 ? 'documento' : 'documentos' }} en total</p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                            Documentos Disponibles
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                                <th scope="col" class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Documento</th>
                                                <th scope="col" class="hidden md:table-cell px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Expiración</th>
                                                <th scope="col" class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                <th scope="col" class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                     @foreach ($documents as $document)
                                         <tr class="hover:bg-gray-50 transition-colors duration-150">
                                             <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                             <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                 <div class="flex items-center">
                                                     <div class="flex-shrink-0 h-8 w-8">
                                                         <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                             <i data-lucide="file-text" class="w-4 h-4 text-blue-600"></i>
                                                         </div>
                                                     </div>
                                                     <div class="ml-3">
                                                         <div class="text-sm font-medium text-gray-900">{{ $document->documentType->name ?? 'Unknown Type' }}</div>
                                                         <div class="text-sm text-gray-500 md:hidden">
                                                             {{ $document->expiration_date ? date('M d, Y', strtotime($document->expiration_date)) : 'No expiration' }}
                                                         </div>
                                                     </div>
                                                 </div>
                                             </td>
                                             <td class="hidden md:table-cell px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                 @if($document->expiration_date)
                                                     @php
                                                         $expirationDate = \Carbon\Carbon::parse($document->expiration_date);
                                                         $isExpired = $expirationDate->isPast();
                                                         $isExpiringSoon = $expirationDate->diffInDays(now()) <= 30 && !$isExpired;
                                                     @endphp
                                                     <div class="flex items-center">
                                                         <span class="text-sm {{ $isExpired ? 'text-red-600' : ($isExpiringSoon ? 'text-yellow-600' : 'text-gray-900') }}">
                                                             {{ $expirationDate->format('M d, Y') }}
                                                         </span>
                                                         @if($isExpired)
                                                             <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                 Expired
                                                             </span>
                                                         @elseif($isExpiringSoon)
                                                             <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                 Expiring Soon
                                                             </span>
                                                         @endif
                                                     </div>
                                                 @else
                                                     <span class="text-sm text-gray-500">No expiration</span>
                                                 @endif
                                             </td>
                                             <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                 @if ($document->status == 'approved')
                                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                         <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                         Approved
                                                     </span>
                                                 @elseif($document->status == 'rejected')
                                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                         <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                         Rejected
                                                     </span>
                                                 @else
                                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                         <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                         Pending
                                                     </span>
                                                 @endif
                                             </td>
                                             <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                 <div class="flex items-center space-x-2">
                                                     @if ($document->hasMedia('carrier_documents'))
                                                         <div class="flex items-center gap-2">
                                                             <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}"
                                                                 class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-150"
                                                                 target="_blank" title="Ver documento">
                                                                 <i data-lucide="eye" class="w-3 h-3 mr-1"></i>
                                                                 Ver
                                                             </a>
                                                             <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}"
                                                                 class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-green-700 bg-green-100 hover:bg-green-200 transition-colors duration-150"
                                                                 download title="Descargar documento">
                                                                 <i data-lucide="download" class="w-3 h-3 mr-1"></i>
                                                                 Descargar
                                                             </a>
                                                         </div>
                                                     @else
                                                         <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                             <i data-lucide="file-x" class="w-3 h-3 mr-1"></i>
                                                             Sin Archivo
                                                         </span>
                                                     @endif
                                                 </div>
                                             </td>
                                         </tr>
                                     @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <span>Total: {{ $documents->count() }} documentos</span>
                                        <a href="{{ route('admin.carrier.documents', $carrier->slug) }}" 
                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                            <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                            Agregar más documentos
                                        </a>
                                    </div>
                                </div>
                            </div>
                         @else
                             <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                                 <div class="text-center py-12">
                                     <div class="mx-auto h-20 w-20 text-gray-300 mb-6">
                                         <i data-lucide="file-text" class="w-20 h-20 mx-auto"></i>
                                     </div>
                                     <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay documentos cargados</h3>
                                     <p class="text-gray-500 mb-8 max-w-md mx-auto">Este transportista aún no ha subido ningún documento. Los documentos son necesarios para la verificación y aprobación.</p>
                                     <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 max-w-lg mx-auto">
                                         <div class="flex items-center">
                                             <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 mr-2"></i>
                                             <p class="text-sm text-yellow-800 font-medium">Documentos requeridos para activación</p>
                                         </div>
                                     </div>
                                     <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                         <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                            class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150 transform hover:scale-105">
                                             <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                                             Subir Primer Documento
                                         </a>
                                         <button class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                             <i data-lucide="list" class="w-5 h-5 mr-2"></i>
                                             Ver Lista de Documentos
                                         </button>
                                     </div>
                                 </div>
                             </div>
                         @endif
                     </div>
                </div>
            </div>

            <!-- Banking Info Tab -->
            <div id="tab-content-banking" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-banking">
                @if(isset($carrier->bankingDetails) && $carrier->bankingDetails)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Header with Status -->
                        <div class="bg-gradient-to-r {{ $carrier->bankingDetails->status === 'approved' ? 'from-green-50 to-green-100 border-green-200' : ($carrier->bankingDetails->status === 'rejected' ? 'from-red-50 to-red-100 border-red-200' : 'from-yellow-50 to-yellow-100 border-yellow-200') }} px-6 py-4 border-b">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 {{ $carrier->bankingDetails->status === 'approved' ? 'bg-green-100' : ($carrier->bankingDetails->status === 'rejected' ? 'bg-red-100' : 'bg-yellow-100') }} rounded-lg">
                                        <i data-lucide="credit-card" class="w-6 h-6 {{ $carrier->bankingDetails->status === 'approved' ? 'text-green-600' : ($carrier->bankingDetails->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-900">Banking Information</h2>
                                        <p class="text-sm text-gray-600">Account details and verification status</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($carrier->bankingDetails->status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                            Verified & Approved
                                        </span>
                                    @elseif($carrier->bankingDetails->status === 'rejected')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                                            <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                                            Pending Verification
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons for Pending Status -->
                        @if($carrier->bankingDetails->status === 'pending')
                            <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600"></i>
                                        <span class="text-sm font-medium text-yellow-800">This banking information requires your review and approval.</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <form method="POST" action="{{ route('admin.carrier.banking.approve', $carrier->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                                                <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                                                Approve Account
                                            </button>
                                        </form>
                                        <button type="button" onclick="openRejectModal()" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150">
                                            <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                            Reject Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Rejection Reason -->
                        @if($carrier->bankingDetails->status === 'rejected' && $carrier->bankingDetails->rejection_reason)
                            <div class="bg-red-50 px-6 py-4 border-b border-red-200">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-medium text-red-800 mb-1">Rejection Reason:</p>
                                        <p class="text-sm text-red-700">{{ $carrier->bankingDetails->rejection_reason }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Banking Details -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Account Information -->
                                <div class="space-y-6">
                                    <div class="border-b border-gray-200 pb-4">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                                            <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                                            Account Holder Information
                                        </h3>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                <i data-lucide="user" class="w-4 h-4 text-gray-500"></i>
                                                <span class="text-gray-900 font-medium">{{ $carrier->bankingDetails->account_holder_name ?? 'N/A' }}</span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Business/Carrier Name</label>
                                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                <i data-lucide="building" class="w-4 h-4 text-gray-500"></i>
                                                <span class="text-gray-900 font-medium">{{ $carrier->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>

                                        @if($carrier->email)
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                    <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                                                    <span class="text-gray-900">{{ $carrier->email }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Banking Details -->
                                <div class="space-y-6">
                                    <div class="border-b border-gray-200 pb-4">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                                            <i data-lucide="credit-card" class="w-5 h-5 text-blue-600"></i>
                                            Banking Details
                                        </h3>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                <i data-lucide="hash" class="w-4 h-4 text-gray-500"></i>
                                                <span class="text-gray-900 font-mono tracking-wider text-lg">
                                                @if($carrier->bankingDetails->account_number && strlen($carrier->bankingDetails->account_number) >= 8)
                                                    <span class="text-blue-600">{{ substr($carrier->bankingDetails->account_number, 0, 4) }}</span><span class="text-gray-400">••••••••</span><span class="text-blue-600 font-bold">{{ substr($carrier->bankingDetails->account_number, -4) }}</span>
                                                @elseif($carrier->bankingDetails->account_number)
                                                    <span class="text-gray-400">••••</span><span class="text-blue-600 font-bold">{{ substr($carrier->bankingDetails->account_number, -4) }}</span>
                                                @else
                                                    <span class="text-gray-400">No disponible</span>
                                                @endif
                                            </span>
                                                <span class="ml-auto text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">Protected</span>
                                            </div>
                                        </div>

                                        @if($carrier->bankingDetails->routing_number)
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Routing Number</label>
                                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                    <i data-lucide="map-pin" class="w-4 h-4 text-gray-500"></i>
                                                    <span class="text-gray-900 font-mono">{{ $carrier->bankingDetails->routing_number }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                <i data-lucide="globe" class="w-4 h-4 text-gray-500"></i>
                                                <span class="text-gray-900">
                                                    @if($carrier->bankingDetails->country_code === 'US')
                                                        🇺🇸 United States
                                                    @elseif($carrier->bankingDetails->country_code === 'CA')
                                                        🇨🇦 Canada
                                                    @elseif($carrier->bankingDetails->country_code === 'MX')
                                                        🇲🇽 Mexico
                                                    @else
                                                        {{ $carrier->bankingDetails->country_code }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Submission Date</label>
                                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                <i data-lucide="calendar" class="w-4 h-4 text-gray-500"></i>
                                                <div>
                                                    <span class="text-gray-900">{{ $carrier->bankingDetails->created_at->format('M d, Y') }}</span>
                                                    <span class="text-gray-500 text-sm ml-2">at {{ $carrier->bankingDetails->created_at->format('H:i') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($carrier->bankingDetails->updated_at && $carrier->bankingDetails->updated_at != $carrier->bankingDetails->created_at)
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Updated</label>
                                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                    <i data-lucide="clock" class="w-4 h-4 text-gray-500"></i>
                                                    <div>
                                                        <span class="text-gray-900">{{ $carrier->bankingDetails->updated_at->format('M d, Y') }}</span>
                                                        <span class="text-gray-500 text-sm ml-2">at {{ $carrier->bankingDetails->updated_at->format('H:i') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                        <div class="text-center py-12">
                            <div class="mx-auto h-20 w-20 text-gray-300 mb-6">
                                <i data-lucide="credit-card" class="w-20 h-20 mx-auto"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay información bancaria</h3>
                            <p class="text-gray-500 mb-6 max-w-md mx-auto">Este transportista aún no ha proporcionado información bancaria. Esta información es necesaria para el procesamiento de pagos.</p>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 max-w-lg mx-auto">
                                <div class="flex items-center justify-center">
                                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mr-2"></i>
                                    <p class="text-sm text-red-800 font-medium">Información bancaria requerida para pagos</p>
                                </div>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 max-w-lg mx-auto">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-medium mb-1">¿Cómo agregar información bancaria?</p>
                                        <p>El transportista debe proporcionar los detalles bancarios a través de su portal de usuario.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
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
                                tabPane.classList.remove('opacity-100');
                                tabPane.classList.add('opacity-0');
                            });

                            // Mostrar el contenido de la pestaña seleccionada
                            const targetPane = document.querySelector(target);
                            if (targetPane) {
                                targetPane.classList.add('active');
                                targetPane.classList.remove('hidden');
                                targetPane.classList.add('opacity-100');
                                targetPane.classList.remove('opacity-0');
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
