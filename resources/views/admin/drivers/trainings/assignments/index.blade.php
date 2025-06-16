@extends('../themes/' . $activeTheme)

@section('title', 'Assignments')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Trainings', 'url' => route('admin.trainings.index')],
        ['label' => 'Assignments', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Assignments</h1>
                <p class="mt-1 text-sm text-gray-600">Manage assignments of trainings to drivers</p>
            </div>
            <div>
                <x-base.button as="a" href="{{ route('admin.select-training') }}" class="w-full sm:w-auto">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="users" />
                    New Assignment
                </x-base.button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Filtros -->
        <div class="box box--stacked mt-5 p-3">
            <h3 class="box-title">Filter Assignments</h3>
            <div class="box-content">
                <form action="{{ route('admin.training-assignments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-base.form-label for="training_id">Training</x-base.form-label>
                        <x-base.form-select name="training_id" id="training_id">
                            <option value="">All</option>
                            @foreach($trainings as $training)
                                <option value="{{ $training->id }}" {{ request('training_id') == $training->id ? 'selected' : '' }}>
                                    {{ $training->title }}
                                </option>
                            @endforeach
                        </x-base.form-select>
                    </div>
                    
                    <div>
                        <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                        <x-base.form-select name="carrier_id" id="carrier_id">
                            <option value="">All</option>
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                            @endforeach
                        </x-base.form-select>
                    </div>
                    
                    <div>
                        <x-base.form-label for="status">Status</x-base.form-label>
                        <x-base.form-select name="status" id="status">
                            <option value="">All</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </x-base.form-select>
                    </div>
                    
                    <div class="flex items-end">
                        <x-base.button type="submit" class="mr-2">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="search" />
                            Filter
                        </x-base.button>
                        
                        <x-base.button type="button" variant="outline" onclick="window.location.href='{{ route('admin.training-assignments.index') }}'">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="x" />
                            Clear
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Assignments ({{ $assignments->total() ?? 0 }})</h3>
            </div>
            <div class="box-content">
                @if($assignments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Driver
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Carrier
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Training
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Due Date
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($assignments as $assignment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $assignment->driver->user->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $assignment->driver->carrier->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $assignment->training->title ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $assignment->due_date ? date('d/m/Y', strtotime($assignment->due_date)) : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($assignment->status === 'completed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                            @elseif($assignment->status === 'in_progress')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    In Progress
                                                </span>
                                            @elseif($assignment->status === 'overdue')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Overdue
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Assigned
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button type="button" onclick="showDetails('{{ $assignment->id }}')" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                                    <x-base.lucide class="w-5 h-5" icon="eye" />
                                                </button>
                                                
                                                @if($assignment->status !== 'completed')
                                                    <button type="button" onclick="markComplete('{{ $assignment->id }}')" class="text-green-600 hover:text-green-900" title="Marcar como completado">
                                                        <x-base.lucide class="w-5 h-5" icon="check" />
                                                    </button>
                                                @else
                                                    <form action="{{ url('admin/training-assignments/' . $assignment->id . '/mark-complete') }}" method="POST" class="inline-block">
                                                        @csrf
                                                        <input type="hidden" name="revert" value="1">
                                                        <button type="submit" class="text-orange-600 hover:text-orange-900" title="Revertir estado">
                                                            <x-base.lucide class="w-5 h-5" icon="rotate-ccw" />
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <form action="{{ url('admin/training-assignments/' . $assignment->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta asignación?')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar asignación">
                                                        <x-base.lucide class="w-5 h-5" icon="trash-2" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $assignments->appends(request()->all())->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No assignments found</h3>
                        <p class="mt-1 text-sm text-gray-500">Start assigning trainings to drivers.</p>
                        <div class="mt-6">
                            <x-base.button as="a" href="{{ route('admin.trainings.index', ['status' => 'active']) }}" class="mt-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Assign Training
                            </x-base.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Modal de detalles -->
    <x-base.dialog id="detailsModal">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Assignment Details</h2>
            </x-base.dialog.title>
            <div id="assignmentDetails" class="mt-4">
                <!-- Aquí se cargarán los detalles mediante AJAX -->
            </div>
            <x-base.dialog.footer>
                <x-base.button type="button" variant="outline" data-tw-dismiss="modal" onclick="closeDetailsModal()">
                    Close
                </x-base.button>
            </x-base.dialog.footer>
        </x-base.dialog.panel>
    </x-base.dialog>
    
    <!-- Modal de completar -->
    <x-base.dialog id="completeModal">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Mark as Completed</h2>
            </x-base.dialog.title>
            <form id="completeForm" action="" method="POST">
                @csrf
                
                <x-base.dialog.description>
                    <div class="mt-4">
                        <x-base.form-label for="completion_notes">Completion Notes</x-base.form-label>
                        <x-base.form-textarea name="completion_notes" id="completion_notes" rows="4" placeholder="Optional notes about the completion of the training"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                
                <x-base.dialog.footer>
                    <x-base.button type="button" variant="outline" data-tw-dismiss="modal" onclick="closeCompleteModal()">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary">
                        <x-base.lucide class="w-5 h-5 mr-2" icon="check" />
                        Mark as Completed
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>
@endsection

@push('scripts')
<script>
    function showDetails(id) {
        const detailsContainer = document.getElementById('assignmentDetails');
        
        // Mostrar modal y spinner de carga
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector('#detailsModal'));
        modal.show();
        
        detailsContainer.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div></div>';
        
        // Cargar detalles mediante AJAX
        fetch(`{{ url('admin/training-assignments') }}/${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (!data || typeof data !== 'object') {
                    throw new Error('Datos inválidos recibidos del servidor');
                }
                
                // Mostrar datos estructurados
                let html = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3">
                        <div>
                            <h4 class="font-semibold">Driver:</h4>
                            <p>${data.driver && data.driver.user ? data.driver.user.name : 'N/A'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Carrier:</h4>
                            <p>${data.driver && data.driver.carrier ? data.driver.carrier.name : 'N/A'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Training:</h4>
                            <p>${data.training ? data.training.title : 'N/A'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Status:</h4>
                            <p>${data.status_label || data.status || 'Desconocido'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Assigned Date:</h4>
                            <p>${data.created_at_formatted || 'No disponible'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Due Date:</h4>
                            <p>${data.due_date_formatted || 'No establecida'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Completed Date:</h4>
                            <p>${data.completed_at_formatted || 'No completado'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Notes:</h4>
                            <p>${data.notes || 'No notes'}</p>
                        </div>
                    </div>
                `;
                detailsContainer.innerHTML = html;
            })
            .catch(error => {
                detailsContainer.innerHTML = `<div class="text-red-500">Error al cargar los detalles: ${error.message}</div>`;
            });
    }
    
    function closeDetailsModal() {
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector('#detailsModal'));
        modal.hide();
    }
    
    function markComplete(id) {
        const form = document.getElementById('completeForm');
        
        // Configurar el formulario con la URL correcta
        form.action = `{{ url('admin/training-assignments') }}/${id}/mark-complete`;
        
        // Mostrar modal
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector('#completeModal'));
        modal.show();
    }
    
    function closeCompleteModal() {
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector('#completeModal'));
        modal.hide();
    }
    
    // Los modales de x-base.dialog ya manejan el cierre al hacer clic fuera de ellos
</script>
@endpush
