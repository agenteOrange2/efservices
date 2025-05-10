@extends('../themes/' . $activeTheme)
@section('title', 'Driver Drug Testing Management')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('carrier.dashboard')],
        ['label' => 'Drivers', 'url' => route('carrier.drivers.index')],
        ['label' => 'Drug Testing Management', 'active' => true],
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
                Driver Drug Testing Management
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button data-tw-toggle="modal" data-tw-target="#add-testing-modal" variant="primary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Test Record
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('carrier.drivers.testings.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search tests..." />
                        </div>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Test Type</label>
                        <select name="test_type"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Types</option>
                            @foreach ($testTypes as $type)
                                <option value="{{ $type }}" {{ request('test_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Test Result</label>
                        <select name="test_result"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Results</option>
                            @foreach ($testResults as $result)
                                <option value="{{ $result }}" {{ request('test_result') == $result ? 'selected' : '' }}>
                                    {{ $result }}
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
                        <a href="{{ route('carrier.drivers.testings.index') }}" class="btn btn-outline-secondary">
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
                                        'carrier.drivers.testings.index',
                                        array_merge(request()->query(), [
                                            'sort_field' => 'test_date',
                                            'sort_direction' =>
                                                request('sort_field') == 'test_date' && request('sort_direction') == 'asc' ? 'desc' : 'asc',
                                        ]),
                                    ) }}"
                                        class="flex items-center">
                                        Date
                                        @if (request('sort_field') == 'test_date')
                                            <x-base.lucide class="w-4 h-4 ml-1"
                                                icon="{{ request('sort_direction') == 'asc' ? 'chevron-up' : 'chevron-down' }}" />
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3">Driver</th>
                                <th scope="col" class="px-6 py-3">Test Type</th>
                                <th scope="col" class="px-6 py-3">Result</th>
                                <th scope="col" class="px-6 py-3">Administered By</th>
                                <th scope="col" class="px-6 py-3">Next Test Due</th>
                                <th scope="col" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($testings as $testing)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                    <td class="px-6 py-4">{{ $testing->test_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        {{ $testing->userDriverDetail->user->name }}
                                        {{ $testing->userDriverDetail->last_name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $testing->test_type }}</td>
                                    <td class="px-6 py-4">
                                        @if (strtolower($testing->test_result) == 'negative' || strtolower($testing->test_result) == 'pass')
                                            <span class="text-success">{{ $testing->test_result }}</span>
                                        @elseif (strtolower($testing->test_result) == 'positive' || strtolower($testing->test_result) == 'fail')
                                            <span class="text-danger">{{ $testing->test_result }}</span>
                                        @else
                                            {{ $testing->test_result }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $testing->administered_by }}</td>
                                    <td class="px-6 py-4">
                                        {{ $testing->next_test_due ? $testing->next_test_due->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center">
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#edit-testing-modal"
                                                variant="primary" class="mr-2 p-1 edit-testing"
                                                data-testing="{{ json_encode($testing) }}">
                                                <x-base.lucide class="w-4 h-4" icon="edit" />
                                            </x-base.button>
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#delete-testing-modal"
                                                variant="danger" class="mr-2 p-1 delete-testing"
                                                data-testing-id="{{ $testing->id }}">
                                                <x-base.lucide class="w-4 h-4" icon="trash" />
                                            </x-base.button>
                                            <a href="{{ route('carrier.drivers.testings.driver_history', $testing->userDriverDetail->id) }}"
                                                class="btn btn-outline-secondary p-1">
                                                <x-base.lucide class="w-4 h-4" icon="eye" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="flex flex-col items-center justify-center py-4">
                                            <x-base.lucide class="w-10 h-10 text-slate-300" icon="clipboard-check" />
                                            <p class="mt-2 text-slate-500">No test records found</p>
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#add-testing-modal"
                                                variant="outline-primary" class="mt-3">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
                                                Add First Test Record
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
                    {{ $testings->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Añadir Prueba -->
    <x-base.dialog id="add-testing-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Test Record</h2>
            </x-base.dialog.title>

            <form action="{{ route('carrier.drivers.testings.store') }}" method="POST">
                @csrf
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Seleccionar Driver -->
                    <div class="col-span-12">
                        <x-base.form-label for="user_driver_detail_id">Driver</x-base.form-label>
                        <select id="user_driver_detail_id" name="user_driver_detail_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Driver</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}">
                                    {{ $driver->user->name }} {{ $driver->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha de la prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="test_date">Test Date</x-base.form-label>
                        <x-base.form-input id="test_date" name="test_date" type="date"
                            value="{{ date('Y-m-d') }}" required />
                    </div>

                    <!-- Tipo de prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="test_type">Test Type</x-base.form-label>
                        <select id="test_type" name="test_type"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Test Type</option>
                            <option value="Pre-Employment">Pre-Employment</option>
                            <option value="Random">Random</option>
                            <option value="Post-Accident">Post-Accident</option>
                            <option value="Reasonable Suspicion">Reasonable Suspicion</option>
                            <option value="Return-to-Duty">Return-to-Duty</option>
                            <option value="Follow-Up">Follow-Up</option>
                        </select>
                    </div>

                    <!-- Resultado de la prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="test_result">Test Result</x-base.form-label>
                        <select id="test_result" name="test_result"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Result</option>
                            <option value="Negative">Negative</option>
                            <option value="Positive">Positive</option>
                            <option value="Inconclusive">Inconclusive</option>
                            <option value="Refused">Refused</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>

                    <!-- Administrado por -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="administered_by">Administered By</x-base.form-label>
                        <x-base.form-input id="administered_by" name="administered_by" type="text" />
                    </div>

                    <!-- Ubicación -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="location">Location</x-base.form-label>
                        <x-base.form-input id="location" name="location" type="text" />
                    </div>

                    <!-- Próxima prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="next_test_due">Next Test Due (if applicable)</x-base.form-label>
                        <x-base.form-input id="next_test_due" name="next_test_due" type="date" />
                    </div>

                    <!-- Opciones adicionales -->
                    <div class="col-span-12">
                        <div class="flex flex-col space-y-2">
                            <label for="is_random_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_random_test" name="is_random_test"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Random Test') }}</label>
                            </label>
                            <label for="is_post_accident_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_post_accident_test" name="is_post_accident_test"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Post-Accident Test') }}</label>
                            </label>
                            <label for="is_reasonable_suspicion_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_reasonable_suspicion_test" name="is_reasonable_suspicion_test"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Reasonable Suspicion Test') }}</label>
                            </label>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="col-span-12">
                        <x-base.form-label for="notes">Notes</x-base.form-label>
                        <x-base.form-textarea id="notes" name="notes"
                            placeholder="Additional notes"></x-base.form-textarea>
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

    <!-- Modal Editar Prueba -->
    <x-base.dialog id="edit-testing-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Edit Test Record</h2>
            </x-base.dialog.title>

            <form id="edit_testing_form" action="" method="POST">
                @csrf
                @method('PUT')
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Seleccionar Driver -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_user_driver_detail_id">Driver</x-base.form-label>
                        <select id="edit_user_driver_detail_id" name="user_driver_detail_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Driver</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}">
                                    {{ $driver->user->name }} {{ $driver->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha de la prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_test_date">Test Date</x-base.form-label>
                        <x-base.form-input id="edit_test_date" name="test_date" type="date" required />
                    </div>

                    <!-- Tipo de prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_test_type">Test Type</x-base.form-label>
                        <select id="edit_test_type" name="test_type"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Test Type</option>
                            <option value="Pre-Employment">Pre-Employment</option>
                            <option value="Random">Random</option>
                            <option value="Post-Accident">Post-Accident</option>
                            <option value="Reasonable Suspicion">Reasonable Suspicion</option>
                            <option value="Return-to-Duty">Return-to-Duty</option>
                            <option value="Follow-Up">Follow-Up</option>
                        </select>
                    </div>

                    <!-- Resultado de la prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_test_result">Test Result</x-base.form-label>
                        <select id="edit_test_result" name="test_result"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Result</option>
                            <option value="Negative">Negative</option>
                            <option value="Positive">Positive</option>
                            <option value="Inconclusive">Inconclusive</option>
                            <option value="Refused">Refused</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>

                    <!-- Administrado por -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_administered_by">Administered By</x-base.form-label>
                        <x-base.form-input id="edit_administered_by" name="administered_by" type="text" />
                    </div>

                    <!-- Ubicación -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_location">Location</x-base.form-label>
                        <x-base.form-input id="edit_location" name="location" type="text" />
                    </div>

                    <!-- Próxima prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_next_test_due">Next Test Due (if applicable)</x-base.form-label>
                        <x-base.form-input id="edit_next_test_due" name="next_test_due" type="date" />
                    </div>

                    <!-- Opciones adicionales -->
                    <div class="col-span-12">
                        <div class="flex flex-col space-y-2">
                            <label for="edit_is_random_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_random_test" name="is_random_test"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Random Test') }}</label>
                            </label>
                            <label for="edit_is_post_accident_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_post_accident_test" name="is_post_accident_test"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Post-Accident Test') }}</label>
                            </label>
                            <label for="edit_is_reasonable_suspicion_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_reasonable_suspicion_test" name="is_reasonable_suspicion_test"
                                    type="checkbox" value="1" />
                                <label class="cursor-pointer select-none">{{ __('Reasonable Suspicion Test') }}</label>
                            </label>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_notes">Notes</x-base.form-label>
                        <x-base.form-textarea id="edit_notes" name="notes"
                            placeholder="Additional notes"></x-base.form-textarea>
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

    <!-- Modal Eliminar Prueba -->
    <x-base.dialog id="delete-testing-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
                <div class="mt-5 text-2xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this test record? <br>
                    This process cannot be undone.
                </div>
            </div>
            <form id="delete_testing_form" action="" method="POST" class="px-5 pb-8 text-center">
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
                // Configuración del modal de edición
                const editButtons = document.querySelectorAll('.edit-testing');

                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const testing = JSON.parse(this.getAttribute('data-testing'));

                        // Establecer la acción del formulario
                        document.getElementById('edit_testing_form').action =
                            `/carrier/driver-testings/${testing.id}`;

                        // Establecer valores en el formulario
                        document.getElementById('edit_user_driver_detail_id').value = testing.user_driver_detail_id;
                        document.getElementById('edit_test_date').value = testing.test_date.split('T')[0];
                        document.getElementById('edit_test_type').value = testing.test_type;
                        document.getElementById('edit_test_result').value = testing.test_result;
                        document.getElementById('edit_administered_by').value = testing.administered_by || '';
                        document.getElementById('edit_location').value = testing.location || '';
                        document.getElementById('edit_notes').value = testing.notes || '';
                        
                        // Establecer fecha de próxima prueba si existe
                        if (testing.next_test_due) {
                            document.getElementById('edit_next_test_due').value = testing.next_test_due.split('T')[0];
                        } else {
                            document.getElementById('edit_next_test_due').value = '';
                        }
                        
                        // Configurar checkboxes
                        document.getElementById('edit_is_random_test').checked = testing.is_random_test;
                        document.getElementById('edit_is_post_accident_test').checked = testing.is_post_accident_test;
                        document.getElementById('edit_is_reasonable_suspicion_test').checked = testing.is_reasonable_suspicion_test;
                    });
                });

                // Configuración del modal de eliminación
                const deleteButtons = document.querySelectorAll('.delete-testing');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const testingId = this.getAttribute('data-testing-id');
                        document.getElementById('delete_testing_form').action =
                            `/carrier/driver-testings/${testingId}`;
                    });
                });
            });
        </script>
    @endpush
@endsection
