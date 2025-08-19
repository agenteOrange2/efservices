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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">¡Registration Complete!</h1>
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
                        <h3 class="font-semibold text-gray-800 text-lg">Personal Information</h3>
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
                        <p class="text-purple-600 font-medium">Plan selected and terms accepted</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="flex items-center p-5 rounded-xl bg-gradient-to-r from-orange-50 to-yellow-50 mb-4 border border-orange-100 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 flex-1">
                        <h3 class="font-semibold text-gray-800 text-lg">Banking Information</h3>
                        <p class="text-orange-600 font-medium">Payment details submitted</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center shadow-lg">
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
                        <h3 class="text-lg font-bold text-emerald-800 mb-2">Registration Successful!</h3>
                        <p class="text-emerald-700 leading-relaxed">
                            Your application has been submitted and is now under review. You'll receive an email notification once your account is approved.
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
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-blue-800 mb-2">What's Next?</h3>
                        <p class="text-blue-700 leading-relaxed">
                            Our team will review your application within 2-5 business days. You can check your status anytime by logging into your account.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Action Buttons -->
            <div class="flex p-6 space-x-4">
                <a href="{{ route('carrier.pending.validation') }}" class="flex-1 py-4 px-6 bg-gradient-to-r from-gray-100 to-gray-200 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold text-center hover:from-gray-200 hover:to-gray-300 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Check Status
                    </span>
                </a>
                <form method="POST" action="{{ route('custom.logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl font-semibold hover:from-emerald-600 hover:to-green-700 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Sign Out
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>