@extends('../themes/' . $activeTheme)
@section('title', 'Driver Licenses')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Licenses', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div>
        <!-- Mensajes Flash -->
        @if (session()->has('success'))
            <div class="alert alert-success flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Cabecera -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
            <h2 class="text-lg font-medium">
                Driver Licenses
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <x-base.button as="a" href="{{ route('admin.licenses.create') }}" class="w-full sm:w-auto">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add New License
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.licenses.docs.all') }}" class="w-full sm:w-auto" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                    View All Documents
                </x-base.button>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Filter Licenses</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.licenses.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-base.form-label for="search_term">Search</x-base.form-label>
                        <x-base.form-input type="text" name="search_term" id="search_term" value="{{ request('search_term') }}" placeholder="License number, state..." />
                    </div>
                    <div>
                        <x-base.form-label for="driver_filter">Driver</x-base.form-label>
                        <select id="driver_filter" name="driver_filter" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Drivers</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_filter') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <x-base.form-label for="date_from">Issue Date (from)</x-base.form-label>
                                <x-base.litepicker name="date_from" value="{{ request('date_from') }}" />
                            </div>
                            <div>
                                <x-base.form-label for="date_to">Expiration Date (to)</x-base.form-label>
                                <x-base.litepicker name="date_to" value="{{ request('date_to') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <x-base.button type="submit" variant="primary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                            Apply Filters
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de licencias -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h3 class="box-title">Licenses ({{ $licenses->total() ?? 0 }})</h3>
                </div>
            </div>

            @if($licenses->count() > 0)
                <div class="box-body p-0">
                    <div class="overflow-x-auto">
                        <x-base.table class="border-separate border-spacing-y-[10px]">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="whitespace-nowrap">
                                        <a href="{{ route('admin.licenses.index', ['sort_field' => 'created_at', 'sort_direction' => request('sort_field') == 'created_at' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                            Created At
                                            @if (request('sort_field') == 'created_at')
                                                @if (request('sort_direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Driver
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Carrier
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        <a href="{{ route('admin.licenses.index', ['sort_field' => 'current_license_number', 'sort_direction' => request('sort_field') == 'current_license_number' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                            License Number
                                            @if (request('sort_field') == 'current_license_number')
                                                @if (request('sort_direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        <a href="{{ route('admin.licenses.index', ['sort_field' => 'expiration_date', 'sort_direction' => request('sort_field') == 'expiration_date' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                            Expiration Date
                                            @if (request('sort_field') == 'expiration_date')
                                                @if (request('sort_direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Documents
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Actions
                                    </x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>                                                                   
                                    @forelse ($licenses as $license)
                                        <x-base.table.tr>
                                            <x-base.table.td  class="px-6 py-4">{{ $license->created_at->format('m/d/Y') }}</x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">
                                                {{ $license->driverDetail->user->name ?? '---' }} 
                                                {{ $license->driverDetail->user->last_name ?? '' }}
                                            </x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">
                                                {{ $license->driverDetail->carrier->name ?? '---' }}
                                            </x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">{{ $license->current_license_number }}</x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">{{ $license->expiration_date }}</x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">
                                                @php
                                                    $docsCount = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\DriverLicense::class)
                                                        ->where('model_id', $license->id)
                                                        ->whereIn('collection_name', ['license_front', 'license_back'])
                                                        ->count();
                                                @endphp
                                                <a href="{{ route('admin.licenses.docs.show', $license->id) }}" class="flex items-center">
                                                    <span class="bg-primary/20 text-primary rounded px-2 py-1 text-xs">
                                                        <x-base.lucide class="w-3 h-3 inline-block" icon="file-text" />
                                                        {{ $docsCount }} {{ Str::plural('Document', $docsCount) }}
                                                    </span>
                                                </a>
                                            </x-base.table.td>
                                            <x-base.table.td>
                                                <x-base.menu class="h-5">
                                                    <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                        <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                            icon="MoreVertical" />
                                                    </x-base.menu.button>

                                                <x-base.menu.items class="w-40">
                                                    <div class="flex  flex-col gap-3">
                                                    <a href="{{ route('admin.licenses.show', $license->id) }}" 
                                                       class="flex mr-1 text-primary" title="View Documents">
                                                       <x-base.lucide class="w-4 h-4 mr-3" icon="file-text" />
                                                       Documents                                                        
                                                       <span class="ml-1">
                                                           ({{ \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\DriverLicense::class)->where('model_id', $license->id)->whereIn('collection_name', ['license_front', 'license_back'])->count() }})
                                                        </span>                                                        
                                                    </a>
                                                    <a href="{{ route('admin.licenses.edit', $license->id) }}" class="btn btn-sm btn-primary flex">
                                                        <x-base.lucide class="w-4 h-4 mr-3" icon="edit" />
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('admin.licenses.destroy', $license->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this license record?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm text-red-600  flex">
                                                            <x-base.lucide class="w-4 h-4 mr-3" icon="trash-2" />
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                                </x-base.menu.items>
                                            </x-base.menu>
                                            </x-base.table.td>
                                        </x-base.table.tr>
                                        @empty
                                        <x-base.table.tr>
                                            <x-base.table.td colspan="7" class="text-center">
                                                <div class="flex flex-col items-center justify-center py-16">
                                                    <x-base.lucide class="h-8 w-8 text-slate-400" icon="Users" />
                                                    No Licenses found
                                                </div>
                                            </x-base.table.td>
                                        </x-base.table.tr>
                                    @endforelse                                
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                </div>
                <!-- Paginación -->
                <div class="box-footer py-5 px-8">
                    {{ $licenses->appends(request()->all())->links() }}
                </div>
            @else
                <div class="box-body p-10 text-center">
                    <div class="flex flex-col items-center justify-center py-8">
                        <x-base.lucide class="w-16 h-16 text-slate-300" icon="file-text" />
                        <div class="mt-5 text-slate-500">
                            No license records found.
                        </div>
                        <x-base.button as="a" href="{{ route('admin.licenses.create') }}" class="mt-5">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
                            Add License
                        </x-base.button>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection