@extends('../themes/' . $activeTheme)
@section('title', 'Driver Inspection Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Inspections', 'url' => route('admin.inspections.index')],
        ['label' => 'All Documents', 'url' => route('admin.inspections.documents')],
        ['label' => $driver->user->name . ' ' . $driver->last_name . ' Documents', 'active' => true],
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

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Inspection Documents: {{ $driver->user->name }} {{ $driver->last_name }}
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0 gap-2">
                <x-base.button as="a" href="{{ route('admin.inspections.documents') }}" variant="outline-secondary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                    All Documents
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.inspections.index') }}" variant="outline-secondary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="clipboard-list" />
                    All Inspections
                </x-base.button>
            </div>
        </div>

        <!-- Información del conductor -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Driver</h3>
                        <p class="mt-1 text-base">{{ $driver->user->name }} {{ $driver->last_name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Carrier</h3>
                        <p class="mt-1 text-base">{{ $driver->carrier->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">License</h3>
                        <p class="mt-1 text-base">{{ $driver->license_number }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Licencia del conductor -->
        @if(isset($license))
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Driver License</h3>
            </div>
            <div class="box-body p-5">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-1/3">
                        <div class="border rounded-lg overflow-hidden shadow-sm">
                            <div class="p-4 bg-gray-50 border-b">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-900 truncate" title="{{ $license->name }}">
                                            {{ $license->name }}
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $license->human_readable_size }} • 
                                            {{ $license->mime_type }} • 
                                            {{ $license->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="flex">
                                        <a href="{{ $license->getUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-2">
                                            <x-base.lucide class="w-5 h-5" icon="eye" />
                                        </a>
                                        <a href="{{ $license->getUrl() }}" download class="text-green-600 hover:text-green-800">
                                            <x-base.lucide class="w-5 h-5" icon="download" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3">
                        <div class="h-full flex items-center">
                            @if(in_array($license->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/jpg']))
                                <img src="{{ $license->getUrl() }}" alt="Driver License" class="max-h-48 object-contain rounded-lg border shadow-sm">
                            @else
                                <div class="flex items-center justify-center w-full h-full bg-gray-100 rounded-lg border p-6">
                                    <div class="text-center">
                                        <x-base.lucide class="h-12 w-12 mx-auto text-gray-400" icon="file-text" />
                                        <p class="mt-2 text-sm text-gray-600">Preview not available</p>
                                        <a href="{{ $license->getUrl() }}" target="_blank" class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            View Document
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('admin.inspections.driver.documents', $driver) }}" method="GET" id="filter-form"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search documents..." />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input name="date_from" type="date" value="{{ request('date_from') }}"
                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input name="date_to" type="date" value="{{ request('date_to') }}"
                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                            Apply Filters
                        </button>
                        <button type="button" id="clear-filters" class="btn btn-outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="x" />
                            Clear Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documentos -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <h3 class="text-lg font-medium mb-5">Documents ({{ $documents->total() }})</h3>
                
                @if($documents->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($documents as $document)
                            <div class="border rounded-lg overflow-hidden shadow-sm">
                                <div class="p-4 bg-gray-50 border-b">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-medium text-gray-900 truncate" title="{{ $document->name }}">
                                                {{ $document->name }}
                                            </h4>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $document->human_readable_size }} • 
                                                {{ $document->mime_type }} • 
                                                {{ $document->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                        <div class="flex">
                                            <a href="{{ $document->getUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-2">
                                                <x-base.lucide class="w-5 h-5" icon="eye" />
                                            </a>
                                            <a href="{{ $document->getUrl() }}" download class="text-green-600 hover:text-green-800">
                                                <x-base.lucide class="w-5 h-5" icon="download" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    @php
                                        $inspection = \App\Models\Admin\Driver\DriverInspection::find($document->model_id);
                                    @endphp
                                    
                                    @if($inspection)
                                        <div class="mb-2">
                                            <span class="text-xs font-medium text-gray-500">Inspection:</span>
                                            <a href="{{ route('admin.inspections.edit', $inspection) }}" class="text-sm text-blue-600 hover:underline">
                                                {{ $inspection->inspection_type }} ({{ $inspection->inspection_date->format('m/d/Y') }})
                                            </a>
                                        </div>
                                        
                                        @if($inspection->vehicle)
                                            <div>
                                                <span class="text-xs font-medium text-gray-500">Vehicle:</span>
                                                <span class="text-sm">
                                                    {{ $inspection->vehicle->year }} {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}
                                                </span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-5">
                        {{ $documents->appends(request()->except('page'))->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <x-base.lucide class="h-12 w-12 mx-auto text-gray-400" icon="file-question" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No documents found</h3>
                        <p class="mt-1 text-sm text-gray-500">No inspection documents match your search criteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Manejar el botón de limpiar filtros
                document.getElementById('clear-filters').addEventListener('click', function() {
                    // Seleccionar todos los inputs y selects del formulario de filtros
                    const form = document.getElementById('filter-form');
                    const inputs = form.querySelectorAll('input:not([type="submit"]), select');
                    
                    // Resetear el valor de cada campo
                    inputs.forEach(input => {
                        if (input.type === 'date' || input.type === 'text') {
                            input.value = '';
                        } else if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        }
                    });
                    
                    // Enviar el formulario con valores limpios
                    form.submit();
                });
            });
        </script>
    @endpush
@endsection
