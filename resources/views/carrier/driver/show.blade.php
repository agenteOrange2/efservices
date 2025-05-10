@extends('../themes/' . $activeTheme)
@section('title', 'Driver Details')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('carrier.dashboard')],
        ['label' => 'Drivers', 'url' => route('carrier.drivers.index')],
        ['label' => 'Driver Details', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="py-5">
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('carrier.drivers.index') }}" class="btn btn-outline-secondary mr-4">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back
                </a>
                <h2 class="text-2xl font-medium">Driver Details</h2>
            </div>
            <div class="mt-2 text-slate-500">
                Detailed information for {{ $driver->user->name }} {{ $driver->last_name }}.
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Personal Information -->
            <div class="col-span-12 lg:col-span-4">
                <div class="box p-5">
                    <div class="flex flex-col items-center pb-5">
                        <div class="w-24 h-24 overflow-hidden rounded-full mb-3">
                            <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}"
                                alt="Profile" class="w-full h-full object-cover">
                        </div>
                        <h3 class="text-lg font-medium">{{ $driver->user->name }} {{ $driver->middle_name }}
                            {{ $driver->last_name }}</h3>
                        <div class="text-slate-500">{{ $driver->getStatusNameAttribute() }}</div>
                    </div>

                    <div class="mt-4">
                        <h4 class="text-base font-medium border-b pb-2 mb-3">Contact Information</h4>
                        <div class="mb-2">
                            <span class="font-medium mr-2">Email:</span>
                            {{ $driver->user->email }}
                        </div>
                        <div class="mb-2">
                            <span class="font-medium mr-2">Phone:</span>
                            {{ $driver->phone }}
                        </div>
                        <div class="mb-2">
                            <span class="font-medium mr-2">Date of Birth:</span>
                            {{ $driver->date_of_birth ? $driver->date_of_birth->format('d/m/Y') : 'N/A' }}
                        </div>
                    </div>

                    <div class="flex mt-5">
                        <a href="{{ route('carrier.drivers.edit', $driver->id) }}" class="btn btn-primary w-full">
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Edit Driver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Application Information -->
            <div class="col-span-12 lg:col-span-8">
                <div class="box p-5 mb-5">
                    <h3 class="text-lg font-medium border-b pb-2 mb-3">Application Status</h3>

                    @php
                        $completionPercentage = app(
                            \App\Services\Admin\DriverStepService::class,
                        )->calculateCompletionPercentage($driver);
                    @endphp

                    <div class="mb-4">
                        <div class="flex justify-between mb-1">
                            <span>Application Progress</span>
                            <span>{{ $completionPercentage }}%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded">
                            <div class="h-full rounded {{ $completionPercentage < 50 ? 'bg-danger' : ($completionPercentage < 100 ? 'bg-warning' : 'bg-success') }}"
                                style="width: {{ $completionPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- Steps Status -->
                    @php
                        $stepStatus = app(\App\Services\Admin\DriverStepService::class)->getStepsStatus($driver);
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach ($stepStatus as $step => $status)
                            @php
                                $stepName = '';
                                $icon = '';
                                $color = '';

                                switch ($step) {
                                    case 1:
                                        $stepName = 'General Information';
                                        $icon = 'user';
                                        break;
                                    case 2:
                                        $stepName = 'Licenses';
                                        $icon = 'credit-card';
                                        break;
                                    case 3:
                                        $stepName = 'Medical Information';
                                        $icon = 'activity';
                                        break;
                                    case 4:
                                        $stepName = 'Training';
                                        $icon = 'book-open';
                                        break;
                                    case 5:
                                        $stepName = 'Violations';
                                        $icon = 'alert-triangle';
                                        break;
                                    case 6:
                                        $stepName = 'Accidents';
                                        $icon = 'alert-circle';
                                        break;

                                    case 7:
                                        $stepName = 'FMCSR';
                                        $icon = 'alert-circle';
                                        break;

                                    case 8:
                                        $stepName = 'Employment History';
                                        $icon = 'alert-circle';
                                        break;

                                    case 9:
                                        $stepName = 'Company Policies';
                                        $icon = 'alert-circle';
                                        break;

                                    case 10:
                                        $stepName = 'Criminal History ';
                                        $icon = 'alert-circle';
                                        break;

                                    case 11:
                                        $stepName = 'Application Certification';
                                        $icon = 'alert-circle';
                                        break;
                                }

                                switch ($status) {
                                    case 'completed':
                                        $color = 'text-success';
                                        break;
                                    case 'pending':
                                        $color = 'text-warning';
                                        break;
                                    case 'missing':
                                        $color = 'text-danger';
                                        break;
                                }
                            @endphp

                            <div class="flex items-center p-3 border rounded">
                                <div class="mr-3 {{ $color }}">
                                    <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $stepName }}</div>
                                    <div class="{{ $color }} text-xs">
                                        @if ($status == 'completed')
                                            Completed
                                        @elseif($status == 'pending')
                                            Pending
                                        @else
                                            Missing
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Documents -->
                <div class="box p-5">
                    <h3 class="text-lg font-medium border-b pb-2 mb-3">Documents</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- License -->
                        @if ($driver->primaryLicense)
                            <div class="border p-3 rounded">
                                <div class="flex items-center mb-2">
                                    <i data-lucide="credit-card" class="w-5 h-5 mr-2 text-primary"></i>
                                    <span class="font-medium">Driver's License</span>
                                </div>
                                <div class="text-sm mb-2">
                                    <div><span class="font-medium">Number:</span>
                                        {{ $driver->primaryLicense->license_number }}</div>
                                    <div><span class="font-medium">State:</span>
                                        {{ $driver->primaryLicense->state_of_issue }}</div>
                                    <div><span class="font-medium">Class:</span>
                                        {{ $driver->primaryLicense->license_class }}</div>
                                    <div><span class="font-medium">Expiration:</span>
                                        {{ $driver->primaryLicense->expiration_date->format('d/m/Y') }}</div>
                                </div>
                                <div class="flex justify-between">
                                    @if ($driver->primaryLicense->getFirstMediaUrl('license_front'))
                                        <a href="{{ $driver->primaryLicense->getFirstMediaUrl('license_front') }}"
                                            target="_blank" class="text-primary text-sm">View Front</a>
                                    @endif
                                    @if ($driver->primaryLicense->getFirstMediaUrl('license_back'))
                                        <a href="{{ $driver->primaryLicense->getFirstMediaUrl('license_back') }}"
                                            target="_blank" class="text-primary text-sm">View Back</a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="border p-3 rounded bg-gray-50">
                                <div class="flex items-center mb-2">
                                    <i data-lucide="credit-card" class="w-5 h-5 mr-2 text-gray-400"></i>
                                    <span class="font-medium text-gray-500">Driver's License</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    No license information available.
                                </div>
                            </div>
                        @endif

                        <!-- Medical Card -->
                        @if ($driver->medicalQualification)
                            <div class="border p-3 rounded">
                                <div class="flex items-center mb-2">
                                    <i data-lucide="activity" class="w-5 h-5 mr-2 text-primary"></i>
                                    <span class="font-medium">Medical Card</span>
                                </div>
                                <div class="text-sm mb-2">
                                    <div><span class="font-medium">Examiner:</span>
                                        {{ $driver->medicalQualification->medical_examiner_name }}</div>
                                    <div><span class="font-medium">Registry:</span>
                                        {{ $driver->medicalQualification->medical_examiner_registry_number }}</div>
                                    <div><span class="font-medium">Expiration:</span>
                                        {{ $driver->medicalQualification->medical_card_expiration_date->format('d/m/Y') }}
                                    </div>
                                </div>
                                @if ($driver->medicalQualification->getFirstMediaUrl('medical_card'))
                                    <div>
                                        <a href="{{ $driver->medicalQualification->getFirstMediaUrl('medical_card') }}"
                                            target="_blank" class="text-primary text-sm">View Medical Card</a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="border p-3 rounded bg-gray-50">
                                <div class="flex items-center mb-2">
                                    <i data-lucide="activity" class="w-5 h-5 mr-2 text-gray-400"></i>
                                    <span class="font-medium text-gray-500">Medical Card</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    No medical information available.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
