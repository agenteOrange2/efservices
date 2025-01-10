<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full bg-white shadow-md rounded-lg p-6">
            <div class="text-center">
                <div class="mx-auto mb-6">
                    <svg class="w-16 h-16 text-green-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Registration Completed</h2>
                <p class="mt-4 text-gray-600">
                    Thank you for registering with EFService! Your carrier registration has been successfully submitted and will be reviewed by our team shortly.
                </p>
                <p class="mt-4 text-gray-600">
                    You will be notified at your registered email once the review is complete.
                </p>
                <div class="mt-6">
                    <a href="{{ url('/') }}" class="inline-block px-6 py-2 text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow">
                        Return to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
