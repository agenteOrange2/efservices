{{-- Licenses Tab --}}
<div class="space-y-6">
    {{-- All Licenses --}}
    @if($driver->licenses && $driver->licenses->count() > 0)
    @foreach($driver->licenses as $license)
    <x-driver.info-card title="License Information" icon="credit-card">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">License Number</label>
                <p class="text-sm text-gray-900 font-mono">{{ $license->license_number ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">License Class</label>
                <p class="text-sm text-gray-900">{{ $license->license_class ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">State Issued</label>
                <p class="text-sm text-gray-900">{{ $license->state_of_issue ?? $license->state ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Type</label>
                <p class="text-sm text-gray-900">{{ $license->is_cdl ? 'CDL' : 'Standard' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Expiration Date</label>
                <p class="text-sm text-gray-900">{{ $license->expiration_date ? $license->expiration_date->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <p>
                    <x-ui.status-badge :status="$license->status ?? 'inactive'" />
                    </p>
                </div>
            </div>
        </div>
        {{-- License Endorsements --}}
        @if($license->endorsements && $license->endorsements->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-4">Endorsements</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($license->endorsements as $endorsement)
                <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-primary text-white">
                    {{ $endorsement->name ?? $endorsement->type ?? 'N/A' }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- License Documents --}}
        @if($license->getFirstMediaUrl('license_front') || $license->getFirstMediaUrl('license_back'))
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-4">License Documents</h4>
            <div class="flex flex-wrap gap-4">
                @if($license->getFirstMediaUrl('license_front'))
                <x-ui.action-button
                    href="{{ $license->getFirstMediaUrl('license_front') }}"
                    icon="image"
                    variant="secondary"
                    size="sm"
                    target="_blank">
                    View Front
                </x-ui.action-button>
                @endif
                @if($license->getFirstMediaUrl('license_back'))
                <x-ui.action-button
                    href="{{ $license->getFirstMediaUrl('license_back') }}"
                    icon="image"
                    variant="secondary"
                    size="sm"
                    target="_blank">
                    View Back
                </x-ui.action-button>
                @endif
            </div>
        </div>
        @endif
    </x-driver.info-card>
    @endforeach
    @else
    <x-driver.info-card title="License Information" icon="credit-card">
        <div class="text-center py-8">
            <x-base.lucide icon="credit-card" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No license information provided</p>
        </div>
    </x-driver.info-card>
    @endif



    {{-- Driving Experience --}}
    <x-driver.info-card title="Driving Experience" icon="clock">
        @if ($driver->experiences && $driver->experiences->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($driver->experiences as $experience)
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Equipment Type</label>
                <p class="text-lg font-semibold text-gray-900">{{ $experience->equipment_type }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Years Experience</label>
                <p class="text-lg font-semibold text-gray-900">{{ $experience->years_experience }} years</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Miles Driven</label>
                <p class="text-lg font-semibold text-gray-900">{{ number_format($experience->miles_driven) }} miles</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">	Requires CDL</label>
                <div class="flex items-center space-x-2">
                    <x-ui.status-badge :status="$experience->requires_cdl ? 'active' : 'inactive'" />
                    <span class="text-sm text-gray-600">{{ $experience->requires_cdl ? 'Yes' : 'No' }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-500">No driving experience information provided</p>
        @endif
    </x-driver.info-card>

    {{-- Additional Licenses --}}
    @if($driver->additionalLicenses && $driver->additionalLicenses->count() > 0)
    <x-driver.info-card title="Additional Licenses" icon="file-text">
        <div class="space-y-4">
            @foreach($driver->additionalLicenses as $license)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">License Number</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $license->license_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Type</label>
                        <p class="text-sm text-gray-900">{{ $license->license_type ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">State</label>
                        <p class="text-sm text-gray-900">{{ $license->state ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Status</label>
                        <x-ui.status-badge :status="$license->status ?? 'inactive'" />
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </x-driver.info-card>
    @endif
</div>