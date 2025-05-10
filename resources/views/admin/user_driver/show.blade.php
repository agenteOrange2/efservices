@extends('../themes/' . $activeTheme)
@section('title', 'Driver Details')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers', 'url' => route('admin.carrier.user_drivers.index', $carrier->slug)],
        ['label' => 'Driver Details', 'active' => true],
    ];
    
    // Obtener estado de los pasos
    $stepsStatus = app(\App\Services\Admin\DriverStepService::class)->getStepsStatus($userDriverDetail);
    
    // Calcular completitud
    $completionPercentage = app(\App\Services\Admin\DriverStepService::class)->calculateCompletionPercentage($userDriverDetail);
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="intro-y flex items-center justify-between mt-8">
                <h2 class="text-lg font-medium">Driver Details</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $carrier, 'userDriverDetail' => $userDriverDetail]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-edit mr-1"></i> Edit Driver
                    </a>
                    <a href="{{ route('admin.carrier.user_drivers.index', $carrier) }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
            
            {{-- Driver Profile Header --}}
            <div class="box mt-5">
                <div class="box-body p-5">
                    <div class="flex flex-col md:flex-row">
                        <div class="flex-none md:mr-6">
                            <img src="{{ $userDriverDetail->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}" 
                                 class="w-32 h-32 rounded-lg object-cover">
                        </div>
                        <div class="flex-grow mt-4 md:mt-0">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold">{{ $userDriverDetail->user->name }} {{ $userDriverDetail->last_name }}</h2>
                                    <p class="text-gray-600">{{ $userDriverDetail->phone }}</p>
                                    <p class="text-gray-600">{{ $userDriverDetail->user->email }}</p>
                                </div>
                                <div class="mt-4 md:mt-0">
                                    @if($userDriverDetail->status == \App\Models\UserDriverDetail::STATUS_ACTIVE)
                                        <span class="px-3 py-1 rounded-full bg-success/20 text-success">Active</span>
                                    @elseif($userDriverDetail->status == \App\Models\UserDriverDetail::STATUS_PENDING)
                                        <span class="px-3 py-1 rounded-full bg-warning/20 text-warning">Pending</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-danger/20 text-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm text-gray-600">Completion Status</div>
                                <div class="flex items-center mt-1">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                        <div class="bg-primary h-2.5 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                                    </div>
                                    <span>{{ $completionPercentage }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Registration Steps Status --}}
        <div class="box mt-5">
            <div class="box-header">
                <h3 class="font-medium">Registration Steps Status</h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach([
                        [\App\Services\Admin\DriverStepService::STEP_GENERAL, 'General Information'],
                        [\App\Services\Admin\DriverStepService::STEP_LICENSES, 'Licenses & Experience'],
                        [\App\Services\Admin\DriverStepService::STEP_MEDICAL, 'Medical Information'],
                        [\App\Services\Admin\DriverStepService::STEP_TRAINING, 'Training History'],
                        [\App\Services\Admin\DriverStepService::STEP_TRAFFIC, 'Traffic Record'],
                        [\App\Services\Admin\DriverStepService::STEP_ACCIDENT, 'Accident History'],
                    ] as [$step, $title])
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium">{{ $title }}</h4>
                                
                                @if($stepsStatus[$step] == \App\Services\Admin\DriverStepService::STATUS_COMPLETED)
                                    <span class="px-2 py-1 rounded-full bg-success/20 text-success text-xs">Completed</span>
                                @elseif($stepsStatus[$step] == \App\Services\Admin\DriverStepService::STATUS_PENDING)
                                    <span class="px-2 py-1 rounded-full bg-warning/20 text-warning text-xs">Pending</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-danger/20 text-danger text-xs">Missing</span>
                                @endif
                            </div>
                            
                            <a href="{{ route('admin.carrier.user_drivers.edit', [
                                'carrier' => $carrier, 
                                'userDriverDetail' => $userDriverDetail, 
                                'active_tab' => match($step) {
                                    1 => 'general',
                                    2 => 'licenses',
                                    3 => 'medical',
                                    4 => 'training',
                                    5 => 'traffic',
                                    6 => 'accident',
                                    default => 'general'
                                }
                            ]) }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Driver Details Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
            {{-- Details Column 1 --}}
            <div>
                {{-- Personal Information --}}
                <div class="box">
                    <div class="box-header">
                        <h3 class="font-medium">Personal Information</h3>
                    </div>
                    <div class="box-body p-5 divide-y">
                        <div class="flex justify-between py-3">
                            <span class="font-medium">Full Name</span>
                            <span>{{ $userDriverDetail->user->name }} {{ $userDriverDetail->middle_name }} {{ $userDriverDetail->last_name }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="font-medium">Email</span>
                            <span>{{ $userDriverDetail->user->email }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="font-medium">Phone</span>
                            <span>{{ $userDriverDetail->phone }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="font-medium">Date of Birth</span>
                            <span>{{ $userDriverDetail->date_of_birth ? $userDriverDetail->date_of_birth->format('M d, Y') : 'Not provided' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Licenses Section --}}
                <div class="box mt-5">
                    <div class="box-header">
                        <h3 class="font-medium">Driver Licenses</h3>
                    </div>
                    <div class="box-body p-5">
                        @if($userDriverDetail->licenses && $userDriverDetail->licenses->count() > 0)
                            @foreach($userDriverDetail->licenses as $license)
                                <div class="mb-4 pb-4 {{ !$loop->last ? 'border-b' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium">{{ $license->state_of_issue }} License</h4>
                                        
                                        @if($license->is_primary)
                                            <span class="px-2 py-1 rounded-full bg-primary/20 text-primary text-xs">Primary</span>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <span class="text-gray-600 text-sm">License Number</span>
                                            <p>{{ $license->license_number }}</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">License Class</span>
                                            <p>{{ $license->license_class }}</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">Expiration Date</span>
                                            <p>{{ $license->expiration_date ? $license->expiration_date->format('M d, Y') : 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">CDL Status</span>
                                            <p>{{ $license->is_cdl ? 'CDL License' : 'Non-CDL License' }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($license->is_cdl && $license->endorsements && $license->endorsements->count() > 0)
                                        <div class="mt-3">
                                            <span class="text-gray-600 text-sm">Endorsements</span>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($license->endorsements as $endorsement)
                                                    <span class="px-2 py-1 rounded-full bg-gray-200 text-gray-700 text-xs">
                                                        {{ $endorsement->code }}: {{ $endorsement->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- License Images --}}
                                    <div class="grid grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <span class="text-gray-600 text-sm">License Front</span>
                                            <div class="mt-1">
                                                @if($license->getFirstMediaUrl('license_front'))
                                                    <img src="{{ $license->getFirstMediaUrl('license_front') }}" 
                                                        class="w-full max-w-xs rounded-md border">
                                                @else
                                                    <p class="text-gray-500 italic">No image provided</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">License Back</span>
                                            <div class="mt-1">
                                                @if($license->getFirstMediaUrl('license_back'))
                                                    <img src="{{ $license->getFirstMediaUrl('license_back') }}" 
                                                        class="w-full max-w-xs rounded-md border">
                                                @else
                                                    <p class="text-gray-500 italic">No image provided</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-500 italic">No licenses have been added yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Details Column 2 --}}
            <div>
                {{-- Medical Information --}}
                <div class="box">
                    <div class="box-header">
                        <h3 class="font-medium">Medical Information</h3>
                    </div>
                    <div class="box-body p-5">
                        @if($userDriverDetail->medicalQualification)
                            <div class="divide-y">
                                <div class="flex justify-between py-3">
                                    <span class="font-medium">Medical Examiner</span>
                                    <span>{{ $userDriverDetail->medicalQualification->medical_examiner_name }}</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="font-medium">Registry Number</span>
                                    <span>{{ $userDriverDetail->medicalQualification->medical_examiner_registry_number }}</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="font-medium">Medical Card Expiration</span>
                                    <span>{{ $userDriverDetail->medicalQualification->medical_card_expiration_date ? 
                                        $userDriverDetail->medicalQualification->medical_card_expiration_date->format('M d, Y') : 'Not provided' }}</span>
                                </div>
                            </div>
                            
                            @if($userDriverDetail->medicalQualification->getFirstMediaUrl('medical_card'))
                                <div class="mt-4">
                                    <h4 class="font-medium mb-2">Medical Card</h4>
                                    <img src="{{ $userDriverDetail->medicalQualification->getFirstMediaUrl('medical_card') }}" 
                                        class="w-full max-w-xs rounded-md border">
                                </div>
                            @endif
                        @else
                            <p class="text-gray-500 italic">No medical information has been added yet.</p>
                        @endif
                    </div>
                </div>

                {{-- Driving Experience --}}
                <div class="box mt-5">
                    <div class="box-header">
                        <h3 class="font-medium">Driving Experience</h3>
                    </div>
                    <div class="box-body p-5">
                        @if($userDriverDetail->experiences && $userDriverDetail->experiences->count() > 0)
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($userDriverDetail->experiences as $experience)
                                    <div class="p-4 border rounded-md {{ $experience->requires_cdl ? 'bg-blue-50' : 'bg-gray-50' }}">
                                        <div class="flex justify-between">
                                            <h4 class="font-medium">{{ $experience->equipment_type }}</h4>
                                            @if($experience->requires_cdl)
                                                <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs">CDL Required</span>
                                            @endif
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 mt-2">
                                            <div>
                                                <span class="text-gray-600 text-sm">Years of Experience</span>
                                                <p>{{ $experience->years_experience }} years</p>
                                            </div>
                                            <div>
                                                <span class="text-gray-600 text-sm">Approximate Miles</span>
                                                <p>{{ number_format($experience->miles_driven) }} miles</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">No driving experience has been added yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Additional sections can be added for training, traffic violations, accidents, etc. --}}
    </div>
</div>
@endsection