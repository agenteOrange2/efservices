@extends('../themes/' . $activeTheme)
@section('title', 'Edit Medical Record')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Medical Records', 'url' => route('admin.medical-records.index')],
['label' => 'Edit', 'active' => true],
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
            Edit Medical Record
        </h2>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.medical-records.index') }}" class="btn btn-outline-secondary" variant="primary">
                <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                Back to Medical Records
            </x-base.button>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="box box--stacked mt-5">
        <div class="box-body p-5">
            <form id="medicalRecordForm" action="{{ route('admin.medical-records.update', $medicalRecord) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                                <option value="{{ $carrier->id }}" {{ old('carrier_id', $medicalRecord->userDriverDetail->carrier_id ?? '') == $carrier->id ? 'selected' : '' }}>
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
                                <option value="{{ $driver->id }}" {{ old('user_driver_detail_id', $medicalRecord->user_driver_detail_id) == $driver->id ? 'selected' : '' }}>
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

                <!-- Sección 2: Información del Driver -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Driver Information</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Social Security Number -->
                        <div>
                            <x-base.form-label for="social_security_number" class="form-label required">Social Security Number</x-base.form-label>
                            <x-base.form-input type="text" id="social_security_number" name="social_security_number" class="form-control @error('social_security_number') is-invalid @enderror" value="{{ old('social_security_number', $medicalRecord->social_security_number) }}" placeholder="XXX-XX-XXXX" pattern="\d{3}-\d{2}-\d{4}" x-mask="999-99-9999" required />
                            <small class="form-text text-muted">Format: XXX-XX-XXXX</small>
                            @error('social_security_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hire Date -->
                        <div>
                            <x-base.form-label for="hire_date" class="form-label">Hire Date</x-base.form-label>
                            <x-base.litepicker id="hire_date" name="hire_date" value="{{ old('hire_date', $medicalRecord->hire_date ? $medicalRecord->hire_date->format('m/d/Y') : '') }}" class="@error('hire_date') @enderror" placeholder="MM/DD/YYYY" />
                            @error('hire_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div>
                            <x-base.form-label for="location" class="form-label">Location</x-base.form-label>
                            <x-base.form-input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $medicalRecord->location) }}" placeholder="Work location" />
                            @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Status Information -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Status Information</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Suspension Status -->
                        <div x-data="{ isSuspended: {{ json_encode(old('is_suspended', $medicalRecord->is_suspended)) }} }">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="is_suspended" name="is_suspended" value="1" x-model="isSuspended" {{ old('is_suspended', $medicalRecord->is_suspended) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                <label for="is_suspended" class="ml-2 text-sm">Driver is Suspended</label>
                            </div>
                            <div x-show="isSuspended" class="mt-3">
                                <x-base.form-label for="suspension_date" class="form-label">Suspension Date</x-base.form-label>
                                <x-base.litepicker id="suspension_date" name="suspension_date" value="{{ old('suspension_date', $medicalRecord->suspension_date ? $medicalRecord->suspension_date->format('m/d/Y') : '') }}" class="@error('suspension_date') @enderror" placeholder="MM/DD/YYYY" />
                                @error('suspension_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Termination Status -->
                        <div x-data="{ isTerminated: {{ json_encode(old('is_terminated', $medicalRecord->is_terminated)) }} }">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="is_terminated" name="is_terminated" value="1" x-model="isTerminated" {{ old('is_terminated', $medicalRecord->is_terminated) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded" />
                                <label for="is_terminated" class="ml-2 text-sm">Driver is Terminated</label>
                            </div>
                            <div x-show="isTerminated" class="mt-3">
                                <x-base.form-label for="termination_date" class="form-label">Termination Date</x-base.form-label>
                                <x-base.litepicker id="termination_date" name="termination_date" value="{{ old('termination_date', $medicalRecord->termination_date ? $medicalRecord->termination_date->format('m/d/Y') : '') }}" class="@error('termination_date') @enderror" placeholder="MM/DD/YYYY" />
                                @error('termination_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Medical Certification Information -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Medical Certification Information</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Medical Examiner Name -->
                        <div>
                            <x-base.form-label for="medical_examiner_name" class="form-label required">Medical Examiner Name</x-base.form-label>
                            <x-base.form-input type="text" id="medical_examiner_name" name="medical_examiner_name" class="form-control @error('medical_examiner_name') is-invalid @enderror" value="{{ old('medical_examiner_name', $medicalRecord->medical_examiner_name) }}" required />
                            @error('medical_examiner_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Medical Examiner Registry Number -->
                        <div>
                            <x-base.form-label for="medical_examiner_registry_number" class="form-label required">Medical Examiner Registry Number</x-base.form-label>
                            <x-base.form-input type="text" id="medical_examiner_registry_number" name="medical_examiner_registry_number" class="form-control @error('medical_examiner_registry_number') is-invalid @enderror" value="{{ old('medical_examiner_registry_number', $medicalRecord->medical_examiner_registry_number) }}" required />
                            @error('medical_examiner_registry_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Medical Card Expiration Date -->
                        <div>
                            <x-base.form-label for="medical_card_expiration_date" class="form-label required">Medical Card Expiration Date</x-base.form-label>
                            <x-base.litepicker id="medical_card_expiration_date" name="medical_card_expiration_date" value="{{ old('medical_card_expiration_date', $medicalRecord->medical_card_expiration_date ? $medicalRecord->medical_card_expiration_date->format('m/d/Y') : '') }}" class="@error('medical_card_expiration_date') @enderror" placeholder="MM/DD/YYYY" required />
                            @error('medical_card_expiration_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección 5: Medical Card Upload -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Medical Card</h4>
                    
                    @php
                        $medicalCardImage = $medicalRecord->getFirstMediaUrl('medical_card');
                    @endphp
                    
                    @if($medicalCardImage)
                        <!-- Current Medical Card Display -->
                        <div class="mb-6">
                            <div class="flex items-center mb-3">
                                @php
                                    $medicalCardMedia = $medicalRecord->getFirstMedia('medical_card');
                                    $isPdf = $medicalCardMedia && $medicalCardMedia->mime_type === 'application/pdf';
                                @endphp
                                <x-base.lucide class="mr-2 h-5 w-5 text-slate-600" icon="{{ $isPdf ? 'file-text' : 'image' }}" />
                                <h5 class="font-medium text-slate-700">Current Medical Card</h5>
                            </div>
                            <div class="relative group max-w-md">
                                @if($isPdf)
                                    <!-- PDF Display -->
                                    <div class="aspect-[3/2] overflow-hidden rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center">
                                        <div class="text-center">
                                            <x-base.lucide class="mx-auto h-16 w-16 text-red-500 mb-2" icon="file-text" />
                                            <p class="text-sm font-medium text-slate-700">PDF Document</p>
                                            <p class="text-xs text-slate-500">{{ $medicalCardMedia->file_name ?? 'medical_card.pdf' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <!-- Image Display -->
                                    <div class="aspect-[3/2] overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        <img 
                                            src="{{ $medicalCardImage }}" 
                                            alt="Medical Card" 
                                            class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                        >
                                    </div>
                                @endif
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg">
                                    <x-base.button
                                        as="a"
                                        href="{{ $medicalCardImage }}"
                                        target="_blank"
                                        variant="primary"
                                        size="sm"
                                    >
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="eye" />
                                        View Full Size
                                    </x-base.button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- No Medical Card Message -->
                        <div class="flex items-center justify-center py-8 mb-6">
                            <div class="text-center">
                                <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="file-text" />
                                <h5 class="mt-2 text-sm font-medium text-slate-900">No medical card uploaded</h5>
                                <p class="mt-1 text-xs text-slate-500">Upload a medical card image or PDF for verification.</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Upload New Medical Card -->
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <x-base.form-label for="medical_card" class="form-label">{{ $medicalCardImage ? 'Replace Medical Card' : 'Upload Medical Card' }}</x-base.form-label>
                            <x-base.form-input type="file" id="medical_card" name="medical_card" class="form-control @error('medical_card') is-invalid @enderror" accept="image/*,application/pdf" />
                            <small class="form-text text-muted">Upload the medical card (PDF or image format, max 10MB)</small>
                            @error('medical_card')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <!-- Preview -->
                            <div id="medical_card_preview" class="mt-2" style="display: none;">
                                <img id="medical_card_preview_img" src="" alt="Medical Card Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones del formulario -->
                <div class="flex justify-end mt-8 space-x-4">
                    <x-base.button type="button" class="mr-3" variant="outline-secondary" as="a" href="{{ route('admin.medical-records.index') }}">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary">
                        Save Medical Record
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
            // Manejar preview de imagen de medical card
            function setupImagePreview(inputId, previewId, imgId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                const img = document.getElementById(imgId);
                
                input.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        // Solo mostrar preview para imágenes, no para PDFs
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                img.src = e.target.result;
                                preview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            preview.style.display = 'none';
                        }
                    } else {
                        preview.style.display = 'none';
                    }
                });
            }
            
            // Configurar preview para medical card
            setupImagePreview('medical_card', 'medical_card_preview', 'medical_card_preview_img');
            
            // Validación de fecha de expiración
            document.getElementById('medicalRecordForm').addEventListener('submit', function(event) {
                const expirationDateEl = document.getElementById('medical_card_expiration_date');
                
                // Verificar que la fecha de expiración no sea en el pasado
                if (expirationDateEl.value) {
                    const expirationDate = new Date(expirationDateEl.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (expirationDate < today) {
                        event.preventDefault();
                        alert('Medical card expiration date cannot be in the past');
                        return;
                    }
                }
            });
            
            // Function to load drivers based on selected carrier
            function loadDriversByCarrier(carrierId) {
                const driverSelect = document.getElementById('user_driver_detail_id');
                
                // Clear current options except the first one
                driverSelect.innerHTML = '<option value="">Select Driver</option>';
                
                if (carrierId) {
                    fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(driver => {
                                const option = document.createElement('option');
                                option.value = driver.id;
                                option.textContent = `${driver.user.name} ${driver.user.last_name || ''}`;
                                driverSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error loading drivers:', error);
                        });
                }
            }

            // Make deleteMedicalCard function global for potential future use
            window.deleteMedicalCard = function(medicalRecordId) {
                if (confirm('Are you sure you want to delete this medical card?')) {
                    fetch(`/admin/medical-records/${medicalRecordId}/delete-medical-card`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error deleting medical card');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting medical card');
                    });
                }
            };
            
            // Manejar cambio de carrier para filtrar conductores
            document.getElementById('carrier_id').addEventListener('change', function() {
                const carrierId = this.value;
                loadDriversByCarrier(carrierId);
            });
        });
    </script>
@endpush