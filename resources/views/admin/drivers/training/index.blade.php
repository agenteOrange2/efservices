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
                <x-base.button as="a" href="{{ route('admin.training-schools.documents') }}" class="w-full sm:w-auto" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                    View All Documents
                </x-base.button>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Filter Training Schools</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.training-schools.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-base.form-label for="search_term">Search</x-base.form-label>
                        <x-base.form-input type="text" name="search_term" id="search_term" value="{{ request('search_term') }}" placeholder="School name, city..." />
                    </div>
                    <div>
                        <x-base.form-label for="driver_filter">Driver</x-base.form-label>
                        <select id="driver_filter" name="driver_filter" class="tom-select w-full">
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
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h3 class="box-title">Training Schools ({{ $trainingSchools->total() ?? 0 }})</h3>
                </div>
            </div>

            @if($trainingSchools->count() > 0)
                <div class="box-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">
                                        <a href="{{ route('admin.training-schools.index', array_merge(request()->all(), ['sort_field' => 'school_name', 'sort_direction' => request('sort_field') == 'school_name' && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                            School Name
                                            @if(request('sort_field') == 'school_name')
                                                @if(request('sort_direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="arrow-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="arrow-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="whitespace-nowrap">
                                        <a href="{{ route('admin.training-schools.index', array_merge(request()->all(), ['sort_field' => 'date_start', 'sort_direction' => request('sort_field') == 'date_start' && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                            Start Date
                                            @if(request('sort_field') == 'date_start')
                                                @if(request('sort_direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="arrow-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="arrow-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="whitespace-nowrap">
                                        <a href="{{ route('admin.training-schools.index', array_merge(request()->all(), ['sort_field' => 'date_end', 'sort_direction' => request('sort_field') == 'date_end' && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                            End Date
                                            @if(request('sort_field') == 'date_end')
                                                @if(request('sort_direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="arrow-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-2" icon="arrow-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="whitespace-nowrap">
                                        Location
                                    </th>
                                    <th class="whitespace-nowrap">
                                        Driver
                                    </th>
                                    <th class="whitespace-nowrap">
                                        Graduated
                                    </th>
                                    <th class="whitespace-nowrap">
                                        Documents
                                    </th>
                                    <th class="whitespace-nowrap text-center">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trainingSchools as $school)
                                    <tr class="intro-x">
                                        <td class="whitespace-nowrap font-medium">
                                            <a href="{{ route('admin.training-schools.edit', $school->id) }}" class="text-primary">
                                                {{ $school->school_name }}
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            {{ $school->date_start->format('m/d/Y') }}
                                        </td>
                                        <td class="whitespace-nowrap">
                                            {{ $school->date_end->format('m/d/Y') }}
                                        </td>
                                        <td class="whitespace-nowrap">
                                            {{ $school->city }}, {{ $school->state }}
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <a href="{{ route('admin.drivers.show', $school->userDriverDetail->id) }}" class="text-primary">
                                                {{ $school->userDriverDetail->user->name }} {{ $school->userDriverDetail->user->last_name ?? '' }}
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            @if($school->graduated)
                                                <span class="bg-success/20 text-success rounded px-2 py-1 text-xs">
                                                    <x-base.lucide class="w-3 h-3 inline-block" icon="check-circle" />
                                                    Graduated
                                                </span>
                                            @else
                                                <span class="bg-danger/20 text-danger rounded px-2 py-1 text-xs">
                                                    <x-base.lucide class="w-3 h-3 inline-block" icon="x-circle" />
                                                    Not Graduated
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap">
                                            @php
                                                $docsCount = \App\Models\DocumentAttachment::where('documentable_type', \App\Models\Admin\Driver\DriverTrainingSchool::class)
                                                    ->where('documentable_id', $school->id)
                                                    ->count();
                                            @endphp
                                            <a href="{{ route('admin.training-schools.show.documents', $school->id) }}" class="flex items-center">
                                                <span class="bg-primary/20 text-primary rounded px-2 py-1 text-xs">
                                                    <x-base.lucide class="w-3 h-3 inline-block" icon="file-text" />
                                                    {{ $docsCount }} {{ Str::plural('Document', $docsCount) }}
                                                </span>
                                            </a>
                                        </td>
                                        <td class="table-report__action">
                                            <div class="flex justify-center items-center">
                                                <a href="{{ route('admin.training-schools.edit', $school->id) }}" class="btn btn-sm btn-primary mr-2">
                                                    <x-base.lucide class="w-4 h-4" icon="edit" />
                                                </a>
                                                <form action="{{ route('admin.training-schools.destroy', $school->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this training school record?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <x-base.lucide class="w-4 h-4" icon="trash-2" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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