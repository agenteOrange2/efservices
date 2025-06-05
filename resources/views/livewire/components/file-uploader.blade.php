<div
    x-data="{
        existingFiles: @entangle('existingFiles').live,
        removeDocument(id) {
            // Esta función se llama cuando se elimina un documento
            // y actualiza la interfaz inmediatamente
            this.existingFiles = this.existingFiles.filter(doc => doc.id != id);
        }
    }"
    @document-deleted.window="removeDocument($event.detail.mediaId)"
>
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
                    @this.uploadMultiple('files', e.dataTransfer.files, (uploadedFilename) => {}, () => {}, (event) => {
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
                            <input type="file" wire:model="files" class="hidden" accept="{{ $accept }}" multiple>
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
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V11C19 11.5523 19.4477 12 20 12C20.5523 12 21 11.5523 21 11V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM10.3078 23.5628C10.4657 23.7575 10.6952 23.9172 10.9846 23.9762C11.2556 24.0316 11.4923 23.981 11.6563 23.9212C11.9581 23.8111 12.1956 23.6035 12.3505 23.4506C12.5941 23.2105 12.8491 22.8848 13.1029 22.5169C14.2122 22.1342 15.7711 21.782 17.287 21.5602C18.1297 21.4368 18.9165 21.3603 19.5789 21.3343C19.8413 21.6432 20.08 21.9094 20.2788 22.1105C20.4032 22.2363 20.5415 22.3671 20.6768 22.4671C20.7378 22.5122 20.8519 22.592 20.999 22.6493C21.0755 22.6791 21.5781 22.871 22.0424 22.4969C22.3156 22.2768 22.5685 22.0304 22.7444 21.7525C22.9212 21.4733 23.0879 21.0471 22.9491 20.5625C22.8131 20.0881 22.4588 19.8221 22.198 19.6848C21.9319 19.5448 21.6329 19.4668 21.3586 19.4187C21.11 19.3751 20.8288 19.3478 20.5233 19.3344C19.9042 18.5615 19.1805 17.6002 18.493 16.6198C17.89 15.76 17.3278 14.904 16.891 14.1587C16.9359 13.9664 16.9734 13.7816 17.0025 13.606C17.0523 13.3052 17.0824 13.004 17.0758 12.7211C17.0695 12.4497 17.0284 12.1229 16.88 11.8177C16.7154 11.4795 16.416 11.1716 15.9682 11.051C15.5664 10.9428 15.1833 11.0239 14.8894 11.1326C14.4359 11.3004 14.1873 11.6726 14.1014 12.0361C14.0288 12.3437 14.0681 12.6407 14.1136 12.8529C14.2076 13.2915 14.4269 13.7956 14.6795 14.2893C14.702 14.3332 14.7251 14.3777 14.7487 14.4225C14.5103 15.2072 14.1578 16.1328 13.7392 17.0899C13.1256 18.4929 12.4055 19.8836 11.7853 20.878C11.3619 21.0554 10.9712 21.2584 10.6746 21.4916C10.4726 21.6505 10.2019 21.909 10.0724 22.2868C9.9132 22.7514 10.0261 23.2154 10.3078 23.5628ZM11.8757 23.0947C11.8755 23.0946 11.8775 23.0923 11.8824 23.0877C11.8783 23.0924 11.8759 23.0947 11.8757 23.0947ZM16.9974 19.5812C16.1835 19.7003 15.3445 19.8566 14.5498 20.0392C14.9041 19.3523 15.2529 18.6201 15.5716 17.8914C15.7526 17.4775 15.9269 17.0581 16.0885 16.6431C16.336 17.0175 16.5942 17.3956 16.8555 17.7681C17.2581 18.3421 17.6734 18.911 18.0759 19.4437C17.7186 19.4822 17.3567 19.5287 16.9974 19.5812ZM16.0609 12.3842C16.0608 12.3829 16.0607 12.3823 16.0606 12.3823C16.0606 12.3822 16.0607 12.3838 16.061 12.3872C16.061 12.386 16.0609 12.385 16.0609 12.3842Z" fill="#c81414"></path> </g></svg>
                            </div>
                        @elseif(Str::contains($doc['mime_type'], 'word') || Str::contains($doc['mime_type'], 'doc'))
                            <div class="h-16 w-16 flex items-center justify-center bg-blue-50 rounded-md border border-gray-200">
                                <svg class="h-8 w-8"  viewBox="0 0 24.00 24.00" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#000000" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H10C10.5523 23 11 22.5523 11 22C11 21.4477 10.5523 21 10 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM12.952 12.694C12.783 12.1682 12.2198 11.879 11.694 12.048C11.1682 12.217 10.879 12.7802 11.048 13.306L13.298 20.306C13.4309 20.7196 13.8156 21 14.25 21C14.6844 21 15.0691 20.7196 15.202 20.306L16.5 16.2679L17.798 20.306C17.9309 20.7196 18.3156 21 18.75 21C19.1844 21 19.5691 20.7196 19.702 20.306L21.952 13.306C22.121 12.7802 21.8318 12.217 21.306 12.048C20.7802 11.879 20.217 12.1682 20.048 12.694L18.75 16.7321L17.452 12.694C17.3191 12.2804 16.9344 12 16.5 12C16.0656 12 15.6809 12.2804 15.548 12.694L14.25 16.7321L12.952 12.694Z" fill="#0a44b8"></path> </g></svg>
                            </div>
                        @else
                            <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#000000" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M9.29289 1.29289C9.48043 1.10536 9.73478 1 10 1H18C19.6569 1 21 2.34315 21 4V20C21 21.6569 19.6569 23 18 23H6C4.34315 23 3 21.6569 3 20V8C3 7.73478 3.10536 7.48043 3.29289 7.29289L9.29289 1.29289ZM18 3H11V8C11 8.55228 10.5523 9 10 9H5V20C5 20.5523 5.44772 21 6 21H18C18.5523 21 19 20.5523 19 20V4C19 3.44772 18.5523 3 18 3ZM6.41421 7H9V4.41421L6.41421 7Z" fill="#0065d1"></path> </g></svg>
                            </div>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium truncate" title="{{ $doc['name'] }}">{{ Str::limit($doc['name'], 25, '...') }}</p>
                        <p class="text-xs text-gray-500">{{ round($doc['size'] / 1024, 2) }} KB · {{ \Carbon\Carbon::parse($doc['created_at'])->format('M d, Y') }}</p>
                        <div class="flex mt-2 space-x-2">
                            <a href="{{ $doc['url'] }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <button type="button" wire:click="removeFile('{{ $doc['id'] }}')" class="text-xs text-red-600 hover:text-red-800 flex items-center">
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
