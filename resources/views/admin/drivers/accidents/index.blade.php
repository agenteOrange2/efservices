@extends('../themes/' . $activeTheme)
@section('title', 'Driver Accidents Management')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Accidents Management', 'active' => true],
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
                Driver Accidents Management
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button data-tw-toggle="modal" data-tw-target="#add-accident-modal" variant="primary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Accident
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('admin.accidents.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search accidents..." />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Carrier</label>
                        <select name="carrier_filter"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Carriers</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}"
                                    {{ request('carrier_filter') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Driver</label>
                        <select name="driver_filter"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Drivers</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}"
                                    {{ request('driver_filter') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->last_name }}
                                </option>
                            @endforeach
                        </select>
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
                        <x-base.button type="submit" variant="outline-primary" class="mr-2">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                            Apply Filters
                        </x-base.button>
                        <a href="{{ route('admin.accidents.index') }}" class="btn btn-outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="x" />
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead>
                            <tr class="bg-slate-50/60">
                                <th class="whitespace-nowrap">
                                    <a href="{{ route(
                                        'admin.accidents.index',
                                        array_merge(request()->query(), [
                                            'sort_field' => 'accident_date',
                                            'sort_direction' =>
                                                request('sort_field') == 'accident_date' && request('sort_direction') == 'asc' ? 'desc' : 'asc',
                                        ]),
                                    ) }}"
                                        class="flex items-center">
                                        Date
                                        @if (request('sort_field') == 'accident_date')
                                            <x-base.lucide class="w-4 h-4 ml-1"
                                                icon="{{ request('sort_direction') == 'asc' ? 'chevron-up' : 'chevron-down' }}" />
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3">Carrier</th>
                                <th scope="col" class="px-6 py-3">Driver</th>
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
                                    <td class="px-6 py-4">{{ $accident->userDriverDetail->carrier->name }}</td>
                                    <td class="px-6 py-4">
                                        {{ $accident->userDriverDetail->user->name }}
                                        {{ $accident->userDriverDetail->last_name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $accident->nature_of_accident }}</td>
                                    <td class="px-6 py-4">
                                        @if ($accident->had_injuries)
                                            <span class="text-success">Yes ({{ $accident->number_of_injuries }})</span>
                                        @else
                                            <span class="text-danger">No</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($accident->had_fatalities)
                                            <span class="text-success">Yes ({{ $accident->number_of_fatalities }})</span>
                                        @else
                                            <span class="text-danger">No</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center">
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#edit-accident-modal"
                                                variant="primary" class="mr-2 p-1 edit-accident"
                                                data-accident="{{ json_encode($accident) }}">
                                                <x-base.lucide class="w-4 h-4" icon="edit" />
                                            </x-base.button>
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#delete-accident-modal"
                                                variant="danger" class="mr-2 p-1 delete-accident"
                                                data-accident-id="{{ $accident->id }}">
                                                <x-base.lucide class="w-4 h-4" icon="trash" />
                                            </x-base.button>
                                            <a href="{{ route('admin.drivers.accident-history', $accident->userDriverDetail->id) }}"
                                                class="btn btn-outline-secondary p-1 mr-2">
                                                <x-base.lucide class="w-4 h-4" icon="eye" />
                                            </a>
                                            <a href="{{ route('admin.accidents.documents', $accident->id) }}"
                                                class="btn btn-outline-primary p-1">
                                                <x-base.lucide class="w-4 h-4" icon="file-text" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="flex flex-col items-center justify-center py-4">
                                            <x-base.lucide class="w-10 h-10 text-slate-300" icon="alert-triangle" />
                                            <p class="mt-2 text-slate-500">No accident records found</p>
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#add-accident-modal"
                                                variant="outline-primary" class="mt-3">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
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
        </div>
    </div>

    <!-- Modal Añadir Accidente -->
    <x-base.dialog id="add-accident-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Accident Record</h2>
            </x-base.dialog.title>

            <form action="{{ route('admin.accidents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Seleccionar Carrier y Driver -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="carrier">Carrier</x-base.form-label>
                        <select id="carrier" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                            required>
                            <option value="">Select Carrier</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="user_driver_detail_id">Driver</x-base.form-label>
                        <select id="user_driver_detail_id" name="user_driver_detail_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Driver</option>
                        </select>
                    </div>

                    <!-- Fecha del accidente -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="accident_date">Accident Date</x-base.form-label>
                        <x-base.form-input id="accident_date" name="accident_date" type="date"
                            value="{{ date('Y-m-d') }}" required />
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
                        <x-base.form-input id="number_of_injuries" name="number_of_injuries" type="number"
                            min="0" value="0" />
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
                        <x-base.form-input id="number_of_fatalities" name="number_of_fatalities" type="number"
                            min="0" value="0" />
                    </div>

                    <!-- Comentarios -->
                    <div class="col-span-12">
                        <x-base.form-label for="comments">Comments</x-base.form-label>
                        <x-base.form-textarea id="comments" name="comments"
                            placeholder="Additional comments"></x-base.form-textarea>
                    </div>
                    
                    <!-- Documentos -->
                    <div class="col-span-12">
                        <x-base.form-label for="documents">Documents</x-base.form-label>
                        <div class="border-2 border-dashed rounded-md p-6 text-center">
                            <div class="mx-auto cursor-pointer relative">
                                <input type="file" name="documents[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="w-full h-full opacity-0 absolute inset-0 cursor-pointer z-50">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Drag and drop files here or click to browse</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">You can add more documents later</p>
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

            <form id="edit_accident_form" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Seleccionar Carrier y Driver -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_carrier">Carrier</x-base.form-label>
                        <select id="edit_carrier"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Carrier</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_user_driver_detail_id">Driver</x-base.form-label>
                        <select id="edit_user_driver_detail_id" name="user_driver_detail_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Driver</option>
                        </select>
                    </div>

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
                    <div class="col-span-12" id="edit_injuries_container" style="display: none;">
                        <x-base.form-label for="edit_number_of_injuries">Number of Injuries</x-base.form-label>
                        <x-base.form-input id="edit_number_of_injuries" name="number_of_injuries" type="number"
                            min="0" value="0" />
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
                        <x-base.form-input id="edit_number_of_fatalities" name="number_of_fatalities" type="number"
                            min="0" value="0" />
                    </div>

                    <!-- Comentarios -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_comments">Comments</x-base.form-label>
                        <x-base.form-textarea id="edit_comments" name="comments"
                            placeholder="Additional comments"></x-base.form-textarea>
                    </div>
                    
                    <!-- Documentos -->
                    <div class="col-span-12">
                        <x-base.form-label for="documents">Documents</x-base.form-label>
                        <div class="border-2 border-dashed rounded-md p-6 text-center">
                            <div class="mx-auto cursor-pointer relative">
                                <input type="file" name="documents[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="w-full h-full opacity-0 absolute inset-0 cursor-pointer z-50">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Drag and drop files here or click to browse</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <p class="text-xs text-gray-500">You can add more documents later</p>
                            <a href="#" id="view_documents_link" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                <i class="fas fa-eye mr-1"></i> View existing documents
                            </a>
                        </div>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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

                // Cargar conductores cuando se selecciona un transportista
                const carrierSelect = document.getElementById('carrier');
                const driverSelect = document.getElementById('user_driver_detail_id');

                carrierSelect.addEventListener('change', function() {
                    const carrierId = this.value;
                    if (carrierId) {
                        fetch(`/api/get-drivers-by-carrier-id/${carrierId}`)
                            .then(response => response.json())
                            .then(data => {
                                driverSelect.innerHTML = '<option value="">Select Driver</option>';
                                data.forEach(driver => {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.user.name} ${driver.last_name}`;
                                    driverSelect.appendChild(option);
                                });
                            });
                    } else {
                        // Si no hay carrier seleccionado, limpiar el select de conductores
                        $('#user_driver_detail_id').empty();
                        $('#user_driver_detail_id').append('<option value="">Select Driver</option>');
                    }
                });

                // Manejar visualización de campos de lesiones y fatalidades
                $('#had_injuries').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#injuries_container').show();
                    } else {
                        $('#injuries_container').hide();
                    }
                });

                $('#had_fatalities').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#fatalities_container').show();
                    } else {
                        $('#fatalities_container').hide();
                    }
                });

                // Configuración del modal de eliminación
                const deleteButtons = document.querySelectorAll('.delete-accident');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const accidentId = this.getAttribute('data-accident-id');
                        document.getElementById('delete_accident_form').action =
                            `/admin/accidents/${accidentId}`;
                    });
                });
            });
        </script>
    @endpush
@endsection
