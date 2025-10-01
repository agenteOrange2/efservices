@extends('../themes/' . $activeTheme)
@section('title', 'Add License')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Licenses', 'url' => route('admin.licenses.index')],
['label' => 'Add', 'active' => true],
];
@endphp

@section('subcontent')
<div>
    <!-- Mensajes flash -->
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Cabecera -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
        <h2 class="text-lg font-medium">
            Add new License
        </h2>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary" variant="primary">
                <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                Back to Licenses
            </x-base.button>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="box box--stacked mt-5">
        <div class="box-body p-5">
            <form id="licenseForm" action="{{ route('admin.licenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Sección 1: Información Básica -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Basic Information</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Carrier -->
                        <div>
                            <x-base.form-label for="carrier_id" class="form-label required">Carrier</x-base.form-label>
                            <x-base.form-select id="carrier_id" name="carrier_id" class="form-select @error('carrier_id') is-invalid @enderror" required>
                                <option value="">Select Carrier</option>
                                @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
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
                                <option value="{{ $driver->id }}" {{ old('user_driver_detail_id') == $driver->id ? 'selected' : '' }}>
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
                            <x-base.form-input type="text" id="current_license_number" name="current_license_number" class="form-control @error('current_license_number') is-invalid @enderror" value="{{ old('current_license_number') }}" required />
                            @error('current_license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- License Number -->
                        <div>
                            <x-base.form-label for="license_number" class="form-label">License Number</x-base.form-label>
                            <x-base.form-input type="text" id="license_number" name="license_number" class="form-control @error('license_number') is-invalid @enderror" value="{{ old('license_number') }}" />
                            @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- License Class -->
                        <div>
                            <x-base.form-label for="license_class" class="form-label">License Class</x-base.form-label>
                            <x-base.form-select id="license_class" name="license_class" class="form-select @error('license_class') is-invalid @enderror">
                                <option value="">Select License Class</option>
                                <option value="A" {{ old('license_class') == 'A' ? 'selected' : '' }}>Class A</option>
                                <option value="B" {{ old('license_class') == 'B' ? 'selected' : '' }}>Class B</option>
                                <option value="C" {{ old('license_class') == 'C' ? 'selected' : '' }}>Class C</option>
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
                                <option value="AL" {{ old('state_of_issue') == 'AL' ? 'selected' : '' }}>Alabama</option>
                                <option value="AK" {{ old('state_of_issue') == 'AK' ? 'selected' : '' }}>Alaska</option>
                                <option value="AZ" {{ old('state_of_issue') == 'AZ' ? 'selected' : '' }}>Arizona</option>
                                <option value="AR" {{ old('state_of_issue') == 'AR' ? 'selected' : '' }}>Arkansas</option>
                                <option value="CA" {{ old('state_of_issue') == 'CA' ? 'selected' : '' }}>California</option>
                                <option value="CO" {{ old('state_of_issue') == 'CO' ? 'selected' : '' }}>Colorado</option>
                                <option value="CT" {{ old('state_of_issue') == 'CT' ? 'selected' : '' }}>Connecticut</option>
                                <option value="DE" {{ old('state_of_issue') == 'DE' ? 'selected' : '' }}>Delaware</option>
                                <option value="FL" {{ old('state_of_issue') == 'FL' ? 'selected' : '' }}>Florida</option>
                                <option value="GA" {{ old('state_of_issue') == 'GA' ? 'selected' : '' }}>Georgia</option>
                                <option value="HI" {{ old('state_of_issue') == 'HI' ? 'selected' : '' }}>Hawaii</option>
                                <option value="ID" {{ old('state_of_issue') == 'ID' ? 'selected' : '' }}>Idaho</option>
                                <option value="IL" {{ old('state_of_issue') == 'IL' ? 'selected' : '' }}>Illinois</option>
                                <option value="IN" {{ old('state_of_issue') == 'IN' ? 'selected' : '' }}>Indiana</option>
                                <option value="IA" {{ old('state_of_issue') == 'IA' ? 'selected' : '' }}>Iowa</option>
                                <option value="KS" {{ old('state_of_issue') == 'KS' ? 'selected' : '' }}>Kansas</option>
                                <option value="KY" {{ old('state_of_issue') == 'KY' ? 'selected' : '' }}>Kentucky</option>
                                <option value="LA" {{ old('state_of_issue') == 'LA' ? 'selected' : '' }}>Louisiana</option>
                                <option value="ME" {{ old('state_of_issue') == 'ME' ? 'selected' : '' }}>Maine</option>
                                <option value="MD" {{ old('state_of_issue') == 'MD' ? 'selected' : '' }}>Maryland</option>
                                <option value="MA" {{ old('state_of_issue') == 'MA' ? 'selected' : '' }}>Massachusetts</option>
                                <option value="MI" {{ old('state_of_issue') == 'MI' ? 'selected' : '' }}>Michigan</option>
                                <option value="MN" {{ old('state_of_issue') == 'MN' ? 'selected' : '' }}>Minnesota</option>
                                <option value="MS" {{ old('state_of_issue') == 'MS' ? 'selected' : '' }}>Mississippi</option>
                                <option value="MO" {{ old('state_of_issue') == 'MO' ? 'selected' : '' }}>Missouri</option>
                                <option value="MT" {{ old('state_of_issue') == 'MT' ? 'selected' : '' }}>Montana</option>
                                <option value="NE" {{ old('state_of_issue') == 'NE' ? 'selected' : '' }}>Nebraska</option>
                                <option value="NV" {{ old('state_of_issue') == 'NV' ? 'selected' : '' }}>Nevada</option>
                                <option value="NH" {{ old('state_of_issue') == 'NH' ? 'selected' : '' }}>New Hampshire</option>
                                <option value="NJ" {{ old('state_of_issue') == 'NJ' ? 'selected' : '' }}>New Jersey</option>
                                <option value="NM" {{ old('state_of_issue') == 'NM' ? 'selected' : '' }}>New Mexico</option>
                                <option value="NY" {{ old('state_of_issue') == 'NY' ? 'selected' : '' }}>New York</option>
                                <option value="NC" {{ old('state_of_issue') == 'NC' ? 'selected' : '' }}>North Carolina</option>
                                <option value="ND" {{ old('state_of_issue') == 'ND' ? 'selected' : '' }}>North Dakota</option>
                                <option value="OH" {{ old('state_of_issue') == 'OH' ? 'selected' : '' }}>Ohio</option>
                                <option value="OK" {{ old('state_of_issue') == 'OK' ? 'selected' : '' }}>Oklahoma</option>
                                <option value="OR" {{ old('state_of_issue') == 'OR' ? 'selected' : '' }}>Oregon</option>
                                <option value="PA" {{ old('state_of_issue') == 'PA' ? 'selected' : '' }}>Pennsylvania</option>
                                <option value="RI" {{ old('state_of_issue') == 'RI' ? 'selected' : '' }}>Rhode Island</option>
                                <option value="SC" {{ old('state_of_issue') == 'SC' ? 'selected' : '' }}>South Carolina</option>
                                <option value="SD" {{ old('state_of_issue') == 'SD' ? 'selected' : '' }}>South Dakota</option>
                                <option value="TN" {{ old('state_of_issue') == 'TN' ? 'selected' : '' }}>Tennessee</option>
                                <option value="TX" {{ old('state_of_issue') == 'TX' ? 'selected' : '' }}>Texas</option>
                                <option value="UT" {{ old('state_of_issue') == 'UT' ? 'selected' : '' }}>Utah</option>
                                <option value="VT" {{ old('state_of_issue') == 'VT' ? 'selected' : '' }}>Vermont</option>
                                <option value="VA" {{ old('state_of_issue') == 'VA' ? 'selected' : '' }}>Virginia</option>
                                <option value="WA" {{ old('state_of_issue') == 'WA' ? 'selected' : '' }}>Washington</option>
                                <option value="WV" {{ old('state_of_issue') == 'WV' ? 'selected' : '' }}>West Virginia</option>
                                <option value="WI" {{ old('state_of_issue') == 'WI' ? 'selected' : '' }}>Wisconsin</option>
                                <option value="WY" {{ old('state_of_issue') == 'WY' ? 'selected' : '' }}>Wyoming</option>
                            </x-base.form-select>
                            @error('state_of_issue')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expiration Date -->
                        <div>
                            <x-base.form-label for="expiration_date" class="form-label required">Expiration Date</x-base.form-label>
                            <x-base.litepicker id="date_end" name="expiration_date" value="{{ old('expiration_date') }}" class="@error('expiration_date') @enderror" placeholder="MM/DD/YYYY" required />
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
                                <input id="is_cdl" name="is_cdl" type="checkbox" value="1" {{ old('is_cdl') ? 'checked' : '' }}
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                <label for="is_cdl" class="form-check-label ml-2">
                                    This is a CDL License
                                </label>
                            </div>
                            @error('is_cdl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CDL Endorsements (hidden by default) -->
                        <div id="cdl_endorsements" class="hidden">
                            <x-base.form-label class="form-label">CDL Endorsements</x-base.form-label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                                <div class="flex items-center">
                                    <input id="endorsement_n" name="endorsements[]" type="checkbox" value="N" {{ in_array('N', old('endorsements', [])) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_n" class="form-check-label ml-2">
                                        N - Tank Vehicle
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_h" name="endorsements[]" type="checkbox" value="H" {{ in_array('H', old('endorsements', [])) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_h" class="form-check-label ml-2">
                                        H - Hazardous Materials
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_x" name="endorsements[]" type="checkbox" value="X" {{ in_array('X', old('endorsements', [])) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_x" class="form-check-label ml-2">
                                        X - Hazmat & Tank
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_t" name="endorsements[]" type="checkbox" value="T" {{ in_array('T', old('endorsements', [])) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_t" class="form-check-label ml-2">
                                        T - Double/Triple Trailers
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_p" name="endorsements[]" type="checkbox" value="P" {{ in_array('P', old('endorsements', [])) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                    <label for="endorsement_p" class="form-check-label ml-2">
                                        P - Passenger
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="endorsement_s" name="endorsements[]" type="checkbox" value="S" {{ in_array('S', old('endorsements', [])) ? 'checked' : '' }}
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
@endsection

@push('scripts')
    <script>
        // Inicialización del formulario
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar checkbox CDL y mostrar/ocultar endorsements
            const cdlCheckbox = document.getElementById('is_cdl');
            const endorsementsSection = document.getElementById('cdl_endorsements');
            
            function toggleEndorsements() {
                if (cdlCheckbox.checked) {
                    endorsementsSection.classList.remove('hidden');
                } else {
                    endorsementsSection.classList.add('hidden');
                    // Desmarcar todos los endorsements cuando se desmarca CDL
                    const endorsementCheckboxes = endorsementsSection.querySelectorAll('input[type="checkbox"]');
                    endorsementCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                }
            }
            
            // Inicializar estado de endorsements
            toggleEndorsements();
            
            // Escuchar cambios en el checkbox CDL
            cdlCheckbox.addEventListener('change', toggleEndorsements);
            
            // Manejar preview de imágenes
            function setupImagePreview(inputId, previewId, imgId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                const img = document.getElementById(imgId);
                
                input.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.style.display = 'none';
                    }
                });
            }
            
            // Configurar previews para ambas imágenes
            setupImagePreview('license_front_image', 'front_image_preview', 'front_preview_img');
            setupImagePreview('license_back_image', 'back_image_preview', 'back_preview_img');
            
            // Validación de fecha de expiración
            document.getElementById('licenseForm').addEventListener('submit', function(event) {
                const expirationDateEl = document.getElementById('expiration_date');
                
                // Verificar que la fecha de expiración no sea en el pasado
                if (expirationDateEl.value) {
                    const expirationDate = new Date(expirationDateEl.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (expirationDate < today) {
                        event.preventDefault();
                        alert('Expiration date cannot be in the past');
                        return;
                    }
                }
            });
            
            // Manejar cambio de carrier para filtrar conductores
            document.getElementById('carrier_id').addEventListener('change', function() {
                const carrierId = this.value;
                
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
                                data.forEach(function(driver) {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.user.name} ${driver.user.last_name || ''}`;
                                    driverSelect.appendChild(option);
                                });
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
        });
    </script>
@endpush