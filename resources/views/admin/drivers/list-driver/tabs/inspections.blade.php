{{-- Inspections Tab --}}
<div class="space-y-6">
    {{-- 1. INSPECTIONS SECTION --}}
    <x-driver.info-card title="Driver Inspections" icon="search">
        {{-- Inspection Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Total Inspections</label>
                <p class="text-2xl font-bold text-gray-900">{{ $driver->inspections->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">This Year</label>
                <p class="text-2xl font-bold text-blue-600">{{ $driver->inspections->where('inspection_date', '>=', now()->startOfYear())->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Last Inspection</label>
                <p class="text-sm font-medium text-gray-900">
                    @php $lastInspection = $driver->inspections->sortByDesc('inspection_date')->first(); @endphp
                    {{ $lastInspection ? $lastInspection->inspection_date->format('M d, Y') : 'N/A' }}
                </p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Defects Found</label>
                <p class="text-2xl font-bold text-red-600">{{ $driver->inspections->where('defects_found', '!=', null)->where('defects_found', '!=', '')->count() ?? 0 }}</p>
            </div>
        </div>

        {{-- Recent Inspections Table --}}
        <!-- Debug: Inspections count: {{ $driver->inspections->count() ?? 0 }} -->
        @if(($driver->inspections ?? collect())->count() > 0)
        <x-driver.data-table 
            :headers="['Date', 'Type', 'Level', 'Inspector', 'Location', 'Status', 'Defects', 'Actions']"
            :data="$driver->inspections->toArray()"
            empty-message="No inspection records available">
            @foreach($driver->inspections->sortByDesc('inspection_date')->take(10) as $inspection)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $inspection->inspection_date ? $inspection->inspection_date->format('M d, Y') : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $inspection->inspection_type ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $inspection->inspection_level === 'I' ? 'bg-red-100 text-red-800' : 
                           ($inspection->inspection_level === 'II' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                        Level {{ $inspection->inspection_level ?? 'N/A' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $inspection->inspector_name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $inspection->location ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <x-ui.status-badge :status="$inspection->status ?? 'pending'" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    @if($inspection->defects_found && trim($inspection->defects_found) !== '')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Has Defects
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            No Defects
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    @if($inspection->getFirstMediaUrl('inspection_documents'))
                    <x-ui.action-button 
                        href="{{ $inspection->getFirstMediaUrl('inspection_documents') }}" 
                        icon="eye" 
                        variant="secondary" 
                        size="xs"
                        target="_blank">
                        View
                    </x-ui.action-button>
                    @endif
                </td>
            </tr>
            @endforeach
        </x-driver.data-table>
        @else
        <div class="text-center py-8">
            <x-base.lucide icon="search-x" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500">No inspection records found for this driver</p>
        </div>
        @endif
    </x-driver.info-card>

    {{-- 2. ACCIDENTS SECTION --}}
    <x-driver.info-card title="Driver Accidents" icon="alert-triangle">
        {{-- Accident Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Total Accidents</label>
                <p class="text-2xl font-bold text-gray-900">{{ $driver->accidents->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">With Fatalities</label>
                <p class="text-2xl font-bold text-red-600">{{ $driver->accidents->where('had_fatalities', true)->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">With Injuries</label>
                <p class="text-2xl font-bold text-orange-600">{{ $driver->accidents->where('had_injuries', true)->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Last Accident</label>
                <p class="text-sm font-medium text-gray-900">
                    @php $lastAccident = $driver->accidents->sortByDesc('accident_date')->first(); @endphp
                    {{ $lastAccident ? $lastAccident->accident_date->format('M d, Y') : 'N/A' }}
                </p>
            </div>
        </div>

        {{-- Accidents Table --}}
        <!-- Debug: Accidents count: {{ $driver->accidents->count() ?? 0 }} -->
        @if(($driver->accidents ?? collect())->count() > 0)
        <x-driver.data-table 
            :headers="['Date', 'Nature of Accident', 'Fatalities', 'Injuries', 'Comments', 'Actions']"
            :data="$driver->accidents->toArray()"
            empty-message="No accident records available">
            @foreach($driver->accidents->sortByDesc('accident_date') as $accident)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $accident->accident_date ? $accident->accident_date->format('M d, Y') : 'N/A' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    {{ $accident->nature_of_accident ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    @if($accident->had_fatalities)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $accident->number_of_fatalities ?? 0 }} fatalities
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            None
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    @if($accident->had_injuries)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ $accident->number_of_injuries ?? 0 }} injuries
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            None
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                    {{ $accident->comments ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    @if($accident->getFirstMediaUrl('accident-images'))
                    <x-ui.action-button 
                        href="{{ $accident->getFirstMediaUrl('accident-images') }}" 
                        icon="eye" 
                        variant="secondary" 
                        size="xs"
                        target="_blank">
                        View
                    </x-ui.action-button>
                    @endif
                </td>
            </tr>
            @endforeach
        </x-driver.data-table>
        @else
        <div class="text-center py-8">
            <x-base.lucide icon="shield-check" class="w-12 h-12 text-green-400 mx-auto mb-3" />
            <p class="text-gray-500">No accident records found for this driver</p>
            <p class="text-sm text-green-600 mt-1">Clean driving record</p>
        </div>
        @endif
    </x-driver.info-card>

    {{-- 3. TRAFFIC CONVICTIONS SECTION --}}
    <x-driver.info-card title="Traffic Convictions" icon="gavel">
        {{-- Traffic Convictions Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Total Convictions</label>
                <p class="text-2xl font-bold text-gray-900">{{ $driver->trafficConvictions->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">This Year</label>
                <p class="text-2xl font-bold text-red-600">{{ $driver->trafficConvictions->where('conviction_date', '>=', now()->startOfYear())->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Last 3 Years</label>
                <p class="text-2xl font-bold text-orange-600">{{ $driver->trafficConvictions->where('conviction_date', '>=', now()->subYears(3))->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Last Conviction</label>
                <p class="text-sm font-medium text-gray-900">
                    @php $lastConviction = $driver->trafficConvictions->sortByDesc('conviction_date')->first(); @endphp
                    {{ $lastConviction ? $lastConviction->conviction_date->format('M d, Y') : 'N/A' }}
                </p>
            </div>
        </div>

        {{-- Traffic Convictions Table --}}
        <!-- Debug: Traffic Convictions count: {{ $driver->trafficConvictions->count() ?? 0 }} -->
        @if(($driver->trafficConvictions ?? collect())->count() > 0)
        <x-driver.data-table 
            :headers="['Date', 'Location', 'Charge', 'Penalty', 'Actions']"
            :data="$driver->trafficConvictions->toArray()"
            empty-message="No traffic conviction records available">
            @foreach($driver->trafficConvictions->sortByDesc('conviction_date') as $conviction)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $conviction->conviction_date ? $conviction->conviction_date->format('M d, Y') : 'N/A' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    {{ $conviction->location ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    {{ $conviction->charge ?? 'N/A' }}
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                    {{ $conviction->penalty ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    @if($conviction->getFirstMediaUrl('traffic_images'))
                    <x-ui.action-button 
                        href="{{ $conviction->getFirstMediaUrl('traffic_images') }}" 
                        icon="eye" 
                        variant="secondary" 
                        size="xs"
                        target="_blank">
                        View
                    </x-ui.action-button>
                    @endif
                </td>
            </tr>
            @endforeach
        </x-driver.data-table>
        @else
        <div class="text-center py-8">
            <x-base.lucide icon="shield-check" class="w-12 h-12 text-green-400 mx-auto mb-3" />
            <p class="text-gray-500">No traffic conviction records found for this driver</p>
            <p class="text-sm text-green-600 mt-1">Clean driving record</p>
        </div>
        @endif
    </x-driver.info-card>

    {{-- 4. VEHICLES ASSOCIATED SECTION --}}
    <x-driver.info-card title="Associated Vehicles" icon="truck">
        @php
            // Get the current active assignment or the most recent assignment
            $currentAssignment = $driver->vehicleAssignments->where('status', 'active')->first() 
                ?? $driver->vehicleAssignments->sortByDesc('start_date')->first();
        @endphp
        
        {{-- Vehicle Assignment Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Total Assignments</label>
                <p class="text-2xl font-bold text-gray-900">{{ $driver->vehicleAssignments->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Active Assignments</label>
                <p class="text-2xl font-bold text-green-600">{{ $driver->vehicleAssignments->where('status', 'active')->count() ?? 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Assignment Type</label>
                <p class="text-sm font-medium text-gray-900">
                    {{ $currentAssignment ? ucfirst(str_replace('_', ' ', $currentAssignment->driver_type)) : 'N/A' }}
                </p>
            </div>
        </div>

        {{-- Vehicle Assignments Table --}}
        <!-- Debug: Vehicle Assignments count: {{ $driver->vehicleAssignments->count() ?? 0 }} -->
        @if(($driver->vehicleAssignments ?? collect())->count() > 0)
        <x-driver.data-table 
            :headers="['Vehicle', 'Type', 'Start Date', 'End Date', 'Status', 'Duration', 'Notes']"
            :data="$driver->vehicleAssignments->toArray()"
            empty-message="No vehicle assignment records available">
            @foreach($driver->vehicleAssignments->sortByDesc('start_date') as $assignment)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">
                    @if($assignment->vehicle)
                        <div>
                            <div class="font-medium">{{ $assignment->vehicle->make ?? 'N/A' }} {{ $assignment->vehicle->model ?? '' }}</div>
                            <div class="text-gray-500 text-xs">{{ $assignment->vehicle->year ?? 'N/A' }}</div>
                        </div>
                    @else
                        N/A
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst(str_replace('_', ' ', $assignment->driver_type ?? 'N/A')) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $assignment->start_date ? $assignment->start_date->format('M d, Y') : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $assignment->end_date ? $assignment->end_date->format('M d, Y') : 'Ongoing' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <x-ui.status-badge :status="$assignment->status ?? 'pending'" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    @if($assignment->end_date)
                        {{ $assignment->start_date->diffInDays($assignment->end_date) + 1 }} days
                    @else
                        {{ $assignment->start_date->diffInDays(now()) + 1 }} days
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                    {{ $assignment->notes ?? 'N/A' }}
                </td>
            </tr>
            @endforeach
        </x-driver.data-table>
        @else
        <div class="text-center py-8">
            <x-base.lucide icon="truck-off" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500">No vehicle assignments found for this driver</p>
        </div>
        @endif
    </x-driver.info-card>
</div>