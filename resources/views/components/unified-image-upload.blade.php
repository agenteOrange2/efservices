@props([
    'inputName' => 'images',
    'multiple' => false,
    'maxFiles' => 1,
    'acceptedTypes' => 'image/*',
    'maxSize' => 5, // MB
    'showPreview' => true,
    'existingFiles' => [],
    'required' => false,
    'label' => 'Upload Images',
    'helpText' => 'Drag and drop images here or click to browse',
    'modelType' => null,
    'modelId' => null,
    'collection' => 'documents',
    'customProperties' => [],
    'value' => null,
    'temporaryStorage' => false,
    'storageKey' => null
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
    collection: @js($collection),
    customProperties: @js($customProperties),
    temporaryStorage: @js($temporaryStorage),
    storageKey: @js($storageKey),
    
    init() {
        @if($value)
            this.files = [{ name: '{{ $value }}', url: '{{ asset('storage/' . $value) }}', uploaded: true }];
        @endif
    },
    
    handleFiles(fileList) {
        this.error = null;
        const newFiles = Array.from(fileList);
        
        if (!{{ $multiple ? 'true' : 'false' }}) {
            this.files = [];
        }
        
        newFiles.forEach(file => {
            if (file.size > {{ $maxSize }} * 1024) {
                this.error = 'File ' + file.name + ' is too large. Maximum size is {{ $maxSize }}KB.';
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                this.error = 'File ' + file.name + ' is not a valid image.';
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
    
    removeFile(index) {
        this.files.splice(index, 1);
        this.error = null;
        this.success = null;
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
                this.error = 'Temporary storage failed: ' + error.message;
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
                
                // Step 1: Temporary upload
                const tempFormData = new FormData();
                tempFormData.append('file', fileObj.file);
                tempFormData.append('type', 'document');
                
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
                
                this.progress = Math.round(((i + 1) / this.files.length) * 100);
            }
            
            this.success = 'Files uploaded and stored successfully!';
            
        } catch (error) {
            this.error = 'Upload failed: ' + error.message;
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
                :accept="accept"
                :multiple="multiple"
                :required="required"
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

    <!-- File Preview -->
    @if($showPreview)
        <div x-show="files.length > 0" class="space-y-2">
            <template x-for="(file, index) in files" :key="index">
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <img :src="file.url" :alt="file.name" class="h-12 w-12 object-cover rounded">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                        <p class="text-xs text-gray-500" x-show="file.uploaded">Uploaded</p>
                    </div>
                    <button 
                        type="button" 
                        class="text-red-600 hover:text-red-800"
                        @click="removeFile(index)"
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