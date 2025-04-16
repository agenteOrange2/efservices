@extends('../themes/' . $activeTheme)
@section('title', 'Driver Test History')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Driver Tests', 'url' => route('admin.testings.index')],
    ['label' => 'Driver Test History', 'active' => true],
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
            Test History for {{ $driver->user->name }} {{ $driver->last_name }}
        </h2>
        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
            <x-base.button as="a" href="{{ route('admin.drivers.show', $driver->id) }}"
                class="w-full sm:w-auto" variant="outline-primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="user" />
                Driver Profile
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.testings.index') }}"
                class="w-full sm:w-auto" variant="primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                All Tests
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
                <div class="text-gray-500">{{ $driver->phone }}</div>
                <div class="text-gray-500">{{ $driver->carrier->name }}</div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="flex items-center">
                    <div class="text-gray-500 mr-2">Total Tests:</div>
                    <div class="text-lg font-medium">{{ $testings->total() }}</div>
                </div>
                @if ($testings->count() > 0)
                <div class="flex items-center mt-1">
                    <div class="text-gray-500 mr-2">Last Test:</div>
                    <div class="text-blue-600">
                        {{ $testings->first()->test_date->format('M d, Y') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cabecera y Búsqueda -->
    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Driver Test Records
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <form action="{{ route('admin.drivers.testing-history', $driver->id) }}" method="GET"
                class="mr-2 flex gap-2">
                <div class="relative">
                    <x-base.lucide
                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                        icon="Search" />
                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                        value="{{ request('search_term') }}" type="text" placeholder="Search tests..." />
                </div>
                
                <select name="test_type" class="mr-2 text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All Test Types</option>
                    @foreach ($testTypes as $type)
                    <option value="{{ $type }}" {{ request('test_type') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                    @endforeach
                </select>
                
                <select name="test_result" class="mr-2 text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All Results</option>
                    @foreach ($testResults as $result)
                    <option value="{{ $result }}" {{ request('test_result') == $result ? 'selected' : '' }}>
                        {{ $result }}
                    </option>
                    @endforeach
                </select>
                
                <x-base.button type="submit" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                    Filter
                </x-base.button>
            </form>
            
            <x-base.button data-tw-toggle="modal" data-tw-target="#add-testing-modal"
                variant="primary" class="flex items-center">
                <x-base.lucide class="h-4 w-4 mr-2" icon="plus" />
                Add Test
            </x-base.button>
        </div>
    </div>

    <!-- Tabla de Tests -->
    <div class="box box--stacked p-5 mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead>
                    <tr class="bg-slate-50/60">
                        <th scope="col" class="px-6 py-3">
                            <a href="{{ route('admin.drivers.testing-history', [
                                'driver' => $driver->id,
                                'sort_field' => 'test_date',
                                'sort_direction' => request('sort_field') == 'test_date' &&
                                request('sort_direction') == 'asc' ? 'desc' : 'asc',
                                'search_term' => request('search_term'),
                                'test_type' => request('test_type'),
                                'test_result' => request('test_result')
                            ]) }}" class="flex items-center">
                                Date
                                @if(request('sort_field') == 'test_date' || !request('sort_field'))
                                <x-base.lucide class="w-4 h-4 ml-1" icon="{{ request('sort_direction') == 'asc'
                                    ? 'chevron-up' : 'chevron-down' }}" />
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Test Type</th>
                        <th scope="col" class="px-6 py-3">Result</th>
                        <th scope="col" class="px-6 py-3">Next Due</th>
                        <th scope="col" class="px-6 py-3">Administered By</th>
                        <th scope="col" class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testings as $testing)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <td class="px-6 py-4">{{ $testing->test_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4">{{ $testing->test_type }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $testing->test_result == 'Pass' ? 'bg-success/20 text-success' : 
                                   ($testing->test_result == 'Fail' ? 'bg-danger/20 text-danger' : 
                                   'bg-warning/20 text-warning') }}">
                                {{ $testing->test_result }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            {{ $testing->next_test_due ? $testing->next_test_due->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4">{{ $testing->administered_by ?? 'N/A' }}</td>
                        <td class="text-center">
                            <div class="flex justify-center items-center">
                                <x-base.button data-tw-toggle="modal" data-tw-target="#edit-testing-modal"
                                    variant="primary" class="mr-2 p-1 edit-testing"
                                    data-testing="{{ json_encode($testing) }}">
                                    <x-base.lucide class="w-4 h-4" icon="edit" />
                                </x-base.button>
                                <x-base.button data-tw-toggle="modal" data-tw-target="#delete-testing-modal"
                                    variant="danger" class="p-1 delete-testing"
                                    data-testing-id="{{ $testing->id }}">
                                    <x-base.lucide class="w-4 h-4" icon="trash" />
                                </x-base.button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10">
                            <div class="flex flex-col items-center">
                                <x-base.lucide class="h-16 w-16 text-gray-300" icon="alert-triangle" />
                                <p class="mt-2 text-gray-500">No test records found for this driver</p>
                                <x-base.button data-tw-toggle="modal" data-tw-target="#add-testing-modal"
                                    variant="outline-primary" class="mt-4">
                                    <x-base.lucide class="h-4 w-4 mr-1" icon="plus" />
                                    Add First Test
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
    
    <!-- Modal Añadir Test -->
    <x-base.dialog id="add-testing-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Test Record</h2>
            </x-base.dialog.title>
            <form action="{{ route('admin.testings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
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
                            <option value="Drug">Drug Test</option>
                            <option value="Alcohol">Alcohol Test</option>
                            <option value="Skills">Skills Test</option>
                            <option value="Knowledge">Knowledge Test</option>
                            <option value="Medical">Medical Exam</option>
                            <option value="Vision">Vision Test</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Resultado de la prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="test_result">Test Result</x-base.form-label>
                        <select id="test_result" name="test_result" 
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Result</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <!-- Próxima fecha de prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="next_test_due">Next Test Due</x-base.form-label>
                        <x-base.form-input id="next_test_due" name="next_test_due" type="date" />
                    </div>

                    <!-- Administered By -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="administered_by">Administered By</x-base.form-label>
                        <x-base.form-input id="administered_by" name="administered_by" type="text"
                            placeholder="Name of tester/organization" />
                    </div>

                    <!-- Location -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="location">Location</x-base.form-label>
                        <x-base.form-input id="location" name="location" type="text"
                            placeholder="Test location" />
                    </div>

                    <!-- Tipos especiales de prueba -->
                    <div class="col-span-12">
                        <div class="flex flex-wrap gap-5">
                            <label for="is_random_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_random_test"
                                    name="is_random_test" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Random Test</span>
                            </label>
                            <label for="is_post_accident_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_post_accident_test"
                                    name="is_post_accident_test" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Post-Accident Test</span>
                            </label>
                            <label for="is_reasonable_suspicion_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_reasonable_suspicion_test"
                                    name="is_reasonable_suspicion_test" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Reasonable Suspicion Test</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="col-span-12">
                        <x-base.form-label for="notes">Notes</x-base.form-label>
                        <x-base.form-textarea id="notes" name="notes"
                            placeholder="Additional notes about the test"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                        class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Editar Test -->
    <x-base.dialog id="edit-testing-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Edit Test Record</h2>
            </x-base.dialog.title>
            <form id="edit_testing_form" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
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
                            <option value="Drug">Drug Test</option>
                            <option value="Alcohol">Alcohol Test</option>
                            <option value="Skills">Skills Test</option>
                            <option value="Knowledge">Knowledge Test</option>
                            <option value="Medical">Medical Exam</option>
                            <option value="Vision">Vision Test</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Resultado de la prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_test_result">Test Result</x-base.form-label>
                        <select id="edit_test_result" name="test_result" 
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Result</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <!-- Próxima fecha de prueba -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_next_test_due">Next Test Due</x-base.form-label>
                        <x-base.form-input id="edit_next_test_due" name="next_test_due" type="date" />
                    </div>

                    <!-- Administered By -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_administered_by">Administered By</x-base.form-label>
                        <x-base.form-input id="edit_administered_by" name="administered_by" type="text"
                            placeholder="Name of tester/organization" />
                    </div>

                    <!-- Location -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_location">Location</x-base.form-label>
                        <x-base.form-input id="edit_location" name="location" type="text"
                            placeholder="Test location" />
                    </div>

                    <!-- Tipos especiales de prueba -->
                    <div class="col-span-12">
                        <div class="flex flex-wrap gap-5">
                            <label for="edit_is_random_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_random_test"
                                    name="is_random_test" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Random Test</span>
                            </label>
                            <label for="edit_is_post_accident_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_post_accident_test"
                                    name="is_post_accident_test" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Post-Accident Test</span>
                            </label>
                            <label for="edit_is_reasonable_suspicion_test" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_reasonable_suspicion_test"
                                    name="is_reasonable_suspicion_test" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Reasonable Suspicion Test</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_notes">Notes</x-base.form-label>
                        <x-base.form-textarea id="edit_notes" name="notes"
                            placeholder="Additional notes about the test"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                        class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Update
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Eliminar Test -->
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
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                    class="mr-1 w-24">
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
    // Configuración del modal de edición
    const editButtons = document.querySelectorAll('.edit-testing');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const testing = JSON.parse(this.getAttribute('data-testing'));
            
            // Establecer la acción del formulario
            document.getElementById('edit_testing_form').action = 
                `/admin/testings/${testing.id}`;
            
            // Establecer valores en el formulario
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
                `/admin/testings/${testingId}`;
        });
    });
});
</script>
@endpush
@endsection