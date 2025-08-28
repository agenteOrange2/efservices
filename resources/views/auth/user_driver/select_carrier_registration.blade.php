{{-- resources/views/auth/user_driver/select_carrier_registration.blade.php --}}
<x-driver-layout>
    <div x-data="{ search: '' }">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-gray-800">Select a Carrier</h2>
            <p class="text-gray-600 mt-2">Choose the company you want to work with</p>
        </div>
        
        <!-- Buscador de carriers -->
        <div class="mb-8 max-w-3xl mx-auto">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    x-model="search" 
                    placeholder="Search carriers by name, DOT number, or location..." 
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
            <div class="mt-2 text-sm text-gray-500 flex justify-between">
                <span>Total carriers: {{ count($carriers) }}</span>
                <button @click="search = ''" class="text-blue-600 hover:text-blue-800" x-show="search">Clear search</button>
            </div>
        </div>
        
        <!-- Carriers disponibles -->
        <div class="mb-10">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Available Carriers</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ carriers: [] }" x-init="carriers = {{ json_encode($carriers->filter(function($carrier) { 
                return $carrier->status == 1 && !$carrier->is_full;
            })->values()) }}">
                <!-- Mensaje si no hay carriers disponibles después de filtrar -->
                <template x-if="carriers.filter(carrier => carrier.name.toLowerCase().includes(search.toLowerCase()) || 
                                                  carrier.dot_number.toString().includes(search) || 
                                                  (carrier.address + ' ' + carrier.state + ' ' + carrier.zipcode).toLowerCase().includes(search.toLowerCase())).length === 0">
                    <div class="col-span-full text-center py-8 bg-gray-50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No carriers match your search</h3>
                        <p class="text-gray-500">Try different search terms or clear the search</p>
                    </div>
                </template>
                
                <!-- Listado de carriers disponibles -->
                <template x-for="carrier in carriers" :key="carrier.id">
                    <div 
                        x-show="carrier.name.toLowerCase().includes(search.toLowerCase()) || 
                                carrier.dot_number.toString().includes(search) || 
                                (carrier.address + ' ' + carrier.state + ' ' + carrier.zipcode).toLowerCase().includes(search.toLowerCase())"
                        class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300"
                    >
                        <div class="h-48 bg-blue-50 relative overflow-hidden">
                            <template x-if="carrier.media && carrier.media.length > 0">
                                <img :src="carrier.media[0].original_url" :alt="carrier.name" class="w-full h-full object-contain p-4">
                            </template>
                            <template x-if="!carrier.media || carrier.media.length === 0">
                                <div class="flex items-center justify-center h-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                            </template>
                            
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="carrier.name"></h3>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span x-text="carrier.address + ', ' + carrier.state + ' ' + carrier.zipcode"></span>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>DOT: <span x-text="carrier.dot_number"></span></span>
                                <template x-if="carrier.mc_number">
                                    <span>
                                        <span class="mx-2">|</span>
                                        <span>MC: <span x-text="carrier.mc_number"></span></span>
                                    </span>
                                </template>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>
                                    <span x-text="carrier.driver_count"></span> of <span x-text="carrier.max_drivers"></span> drivers
                                </span>
                            </div>
                            
                            <a :href="`{{ route('driver.register.form', '') }}/${carrier.slug}`" 
                               class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded text-center transition-colors duration-300">
                                Select Carrier
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Carriers llenos -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Full Carriers (Not Available)</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ carriers: [] }" x-init="carriers = {{ json_encode($carriers->filter(function($carrier) { 
                return $carrier->is_full;
            })->values()) }}">
                <!-- Mensaje si no hay carriers llenos después de filtrar -->
                <template x-if="carriers.filter(carrier => carrier.name.toLowerCase().includes(search.toLowerCase()) || 
                                                  carrier.dot_number.toString().includes(search) || 
                                                  (carrier.address + ' ' + carrier.state + ' ' + carrier.zipcode).toLowerCase().includes(search.toLowerCase())).length === 0">
                    <div class="col-span-full text-center py-8 bg-gray-50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No full carriers</h3>
                        <p class="text-gray-500">All carriers have available positions</p>
                    </div>
                </template>
                
                <!-- Listado de carriers llenos -->
                <template x-for="carrier in carriers" :key="carrier.id">
                    <div 
                        x-show="carrier.name.toLowerCase().includes(search.toLowerCase()) || 
                                carrier.dot_number.toString().includes(search) || 
                                (carrier.address + ' ' + carrier.state + ' ' + carrier.zipcode).toLowerCase().includes(search.toLowerCase())"
                        class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 opacity-75"
                    >
                        <div class="h-48 bg-gray-50 relative overflow-hidden">
                            <template x-if="carrier.media && carrier.media.length > 0">
                                <img :src="carrier.media[0].original_url" :alt="carrier.name" class="w-full h-full object-contain p-4 grayscale">
                            </template>
                            <template x-if="!carrier.media || carrier.media.length === 0">
                                <div class="flex items-center justify-center h-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                            </template>
                            
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Full
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-700 mb-2" x-text="carrier.name"></h3>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span x-text="carrier.address + ', ' + carrier.state + ' ' + carrier.zipcode"></span>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>DOT: <span x-text="carrier.dot_number"></span></span>
                                <template x-if="carrier.mc_number">
                                    <span>
                                        <span class="mx-2">|</span>
                                        <span>MC: <span x-text="carrier.mc_number"></span></span>
                                    </span>
                                </template>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-red-600 font-medium">
                                    Full (<span x-text="carrier.driver_count"></span>/<span x-text="carrier.max_drivers"></span> drivers)
                                </span>
                            </div>
                            
                            <button disabled
                               class="block w-full bg-gray-300 text-gray-500 cursor-not-allowed font-medium py-2 px-4 rounded text-center">
                                Not Available
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Mensaje si no hay carriers en absoluto -->
        @if(count($carriers) == 0)
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No carriers available</h3>
                <p class="mt-1 text-gray-500">Please try again later or contact an administrator.</p>
            </div>
        @endif
    </div>
    </div>
    
    <!-- Alpine.js is already included with Livewire -->
</x-driver-layout>
