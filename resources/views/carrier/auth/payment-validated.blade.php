<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-blue-50 to-purple-50 flex items-center justify-center p-4">
        <div class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            <!-- Header Section with animated background -->
            <div class="relative bg-gradient-to-r from-emerald-500 via-green-500 to-teal-600 px-8 py-8 overflow-hidden">
                <!-- Animated background elements -->
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-16 -translate-y-16 animate-pulse"></div>
                    <div class="absolute bottom-0 right-0 w-24 h-24 bg-white rounded-full translate-x-12 translate-y-12 animate-pulse delay-1000"></div>
                </div>
                
                <div class="relative flex items-center justify-center">
                    <div class="bg-white rounded-full p-4 mr-6 shadow-lg">
                        <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Payment Validated!</h1>
                        <p class="text-emerald-100 text-lg">Welcome to EF Services, {{ auth()->user()->name }}</p>
                    </div>
                </div>
                
                <!-- Enhanced Progress Circle -->
                <div class="flex justify-center my-8">
                    <div class="relative">
                        <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="45" 
                                fill="none" 
                                stroke="rgba(255,255,255,0.2)" 
                                stroke-width="6" 
                            />
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="45" 
                                fill="none" 
                                stroke="white" 
                                stroke-width="6" 
                                stroke-dasharray="282.5" 
                                stroke-dashoffset="0" 
                                stroke-linecap="round" 
                                class="animate-pulse"
                            />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-3xl font-bold text-white drop-shadow-lg">100%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100"></div>
            
            <!-- Enhanced Status Items -->
            <div class="px-6 py-4">
                <!-- Item 1 -->
                <div class="flex items-center p-5 rounded-xl bg-gradient-to-r from-emerald-50 to-green-50 mb-4 border border-emerald-100 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-green-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 flex-1">
                        <h3 class="font-semibold text-gray-800 text-lg">Registration Complete</h3>
                        <p class="text-emerald-600 font-medium">Account details verified</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="flex items-center p-5 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 mb-4 border border-blue-100 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-5 flex-1">
                        <h3 class="font-semibold text-gray-800 text-lg">Company Information</h3>
                        <p class="text-blue-600 font-medium">Business details confirmed</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="flex items-center p-5 rounded-xl bg-gradient-to-r from-purple-50 to-pink-50 mb-4 border border-purple-100 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 flex-1">
                        <h3 class="font-semibold text-gray-800 text-lg">Membership Plan</h3>
                        <p class="text-purple-600 font-medium">Plan activated successfully</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="flex items-center p-5 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 mb-4 border border-green-100 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 flex-1">
                        <h3 class="font-semibold text-gray-800 text-lg">Payment Validation</h3>
                        <p class="text-green-600 font-medium">Banking information validated successfully</p>
                    </div>
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100 mt-2"></div>
            
            <!-- Enhanced Success Information Section -->
            <div class="mx-6 mb-6 p-6 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-2xl shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-emerald-800 mb-2">Account Activated!</h3>
                        <p class="text-emerald-700 leading-relaxed">
                            Congratulations! Your payment information has been validated and your carrier account is now fully active. You can now access all features of the EF Services platform.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Next Steps Section -->
            <div class="mx-6 mb-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-blue-800 mb-2">Ready to Get Started?</h3>
                        <p class="text-blue-700 leading-relaxed mb-3">
                            Your dashboard is now available with full access to manage drivers, vehicles, documents, and more.
                        </p>
                        <ul class="text-blue-700 space-y-1">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Manage your driver roster
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Track vehicle compliance
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Upload and manage documents
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Action Buttons -->
            <div class="flex p-6 space-x-4">
                <a href="{{ route('carrier.dashboard') }}" class="flex-1 py-4 px-6 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl font-semibold hover:from-emerald-600 hover:to-green-700 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                        </svg>
                        Go to Dashboard
                    </span>
                </a>
                <a href="{{ route('carrier.profile') }}" class="flex-1 py-4 px-6 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        View Profile
                    </span>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>