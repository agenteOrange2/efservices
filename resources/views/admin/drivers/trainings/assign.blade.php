@extends('../themes/' . $activeTheme)

@section('title', 'Assign Training')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Trainings', 'url' => route('admin.trainings.index')],
        ['label' => 'Assign', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Assign Training</h1>
                <p class="mt-1 text-sm text-gray-600">Assign trainings to drivers</p>
            </div>
            <div>
                <x-base.button as="a" href="{{ route('admin.trainings.index') }}" variant="outline">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="arrow-left" />
                    Back
                </x-base.button>
            </div>
        </div>

        @if(!isset($selectedTraining))
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Select Training</h3>
            </div>
            <div class="box-content">
                <form action="{{ route('admin.trainings.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-base.form-label for="training_id" required>Training</x-base.form-label>
                        <x-base.form-select name="training_id" id="training_id" required>
                            <option value="">Select training</option>
                            @foreach ($trainings as $trainingItem)
                                <option value="{{ $trainingItem->id }}"
                                    {{ request('training_id') == $trainingItem->id ? 'selected' : '' }}>
                                    {{ $trainingItem->title }}
                                </option>
                            @endforeach
                        </x-base.form-select>
                    </div>

                    <div class="flex items-end">
                        <x-base.button type="submit">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="search" />
                            Select
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if (isset($selectedTraining))
            <div class="box box--stacked mt-5 p-3">
                <div class="box-header">
                    <h3 class="box-title">Assign "{{ $selectedTraining->title }}" to Drivers</h3>
                </div>
                <div class="box-content">
                    <form action="{{ route('admin.trainings.assign', $selectedTraining->id) }}" method="POST"
                        x-data="assignmentForm()" id="assignmentForm">
                        @csrf
                        <input type="hidden" name="training_id" value="{{ $selectedTraining->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                                <x-base.form-select name="carrier_id" id="carrier_id" @change="loadDrivers($event.target.value)">
                                    <option value="">All carriers</option>
                                    @foreach ($carriers as $carrier)
                                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                                    @endforeach
                                </x-base.form-select>
                                <p class="mt-1 text-xs text-gray-500">Seleccione un carrier para filtrar conductores activos</p>
                            </div>

                            <div>
                                <x-base.form-label for="driver_ids" required>Drivers</x-base.form-label>
                                <div class="relative">
                                    <select name="driver_ids[]" id="driver_ids" class="tom-select w-full" multiple required
                                        x-ref="driversSelect" :disabled="isLoading">
                                        <template x-if="isLoading">
                                            <option value="">Loading drivers...</option>
                                        </template>
                                        <template x-if="!isLoading">
                                            <template x-for="driver in drivers" :key="driver.id">
                                                <option :value="driver.id" x-text="driver.name"></option>
                                            </template>
                                        </template>
                                    </select>
                                    <div x-show="isLoading"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Puede buscar y seleccionar múltiples conductores</p>
                                @error('driver_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-base.form-label for="due_date" required>Due Date</x-base.form-label>
                                <x-base.form-input type="date" name="due_date" id="due_date"
                                    value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required />
                                <p class="mt-1 text-xs text-gray-500">Due date for completing the training</p>
                                @error('due_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-base.form-label for="status" required>Initial Status</x-base.form-label>
                                <x-base.form-select name="status" id="status" required>
                                    <option value="assigned" {{ old('status') === 'assigned' ? 'selected' : '' }}>Assigned
                                    </option>
                                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                </x-base.form-select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <x-base.form-label for="notes">Notes</x-base.form-label>
                                <x-base.form-textarea name="notes" id="notes"
                                    rows="3">{{ old('notes') }}</x-base.form-textarea>
                                <p class="mt-1 text-xs text-gray-500">Optional notes about this assignment</p>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-base.button type="button" variant="outline" class="mr-3"
                                onclick="window.location.href='{{ route('admin.trainings.index') }}'">
                                Cancel
                            </x-base.button>
                            <x-base.button type="button" id="openConfirmModal" data-tw-toggle="modal"
                                data-tw-target="#confirmAssignmentModal">
                                <x-base.lucide class="w-5 h-5 mr-2" icon="users" />
                                Assign Training
                            </x-base.button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal de confirmación para asignación de entrenamiento -->
    {{-- <div id="confirmAssignmentModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Confirmar Asignación</h2>
                </div>
                <div class="modal-body">
                    <div class="p-5 text-center">
                        <x-base.lucide class="w-16 h-16 text-success mx-auto mt-3" icon="check-circle" />
                        <div class="text-3xl mt-5">¿Está seguro?</div>
                        <div class="text-slate-500 mt-2">¿Desea asignar este entrenamiento a los conductores seleccionados?
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <x-base.button type="button" variant="outline" data-tw-dismiss="modal"
                        class="mr-1">Cancelar</x-base.button>
                    <x-base.button type="button" variant="primary" id="confirmAssignBtn">Confirmar</x-base.button>
                </div>
            </div>
        </div>
    </div> --}}

    <x-base.dialog id="confirmAssignmentModal">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Confirm Assignment</h2>
            </x-base.dialog.title>
            <x-base.dialog.description>
                Are you sure you want to assign this training to the selected drivers? This action cannot be undone.
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button type="button" variant="outline" data-tw-dismiss="modal"
                    class="mr-1">Cancelar</x-base.button>
                <x-base.button type="button" variant="primary" id="confirmAssignBtn">Confirmar</x-base.button>
            </x-base.dialog.footer>
        </x-base.dialog.panel>
    </x-base.dialog>
@endsection

@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
    @vite('resources/js/vendors/tom-select.js')
    @vite('resources/css/vendors/tom-select.css')
    @vite('resources/js/vendors/modal.js')
@endPushOnce


@push('scripts')
    <script>
        function assignmentForm() {
            return {
                drivers: [],
                isLoading: false,
                selectedCarrier: '',
                init() {
                    // No cargar conductores inicialmente, esperar a que se seleccione un carrier
                    // Esto mejora la experiencia de usuario y evita cargar datos innecesarios
                },
                loadDrivers(carrierId) {
                    this.isLoading = true;

                    // Si no hay carrierId, usar 0 para obtener todos
                    const carrier = carrierId || document.getElementById('carrier_id').value || 0;
                    // Usar la ruta API correcta del controlador TrainingAssignmentsController
                    const url = '/admin/training-assignments/get-drivers/' + carrier;

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error al cargar conductores del transportista');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // El controlador devuelve directamente un array de conductores
                            this.drivers = data.map(driver => {
                                // Incluir información adicional para mostrar en el selector
                                let carrierInfo = driver.carrier ? ` (${driver.carrier.name})` : '';
                                return {
                                    id: driver.id,
                                    name: `${driver.user.name} ${driver.last_name || ''}${carrierInfo}`,
                                    carrier_id: driver.carrier_id
                                };
                            });

                            // Actualizar TomSelect con los nuevos datos
                            if (window.tomSelectDrivers) {
                                // Limpiar opciones existentes
                                window.tomSelectDrivers.clear();
                                window.tomSelectDrivers.clearOptions();

                                // Agregar nuevas opciones
                                this.drivers.forEach(driver => {
                                    window.tomSelectDrivers.addOption(driver);
                                });

                                // Refrescar el control
                                window.tomSelectDrivers.refreshOptions(false);
                            }

                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Usar Toastify para mostrar errores de manera más elegante
                            Toastify({
                                text: "Error al cargar conductores. Por favor, intente de nuevo.",
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#f44336",
                                className: "error",
                            }).showToast();
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                }
            };
        }
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variable global para almacenar la instancia de TomSelect
            window.tomSelectDrivers = null;

            // Inicializar TomSelect para el selector de conductores
            let driverSelect = document.getElementById('driver_ids');

            // Configuración de TomSelect
            if (driverSelect) {
                window.tomSelectDrivers = new TomSelect(driverSelect, {
                    plugins: ['remove_button', 'clear_button'],
                    maxItems: null, // Permitir selección múltiple sin límite
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name'], // Buscar por nombre
                    placeholder: 'Seleccione uno o más conductores',
                    // Permitir crear nuevas opciones: false
                    create: false,
                    // Personalizar mensajes y apariencia
                    render: {
                        no_results: function() {
                            return '<div class="py-2 px-3 text-red-500">No se encontraron conductores activos. Seleccione otro carrier o verifique que haya conductores activos.</div>';
                        },
                        option: function(data, escape) {
                            return '<div class="py-2 px-3 flex items-center justify-between">' + 
                                   '<span>' + escape(data.name) + '</span>' + 
                                   '</div>';
                        },
                        item: function(data, escape) {
                            return '<div class="item py-1 px-2 bg-blue-100 rounded flex items-center">' + 
                                   escape(data.name) + 
                                   '</div>';
                        }
                    }
                });
            }

            // Manejar el botón de confirmación del modal
            const confirmAssignBtn = document.getElementById('confirmAssignBtn');
            if (confirmAssignBtn) {
                confirmAssignBtn.addEventListener('click', function() {
                    // Validar que haya al menos un conductor seleccionado
                    const selectedDrivers = window.tomSelectDrivers ? window.tomSelectDrivers.getValue() :
                    [];
                    if (selectedDrivers.length === 0) {
                        // Mostrar notificación de error
                        Toastify({
                            text: "Debe seleccionar al menos un conductor",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f44336",
                            className: "error",
                        }).showToast();

                        // Cerrar el modal
                        const modal = tailwind.Modal.getInstance(document.querySelector(
                            '#confirmAssignmentModal'));
                        modal.hide();
                        return;
                    }

                    // Enviar el formulario
                    document.getElementById('assignmentForm').submit();
                });
            }
        });
    </script>
@endpush
