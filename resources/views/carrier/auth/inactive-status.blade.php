<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            <!-- Header Section with animated background -->
            <div class="relative bg-gradient-to-r from-red-500 via-orange-500 to-red-600 px-8 py-8 overflow-hidden">
                <!-- Animated background elements -->
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-16 -translate-y-16 animate-pulse"></div>
                    <div class="absolute bottom-0 right-0 w-24 h-24 bg-white rounded-full translate-x-12 translate-y-12 animate-pulse delay-1000"></div>
                </div>
                
                <div class="relative flex items-center justify-center">
                    <div class="bg-white rounded-full p-4 mr-6 shadow-lg">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Account Inactive</h1>
                        <p class="text-red-100 text-lg">{{ auth()->user()->name }}, your account is currently inactive</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100"></div>
            
            <!-- Carrier Information Section -->
            <div class="px-6 py-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 mb-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Carrier Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Company Name</p>
                            <p class="font-medium text-gray-800">{{ $carrier->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $carrier->status_name ?? 'Inactive' }}
                            </span>
                        </div>
                        @if($carrier->dot_number)
                        <div>
                            <p class="text-sm text-gray-500">DOT Number</p>
                            <p class="font-medium text-gray-800">{{ $carrier->dot_number }}</p>
                        </div>
                        @endif
                        @if($carrier->mc_number)
                        <div>
                            <p class="text-sm text-gray-500">MC Number</p>
                            <p class="font-medium text-gray-800">{{ $carrier->mc_number }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Reason Section -->
            <div class="mx-6 mb-6 p-6 bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-2xl shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-red-800 mb-2">Account Deactivated</h3>
                        <p class="text-red-700 leading-relaxed mb-3">
                            Your carrier account has been deactivated by our administrators. This may be due to compliance issues, incomplete documentation, or other administrative reasons.
                        </p>
                        <p class="text-red-700 leading-relaxed">
                            Please contact our support team to understand the reason for deactivation and the steps needed for reactivation.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Support Information Section -->
            <div class="mx-6 mb-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-blue-800 mb-2">Need Help?</h3>
                        <p class="text-blue-700 leading-relaxed mb-3">
                            Our support team is here to help you understand the deactivation and guide you through the reactivation process.
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center text-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium">Email:</span> support@efcts.com
                            </div>
                            <div class="flex items-center text-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="font-medium">Phone:</span> (555) 123-4567
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex p-6 space-x-4">
                <form method="POST" action="{{ route('carrier.request.reactivation') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-orange-500 to-red-600 text-white rounded-xl font-semibold hover:from-orange-600 hover:to-red-700 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Request Reactivation
                        </span>
                    </button>
                </form>
                <a href="mailto:support@efcts.com" class="flex-1 py-4 px-6 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Contact Support
                    </span>
                </a>
            </div>
            
            <!-- Sign Out Button -->
            <div class="px-6 pb-6">
                <form method="POST" action="{{ route('custom.logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-gray-100 to-gray-200 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:from-gray-200 hover:to-gray-300 hover:shadow-lg transition-all duration-300">
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