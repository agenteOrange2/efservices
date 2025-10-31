<div class="box box--stacked flex flex-col p-5">
    <div class="mb-6 border-b border-dashed border-slate-300/70 pb-5 text-[0.94rem] font-medium">
        Filter Documents
    </div>
    
    <!-- Status Filters -->
    <div class="mb-6">
        <h4 class="text-sm font-medium text-slate-700 mb-3">By Status</h4>
        <div class="space-y-2">
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="status_filter" value="all" class="mr-2" checked>
                <span class="text-sm text-slate-600">All Documents</span>
                <span class="ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full">{{ $documentStats['all'] }}</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="status_filter" value="uploaded" class="mr-2">
                <span class="text-sm text-slate-600">Uploaded</span>
                <span class="ml-auto text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full">{{ $documentStats['uploaded'] }}</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="status_filter" value="pending" class="mr-2">
                <span class="text-sm text-slate-600">Pending</span>
                <span class="ml-auto text-xs bg-yellow-100 text-yellow-600 px-2 py-1 rounded-full">{{ $documentStats['pending'] }}</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="status_filter" value="missing" class="mr-2">
                <span class="text-sm text-slate-600">Missing</span>
                <span class="ml-auto text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full">{{ $documentStats['missing'] }}</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="status_filter" value="rejected" class="mr-2">
                <span class="text-sm text-slate-600">Rejected</span>
                <span class="ml-auto text-xs bg-red-200 text-red-700 px-2 py-1 rounded-full">{{ $documentStats['rejected'] ?? 0 }}</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="status_filter" value="default-available" class="mr-2">
                <span class="text-sm text-slate-600">Default Available</span>
                <span class="ml-auto text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full">{{ $documentStats['default-available'] }}</span>
            </label>
        </div>
    </div>
    
    <!-- Requirement Filters -->
    <div class="mb-6">
        <h4 class="text-sm font-medium text-slate-700 mb-3">By Requirement</h4>
        <div class="space-y-2">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="requirement_filter" value="mandatory" class="mr-2" checked>
                <span class="text-sm text-slate-600">Mandatory</span>
                <span class="ml-auto text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full">{{ $documentStats['mandatory'] }}</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="requirement_filter" value="optional" class="mr-2" checked>
                <span class="text-sm text-slate-600">Optional</span>
                <span class="ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full">{{ $documentStats['optional'] }}</span>
            </label>
        </div>
    </div>
    
    <!-- Clear Filters -->
    <div class="mt-auto">
        <x-base.button variant="outline-secondary" size="sm" onclick="clearFilters()" class="w-full">
            <x-base.lucide class="w-4 h-4 mr-1" icon="RotateCcw" />
            Clear Filters
        </x-base.button>
    </div>
</div>