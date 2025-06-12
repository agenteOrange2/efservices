@extends('../themes/' . $activeTheme)
@section('title', 'Driver Accident History')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Driver Accidents', 'url' => route('admin.accidents.index')],
    ['label' => 'Driver Accident History', 'active' => true],
];
@endphp

@section('subcontent')
<div>
    <!-- Mensajes Flash -->
    @if(session()->has('success'))
        <div class="alert alert-success flex items-center mb-5">
            <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
            {{ session('success') }}
        </div>
    @endif

    <!-- Cabecera -->
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Accident History for {{ $driver->user->name }} {{ $driver->last_name }}
        </h2>
        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
            <x-base.button as="a" href="{{ route('admin.drivers.show', $driver->id) }}"
                class="w-full sm:w-auto" variant="outline-primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="user" />
                Driver Profile
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.accidents.documents.index') }}"
                class="w-full sm:w-auto" variant="outline-primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                View All Documents
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.accidents.index') }}"
                class="w-full sm:w-auto" variant="primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                All Accidents
            </x-base.button>
        </div>
    </div>

    <!-- Info del Conductor -->
    <div class="box box--stacked p-5 mt-5">
        <div class="flex flex-col md:flex-row items-center">
            <div class="w-24 h-24 md:w-16 md:h-16 rounded-full overflow-hidden mr-5 mb-4 md:mb-0">
                @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                    <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}" alt="{{ $driver->user->name }}"
                        class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-500">
                        <x-base.lucide class="h-8 w-8" icon="user" />
                    </div>
                @endif
            </div>
            <div class="text-center md:text-left md:mr-auto">
                <div class="text-lg font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                <div class="text-gray-500 flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="phone" />
                    {{ $driver->phone }}
                </div>
                <div class="text-gray-500 flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="building" />
                    {{ $driver->carrier->name }}
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="flex items-center">
                    <div class="text-gray-500 mr-2">Total Accidents:</div>
                    <div class="text-lg font-medium">{{ $accidents->total() }}</div>
                </div>
                @if ($accidents->count() > 0)
                    <div class="flex items-center mt-1">
                        <div class="text-gray-500 mr-2">Last Accident:</div>
                        <div class="text-red-600">
                            {{ $accidents->first()->accident_date->format('M d, Y') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cabecera y Búsqueda -->
    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Driver Accident Records
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <form action="{{ route('admin.drivers.accident-history', $driver->id) }}" method="GET" class="mr-2">
                <div class="relative">
                    <x-base.lucide
                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                        icon="Search" />
                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                        value="{{ request('search_term') }}" type="text" placeholder="Search accidents..." />
                </div>
            </form>
            <x-base.button data-tw-toggle="modal" data-tw-target="#add-accident-modal" 
                variant="primary" class="flex items-center">
                <x-base.lucide class="h-4 w-4 mr-2" icon="plus" />
                Add Accident
            </x-base.button>
        </div>
    </div>

    <!-- Tabla de Accidentes -->
    <div class="box box--stacked p-5 mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead>
                    <tr class="bg-slate-50/60">
                        <th scope="col" class="px-6 py-3">
                            <a href="{{ route('admin.drivers.accident-history', [
                                'driver' => $driver->id,
                                'sort_field' => 'accident_date',
                                'sort_direction' => request('sort_field') == 'accident_date' && request('sort_direction') == 'asc' ? 'desc' : 'asc',
                                'search_term' => request('search_term')
                            ]) }}" class="flex items-center">
                                Date
                                @if(request('sort_field') == 'accident_date' || !request('sort_field'))
                                    <x-base.lucide class="w-4 h-4 ml-1" icon="{{ request('sort_direction') == 'asc' ? 'chevron-up' : 'chevron-down' }}" />
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Nature of Accident</th>
                        <th scope="col" class="px-6 py-3">Injuries</th>
                        <th scope="col" class="px-6 py-3">Fatalities</th>
                        <th scope="col" class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accidents as $accident)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <td class="px-6 py-4">{{ $accident->accident_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4">{{ $accident->nature_of_accident }}</td>
                            <td class="px-6 py-4">
                                @if($accident->had_injuries)
                                    <span class="text-success">Yes ({{ $accident->number_of_injuries }})</span>
                                @else
                                    <span class="text-danger">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($accident->had_fatalities)
                                    <span class="text-success">Yes ({{ $accident->number_of_fatalities }})</span>
                                @else
                                    <span class="text-danger">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center items-center">
                                    <a href="{{ route('admin.accidents.edit', $accident->id) }}" 
                                        class="btn btn-primary mr-2 p-1" title="Edit Accident">
                                        <x-base.lucide class="w-4 h-4" icon="edit" />
                                    </a>
                                    <form action="{{ route('admin.accidents.destroy', $accident->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger p-1 mr-2" title="Delete Accident"
                                            onclick="return confirm('Are you sure you want to delete this accident record?')">
                                            <x-base.lucide class="w-4 h-4" icon="trash" />
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.accidents.documents.index', ['accident_id' => $accident->id]) }}"
                                        class="btn btn-outline-primary p-1" title="View Documents">
                                        <x-base.lucide class="w-4 h-4" icon="file-text" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <div class="flex flex-col items-center">
                                    <x-base.lucide class="h-16 w-16 text-gray-300" icon="alert-triangle" />
                                    <p class="mt-2 text-gray-500">No accident records found for this driver</p>
                                    <x-base.button data-tw-toggle="modal" data-tw-target="#add-accident-modal"
                                        variant="outline-primary" class="mt-4">
                                        <x-base.lucide class="h-4 w-4 mr-1" icon="plus" />
                                        Add First Accident
                                    </x-base.button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="mt-5">
            {{ $accidents->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Modal Añadir Accidente -->
    <x-base.dialog id="add-accident-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Accident Record</h2>
            </x-base.dialog.title>
            
            <form action="{{ route('admin.accidents.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Fecha del accidente -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="accident_date">Accident Date</x-base.form-label>
                        <x-base.form-input id="accident_date" name="accident_date" type="date" value="{{ date('Y-m-d') }}" required />
                    </div>
                    
                    <!-- Naturaleza del accidente -->
                    <div class="col-span-12">
                        <x-base.form-label for="nature_of_accident">Nature of Accident</x-base.form-label>
                        <x-base.form-input id="nature_of_accident" name="nature_of_accident" type="text" 
                            placeholder="Describe the accident" required />
                    </div>
                    
                    <!-- Lesiones -->
                    <div class="col-span-12 ">
                        <div class="flex items-center mr-auto">
                            <label for="had_injuries" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="had_injuries" name="had_injuries"
                                    value="1" type="checkbox" />
                                <label class="cursor-pointer select-none">{{ __('Had Injuries?') }}</label>
                            </label>
                        </div>
                    </div>
                    <div class="col-span-12 " id="injuries_container" style="display: none;">
                        <x-base.form-label for="number_of_injuries">Number of Injuries</x-base.form-label>
                        <x-base.form-input id="number_of_injuries" name="number_of_injuries" type="number" min="0" value="0" />
                    </div>
                    
                    <!-- Fatalidades -->
                    <div class="col-span-12 ">
                        <div class="flex items-center mr-auto">
                            <label for="had_fatalities" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="had_fatalities" name="had_fatalities"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Had Fatalities?') }}</label>
                            </label>
                        </div>
                    </div>
                    <div class="col-span-12 " id="fatalities_container" style="display: none;">
                        <x-base.form-label for="number_of_fatalities">Number of Fatalities</x-base.form-label>
                        <x-base.form-input id="number_of_fatalities" name="number_of_fatalities" type="number" min="0" value="0" />
                    </div>
                    
                    <!-- Comentarios -->
                    <div class="col-span-12">
                        <x-base.form-label for="comments">Comments</x-base.form-label>
                        <x-base.form-textarea id="comments" name="comments" placeholder="Additional comments"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Editar Accidente -->
    <x-base.dialog id="edit-accident-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Edit Accident Record</h2>
            </x-base.dialog.title>
            
            <form id="edit_accident_form" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Fecha del accidente -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_accident_date">Accident Date</x-base.form-label>
                        <x-base.form-input id="edit_accident_date" name="accident_date" type="date" required />
                    </div>
                    
                    <!-- Naturaleza del accidente -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_nature_of_accident">Nature of Accident</x-base.form-label>
                        <x-base.form-input id="edit_nature_of_accident" name="nature_of_accident" type="text" 
                            placeholder="Describe the accident" required />
                    </div>
                    
                    <!-- Lesiones -->
                    <div class="col-span-12 ">
                        <div class="flex items-center mr-auto">
                            <label for="edit_had_injuries" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_had_injuries" name="had_injuries"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Had Injuries?') }}</label>
                            </label>
                        </div>
                    </div>
                    <div class="col-span-12 " id="edit_injuries_container" style="display: none;">
                        <x-base.form-label for="edit_number_of_injuries">Number of Injuries</x-base.form-label>
                        <x-base.form-input id="edit_number_of_injuries" name="number_of_injuries" type="number" min="0" value="0" />
                    </div>
                    
                    <!-- Fatalidades -->
                    <div class="col-span-12 ">
                        <div class="flex items-center mr-auto">
                            <label for="edit_had_fatalities" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_had_fatalities"
                                    name="had_fatalities" type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Had Fatalities?') }}</label>
                            </label>
                        </div>
                    </div>
                    <div class="col-span-12 " id="edit_fatalities_container" style="display: none;">
                        <x-base.form-label for="edit_number_of_fatalities">Number of Fatalities</x-base.form-label>
                        <x-base.form-input id="edit_number_of_fatalities" name="number_of_fatalities" type="number" min="0" value="0" />
                    </div>
                    
                    <!-- Comentarios -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_comments">Comments</x-base.form-label>
                        <x-base.form-textarea id="edit_comments" name="comments" placeholder="Additional comments"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Update
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Eliminar Accidente -->
    <x-base.dialog id="delete-accident-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
                <div class="mt-5 text-2xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this accident record? <br>
                    This process cannot be undone.
                </div>
            </div>
            <form id="delete_accident_form" action="" method="POST" class="px-5 pb-8 text-center">
                @csrf
                @method('DELETE')
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-24">
                    Cancel
                </x-base.button>
                <x-base.button type="submit" variant="danger" class="w-24">
                    Delete
                </x-base.button>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Funcionalidad para mostrar/ocultar campos de lesiones y fatalidades
        const hadInjuriesCheckbox = document.getElementById('had_injuries');
        const hadFatalitiesCheckbox = document.getElementById('had_fatalities');
        const injuriesContainer = document.getElementById('injuries_container');
        const fatalitiesContainer = document.getElementById('fatalities_container');
        
        hadInjuriesCheckbox.addEventListener('change', function() {
            injuriesContainer.style.display = this.checked ? 'block' : 'none';
        });
        
        hadFatalitiesCheckbox.addEventListener('change', function() {
            fatalitiesContainer.style.display = this.checked ? 'block' : 'none';
        });
        
        // Misma funcionalidad para el formulario de edición
        const editHadInjuriesCheckbox = document.getElementById('edit_had_injuries');
        const editHadFatalitiesCheckbox = document.getElementById('edit_had_fatalities');
        const editInjuriesContainer = document.getElementById('edit_injuries_container');
        const editFatalitiesContainer = document.getElementById('edit_fatalities_container');
        
        editHadInjuriesCheckbox.addEventListener('change', function() {
            editInjuriesContainer.style.display = this.checked ? 'block' : 'none';
        });
        
        editHadFatalitiesCheckbox.addEventListener('change', function() {
            editFatalitiesContainer.style.display = this.checked ? 'block' : 'none';
        });
        
        // Configuración del modal de edición
        const editButtons = document.querySelectorAll('.edit-accident');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const accident = JSON.parse(this.getAttribute('data-accident'));
                
                // Establecer la acción del formulario
                document.getElementById('edit_accident_form').action = `/admin/accidents/${accident.id}`;
                
                // Establecer valores en el formulario
                document.getElementById('edit_accident_date').value = accident.accident_date.split('T')[0];
                document.getElementById('edit_nature_of_accident').value = accident.nature_of_accident;
                document.getElementById('edit_comments').value = accident.comments || '';
                
                // Configurar checkboxes y campos numéricos
                document.getElementById('edit_had_injuries').checked = accident.had_injuries;
                document.getElementById('edit_injuries_container').style.display = accident.had_injuries ? 'block' : 'none';
                if (accident.had_injuries) {
                    document.getElementById('edit_number_of_injuries').value = accident.number_of_injuries;
                }
                
                document.getElementById('edit_had_fatalities').checked = accident.had_fatalities;
                document.getElementById('edit_fatalities_container').style.display = accident.had_fatalities ? 'block' : 'none';
                if (accident.had_fatalities) {
                    document.getElementById('edit_number_of_fatalities').value = accident.number_of_fatalities;
                }
            });
        });
        
        // Configuración del modal de eliminación
        const deleteButtons = document.querySelectorAll('.delete-accident');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const accidentId = this.getAttribute('data-accident-id');
                document.getElementById('delete_accident_form').action = `/admin/accidents/${accidentId}`;
            });
        });
    });
</script>
@endpush
@endsection