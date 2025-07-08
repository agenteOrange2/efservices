<!-- Modal para subir Clearing House -->
<div x-data="{ open: false, isUploading: false, fileSelected: false }" 
     x-init="$wire.on('open-clearing-house-modal', () => { open = true });
             $wire.on('close-clearing-house-modal', () => { open = false; isUploading = false; });"
     x-show="open" 
     x-cloak 
     class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s] overflow-y-auto show">
    
    <!-- Contenido del Modal -->
    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-4"
         class="mb-6 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg overflow-hidden max-w-lg w-full">
            <div class="px-4 py-5 sm:px-6 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-slate-900">Upload Clearing House</h3>
                <button @click="$dispatch('close-clearing-house-modal')" class="text-slate-400 hover:text-slate-600" :disabled="isUploading">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Formulario de carga de archivos -->
            <div class="px-4 py-5 sm:p-6">
                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                
                <div class="mb-4">
                    <label for="clearing-house-file" class="block text-sm font-medium text-slate-700 mb-2">
                        Select Clearing House File
                    </label>
                    <input type="file" wire:model.live="documentFile" id="clearing-house-file" 
                           x-on:change="fileSelected = $event.target.files.length > 0"
                           class="block w-full text-sm text-slate-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded file:border-0
                                  file:text-sm file:font-medium
                                  file:bg-primary file:text-white
                                  hover:file:bg-primary-dark" accept=".jpg,.jpeg,.png,.pdf"
                           :disabled="isUploading">
                    @error('documentFile')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                    <div x-show="!fileSelected && !isUploading" class="text-yellow-600 text-xs mt-1">Please select a file to upload</div>
                </div>                                
            </div>
            
            <div class="px-4 py-3 bg-slate-50 text-right sm:px-6 flex justify-end space-x-2">
                <button type="button" @click="$dispatch('close-clearing-house-modal')" :disabled="isUploading"
                        class="inline-flex justify-center py-2 px-4 border border-slate-300 shadow-sm text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancel
                </button>
                <button type="button" 
                        wire:click="uploadClearingHouse"
                        x-on:click="isUploading = true"
                        :disabled="isUploading || !fileSelected"
                        class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isUploading ? 'Uploading...' : 'Upload'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
