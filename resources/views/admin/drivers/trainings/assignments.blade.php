@extends('../themes/' . $activeTheme)

@section('title', 'Asignaciones de Entrenamientos')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Entrenamientos', 'url' => route('admin.trainings.index')],
        ['label' => 'Asignaciones', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Asignaciones de Entrenamientos</h1>
                <p class="mt-1 text-sm text-gray-600">Gestiona las asignaciones de entrenamientos a conductores</p>
            </div>
            <div>
                <x-base.button as="a" href="{{ route('admin.trainings.assign') }}" class="w-full sm:w-auto">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="users" />
                    Nueva Asignación
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box mb-6">
            <h3 class="box-title">Filtrar Asignaciones</h3>
            <div class="box-content">
                <form action="{{ route('admin.trainings.assignments') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-base.form-label for="training_id">Entrenamiento</x-base.form-label>
                        <x-base.form-select name="training_id" id="training_id">
                            <option value="">Todos</option>
                            @foreach($trainings as $training)
                                <option value="{{ $training->id }}" {{ request('training_id') == $training->id ? 'selected' : '' }}>
                                    {{ $training->title }}
                                </option>
                            @endforeach
                        </x-base.form-select>
                    </div>
                    
                    <div>
                        <x-base.form-label for="carrier_id">Transportista</x-base.form-label>
                        <x-base.form-select name="carrier_id" id="carrier_id">
                            <option value="">Todos</option>
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                            @endforeach
                        </x-base.form-select>
                    </div>
                    
                    <div>
                        <x-base.form-label for="status">Estado</x-base.form-label>
                        <x-base.form-select name="status" id="status">
                            <option value="">Todos</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Asignado</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencido</option>
                        </x-base.form-select>
                    </div>
                    
                    <div class="flex items-end">
                        <x-base.button type="submit" class="mr-2">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="search" />
                            Filtrar
                        </x-base.button>
                        
                        <x-base.button type="button" variant="outline" onclick="window.location.href='{{ route('admin.trainings.assignments') }}'">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="x" />
                            Limpiar
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado -->
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Asignaciones ({{ $assignments->total() ?? 0 }})</h3>
            </div>
            <div class="box-content">
                @if($assignments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Conductor
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transportista
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Entrenamiento
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Límite
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
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
                                                    Completado
                                                </span>
                                            @elseif($assignment->status === 'in_progress')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    En Progreso
                                                </span>
                                            @elseif($assignment->status === 'overdue')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Vencido
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Asignado
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
                                                @endif
                                                
                                                <form action="{{ route('admin.trainings.assignment.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta asignación?')" class="inline-block">
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
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron asignaciones</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza asignando entrenamientos a conductores.</p>
                        <div class="mt-6">
                            <x-base.button as="a" href="{{ route('admin.trainings.assign') }}" class="mt-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Asignar Entrenamiento
                            </x-base.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Modal de detalles -->
    <div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" x-data="{ open: false, assignment: null }">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold text-gray-900">Detalles de la Asignación</h3>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <x-base.lucide class="w-6 h-6" icon="x" />
                </button>
            </div>
            
            <div id="assignmentDetails" class="mt-4">
                <!-- Los detalles se cargarán aquí mediante AJAX -->
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <x-base.button type="button" variant="outline" onclick="closeDetailsModal()">
                    Cerrar
                </x-base.button>
            </div>
        </div>
    </div>
    
    <!-- Modal de completar -->
    <div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold text-gray-900">Marcar como Completado</h3>
                <button onclick="closeCompleteModal()" class="text-gray-400 hover:text-gray-500">
                    <x-base.lucide class="w-6 h-6" icon="x" />
                </button>
            </div>
            
            <form id="completeForm" action="" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mt-4">
                    <x-base.form-label for="completion_notes">Notas de Finalización</x-base.form-label>
                    <x-base.form-textarea name="completion_notes" id="completion_notes" rows="4" placeholder="Notas opcionales sobre la finalización del entrenamiento"></x-base.form-textarea>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <x-base.button type="button" variant="outline" onclick="closeCompleteModal()">
                        Cancelar
                    </x-base.button>
                    <x-base.button type="submit">
                        <x-base.lucide class="w-5 h-5 mr-2" icon="check" />
                        Marcar como Completado
                    </x-base.button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showDetails(id) {
        const modal = document.getElementById('detailsModal');
        const detailsContainer = document.getElementById('assignmentDetails');
        
        // Mostrar modal y spinner de carga
        modal.classList.remove('hidden');
        detailsContainer.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div></div>';
        
        // Cargar detalles mediante AJAX
        fetch(`{{ route('admin.trainings.assignment-details') }}?id=${id}`)
            .then(response => response.text())
            .then(html => {
                detailsContainer.innerHTML = html;
            })
            .catch(error => {
                detailsContainer.innerHTML = `<div class="text-red-500">Error al cargar los detalles: ${error.message}</div>`;
            });
    }
    
    function closeDetailsModal() {
        const modal = document.getElementById('detailsModal');
        modal.classList.add('hidden');
    }
    
    function markComplete(id) {
        const modal = document.getElementById('completeModal');
        const form = document.getElementById('completeForm');
        
        // Configurar el formulario con la URL correcta
        form.action = `{{ route('admin.trainings.mark-complete', '') }}/${id}`;
        
        // Mostrar modal
        modal.classList.remove('hidden');
    }
    
    function closeCompleteModal() {
        const modal = document.getElementById('completeModal');
        modal.classList.add('hidden');
    }
    
    // Cerrar modales al hacer clic fuera de ellos
    window.onclick = function(event) {
        const detailsModal = document.getElementById('detailsModal');
        const completeModal = document.getElementById('completeModal');
        
        if (event.target === detailsModal) {
            closeDetailsModal();
        }
        
        if (event.target === completeModal) {
            closeCompleteModal();
        }
    }
</script>
@endpush
