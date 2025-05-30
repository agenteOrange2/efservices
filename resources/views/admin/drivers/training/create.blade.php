@extends('../themes/' . $activeTheme)
@section('title', 'Add Training School')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Training Schools', 'url' => route('admin.training-schools.index')],
        ['label' => 'Create New', 'active' => true],
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

        <!-- Título de la página -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium">
                Add New Training School
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <a href="{{ route('admin.training-schools.index') }}" class="btn btn-outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Training Schools
                </a>
            </div>
        </div>

        <!-- Formulario -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Training School Information</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.training-schools.store') }}" method="post" enctype="multipart/form-data" id="schoolForm">
                    @csrf

                    <!-- Información Básica -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Columna Izquierda -->
                        <div class="space-y-4">
                            <!-- Conductor -->
                            <div>
                                <x-base.form-label for="user_driver_detail_id" required>Driver</x-base.form-label>
                                <select name="user_driver_detail_id" id="user_driver_detail_id" class="tom-select w-full @error('user_driver_detail_id') border-danger @enderror" required>
                                    <option value="">Select Driver</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('user_driver_detail_id') == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_driver_detail_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nombre de la escuela -->
                            <div>
                                <x-base.form-label for="school_name" required>School Name</x-base.form-label>
                                <x-base.form-input type="text" id="school_name" name="school_name" placeholder="Enter school name" value="{{ old('school_name') }}" class="@error('school_name') border-danger @enderror" required />
                                @error('school_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ciudad -->
                            <div>
                                <x-base.form-label for="city" required>City</x-base.form-label>
                                <x-base.form-input type="text" id="city" name="city" placeholder="Enter city" value="{{ old('city') }}" class="@error('city') border-danger @enderror" required />
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <x-base.form-label for="state" required>State</x-base.form-label>
                                <x-base.form-input type="text" id="state" name="state" placeholder="Enter state" value="{{ old('state') }}" class="@error('state') border-danger @enderror" required />
                                @error('state')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="space-y-4">
                            <!-- Teléfono -->
                            <div>
                                <x-base.form-label for="phone_number" required>Phone Number</x-base.form-label>
                                <x-base.form-input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number" value="{{ old('phone_number') }}" class="@error('phone_number') border-danger @enderror" required />
                                @error('phone_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de inicio -->
                            <div>
                                <x-base.form-label for="date_start" required>Start Date</x-base.form-label>
                                <x-base.litepicker id="date_start" name="date_start" value="{{ old('date_start') }}" class="@error('date_start') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('date_start')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de finalización -->
                            <div>
                                <x-base.form-label for="date_end" required>End Date</x-base.form-label>
                                <x-base.litepicker id="date_end" name="date_end" value="{{ old('date_end') }}" class="@error('date_end') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('date_end')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Checkboxes -->
                            <div class="mt-4">
                                <div class="form-check">
                                    <input type="checkbox" id="graduated" name="graduated" class="form-check-input" value="1" {{ old('graduated') ? 'checked' : '' }}>
                                    <x-base.form-label for="graduated" class="form-check-label">Graduated</x-base.form-label>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" id="subject_to_safety_regulations" name="subject_to_safety_regulations" class="form-check-input" value="1" {{ old('subject_to_safety_regulations') ? 'checked' : '' }}>
                                    <x-base.form-label for="subject_to_safety_regulations" class="form-check-label">Subject to Safety Regulations</x-base.form-label>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" id="performed_safety_functions" name="performed_safety_functions" class="form-check-input" value="1" {{ old('performed_safety_functions') ? 'checked' : '' }}>
                                    <x-base.form-label for="performed_safety_functions" class="form-check-label">Performed Safety Functions</x-base.form-label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Habilidades -->
                    <div class="mt-8">
                        <h4 class="font-medium">Training Skills</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="form-check">
                                <input type="checkbox" id="skill_driving" name="training_skills[]" class="form-check-input" value="driving" {{ old('training_skills') && in_array('driving', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_driving" class="form-check-label">Driving</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_safety" name="training_skills[]" class="form-check-input" value="safety" {{ old('training_skills') && in_array('safety', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_safety" class="form-check-label">Safety Procedures</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_maintenance" name="training_skills[]" class="form-check-input" value="maintenance" {{ old('training_skills') && in_array('maintenance', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_maintenance" class="form-check-label">Vehicle Maintenance</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_loading" name="training_skills[]" class="form-check-input" value="loading" {{ old('training_skills') && in_array('loading', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_loading" class="form-check-label">Loading/Unloading</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_regulations" name="training_skills[]" class="form-check-input" value="regulations" {{ old('training_skills') && in_array('regulations', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_regulations" class="form-check-label">DOT Regulations</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_emergency" name="training_skills[]" class="form-check-input" value="emergency" {{ old('training_skills') && in_array('emergency', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_emergency" class="form-check-label">Emergency Procedures</x-base.form-label>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Documentos -->
                    <div class="mt-8">
                        <h4 class="font-medium mb-3">Documents</h4>
                        
                        <!-- Componente Livewire para carga de archivos -->
                        <livewire:components.file-uploader model-name="training_files" model-index="0" label="Upload Training Documents" :existing-files="[]" />
                    </div>

                    <!-- Botones del formulario -->
                    <div class="flex justify-end mt-8">
                        <x-base.button type="button" class="mr-3" variant="outline-secondary" as="a" href="{{ route('admin.training-schools.index') }}">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Save Training School
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Validación del formulario
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar que la fecha de fin es posterior a la fecha de inicio
            document.getElementById('schoolForm').addEventListener('submit', function(event) {
                const startDate = new Date(document.getElementById('date_start').value);
                const endDate = new Date(document.getElementById('date_end').value);
                
                if (endDate < startDate) {
                    event.preventDefault();
                    alert('End date must be after or equal to start date');
                }
            });
        });
    </script>
@endpush