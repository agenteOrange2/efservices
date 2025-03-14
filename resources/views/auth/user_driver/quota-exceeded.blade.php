
<x-guest-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 sm:px-20 bg-white">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Maximum Drivers Reached
                    </h2>
                    <p class="mt-2 text-gray-600">
                        This carrier has reached their maximum number of allowed drivers.
                        Please contact the carrier administrator for more information.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>