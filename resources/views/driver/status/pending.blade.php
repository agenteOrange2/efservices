<x-driver-layout>
    <div class="py-12 h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-scree">
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl">
                <div class="p-8 bg-white border-b border-gray-200">
                    <div class="text-center max-w-md mx-auto">
                        <img src="{{ asset('build/img/favicon_efservices.png') }}" alt="Application Under Review"
                            class="mx-auto h-32 w-auto mb-6">

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
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="ml-3 text-gray-600">
                                    You'll receive an email notification once your application has been approved.
                                </p>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="ml-3 text-gray-600">
                                    We will also contact you if we need additional information to complete the process.
                                </p>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="ml-3 text-gray-600">
                                    You can check the status of your application at any time from your dashboard.
                                </p>
                            </div>
                        </div>

                        <!-- Información de Clearinghouse -->
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-left">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-md font-medium text-primary">Important: FMCSA Clearinghouse
                                        Registration</h3>
                                    <div class="mt-2 text-sm text-primary">
                                        <p>As part of the driver onboarding process, you must register with the FMCSA
                                            Drug and Alcohol Clearinghouse.</p>
                                        <button type="button"
                                            onclick="document.getElementById('clearinghouseModal').classList.remove('hidden')"
                                            class="mt-2  w-full text-center items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                            Learn More
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t pt-6">
                            <p class="text-sm text-gray-500 mb-6">
                                If you have any questions about your application, please contact us at
                                <a href="mailto:support@efservices.com"
                                    class="text-blue-600 hover:text-blue-800">support@efservices.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Clearinghouse -->
    <div id="clearinghouseModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 overflow-hidden">
                <div class="bg-white px-6 py-4">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h3 class="text-xl font-medium text-gray-900">FMCSA Clearinghouse Information</h3>
                        <button type="button"
                            onclick="document.getElementById('clearinghouseModal').classList.add('hidden')"
                            class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-blue-700">The FMCSA Drug and Alcohol Clearinghouse is a secure online
                                database that gives employers and government agencies real-time information about CDL
                                driver drug and alcohol program violations.</p>
                        </div>

                        <h4 class="font-bold text-lg mt-4">Required Actions:</h4>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li>Register for an account at the FMCSA Clearinghouse website</li>
                            <li>Provide electronic consent for pre-employment and annual queries</li>
                            <li>Complete any required steps in your Clearinghouse account</li>
                        </ol>

                        <h4 class="font-bold text-lg mt-4">Why is this important?</h4>
                        <p class="mt-1">Compliance with the FMCSA Clearinghouse regulations is mandatory for all CDL
                            drivers. Failure to register and provide consent can delay or prevent your employment as a
                            commercial driver.</p>

                        <h4 class="font-bold text-lg mt-4">What information is recorded?</h4>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Alcohol test results with a concentration of 0.04% or greater</li>
                            <li>Refusals to take an alcohol or drug test</li>
                            <li>Drug test results showing positive for illegal substances</li>
                            <li>Any other drug and alcohol program violations</li>
                            <li>Completed return-to-duty process following a violation</li>
                        </ul>

                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-bold">Need Help?</h4>
                            <p>For questions or assistance with the Clearinghouse, contact the FMCSA at 1-844-955-0207
                                or visit their <a href="https://clearinghouse.fmcsa.dot.gov/Resource/Index/Contact-Us"
                                    class="text-blue-600 hover:underline" target="_blank">support portal</a>.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-between">
                        <button type="button"
                            onclick="document.getElementById('clearinghouseModal').classList.add('hidden')"
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Close
                        </button>
                        <a href="https://clearinghouse.fmcsa.dot.gov/" target="_blank"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                            Visit Clearinghouse Website
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                <path
                                    d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-driver-layout>
