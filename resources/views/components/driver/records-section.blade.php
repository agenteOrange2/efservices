@props(['trafficConvictions' => [], 'accidents' => []])

<div class="space-y-8">
    <!-- Traffic Convictions Section -->
    <x-driver.info-card title="Traffic Convictions" icon="alert-triangle" class="mb-8">
        @if(count($trafficConvictions) > 0)
            <x-driver.data-table 
                :headers="['Date', 'Location', 'Violation', 'Penalty', 'Status']"
                :data="$trafficConvictions"
                emptyMessage="No traffic convictions on record">
                @foreach($trafficConvictions as $conviction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $conviction->date ? \Carbon\Carbon::parse($conviction->date)->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $conviction->location ?? 'N/A' }}</div>
                                @if($conviction->state)
                                    <div class="text-gray-500">{{ $conviction->state }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $conviction->violation ?? 'N/A' }}</div>
                                @if($conviction->description)
                                    <div class="text-gray-500 text-xs mt-1">{{ $conviction->description }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($conviction->penalty)
                                <div class="flex items-center">
                                    <x-base.lucide icon="dollar-sign" class="w-3 h-3 mr-1 text-gray-400" />
                                    {{ $conviction->penalty }}
                                </div>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($conviction->status ?? 'unknown') {
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'active' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($conviction->status ?? 'Unknown') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-driver.data-table>
        @else
            <div class="text-center py-8">
                <div class="mx-auto w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <x-base.lucide icon="shield-check" class="w-6 h-6 text-green-600" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">Clean Driving Record</h3>
                <p class="text-sm text-gray-500">No traffic convictions on record.</p>
            </div>
        @endif
    </x-driver.info-card>

    <!-- Accidents Section -->
    <x-driver.info-card title="Accident History" icon="car-crash">
        @if(count($accidents) > 0)
            <x-driver.data-table 
                :headers="['Date', 'Location', 'Type', 'Severity', 'Injuries', 'Status']"
                :data="$accidents"
                emptyMessage="No accidents on record">
                @foreach($accidents as $accident)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $accident->date ? \Carbon\Carbon::parse($accident->date)->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $accident->location ?? 'N/A' }}</div>
                                @if($accident->state)
                                    <div class="text-gray-500">{{ $accident->state }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                @php
                                    $typeIcon = match($accident->type ?? 'other') {
                                        'collision' => 'car',
                                        'rollover' => 'rotate-cw',
                                        'rear-end' => 'move-right',
                                        default => 'alert-circle'
                                    };
                                @endphp
                                <x-base.lucide icon="{{ $typeIcon }}" class="w-4 h-4 mr-2 text-gray-400" />
                                {{ ucfirst($accident->type ?? 'Other') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $severityColor = match($accident->severity ?? 'unknown') {
                                    'minor' => 'bg-green-100 text-green-800',
                                    'moderate' => 'bg-yellow-100 text-yellow-800',
                                    'severe' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $severityColor }}">
                                {{ ucfirst($accident->severity ?? 'Unknown') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($accident->injuries)
                                <div class="flex items-center text-red-600">
                                    <x-base.lucide icon="alert-triangle" class="w-4 h-4 mr-1" />
                                    Yes
                                </div>
                            @else
                                <div class="flex items-center text-green-600">
                                    <x-base.lucide icon="check-circle" class="w-4 h-4 mr-1" />
                                    No
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($accident->status ?? 'unknown') {
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'investigating' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($accident->status ?? 'Unknown') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-driver.data-table>
        @else
            <div class="text-center py-8">
                <div class="mx-auto w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <x-base.lucide icon="shield-check" class="w-6 h-6 text-green-600" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">No Accidents</h3>
                <p class="text-sm text-gray-500">No accidents reported on record.</p>
            </div>
        @endif
    </x-driver.info-card>
</div>