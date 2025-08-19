<x-guest-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 text-center">
                <h2 class="text-xl text-gray-800 font-semibold">Hi, {{ auth()->user()->name }}</h2>
                <p class="text-gray-500 mt-1">Banking information under validation</p>
                
                <!-- Timer Circle -->
                <div class="flex justify-center my-6">
                    <div class="relative">
                        <svg class="w-32 h-32" viewBox="0 0 100 100">
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="45" 
                                fill="none" 
                                stroke="#E2E8F0" 
                                stroke-width="6" 
                            />
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="45" 
                                fill="none" 
                                stroke="url(#gradient)" 
                                stroke-width="6" 
                                stroke-dasharray="282.5" 
                                stroke-dashoffset="85" 
                                stroke-linecap="round" 
                                class="animate-pulse"
                            />
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#1D4ED8;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $estimatedTime['estimated_days_remaining'] }}</div>
                            <div class="text-xs text-gray-500 font-medium">days left</div>
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
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Registration Complete</h3>
                        <p class="text-sm text-gray-500">Company information verified</p>
                    </div>
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="flex items-center p-4">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Banking Information Submitted</h3>
                        <p class="text-sm text-gray-500">Account details received securely</p>
                    </div>
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="flex items-center p-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Banking Validation</h3>
                        <p class="text-sm text-gray-500">Verifying account information</p>
                    </div>
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="flex items-center p-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="font-medium text-gray-800">Final Approval</h3>
                        <p class="text-sm text-gray-500">Account activation pending</p>
                    </div>
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100 mt-2"></div>
            
            <!-- Information Section -->
            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="h-4 w-4 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-blue-800 mb-1">Security Notice</h4>
                        <p class="text-sm text-blue-700">
                            Your banking information is encrypted and secure. Our team is validating the details to ensure account safety and compliance.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex p-4 space-x-3">
                <a href="mailto:support@efservices.la" class="flex-1 py-3 px-4 bg-white border-2 border-gray-200 text-gray-700 rounded-lg font-medium text-center hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Contact Support
                </a>
                <form method="POST" action="{{ route('custom.logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-medium hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>