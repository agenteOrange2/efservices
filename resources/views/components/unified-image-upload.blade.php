@props([
    'inputName' => 'images',
    'multiple' => false,
    'maxFiles' => 1,
    'acceptedTypes' => 'image/*',
    'maxSize' => 5, // MB
    'showPreview' => true,
    'existingFiles' => [],
    'existingImage' => null, // Nueva prop para imagen existente
    'existingImageUrl' => null, // URL de imagen existente
    'existingImageName' => null, // Nombre de imagen existente
    'removeMethod' => 'removeLicenseImage', // Método de eliminación configurable
    'required' => false,
    'label' => 'Upload Images',
    'helpText' => 'Drag and drop images here or click to browse',
    'modelType' => null,
    'modelId' => null,
    'driverId' => null, // ID del UserDriverDetail para uploads de licencias
    'collection' => 'documents',
    'customProperties' => [],
    'value' => null,
    'temporaryStorage' => false,
    'storageKey' => null,
    'documentType' => 'document', // Nuevo parámetro para especificar el tipo de documento
    'side' => null, // Prop para especificar el lado de la licencia (front/back)
    'uniqueId' => null // Prop para el ID único de la licencia
])

<div class="space-y-4" x-data="{
    files: [],
    uploading: false,
    progress: 0,
    error: null,
    success: null,
    dragOver: false,
    modelType: @js($modelType),
    modelId: @js($modelId),
    driverId: @js($driverId),
    collection: @js($collection),
    customProperties: @js($customProperties),
    temporaryStorage: @js($temporaryStorage),
    storageKey: @js($storageKey),
    documentType: @js($documentType),
    uniqueId: @js($uniqueId),
    multiple: @js($multiple),
    maxSize: @js($maxSize),
    value: @js($value),
    existingImageUrl: @js($existingImageUrl ?? null),
    existingImageName: @js($existingImageName ?? null),
    existingImage: @js($existingImage ?? null),
    
    init() {
        if (this.value) {
            this.files = [{ 
                name: this.value, 
                url: '/storage/' + this.value, 
                uploaded: true 
            }];
        } else if (this.existingImageUrl) {
            this.files = [{ 
                name: this.existingImageName || 'Existing Image', 
                url: this.existingImageUrl, 
                uploaded: true, 
                existing: true 
            }];
        } else if (this.existingImage) {
            this.files = [{ 
                name: 'Existing Image', 
                url: this.existingImage, 
                uploaded: true, 
                existing: true 
            }];
        }
    },
    
    handleFiles(fileList) {
        this.error = null;
        const newFiles = Array.from(fileList);
        
        if (!this.multiple) {
            this.files = [];
        }
        
        newFiles.forEach(file => {
            if (file.size > this.maxSize * 1024) {
                this.error = `File ${file.name} is too large. Maximum size is ${this.maxSize}KB.`;
                return;
            }
            
            if (!file.type.startsWith('image/') && file.type !== 'application/pdf') {
                this.error = `File ${file.name} is not a valid image or PDF file.`;
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.files.push({
                    name: file.name,
                    url: e.target.result,
                    file: file,
                    uploaded: false
                });
            };
            reader.readAsDataURL(file);
        });
    },
    
    async removeFile(index) {
        console.log('Removing file at index:', index);
        console.log('Document type:', this.documentType);
        console.log('Driver ID:', this.driverId);
        console.log('Model ID:', this.modelId);
        
        const file = this.files[index];
        if (!file) return;

        // Create FormData for deletion
        const deleteFormData = new FormData();
        deleteFormData.append('model_type', this.modelType || 'user_driver');
        
        // Use driverId for license documents, modelId for others
        if (this.documentType && this.documentType.startsWith('license')) {
            console.log('Using driverId for license document');
            deleteFormData.append('model_id', this.driverId);
        } else {
            console.log('Using modelId for non-license document');
            deleteFormData.append('model_id', this.modelId);
        }
        
        deleteFormData.append('collection', this.collection || 'default');

        if (file.isExisting) {
            // Delete existing file from server
            try {
                const response = await fetch('/api/documents/delete-media', {
                    method: 'POST',
                    body: deleteFormData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    console.log('File deleted successfully');
                } else {
                    console.error('Failed to delete file:', data.message);
                    alert(`Failed to delete image: ${data.message}`);
                }
            } catch (error) {
                console.error('Error deleting file:', error);
                alert(`Failed to delete image: ${error.message}`);
            }
        } else {
            // Remove temporary file
            this.removeTempFile(file.tempId);
        }

        // Remove from files array
        this.files.splice(index, 1);
    },
    
    removeTempFile(tempId) {
        // Remove temporary file from session storage if it exists
        if (this.storageKey) {
            sessionStorage.removeItem(this.storageKey);
        }
        
        // Remove any temporary file references by tempId
        if (tempId) {
            // Clean up any temporary storage related to this tempId
            const tempKey = `temp_file_${tempId}`;
            sessionStorage.removeItem(tempKey);
            
            // Also clean up any other temporary references
            Object.keys(sessionStorage).forEach(key => {
                if (key.includes(tempId)) {
                    sessionStorage.removeItem(key);
                }
            });
        }
        
        console.log('Temporary file removed:', tempId);
    },
    
    async uploadFiles() {
        if (this.files.length === 0) return;
        
        // Handle temporary storage
        if (this.temporaryStorage) {
            if (!this.storageKey) {
                this.error = 'Storage key is required for temporary storage';
                return;
            }
            
            this.uploading = true;
            this.progress = 0;
            this.error = null;
            
            try {
                for (let i = 0; i < this.files.length; i++) {
                    const fileObj = this.files[i];
                    if (!fileObj.file) continue;
                    
                    // Store in session storage for temporary handling
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        sessionStorage.setItem(this.storageKey, JSON.stringify({
                            name: fileObj.name,
                            data: e.target.result,
                            type: fileObj.file.type,
                            size: fileObj.file.size
                        }));
                    };
                    reader.readAsDataURL(fileObj.file);
                    
                    fileObj.uploaded = true;
                    this.progress = Math.round(((i + 1) / this.files.length) * 100);
                }
                
                this.success = 'Photo stored temporarily. It will be uploaded after registration.';
                
            } catch (error) {
                this.error = `Temporary storage failed: ${error.message}`;
                console.error('Temporary storage error:', error);
            } finally {
                this.uploading = false;
                this.progress = 0;
            }
            return;
        }
        
        // Validate required props for permanent storage
        if (!this.modelType || !this.modelId) {
            this.error = 'Model type and ID are required for permanent storage';
            return;
        }
        
        this.uploading = true;
        this.progress = 0;
        this.error = null;
        
        try {
            for (let i = 0; i < this.files.length; i++) {
                const fileObj = this.files[i];
                if (!fileObj.file) continue;
                
                // Check if this is a license upload for processing
                const isLicenseUpload = ['license_front', 'license_back'].includes(this.documentType);
                
                // Debug logging
                console.log('Upload Debug:', {
                    isLicenseUpload,
                    modelId: this.modelId,
                    uniqueId: this.uniqueId,
                    documentType: this.documentType
                });
                
                // Check if uniqueId has the license_ID_hash format (indicates existing license)
                const hasValidLicenseFormat = this.uniqueId && /^license_\d+_/.test(this.uniqueId);
                
                if (isLicenseUpload && this.modelId && hasValidLicenseFormat) {
                    // Direct license upload for existing licenses
                    const directFormData = new FormData();
                    directFormData.append('file', fileObj.file);
                    directFormData.append('driver_id', this.driverId || this.modelId);
                    directFormData.append('type', this.documentType);
                    if (this.uniqueId) {
                        directFormData.append('unique_id', this.uniqueId);
                    }
                    
                    const directResponse = await fetch('/api/documents/upload-license-direct', {
                        method: 'POST',
                        body: directFormData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                        }
                    });
                    
                    if (!directResponse.ok) {
                        const errorData = await directResponse.json();
                        throw new Error(errorData.error || 'Direct license upload failed');
                    }
                    
                    const directResult = await directResponse.json();
                    fileObj.uploaded = true;
                    fileObj.documentId = directResult.document.id;
                    
                } else if (isLicenseUpload && !hasValidLicenseFormat) {
                    // Temporary license upload for new licenses (no valid license_ID_hash format)
                    const tempLicenseFormData = new FormData();
                    tempLicenseFormData.append('file', fileObj.file);
                    tempLicenseFormData.append('type', this.documentType);
                    if (this.uniqueId) {
                        tempLicenseFormData.append('session_id', this.uniqueId);
                    }
                    
                    const tempLicenseResponse = await fetch('/api/driver/upload-license-temp', {
                        method: 'POST',
                        body: tempLicenseFormData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                        }
                    });
                    
                    if (!tempLicenseResponse.ok) {
                        const errorData = await tempLicenseResponse.json();
                        throw new Error(errorData.error || 'Temporary license upload failed');
                    }
                    
                    const tempLicenseResult = await tempLicenseResponse.json();
                    fileObj.uploaded = true;
                    fileObj.documentId = tempLicenseResult.temp_upload.id;
                    
                } else {
                    // Original two-step process for other document types
                    // Step 1: Temporary upload
                    const tempFormData = new FormData();
                    tempFormData.append('file', fileObj.file);
                    tempFormData.append('type', this.documentType);
                    
                    const tempResponse = await fetch('/api/documents/upload', {
                        method: 'POST',
                        body: tempFormData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                        }
                    });
                    
                    if (!tempResponse.ok) {
                        const errorData = await tempResponse.json();
                        throw new Error(errorData.error || 'Temporary upload failed');
                    }
                    
                    const tempResult = await tempResponse.json();
                    
                    // Step 2: Permanent storage
                    const storeFormData = new FormData();
                    storeFormData.append('model_type', this.modelType);
                    storeFormData.append('model_id', this.modelId);
                    storeFormData.append('collection', this.collection);
                    storeFormData.append('token', tempResult.token);
                    
                    // Add custom properties if provided
                    if (this.customProperties && Object.keys(this.customProperties).length > 0) {
                        Object.keys(this.customProperties).forEach(key => {
                            storeFormData.append(`custom_properties[${key}]`, this.customProperties[key]);
                        });
                    }
                    
                    const storeResponse = await fetch('/api/documents/store', {
                        method: 'POST',
                        body: storeFormData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                        }
                    });
                    
                    if (!storeResponse.ok) {
                        const errorData = await storeResponse.json();
                        throw new Error(errorData.error || 'Permanent storage failed');
                    }
                    
                    const storeResult = await storeResponse.json();
                    fileObj.uploaded = true;
                    fileObj.documentId = storeResult.document.id;
                }
                
                this.progress = Math.round(((i + 1) / this.files.length) * 100);
            }
            
            this.success = 'Files uploaded and stored successfully!';
            
            // Dispatch event to refresh preview in Livewire component
            if (this.modelType === 'medical_card') {
                @this.call('refreshMedicalCardPreview');
            } else if (['license_front', 'license_back'].includes(this.documentType)) {
                @this.call('refreshLicensePreview');
            }
            
        } catch (error) {
            this.error = `Upload failed: ${error.message}`;
            console.error('Upload error:', error);
        } finally {
            this.uploading = false;
            this.progress = 0;
        }
    }
}">
    <!-- Label -->
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <!-- Upload Area -->
    <div class="relative">
        <div 
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors duration-200"
            :class="{
                'border-blue-400 bg-blue-50': dragOver,
                'border-gray-300': !dragOver
            }"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="dragOver = false; handleFiles($event.dataTransfer.files)"
        >
            <input 
                type="file" 
                :name="name"
                accept="{{ $acceptedTypes }}"
                :multiple="multiple"
                {{ $required ? 'required' : '' }}
                class="hidden"
                x-ref="fileInput"
                @change="handleFiles($event.target.files)"
            >
            
            <div class="space-y-2">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="text-sm text-gray-600">
                    <button type="button" class="font-medium text-blue-600 hover:text-blue-500" @click="$refs.fileInput.click()">
                        Upload a file
                    </button>
                    or drag and drop
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF up to {{ $maxSize }}KB</p>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div x-show="uploading" class="w-full bg-gray-200 rounded-full h-2">
        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
    </div>

    <!-- Existing Image Preview -->
    @if($existingImageUrl && $showPreview)
        <div class="mt-4" x-show="files.length === 0">
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <img src="{{ $existingImageUrl }}" alt="Current image" class="h-16 w-16 object-cover rounded-lg shadow-sm">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ $existingImageName ?: 'Imagen actual' }}</p>
                    <p class="text-xs text-green-600">✓ uploaded</p>
                </div>
                <button 
                    type="button" 
                    class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition-colors"
                    onclick="if(confirm('¿Estás seguro de que quieres eliminar esta imagen?')) { 
                        @this.call('{{ $removeMethod }}', '{{ $uniqueId }}', '{{ $side }}');
                    }"
                    title="Eliminar imagen"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- File Preview -->
    @if($showPreview)
        <div x-show="files.length > 0" class="space-y-2">
            <template x-for="(file, index) in files" :key="index">
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <!-- Show PDF icon for PDF files, image preview for others -->
                    <div class="h-12 w-12 flex items-center justify-center rounded shadow-sm" 
                         :class="file.file && file.file.type === 'application/pdf' ? 'bg-red-100' : ''">
                        <template x-if="file.file && file.file.type === 'application/pdf'">
                            <!-- PDF Icon -->
                            <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                <path d="M10.5,11.5C10.5,12.3 9.8,13 9,13H8V15H6.5V9H9C9.8,9 10.5,9.7 10.5,10.5V11.5M9,10.5H8V11.5H9V10.5Z" />
                                <path d="M12.5,9H14.5C15.3,9 16,9.7 16,10.5V11.5C16,12.3 15.3,13 14.5,13H13V15H11.5V9H12.5M14.5,10.5H13V11.5H14.5V10.5Z" />
                                <path d="M17.5,9H19V10.5H17.5V11.5H19V13H17.5V15H16V9H17.5Z" />
                            </svg>
                        </template>
                        <template x-if="!file.file || file.file.type !== 'application/pdf'">
                            <img :src="file.url" :alt="file.name" class="h-12 w-12 object-cover rounded shadow-sm">
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                        <p class="text-xs text-blue-600" x-show="file.uploaded">✓ Uploaded</p>
                        <p class="text-xs text-gray-500" x-show="!file.uploaded">Ready to upload</p>
                    </div>
                    <button 
                        type="button" 
                        class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition-colors"
                        @click="removeFile(index)"
                        title="Eliminar archivo"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    @endif

    <!-- Upload Button -->
    <div x-show="files.length > 0 && !uploading">
        <button 
            type="button" 
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"
            @click="uploadFiles()"
        >
            Upload Files
        </button>
    </div>

    <!-- Error Message -->
    <div x-show="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600" x-text="error"></p>
    </div>

    <!-- Success Message -->
    <div x-show="success" class="p-3 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-600" x-text="success"></p>
    </div>

    <!-- Help Text -->
    @if($helpText)
        <p class="text-sm text-gray-500">{{ $helpText }}</p>
    @endif
</div>