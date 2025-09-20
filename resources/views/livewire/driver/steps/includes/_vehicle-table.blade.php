{{-- Componente reutilizable para tabla de vehículos existentes --}}
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h4 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Existing Vehicles' }}</h4>
        </div>
    </div>

    @if(count($vehicles) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($vehicles as $vehicle)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            VIN: {{ $vehicle->vin }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div><strong>Type:</strong> {{ ucfirst($vehicle->type) }}</div>
                                    @if($vehicle->company_unit_number)
                                        <div><strong>Unit #:</strong> {{ $vehicle->company_unit_number }}</div>
                                    @endif
                                    @if($vehicle->gvwr && is_numeric($vehicle->gvwr))
                                        <div><strong>GVWR:</strong> {{ number_format((float)$vehicle->gvwr) }} lbs</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($vehicle->irp_plate)
                                        <div><strong>IRP:</strong> {{ $vehicle->irp_plate }}</div>
                                    @endif
                                    @if($vehicle->registration_state && $vehicle->registration_number)
                                        <div><strong>Reg:</strong> {{ $vehicle->registration_state }} - {{ $vehicle->registration_number }}</div>
                                    @endif
                                    @if($vehicle->registration_expiry_date)
                                        <div class="text-xs {{ \Carbon\Carbon::parse($vehicle->registration_expiry_date)->isPast() ? 'text-red-600' : 'text-gray-500' }}">
                                            <strong>Expires:</strong> {{ \Carbon\Carbon::parse($vehicle->registration_expiry_date)->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-2">
                                <div class="flex flex-col space-y-2">
                                    <button type="button" 
                                            wire:click="{{ isset($selectMethod) ? $selectMethod : 'selectVehicle' }}({{ $vehicle->id }})" 
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        Select
                                    </button>
                                    <button type="button" 
                                            wire:click="viewVehicleDetails({{ $vehicle->id }})" 
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No vehicles found</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $emptyMessage ?? 'You haven\'t registered any vehicles yet.' }}</p>
        </div>
    @endif

    @if(isset($showAddButton) && $showAddButton)
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <button type="button" 
                    wire:click="{{ $addAction ?? 'addNewVehicle' }}" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ $addButtonText ?? 'Register New Vehicle' }}
            </button>
        </div>
    @endif
</div>