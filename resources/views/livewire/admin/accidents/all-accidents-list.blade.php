<div>
    <!-- Mensajes Flash -->
    @if(session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-5">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- Cabecera -->
    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Driver Accidents Management
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button wire:click="openAddModal" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-md flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Accident
            </button>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-md shadow-md p-5 mt-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="searchTerm" type="text" class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" placeholder="Search accidents...">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Carrier</label>
                <select wire:model.live="carrierFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="">All Carriers</option>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Driver</label>
                <select wire:model.live="driverFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->user->name }} {{ $driver->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input wire:model="dateFrom" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" placeholder="From">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input wire:model.live="dateTo" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" placeholder="To">
            </div>
            <div class="flex items-end">
                <button wire:click="$set('dateFrom', ''); $set('dateTo', '');" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear Dates
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-md shadow-md p-5 mt-5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('accident_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            Date
                            @if($sortField === 'accident_date')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nature of Accident</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Injuries</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fatalities</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($accidents as $accident)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $accident->accident_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $accident->userDriverDetail->carrier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $accident->userDriverDetail->user->name }} {{ $accident->userDriverDetail->last_name }}
                        </td>
                        <td class="px-6 py-4">{{ $accident->nature_of_accident }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($accident->had_injuries)
                                <span class="text-red-600">Yes ({{ $accident->number_of_injuries }})</span>
                            @else
                                <span class="text-green-600">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($accident->had_fatalities)
                                <span class="text-red-600">Yes ({{ $accident->number_of_fatalities }})</span>
                            @else
                                <span class="text-green-600">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center items-center">
                                <button wire:click="openEditModal({{ $accident->id }})" class="p-1 bg-blue-600 text-white rounded-md mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="openDeleteModal({{ $accident->id }})" class="p-1 bg-red-600 text-white rounded-md mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <a href="{{ route('admin.drivers.accident-history', $accident->userDriverDetail->id) }}" class="p-1 border border-gray-300 bg-white text-gray-700 rounded-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="mt-2 text-gray-500">No accident records found</p>
                                <button wire:click="openAddModal" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add First Accident
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-5">
            {{ $accidents->links() }}
        </div>
    </div>
    
    <!-- Modal Añadir Accidente -->
    <div id="add-accident-modal" class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showAddModal ? '' : 'display: none;' }}">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Add Accident Record</h3>
                        <button wire:click="closeModals" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="save" class="mt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Seleccionar Carrier y Driver -->
                            <div class="col-span-1">
                                <label for="carrier" class="block text-sm font-medium text-gray-700 mb-1">Carrier</label>
                                <select wire:model.live="selectedCarrierId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                                    <option value="">Select Carrier</option>
                                    @foreach($allCarriers as $carrier)
                                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCarrierId') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-span-1">
                                <label for="driver" class="block text-sm font-medium text-gray-700 mb-1">Driver</label>
                                <select wire:model.live="selectedDriverId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required {{ empty($driversForCarrier) ? 'disabled' : '' }}>
                                    <option value="">{{ empty($driversForCarrier) ? 'Select a carrier first' : 'Select Driver' }}</option>
                                    @foreach($driversForCarrier as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->user->name }} {{ $driver->last_name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedDriverId') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Fecha del accidente -->
                            <div class="col-span-1">
                                <label for="accident_date" class="block text-sm font-medium text-gray-700 mb-1">Accident Date</label>
                                <input wire:model="accident_date" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="accident_date" required>
                                @error('accident_date') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Naturaleza del accidente -->
                            <div class="col-span-2">
                                <label for="nature_of_accident" class="block text-sm font-medium text-gray-700 mb-1">Nature of Accident</label>
                                <input wire:model="nature_of_accident" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="nature_of_accident" 
                                    placeholder="Describe the accident" required>
                                @error('nature_of_accident') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Lesiones -->
                            <div class="col-span-1">
                                <div class="flex items-center">
                                    <input wire:model.live="had_injuries" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" id="had_injuries">
                                    <label for="had_injuries" class="ml-2 block text-sm font-medium text-gray-700">Had Injuries?</label>
                                </div>
                                @error('had_injuries') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($had_injuries)
                            <div class="col-span-1">
                                <label for="number_of_injuries" class="block text-sm font-medium text-gray-700 mb-1">Number of Injuries</label>
                                <input wire:model.live.live="number_of_injuries" type="number" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="number_of_injuries" required>
                                @error('number_of_injuries') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            @endif
                            
                            <!-- Fatalidades -->
                            <div class="col-span-1">
                                <div class="flex items-center">
                                    <input wire:model="had_fatalities" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" id="had_fatalities">
                                    <label for="had_fatalities" class="ml-2 block text-sm font-medium text-gray-700">Had Fatalities?</label>
                                </div>
                                @error('had_fatalities') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($had_fatalities)
                            <div class="col-span-1">
                                <label for="number_of_fatalities" class="block text-sm font-medium text-gray-700 mb-1">Number of Fatalities</label>
                                <input wire:model="number_of_fatalities" type="number" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="number_of_fatalities" required>
                                @error('number_of_fatalities') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            @endif
                            
                            <!-- Comentarios -->
                            <div class="col-span-2">
                                <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
                                <textarea wire:model="comments" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="comments" rows="3" 
                                    placeholder="Additional comments"></textarea>
                                @error('comments') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                            <button wire:click="closeModals" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:text-sm">
                                Cancel
                            </button>
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Editar Accidente - Similar al modal de añadir pero con los datos pre-rellenados -->
    <div id="edit-accident-modal" class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showEditModal ? '' : 'display: none;' }}">
        <!-- Estructura similar al modal de añadir, con los mismos campos pero para edición -->
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Edit Accident Record</h3>
                        <button wire:click="closeModals" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="update" class="mt-4">
                        <!-- Mismo contenido que el formulario de añadir -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Mismos campos que en el formulario de añadir -->
                            <div class="col-span-1">
                                <label for="edit_carrier" class="block text-sm font-medium text-gray-700 mb-1">Carrier</label>
                                <select wire:model="selectedCarrierId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                                    <option value="">Select Carrier</option>
                                    @foreach($allCarriers as $carrier)
                                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCarrierId') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-span-1">
                                <label for="edit_driver" class="block text-sm font-medium text-gray-700 mb-1">Driver</label>
                                <select wire:model="selectedDriverId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required {{ empty($driversForCarrier) ? 'disabled' : '' }}>
                                    <option value="">{{ empty($driversForCarrier) ? 'Select a carrier first' : 'Select Driver' }}</option>
                                    @foreach($driversForCarrier as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->user->name }} {{ $driver->last_name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedDriverId') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-span-1">
                                <label for="edit_accident_date" class="block text-sm font-medium text-gray-700 mb-1">Accident Date</label>
                                <input wire:model="accident_date" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="edit_accident_date" required>
                                @error('accident_date') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-span-2">
                                <label for="edit_nature_of_accident" class="block text-sm font-medium text-gray-700 mb-1">Nature of Accident</label>
                                <input wire:model="nature_of_accident" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="edit_nature_of_accident" 
                                    placeholder="Describe the accident" required>
                                @error('nature_of_accident') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-span-1">
                                <div class="flex items-center">
                                    <input wire:model="had_injuries" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" id="edit_had_injuries">
                                    <label for="edit_had_injuries" class="ml-2 block text-sm font-medium text-gray-700">Had Injuries?</label>
                                </div>
                                @error('had_injuries') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            @if($had_injuries)
                            <div class="col-span-1">
                                <label for="edit_number_of_injuries" class="block text-sm font-medium text-gray-700 mb-1">Number of Injuries</label>
                                <input wire:model="number_of_injuries" type="number" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="edit_number_of_injuries" required>
                                @error('number_of_injuries') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            @endif
                            
                            <div class="col-span-1">
                                <div class="flex items-center">
                                    <input wire:model="had_fatalities" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" id="edit_had_fatalities">
                                    <label for="edit_had_fatalities" class="ml-2 block text-sm font-medium text-gray-700">Had Fatalities?</label>
                                </div>
                                @error('had_fatalities') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($had_fatalities)
                            <div class="col-span-1">
                                <label for="edit_number_of_fatalities" class="block text-sm font-medium text-gray-700 mb-1">Number of Fatalities</label>
                                <input wire:model="number_of_fatalities" type="number" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="edit_number_of_fatalities" required>
                                @error('number_of_fatalities') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            @endif
                            
                            <div class="col-span-2">
                                <label for="edit_comments" class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
                                <textarea wire:model="comments" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="edit_comments" rows="3" 
                                    placeholder="Additional comments"></textarea>
                                @error('comments') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                            <button wire:click="closeModals" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:text-sm">
                                Cancel
                            </button>
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Eliminar Accidente -->
    <div id="delete-accident-modal" class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showDeleteModal ? '' : 'display: none;' }}">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Are you sure?
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Do you really want to delete this accident record? This process cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="delete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button wire:click="closeModals" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
