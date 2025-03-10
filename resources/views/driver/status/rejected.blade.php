<x-driver-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h2 class="mt-4 text-2xl font-bold text-gray-900">Application Rejected</h2>
                        <p class="mt-2 text-gray-600">
                            Unfortunately, your driver application has been rejected.
                        </p>
                        <p class="mt-4 text-gray-600">
                            Please contact our support team for more information about why your application 
                            was rejected and to discuss next steps.
                        </p>
                        <div class="mt-6 flex justify-center space-x-4">
                            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-700 transition">
                                Dashboard
                            </a>
                            <a href="mailto:support@example.com" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-700 transition">
                                Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-driver-layout>