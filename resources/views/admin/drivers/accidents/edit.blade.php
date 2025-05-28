@extends('../themes/' . $activeTheme)
@section('title', 'Edit Accident Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Accidents Management', 'url' => route('admin.accidents.index')],
        ['label' => 'Edit Accident Record', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto py-5">
        <div class="box box--stacked">
            <div class="box-header">
                <h2 class="box-title">Edit Accident Record</h2>
            </div>

            <div class="box-body p-5">
                <!-- Mensajes Flash de éxito o error -->
                @if (session('success'))
                    <div class="alert alert-success show mb-5" role="alert">
                        <div class="flex items-center">
                            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger show mb-5" role="alert">
                        <div class="flex items-center">
                            <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                <form action="{{ route('admin.accidents.update', $accident->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Carrier Selection -->
                        <div>
                            <label for="carrier_id" class="form-label">Carrier</label>
                            <select id="carrier_id" class="form-control w-full" disabled>
                                <option value="{{ $accident->userDriverDetail->carrier_id }}">
                                    {{ $accident->userDriverDetail->carrier->name }}</option>
                            </select>
                            <input type="hidden" name="carrier_id" value="{{ $accident->userDriverDetail->carrier_id }}">
                        </div>

                        <!-- Driver Selection -->
                        <div>
                            <label for="user_driver_detail_id" class="form-label">Driver</label>
                            <select id="user_driver_detail_id" name="user_driver_detail_id" class="form-control w-full"
                                required>
                                <option value="{{ $accident->user_driver_detail_id }}">
                                    {{ $accident->userDriverDetail->user->name }}
                                    {{ $accident->userDriverDetail->last_name }}
                                </option>
                            </select>
                            @error('user_driver_detail_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Accident Date -->
                        <div>
                            <label for="accident_date" class="form-label">Accident Date</label>
                            <input type="date" id="accident_date" name="accident_date" class="form-control w-full"
                                value="{{ $accident->accident_date->format('Y-m-d') }}" required>
                            @error('accident_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Registration Date (Read-only) -->
                        <div>
                            <label class="form-label">Registration Date</label>
                            <input type="text" class="form-control w-full bg-gray-100"
                                value="{{ $accident->created_at->format('m/d/Y H:i') }}" readonly>
                        </div>

                        <!-- Nature of Accident -->
                        <div class="md:col-span-2">
                            <label for="nature_of_accident" class="form-label">Nature of Accident</label>
                            <input type="text" id="nature_of_accident" name="nature_of_accident"
                                class="form-control w-full" value="{{ $accident->nature_of_accident }}" required>
                            @error('nature_of_accident')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Had Injuries -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_injuries" name="had_injuries" class="form-checkbox"
                                    value="1" {{ $accident->had_injuries ? 'checked' : '' }}>
                                <label for="had_injuries" class="ml-2 form-label">Had Injuries?</label>
                            </div>

                            <div id="injuries_container" class="mt-3 {{ $accident->had_injuries ? '' : 'hidden' }}">
                                <label for="number_of_injuries" class="form-label">Number of Injuries</label>
                                <input type="number" id="number_of_injuries" name="number_of_injuries"
                                    class="form-control w-full" min="0" value="{{ $accident->number_of_injuries }}">
                                @error('number_of_injuries')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Had Fatalities -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_fatalities" name="had_fatalities" class="form-checkbox"
                                    value="1" {{ $accident->had_fatalities ? 'checked' : '' }}>
                                <label for="had_fatalities" class="ml-2 form-label">Had Fatalities?</label>
                            </div>

                            <div id="fatalities_container" class="mt-3 {{ $accident->had_fatalities ? '' : 'hidden' }}">
                                <label for="number_of_fatalities" class="form-label">Number of Fatalities</label>
                                <input type="number" id="number_of_fatalities" name="number_of_fatalities"
                                    class="form-control w-full" min="0"
                                    value="{{ $accident->number_of_fatalities }}">
                                @error('number_of_fatalities')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="mt-6">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea id="comments" name="comments" class="form-control w-full" rows="4">{{ $accident->comments }}</textarea>
                        @error('comments')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Documentos existentes -->
                    <div class="mt-8 border-t pt-6" id="documents">
                        <h3 class="text-lg font-medium mb-4">Documents</h3>
                        
                        @if($documents->count() > 0)
                            <div class="mt-4">
                                <label class="form-label">Existing Documents</label>
                                <div class="border rounded-lg p-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                        @foreach($documents as $document)
                                            <div class="border rounded p-3 flex flex-col">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium truncate" title="{{ $document->file_name }}">
                                                        {{ $document->file_name }}
                                                    </span>
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route('admin.accidents.document.preview', $document->id) }}" 
                                                            target="_blank" class="text-primary hover:text-primary-focus">
                                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                                        </a>
                                                        
                                                        <form action="{{ route('admin.accidents.documents.destroy', $document->id) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-danger hover:text-danger-focus border-0 bg-transparent p-0" 
                                                                onclick="return confirm('¿Estás seguro que deseas eliminar este documento?');">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="flex-grow flex items-center justify-center p-2 bg-slate-50 rounded">
                                                    @php
                                                        $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                                                    @endphp
                                                    
                                                    @if($isImage)
                                                        <img src="{{ route('admin.accidents.document.preview', $document->id) }}" 
                                                            alt="{{ $document->file_name }}" class="max-h-24 object-contain">
                                                    @else
                                                        <div class="text-center">
                                                            <i data-lucide="file-text" class="mx-auto w-12 h-12 text-slate-400"></i>
                                                            <span class="block text-xs text-slate-500 mt-1">{{ strtoupper($extension) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Formulario directo para subir documentos -->
                        <div class="mt-6">
                            <h3 class="text-lg font-medium mb-4">Upload New Documents</h3>
                            <form action="/admin/accidents/{{ $accident->id }}/documents" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label for="documents" class="form-label">Select Files</label>
                                    <input type="file" name="documents[]" id="documents" class="form-control w-full" multiple 
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    <p class="text-xs text-gray-500 mt-1">Allowed: JPG, JPEG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                                </div>
                                
                                @error('documents')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                @error('documents.*')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                
                                <button type="submit" class="btn btn-primary mt-2">Upload Documents</button>
                            </form>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end mt-6 gap-2">
                        <a href="{{ route('admin.accidents.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>


            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            // Función para eliminar documentos
            function deleteDocument(documentId) {
                document.getElementById('delete-doc-form-' + documentId).submit();
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Load drivers for the selected carrier
                const carrierId = {{ $accident->userDriverDetail->carrier_id }};
                const driverSelect = document.getElementById('user_driver_detail_id');
                const currentDriverId = {{ $accident->user_driver_detail_id }};

                // Fetch drivers for the carrier
                fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Clear the select
                        driverSelect.innerHTML = '';

                        // Add the current driver (in case they are inactive)
                        const currentDriverOption = document.createElement('option');
                        currentDriverOption.value = currentDriverId;
                        currentDriverOption.text =
                            `{{ $accident->userDriverDetail->user->name }} {{ $accident->userDriverDetail->last_name }}`;
                        currentDriverOption.selected = true;
                        driverSelect.appendChild(currentDriverOption);

                        // Add other active drivers
                        data.forEach(driver => {
                            if (driver.id !== currentDriverId) {
                                const option = document.createElement('option');
                                option.value = driver.id;
                                option.text = `${driver.user.name} ${driver.last_name || ''}`;
                                driverSelect.appendChild(option);
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching drivers:', error);
                    });

                // Injuries/Fatalities Checkbox Logic
                const hadInjuriesCheckbox = document.getElementById('had_injuries');
                const injuriesContainer = document.getElementById('injuries_container');
                const hadFatalitiesCheckbox = document.getElementById('had_fatalities');
                const fatalitiesContainer = document.getElementById('fatalities_container');

                hadInjuriesCheckbox.addEventListener('change', function() {
                    injuriesContainer.classList.toggle('hidden', !this.checked);
                    if (!this.checked) {
                        document.getElementById('number_of_injuries').value = '';
                    }
                });

                hadFatalitiesCheckbox.addEventListener('change', function() {
                    fatalitiesContainer.classList.toggle('hidden', !this.checked);
                    if (!this.checked) {
                        document.getElementById('number_of_fatalities').value = '';
                    }
                });

                // File Upload Logic
                const dropzone = document.getElementById('dropzone');
                const fileInput = document.getElementById('document_input');
                const selectedFilesContainer = document.getElementById('selected_files_container');
                const selectedFilesList = document.getElementById('selected_files_list');

                // Handle click on dropzone
                dropzone.addEventListener('click', function() {
                    fileInput.click();
                });

                // Handle drag and drop
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, unhighlight, false);
                });

                function highlight() {
                    dropzone.classList.add('border-primary');
                }

                function unhighlight() {
                    dropzone.classList.remove('border-primary');
                }

                // Handle file drop
                dropzone.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    handleFiles(files);
                }

                // Handle file selection via input
                fileInput.addEventListener('change', function() {
                    handleFiles(this.files);
                });

                function handleFiles(files) {
                    updateFilesList(files);
                }

                function updateFilesList(files) {
                    if (files.length === 0) return;

                    selectedFilesContainer.classList.remove('hidden');
                    selectedFilesList.innerHTML = '';

                    Array.from(files).forEach(file => {
                        const li = document.createElement('li');
                        li.className = 'text-primary';
                        li.textContent = `${file.name} (${formatFileSize(file.size)})`;
                        selectedFilesList.appendChild(li);
                    });
                }

                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            });
        </script>
    @endpush
