@extends('../themes/' . $activeTheme)
@section('title', 'Edit License')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Licenses', 'url' => route('admin.licenses.index')],
['label' => 'Edit', 'active' => true],
];
@endphp

@section('subcontent')
<div>
    <!-- Flash Messages -->
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

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium">
            Edit License: {{ $license->current_license_number }}
        </h2>
        <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
            <x-base.button as="a" href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary">
                <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                Back to Licenses
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.licenses.show', $license->id) }}" class="btn btn-outline-primary ml-2">
                <x-base.lucide class="w-4 h-4 mr-1" icon="file-text" />
                View Documents
            </x-base.button>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="box box--stacked mt-5">
        <div class="box-body p-5">
            <form id="licenseForm" action="{{ route('admin.licenses.update', $license) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Sección 1: Información Básica -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Edit Information</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Carrier -->
                        <div>
                            <x-base.form-label for="carrier_id" class="form-label required">Carrier</x-base.form-label>
                            <x-base.form-select id="carrier_id" name="carrier_id" class="form-select @error('carrier_id') is-invalid @enderror" required>
                                <option value="">Select Carrier</option>
                                @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ old('carrier_id', $license->driverDetail->carrier_id) == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                            @error('carrier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Driver -->
                        <div>
                            <x-base.form-label for="user_driver_detail_id" class="form-label required">Driver</x-base.form-label>
                            <x-base.form-select id="user_driver_detail_id" name="user_driver_detail_id" class="form-select @error('user_driver_detail_id') is-invalid @enderror" required>
                                <option value="">Select Driver</option>
                                @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('user_driver_detail_id', $license->user_driver_detail_id) == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                            @error('user_driver_detail_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Información de Licencia -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">License Information</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Current License Number -->
                        <div>
                            <x-base.form-label for="current_license_number" class="form-label required">Current License Number</x-base.form-label>
                            <x-base.form-input type="text" id="current_license_number" name="current_license_number" class="form-control @error('current_license_number') is-invalid @enderror" value="{{ old('current_license_number', $license->current_license_number) }}" required />
                            @error('current_license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- License Number -->
                        <div>
                            <x-base.form-label for="license_number" class="form-label">License Number</x-base.form-label>
                            <x-base.form-input type="text" id="license_number" name="license_number" class="form-control @error('license_number') is-invalid @enderror" value="{{ old('license_number', $license->license_number) }}" />
                            @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- License Class -->
                        <div>
                            <x-base.form-label for="license_class" class="form-label">License Class</x-base.form-label>
                            <x-base.form-select id="license_class" name="license_class" class="form-select @error('license_class') is-invalid @enderror">
                                <option value="">Select License Class</option>
                                <option value="A" {{ old('license_class', $license->license_class) == 'A' ? 'selected' : '' }}>Class A</option>
                                <option value="B" {{ old('license_class', $license->license_class) == 'B' ? 'selected' : '' }}>Class B</option>
                                <option value="C" {{ old('license_class', $license->license_class) == 'C' ? 'selected' : '' }}>Class C</option>
                            </x-base.form-select>
                            @error('license_class')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- State of Issue -->
                        <div>
                            <x-base.form-label for="state_of_issue" class="form-label">State of Issue</x-base.form-label>
                            <x-base.form-select id="state_of_issue" name="state_of_issue" class="form-select @error('state_of_issue') is-invalid @enderror">
                                <option value="">Select State</option>
                                <option value="AL" {{ old('state_of_issue', $license->state_of_issue) == 'AL' ? 'selected' : '' }}>Alabama</option>
                                <option value="AK" {{ old('state_of_issue', $license->state_of_issue) == 'AK' ? 'selected' : '' }}>Alaska</option>
                                <option value="AZ" {{ old('state_of_issue', $license->state_of_issue) == 'AZ' ? 'selected' : '' }}>Arizona</option>
                                <option value="AR" {{ old('state_of_issue', $license->state_of_issue) == 'AR' ? 'selected' : '' }}>Arkansas</option>
                                <option value="CA" {{ old('state_of_issue', $license->state_of_issue) == 'CA' ? 'selected' : '' }}>California</option>
                                <option value="CO" {{ old('state_of_issue', $license->state_of_issue) == 'CO' ? 'selected' : '' }}>Colorado</option>
                                <option value="CT" {{ old('state_of_issue', $license->state_of_issue) == 'CT' ? 'selected' : '' }}>Connecticut</option>
                                <option value="DE" {{ old('state_of_issue', $license->state_of_issue) == 'DE' ? 'selected' : '' }}>Delaware</option>
                                <option value="FL" {{ old('state_of_issue', $license->state_of_issue) == 'FL' ? 'selected' : '' }}>Florida</option>
                                <option value="GA" {{ old('state_of_issue', $license->state_of_issue) == 'GA' ? 'selected' : '' }}>Georgia</option>
                                <option value="HI" {{ old('state_of_issue', $license->state_of_issue) == 'HI' ? 'selected' : '' }}>Hawaii</option>
                                <option value="ID" {{ old('state_of_issue', $license->state_of_issue) == 'ID' ? 'selected' : '' }}>Idaho</option>
                                <option value="IL" {{ old('state_of_issue', $license->state_of_issue) == 'IL' ? 'selected' : '' }}>Illinois</option>
                                <option value="IN" {{ old('state_of_issue', $license->state_of_issue) == 'IN' ? 'selected' : '' }}>Indiana</option>
                                <option value="IA" {{ old('state_of_issue', $license->state_of_issue) == 'IA' ? 'selected' : '' }}>Iowa</option>
                                <option value="KS" {{ old('state_of_issue', $license->state_of_issue) == 'KS' ? 'selected' : '' }}>Kansas</option>
                                <option value="KY" {{ old('state_of_issue', $license->state_of_issue) == 'KY' ? 'selected' : '' }}>Kentucky</option>
                                <option value="LA" {{ old('state_of_issue', $license->state_of_issue) == 'LA' ? 'selected' : '' }}>Louisiana</option>
                                <option value="ME" {{ old('state_of_issue', $license->state_of_issue) == 'ME' ? 'selected' : '' }}>Maine</option>
                                <option value="MD" {{ old('state_of_issue', $license->state_of_issue) == 'MD' ? 'selected' : '' }}>Maryland</option>
                                <option value="MA" {{ old('state_of_issue', $license->state_of_issue) == 'MA' ? 'selected' : '' }}>Massachusetts</option>
                                <option value="MI" {{ old('state_of_issue', $license->state_of_issue) == 'MI' ? 'selected' : '' }}>Michigan</option>
                                <option value="MN" {{ old('state_of_issue', $license->state_of_issue) == 'MN' ? 'selected' : '' }}>Minnesota</option>
                                <option value="MS" {{ old('state_of_issue', $license->state_of_issue) == 'MS' ? 'selected' : '' }}>Mississippi</option>
                                <option value="MO" {{ old('state_of_issue', $license->state_of_issue) == 'MO' ? 'selected' : '' }}>Missouri</option>
                                <option value="MT" {{ old('state_of_issue', $license->state_of_issue) == 'MT' ? 'selected' : '' }}>Montana</option>
                                <option value="NE" {{ old('state_of_issue', $license->state_of_issue) == 'NE' ? 'selected' : '' }}>Nebraska</option>
                                <option value="NV" {{ old('state_of_issue', $license->state_of_issue) == 'NV' ? 'selected' : '' }}>Nevada</option>
                                <option value="NH" {{ old('state_of_issue', $license->state_of_issue) == 'NH' ? 'selected' : '' }}>New Hampshire</option>
                                <option value="NJ" {{ old('state_of_issue', $license->state_of_issue) == 'NJ' ? 'selected' : '' }}>New Jersey</option>
                                <option value="NM" {{ old('state_of_issue', $license->state_of_issue) == 'NM' ? 'selected' : '' }}>New Mexico</option>
                                <option value="NY" {{ old('state_of_issue', $license->state_of_issue) == 'NY' ? 'selected' : '' }}>New York</option>
                                <option value="NC" {{ old('state_of_issue', $license->state_of_issue) == 'NC' ? 'selected' : '' }}>North Carolina</option>
                                <option value="ND" {{ old('state_of_issue', $license->state_of_issue) == 'ND' ? 'selected' : '' }}>North Dakota</option>
                                <option value="OH" {{ old('state_of_issue', $license->state_of_issue) == 'OH' ? 'selected' : '' }}>Ohio</option>
                                <option value="OK" {{ old('state_of_issue', $license->state_of_issue) == 'OK' ? 'selected' : '' }}>Oklahoma</option>
                                <option value="OR" {{ old('state_of_issue', $license->state_of_issue) == 'OR' ? 'selected' : '' }}>Oregon</option>
                                <option value="PA" {{ old('state_of_issue', $license->state_of_issue) == 'PA' ? 'selected' : '' }}>Pennsylvania</option>
                                <option value="RI" {{ old('state_of_issue', $license->state_of_issue) == 'RI' ? 'selected' : '' }}>Rhode Island</option>
                                <option value="SC" {{ old('state_of_issue', $license->state_of_issue) == 'SC' ? 'selected' : '' }}>South Carolina</option>
                                <option value="SD" {{ old('state_of_issue', $license->state_of_issue) == 'SD' ? 'selected' : '' }}>South Dakota</option>
                                <option value="TN" {{ old('state_of_issue', $license->state_of_issue) == 'TN' ? 'selected' : '' }}>Tennessee</option>
                                <option value="TX" {{ old('state_of_issue', $license->state_of_issue) == 'TX' ? 'selected' : '' }}>Texas</option>
                                <option value="UT" {{ old('state_of_issue', $license->state_of_issue) == 'UT' ? 'selected' : '' }}>Utah</option>
                                <option value="VT" {{ old('state_of_issue', $license->state_of_issue) == 'VT' ? 'selected' : '' }}>Vermont</option>
                                <option value="VA" {{ old('state_of_issue', $license->state_of_issue) == 'VA' ? 'selected' : '' }}>Virginia</option>
                                <option value="WA" {{ old('state_of_issue', $license->state_of_issue) == 'WA' ? 'selected' : '' }}>Washington</option>
                                <option value="WV" {{ old('state_of_issue', $license->state_of_issue) == 'WV' ? 'selected' : '' }}>West Virginia</option>
                                <option value="WI" {{ old('state_of_issue', $license->state_of_issue) == 'WI' ? 'selected' : '' }}>Wisconsin</option>
                                <option value="WY" {{ old('state_of_issue', $license->state_of_issue) == 'WY' ? 'selected' : '' }}>Wyoming</option>
                            </x-base.form-select>
                            @error('state_of_issue')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expiration Date -->
                        <div>
                            <x-base.form-label for="expiration_date" class="form-label required">Expiration Date</x-base.form-label>
                            <x-base.litepicker id="date_end" name="expiration_date" value="{{ old('expiration_date', $license->expiration_date ? $license->expiration_date->format('m/d/Y') : '') }}" class="@error('expiration_date') @enderror" placeholder="MM/DD/YYYY" required />
                            @error('expiration_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección 3: CDL y Endorsements -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">CDL Information</h4>
                    <div class="grid grid-cols-1 gap-6">
                        <!-- CDL Checkbox -->
                        <div>
                            <x-base.form-label class="form-label">Commercial Driver's License (CDL)</x-base.form-label>
                            <div class="flex items-center mb-2">
                                <input id="is_cdl" name="is_cdl" type="checkbox" value="1" {{ old('is_cdl', $license->is_cdl) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                <label for="is_cdl" class="form-check-label ml-2">
                                    This is a CDL License
                                </label>
                            </div>
                            @error('is_cdl')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CDL Endorsements -->
                        <div id="cdl_endorsements" class="{{ old('is_cdl', $license->is_cdl) ? '' : 'hidden' }}">
                            <x-base.form-label class="form-label">CDL Endorsements</x-base.form-label>
                            @php
                                $currentEndorsements = old('endorsements', $license->endorsements->pluck('code')->toArray());
                            @endphp
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                                <div class="flex items-center">
                                    <input id="endorsement_n" name="endorsement_n" type="checkbox" value="1" {{ in_array('N', $currentEndorsements) ? 'checked' : '' }}
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_n" class="form-check-label ml-2">
                                        N - Tank Vehicle
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_h" name="endorsement_h" type="checkbox" value="1" {{ in_array('H', $currentEndorsements) ? 'checked' : '' }}
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_h" class="form-check-label ml-2">
                                        H - Hazardous Materials
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_x" name="endorsement_x" type="checkbox" value="1" {{ in_array('X', $currentEndorsements) ? 'checked' : '' }}
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_x" class="form-check-label ml-2">
                                        X - Hazmat & Tank
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_t" name="endorsement_t" type="checkbox" value="1" {{ in_array('T', $currentEndorsements) ? 'checked' : '' }}
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_t" class="form-check-label ml-2">
                                        T - Double/Triple Trailers
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_p" name="endorsement_p" type="checkbox" value="1" {{ in_array('P', $currentEndorsements) ? 'checked' : '' }}
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_p" class="form-check-label ml-2">
                                        P - Passenger
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_s" name="endorsement_s" type="checkbox" value="1" {{ in_array('S', $currentEndorsements) ? 'checked' : '' }}
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_s" class="form-check-label ml-2">
                                        S - School Bus
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Documentos -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">License Images</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- License Front Image -->
                        <div>
                            <x-base.form-label for="license_front_image" class="form-label">License Front Image</x-base.form-label>
                            <x-base.form-input type="file" id="license_front_image" name="license_front_image" class="form-control @error('license_front_image') is-invalid @enderror" accept="image/*" />
                            <small class="form-text text-muted">Upload the front side of the driver's license</small>
                            @error('license_front_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <!-- Preview -->
                            <div id="front_image_preview" class="mt-2" style="display: none;">
                                <img id="front_preview_img" src="" alt="Front Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>

                        <!-- License Back Image -->
                        <div>
                            <x-base.form-label for="license_back_image" class="form-label">License Back Image</x-base.form-label>
                            <x-base.form-input type="file" id="license_back_image" name="license_back_image" class="form-control @error('license_back_image') is-invalid @enderror" accept="image/*" />
                            <small class="form-text text-muted">Upload the back side of the driver's license</small>
                            @error('license_back_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <!-- Preview -->
                            <div id="back_image_preview" class="mt-2" style="display: none;">
                                <img id="back_preview_img" src="" alt="Back Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Botones del formulario -->
                <div class="flex justify-end mt-8 space-x-4">
                    <x-base.button type="button" class="mr-3" variant="outline-secondary" as="a" href="{{ route('admin.licenses.index') }}">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary">
                        Save License
                    </x-base.button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    // Inicialización del formulario
    document.addEventListener('DOMContentLoaded', function() {
        // Almacenar archivos subidos del componente Livewire
        const licenseFilesInput = document.getElementById('license_files_input');
        let licenseFiles = [];

        // Inicializar con archivos existentes, si hay alguno
        @if(isset($existingFilesArray) && count($existingFilesArray) > 0)
        licenseFiles = @json($existingFilesArray);
        licenseFilesInput.value = JSON.stringify(licenseFiles);
        @endif

        // Escuchar eventos emitidos por el componente Livewire
        document.addEventListener('livewire:initialized', () => {
            // Este evento se dispara cuando se sube un nuevo archivo
            Livewire.on('fileUploaded', (data) => {
                const fileData = data[0];

                if (fileData.modelName === 'license_files') {
                    // Agregar el archivo al array
                    licenseFiles.push({
                        name: fileData.originalName,
                        original_name: fileData.originalName,
                        mime_type: fileData.mimeType,
                        size: fileData.size,
                        is_temp: true,
                        tempPath: fileData.tempPath,
                        path: fileData.tempPath,
                        id: fileData.previewData.id
                    });

                    // Actualizar el input hidden con los datos JSON
                    licenseFilesInput.value = JSON.stringify(licenseFiles);
                    console.log('Archivo agregado:', fileData.originalName);
                    console.log('Total archivos:', licenseFiles.length);
                }
            });

            // Este evento se dispara cuando se elimina un archivo
            Livewire.on('fileRemoved', (eventData) => {
                console.log('Evento fileRemoved recibido:', eventData);
                const data = eventData[0]; // Los datos vienen como primer elemento del array
                const fileId = data.fileId;

                // Verificar si el archivo es permanente (no temporal) y pertenece a nuestro modelo
                if (data.modelName === 'license_files' && !data.isTemp) {
                    console.log('Eliminando documento permanente con ID:', fileId);

                    // Hacer llamada AJAX para eliminar el documento físicamente
                    fetch(`{{ url('admin/licenses/document') }}/${fileId}/ajax`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                console.log('Documento eliminado con éxito de la base de datos');
                            } else {
                                console.error('Error al eliminar documento:', result.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error en la solicitud AJAX:', error);
                        });
                }

                // Encontrar y eliminar el archivo del array local (tanto temporales como permanentes)
                licenseFiles = licenseFiles.filter(file => file.id != fileId);

                // Actualizar el input hidden
                licenseFilesInput.value = JSON.stringify(licenseFiles);
                console.log('Archivo eliminado, ID:', fileId);
                console.log('Total archivos restantes:', licenseFiles.length);
            });
        });

        // Validar fecha de expiración
        document.getElementById('licenseForm').addEventListener('submit', function(event) {
            const expirationDateInput = document.getElementById('expiration_date');
            const expirationDateValue = expirationDateInput.value;

            if (expirationDateValue) {
                // Crear objeto Date para validación
                const expirationDate = new Date(expirationDateValue);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Resetear horas para comparar solo fechas

                // Verificar que la fecha sea válida
                if (isNaN(expirationDate.getTime())) {
                    event.preventDefault();
                    alert('Please enter a valid expiration date');
                    return;
                }

                // Verificar que la fecha de expiración no esté en el pasado
                if (expirationDate < today) {
                    event.preventDefault();
                    alert('Expiration date cannot be in the past');
                    return;
                }

                // Convertir fecha al formato YYYY-MM-DD para Laravel
                const formatDate = (date) => {
                    const d = new Date(date);
                    return d.getFullYear() + '-' +
                        ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                        ('0' + d.getDate()).slice(-2);
                };

                expirationDateInput.value = formatDate(expirationDateValue);
            }
        });

        // Manejar checkbox CDL
        const isCdlCheckbox = document.getElementById('is_cdl');
        if (isCdlCheckbox) {
            isCdlCheckbox.addEventListener('change', function() {
                const endorsementsSection = document.getElementById('cdl_endorsements');
                if (endorsementsSection) {
                    if (this.checked) {
                        endorsementsSection.classList.remove('hidden');
                    } else {
                        endorsementsSection.classList.add('hidden');
                        // Desmarcar todos los endorsements
                        const endorsementCheckboxes = endorsementsSection.querySelectorAll('input[type="checkbox"]');
                        endorsementCheckboxes.forEach(checkbox => {
                            checkbox.checked = false;
                        });
                    }
                }
            });
        }

        // Inicializar la visibilidad de la sección de endorsements al cargar la página
        const endorsementsSection = document.getElementById('cdl_endorsements');
        if (isCdlCheckbox && endorsementsSection) {
            // Forzar la inicialización correcta basada en el estado del checkbox
            if (isCdlCheckbox.checked) {
                endorsementsSection.classList.remove('hidden');
                console.log('CDL endorsements section shown on page load');
            } else {
                endorsementsSection.classList.add('hidden');
                console.log('CDL endorsements section hidden on page load');
            }
        }

        // Agregar event listeners para las previsualizaciones de imágenes
        const licenseFrontInput = document.getElementById('license_front_image');
        const licenseBackInput = document.getElementById('license_back_image');

        if (licenseFrontInput) {
            licenseFrontInput.addEventListener('change', function() {
                previewImage(this, 'front_image_preview', 'front_preview_img');
            });
        }

        if (licenseBackInput) {
            licenseBackInput.addEventListener('change', function() {
                previewImage(this, 'back_image_preview', 'back_preview_img');
            });
        }

        // Función para previsualizar imágenes
        function previewImage(input, previewId, imgId) {
            const preview = document.getElementById(previewId);
            const imgElement = document.getElementById(imgId);
            const file = input.files[0];

            if (file && preview && imgElement) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgElement.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Mostrar imágenes existentes al cargar la página
        @if($license->hasMedia('license_front'))
        const frontPreview = document.getElementById('front_image_preview');
        const frontImg = document.getElementById('front_preview_img');
        if (frontPreview && frontImg) {
            frontImg.src = '{{ $license->getFirstMediaUrl("license_front") }}';
            frontPreview.style.display = 'block';
        }
        @endif

        @if($license->hasMedia('license_back'))
        const backPreview = document.getElementById('back_image_preview');
        const backImg = document.getElementById('back_preview_img');
        if (backPreview && backImg) {
            backImg.src = '{{ $license->getFirstMediaUrl("license_back") }}';
            backPreview.style.display = 'block';
        }
        @endif

        // Manejar cambio de carrier para filtrar conductores
        const carrierSelect = document.getElementById('carrier_id');
        if (carrierSelect) {
            carrierSelect.addEventListener('change', function() {
                const carrierId = this.value;
                const currentDriverId = "{{ $license->user_driver_detail_id }}";

                // Limpiar el select de conductores usando JavaScript nativo
                const driverSelect = document.getElementById('user_driver_detail_id');
                driverSelect.innerHTML = '<option value="">Select Driver</option>';

                if (carrierId) {
                    // Hacer una petición AJAX para obtener los conductores activos de esta transportista
                    fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                // Hay conductores activos, agregarlos al select
                                let driverFound = false;

                                data.forEach(function(driver) {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.user.name} ${driver.user.last_name || ''}`;

                                    if (driver.id == currentDriverId) {
                                        option.selected = true;
                                        driverFound = true;
                                    }

                                    driverSelect.appendChild(option);
                                });

                                // Si el conductor actual no está en la lista (puede estar inactivo o pertenecer a otro carrier)
                                if (!driverFound && currentDriverId) {
                                    // Mantener el conductor actual como opción seleccionada
                                    // El backend ya se encarga de incluirlo en la lista de drivers
                                }
                            } else {
                                // No hay conductores activos para este carrier
                                const option = document.createElement('option');
                                option.value = '';
                                option.disabled = true;
                                option.textContent = 'No active drivers found for this carrier';
                                driverSelect.appendChild(option);
                            }

                            // Disparar un evento change para que se actualice la UI
                            driverSelect.dispatchEvent(new Event('change'));
                        })
                        .catch(error => {
                            console.error('Error loading drivers:', error);
                            const option = document.createElement('option');
                            option.value = '';
                            option.disabled = true;
                            option.textContent = 'Error loading drivers';
                            driverSelect.appendChild(option);
                            driverSelect.dispatchEvent(new Event('change'));
                        });
                }
            });
        }

        // Validar formulario con verificación null
        const licenseForm = document.getElementById('licenseForm');
        if (licenseForm) {
            // El event listener ya existe arriba, solo agregamos esta verificación
        }
    });
</script>
@endpush