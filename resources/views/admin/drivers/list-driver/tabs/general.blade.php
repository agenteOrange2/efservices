{{-- General Information Tab --}}
<div class="space-y-6">
    {{-- Personal Information --}}
    <x-driver.info-card title="Personal Information" icon="user">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Full Name</label>
                <p class="text-sm text-gray-900">{{ $driver->user->name ?? 'Unknown' }} {{ $driver->middle_name ? $driver->middle_name . ' ' : '' }}
                    {{ $driver->last_name }}
                </p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Email</label>
                <p class="text-sm text-gray-900">{{ $driver->user->email ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Phone</label>
                <p class="text-sm text-gray-900">{{ $driver->phone ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                <p class="text-sm text-gray-900">{{ $driver->date_of_birth ? $driver->date_of_birth->format('M d, Y') : 'N/A' }}</p>
            </div>
        </div>
    </x-driver.info-card>

    {{-- Address Information --}}
    <x-driver.info-card title="Address Information" icon="map-pin">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                @if ($driver->application && $driver->application->addresses->count() > 0)
                <div class="space-y-4">
                    @foreach ($driver->application->addresses as $address)
                    <div class="{{ !$loop->last ? 'pb-4 border-b' : '' }}">
                        <h4 class="font-bold text-lg text-primary mb-5">
                            {{ $address->primary ? 'Current Address' : 'Previous Address' }}
                        </h4>
                        <div class="space-y-3 py-3">
                            <label class="text-sm font-bold text-gray-600">Address Line 1</label>
                            <p class="text-sm text-gray-900">{{ $address->address_line1 }}</p>
                        </div>
                        @if ($address->address_line2)
                        <div class="space-y-3 py-3">
                            <label class="text-md font-bold text-gray-600">Address Line #2</label>
                            <p class="text-sm text-gray-900">{{ $address->address_line2 }}</p>
                        </div>
                        @endif
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <label class="text-md font-bold text-gray-600">City</label>
                                <p class="text-sm text-gray-900">{{ $address->city }}</p>
                            </div>
                            <div class="space-y-3">
                                <label class="text-md font-bold text-gray-600">State</label>
                                <p class="text-sm text-gray-900">{{ $address->state }}</p>
                            </div>
                            <div class="space-y-3">
                                <label class="text-md font-bold text-gray-600">ZIP Code</label>
                                <p class="text-sm text-gray-900">{{ $address->zip_code }}</p>
                            </div>
                            <div class="space-y-3">
                                <label class="text-sm font-bold text-gray-600">Date Range</label>
                                <p class="text-sm text-gray-900">
                                    {{ $address->from_date ? $address->from_date->format('M Y') : '' }}
                                    {{ $address->to_date ? ' - ' . $address->to_date->format('M Y') : ' - Present' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-slate-500">No address information provided</p>
                    @endif
                </div>

                @if($driver->previous_address)
                <div class="space-y-4">
                    <h4 class="font-medium text-primary">Previous Address</h4>
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Street Address</label>
                            <p class="text-sm text-gray-900">{{ $driver->previous_address ?? 'N/A' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-500">City</label>
                                <p class="text-sm text-gray-900">{{ $driver->previous_city ?? 'N/A' }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-500">State</label>
                                <p class="text-sm text-gray-900">{{ $driver->previous_state ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">ZIP Code</label>
                            <p class="text-sm text-gray-900">{{ $driver->previous_zip_code ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </x-driver.info-card>

    {{-- Carrier Information --}}
    <x-driver.info-card title="Carrier Information" icon="truck">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Carrier Name</label>
                <p class="text-sm text-gray-900">{{ $driver->carrier->name ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Address</label>
                <p class="text-sm text-gray-900">{{ $driver->carrier->address ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">DOT Number</label>
                <p class="text-sm text-gray-900">{{ $driver->carrier->dot_number ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">MC Number</label>
                <p class="text-sm text-gray-900">{{ $driver->carrier->mc_number ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Join Date</label>
                <p class="text-sm text-gray-900">{{ $driver->created_at ? $driver->created_at->format('M d, Y') : 'N/A' }}</p>
            </div>            
            <div class=space-y-1">
                <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Status</label>
                <div class="mt-2">
                    @if ($driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE)
                    <x-base.badge variant="success" class="gap-1.5">
                        <span class="w-1.5 h-1.5 bg-success rounded-full"></span>
                        Active
                    </x-base.badge>
                    @elseif ($driver->status == App\Models\UserDriverDetail::STATUS_INACTIVE)
                    <x-base.badge variant="danger" class="gap-1.5">
                        <span class="w-1.5 h-1.5 bg-danger rounded-full"></span>
                        Inactive
                    </x-base.badge>
                    @elseif ($driver->status == App\Models\UserDriverDetail::STATUS_PENDING)
                    <x-base.badge variant="warning" class="gap-1.5">
                        <span class="w-1.5 h-1.5 bg-warning rounded-full"></span>
                        Pending
                    </x-base.badge>
                    @else
                    <x-base.badge variant="secondary" class="gap-1.5">
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                        Unknown
                    </x-base.badge>
                    @endif
                </div>
            </div>
        </div>
    </x-driver.info-card>

    {{-- Emergency Contact --}}
    @if($driver->emergency_contact_name || $driver->emergency_contact_phone)
    <x-driver.info-card title="Emergency Contact" icon="phone">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Contact Name</label>
                <p class="text-sm text-gray-900">{{ $driver->emergency_contact_name ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Phone Number</label>
                <p class="text-sm text-gray-900">{{ $driver->emergency_contact_phone ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Relationship</label>
                <p class="text-sm text-gray-900">{{ $driver->emergency_contact_relationship ?? 'N/A' }}</p>
            </div>
        </div>
    </x-driver.info-card>
    @endif
</div>