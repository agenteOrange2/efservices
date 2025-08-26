// Unified Image Upload Component for Alpine.js
function unifiedImageUpload(config) {
    return {
        files: config.files || [],
        uploading: false,
        uploadProgress: 0,
        dragOver: false,
        error: null,
        success: null,
        inputId: config.inputId,
        maxSize: config.maxSize || 2048,
        maxWidth: config.maxWidth || 1920,
        maxHeight: config.maxHeight || 1080,
        quality: config.quality || 0.8,
        uploadUrl: config.uploadUrl || '/api/documents/upload',
        multiple: config.multiple || false,
        wireModel: config.wireModel || null,
        
        init() {
            this.$nextTick(() => {
                this.setupEventListeners();
                this.preservePreviewOnLivewireUpdate();
                this.restoreFilesFromStorage();
            });
        },
        
        setupEventListeners() {
            const input = document.getElementById(this.inputId);
            if (!input) {
                console.warn('File input not found:', this.inputId);
                return;
            }
            
            // Let Livewire handle the file input directly
            // We'll use handleFileInput method for additional processing
        },
        
        restoreFilesFromStorage() {
            const storageKey = `unified_image_upload_${this.inputId}`;
            const storedFiles = sessionStorage.getItem(storageKey);
            if (storedFiles) {
                try {
                    const parsedFiles = JSON.parse(storedFiles);
                    if (parsedFiles.length > 0) {
                        this.files = parsedFiles;
                    }
                } catch (e) {
                    console.warn('Failed to restore files from storage:', e);
                }
            }
        },
        
        preservePreviewOnLivewireUpdate() {
            // Solo escuchar actualizaciones de Livewire
            document.addEventListener('livewire:updated', () => {
                this.restoreFilesFromStorage();
            });
            
            // Escuchar eventos de archivos removidos desde Livewire
            document.addEventListener('file-removed', (e) => {
                if (e.detail && e.detail.fieldName === this.wireModel) {
                    this.files = [];
                    this.clearSessionStorage();
                }
            });
            
            // Observar cambios en los archivos para actualizar sessionStorage
            this.$watch('files', (newFiles) => {
                this.storeFilesInSession(newFiles);
            });
        },
        
        handleFileInput(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                this.error = null;
                
                // Create preview files for immediate display
                const fileArray = Array.from(files).map(file => ({
                    id: Date.now() + Math.random(),
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    url: URL.createObjectURL(file),
                    isPreview: true
                }));
                
                if (this.multiple) {
                    this.files = [...this.files, ...fileArray];
                } else {
                    this.files = fileArray;
                }
                
                // Store in sessionStorage for persistence
                const storageKey = `unified_image_upload_${this.inputId}`;
                sessionStorage.setItem(storageKey, JSON.stringify(this.files));
                
                this.success = `${files.length} file(s) selected successfully!`;
                
                // Clear success message after 3 seconds
                setTimeout(() => {
                    this.success = null;
                }, 3000);
            }
        },
        
        async handleFileSelect(fileList) {
            if (!fileList || fileList.length === 0) return;
            
            this.error = null;
            this.success = null;
            
            const files = Array.from(fileList);
            
            for (const file of files) {
                if (!this.validateFile(file)) continue;
                
                try {
                    const compressedFile = await this.compressImage(file);
                    await this.uploadFile(compressedFile, file.name);
                } catch (error) {
                    this.error = `Failed to process ${file.name}: ${error.message}`;
                    console.error('File processing error:', error);
                }
            }
        },
        
        validateFile(file) {
            // Check file type
            if (!file.type.startsWith('image/') && file.type !== 'application/pdf') {
                this.error = 'Please select an image file or PDF.';
                return false;
            }
            
            // Check file size (before compression)
            const maxSizeBytes = this.maxSize * 1024;
            if (file.size > maxSizeBytes * 2) { // Allow 2x size before compression
                this.error = `File size must be less than ${Math.round(this.maxSize / 1024 * 2)}MB.`;
                return false;
            }
            
            return true;
        },
        
        async compressImage(file) {
            // If it's a PDF, return as-is
            if (file.type === 'application/pdf') {
                return file;
            }
            
            return new Promise((resolve, reject) => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();
                
                img.onload = () => {
                    // Calculate new dimensions
                    let { width, height } = img;
                    
                    if (width > this.maxWidth || height > this.maxHeight) {
                        const ratio = Math.min(this.maxWidth / width, this.maxHeight / height);
                        width *= ratio;
                        height *= ratio;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    // Draw and compress
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob((blob) => {
                        if (blob) {
                            // Create a new File object with the compressed data
                            const compressedFile = new File([blob], file.name, {
                                type: file.type,
                                lastModified: Date.now()
                            });
                            resolve(compressedFile);
                        } else {
                            reject(new Error('Failed to compress image'));
                        }
                    }, file.type, this.quality);
                };
                
                img.onerror = () => reject(new Error('Failed to load image'));
                img.src = URL.createObjectURL(file);
            });
        },
        
        async uploadFile(file, originalName) {
            this.uploading = true;
            this.uploadProgress = 0;
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('original_name', originalName);
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            try {
                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`Upload failed: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    const fileData = {
                        id: result.file_id || Date.now(),
                        name: originalName,
                        url: result.file_url,
                        path: result.file_path,
                        size: file.size,
                        type: file.type
                    };
                    
                    if (this.multiple) {
                        this.files.push(fileData);
                    } else {
                        this.files = [fileData];
                    }
                    
                    this.success = `${originalName} uploaded successfully!`;
                    
                    // Livewire will handle the file upload automatically
                    // We just update the preview
                    
                    // Dispatch custom event
                    this.$dispatch('file-uploaded', {
                        file: fileData,
                        allFiles: this.files
                    });
                    
                } else {
                    throw new Error(result.message || 'Upload failed');
                }
                
            } catch (error) {
                this.error = error.message;
                console.error('Upload error:', error);
            } finally {
                this.uploading = false;
                this.uploadProgress = 0;
            }
        },
        
        removeFile(index) {
            const file = this.files[index];
            if (file && file.url && file.url.startsWith('blob:')) {
                URL.revokeObjectURL(file.url);
            }
            
            this.files.splice(index, 1);
            
            // Update sessionStorage
            this.storeFilesInSession(this.files);
            
            // Reset file input
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
            
            // Dispatch event to Livewire and parent components
            this.$dispatch('file-removed', {
                fieldName: this.wireModel,
                index: index
            });
            
            // También disparar evento personalizado para el formulario padre
            const event = new CustomEvent('file-removed', {
                detail: { fieldName: this.wireModel, index: index },
                bubbles: true
            });
            this.$el.dispatchEvent(event);
        },
        
        storeFilesInSession(files) {
            const storageKey = `unified_image_upload_${this.inputId}`;
            if (files.length === 0) {
                sessionStorage.removeItem(storageKey);
            } else {
                sessionStorage.setItem(storageKey, JSON.stringify(files));
            }
        },
        
        clearSessionStorage() {
            const storageKey = `unified_image_upload_${this.inputId}`;
            sessionStorage.removeItem(storageKey);
        },
        
        openFileDialog() {
            if (this.uploading) return;
            const input = document.getElementById(this.inputId);
            if (input) {
                input.click();
            } else {
                console.error('File input not found:', this.inputId);
            }
        },
        
        openCamera() {
            if (this.uploading) return;
            const input = document.getElementById(this.inputId);
            if (input) {
                input.setAttribute('capture', 'environment');
                input.click();
            } else {
                console.error('File input not found:', this.inputId);
            }
        },
        
        handleDragOver(e) {
            e.preventDefault();
            this.dragOver = true;
        },
        
        handleDragLeave(e) {
            e.preventDefault();
            this.dragOver = false;
        },
        
        handleDrop(e) {
            e.preventDefault();
            this.dragOver = false;
            
            if (this.uploading) return;
            
            const files = e.dataTransfer.files;
            this.handleFileSelect(files);
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };
}

// Make it globally available
window.unifiedImageUpload = unifiedImageUpload;