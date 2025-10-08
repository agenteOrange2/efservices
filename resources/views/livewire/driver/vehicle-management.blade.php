<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Vehicle Management Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage your vehicles and assignments</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Driver Types Tabs -->
    @if(count($selectedDriverTypes) > 1)
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    @foreach($selectedDriverTypes as $driverType)
                        <button 
                            wire:click="$set('currentDriverType', '{{ $driverType }}')"
                            class="py-2 px-1 border-b-2 font-medium text-sm {{ $currentDriverType === $driverType ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        >
                            {{ ucfirst(str_replace('_', ' ', $driverType)) }}
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif

    <!-- Vehicles Section -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">Vehicles</h2>
            <button 
                wire:click="openAddVehicleModal('{{ $currentDriverType }}')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
                Add Vehicle
            </button>
        </div>
        
        <div class="p-6">
            @if(isset($vehiclesByType[$currentDriverType]) && count($vehiclesByType[$currentDriverType]) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($vehiclesByType[$currentDriverType] as $vehicle)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-900">
                                        {{ $vehicle['year'] }} {{ $vehicle['make'] }} {{ $vehicle['model'] }}
                                    </h3>
                                    <p class="text-sm text-gray-600">VIN: {{ $vehicle['vin'] }}</p>
                                    @if($vehicle['company_unit_number'])
                                        <p class="text-sm text-gray-600">Unit #: {{ $vehicle['company_unit_number'] }}</p>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <button 
                                        wire:click="openEditVehicleModal({{ $vehicle['id'] }})"
                                        class="text-blue-600 hover:text-blue-800 text-sm"
                                    >
                                        Edit
                                    </button>
                                    <button 
                                        wire:click="deleteVehicle({{ $vehicle['id'] }})"
                                        onclick="return confirm('Are you sure you want to delete this vehicle?')"
                                        class="text-red-600 hover:text-red-800 text-sm"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-1 text-sm text-gray-600">
                                <p><span class="font-medium">Type:</span> {{ ucfirst($vehicle['type']) }}</p>
                                <p><span class="font-medium">Fuel:</span> {{ ucfirst($vehicle['fuel_type']) }}</p>
                                @if($vehicle['registration_state'] && $vehicle['registration_number'])
                                    <p><span class="font-medium">Registration:</span> {{ $vehicle['registration_state'] }} - {{ $vehicle['registration_number'] }}</p>
                                @endif
                                @if($vehicle['location'])
                                    <p><span class="font-medium">Location:</span> {{ $vehicle['location'] }}</p>
                                @endif
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <button 
                                    wire:click="openAssignmentModal({{ $vehicle['id'] }})"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium"
                                >
                                    Create Assignment
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 mb-4">No vehicles found for {{ ucfirst(str_replace('_', ' ', $currentDriverType)) }}</p>
                    <button 
                        wire:click="openAddVehicleModal('{{ $currentDriverType }}')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Add Your First Vehicle
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Assignments Section -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Vehicle Assignments</h2>
        </div>
        
        <div class="p-6">
            @if(count($assignments) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($assignments as $assignment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $assignment['vehicle']['year'] ?? '' }} {{ $assignment['vehicle']['make'] ?? '' }} {{ $assignment['vehicle']['model'] ?? '' }}
                                        </div>
                                        <div class="text-sm text-gray-500">VIN: {{ $assignment['vehicle']['vin'] ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($assignment['start_date'])->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $assignment['end_date'] ? \Carbon\Carbon::parse($assignment['end_date'])->format('M d, Y') : 'Active' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $assignment['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                               ($assignment['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($assignment['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($assignment['status'] === 'active')
                                            <button 
                                                wire:click="endAssignment({{ $assignment['id'] }})"
                                                onclick="return confirm('Are you sure you want to end this assignment?')"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                End Assignment
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500">No assignments found</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Vehicle Modal -->
    @if($showAddVehicleModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $editingVehicleId ? 'Edit Vehicle' : 'Add New Vehicle' }}
                        </h3>
                        <button wire:click="closeAddVehicleModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="saveVehicle" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Make *</label>
                                <input type="text" wire:model="vehicle_make" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('vehicle_make') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Model *</label>
                                <input type="text" wire:model="vehicle_model" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('vehicle_model') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                                <input type="number" wire:model="vehicle_year" min="1900" max="{{ date('Y') + 1 }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('vehicle_year') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">VIN *</label>
                                <input type="text" wire:model="vehicle_vin" maxlength="17" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('vehicle_vin') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company Unit Number</label>
                                <input type="text" wire:model="vehicle_company_unit_number" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type *</label>
                                <select wire:model="vehicle_type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="truck">Truck</option>
                                    <option value="trailer">Trailer</option>
                                    <option value="van">Van</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('vehicle_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">GVWR</label>
                                <input type="text" wire:model="vehicle_gvwr" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Type *</label>
                                <select wire:model="vehicle_fuel_type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Fuel Type</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="gasoline">Gasoline</option>
                                    <option value="electric">Electric</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                                @error('vehicle_fuel_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registration State *</label>
                                <input type="text" wire:model="vehicle_registration_state" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('vehicle_registration_state') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Number *</label>
                                <input type="text" wire:model="vehicle_registration_number" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('vehicle_registration_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Expiration Date *</label>
                                <input type="date" wire:model="vehicle_registration_expiration_date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('vehicle_registration_expiration_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input type="text" wire:model="vehicle_location" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea wire:model="vehicle_notes" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" wire:click="closeAddVehicleModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                                {{ $editingVehicleId ? 'Update Vehicle' : 'Add Vehicle' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Assignment Modal -->
    @if($showAssignmentModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Create Vehicle Assignment</h3>
                        <button wire:click="closeAssignmentModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="saveAssignment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle *</label>
                            <select wire:model="assignment_vehicle_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle['id'] }}">{{ $vehicle['year'] }} {{ $vehicle['make'] }} {{ $vehicle['model'] }} ({{ $vehicle['vin'] }})</option>
                                @endforeach
                            </select>
                            @error('assignment_vehicle_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                                <input type="date" wire:model="assignment_start_date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('assignment_start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" wire:model="assignment_end_date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                            <select wire:model="assignment_status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('assignment_status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea wire:model="assignment_notes" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" wire:click="closeAssignmentModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                                Create Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>