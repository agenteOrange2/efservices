@extends('../themes/' . $activeTheme)
@section('title', 'Edit Driver')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('carrier.dashboard')],
        ['label' => 'Drivers', 'url' => route('carrier.drivers.index')],
        ['label' => 'Edit Driver', 'active' => true],
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
                Edit Driver: {{ $driver->user->name }} {{ $driver->last_name }}
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('carrier.drivers.show', $driver->id) }}" class="btn btn-outline-secondary flex items-center mr-3">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                    View Driver
                </a>
                <a href="{{ route('carrier.drivers.index') }}" class="btn btn-outline-secondary flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to Drivers List
                </a>
            </div>
        </div>

        <!-- Formulario con pasos -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h2 class="box-title">Driver Information</h2>
            </div>
            <div class="box-body p-5">
                <!-- Barra de progreso -->
                <div class="w-full bg-slate-200 rounded-full h-3 mb-5">
                    <div id="progress-bar" class="bg-primary h-3 rounded-full" style="width: 33%"></div>
                </div>

                <!-- Pasos -->
                <div class="flex justify-between mb-5">
                    <div class="flex flex-col items-center">
                        <div id="step1-indicator" class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-medium">1</div>
                        <div class="text-sm mt-1">Personal Info</div>
                    </div>
                    <div class="flex flex-col items-center">
                        <div id="step2-indicator" class="w-10 h-10 rounded-full bg-slate-300 text-slate-600 flex items-center justify-center font-medium">2</div>
                        <div class="text-sm mt-1">License Info</div>
                    </div>
                    <div class="flex flex-col items-center">
                        <div id="step3-indicator" class="w-10 h-10 rounded-full bg-slate-300 text-slate-600 flex items-center justify-center font-medium">3</div>
                        <div class="text-sm mt-1">Employment Info</div>
                    </div>
                </div>

                <form id="driver-form" action="{{ route('carrier.drivers.update', $driver->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Paso 1: Información Personal -->
                    <div id="step1" class="step-content">
                        <div class="grid grid-cols-12 gap-4 gap-y-5">
                            <div class="col-span-12">
                                <h3 class="text-lg font-medium">Personal Information</h3>
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="first_name">First Name</x-base.form-label>
                                <x-base.form-input id="first_name" name="first_name" type="text" value="{{ old('first_name', $driver->user->name) }}" required />
                                @error('first_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="last_name">Last Name</x-base.form-label>
                                <x-base.form-input id="last_name" name="last_name" type="text" value="{{ old('last_name', $driver->last_name) }}" required />
                                @error('last_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="email">Email</x-base.form-label>
                                <x-base.form-input id="email" name="email" type="email" value="{{ old('email', $driver->user->email) }}" required />
                                @error('email')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="phone">Phone Number</x-base.form-label>
                                <x-base.form-input id="phone" name="phone" type="text" value="{{ old('phone', $driver->phone) }}" required />
                                @error('phone')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="date_of_birth">Date of Birth</x-base.form-label>
                                <x-base.form-input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : '') }}" required />
                                @error('date_of_birth')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="ssn">Social Security Number</x-base.form-label>
                                <x-base.form-input id="ssn" name="ssn" type="text" value="{{ old('ssn', $driver->ssn) }}" placeholder="XXX-XX-XXXX" required />
                                @error('ssn')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12">
                                <x-base.form-label for="address">Address</x-base.form-label>
                                <x-base.form-input id="address" name="address" type="text" value="{{ old('address', $driver->address) }}" required />
                                @error('address')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <x-base.form-label for="city">City</x-base.form-label>
                                <x-base.form-input id="city" name="city" type="text" value="{{ old('city', $driver->city) }}" required />
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <x-base.form-label for="state">State</x-base.form-label>
                                <x-base.form-input id="state" name="state" type="text" value="{{ old('state', $driver->state) }}" required />
                                @error('state')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <x-base.form-label for="zip_code">ZIP Code</x-base.form-label>
                                <x-base.form-input id="zip_code" name="zip_code" type="text" value="{{ old('zip_code', $driver->zip_code) }}" required />
                                @error('zip_code')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Foto de Perfil -->
                            <div class="col-span-12 mt-5">
                                <h3 class="text-lg font-medium">Profile Photo</h3>
                            </div>

                            <div class="col-span-12">
                                <div class="border-2 border-dashed shadow-sm border-slate-200/60 dark:border-darkmode-400 rounded-md p-5">
                                    <div class="flex flex-wrap px-4">
                                        <div class="w-24 h-24 relative image-fit mb-5 mr-5 cursor-pointer zoom-in">
                                            <img class="rounded-md" alt="Driver Photo" src="{{ $driver->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}" id="preview-image">
                                        </div>
                                    </div>
                                    <div class="px-4 pb-4 flex items-center cursor-pointer relative">
                                        <x-base.lucide class="w-4 h-4 mr-2" icon="image" />
                                        <span class="text-primary mr-1 font-medium">Upload a file</span> or drag and drop
                                        <input id="profile_photo" name="profile_photo" type="file" class="w-full h-full top-0 left-0 absolute opacity-0" accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    @error('profile_photo')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-5">
                            <button type="button" class="btn btn-primary w-24" onclick="nextStep(1)">Next</button>
                        </div>
                    </div>

                    <!-- Paso 2: Información de Licencia -->
                    <div id="step2" class="step-content hidden">
                        <div class="grid grid-cols-12 gap-4 gap-y-5">
                            <div class="col-span-12">
                                <h3 class="text-lg font-medium">License Information</h3>
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="license_number">License Number</x-base.form-label>
                                <x-base.form-input id="license_number" name="license_number" type="text" value="{{ old('license_number', $driver->license_number) }}" required />
                                @error('license_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="license_state">License State</x-base.form-label>
                                <x-base.form-input id="license_state" name="license_state" type="text" value="{{ old('license_state', $driver->license_state) }}" required />
                                @error('license_state')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="license_class">License Class</x-base.form-label>
                                <select id="license_class" name="license_class" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                                    <option value="">Select License Class</option>
                                    <option value="A" {{ old('license_class', $driver->license_class) == 'A' ? 'selected' : '' }}>Class A</option>
                                    <option value="B" {{ old('license_class', $driver->license_class) == 'B' ? 'selected' : '' }}>Class B</option>
                                    <option value="C" {{ old('license_class', $driver->license_class) == 'C' ? 'selected' : '' }}>Class C</option>
                                </select>
                                @error('license_class')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="license_expiration">License Expiration Date</x-base.form-label>
                                <x-base.form-input id="license_expiration" name="license_expiration" type="date" value="{{ old('license_expiration', $driver->license_expiration ? $driver->license_expiration->format('Y-m-d') : '') }}" required />
                                @error('license_expiration')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12">
                                <h3 class="text-lg font-medium mt-5">License Endorsements</h3>
                                <div class="grid grid-cols-3 gap-4 mt-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="endorsement_h" name="endorsements[]" value="H" class="form-checkbox h-5 w-5 text-primary" {{ in_array('H', old('endorsements', $driver->endorsements ?? [])) ? 'checked' : '' }}>
                                        <label for="endorsement_h" class="ml-2">H - Hazardous Materials</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="endorsement_n" name="endorsements[]" value="N" class="form-checkbox h-5 w-5 text-primary" {{ in_array('N', old('endorsements', $driver->endorsements ?? [])) ? 'checked' : '' }}>
                                        <label for="endorsement_n" class="ml-2">N - Tank Vehicle</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="endorsement_p" name="endorsements[]" value="P" class="form-checkbox h-5 w-5 text-primary" {{ in_array('P', old('endorsements', $driver->endorsements ?? [])) ? 'checked' : '' }}>
                                        <label for="endorsement_p" class="ml-2">P - Passenger</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="endorsement_t" name="endorsements[]" value="T" class="form-checkbox h-5 w-5 text-primary" {{ in_array('T', old('endorsements', $driver->endorsements ?? [])) ? 'checked' : '' }}>
                                        <label for="endorsement_t" class="ml-2">T - Double/Triple Trailers</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="endorsement_x" name="endorsements[]" value="X" class="form-checkbox h-5 w-5 text-primary" {{ in_array('X', old('endorsements', $driver->endorsements ?? [])) ? 'checked' : '' }}>
                                        <label for="endorsement_x" class="ml-2">X - Tanker & Hazmat</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-12">
                                <h3 class="text-lg font-medium mt-5">License Images</h3>
                                <div class="grid grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <x-base.form-label for="license_front">Front of License</x-base.form-label>
                                        <div class="border-2 border-dashed shadow-sm border-slate-200/60 dark:border-darkmode-400 rounded-md p-5">
                                            <div class="flex flex-wrap px-4">
                                                <div class="w-24 h-24 relative image-fit mb-5 mr-5 cursor-pointer zoom-in">
                                                    <img class="rounded-md" alt="License Front" src="{{ $driver->getFirstMediaUrl('license_front') ?: asset('build/default_profile.png') }}" id="preview-license-front">
                                                </div>
                                            </div>
                                            <div class="px-4 pb-4 flex items-center cursor-pointer relative">
                                                <x-base.lucide class="w-4 h-4 mr-2" icon="image" />
                                                <span class="text-primary mr-1 font-medium">Upload front</span>
                                                <input id="license_front" name="license_front" type="file" class="w-full h-full top-0 left-0 absolute opacity-0" accept="image/*" onchange="previewLicenseFront(this)">
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <x-base.form-label for="license_back">Back of License</x-base.form-label>
                                        <div class="border-2 border-dashed shadow-sm border-slate-200/60 dark:border-darkmode-400 rounded-md p-5">
                                            <div class="flex flex-wrap px-4">
                                                <div class="w-24 h-24 relative image-fit mb-5 mr-5 cursor-pointer zoom-in">
                                                    <img class="rounded-md" alt="License Back" src="{{ $driver->getFirstMediaUrl('license_back') ?: asset('build/default_profile.png') }}" id="preview-license-back">
                                                </div>
                                            </div>
                                            <div class="px-4 pb-4 flex items-center cursor-pointer relative">
                                                <x-base.lucide class="w-4 h-4 mr-2" icon="image" />
                                                <span class="text-primary mr-1 font-medium">Upload back</span>
                                                <input id="license_back" name="license_back" type="file" class="w-full h-full top-0 left-0 absolute opacity-0" accept="image/*" onchange="previewLicenseBack(this)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between mt-5">
                            <button type="button" class="btn btn-outline-secondary w-24" onclick="prevStep(2)">Previous</button>
                            <button type="button" class="btn btn-primary w-24" onclick="nextStep(2)">Next</button>
                        </div>
                    </div>

                    <!-- Paso 3: Información de Empleo -->
                    <div id="step3" class="step-content hidden">
                        <div class="grid grid-cols-12 gap-4 gap-y-5">
                            <div class="col-span-12">
                                <h3 class="text-lg font-medium">Employment Information</h3>
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="hire_date">Hire Date</x-base.form-label>
                                <x-base.form-input id="hire_date" name="hire_date" type="date" value="{{ old('hire_date', $driver->hire_date ? $driver->hire_date->format('Y-m-d') : date('Y-m-d')) }}" required />
                                @error('hire_date')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="status">Status</x-base.form-label>
                                <select id="status" name="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                                    <option value="1" {{ old('status', $driver->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $driver->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                    <option value="2" {{ old('status', $driver->status) == '2' ? 'selected' : '' }}>Pending</option>
                                </select>
                                @error('status')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12">
                                <x-base.form-label for="notes">Notes</x-base.form-label>
                                <x-base.form-textarea id="notes" name="notes">{{ old('notes', $driver->notes) }}</x-base.form-textarea>
                                @error('notes')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12">
                                <h3 class="text-lg font-medium mt-5">Medical Information</h3>
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="medical_card_number">Medical Card Number</x-base.form-label>
                                <x-base.form-input id="medical_card_number" name="medical_card_number" type="text" value="{{ old('medical_card_number', $driver->medical_card_number) }}" />
                                @error('medical_card_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label for="medical_card_expiration">Medical Card Expiration</x-base.form-label>
                                <x-base.form-input id="medical_card_expiration" name="medical_card_expiration" type="date" value="{{ old('medical_card_expiration', $driver->medical_card_expiration ? $driver->medical_card_expiration->format('Y-m-d') : '') }}" />
                                @error('medical_card_expiration')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-12">
                                <x-base.form-label for="medical_card_image">Medical Card Image</x-base.form-label>
                                <div class="border-2 border-dashed shadow-sm border-slate-200/60 dark:border-darkmode-400 rounded-md p-5">
                                    <div class="flex flex-wrap px-4">
                                        <div class="w-24 h-24 relative image-fit mb-5 mr-5 cursor-pointer zoom-in">
                                            <img class="rounded-md" alt="Medical Card" src="{{ $driver->getFirstMediaUrl('medical_card') ?: asset('build/default_profile.png') }}" id="preview-medical-card">
                                        </div>
                                    </div>
                                    <div class="px-4 pb-4 flex items-center cursor-pointer relative">
                                        <x-base.lucide class="w-4 h-4 mr-2" icon="image" />
                                        <span class="text-primary mr-1 font-medium">Upload medical card</span>
                                        <input id="medical_card_image" name="medical_card_image" type="file" class="w-full h-full top-0 left-0 absolute opacity-0" accept="image/*" onchange="previewMedicalCard(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between mt-5">
                            <button type="button" class="btn btn-outline-secondary w-24" onclick="prevStep(3)">Previous</button>
                            <button type="submit" class="btn btn-primary w-24">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Función para previsualizar la imagen de perfil
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        document.getElementById('preview-image').src = e.target.result;
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Función para previsualizar la imagen frontal de la licencia
            function previewLicenseFront(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        document.getElementById('preview-license-front').src = e.target.result;
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Función para previsualizar la imagen trasera de la licencia
            function previewLicenseBack(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        document.getElementById('preview-license-back').src = e.target.result;
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Función para previsualizar la imagen de la tarjeta médica
            function previewMedicalCard(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        document.getElementById('preview-medical-card').src = e.target.result;
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Función para avanzar al siguiente paso
            function nextStep(currentStep) {
                // Validar campos del paso actual
                if (!validateStep(currentStep)) {
                    return;
                }

                // Ocultar paso actual
                document.getElementById('step' + currentStep).classList.add('hidden');
                
                // Mostrar siguiente paso
                document.getElementById('step' + (currentStep + 1)).classList.remove('hidden');
                
                // Actualizar indicadores de paso
                document.getElementById('step' + currentStep + '-indicator').classList.remove('bg-primary', 'text-white');
                document.getElementById('step' + currentStep + '-indicator').classList.add('bg-success', 'text-white');
                
                document.getElementById('step' + (currentStep + 1) + '-indicator').classList.remove('bg-slate-300', 'text-slate-600');
                document.getElementById('step' + (currentStep + 1) + '-indicator').classList.add('bg-primary', 'text-white');
                
                // Actualizar barra de progreso
                const progressBar = document.getElementById('progress-bar');
                if (currentStep === 1) {
                    progressBar.style.width = '66%';
                } else if (currentStep === 2) {
                    progressBar.style.width = '100%';
                }
            }

            // Función para volver al paso anterior
            function prevStep(currentStep) {
                // Ocultar paso actual
                document.getElementById('step' + currentStep).classList.add('hidden');
                
                // Mostrar paso anterior
                document.getElementById('step' + (currentStep - 1)).classList.remove('hidden');
                
                // Actualizar indicadores de paso
                document.getElementById('step' + currentStep + '-indicator').classList.remove('bg-primary', 'text-white');
                document.getElementById('step' + currentStep + '-indicator').classList.add('bg-slate-300', 'text-slate-600');
                
                document.getElementById('step' + (currentStep - 1) + '-indicator').classList.remove('bg-success');
                document.getElementById('step' + (currentStep - 1) + '-indicator').classList.add('bg-primary', 'text-white');
                
                // Actualizar barra de progreso
                const progressBar = document.getElementById('progress-bar');
                if (currentStep === 2) {
                    progressBar.style.width = '33%';
                } else if (currentStep === 3) {
                    progressBar.style.width = '66%';
                }
            }

            // Función para validar campos del paso actual
            function validateStep(step) {
                let isValid = true;
                
                if (step === 1) {
                    // Validar campos del paso 1
                    const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'ssn', 'address', 'city', 'state', 'zip_code'];
                    
                    requiredFields.forEach(field => {
                        const input = document.getElementById(field);
