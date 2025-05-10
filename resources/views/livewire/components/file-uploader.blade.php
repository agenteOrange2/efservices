<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <div class="mt-4 mb-4">
        <label class="block text-sm font-medium mb-2">{{ $label }}</label>
        <div
            x-data="{
                isUploading: false,
                progress: 0,
                isDragging: false,
                handleDrop(e) {
                    e.preventDefault();
                    this.isDragging = false;
                    @this.upload('files', e.dataTransfer.files[0], (uploadedFilename) => {}, () => {}, (event) => {
                        this.isUploading = true;
                        this.progress = event.detail.progress;
                    });
                }
            }"
            class="relative border-2 border-dashed rounded-md p-6 transition-all"
            :class="{ 'border-primary bg-primary/5': isUploading || isDragging, 'border-gray-300 hover:border-primary/50 hover:bg-gray-50': !isUploading && !isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop($event)"
        >
            <div class="text-center">
                <template x-if="isDragging">
                    <div>
                        <i class="fas fa-file-import text-3xl text-primary mb-2 animate-bounce"></i>
                        <p class="text-sm text-primary font-medium">Drop file to upload</p>
                    </div>
                </template>
                
                <template x-if="!isDragging && !isUploading">
                    <div>
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Drag and drop files here or</p>
                        <label class="mt-2 inline-block px-4 py-2 bg-primary text-white rounded-md cursor-pointer hover:bg-primary-dark transition">
                            <span>Select Files</span>
                            <input type="file" wire:model="files" class="hidden" accept="{{ $accept }}">
                        </label>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                    </div>
                </template>
                
                <template x-if="isUploading && !isDragging">
                    <div>
                        <i class="fas fa-spinner fa-spin text-3xl text-primary mb-2"></i>
                        <p class="text-sm text-primary font-medium">Uploading file...</p>
                    </div>
                </template>
            </div>
            
            <!-- Upload Progress -->
            <div x-show="isUploading" class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-primary h-2.5 rounded-full" :style="{ width: progress + '%' }"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-text="'Uploading: ' + progress + '%'"></p>
            </div>
        </div>
    </div>
    
    <!-- Documentos existentes -->
    @if(!empty($existingFiles))
    <div class="mt-4">
        <h5 class="text-sm font-medium mb-2 flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            <span>Uploaded Documents ({{ count($existingFiles) }})</span>
        </h5>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($existingFiles as $doc)
                <div class="border border-gray-200 rounded-md p-3 flex items-start hover:shadow-md transition-shadow bg-white">
                    <div class="flex-shrink-0">
                        @if(Str::contains($doc['mime_type'], 'image'))
                            <a href="{{ $doc['url'] }}" target="_blank" class="block">
                                <img src="{{ $doc['url'] }}" alt="{{ $doc['name'] }}" class="h-16 w-16 object-cover rounded-md border border-gray-200 hover:border-primary transition">
                            </a>
                        @elseif(Str::contains($doc['mime_type'], 'pdf'))
                            <div class="h-16 w-16 flex items-center justify-center bg-red-50 rounded-md border border-gray-200">
                                <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                            </div>
                        @elseif(Str::contains($doc['mime_type'], 'word') || Str::contains($doc['mime_type'], 'doc'))
                            <div class="h-16 w-16 flex items-center justify-center bg-blue-50 rounded-md border border-gray-200">
                                <i class="fas fa-file-word text-blue-500 text-xl"></i>
                            </div>
                        @else
                            <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium truncate" title="{{ $doc['name'] }}">{{ $doc['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ round($doc['size'] / 1024, 2) }} KB · {{ \Carbon\Carbon::parse($doc['created_at'])->format('M d, Y') }}</p>
                        <div class="flex mt-2 space-x-2">
                            <a href="{{ $doc['url'] }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <button type="button" wire:click="removeFile({{ $doc['id'] }})" class="text-xs text-red-600 hover:text-red-800 flex items-center">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="mt-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 flex items-center">
            <i class="fas fa-exclamation-circle text-yellow-500 mr-2 text-lg"></i>
            <div>
                <p class="text-sm text-yellow-700">No documents uploaded yet</p>
                <p class="text-xs text-yellow-600 mt-1">Please upload at least one document using the area above.</p>
            </div>
        </div>
    </div>
    @endif
</div>
