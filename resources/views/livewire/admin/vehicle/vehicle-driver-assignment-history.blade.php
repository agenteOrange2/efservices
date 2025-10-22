<div class="space-y-6">
    <!-- Current Assignment Section -->
    @if($currentAssignment)
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Current Driver Assignment</h3>
                <span class="px-3 py-1 text-xs font-medium rounded-full
                    @if($currentAssignment->assignment_type === 'company_driver') bg-blue-100 text-blue-800
                    @elseif($currentAssignment->assignment_type === 'owner_operator') bg-green-100 text-green-800
                    @elseif($currentAssignment->assignment_type === 'third_party') bg-purple-100 text-purple-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $currentAssignment->assignment_type)) }}
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium text-slate-900">{{ $this->getDriverDisplayName($currentAssignment) }}</div>
                            @php $details = $this->getDriverDetails($currentAssignment) @endphp
                            
                            @if($currentAssignment->driver_type === 'third_party')
                                <!-- Información del conductor -->
                                @if($details['driver_name'])
                                    <div class="text-sm text-slate-600 mt-1">
                                        <strong>Driver:</strong> {{ $details['driver_name'] }}
                                    </div>
                                    @if($details['driver_email'])
                                        <div class="text-xs text-slate-500">{{ $details['driver_email'] }}</div>
                                    @endif
                                    @if($details['driver_phone'])
                                        <div class="text-xs text-slate-500">{{ $details['driver_phone'] }}</div>
                                    @endif
                                @endif
                                
                                <!-- Información de la empresa -->
                                @if($details['company_name'])
                                    <div class="text-sm text-slate-600 mt-2">
                                        <strong>Company:</strong> {{ $details['company_name'] }}
                                    </div>
                                    @if($details['company_email'])
                                        <div class="text-xs text-slate-500">{{ $details['company_email'] }}</div>
                                    @endif
                                    @if($details['company_phone'])
                                        <div class="text-xs text-slate-500">{{ $details['company_phone'] }}</div>
                                    @endif
                                    @if($details['contact'])
                                        <div class="text-xs text-slate-500"><strong>Contact:</strong> {{ $details['contact'] }}</div>
                                    @endif
                                    @if($details['fein'])
                                        <div class="text-xs text-slate-500"><strong>FEIN:</strong> {{ $details['fein'] }}</div>
                                    @endif
                                @endif
                            @else
                                <!-- Para otros tipos de asignación (company_driver, owner_operator) -->
                                @if($details['email'])
                                    <div class="text-sm text-slate-500">{{ $details['email'] }}</div>
                                @endif
                                @if($details['phone'])
                                    <div class="text-sm text-slate-500">{{ $details['phone'] }}</div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-sm text-slate-500 mb-2">
                        <strong>Effective:</strong> {{ $currentAssignment->start_date ? $currentAssignment->start_date->format('M d, Y') : 'N/A' }}
                    </div>
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('admin.vehicles.assign-driver-type', $vehicle->id) }}"
                           class="inline-flex items-center px-3 py-1 border border-slate-300 rounded-md text-xs font-medium text-slate-700 bg-white hover:bg-slate-50">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            Change
                        </a>
                        <button wire:click="removeAssignment({{ $currentAssignment->id }})"
                                wire:confirm="Are you sure you want to remove this driver assignment?"
                                class="inline-flex items-center px-3 py-1 border border-red-300 rounded-md text-xs font-medium text-red-700 bg-white hover:bg-red-50">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Remove
                        </button>
                    </div>
                </div>
            </div>
            
            @if($currentAssignment->notes)
                <div class="mt-4 p-3 bg-slate-50 rounded-md">
                    <div class="text-sm text-slate-600">
                        <strong>Notes:</strong> {{ $currentAssignment->notes }}
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-yellow-800">No Driver Assigned</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            This vehicle does not have a current driver assignment.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.vehicles.assign-driver-type', $vehicle->id) }}"
                   class="inline-flex items-center px-2 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Assign Driver
                </a>
            </div>
        </div>
    @endif

    <!-- Assignment History Section -->
    @if($assignmentHistory->count() > 0)
        <div class="bg-white rounded-lg border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200">
                <button wire:click="toggleHistory" 
                        class="flex items-center justify-between w-full text-left">
                    <h3 class="text-lg font-semibold text-slate-800">
                        Driver Assignment History ({{ $assignmentHistory->count() }} {{ $assignmentHistory->count() === 1 ? 'record' : 'records' }})
                    </h3>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform {{ $showHistory ? 'rotate-180' : '' }}" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            @if($showHistory)
                <div class="divide-y divide-slate-200">
                    @foreach($assignmentHistory as $assignment)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900">{{ $this->getDriverDisplayName($assignment) }}</div>
                                        @php $details = $this->getDriverDetails($assignment) @endphp
                                        <div class="text-sm text-slate-500">{{ $details['type'] }}</div>
                                        
                                        @if($assignment->driver_type === 'third_party')
                                            <!-- Información del conductor en historial -->
                                            @if($details['driver_name'])
                                                <div class="text-xs text-slate-400 mt-1">
                                                    <strong>Conductor:</strong> {{ $details['driver_name'] }}
                                                </div>
                                                @if($details['driver_email'])
                                                    <div class="text-xs text-slate-400">{{ $details['driver_email'] }}</div>
                                                @endif
                                            @endif
                                            
                                            <!-- Información de la empresa en historial -->
                                            @if($details['company_name'])
                                                <div class="text-xs text-slate-400 mt-1">
                                                    <strong>Empresa:</strong> {{ $details['company_name'] }}
                                                </div>
                                                @if($details['company_email'])
                                                    <div class="text-xs text-slate-400">{{ $details['company_email'] }}</div>
                                                @endif
                                            @endif
                                        @else
                                            <!-- Para otros tipos de asignación en historial -->
                                            @if($details['email'])
                                                <div class="text-xs text-slate-400">{{ $details['email'] }}</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                        Ended
                                    </span>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ $assignment->start_date ? $assignment->start_date->format('M d, Y') : 'N/A' }} - {{ $assignment->end_date ? $assignment->end_date->format('M d, Y') : 'N/A' }}
                                    </div>
                                    @if($assignment->start_date && $assignment->end_date)
                                        <div class="text-xs text-slate-400">
                                            Duration: {{ $assignment->start_date->diffInDays($assignment->end_date) }} days
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($assignment->notes)
                                <div class="mt-3 ml-11 p-2 bg-slate-50 rounded text-xs text-slate-600">
                                    <strong>Notes:</strong> {{ $assignment->notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('driver_assignment_success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {{ session('driver_assignment_success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('driver_assignment_error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">
                        {{ session('driver_assignment_error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>