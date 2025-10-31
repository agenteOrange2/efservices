@props(['driver', 'stats' => []])

@php
    $defaultStats = [
        'completion_percentage' => $driver->completion_percentage ?? 0,
        'total_licenses' => $driver->driverLicenses ? $driver->driverLicenses->count() : 0,
        'medical_status' => $driver->driverMedicalQualification ? 'Valid' : 'Pending',
        'total_documents' => $driver->getMedia('*')->count() ?? 0,
        'years_experience' => $driver->driverExperiences ? $driver->driverExperiences->sum('years') : 0,
        'last_updated' => $driver->updated_at ? $driver->updated_at->diffForHumans() : 'Never'
    ];
    
    $statsData = array_merge($defaultStats, $stats);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    <!-- Profile Completion -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <x-base.lucide icon="user-check" class="w-5 h-5 text-blue-600" />
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Profile Completion</p>
                    <div class="flex items-center mt-1">
                        <p class="text-2xl font-semibold text-gray-900">{{ $statsData['completion_percentage'] }}%</p>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ $statsData['completion_percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Licenses -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <x-base.lucide icon="credit-card" class="w-5 h-5 text-green-600" />
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Active Licenses</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statsData['total_licenses'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">CDL & Endorsements</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Medical Status -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 {{ $statsData['medical_status'] === 'Valid' ? 'bg-green-100' : 'bg-yellow-100' }} rounded-lg flex items-center justify-center">
                        <x-base.lucide icon="heart-pulse" class="w-5 h-5 {{ $statsData['medical_status'] === 'Valid' ? 'text-green-600' : 'text-yellow-600' }}" />
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Medical Status</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statsData['medical_status'] }}</p>
                    @if($driver->driverMedicalQualification && $driver->driverMedicalQualification->expiration_date)
                        <p class="text-xs text-gray-400 mt-1">
                            Expires {{ \Carbon\Carbon::parse($driver->driverMedicalQualification->expiration_date)->format('M d, Y') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Total Documents -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <x-base.lucide icon="file-text" class="w-5 h-5 text-purple-600" />
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Documents</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statsData['total_documents'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Files uploaded</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Years of Experience -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <x-base.lucide icon="clock" class="w-5 h-5 text-orange-600" />
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Experience</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statsData['years_experience'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Years driving</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Last Updated -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <x-base.lucide icon="calendar" class="w-5 h-5 text-gray-600" />
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Last Updated</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $statsData['last_updated'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Profile changes</p>
                </div>
            </div>
        </div>
    </div>
</div>