<x-guest-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 sm:px-20 bg-white">
                <div class="text-center">
                    <div class="mb-4">
                        <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Carrier Status Notice
                    </h2>
                    
                    <p class="mt-2 text-gray-600">
                        {{ session('error') ?? 'This carrier is currently unavailable for new driver registrations.' }}
                    </p>
                    
                    <p class="mt-4 text-sm text-gray-500">
                        Please contact the carrier administrator for more information about when registration will be available.
                    </p>

                    <div class="mt-6 flex justify-center space-x-4">
                        <a href="{{ url('/') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Return to Home
                        </a>
                        <a href="mailto:support@example.com" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>