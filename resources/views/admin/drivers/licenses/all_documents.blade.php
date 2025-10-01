@extends('../themes/' . $activeTheme)
@section('title', 'License Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Licenses', 'url' => route('admin.licenses.index')],
        ['label' => 'All Documents', 'active' => true],
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
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                All License Documents
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0 gap-2">
                <x-base.button as="a" href="{{ route('admin.licenses.index') }}" variant="outline-secondary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                    Back to Licenses
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('admin.licenses.docs.all') }}" method="GET" id="filter-form"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search documents..." />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by License</label>
                        <select name="license_filter" id="license-filter" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Licenses</option>
                            @foreach ($licenses as $licenseItem)
                                <option value="{{ $licenseItem->id }}" {{ request('license_filter') == $licenseItem->id ? 'selected' : '' }}>
                                    {{ $licenseItem->current_license_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Driver</label>
                        <select name="driver_filter" id="driver-filter" class="form-select w-full">
                            <option value="">All Drivers</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_filter') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <div class="relative">
                            <x-base.form-input class="datepicker form-control pl-12" name="date_from"
                                value="{{ request('date_from') }}" data-single-mode="true" />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <x-base.lucide class="w-5 h-5 text-slate-500" icon="calendar" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <div class="relative">
                            <x-base.form-input class="datepicker form-control pl-12" name="date_to"
                                value="{{ request('date_to') }}" data-single-mode="true" />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <x-base.lucide class="w-5 h-5 text-slate-500" icon="calendar" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end">
                        <x-base.button type="submit" variant="primary" class="mr-2">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                            Filter
                        </x-base.button>
                        <x-base.button as="a" href="{{ route('admin.licenses.docs.all') }}" variant="outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="refresh-cw" />
                            Reset
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de documentos -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-body p-0 overflow-x-auto">
                <table class="table table-striped w-full text-start">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">#</th>
                            <th class="whitespace-nowrap">Document</th>
                            <th class="whitespace-nowrap">Type</th>
                            <th class="whitespace-nowrap">Size</th>
                            <th class="whitespace-nowrap">License</th>
                            <th class="whitespace-nowrap">Driver</th>
                            <th class="whitespace-nowrap">Date</th>
                            <th class="whitespace-nowrap text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            <tr id="document-row-{{ $document->id }}">
                                <td>{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>
                                <td>
                                    <div class="flex items-center">
                                        @php
                                            $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                            $iconClass = 'file-text';
                                            
                                            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                                                $iconClass = 'image';
                                            } elseif (in_array($extension, ['pdf'])) {
                                                $iconClass = 'file-text';
                                            } elseif (in_array($extension, ['doc', 'docx'])) {
                                                $iconClass = 'file';
                                            } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                                                $iconClass = 'file-spreadsheet';
                                            }
                                        @endphp
                                        
                                        <x-base.lucide class="w-5 h-5 mr-2 text-primary" icon="{{ $iconClass }}" />
                                        {{ $document->file_name }}
                                    </div>
                                </td>
                                <td>{{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }}</td>
                                <td>{{ $document->human_readable_size }}</td>
                                <td>
                                    @php
                                        $license = \App\Models\Admin\Driver\DriverLicense::find($document->model_id);
                                    @endphp
                                    @if($license)
                                        <a href="{{ route('admin.licenses.show', $license->id) }}" class="text-primary hover:underline">
                                            {{ $license->current_license_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($license && $license->driverDetail && $license->driverDetail->user)
                                    {{ $license->driverDetail->user->name }}
                                    {{ $license->driverDetail->user->last_name ?? '' }}
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $document->created_at->format('m/d/Y H:i') }}</td>
                                <td class="text-center">
                                    <div class="flex justify-center">
                                        <a href="{{ route('admin.licenses.docs.preview', $document->id) }}" class="btn btn-sm btn-primary mr-2" target="_blank">
                                            <x-base.lucide class="w-4 h-4" icon="eye" />
                                        </a>
                                        @if($license)
                                        <a href="{{ route('admin.licenses.edit', $license->id) }}" class="btn btn-sm btn-warning mr-2">
                                            <x-base.lucide class="w-4 h-4" icon="clipboard-list" />
                                        </a>
                                        @endif
                                        <form action="{{ route('admin.licenses.docs.delete', $document->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este documento?')">
                                                <x-base.lucide class="w-4 h-4" icon="trash" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="p-5">
                {{ $documents->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar TomSelect para los selectores
        new TomSelect('#license-filter', {
            placeholder: 'Select a license',
            allowEmptyOption: true
        });
        
        new TomSelect('#driver-filter', {
            placeholder: 'Select a driver',
            allowEmptyOption: true
        });
        
        // Inicializar datepickers
        const datepickers = document.querySelectorAll('.datepicker');
        datepickers.forEach(function(el) {
            new Litepicker({
                element: el,
                format: 'YYYY-MM-DD',
                autoApply: true,
                buttonText: {
                    apply: 'Apply',
                    cancel: 'Cancel'
                }
            });
        });
    });
</script>
@endsection