<div class="box box--stacked flex flex-col p-5">
    <div class="mb-6 border-b border-dashed border-slate-300/70 pb-5 text-[0.94rem] font-medium">
        Document Progress Overview
    </div>
    
    <!-- Progress Bar -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-slate-700">Overall Progress</span>
            <span class="text-sm font-bold text-primary">{{ $progress['progress_percentage'] }}%</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-3">
            <div class="bg-gradient-to-r from-primary to-primary/80 h-3 rounded-full transition-all duration-500" 
                 style="width: {{ $progress['progress_percentage'] }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-slate-500 mt-1">
            <span>{{ $progress['completed'] }} of {{ $progress['total'] }} completed</span>
            <span>{{ $progress['total'] - $progress['completed'] }} remaining</span>
        </div>
    </div>
    
    <!-- Statistics Grid -->
    <div class="grid grid-cols-2 gap-4">
        <!-- Completed Documents -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Completed</p>
                    <p class="text-2xl font-bold text-green-700">{{ $progress['approved'] }}</p>
                </div>
                <div class="p-2 bg-green-200 rounded-lg">
                    <x-base.lucide class="w-5 h-5 text-green-600" icon="CheckCircle" />
                </div>
            </div>
        </div>
        
        <!-- Pending Documents -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">Pending</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ $progress['pending'] }}</p>
                </div>
                <div class="p-2 bg-yellow-200 rounded-lg">
                    <x-base.lucide class="w-5 h-5 text-yellow-600" icon="Clock" />
                </div>
            </div>
        </div>
        
        <!-- Rejected Documents -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-red-600 uppercase tracking-wide">Rejected</p>
                    <p class="text-2xl font-bold text-red-700">{{ $progress['rejected'] }}</p>
                </div>
                <div class="p-2 bg-red-200 rounded-lg">
                    <x-base.lucide class="w-5 h-5 text-red-600" icon="XCircle" />
                </div>
            </div>
        </div>
        
        <!-- Total Documents -->
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 p-4 rounded-lg border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-600 uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-slate-700">{{ $progress['total'] }}</p>
                </div>
                <div class="p-2 bg-slate-200 rounded-lg">
                    <x-base.lucide class="w-5 h-5 text-slate-600" icon="FileText" />
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="mt-6 pt-4 border-t border-slate-200">
        <h4 class="text-sm font-medium text-slate-700 mb-3">Quick Actions</h4>
        <div class="space-y-2">
            <x-base.button variant="outline-primary" size="sm" onclick="showMissingDocuments()" class="w-full justify-start">
                <x-base.lucide class="w-4 h-4 mr-2" icon="AlertCircle" />
                Show Missing Documents
            </x-base.button>
            <x-base.button variant="outline-secondary" size="sm" onclick="refreshProgress()" class="w-full justify-start">
                <x-base.lucide class="w-4 h-4 mr-2" icon="RefreshCw" />
                Refresh Progress
            </x-base.button>
        </div>
    </div>
</div>