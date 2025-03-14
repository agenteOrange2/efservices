<x-driver-layout>
    <div class="py-12 h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-scree">
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl">
                <div class="p-8 bg-white border-b border-gray-200">
                    <div class="text-center max-w-md mx-auto">
                        <img src="{{ asset('build/img/favicon_efservices.png') }}" alt="Application Under Review" class="mx-auto h-32 w-auto mb-6">
                        
                        <h2 class="mt-4 text-3xl font-bold text-gray-900">Application Under Review</h2>
                        
                        <div class="mt-6 border border-yellow-200 bg-yellow-50 rounded-lg p-4">
                            <div class="flex items-center justify-center mb-2">
                                <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></div>
                                <span class="ml-2 text-sm font-medium text-yellow-700">Status: Pending Approval</span>
                            </div>
                            <p class="text-gray-700">
                                Your driver application is currently being reviewed by our team. 
                                This process typically takes 1-3 business days.
                            </p>
                        </div>
                        
                        <div class="mt-8 space-y-4 text-left">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="ml-3 text-gray-600">
                                    You'll receive an email notification once your application has been approved.
                                </p>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="ml-3 text-gray-600">
                                    We will also contact you if we need additional information to complete the process.
                                </p>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="ml-3 text-gray-600">
                                    You can check the status of your application at any time from your dashboard.
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-10 border-t pt-6">
                            <p class="text-sm text-gray-500 mb-6">
                                If you have any questions about your application, please contact us at 
                                <a href="mailto:support@efservices.com" class="text-blue-600 hover:text-blue-800">support@efservices.com</a>
                            </p>
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-driver-layout>