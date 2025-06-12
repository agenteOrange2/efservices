@extends('../themes/' . $activeTheme)
@section('title', 'Create New Drug Test')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Testing Drugs Management', 'url' => route('admin.driver-testings.index')],
        ['label' => 'Create New Test', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div>
        <!-- Mensajes Flash -->
        @if (session()->has('error'))
            <div class="alert alert-danger flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-triangle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Create New Drug Test
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('admin.driver-testings.index') }}">
                    <x-base.button variant="outline-secondary" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                        Back to List
                    </x-base.button>
                </a>
            </div>
        </div>

        <!-- Formulario y Selección de Carrier/Driver -->
        <div class="grid grid-cols-12 gap-6 mt-5">
            <!-- Panel de Selección de Carrier y Driver -->
            <div class="col-span-12 xl:col-span-4">
                <div class="box box--stacked p-5">
                    <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                        <div class="font-medium truncate text-base mr-5">Select Carrier & Driver</div>
                    </div>

                    <!-- Selección de Carrier -->
                    <div class="mb-5">
                        <label for="carrier_id" class="form-label">Carrier <span class="text-danger">*</span></label>
                        <select id="carrier_id" name="carrier_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">-- Select a carrier --</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}">
                                    {{ $carrier->name }} (USDOT: {{ $carrier->usdot ?: 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selección de Driver -->
                    <div class="mb-5">
                        <label for="user_driver_detail_id" class="form-label">Driver <span
                                class="text-danger">*</span></label>
                        <select id="user_driver_detail_id" name="user_driver_detail_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" disabled required>
                            <option value="">-- Select a driver --</option>
                        </select>
                        <div id="driver-loading" class="mt-2 hidden">
                            <div class="flex items-center">
                                <div class="w-4 h-4 animate-spin mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                                    </svg>
                                </div>
                                <span class="text-xs text-slate-500">Loading drivers...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Details Card -->
                    <div id="driver-detail-card" class="card border shadow-sm mt-4 hidden p-3">
                        <div class="card-header">
                            <h3 class="font-medium text-base">Driver Details</h3>
                        </div>
                        <div class="card-body ">
                            <div class="mb-2">
                                <span class="text-gray-500">Full Name</span><br>
                                <span id="driver-name" class="font-medium">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-gray-500">Email</span><br>
                                <span id="driver-email" class="font-medium">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-gray-500">Phone</span><br>
                                <span id="driver-phone" class="font-medium">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-gray-500">License Information</span><br>
                                <span id="driver-license" class="font-medium">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-gray-500">License Class</span><br>
                                <span id="driver-license-class" class="font-medium">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-gray-500">License Expiration</span><br>
                                <span id="driver-license-expiration" class="font-medium">-</span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel del Formulario Principal -->
            <div class="col-span-12 xl:col-span-8">
                <div class="box box--stacked p-3">
                    <div class="box-header box-header--transparent">
                        <div class="box-title mb-5">Drug & Alcohol Test Details</div>
                    </div>
                    <div class="box-body">
                        <form action="{{ route('admin.driver-testings.store') }}" method="POST" id="create-test-form"
                            enctype="multipart/form-data">
                            @csrf
                            <!-- Campos ocultos para datos seleccionados -->
                            <input type="hidden" name="carrier_id" id="carrier_id_hidden">
                            <input type="hidden" name="user_driver_detail_id" id="user_driver_detail_id_hidden">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Tipo de Prueba -->
                                <div>
                                    <label for="test_type" class="form-label">Test Type</label>
                                    <select name="test_type" id="test_type"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                        required>
                                        <option value="">-- Select test type --</option>
                                        @foreach ($testTypes as $testType)
                                            <option value="{{ $testType }}">{{ $testType }}</option>
                                        @endforeach
                                    </select>
                                    @error('test_type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Administered By -->
                                <div x-data="{ showOtherField: '{{ old('administered_by') }}' === 'other' }">
                                    <label for="administered_by_select" class="form-label">Administered By <span
                                            class="text-danger">*</span></label>
                                    <select id="administered_by_select"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                        required x-on:change="showOtherField = $event.target.value === 'other'">
                                        <option value="">-- Select administrator --</option>
                                        @foreach (\App\Models\Admin\Driver\DriverTesting::getAdministrators() as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('administered_by') == $value && $value != 'other' ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <!-- Campo oculto que almacenará el valor real a enviar -->
                                    <input type="hidden" id="administered_by" name="administered_by"
                                        value="{{ old('administered_by') }}">
                                    <div id="administered_by_other_container" class="mt-2" x-show="showOtherField">
                                        <input type="text" id="administered_by_other"
                                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                            placeholder="Please specify"
                                            value="{{ old('administered_by_other', old('administered_by') != 'other' ? old('administered_by') : '') }}"
                                            x-bind:required="showOtherField">
                                    </div>
                                    @error('administered_by')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const selectElement = document.getElementById('administered_by_select');
                                        const hiddenInput = document.getElementById('administered_by');
                                        const otherInput = document.getElementById('administered_by_other');
                                        const formElement = document.getElementById('create-test-form');

                                        // Inicializar el select con el valor correcto
                                        if (hiddenInput.value && hiddenInput.value !== 'other') {
                                            // Verificar si el valor está en las opciones predefinidas
                                            let found = false;
                                            for (let i = 0; i < selectElement.options.length; i++) {
                                                if (selectElement.options[i].value === hiddenInput.value) {
                                                    selectElement.selectedIndex = i;
                                                    found = true;
                                                    break;
                                                }
                                            }

                                            // Si no está en las opciones, es un valor personalizado
                                            if (!found) {
                                                selectElement.value = 'other';
                                                otherInput.value = hiddenInput.value;
                                            }
                                        }

                                        // Manejar cambios en el select
                                        selectElement.addEventListener('change', function() {
                                            if (this.value === 'other') {
                                                // Si se selecciona 'other', el valor real será lo que se escriba en el campo de texto
                                                hiddenInput.value = otherInput.value || 'other';
                                            } else {
                                                // Si se selecciona otra opción, usar ese valor
                                                hiddenInput.value = this.value;
                                            }
                                        });

                                        // Manejar cambios en el campo de texto 'other'
                                        otherInput.addEventListener('input', function() {
                                            if (selectElement.value === 'other') {
                                                hiddenInput.value = this.value || 'other';
                                            }
                                        });

                                        // Validar el formulario antes de enviar
                                        formElement.addEventListener('submit', function(e) {
                                            // Verificar que se haya seleccionado un carrier
                                            const carrierId = document.getElementById('carrier_id').value;
                                            if (!carrierId) {
                                                e.preventDefault();
                                                alert('Please select a carrier');
                                                return false;
                                            }

                                            // Verificar que se haya seleccionado un driver
                                            const driverId = document.getElementById('user_driver_detail_id').value;
                                            if (!driverId) {
                                                e.preventDefault();
                                                alert('Please select a driver');
                                                return false;
                                            }

                                            // Si se seleccionó 'other', verificar que se haya ingresado un valor en el campo de texto
                                            if (selectElement.value === 'other') {
                                                const otherValue = otherInput.value.trim();
                                                if (!otherValue) {
                                                    e.preventDefault();
                                                    alert('Please specify who administered the test');
                                                    return false;
                                                }
                                                // Asegurarse de que el valor del campo oculto sea el texto ingresado
                                                hiddenInput.value = otherValue;
                                            }
                                        });
                                    });
                                </script>

                                <!-- Fecha del Test -->
                                <div>
                                    <label for="test_date" class="form-label">Test Date <span
                                            class="text-danger">*</span></label>
                                    {{-- <input type="date" id="test_date" name="test_date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('test_date') is-invalid @enderror" value="{{ old('test_date', date('Y-m-d')) }}" required> --}}
                                    <x-base.litepicker id="date_to" name="date_to" class="w-full"
                                        value="{{ request('date_to') }}" />

                                    @error('test_date')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Ubicación -->
                                <div>
                                    <label for="location" class="form-label">Location <span
                                            class="text-danger">*</span></label>
                                    <select id="location" name="location"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('location') is-invalid @enderror"
                                        required>
                                        <option value="">-- Select location --</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location }}"
                                                {{ old('location') == $location ? 'selected' : '' }}>
                                                {{ $location }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Requester Name -->
                                <div>
                                    <label for="requester_name" class="form-label">Requester Name</label>
                                    <input type="text" id="requester_name" name="requester_name"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('requester_name') is-invalid @enderror"
                                        value="{{ old('requester_name') }}">
                                    @error('requester_name')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- MRO -->
                                <div>
                                    <label for="mro" class="form-label">MRO</label>
                                    <input type="text" id="mro" name="mro"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('mro') is-invalid @enderror"
                                        value="{{ old('mro') }}">
                                    @error('mro')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Scheduled Time -->
                                <div>
                                    <label for="scheduled_time" class="form-label">Scheduled Time</label>
                                    <input type="datetime-local" id="scheduled_time" name="scheduled_time"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('scheduled_time') is-invalid @enderror"
                                        value="{{ old('scheduled_time') }}">
                                    @error('scheduled_time')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Solicita la prueba -->
                                <div>
                                    <label for="requester_name" class="form-label">Test Requested By</label>
                                    <input type="text" id="requester_name" name="requester_name"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('requester_name') is-invalid @enderror"
                                        value="{{ old('requester_name') }}"
                                        placeholder="Name of person requesting the test">
                                    @error('requester_name')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Test Result -->
                                <div>
                                    <label for="test_result" class="form-label">Test Result</label>
                                    <select id="test_result" name="test_result"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('test_result') is-invalid @enderror">
                                        <option value="">-- Select result --</option>
                                        <option value="Negative" {{ old('test_result') == 'Negative' ? 'selected' : '' }}>
                                            Negative</option>
                                        <option value="Positive" {{ old('test_result') == 'Positive' ? 'selected' : '' }}>
                                            Positive</option>
                                        <option value="Inconclusive"
                                            {{ old('test_result') == 'Inconclusive' ? 'selected' : '' }}>Inconclusive
                                        </option>
                                        <option value="Refused" {{ old('test_result') == 'Refused' ? 'selected' : '' }}>
                                            Refused</option>
                                        <option value="Pending"
                                            {{ old('test_result', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending
                                        </option>
                                    </select>
                                    @error('test_result')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="form-label">Status</label>
                                    <select id="status" name="status"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('status') is-invalid @enderror">
                                        @foreach (\App\Models\Admin\Driver\DriverTesting::getStatuses() as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ old('status', 'active') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Next Test Due -->
                                <div>
                                    <label for="next_test_due" class="form-label">Next Test Due Date</label>
                                    <x-base.litepicker id="next_test_due" name="next_test_due" class="w-full"
                                        value="{{ old('next_test_due') }}" />
                                    @error('next_test_due')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Bill To -->
                                <div>
                                    <label for="bill_to" class="form-label">Bill To <span
                                            class="text-danger">*</span></label>
                                    <select id="bill_to" name="bill_to"
                                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('bill_to') is-invalid @enderror"
                                        required>
                                        <option value="">-- Select billing option --</option>
                                        @foreach ($billOptions as $option)
                                            <option value="{{ $option }}"
                                                {{ old('bill_to') == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bill_to')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tipo de Test Checkboxes -->
                            <div class="mt-6">
                                <div class="font-medium mb-2">Test Details</div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_random_test" name="is_random_test" value="1"
                                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2" {{ old('is_random_test') ? 'checked' : '' }}>
                                        <label for="is_random_test" class="cursor-pointer">Random Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_post_accident_test" name="is_post_accident_test"
                                            value="1" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                            {{ old('is_post_accident_test') ? 'checked' : '' }}>
                                        <label for="is_post_accident_test" class="cursor-pointer">Post-Accident
                                            Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_reasonable_suspicion_test"
                                            name="is_reasonable_suspicion_test" value="1" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                            {{ old('is_reasonable_suspicion_test') ? 'checked' : '' }}>
                                        <label for="is_reasonable_suspicion_test" class="cursor-pointer">Reasonable
                                            Suspicion Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_pre_employment_test" name="is_pre_employment_test"
                                            value="1" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                            {{ old('is_pre_employment_test') ? 'checked' : '' }}>
                                        <label for="is_pre_employment_test" class="cursor-pointer">Pre-Employment
                                            Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_follow_up_test" name="is_follow_up_test"
                                            value="1" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                            {{ old('is_follow_up_test') ? 'checked' : '' }}>
                                        <label for="is_follow_up_test" class="cursor-pointer">Follow-Up Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_return_to_duty_test" name="is_return_to_duty_test"
                                            value="1" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                            {{ old('is_return_to_duty_test') ? 'checked' : '' }}>
                                        <label for="is_return_to_duty_test" class="cursor-pointer">Return-To-Duty
                                            Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_other_reason_test" name="is_other_reason_test"
                                            value="1" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                            {{ old('is_other_reason_test') ? 'checked' : '' }}>
                                        <label for="is_other_reason_test" class="cursor-pointer">Other Reason</label>
                                    </div>
                                </div>
                                <!-- Campo de descripción para Other Reason -->
                                <div id="other_reason_container" class="w-full" style="display: none;">
                                    <input type="text" id="other_reason_description" name="other_reason_description"
                                        class="w-full mt-3 text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                        placeholder="Specify other reason" value="{{ old('other_reason_description') }}">
                                </div>
                            </div>

                            <!-- Notas -->
                            <div class="mt-6">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" rows="3"
                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Document Upload with Livewire component -->
                            <div class="mt-6">
                                <div class="font-medium mb-2">Upload Files</div>
                                <livewire:components.file-uploader model-name="driver_testing_files" :model-index="0"
                                    :auto-upload="true"
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer" />
                                <!-- Campo oculto para almacenar los archivos subidos -->
                                <input type="hidden" name="driver_testing_files" id="driver_testing_files_input">
                                @error('driver_testing_files')
                                    <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="btn btn-primary">
                                    <x-base.lucide icon="save" class="w-4 h-4 mr-2" />
                                    Create Drug Test
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Referencias a elementos DOM - Verificando que existan
            const carrierSelect = document.getElementById('carrier_id');
            const driverSelect = document.getElementById('user_driver_detail_id');
            const driverLoading = document.getElementById('driver-loading');
            const driverDetailCard = document.getElementById('driver-detail-card');
            const driverName = document.getElementById('driver-name');
            const driverEmail = document.getElementById('driver-email');
            const driverPhone = document.getElementById('driver-phone');
            const driverLicense = document.getElementById('driver-license');
            const driverLicenseClass = document.getElementById('driver-license-class');
            const driverLicenseExpiration = document.getElementById('driver-license-expiration');
            const carrierIdHidden = document.getElementById('carrier_id_hidden');
            const userDriverDetailIdHidden = document.getElementById('user_driver_detail_id_hidden');

            // Verificar que todos los elementos existan
            console.log('DOM Elements:', {
                carrierSelect: !!carrierSelect,
                driverSelect: !!driverSelect,
                driverDetailCard: !!driverDetailCard,
                driverName: !!driverName,
                driverEmail: !!driverEmail,
                driverPhone: !!driverPhone,
                driverLicense: !!driverLicense,
                driverLicenseClass: !!driverLicenseClass,
                driverLicenseExpiration: !!driverLicenseExpiration
            });

            // Cargar drivers cuando se selecciona un carrier
            carrierSelect.addEventListener('change', function() {
                const carrierId = this.value;
                carrierIdHidden.value = carrierId;

                // Limpiar y deshabilitar el select de drivers si no hay carrier seleccionado
                if (!carrierId) {
                    driverSelect.innerHTML = '<option value="">-- Select a driver --</option>';
                    driverSelect.disabled = true;
                    driverDetailCard.classList.add('hidden');
                    return;
                }

                // Mostrar indicador de carga
                driverSelect.disabled = true;
                driverLoading.classList.remove('hidden');
                driverSelect.innerHTML = '<option value="">Loading drivers...</option>';

                // Cargar conductores para el carrier seleccionado
                fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Drivers data received:', data);
                        // Guardar datos para uso posterior
                        window.driversData = data;

                        // Limpiar el select
                        driverSelect.innerHTML = '<option value="">-- Select a driver --</option>';
                        driverSelect.disabled = false;

                        if (data && data.length > 0) {
                            // Agregar opciones al select
                            data.forEach(driver => {
                                const option = document.createElement('option');
                                option.value = driver.id;

                                // Crear nombre completo del conductor
                                // El nombre (first name) viene de la tabla users
                                const firstName = driver.user ? driver.user.name || '' : '';
                                // El middle_name y last_name vienen directamente de la tabla user_driver_details
                                const middleName = driver.middle_name || '';
                                const lastName = driver.last_name || '';

                                const fullName = `${firstName} ${middleName} ${lastName}`
                                    .replace(/\s+/g, ' ').trim();
                                option.textContent = fullName;

                                // Obtener la licencia activa (la primera en la colección, ya que están ordenadas por created_at desc)
                                let licenseInfo = 'N/A';
                                let licenseClass = '';
                                let licenseExpiration = '';

                                if (driver.licenses && driver.licenses.length > 0) {
                                    const activeLicense = driver.licenses[0];
                                    licenseInfo = activeLicense.license_number || 'N/A';
                                    licenseClass = activeLicense.license_class || '';
                                    licenseExpiration = activeLicense.expiration_date || '';

                                    // Formatear la fecha de expiración si existe
                                    if (licenseExpiration) {
                                        const expDate = new Date(licenseExpiration);
                                        licenseExpiration = expDate.toLocaleDateString();
                                    }
                                }

                                // Para depuración
                                console.log('Driver data structure:', {
                                    id: driver.id,
                                    user_name: driver.user ? driver.user.name : null,
                                    middle_name: driver.middle_name,
                                    last_name: driver.last_name,
                                    phone: driver.phone,
                                    license: licenseInfo
                                });

                                // Guardar datos adicionales como atributos data-*
                                option.setAttribute('data-email', driver.user ? driver.user
                                    .email || '' : '');
                                option.setAttribute('data-phone', driver.phone || '');
                                option.setAttribute('data-license', licenseInfo);
                                option.setAttribute('data-license-class', licenseClass);
                                option.setAttribute('data-license-expiration',
                                    licenseExpiration);

                                // Guardar componentes del nombre por separado para mejor formato en la tarjeta
                                option.setAttribute('data-first-name', firstName);
                                option.setAttribute('data-middle-name', middleName);
                                option.setAttribute('data-last-name', lastName);

                                // Verificar que los atributos se hayan establecido correctamente
                                console.log('Driver option:', {
                                    id: driver.id,
                                    name: fullName,
                                    email: option.getAttribute('data-email'),
                                    phone: option.getAttribute('data-phone'),
                                    license: option.getAttribute('data-license'),
                                    licenseClass: option.getAttribute(
                                        'data-license-class'),
                                    licenseExpiration: option.getAttribute(
                                        'data-license-expiration')
                                });

                                driverSelect.appendChild(option);
                            });
                        } else {
                            // Agregar un mensaje si no hay drivers
                            const option = document.createElement('option');
                            option.value = '';
                            option.disabled = true;
                            option.textContent = 'No active drivers found for this carrier';
                            driverSelect.appendChild(option);
                        }

                        driverLoading.classList.add('hidden');
                    })
                    .catch(error => {
                        console.error('Error loading drivers:', error);
                        driverLoading.classList.add('hidden');
                        driverSelect.innerHTML = '<option value="">Error loading drivers</option>';
                    });
            });

            // Si hay un valor previo para driver (por ejemplo, después de un error de validación), seleccionarlo
            if ('{{ old('user_driver_detail_id') }}') {
                // Esperar a que se carguen los drivers si es necesario
                const checkDriversLoaded = setInterval(() => {
                    if (window.driversData && window.driversData.length > 0) {
                        clearInterval(checkDriversLoaded);
                        driverSelect.value = '{{ old('user_driver_detail_id') }}';
                        // Disparar el evento change para mostrar los detalles del conductor
                        const event = new Event('change');
                        driverSelect.dispatchEvent(event);
                    }
                }, 500);
            }

            // Evento al cambiar el driver - usando datos completos
            driverSelect.addEventListener('change', function() {
                const driverId = this.value;
                userDriverDetailIdHidden.value = driverId;

                if (!driverId) {
                    driverDetailCard.classList.add('hidden');
                    return;
                }

                try {
                    // Obtener los datos del driver seleccionado
                    const driverSelectedOption = this.options[this.selectedIndex];
                    console.log('Selected driver option:', driverSelectedOption);

                    // Verificar que la opción tenga los atributos data-*
                    console.log('Option attributes:', {
                        email: driverSelectedOption.getAttribute('data-email'),
                        phone: driverSelectedOption.getAttribute('data-phone'),
                        license: driverSelectedOption.getAttribute('data-license')
                    });

                    // Obtener el nombre completo formateado correctamente
                    const driverNameText = driverSelectedOption.textContent;

                    // Obtener el resto de datos del conductor
                    const driverEmailText = driverSelectedOption.getAttribute('data-email') || 'N/A';
                    const driverPhoneText = driverSelectedOption.getAttribute('data-phone') || 'N/A';
                    const driverLicenseText = driverSelectedOption.getAttribute('data-license') || 'N/A';
                    const driverLicenseClassText = driverSelectedOption.getAttribute(
                        'data-license-class') || 'N/A';
                    const driverLicenseExpirationText = driverSelectedOption.getAttribute(
                        'data-license-expiration') || 'N/A';

                    // Obtener componentes del nombre por separado para mostrarlos formateados
                    // Estos datos vienen correctamente de los atributos data-* que establecimos
                    const firstName = driverSelectedOption.getAttribute('data-first-name') || '';
                    const middleName = driverSelectedOption.getAttribute('data-middle-name') || '';
                    const lastName = driverSelectedOption.getAttribute('data-last-name') || '';

                    console.log('Nombre completo componentes:', {
                        firstName,
                        middleName,
                        lastName
                    });

                    // Crear un nombre formateado para mostrar
                    const formattedName = [
                        firstName,
                        middleName ? `<span class="text-gray-700">${middleName}</span>` : '',
                        lastName ? `<span class="font-semibold">${lastName}</span>` : ''
                    ].filter(Boolean).join(' ');

                    // Formatear la información de la licencia
                    const licenseInfo = driverLicenseText !== 'N/A' ?
                        `${driverLicenseText}` : 'N/A';

                    console.log('Driver details to display:', {
                        name: driverNameText,
                        email: driverEmailText,
                        phone: driverPhoneText,
                        license: licenseInfo,
                        licenseClass: driverLicenseClassText,
                        licenseExpiration: driverLicenseExpirationText
                    });

                    // Actualizar la tarjeta de detalles - verificando que los elementos existan
                    if (driverName) driverName.innerHTML = formattedName || driverNameText;
                    if (driverEmail) driverEmail.textContent = driverEmailText;
                    if (driverPhone) driverPhone.textContent = driverPhoneText;
                    if (driverLicense) driverLicense.textContent = licenseInfo;
                    if (driverLicenseClass) driverLicenseClass.textContent = driverLicenseClassText;
                    if (driverLicenseExpiration) driverLicenseExpiration.textContent =
                        driverLicenseExpirationText;

                    // Mostrar la tarjeta de detalles
                    if (driverDetailCard) driverDetailCard.classList.remove('hidden');
                } catch (error) {
                    console.error('Error displaying driver details:', error);
                }
            });

            // Validar formulario antes de enviar
            const createTestForm = document.getElementById('create-test-form');
            createTestForm.addEventListener('submit', function(e) {
                // Verificar que se haya seleccionado un carrier
                const carrierId = document.getElementById('carrier_id').value;
                if (!carrierId) {
                    e.preventDefault();
                    alert('Please select a carrier');
                    return false;
                }

                // Verificar que se haya seleccionado un driver
                const driverId = document.getElementById('user_driver_detail_id').value;
                if (!driverId) {
                    e.preventDefault();
                    alert('Please select a driver');
                    return false;
                }

                // Actualizar campos ocultos
                carrierIdHidden.value = carrierId;
                userDriverDetailIdHidden.value = driverId;

                return true;
            });

            // Control de visibilidad para el campo Other Reason Description
            const otherReasonCheckbox = document.getElementById('is_other_reason_test');
            const otherReasonContainer = document.getElementById('other_reason_container');

            // Función para manejar la visibilidad del campo de descripción
            function toggleOtherReasonField() {
                if (otherReasonCheckbox.checked) {
                    otherReasonContainer.style.display = 'block';
                } else {
                    otherReasonContainer.style.display = 'none';
                }
            }

            // Manejar cambio en el checkbox
            otherReasonCheckbox.addEventListener('change', toggleOtherReasonField);

            // Inicializar estado al cargar la página
            toggleOtherReasonField();

            // Inicializar array para archivos subidos
            let uploadedFiles = [];
            const driverTestingFilesInput = document.getElementById('driver_testing_files_input');
            driverTestingFilesInput.value = JSON.stringify(uploadedFiles);

            // Escuchar eventos de Livewire 3
            window.addEventListener('livewire:initialized', () => {
                console.log('Livewire 3 initialized - registering event listeners');

                // Escuchar el evento fileUploaded del componente Livewire
                Livewire.on('fileUploaded', (eventData) => {
                    console.log('Archivo subido:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array

                    if (data.modelName === 'driver_testing_files') {
                        // Añadir el archivo al array con la estructura correcta que espera el controlador
                        uploadedFiles.push({
                            path: data
                                .tempPath, // Mantener el nombre que envía el componente
                            original_name: data
                                .originalName, // Mantener el nombre que envía el componente
                            mime_type: data.mimeType,
                            size: data.size
                        });

                        // Actualizar el campo oculto con el nuevo array
                        driverTestingFilesInput.value = JSON.stringify(uploadedFiles);
                        console.log('Archivos actualizados:', driverTestingFilesInput.value);
                    }
                });

                // Escuchar el evento fileRemoved del componente Livewire
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Archivo eliminado:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array

                    if (data.modelName === 'driver_testing_files') {
                        // Eliminar el archivo del array
                        const fileId = data.fileId;
                        uploadedFiles = uploadedFiles.filter((file, index) => {
                            // Para archivos temporales, el ID contiene un timestamp
                            if (fileId.startsWith('temp_') && index === uploadedFiles
                                .length - 1) {
                                // Eliminar el último archivo añadido si es temporal
                                return false;
                            }
                            return true;
                        });

                        // Actualizar el campo oculto con el nuevo array
                        driverTestingFilesInput.value = JSON.stringify(uploadedFiles);
                        console.log('Archivos actualizados después de eliminar:',
                            driverTestingFilesInput.value);
                    }
                });
            });
        });
    </script>
@endpush
