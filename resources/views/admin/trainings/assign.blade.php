@extends('../themes/' . $activeTheme)

@section('title', 'Asignar Entrenamiento')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Entrenamientos', 'url' => route('admin.trainings.index')],
        ['label' => 'Asignar', 'active' => true],
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

        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Select Training</h3>
            </div>
            <div class="box-content">
                <form action="{{ route('admin.trainings.assign.select') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-base.form-label for="training_id" required>Training</x-base.form-label>
                        <x-base.form-select name="training_id" id="training_id" required>
                            <option value="">Select training</option>
                            @foreach($trainings as $trainingItem)
                                <option value="{{ $trainingItem->id }}" {{ request('training_id') == $trainingItem->id ? 'selected' : '' }}>
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

        @if(isset($selectedTraining))
            <div class="box box--stacked mt-5 p-3">
                <div class="box-header">
                    <h3 class="box-title">Assign "{{ $selectedTraining->title }}" to Drivers</h3>
                </div>
                <div class="box-content">
                    <form action="{{ route('admin.trainings.assign', $selectedTraining->id) }}" method="POST" x-data="assignmentForm()">
                        @csrf
                        <input type="hidden" name="training_id" value="{{ $selectedTraining->id }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                                <x-base.form-select name="carrier_id" id="carrier_id" @change="loadDrivers()">
                                    <option value="">All carriers</option>
                                    @foreach($carriers as $carrier)
                                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                                    @endforeach
                                </x-base.form-select>
                                <p class="mt-1 text-xs text-gray-500">Optional: Filter drivers by carrier</p>
                            </div>
                            
                            <div>
                                <x-base.form-label for="driver_ids" required>Drivers</x-base.form-label>
                                <div class="relative">
                                    <select name="driver_ids[]" id="driver_ids" class="form-multiselect block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" multiple required x-ref="driversSelect" :disabled="isLoading">
                                        <template x-if="isLoading">
                                            <option value="">Loading drivers...</option>
                                        </template>
                                        <template x-if="!isLoading">
                                            <template x-for="driver in drivers" :key="driver.id">
                                                <option :value="driver.id" x-text="driver.name"></option>
                                            </template>
                                        </template>
                                    </select>
                                    <div x-show="isLoading" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Hold down the Ctrl (or Cmd on Mac) key to select multiple drivers</p>
                                @error('driver_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <x-base.form-label for="due_date" required>Due Date</x-base.form-label>
                                <x-base.form-input type="date" name="due_date" id="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required />
                                <p class="mt-1 text-xs text-gray-500">Due date for completing the training</p>
                                @error('due_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <x-base.form-label for="status" required>Initial Status</x-base.form-label>
                                <x-base.form-select name="status" id="status" required>
                                    <option value="assigned" {{ old('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                </x-base.form-select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="col-span-2">
                                <x-base.form-label for="notes">Notes</x-base.form-label>
                                <x-base.form-textarea name="notes" id="notes" rows="3">{{ old('notes') }}</x-base.form-textarea>
                                <p class="mt-1 text-xs text-gray-500">Optional notes about this assignment</p>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <x-base.button type="button" variant="outline" class="mr-3" onclick="window.location.href='{{ route('admin.trainings.index') }}'">
                                Cancel
                            </x-base.button>
                            <x-base.button type="submit">
                                <x-base.lucide class="w-5 h-5 mr-2" icon="users" />
                                Assign Training
                            </x-base.button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function assignmentForm() {
        return {
            isLoading: false,
            drivers: [],
            
            init() {
                this.loadDrivers();
            },
            
            loadDrivers() {
                const carrierId = document.getElementById('carrier_id').value;
                this.isLoading = true;
                console.log('Cargando conductores para transportista ID:', carrierId);
                
                // Limpiar la lista de conductores mientras se cargan nuevos
                this.drivers = [];
                
                if (!carrierId) {
                    console.log('No se ha seleccionado un transportista');
                    this.isLoading = false;
                    return;
                }
                
                // Construir URL con el ID del transportista - usamos la ruta API definida en admin.php
                const url = `/admin/trainings/carrier/${carrierId}/drivers`;
                console.log('URL de la solicitud:', url);
                
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Conductores recibidos:', data);
                        if (data.drivers) {
                            // Si la respuesta tiene el formato {drivers: [...]} (formato del controlador)
                            this.drivers = data.drivers.map(driver => ({
                                id: driver.id,
                                name: `${driver.user.name} ${driver.last_name || ''}`
                            }));
                        } else if (Array.isArray(data)) {
                            // Si la respuesta es un array directamente
                            this.drivers = data.map(driver => ({
                                id: driver.id,
                                name: `${driver.user.name} ${driver.last_name || ''}`
                            }));
                        } else {
                            this.drivers = [];
                        }
                        this.isLoading = false;
                    })
                    .catch(error => {
                        console.error('Error loading drivers:', error);
                        this.isLoading = false;
                        alert('Error al cargar conductores. Consulta la consola para más detalles.');
                    });
            }
        }
    }
</script>
@endpush
