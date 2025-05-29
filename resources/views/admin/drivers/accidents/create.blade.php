@extends('../themes/' . $activeTheme)
@section('title', 'Add Accident Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Accidents Management', 'url' => route('admin.accidents.index')],
        ['label' => 'Add Accident Record', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto py-5">
        <div class="box box--stacked">
            <div class="box-header">
                <h2 class="box-title">Add Accident Record</h2>
            </div>
            
            <div class="box-body p-5">
                <form action="{{ route('admin.accidents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Carrier Selection -->
                        <div>
                            <label for="carrier_id" class="form-label">Carrier</label>
                            <select id="carrier_id" name="carrier_id" class="form-control w-full" required>
                                <option value="">Select Carrier</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                                @endforeach
                            </select>
                            @error('carrier_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Driver Selection -->
                        <div>
                            <label for="user_driver_detail_id" class="form-label">Driver</label>
                            <select id="user_driver_detail_id" name="user_driver_detail_id" class="form-control w-full" required disabled>
                                <option value="">Select a carrier first</option>
                            </select>
                            @error('user_driver_detail_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Accident Date -->
                        <div>
                            <label for="accident_date" class="form-label">Accident Date</label>
                            <input type="date" id="accident_date" name="accident_date" class="form-control w-full" required>
                            @error('accident_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Nature of Accident -->
                        <div>
                            <label for="nature_of_accident" class="form-label">Nature of Accident</label>
                            <input type="text" id="nature_of_accident" name="nature_of_accident" class="form-control w-full" required>
                            @error('nature_of_accident')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Had Injuries -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_injuries" name="had_injuries" class="form-checkbox" value="1">
                                <label for="had_injuries" class="ml-2 form-label">Had Injuries?</label>
                            </div>
                            
                            <div id="injuries_container" class="mt-3 hidden">
                                <label for="number_of_injuries" class="form-label">Number of Injuries</label>
                                <input type="number" id="number_of_injuries" name="number_of_injuries" class="form-control w-full" min="0">
                                @error('number_of_injuries')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Had Fatalities -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_fatalities" name="had_fatalities" class="form-checkbox" value="1">
                                <label for="had_fatalities" class="ml-2 form-label">Had Fatalities?</label>
                            </div>
                            
                            <div id="fatalities_container" class="mt-3 hidden">
                                <label for="number_of_fatalities" class="form-label">Number of Fatalities</label>
                                <input type="number" id="number_of_fatalities" name="number_of_fatalities" class="form-control w-full" min="0">
                                @error('number_of_fatalities')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comments -->
                    <div class="mt-6">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea id="comments" name="comments" class="form-control w-full" rows="4"></textarea>
                        @error('comments')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Documentos - Usando el componente reutilizable -->
                    <div class="mt-6" id="documents">
                        <input type="hidden" name="upload_documents" value="1">
                        <p class="text-sm text-gray-600 mb-4">You can upload documents after creating the accident record.</p>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex justify-end mt-6 gap-2">
                        <a href="{{ route('admin.accidents.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Carrier and Driver Select Logic
            const carrierSelect = document.getElementById('carrier_id');
            const driverSelect = document.getElementById('user_driver_detail_id');
            
            carrierSelect.addEventListener('change', function() {
                const carrierId = this.value;
                driverSelect.disabled = true;
                driverSelect.innerHTML = '<option value="">Loading drivers...</option>';
                
                if (carrierId) {
                    // Fetch drivers for the selected carrier using fetch API
                    fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                        .then(response => response.json())
                        .then(data => {
                            driverSelect.innerHTML = '<option value="">Select Driver</option>';
                            
                            if (data.length === 0) {
                                driverSelect.innerHTML += '<option value="" disabled>No active drivers found</option>';
                            } else {
                                data.forEach(driver => {
                                    driverSelect.innerHTML += `<option value="${driver.id}">${driver.user.name} ${driver.last_name || ''}</option>`;
                                });
                            }
                            
                            driverSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error fetching drivers:', error);
                            driverSelect.innerHTML = '<option value="">Error loading drivers</option>';
                            driverSelect.disabled = false;
                        });
                } else {
                    driverSelect.innerHTML = '<option value="">Select a carrier first</option>';
                    driverSelect.disabled = true;
                }
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
