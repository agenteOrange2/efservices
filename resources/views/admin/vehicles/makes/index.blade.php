@extends('../themes/' . $activeTheme)
@section('title', 'Vehicle Makes')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
    ['label' => 'Makes', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Vehicle Makes
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="primary" data-tw-toggle="modal" data-tw-target="#add-make-modal">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                    Add New Make
                </x-base.button>
            </div>
        </div>
        <div class="mt-3.5">
            <div class="box box--stacked flex flex-col">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div>
                        <div class="relative">
                            <form action="{{ route('admin.vehicle-makes.index') }}" method="GET">
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search" />
                                <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search" type="text"
                                    placeholder="Search makes..." value="{{ request('search') }}" />
                            </form>
                        </div>
                    </div>
                </div>
                <div class="overflow-auto xl:overflow-visible">
                    <x-base.table class="border-b border-slate-200/60">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.td
                                    class="w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    #
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Name
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Vehicles Count
                                </x-base.table.td>
                                <x-base.table.td
                                    class="w-36 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                    Action
                                </x-base.table.td>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @foreach ($vehicleMakes as $index => $make)
                                <x-base.table.tr class="[&_td]:last:border-b-0">
                                    <x-base.table.td class="border-dashed py-4">
                                        {{ $index + 1 + ($vehicleMakes->currentPage() - 1) * $vehicleMakes->perPage() }}
                                    </x-base.table.td>
                                    <x-base.table.td class="border-dashed py-4 font-medium">
                                        {{ $make->name }}
                                    </x-base.table.td>
                                    <x-base.table.td class="border-dashed py-4">
                                        {{ $make->vehicles_count }}
                                    </x-base.table.td>
                                    <x-base.table.td class="relative border-dashed py-4">
                                        <div class="flex items-center justify-center">
                                            <x-base.menu class="h-5">
                                                <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                    <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                        icon="MoreVertical" />
                                                </x-base.menu.button>
                                                <x-base.menu.items class="w-40">
                                                    <x-base.menu.item
                                                        data-tw-toggle="modal" 
                                                        data-tw-target="#edit-make-modal"
                                                        data-id="{{ $make->id }}"
                                                        data-name="{{ $make->name }}"
                                                        class="edit-make-btn">
                                                        <x-base.lucide class="mr-2 h-4 w-4" icon="CheckSquare" />
                                                        Edit
                                                    </x-base.menu.item>
                                                    <x-base.menu.item class="text-danger" data-tw-toggle="modal"
                                                        data-tw-target="#delete-modal-{{ $make->id }}">
                                                        <x-base.lucide class="mr-2 h-4 w-4" icon="Trash2" />
                                                        Delete
                                                    </x-base.menu.item>
                                                </x-base.menu.items>
                                            </x-base.menu>
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                                
                                <!-- DELETE MODAL -->
                                <x-base.dialog id="delete-modal-{{ $make->id }}" size="md">
                                    <x-base.dialog.panel>
                                        <div class="p-5 text-center">
                                            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger"
                                                icon="XCircle" />
                                            <div class="mt-5 text-2xl">¿Estás seguro?</div>
                                            <div class="mt-2 text-slate-500">
                                                ¿Realmente quieres eliminar esta marca?
                                                @if ($make->vehicles_count > 0)
                                                    <div class="mt-1 text-danger">
                                                        <strong>¡Atención!</strong> Esta marca está siendo utilizada por {{ $make->vehicles_count }} vehículo(s).
                                                    </div>
                                                @endif
                                                <br>
                                                Este proceso no se puede deshacer.
                                            </div>
                                        </div>
                                        <div class="px-5 pb-8 text-center">
                                            <form action="{{ route('admin.vehicle-makes.destroy', $make->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <x-base.button class="mr-1 w-24" data-tw-dismiss="modal"
                                                    type="button"
                                                    variant="outline-secondary">
                                                    Cancelar
                                                </x-base.button>
                                                <x-base.button class="w-24" type="submit" variant="danger">
                                                    Eliminar
                                                </x-base.button>
                                            </form>
                                        </div>
                                    </x-base.dialog.panel>
                                </x-base.dialog>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
                <div class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 p-5 sm:flex-row">
                    {{ $vehicleMakes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Make Modal -->
<x-base.dialog id="add-make-modal" size="md">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">
                Add New Vehicle Make
            </h2>
        </x-base.dialog.title>
        <form action="{{ route('admin.vehicle-makes.store') }}" method="POST" id="add-make-form">
            @csrf
            <x-base.dialog.description>
                <div class="mt-3">
                    <x-base.form-label for="make-name">Make Name</x-base.form-label>
                    <x-base.form-input id="make-name" name="name" type="text" placeholder="Enter make name" required />
                    <div id="make-name-error" class="text-danger mt-1 hidden"></div>
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                    Cancel
                </x-base.button>
                <x-base.button class="w-20" type="submit" variant="primary" id="submit-make">
                    Save
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>

<!-- Edit Make Modal -->
<x-base.dialog id="edit-make-modal" size="md">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">
                Edit Vehicle Make
            </h2>
        </x-base.dialog.title>
        <form action="" method="POST" id="edit-make-form">
            @csrf
            @method('PUT')
            <x-base.dialog.description>
                <div class="mt-3">
                    <x-base.form-label for="edit-make-name">Make Name</x-base.form-label>
                    <x-base.form-input id="edit-make-name" name="name" type="text" placeholder="Enter make name" required />
                    <div id="edit-make-name-error" class="text-danger mt-1 hidden"></div>
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                    Cancel
                </x-base.button>
                <x-base.button class="w-20" type="submit" variant="primary" id="update-make">
                    Update
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación para el formulario de añadir marca
        const addMakeForm = document.getElementById('add-make-form');
        const makeNameInput = document.getElementById('make-name');
        const makeNameError = document.getElementById('make-name-error');
        
        // Validación para el formulario de editar marca
        const editMakeForm = document.getElementById('edit-make-form');
        const editMakeNameInput = document.getElementById('edit-make-name');
        const editMakeNameError = document.getElementById('edit-make-name-error');
        
        // Configurar el botón de editar para cargar los datos de la marca
        const editButtons = document.querySelectorAll('.edit-make-btn');
        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const makeId = this.getAttribute('data-id');
                const makeName = this.getAttribute('data-name');
                
                // Actualizar el formulario de edición
                editMakeForm.action = `{{ url('admin/vehicle-makes') }}/${makeId}`;
                editMakeNameInput.value = makeName;
                editMakeNameError.classList.add('hidden');
            });
        });
        
        // Validación en el lado del cliente para formulario de añadir
        addMakeForm.addEventListener('submit', function(e) {
            let valid = true;
            
            if (!makeNameInput.value.trim()) {
                makeNameError.textContent = 'Make name is required';
                makeNameError.classList.remove('hidden');
                valid = false;
            } else {
                makeNameError.classList.add('hidden');
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
        
        // Validación en el lado del cliente para formulario de editar
        editMakeForm.addEventListener('submit', function(e) {
            let valid = true;
            
            if (!editMakeNameInput.value.trim()) {
                editMakeNameError.textContent = 'Make name is required';
                editMakeNameError.classList.remove('hidden');
                valid = false;
            } else {
                editMakeNameError.classList.add('hidden');
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection