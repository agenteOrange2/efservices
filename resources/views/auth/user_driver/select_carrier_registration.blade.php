{{-- resources/views/auth/user_driver/select_carrier_registration.blade.php --}}
<x-driver-layout>
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-gray-800">Select a Carrier</h2>
            <p class="text-gray-600 mt-2">Choose the company you want to work with</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($carriers as $carrier)
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48 bg-blue-50 relative overflow-hidden">
                        @if($carrier->getFirstMediaUrl('logo_carrier'))
                            <img src="{{ $carrier->getFirstMediaUrl('logo_carrier') }}" 
                                alt="{{ $carrier->name }}" 
                                class="w-full h-full object-contain p-4">
                        @else
                            <div class="flex items-center justify-center h-full">
                                <img src="{{ asset('build/default_carrier.png') }}" 
                                    alt="{{ $carrier->name }}" 
                                    class="w-32 h-32 object-contain opacity-50">
                            </div>
                        @endif
                        
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $carrier->status == 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $carrier->status == 1 ? 'Active' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $carrier->name }}</h3>
                        
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $carrier->address }}, {{ $carrier->state }} {{ $carrier->zipcode }}
                        </div>
                        
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            DOT: {{ $carrier->dot_number }}
                            @if($carrier->mc_number)
                                <span class="mx-2">|</span>
                                <span>MC: {{ $carrier->mc_number }}</span>
                            @endif
                        </div>
                        
                        <a href="{{ route('driver.register.form', $carrier->slug) }}" 
                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded text-center transition-colors duration-300">
                            Select Carrier
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Mensaje si no hay carriers disponibles -->
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
</x-driver-layout>
