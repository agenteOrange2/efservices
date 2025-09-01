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
                        <h3 class="text-sm font-medium text-gray-500">Status</h3>
                        <div class="mt-1">
                            @php
                                $statusClass = '';
                                $iconClass = '';
                                switch(strtolower($driver->status_name)) {
                                    case 'active':
                                    case 'activo':
                                        $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                        $iconClass = 'text-green-600';
                                        break;
                                    case 'inactive':
                                    case 'inactivo':
                                        $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                        $iconClass = 'text-red-600';
                                        break;
                                    case 'pending':
                                    case 'pendiente':
                                        $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                        $iconClass = 'text-yellow-600';
                                        break;
                                    case 'suspended':
                                    case 'suspendido':
                                        $statusClass = 'bg-orange-100 text-orange-800 border-orange-200';
                                        $iconClass = 'text-orange-600';
                                        break;
                                    default:
                                        $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                        $iconClass = 'text-gray-600';
                                }
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $statusClass }}">
                                @switch(strtolower($driver->status_name))
                                    @case('active')
                                    @case('activo')
                                        <x-base.lucide class="w-4 h-4 mr-1 {{ $iconClass }}" icon="check-circle" />
                                        @break
                                    @case('inactive')
                                    @case('inactivo')
                                        <x-base.lucide class="w-4 h-4 mr-1 {{ $iconClass }}" icon="x-circle" />
                                        @break
                                    @case('pending')
                                    @case('pendiente')
                                        <x-base.lucide class="w-4 h-4 mr-1 {{ $iconClass }}" icon="clock" />
                                        @break
                                    @case('suspended')
                                    @case('suspendido')
                                        <x-base.lucide class="w-4 h-4 mr-1 {{ $iconClass }}" icon="pause-circle" />
                                        @break
                                    @default
                                        <x-base.lucide class="w-4 h-4 mr-1 {{ $iconClass }}" icon="help-circle" />
                                @endswitch
                                {{ $driver->status_name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        


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
                            <x-base.form-input class="rounded-[0.5rem] pl-9" name="search_term"
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

                    <div class="flex items-end justify-between sm:justify-start">
                        <x-base.button type="submit" class="btn btn-primary mr-2 flex item-center" variant="primary" >
                            <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                            Apply Filters
                        </x-base.button>
                        <x-base.button type="button" id="clear-filters" class="btn btn-outline-secondary flex item-center" variant="primary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="x" />
                            Clear Filters
                        </x-base.button>
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
