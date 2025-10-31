@props(['driver'])

@php
    $completionPercentage = $driver->completion_percentage ?? 0;
    $profileImage = $driver->getFirstMediaUrl('profile_image') ?: asset('images/default-avatar.png');
    $statusColor = match($driver->status ?? 'active') {
        'active' => 'bg-green-100 text-green-800',
        'inactive' => 'bg-red-100 text-red-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        default => 'bg-gray-100 text-gray-800'
    };
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="px-6 py-8">
        <!-- Profile Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-6">
            <!-- Profile Image -->
            <div class="flex-shrink-0 mx-auto sm:mx-0 mb-4 sm:mb-0">
                <div class="relative">
                    <img class="h-24 w-24 rounded-full object-cover border-4 border-white shadow-lg" 
                         src="{{ $profileImage }}" 
                         alt="{{ $driver->user->name ?? 'Driver' }}"
                         onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    <!-- Status Indicator -->
                    <div class="absolute bottom-0 right-0 h-6 w-6 rounded-full border-2 border-white {{ $driver->status === 'active' ? 'bg-green-400' : 'bg-red-400' }}"></div>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-xl font-bold text-gray-900">
                    {{ $driver->user->name ?? 'N/A' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $driver->user->email ?? 'No email provided' }}
                </p>
                
                <!-- Status and Details -->
                <div class="flex flex-wrap justify-center sm:justify-start items-center gap-3 mt-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                        <x-base.lucide icon="circle" class="w-2 h-2 mr-1 fill-current" />
                        {{ ucfirst($driver->status ?? 'active') }}
                    </span>
                    
                    @if($driver->phone)
                        <span class="inline-flex items-center text-xs text-gray-500">
                            <x-base.lucide icon="phone" class="w-3 h-3 mr-1" />
                            {{ $driver->phone }}
                        </span>
                    @endif
                    
                    @if($driver->created_at)
                        <span class="inline-flex items-center text-xs text-gray-500">
                            <x-base.lucide icon="calendar" class="w-3 h-3 mr-1" />
                            Joined {{ $driver->created_at->format('M Y') }}
                        </span>
                    @endif
                </div>

                <!-- Carrier Information -->
                @if($driver->carrier)
                    <div class="mt-3">
                        <span class="inline-flex items-center text-sm text-gray-600">
                            <x-base.lucide icon="building" class="w-4 h-4 mr-2" />
                            <span class="font-medium">{{ $driver->carrier->name }}</span>
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Profile Completion</span>
                <span class="text-sm font-semibold text-gray-900">{{ $completionPercentage }}%</span>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full transition-all duration-500 ease-out {{ $completionPercentage >= 80 ? 'bg-green-500' : ($completionPercentage >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                     style="width: {{ $completionPercentage }}%"></div>
            </div>
            
            <!-- Completion Status -->
            <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-gray-500">
                    @if($completionPercentage >= 80)
                        Profile is complete
                    @elseif($completionPercentage >= 50)
                        Profile needs attention
                    @else
                        Profile incomplete
                    @endif
                </span>
                @if($completionPercentage < 100)
                    <a href="#" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Complete Profile
                    </a>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $driver->driverLicenses ? $driver->driverLicenses->count() : 0 }}
                    </div>
                    <div class="text-xs text-gray-500">Licenses</div>
                </div>
                <div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $driver->driverExperiences ? $driver->driverExperiences->sum('years') : 0 }}
                    </div>
                    <div class="text-xs text-gray-500">Years Exp.</div>
                </div>
                <div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $driver->getMedia('*')->count() ?? 0 }}
                    </div>
                    <div class="text-xs text-gray-500">Documents</div>
                </div>
            </div>
        </div>
    </div>
</div>