<x-driver-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl">
                <div class="p-8 bg-white">
                    <div class="max-w-md mx-auto">
                        <div class="text-center">
                            <img src="{{ asset('build/img/favicon_efservices.png') }}" alt="Application Rejected" class="mx-auto h-32 w-auto mb-6">
                            
                            <h2 class="text-3xl font-bold text-gray-900">Application Rejected</h2>
                            
                            <div class="mt-6 border border-red-200 bg-red-50 rounded-lg p-5">
                                <div class="flex items-center justify-center mb-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100">
                                        <span class="text-red-600 text-lg font-bold">!</span>
                                    </span>
                                </div>
                                <p class="text-gray-700">
                                    Unfortunately, your driver application has been rejected.
                                </p>
                            </div>
                            
                            <div class="mt-6 text-left bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="font-semibold text-gray-900 mb-3">What happens next?</h3>
                                
                                <ul class="space-y-3">
                                    <li class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-sm text-gray-600">
                                            Please contact our support team for more information about why your application was rejected.
                                        </p>
                                    </li>
                                    <li class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-sm text-gray-600">
                                            Our team can provide guidance on how to improve your application for future consideration.
                                        </p>
                                    </li>
                                    <li class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <p class="ml-3 text-sm text-gray-600">
                                            You may be eligible to reapply after a certain period, depending on the reason for rejection.
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <p class="text-sm text-gray-500 text-center mb-6">
                                If you believe there has been an error, please contact us at 
                                <a href="mailto:support@efservices.com" class="text-blue-600 font-medium hover:text-blue-800">support@efservices.com</a>
                            </p>
                            
                            <div class="flex flex-col sm:flex-row justify-center gap-4">
                                <a href="mailto:support@efservices.com" 
                                   class="inline-flex justify-center items-center px-5 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-sm text-white tracking-wider hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-driver-layout>