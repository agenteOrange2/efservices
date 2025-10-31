<div class="col-span-12">
    <form id="upload-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="document_type_id" name="document_type_id">
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Document Type: <span id="document-type-name" class="font-semibold text-primary"></span>
            </label>
        </div>
        
        <div class="mb-4">
            <div class="upload-area border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-primary/50 transition-colors cursor-pointer" 
                 id="upload-area"
                 ondrop="handleDrop(event)" 
                 ondragover="handleDragOver(event)" 
                 ondragleave="handleDragLeave(event)">
                <div class="text-center">
                    <x-base.lucide class="mx-auto h-12 w-12 text-slate-400 mb-4" icon="Upload" />
                    <div class="text-sm text-slate-600">
                        <label for="file-upload" class="cursor-pointer font-medium text-primary hover:text-primary/80">
                            Click to upload
                        </label>
                        or drag and drop
                    </div>
                    <p class="text-xs text-slate-500 mt-1">PDF, PNG, JPG up to 10MB</p>
                </div>
                <input id="file-upload" name="file" type="file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg" onchange="handleFileSelect(event)">
            </div>
        </div>
        
        <div id="file-preview" class="hidden mb-4">
            <div class="flex items-center p-3 bg-slate-50 rounded-lg border">
                <x-base.lucide class="h-8 w-8 text-slate-400 mr-3" icon="FileText" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-900" id="file-name"></p>
                    <p class="text-xs text-slate-500" id="file-size"></p>
                </div>
                <button type="button" onclick="removeFile()" class="text-slate-400 hover:text-slate-600 ml-2">
                    <x-base.lucide class="h-5 w-5" icon="X" />
                </button>
            </div>
        </div>
        
        <div id="upload-progress" class="hidden mb-4">
            <div class="bg-slate-200 rounded-full h-2 mb-2">
                <div id="progress-bar" class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p class="text-xs text-slate-500 text-center" id="progress-text">Uploading...</p>
        </div>
        
        <div id="upload-error" class="hidden mb-4">
            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                <div class="flex">
                    <x-base.lucide class="h-5 w-5 text-red-400 mr-2" icon="AlertCircle" />
                    <div class="text-sm text-red-700" id="error-message"></div>
                </div>
            </div>
        </div>
    </form>
</div>