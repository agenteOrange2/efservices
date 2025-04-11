@extends('../themes/' . $activeTheme)
@section('title', 'Vehicle Types')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
    ['label' => 'Types', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Vehicle Types
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="primary" data-tw-toggle="modal" data-tw-target="#add-type-modal">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                    Add New Type
                </x-base.button>
            </div>
        </div>
        <div class="mt-3.5">
            <div class="box box--stacked flex flex-col">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div>
                        <div class="relative">
                            <form action="{{ route('admin.vehicle-types.index') }}" method="GET">
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search" />
                                <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search" type="text"
                                    placeholder="Search types..." value="{{ request('search') }}" />
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
                            @foreach ($vehicleTypes as $index => $type)
                                <x-base.table.tr class="[&_td]:last:border-b-0">
                                    <x-base.table.td class="border-dashed py-4">
                                        {{ $index + 1 + ($vehicleTypes->currentPage() - 1) * $vehicleTypes->perPage() }}
                                    </x-base.table.td>
                                    <x-base.table.td class="border-dashed py-4 font-medium">
                                        {{ $type->name }}
                                    </x-base.table.td>
                                    <x-base.table.td class="border-dashed py-4">
                                        {{ $type->vehicles_count }}
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
                                                        data-tw-target="#edit-type-modal"
                                                        data-id="{{ $type->id }}"
                                                        data-name="{{ $type->name }}"
                                                        class="edit-type-btn">
                                                        <x-base.lucide class="mr-2 h-4 w-4" icon="CheckSquare" />
                                                        Edit
                                                    </x-base.menu.item>
                                                    <x-base.menu.item class="text-danger" data-tw-toggle="modal"
                                                        data-tw-target="#delete-modal-{{ $type->id }}">
                                                        <x-base.lucide class="mr-2 h-4 w-4" icon="Trash2" />
                                                        Delete
                                                    </x-base.menu.item>
                                                </x-base.menu.items>
                                            </x-base.menu>
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                                
                                <!-- DELETE MODAL -->
                                <x-base.dialog id="delete-modal-{{ $type->id }}" size="md">
                                    <x-base.dialog.panel>
                                        <div class="p-5 text-center">
                                            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger"
                                                icon="XCircle" />
                                            <div class="mt-5 text-2xl">¿Estás seguro?</div>
                                            <div class="mt-2 text-slate-500">
                                                ¿Realmente quieres eliminar este tipo de vehículo?
                                                @if ($type->vehicles_count > 0)
                                                    <div class="mt-1 text-danger">
                                                        <strong>¡Atención!</strong> Este tipo está siendo utilizado por {{ $type->vehicles_count }} vehículo(s).
                                                    </div>
                                                @endif
                                                <br>
                                                Este proceso no se puede deshacer.
                                            </div>
                                        </div>
                                        <div class="px-5 pb-8 text-center">
                                            <form action="{{ route('admin.vehicle-types.destroy', $type->id) }}"
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
                    {{ $vehicleTypes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Type Modal -->
<x-base.dialog id="add-type-modal" size="md">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">
                Add New Vehicle Type
            </h2>
        </x-base.dialog.title>
        <form action="{{ route('admin.vehicle-types.store') }}" method="POST" id="add-type-form">
            @csrf
            <x-base.dialog.description>
                <div class="mt-3">
                    <x-base.form-label for="type-name">Type Name</x-base.form-label>
                    <x-base.form-input id="type-name" name="name" type="text" placeholder="Enter type name" required />
                    <div id="type-name-error" class="text-danger mt-1 hidden"></div>
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                    Cancel
                </x-base.button>
                <x-base.button class="w-20" type="submit" variant="primary" id="submit-type">
                    Save
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>

<!-- Edit Type Modal -->
<x-base.dialog id="edit-type-modal" size="md">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">
                Edit Vehicle Type
            </h2>
        </x-base.dialog.title>
        <form action="" method="POST" id="edit-type-form">
            @csrf
            @method('PUT')
            <x-base.dialog.description>
                <div class="mt-3">
                    <x-base.form-label for="edit-type-name">Type Name</x-base.form-label>
                    <x-base.form-input id="edit-type-name" name="name" type="text" placeholder="Enter type name" required />
                    <div id="edit-type-name-error" class="text-danger mt-1 hidden"></div>
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                    Cancel
                </x-base.button>
                <x-base.button class="w-20" type="submit" variant="primary" id="update-type">
                    Update
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación para el formulario de añadir tipo
        const addTypeForm = document.getElementById('add-type-form');
        const typeNameInput = document.getElementById('type-name');
        const typeNameError = document.getElementById('type-name-error');
        
        // Validación para el formulario de editar tipo
        const editTypeForm = document.getElementById('edit-type-form');
        const editTypeNameInput = document.getElementById('edit-type-name');
        const editTypeNameError = document.getElementById('edit-type-name-error');
        
        // Configurar el botón de editar para cargar los datos del tipo
        const editButtons = document.querySelectorAll('.edit-type-btn');
        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const typeId = this.getAttribute('data-id');
                const typeName = this.getAttribute('data-name');
                
                // Actualizar el formulario de edición
                editTypeForm.action = `{{ url('admin/vehicle-types') }}/${typeId}`;
                editTypeNameInput.value = typeName;
                editTypeNameError.classList.add('hidden');
            });
        });
        
        // Validación en el lado del cliente para formulario de añadir
        addTypeForm.addEventListener('submit', function(e) {
            let valid = true;
            
            if (!typeNameInput.value.trim()) {
                typeNameError.textContent = 'Type name is required';
                typeNameError.classList.remove('hidden');
                valid = false;
            } else {
                typeNameError.classList.add('hidden');
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
        
        // Validación en el lado del cliente para formulario de editar
        editTypeForm.addEventListener('submit', function(e) {
            let valid = true;
            
            if (!editTypeNameInput.value.trim()) {
                editTypeNameError.textContent = 'Type name is required';
                editTypeNameError.classList.remove('hidden');
                valid = false;
            } else {
                editTypeNameError.classList.add('hidden');
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection