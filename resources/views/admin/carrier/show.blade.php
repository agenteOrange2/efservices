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

<!-- Professional Breadcrumbs using x-base component -->
<div class="mb-6">
    <x-base.breadcrumb :links="$breadcrumbLinks" />
</div>

<!-- Professional Header with x-base components -->
<div class="box box--stacked p-8 mb-8">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-primary/10 rounded-xl border border-primary/20">
                <x-base.lucide class="w-8 h-8 text-primary" icon="Truck" />
            </div>
            <div>
                <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ $carrier->name }}</h1>
                <div class="flex items-center gap-3">
                    <p class="text-slate-600">Carrier Management Dashboard</p>
                    @if ($carrier->status == 1)
                    <x-base.badge variant="success" class="gap-1.5">
                        <span class="w-2 h-2 bg-success rounded-full"></span>
                        Active
                    </x-base.badge>
                    @elseif ($carrier->status == 0)
                    <x-base.badge variant="danger" class="gap-1.5">
                        <span class="w-2 h-2 bg-danger rounded-full"></span>
                        Inactive
                    </x-base.badge>
                    @elseif ($carrier->status == 2)
                    <x-base.badge variant="warning" class="gap-1.5">
                        <span class="w-2 h-2 bg-warning rounded-full"></span>
                        Pending
                    </x-base.badge>
                    @elseif ($carrier->status == 3)
                    <x-base.badge variant="primary" class="gap-1.5">
                        <span class="w-2 h-2 bg-primary rounded-full"></span>
                        Pending Validation
                    </x-base.badge>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <x-base.button as="a" href="{{ route('admin.carrier.index') }}" variant="secondary" class="gap-2">
                <x-base.lucide class="w-4 h-4" icon="ArrowLeft" />
                Back to List
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.carrier.edit', $carrier) }}" variant="primary" class="gap-2">
                <x-base.lucide class="w-4 h-4" icon="Edit" />
                Edit Carrier
            </x-base.button>
        </div>
    </div>
</div>


<div class="grid grid-cols-12 gap-6 mt-5">
    <!-- Professional Information Section -->
    <div class="col-span-12 lg:col-span-6">
        <div class="box box--stacked flex flex-col p-6 h-fit">
            <div class="flex items-center gap-3 mb-6">
                <x-base.lucide class="w-5 h-5 text-primary" icon="Info" />
                <h2 class="text-lg font-semibold text-slate-800">Main Information</h2>
            </div>
            <!-- Professional Logo Section -->
            <div class="flex justify-center mb-6">
                @if ($carrier->hasMedia('logo_carrier'))
                <div class="relative group">
                    <img src="{{ $carrier->getFirstMediaUrl('logo_carrier') }}" alt="Carrier Logo"
                        class="w-32 h-32 object-contain border-2 border-dashed border-primary/20 rounded-xl p-3 bg-slate-50/50 group-hover:border-primary/40 transition-colors">
                </div>
                @else
                <div class="w-32 h-32 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl flex items-center justify-center border-2 border-dashed border-slate-200">
                    <x-base.lucide class="w-12 h-12 text-slate-400" icon="Image" />
                </div>
                @endif
            </div>

            <!-- Professional Information Grid -->
            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Name</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->name }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Address</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->address }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">State</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->state }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Zipcode</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->zipcode }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">EIN Number</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->ein_number }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">DOT Number</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->dot_number ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">MC Number</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->mc_number ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">DOT State</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->state_dot ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">IFTA Account</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->ifta_account ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Business Type</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->business_type ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Years in Business</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->years_in_business ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Fleet Size</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->fleet_size ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Plan</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $carrier->membership->name ?? 'No Plan' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Status</label>
                        <div class="mt-2">
                            @if ($carrier->status == 1)
                            <x-base.badge variant="success" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-success rounded-full"></span>
                                Active
                            </x-base.badge>
                            @elseif ($carrier->status == 0)
                            <x-base.badge variant="danger" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-danger rounded-full"></span>
                                Inactive
                            </x-base.badge>
                            @elseif ($carrier->status == 2)
                            <x-base.badge variant="warning" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-warning rounded-full"></span>
                                Pending
                            </x-base.badge>
                            @elseif ($carrier->status == 3)
                            <x-base.badge variant="primary" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-primary rounded-full"></span>
                                Pending Validation
                            </x-base.badge>
                            @else
                            <x-base.badge variant="secondary" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                Unknown
                            </x-base.badge>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Central - Estadísticas y Pestañas -->
    <div class="col-span-12 lg:col-span-6 space-y-6">
        <!-- Professional Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Users Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Users</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-primary transition-colors">{{ $userCarriers->count() }}</h3>
                    </div>
                    <div class="p-3 bg-primary/10 rounded-xl group-hover:bg-primary/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-primary" icon="Users" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="TrendingUp" />
                    Active users in system
                </div>
            </div>

            <!-- Total Drivers Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Drivers</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-warning transition-colors">{{ $drivers->count() }}</h3>
                    </div>
                    <div class="p-3 bg-warning/10 rounded-xl group-hover:bg-warning/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-warning" icon="UserCheck" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="Truck" />
                    Registered drivers
                </div>
            </div>

            <!-- Total Documents Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Documents</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-success transition-colors">{{ $documents->count() }}</h3>
                    </div>
                    <div class="p-3 bg-success/10 rounded-xl group-hover:bg-success/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-success" icon="FileText" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="Archive" />
                    Documents uploaded
                </div>
            </div>
        </div>

        <!-- Professional Document Status -->
        <div class="box box--stacked flex flex-col p-6">
            <div class="flex items-center gap-3 mb-6">
                <x-base.lucide class="w-5 h-5 text-primary" icon="BarChart2" />
                <h2 class="text-lg font-semibold text-slate-800">Document Status</h2>
            </div>

            <div class="space-y-6">
                <!-- Approved Documents -->
                <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <x-base.lucide class="w-4 h-4 text-success" icon="CheckCircle" />
                            <p class="text-sm font-medium text-slate-700">Approved Documents</p>
                        </div>
                        <x-base.badge variant="success" class="text-xs">
                            {{ $stats['approved_documents_count'] ?? 0 }} / {{ $stats['total_documents'] ?? 0 }}
                        </x-base.badge>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3">
                        <div class="bg-success h-3 rounded-full transition-all duration-300"
                            style="width: {{ ($stats['total_documents'] ?? 0) > 0 ? (($stats['approved_documents_count'] ?? 0) / ($stats['total_documents'] ?? 0)) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>

                <!-- Pending Documents -->
                <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <x-base.lucide class="w-4 h-4 text-warning" icon="Clock" />
                            <p class="text-sm font-medium text-slate-700">Pending Documents</p>
                        </div>
                        <x-base.badge variant="warning" class="text-xs">
                            {{ $stats['pending_documents_count'] ?? 0 }} / {{ $stats['total_documents'] ?? 0 }}
                        </x-base.badge>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3">
                        <div class="bg-warning h-3 rounded-full transition-all duration-300"
                            style="width: {{ ($stats['total_documents'] ?? 0) > 0 ? (($stats['pending_documents_count'] ?? 0) / ($stats['total_documents'] ?? 0)) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>

                <!-- Rejected Documents -->
                <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <x-base.lucide class="w-4 h-4 text-danger" icon="XCircle" />
                            <p class="text-sm font-medium text-slate-700">Rejected Documents</p>
                        </div>
                        <x-base.badge variant="danger" class="text-xs">
                            {{ $stats['rejected_documents_count'] ?? 0 }} / {{ $stats['total_documents'] ?? 0 }}
                        </x-base.badge>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3">
                        <div class="bg-danger h-3 rounded-full transition-all duration-300"
                            style="width: {{ ($stats['total_documents'] ?? 0) > 0 ? (($stats['rejected_documents_count'] ?? 0) / ($stats['total_documents'] ?? 0)) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  Columna tabs -->
    <div class="col-span-12 lg:col-span-12">
        <!-- Professional Tabs Section -->
        <div class="box box--stacked flex flex-col p-6 mt-6">
            <div class="flex items-center gap-3 mb-6">
                <x-base.lucide class="w-5 h-5 text-primary" icon="LayoutGrid" />
                <h2 class="text-lg font-semibold text-slate-800">Detailed Information</h2>
            </div>

            <!-- Professional Tab Navigation -->
            <div class="border-b border-slate-200">
                <nav class="flex space-x-1 overflow-x-auto scrollbar-hide flex-col md:flex-row" aria-label="Tabs">
                    <x-base.tab id="users-tab" :selected="true">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-users"
                            aria-controls="tab-content-users"
                            aria-selected="true">
                            <x-base.lucide class="w-4 h-4" icon="Users" />
                            <span class="hidden sm:inline">Users</span>
                            <span class="sm:hidden">Users</span>
                            @if(($stats['total_users'] ?? 0) > 0)
                            <x-base.badge variant="primary" class="ml-1 text-xs">{{ $stats['total_users'] ?? 0 }}</x-base.badge>
                            @endif
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="drivers-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-drivers"
                            aria-controls="tab-content-drivers"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="UserCheck" />
                            <span class="hidden sm:inline">Drivers</span>
                            <span class="sm:hidden">Drivers</span>
                            @if(($stats['total_drivers'] ?? 0) > 0)
                            <x-base.badge variant="warning" class="ml-1 text-xs">{{ $stats['total_drivers'] ?? 0 }}</x-base.badge>
                            @endif
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="documents-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-documents"
                            aria-controls="tab-content-documents"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="FileText" />
                            <span class="hidden sm:inline">Documents</span>
                            <span class="sm:hidden">Docs</span>
                            @if(($stats['total_documents'] ?? 0) > 0)
                            <x-base.badge variant="success" class="ml-1 text-xs">{{ $stats['total_documents'] ?? 0 }}</x-base.badge>
                            @endif
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="banking-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-banking"
                            aria-controls="tab-content-banking"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="CreditCard" />
                            <span class="hidden sm:inline">Banking</span>
                            <span class="sm:hidden">Bank</span>
                            @if($carrier->bankingDetails)
                            <span class="ml-1 w-2 h-2 rounded-full {{ $carrier->bankingDetails->status === 'approved' ? 'bg-success' : ($carrier->bankingDetails->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}"></span>
                            @endif
                        </x-base.tab.button>
                    </x-base.tab>
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
                    <div class="overflow-x-auto">
                        <x-base.table class="w-full">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="w-16">#</x-base.table.th>
                                    <x-base.table.th>Name</x-base.table.th>
                                    <x-base.table.th class="hidden md:table-cell">Email</x-base.table.th>
                                    <x-base.table.th class="hidden lg:table-cell">Role</x-base.table.th>
                                    <x-base.table.th>Status</x-base.table.th>
                                    <x-base.table.th class="w-24">Actions</x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach ($userCarriers as $user)
                                <x-base.table.tr>
                                    <x-base.table.td class="text-slate-500">{{ $loop->iteration }}</x-base.table.td>
                                    <x-base.table.td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                    <span class="text-sm font-semibold text-primary">{{ substr($user->user->name ?? 'N/A', 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-800">{{ $user->user->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-slate-500 md:hidden">{{ $user->user->email ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td class="hidden md:table-cell text-slate-500">{{ $user->user->email ?? 'N/A' }}</x-base.table.td>
                                    <x-base.table.td class="hidden lg:table-cell">
                                        <x-base.badge variant="secondary" class="text-xs">
                                            {{ $user->user->getRoleNames()->first() ?? 'No Role' }}
                                        </x-base.badge>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        @if ($user->status == 1)
                                        <x-base.badge variant="success" class="gap-1.5">
                                            <span class="w-1.5 h-1.5 bg-success rounded-full"></span>
                                            Active
                                        </x-base.badge>
                                        @else
                                        <x-base.badge variant="warning" class="gap-1.5">
                                            <span class="w-1.5 h-1.5 bg-warning rounded-full"></span>
                                            Pending
                                        </x-base.badge>
                                        @endif
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <div class="flex items-center gap-2">
                                            <x-base.button
                                                as="a"
                                                href="{{ route('admin.carrier.user_carriers.edit', ['carrier' => $carrier, 'userCarrierDetails' => $user]) }}"
                                                variant="primary"
                                                size="sm"
                                                class="flex items-center gap-1">
                                                <x-base.lucide class="w-3 h-3" icon="Edit" />
                                                <span class="hidden sm:inline">Edit</span>
                                            </x-base.button>
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <x-base.lucide icon="users" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Users Found</h3>
                        <p class="text-gray-500 mb-4">This carrier doesn't have any associated users yet.</p>
                        <x-base.button as="a" href="{{ route('admin.carrier.user_carriers.create', $carrier) }}" variant="primary" class="inline-flex items-center">
                            <x-base.lucide icon="plus" class="w-4 h-4 mr-2" />
                            Add First User
                        </x-base.button>
                    </div>
                    @endif
                </div>

                <!-- Tab Conductores -->
                <div id="tab-content-drivers" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-drivers">
                    @if(isset($drivers) && $drivers && $drivers->count() > 0)
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <x-base.lucide icon="users" class="w-5 h-5 text-blue-600" />
                            <h3 class="text-lg font-medium text-gray-900">Drivers Management</h3>
                            <x-base.badge variant="primary" class="text-xs">
                                {{ $drivers->count() }} {{ $drivers->count() === 1 ? 'Driver' : 'Drivers' }}
                            </x-base.badge>
                        </div>
                        <x-base.button as="a" href="{{ route('admin.carrier.user_drivers.create', $carrier) }}" variant="primary" class="inline-flex items-center">
                            <x-base.lucide icon="plus" class="w-4 h-4 mr-2" />
                            Add Driver
                        </x-base.button>
                    </div>
                    <x-base.table class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</x-base.table.th>
                                <x-base.table.th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver Info</x-base.table.th>
                                <x-base.table.th class="hidden lg:table-cell px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</x-base.table.th>
                                <x-base.table.th class="hidden md:table-cell px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License</x-base.table.th>
                                <x-base.table.th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</x-base.table.th>
                                <x-base.table.th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($drivers as $driver)
                            <x-base.table.tr class="hover:bg-gray-50 transition-colors duration-150">
                                <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</x-base.table.td>
                                <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap">
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
                                </x-base.table.td>
                                <x-base.table.td class="hidden lg:table-cell px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>
                                        <div>{{ $driver->user->email ?? 'N/A' }}</div>
                                        @if($driver->phone)
                                        <div class="text-xs text-gray-400">{{ $driver->phone }}</div>
                                        @endif
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="hidden md:table-cell px-3 sm:px-6 py-4 whitespace-nowrap">
                                    @if($driver->license_number)
                                    <div class="text-sm text-gray-900">{{ $driver->license_number }}</div>
                                    @if($driver->license_state)
                                    <div class="text-xs text-gray-500">{{ $driver->license_state }}</div>
                                    @endif
                                    @else
                                    <x-base.badge variant="secondary" class="inline-flex items-center text-xs">
                                        <x-base.lucide icon="alert-circle" class="w-3 h-3 mr-1" />
                                        No License
                                    </x-base.badge>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    @if ($driver->status == 1)
                                    <x-base.badge variant="success" class="inline-flex items-center text-xs">
                                        <span class="w-1.5 h-1.5 bg-success rounded-full mr-1.5"></span>
                                        Active
                                    </x-base.badge>
                                    @elseif($driver->status == 0)
                                    <x-base.badge variant="danger" class="inline-flex items-center text-xs">
                                        <span class="w-1.5 h-1.5 bg-danger rounded-full mr-1.5"></span>
                                        Inactive
                                    </x-base.badge>
                                    @else
                                    <x-base.badge variant="warning" class="inline-flex items-center text-xs">
                                        <span class="w-1.5 h-1.5 bg-warning rounded-full mr-1.5"></span>
                                        Pending
                                    </x-base.badge>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <x-base.button as="a" href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $carrier, 'userDriverDetail' => $driver]) }}" variant="outline-primary" size="sm" class="flex items-center gap-1" title="Edit driver details">
                                            <x-base.lucide icon="edit" class="w-4 h-4" />
                                            <span class="hidden sm:inline">Edit</span>
                                        </x-base.button>
                                        <x-base.button type="button" onclick="viewDriverDetails({{ $driver->id }})" variant="outline-success" size="sm" class="flex items-center gap-1" title="View driver details">
                                            <x-base.lucide icon="eye" class="w-4 h-4" />
                                            <span class="hidden sm:inline">View</span>
                                        </x-base.button>
                                    </div>
                                </x-base.table.td>
                            </x-base.table.tr>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                    @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                        <div class="text-center py-12">
                            <div class="mx-auto h-20 w-20 text-gray-300 mb-6">
                                <x-base.lucide icon="users" class="w-20 h-20 mx-auto" />
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay conductores registrados</h3>
                            <p class="text-gray-500 mb-8 max-w-md mx-auto">Este transportista aún no tiene conductores registrados. Agrega el primer conductor para comenzar.</p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <x-base.button as="a" href="{{ route('admin.carrier.user_drivers.create', $carrier) }}" variant="primary" class="inline-flex items-center px-6 py-3 shadow-sm text-sm font-medium transition-all duration-150 transform hover:scale-105">
                                    <x-base.lucide icon="plus" class="w-5 h-5 mr-2" />
                                    Agregar Primer Conductor
                                </x-base.button> <!-- Correcto: cierre del componente -->
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
                            <x-base.lucide icon="file-text" class="w-5 h-5 text-blue-600" />
                            <h3 class="text-lg font-medium text-gray-900">Document Management</h3>
                        </div>
                        <x-base.button as="a" href="{{ route('admin.carrier.documents', $carrier->slug) }}" variant="primary" class="inline-flex items-center">
                            <x-base.lucide icon="upload" class="w-4 h-4 mr-2" />
                            Upload Documents
                        </x-base.button>
                    </div>
                    @if($documents && $documents->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <x-base.lucide icon="file-text" class="w-5 h-5 text-blue-600" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Uploaded Documents</h3>
                                        <p class="text-sm text-gray-600">{{ $documents->count() }} {{ $documents->count() === 1 ? 'documento' : 'documentos' }} en total</p>
                                    </div>
                                </div>
                                <x-base.badge variant="primary" class="inline-flex items-center text-sm">
                                    <x-base.lucide icon="check-circle" class="w-4 h-4 mr-2" />
                                    Available Documents
                                </x-base.badge>
                            </div>
                        </div>
                        <x-base.table class="overflow-x-auto">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</x-base.table.th>
                                    <x-base.table.th class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</x-base.table.th>
                                    <x-base.table.th class="hidden md:table-cell px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiration Date</x-base.table.th>
                                    <x-base.table.th class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</x-base.table.th>
                                    <x-base.table.th class="px-3 sm:px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($documents as $document)
                                <x-base.table.tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</x-base.table.td>
                                    <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <x-base.lucide icon="file-text" class="w-4 h-4 text-blue-600" />
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $document->documentType->name ?? 'Unknown Type' }}</div>
                                                <div class="text-sm text-gray-500 md:hidden">
                                                    {{ $document->expiration_date ? date('M d, Y', strtotime($document->expiration_date)) : 'No expiration' }}
                                                </div>
                                            </div>
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td class="hidden md:table-cell px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                            <x-base.badge variant="danger" class="ml-2 text-xs">
                                                Expired
                                            </x-base.badge>
                                            @elseif($isExpiringSoon)
                                            <x-base.badge variant="warning" class="ml-2 text-xs">
                                                Expiring Soon
                                            </x-base.badge>
                                            @endif
                    </div>
                    @else
                    <span class="text-sm text-gray-500">No expiration</span>
                    @endif
                    </x-base.table.td>
                    <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        @if ($document->status == 1)
                        <x-base.badge variant="success" class="inline-flex items-center text-xs">
                            <span class="w-1.5 h-1.5 bg-success rounded-full mr-1.5"></span>
                            Approved
                        </x-base.badge>
                        @elseif($document->status == 2)
                        <x-base.badge variant="danger" class="inline-flex items-center text-xs">
                            <span class="w-1.5 h-1.5 bg-danger rounded-full mr-1.5"></span>
                            Rejected
                        </x-base.badge>
                        @else
                        <x-base.badge variant="warning" class="inline-flex items-center text-xs">
                            <span class="w-1.5 h-1.5 bg-warning rounded-full mr-1.5"></span>
                            Pending
                        </x-base.badge>
                        @endif
                    </x-base.table.td>
                    <x-base.table.td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            @if ($document->hasMedia('carrier_documents'))
                            <div class="flex items-center gap-2">
                                <x-base.button as="a" href="{{ $document->getFirstMediaUrl('carrier_documents') }}" variant="outline-primary" size="sm" target="_blank" title="View Document" class="inline-flex items-center">
                                    <x-base.lucide icon="eye" class="w-3 h-3 mr-1" />
                                    View Document
                                </x-base.button>
                                <x-base.button as="a" href="{{ $document->getFirstMediaUrl('carrier_documents') }}" variant="outline-success" size="sm" download title="Download Document" class="inline-flex items-center">
                                    <x-base.lucide icon="download" class="w-3 h-3 mr-1" />
                                    Download Document
                                </x-base.button>
                            </div>
                            @else
                            <x-base.badge variant="danger" class="inline-flex items-center text-xs">
                                <x-base.lucide icon="file-x" class="w-3 h-3 mr-1" />
                                No File 
                            </x-base.badge>
                            @endif
                        </div>
                    </x-base.table.td>
                    </x-base.table.tr>
                    @endforeach
                    </x-base.table.tbody>
                    </x-base.table>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>Total: {{ $documents->count() }} documents</span>
                            <x-base.button as="a" href="{{ route('admin.carrier.documents', $carrier->slug) }}" variant="outline-primary" size="sm" class="inline-flex items-center">
                                <x-base.lucide icon="plus" class="w-4 h-4 mr-1" />
                                Add More Documents
                            </x-base.button>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center py-12">
                        <div class="mx-auto h-20 w-20 text-gray-300 mb-6">
                            <x-base.lucide icon="file-text" class="w-20 h-20 mx-auto" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No documents uploaded</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">This carrier has not uploaded any documents yet. Documents are required for verification and approval.</p>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 max-w-lg mx-auto">
                            <div class="flex items-center">
                                <x-base.lucide icon="alert-triangle" class="w-5 h-5 text-yellow-600 mr-2" />
                                <p class="text-sm text-yellow-800 font-medium">Documents required for activation</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <x-base.button as="a" href="{{ route('admin.carrier.documents', $carrier->slug) }}" variant="primary" class="inline-flex items-center px-6 py-3 shadow-sm text-sm font-medium transition-all duration-150 transform hover:scale-105">
                                <x-base.lucide icon="upload" class="w-5 h-5 mr-2" />
                                Upload First Document
                            </x-base.button>
                            <x-base.button variant="outline-secondary" class="inline-flex items-center px-6 py-3 shadow-sm text-sm font-medium transition-colors duration-150">
                                <x-base.lucide icon="list" class="w-5 h-5 mr-2" />
                                View Document List
                            </x-base.button>
                        </div>
                    </div>
                </div>
                @endif
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
                                <x-base.lucide icon="credit-card" class="w-6 h-6 {{ $carrier->bankingDetails->status === 'approved' ? 'text-green-600' : ($carrier->bankingDetails->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Banking Information</h2>
                                <p class="text-sm text-gray-600">Account details and verification status</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($carrier->bankingDetails->status === 'approved')
                            <x-base.badge variant="success" class="inline-flex items-center px-3 py-1.5 text-sm font-medium">
                                <x-base.lucide icon="check-circle" class="w-4 h-4 mr-2" />
                                Verified & Approved
                            </x-base.badge>
                            @elseif($carrier->bankingDetails->status === 'rejected')
                            <x-base.badge variant="danger" class="inline-flex items-center px-3 py-1.5 text-sm font-medium">
                                <x-base.lucide icon="x-circle" class="w-4 h-4 mr-2" />
                                Rejected
                            </x-base.badge>
                            @else
                            <x-base.badge variant="warning" class="inline-flex items-center px-3 py-1.5 text-sm font-medium">
                                <x-base.lucide icon="clock" class="w-4 h-4 mr-2" />
                                Pending Verification
                            </x-base.badge>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons for Pending Status -->
                @if($carrier->bankingDetails->status === 'pending')
                <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-base.lucide icon="alert-triangle" class="w-5 h-5 text-yellow-600" />
                            <span class="text-sm font-medium text-yellow-800">This banking information requires your review and approval.</span>
                        </div>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.carrier.banking.approve', $carrier) }}" class="inline">
                                @csrf
                                <x-base.button type="submit" variant="success" class="inline-flex items-center px-4 py-2 text-sm font-medium transition-colors duration-150">
                                    <x-base.lucide icon="check" class="w-4 h-4 mr-2" />
                                    Approve Account
                                </x-base.button>
                            </form>
                            <x-base.button type="button" onclick="openRejectModal()" variant="danger" class="inline-flex items-center px-4 py-2 text-sm font-medium transition-colors duration-150">
                                <x-base.lucide icon="x" class="w-4 h-4 mr-2" />
                                Reject Account
                            </x-base.button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Rejection Reason -->
                @if($carrier->bankingDetails->status === 'rejected' && $carrier->bankingDetails->rejection_reason)
                <div class="bg-red-50 px-6 py-4 border-b border-red-200">
                    <div class="flex items-start gap-3">
                        <x-base.lucide icon="alert-circle" class="w-5 h-5 text-red-600 mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-red-800 mb-1">Rejection Reason:</p>
                            <p class="text-sm text-red-700">{{ $carrier->bankingDetails->rejection_reason }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Edit Banking Information Button -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <x-base.button type="button" onclick="toggleBankingEditForm()" variant="primary" class="inline-flex items-center px-4 py-2 text-sm font-medium transition-colors duration-150">
                        <x-base.lucide icon="edit" class="w-4 h-4 mr-2" />
                        Edit Banking Information
                    </x-base.button>
                </div>

                <!-- Banking Details -->
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Account Information -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                                    <x-base.lucide icon="user" class="w-5 h-5 text-blue-600" />
                                    Account Holder Information
                                </h3>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <x-base.lucide icon="user" class="w-4 h-4 text-gray-500" />
                                        <span class="text-gray-900 font-medium">{{ $carrier->bankingDetails->account_holder_name ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Business/Carrier Name</label>
                                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <x-base.lucide icon="building" class="w-4 h-4 text-gray-500" />
                                        <span class="text-gray-900 font-medium">{{ $carrier->name ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                @if($carrier->email)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <x-base.lucide icon="mail" class="w-4 h-4 text-gray-500" />
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
                                    <x-base.lucide icon="credit-card" class="w-5 h-5 text-blue-600" />
                                    Banking Details
                                </h3>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <x-base.lucide icon="hash" class="w-4 h-4 text-gray-500" />
                                        <span class="text-gray-900 font-mono tracking-wider text-lg">
                                            @if($carrier->bankingDetails->account_number && strlen($carrier->bankingDetails->account_number) >= 8)
                                            <span class="text-blue-600">{{ substr($carrier->bankingDetails->account_number, 0, 4) }}</span><span class="text-gray-400">••••••••</span><span class="text-blue-600 font-bold">{{ substr($carrier->bankingDetails->account_number, -4) }}</span>
                                            @elseif($carrier->bankingDetails->account_number)
                                            <span class="text-gray-400">••••</span><span class="text-blue-600 font-bold">{{ substr($carrier->bankingDetails->account_number, -4) }}</span>
                                            @else
                                            <span class="text-gray-400">No disponible</span>
                                            @endif
                                        </span>
                                        <x-base.badge variant="secondary" size="sm" class="ml-auto text-xs">Protected</x-base.badge>
                                    </div>
                                </div>

                                @if($carrier->bankingDetails->routing_number)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Routing Number</label>
                                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <x-base.lucide icon="map-pin" class="w-4 h-4 text-gray-500" />
                                        <span class="text-gray-900 font-mono">{{ $carrier->bankingDetails->routing_number }}</span>
                                    </div>
                                </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <x-base.lucide icon="globe" class="w-4 h-4 text-gray-500" />
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
                                        <x-base.lucide icon="calendar" class="w-4 h-4 text-gray-500" />
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
                                        <x-base.lucide icon="clock" class="w-4 h-4 text-gray-500" />
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

                <!-- Banking Edit Form (Hidden by default) -->
                <div id="bankingEditForm" class="hidden border-t border-gray-200 bg-gray-50 p-6">
                    <form method="POST" action="{{ route('admin.carrier.banking.update', $carrier) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="bg-white rounded-lg p-6 border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-6 flex items-center gap-2">
                                <x-base.lucide icon="edit-3" class="w-5 h-5 text-blue-600" />
                                Edit Banking Information
                            </h3>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Single Column Layout -->
                                <div class="space-y-4">
                                    <div>
                                        <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name</label>
                                        <input type="text" id="account_holder_name" name="account_holder_name" value="{{ old('account_holder_name', $carrier->bankingDetails->account_holder_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    </div>

                                    <div>
                                        <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                                        <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $carrier->bankingDetails->account_number) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    </div>

                                    <div>
                                        <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                        <select id="country_code" name="country_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                            <option value="US" {{ old('country_code', $carrier->bankingDetails->country_code) === 'US' ? 'selected' : '' }}>United States</option>
                                            <option value="CA" {{ old('country_code', $carrier->bankingDetails->country_code) === 'CA' ? 'selected' : '' }}>Canada</option>
                                            <option value="MX" {{ old('country_code', $carrier->bankingDetails->country_code) === 'MX' ? 'selected' : '' }}>Mexico</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required onchange="toggleRejectionReason()">
                                            <option value="pending" {{ old('status', $carrier->bankingDetails->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ old('status', $carrier->bankingDetails->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ old('status', $carrier->bankingDetails->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Rejection Reason (shown only when status is rejected) -->
                            <div id="rejectionReasonDiv" class="mt-4 {{ old('status', $carrier->bankingDetails->status) === 'rejected' ? '' : 'hidden' }}">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Please provide a reason for rejection...">{{ old('rejection_reason', $carrier->bankingDetails->rejection_reason) }}</textarea>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                                <x-base.button type="button" onclick="toggleBankingEditForm()" variant="outline-secondary" class="px-4 py-2 text-sm font-medium">
                                    Cancel
                                </x-base.button>
                                <x-base.button type="submit" variant="primary" class="px-4 py-2 text-sm font-medium">
                                    <x-base.lucide icon="save" class="w-4 h-4 mr-2 inline" />
                                    Save Changes
                                </x-base.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="text-center py-12">
                    <div class="mx-auto h-20 w-20 text-gray-300 mb-6">
                        <x-base.lucide icon="credit-card" class="w-20 h-20 mx-auto" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">There is no banking information for this carrier</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">This carrier has not provided banking information yet. This information is required for processing payments.</p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 max-w-lg mx-auto">
                        <div class="flex items-center justify-center">
                            <x-base.lucide icon="alert-circle" class="w-5 h-5 text-red-600 mr-2" />
                            <p class="text-sm text-red-800 font-medium">Banking information is required for processing payments</p>
                        </div>
                    </div>

                    <!-- Botón para agregar información bancaria -->
                    <div class="mb-6">
                        <x-base.button type="button" onclick="showAddBankingForm()" variant="primary" class="px-6 py-3 text-sm font-medium transition-colors">
                            <x-base.lucide icon="plus" class="w-4 h-4 mr-2 inline" />
                            Add Banking Information
                        </x-base.button>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 max-w-lg mx-auto">
                        <div class="flex items-start gap-3">
                            <x-base.lucide icon="info" class="w-5 h-5 text-blue-600 mt-0.5" />
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">How to Add Banking Information</p>
                                <p>You can add the banking information directly from here or the carrier can provide it through their user portal.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario para agregar información bancaria (oculto por defecto) -->
                <div id="addBankingForm" class="hidden mt-8 border-t pt-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-6 text-center">Add Banking Information</h4>
                    <form action="{{ route('admin.carrier.banking.store', $carrier->slug) }}" method="POST" class="max-w-2xl mx-auto">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Holder Name -->
                            <div class="md:col-span-2">
                                <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name *</label>
                                <input type="text" id="account_holder_name" name="account_holder_name" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter account holder name">
                            </div>

                            <!-- Account Number -->
                            <div class="md:col-span-2">
                                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">Account Number *</label>
                                <input type="text" id="account_number" name="account_number" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter account number">
                            </div>

                            <!-- Country Code -->
                            <div>
                                <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                                <select id="country_code" name="country_code" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select country</option>
                                    <option value="US" selected>United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="MX">Mexico</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select id="status" name="status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="toggleRejectionReason(this.value)">
                                    <option value="pending" selected>Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <!-- Rejection Reason (hidden by default) -->
                            <div id="rejection_reason_container" class="md:col-span-2 hidden">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter reason for rejection..."></textarea>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-center gap-4 mt-8">
                            <x-base.button type="button" onclick="hideAddBankingForm()" variant="outline-secondary" class="px-6 py-2 text-sm font-medium">
                                Cancel
                            </x-base.button>
                            <x-base.button type="submit" variant="primary" class="px-6 py-2 text-sm font-medium">
                                <x-base.lucide icon="save" class="w-4 h-4 mr-2 inline" />
                                Save Banking Information
                            </x-base.button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>


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
                            required></textarea>
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
                            required></textarea>
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
</div>
    @endsection

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
                        form.action = '{{ route("admin.carriers.documents.index", ["carrier" => $carrier->id]) }}/' + documentId;
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

        // Funciones para mostrar/ocultar el formulario de banking
        function showAddBankingForm() {
            document.getElementById('addBankingForm').classList.remove('hidden');
            // Scroll suave hacia el formulario
            document.getElementById('addBankingForm').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function hideAddBankingForm() {
            document.getElementById('addBankingForm').classList.add('hidden');
        }

        // Función para abrir el modal de rechazo
        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        // Función para cerrar el modal de rechazo
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectionReason').value = '';
        }

        // Función para mostrar/ocultar el formulario de edición de banking
        function toggleBankingEditForm() {
            const form = document.getElementById('bankingEditForm');
            const editBtn = document.getElementById('editBankingBtn');

            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                editBtn.textContent = 'Cancel Edit';
                editBtn.classList.remove('btn-primary');
                editBtn.classList.add('btn-secondary');
            } else {
                form.classList.add('hidden');
                editBtn.textContent = 'Edit Banking Information';
                editBtn.classList.remove('btn-secondary');
                editBtn.classList.add('btn-primary');
            }
        }

        // Función para mostrar/ocultar el campo de razón de rechazo
        function toggleRejectionReason(statusValue) {
            // Para el formulario de edición
            const statusSelect = document.getElementById('banking_status');
            const rejectionReasonDiv = document.getElementById('rejectionReasonDiv');

            if (statusSelect && rejectionReasonDiv) {
                if (statusSelect.value === 'rejected') {
                    rejectionReasonDiv.classList.remove('hidden');
                } else {
                    rejectionReasonDiv.classList.add('hidden');
                }
            }

            // Para el formulario de creación
            const rejectionReasonContainer = document.getElementById('rejection_reason_container');
            if (rejectionReasonContainer) {
                if (statusValue === 'rejected') {
                    rejectionReasonContainer.classList.remove('hidden');
                } else {
                    rejectionReasonContainer.classList.add('hidden');
                }
            }
        }
    </script>
    @endpush