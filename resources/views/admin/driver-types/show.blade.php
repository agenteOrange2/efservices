@extends('../themes/' . $activeTheme)

@section('subhead')
    <title>Driver Details - {{ $driver->user->name ?? 'N/A' }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-y-10 gap-x-6">
        <div class="col-span-12">
            <!-- Header -->
            <div class="flex flex-col md:h-10 gap-y-3 md:items-center md:flex-row">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Driver Details - {{ $driver->user->name ?? 'N/A' }}
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.driver-types.index') }}" variant="outline-secondary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                        Back to List
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="primary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                        Assign Vehicle
                    </x-base.button>
                </div>
            </div>

            <!-- Driver Information Card -->
            <div class="box box--stacked mt-5">
                <div class="box-header p-5">
                    <h3 class="box-title">Driver Information</h3>
                </div>
                <div class="box-body p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-slate-700 border-b pb-2">Personal Information</h4>
                            
                            <div class="flex items-center space-x-3">
                                <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center">
                                    <x-base.lucide class="w-8 h-8 text-slate-500" icon="user" />
                                </div>
                                <div>
                                    <div class="font-medium text-lg">{{ $driver->user->name ?? 'N/A' }}</div>
                                    <div class="text-slate-500">Driver ID: {{ $driver->id }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Email</label>
                                    <div class="text-slate-800">{{ $driver->user->email ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Phone</label>
                                    <div class="text-slate-800">{{ $driver->phone ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-600">License Number</label>
                                    <div class="text-slate-800">{{ $driver->license_number ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-600">License Expiration</label>
                                    <div class="text-slate-800">
                                        {{ $driver->license_expiration ? \Carbon\Carbon::parse($driver->license_expiration)->format('M d, Y') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Information -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-slate-700 border-b pb-2">Company Information</h4>
                            
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Carrier</label>
                                    <div class="text-slate-800">{{ $driver->carrier->name ?? 'N/A' }}</div>
                                </div>
                                
                                @if($driver->driverEmploymentCompanies->count() > 0)
                                    @foreach($driver->driverEmploymentCompanies as $employment)
                                        <div>
                                            <label class="text-sm font-medium text-slate-600">Company</label>
                                            <div class="text-slate-800">{{ $employment->company->company_name ?? 'N/A' }}</div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-600">Employment Date</label>
                                            <div class="text-slate-800">{{ $employment->created_at->format('M d, Y') }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">Company</label>
                                        <div class="text-slate-400">No company assigned</div>
                                    </div>
                                @endif

                                <div>
                                    <label class="text-sm font-medium text-slate-600">Registration Date</label>
                                    <div class="text-slate-800">{{ $driver->created_at->format('M d, Y H:i') }}</div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-slate-600">Application Status</label>
                                    <div>
                                        @if($driver->application_completed)
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle Assignments -->
            <div class="box box--stacked mt-5">
                <div class="box-header p-5">
                    <h3 class="box-title">Vehicle Assignment History</h3>
                </div>
                <div class="box-body p-5">
                    @if($driver->vehicleAssignments && $driver->vehicleAssignments->count() > 0)
                        <div class="overflow-x-auto">
                            <x-base.table>
                                <x-base.table.thead>
                                    <x-base.table.tr>
                                        <x-base.table.th>Vehicle</x-base.table.th>
                                        <x-base.table.th>Assignment Date</x-base.table.th>
                                        <x-base.table.th>Status</x-base.table.th>
                                        <x-base.table.th>Notes</x-base.table.th>
                                    </x-base.table.tr>
                                </x-base.table.thead>
                                <x-base.table.tbody>
                                    @foreach($driver->vehicleAssignments as $assignment)
                                        <x-base.table.tr>
                                            <x-base.table.td>
                                                <div class="font-medium">{{ $assignment->vehicle->vehicle_number ?? 'N/A' }}</div>
                                                <div class="text-slate-500 text-sm">{{ $assignment->vehicle->make }} {{ $assignment->vehicle->model }}</div>
                                            </x-base.table.td>
                                            <x-base.table.td>{{ \Carbon\Carbon::parse($assignment->assignment_date)->format('M d, Y') }}</x-base.table.td>
                                            <x-base.table.td>
                                                @if($assignment->status === 'active')
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                                @else
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($assignment->status) }}</span>
                                                @endif
                                            </x-base.table.td>
                                            <x-base.table.td>{{ $assignment->notes ?? 'No notes' }}</x-base.table.td>
                                        </x-base.table.tr>
                                    @endforeach
                                </x-base.table.tbody>
                            </x-base.table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto" icon="truck" />
                            <div class="text-xl font-medium text-slate-500 mt-3">No Vehicle Assignments</div>
                            <div class="text-slate-400 mt-2">This driver has not been assigned to any vehicles yet.</div>
                            <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="primary" class="mt-4">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                                Assign Vehicle
                            </x-base.button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="box box--stacked mt-5">
                <div class="box-header p-5">
                    <h3 class="box-title">Actions</h3>
                </div>
                <div class="box-body p-5">
                    <div class="flex flex-wrap gap-3">
                        <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="primary">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                            Assign Vehicle
                        </x-base.button>
                        <x-base.button as="a" href="{{ route('admin.driver-types.contact', $driver) }}" variant="outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="mail" />
                            Contact Driver
                        </x-base.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection