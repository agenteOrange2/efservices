@extends('../themes/' . $activeTheme)
@section('title', 'Driver Training Schools')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Training Schools', 'active' => true],
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
                Driver Training Schools
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <x-base.button as="a" href="{{ route('admin.training-schools.create') }}" class="w-full sm:w-auto">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add New Training School
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.training-schools.docs.all') }}" class="w-full sm:w-auto" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                    View All Documents
                </x-base.button>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Filter Training Schools</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.training-schools.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-base.form-label for="search_term">Search</x-base.form-label>
                        <x-base.form-input type="text" name="search_term" id="search_term" value="{{ request('search_term') }}" placeholder="School name, city..." />
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
                                <x-base.form-label for="date_from">Start Date (from)</x-base.form-label>
                                <x-base.litepicker name="date_from" value="{{ request('date_from') }}" />
                            </div>
                            <div>
                                <x-base.form-label for="date_to">End Date (to)</x-base.form-label>
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

        <!-- Lista de escuelas de entrenamiento -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h3 class="box-title">Training Schools ({{ $trainingSchools->total() ?? 0 }})</h3>
                </div>
            </div>

            @if($trainingSchools->count() > 0)
                <div class="box-body p-0">
                    <div class="overflow-x-auto">
                        <x-base.table class="border-separate border-spacing-y-[10px]">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="whitespace-nowrap">
                                        <a href="{{ route('admin.training-schools.index', ['sort_field' => 'created_at', 'sort_direction' => request('sort_field') == 'created_at' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
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
                                        <a href="{{ route('admin.training-schools.index', ['sort_field' => 'school_name', 'sort_direction' => request('sort_field') == 'school_name' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                            School Name
                                            @if (request('sort_field') == 'school_name')
                                                @if (request('sort_direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="chevron-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        <a href="{{ route('admin.training-schools.index', ['sort_field' => 'date_end', 'sort_direction' => request('sort_field') == 'date_end' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                            Expiration Date
                                            @if (request('sort_field') == 'date_end')
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
                                    @forelse ($trainingSchools as $school)
                                        <x-base.table.tr>
                                            <x-base.table.td  class="px-6 py-4">{{ $school->created_at->format('m/d/Y') }}</x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">
                                                {{ $school->userDriverDetail->user->name ?? '---' }} 
                                                {{ $school->userDriverDetail->user->last_name ?? '' }}
                                            </x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">
                                                {{ $school->userDriverDetail->carrier->name ?? '---' }}
                                            </x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">{{ $school->school_name }}</x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">{{ $school->date_end }}</x-base.table.td>
                                            <x-base.table.td  class="px-6 py-4">
                                                @php
                                                    $docsCount = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\DriverTrainingSchool::class)
                                                        ->where('model_id', $school->id)
                                                        ->where('collection_name', 'school_certificates')
                                                        ->count();
                                                @endphp
                                                <a href="{{ route('admin.training-schools.docs.show', $school->id) }}" class="flex items-center">
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
                                                    <a href="{{ route('admin.training-schools.show', $school->id) }}" 
                                                       class="flex mr-1 text-primary" title="View Documents">
                                                       <x-base.lucide class="w-4 h-4 mr-3" icon="file-text" />
                                                       Documents                                                        
                                                       <span class="ml-1">
                                                           ({{ \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\DriverTrainingSchool::class)->where('model_id', $school->id)->where('collection_name', 'school_certificates')->count() }})
                                                        </span>                                                        
                                                    </a>
                                                    <a href="{{ route('admin.training-schools.edit', $school->id) }}" class="btn btn-sm btn-primary flex">
                                                        <x-base.lucide class="w-4 h-4 mr-3" icon="edit" />
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('admin.training-schools.destroy', $school->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this training school record?');">
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
                                            <x-base.table.td colspan="6" class="text-center">
                                                <div class="flex flex-col items-center justify-center py-16">
                                                    <x-base.lucide class="h-8 w-8 text-slate-400" icon="Users" />
                                                    No Training School found
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
                    {{ $trainingSchools->appends(request()->all())->links() }}
                </div>
            @else
                <div class="box-body p-10 text-center">
                    <div class="flex flex-col items-center justify-center py-8">
                        <x-base.lucide class="w-16 h-16 text-slate-300" icon="file-text" />
                        <div class="mt-5 text-slate-500">
                            No training school records found.
                        </div>
                        <x-base.button as="a" href="{{ route('admin.training-schools.create') }}" class="mt-5">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
                            Add Training School
                        </x-base.button>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection