<x-guest-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 text-center">
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-xl text-gray-800 font-semibold">Registration Completed</h2>
                <p class="text-gray-500 mt-3">
                    Thank you for registering with EFService! Your carrier registration has been successfully submitted.
                </p>
                
                <!-- Status Indicator -->
                <div class="flex justify-center my-6">
                    <div class="relative">
                        <svg class="w-28 h-28" viewBox="0 0 100 100">
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="45" 
                                fill="none" 
                                stroke="#E2E8F0" 
                                stroke-width="8" 
                            />
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="45" 
                                fill="none" 
                                stroke="#10B981" 
                                stroke-width="8" 
                                stroke-dasharray="282.5" 
                                stroke-dashoffset="70" 
                                stroke-linecap="round" 
                            />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-xl font-bold text-gray-700">75%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100"></div>
            
            <!-- Status Items -->
            <div class="px-1">
                <!-- Item 1 -->
                <div class="flex items-center p-4">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Registration Submitted</h3>
                        <p class="text-sm text-gray-500">Your carrier account has been created</p>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="flex items-center p-4">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Basic Information</h3>
                        <p class="text-sm text-gray-500">Company details received</p>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="flex items-center p-4">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Verification Process</h3>
                        <p class="text-sm text-gray-500">Under review by our team</p>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="flex items-center p-4">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Account Activation</h3>
                        <p class="text-sm text-gray-500">Pending approval</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100 mt-2"></div>
            
            <!-- Action Buttons -->
            <div class="flex p-4">
                <a href="mailto:support@efservices.la" class="flex-1 py-3 px-4 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium text-center hover:bg-gray-50 transition-colors">
                    Contact Support
                </a>
                <form method="POST" action="{{ route('custom.logout') }}" class="flex-1 ml-3">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>