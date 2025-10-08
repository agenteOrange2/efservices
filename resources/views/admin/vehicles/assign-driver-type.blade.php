@extends('../themes/' . $activeTheme)
@section('title', 'Asign type vehicle')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => 'Assign type Vehicle', 'active' => true],
    ];
@endphp
@section('subcontent')
    <!-- Contenido principal -->
    <div class="box box--stacked mt-5">
        <div class="box-body p-5">
            <form id="assign-driver-form" action="{{ route('admin.vehicle-driver-assignments.store') }}" method="POST" x-data="{ ownershipType: '{{ old('ownership_type', $driverData['ownership_type'] ?? '') }}' }" class="space-y-6">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <input type="hidden" name="effective_date" value="{{ date('Y-m-d') }}">

                <!-- Sección 1: Información del Vehículo -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Vehicle Information</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <div>
                            <x-base.form-label class="form-label">Make</x-base.form-label>
                            <div class="form-control bg-gray-50">{{ $vehicle->make ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <x-base.form-label class="form-label">Model</x-base.form-label>
                            <div class="form-control bg-gray-50">{{ $vehicle->model ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <x-base.form-label class="form-label">Year</x-base.form-label>
                            <div class="form-control bg-gray-50">{{ $vehicle->year ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <x-base.form-label class="form-label">VIN</x-base.form-label>
                            <div class="form-control bg-gray-50">{{ $vehicle->vin ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Driver Type Assignment -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Driver Type Assignment</h4>
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Tipo de Conductor -->
                        <div>
                            <x-base.form-label for="ownership_type" class="form-label required">Driver Type</x-base.form-label>
                            <x-base.form-select 
                                id="assignment_type" 
                                name="assignment_type" 
                                class="form-select @error('assignment_type') is-invalid @enderror" 
                                required
                                x-model="ownershipType"
                            >
                                <option value="">Select Driver Type</option>
                                <option value="company_driver" {{ old('assignment_type') == 'company_driver' ? 'selected' : '' }}>Company Driver</option>
                                <option value="owner_operator" {{ old('assignment_type') == 'owner_operator' ? 'selected' : '' }}>Owner Operator</option>
                                <option value="third_party" {{ old('assignment_type') == 'third_party' ? 'selected' : '' }}>Third Party</option>
                            </x-base.form-select>
                            <small class="form-text text-muted">Select the driver type that best describes the relationship with the vehicle.</small>
                            @error('assignment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    </div>

                    <!-- Sección 2.5: Driver Selection -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Driver Selection</h4>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <x-base.form-label for="user_id" class="form-label required">Select Driver</x-base.form-label>
                                <x-base.form-select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select a driver</option>
                                    @if(isset($availableDrivers))
                                        @foreach($availableDrivers as $driver)
                                            <option value="{{ $driver->id }}" {{ old('user_id') == $driver->id ? 'selected' : '' }}>
                                                {{ $driver->name }} ({{ $driver->email }})
                                            </option>
                                        @endforeach
                                    @endif
                                </x-base.form-select>
                                <small class="form-text text-muted">Select the driver who will be assigned to this vehicle.</small>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Owner Operator Information -->
                <div x-show="ownershipType === 'owner_operator'" class="mb-8" style="display: none;">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Owner Operator Information</h4>
                    
                    <!-- Personal Information -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium mb-3 text-gray-700">Personal Information</h5>
                        @if($driverData)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <p class="text-sm text-blue-700 mb-2"><i class="fas fa-info-circle mr-1"></i> Information auto-filled from assigned driver</p>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <x-base.form-label for="owner_first_name" class="form-label required">First Name</x-base.form-label>
                                <input type="text" id="owner_first_name" name="owner_first_name" class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 form-control @error('owner_first_name') is-invalid @enderror" value="{{ old('owner_first_name', $driverData['first_name'] ?? '') }}" placeholder="Enter first name" {{ $driverData ? 'readonly' : '' }} />
                                @error('owner_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="owner_last_name" class="form-label required">Last Name</x-base.form-label>
                                <input type="text" id="owner_last_name" name="owner_last_name" class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 form-control @error('owner_last_name') is-invalid @enderror" value="{{ old('owner_last_name', $driverData['last_name'] ?? '') }}" placeholder="Enter last name" {{ $driverData ? 'readonly' : '' }} />
                                @error('owner_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="owner_phone" class="form-label required">Phone Number</x-base.form-label>
                                <input type="tel" id="owner_phone" name="owner_phone" class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 form-control @error('owner_phone') is-invalid @enderror" value="{{ old('owner_phone', $driverData['phone'] ?? '') }}" placeholder="(555) 123-4567" {{ $driverData ? 'readonly' : '' }} />
                                @error('owner_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="owner_email" class="form-label required">Email Address</x-base.form-label>
                                <input type="email" id="owner_email" name="owner_email" class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 form-control @error('owner_email') is-invalid @enderror" value="{{ old('owner_email', $driverData['email'] ?? '') }}" placeholder="example@email.com" {{ $driverData ? 'readonly' : '' }} />
                                @error('owner_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- License Information -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium mb-3 text-gray-700">License Information</h5>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div>
                                <x-base.form-label for="owner_license_number" class="form-label required">License Number</x-base.form-label>
                                <input type="text" id="owner_license_number" name="owner_license_number" class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 form-control @error('owner_license_number') is-invalid @enderror" value="{{ old('owner_license_number', $driverData['license_number'] ?? '') }}" placeholder="License number" {{ $driverData ? 'readonly' : '' }} />
                                @error('owner_license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="owner_license_state" class="form-label required">State of Issue</x-base.form-label>
                                @if($driverData && $driverData['license_state'])
                                    <input type="text" id="owner_license_state" name="owner_license_state" class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 form-control" value="{{ $driverData['license_state'] }}" readonly />
                                @else
                                    <x-base.form-select id="owner_license_state" name="owner_license_state" class="form-select @error('owner_license_state') is-invalid @enderror">
                                        <option value="">Select State</option>
                                        <option value="AL" {{ old('owner_license_state') == 'AL' ? 'selected' : '' }}>Alabama</option>
                                        <option value="AK" {{ old('owner_license_state') == 'AK' ? 'selected' : '' }}>Alaska</option>
                                        <option value="AZ" {{ old('owner_license_state') == 'AZ' ? 'selected' : '' }}>Arizona</option>
                                        <option value="AR" {{ old('owner_license_state') == 'AR' ? 'selected' : '' }}>Arkansas</option>
                                        <option value="CA" {{ old('owner_license_state') == 'CA' ? 'selected' : '' }}>California</option>
                                        <option value="CO" {{ old('owner_license_state') == 'CO' ? 'selected' : '' }}>Colorado</option>
                                        <option value="CT" {{ old('owner_license_state') == 'CT' ? 'selected' : '' }}>Connecticut</option>
                                        <option value="DE" {{ old('owner_license_state') == 'DE' ? 'selected' : '' }}>Delaware</option>
                                        <option value="FL" {{ old('owner_license_state') == 'FL' ? 'selected' : '' }}>Florida</option>
                                        <option value="GA" {{ old('owner_license_state') == 'GA' ? 'selected' : '' }}>Georgia</option>
                                        <option value="TX" {{ old('owner_license_state') == 'TX' ? 'selected' : '' }}>Texas</option>
                                        <!-- Add more states as needed -->
                                    </x-base.form-select>
                                @endif
                                @error('owner_license_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="owner_license_expiry" class="form-label required">Expiration Date</x-base.form-label>
                                @if($driverData)
                                    <x-base.form-input type="text" id="owner_license_expiry" name="owner_license_expiry" value="{{ old('owner_license_expiry', $driverData['license_expiration'] ?? '') }}" class="@error('owner_license_expiry') @enderror" placeholder="MM/DD/YYYY" readonly />
                                @else
                                    <x-base.litepicker id="owner_license_expiry" name="owner_license_expiry" value="{{ old('owner_license_expiry', $driverData['license_expiration'] ?? '') }}" class="@error('owner_license_expiry') @enderror" placeholder="MM/DD/YYYY" />
                                @endif
                                @error('owner_license_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Third Party Information -->
                <div x-show="ownershipType === 'third_party'" class="mb-8" style="display: none;">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Third Party Information</h4>
                    
                    <!-- Company Information -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium mb-3 text-gray-700">Company Information</h5>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <x-base.form-label for="third_party_name" class="form-label required">Company Name</x-base.form-label>
                                <x-base.form-input type="text" id="third_party_name" name="third_party_name" class="form-control @error('third_party_name') is-invalid @enderror" value="{{ old('third_party_name') }}" placeholder="Enter company name" />
                                @error('third_party_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="third_party_address" class="form-label required">Company Address</x-base.form-label>
                                <x-base.form-input id="third_party_address" name="third_party_address" type="text" class="form-control @error('third_party_address') is-invalid @enderror" placeholder="Enter complete address" value="{{ old('third_party_address') }}" />
                                @error('third_party_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium mb-3 text-gray-700">Contact Information</h5>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div>
                                <x-base.form-label for="third_party_phone" class="form-label required">Phone Number</x-base.form-label>
                                <x-base.form-input type="tel" id="third_party_phone" name="third_party_phone" class="form-control @error('third_party_phone') is-invalid @enderror" value="{{ old('third_party_phone') }}" placeholder="(555) 123-4567" />
                                @error('third_party_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="third_party_email" class="form-label required">Email Address</x-base.form-label>
                                <x-base.form-input type="email" id="third_party_email" name="third_party_email" class="form-control @error('third_party_email') is-invalid @enderror" value="{{ old('third_party_email') }}" placeholder="company@email.com" />
                                @error('third_party_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="third_party_fein" class="form-label">FEIN / Tax ID</x-base.form-label>
                                <x-base.form-input type="text" id="third_party_fein" name="third_party_fein" class="form-control @error('third_party_fein') is-invalid @enderror" value="{{ old('third_party_fein') }}" placeholder="XX-XXXXXXX" />
                                @error('third_party_fein')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Additional Contact Information -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium mb-3 text-gray-700">Additional Contact Information</h5>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <x-base.form-label for="third_party_contact_person" class="form-label">Contact Person</x-base.form-label>
                                <x-base.form-input type="text" id="third_party_contact_person" name="third_party_contact_person" class="form-control @error('third_party_contact_person') is-invalid @enderror" value="{{ old('third_party_contact_person') }}" placeholder="Primary contact name" />
                                @error('third_party_contact_person')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <x-base.form-label for="third_party_contact_phone" class="form-label">Contact Phone</x-base.form-label>
                                <x-base.form-input type="tel" id="third_party_contact_phone" name="third_party_contact_phone" class="form-control @error('third_party_contact_phone') is-invalid @enderror" value="{{ old('third_party_contact_phone') }}" placeholder="(XXX) XXX-XXXX" />
                                @error('third_party_contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 5: Company Driver Information -->
                <div x-show="ownershipType === 'company_driver'" class="mb-8" style="display: none;">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Company Driver Information</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h5 class="text-lg font-medium text-blue-900">Company Driver Assignment</h5>
                                <p class="text-blue-700">This vehicle will be assigned to a company employee driver. No additional information is required at this time.</p>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Sección 7: Action Buttons -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.vehicles.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancel and Return
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Assign Driver Type
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
        const assignmentTypeSelect = document.getElementById('assignment_type');
        
        function toggleSections() {
            const selectedValue = assignmentTypeSelect.value;
            
            // Obtener todas las secciones condicionales
            const ownerOperatorSection = document.querySelector('[x-show="ownershipType === \'owner_operator\'"]');
            const thirdPartySection = document.querySelector('[x-show="ownershipType === \'third_party\'"]');
            const companyDriverSection = document.querySelector('[x-show="ownershipType === \'company_driver\'"]');
            
            // Ocultar todas las secciones
            [ownerOperatorSection, thirdPartySection, companyDriverSection].forEach(section => {
                if (section) {
                    section.style.display = 'none';
                }
            });
            
            // Mostrar la sección correspondiente
            switch(selectedValue) {
                case 'owner_operator':
                    if (ownerOperatorSection) {
                        ownerOperatorSection.style.display = 'block';
                        validateOwnerOperatorFields();
                    }
                    break;
                case 'third_party':
                    if (thirdPartySection) thirdPartySection.style.display = 'block';
                    break;
                case 'company_driver':
                    if (companyDriverSection) companyDriverSection.style.display = 'block';
                    break;
            }
        }
        
        function validateOwnerOperatorFields() {
            // Verificar si hay datos del conductor auto-rellenados
            const firstNameField = document.getElementById('owner_first_name');
            const lastNameField = document.getElementById('owner_last_name');
            const phoneField = document.getElementById('owner_phone');
            const emailField = document.getElementById('owner_email');
            const licenseField = document.getElementById('owner_license_number');
            
            // Si los campos están auto-rellenados (readonly), mostrar mensaje informativo
            if (firstNameField && firstNameField.hasAttribute('readonly')) {
                console.log('Owner Operator fields auto-filled with assigned driver data');
            }
        }

        assignmentTypeSelect.addEventListener('change', toggleSections);
        
        // Ejecutar al cargar la página
        toggleSections();
        
        // Manejar envío del formulario
        const form = document.getElementById('assign-driver-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const selectedType = assignmentTypeSelect.value;
                
                // Validación básica
                if (!selectedType) {
                    e.preventDefault();
                    alert('Please select a driver type.');
                    assignmentTypeSelect.focus();
                    return false;
                }
            });
        }
    });
</script>
@endpush