<x-driver-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h2 class="mt-4 text-2xl font-bold text-gray-900">Application Under Review</h2>
                        <p class="mt-2 text-gray-600">
                            Your driver application is currently being reviewed by our team. 
                            This process typically takes 1-3 business days.
                        </p>
                        <p class="mt-4 text-gray-600">
                            You'll receive an email notification once your application has been approved or if we need additional information.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-700 transition">
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-driver-layout>