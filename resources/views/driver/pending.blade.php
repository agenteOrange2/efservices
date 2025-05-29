<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white">
                    <div class="text-center">
                        <div class="mb-4">
                            <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            Account Pending Approval
                        </h2>
                        
                        <p class="mt-2 text-gray-600">
                            Your driver account is currently pending approval from the administrator.
                            We will notify you via email once your account has been approved.
                        </p>
                        
                        <p class="mt-4 text-sm text-gray-500">
                            If you have any questions, please contact your carrier administrator.
                        </p>
                        
                        <!-- Información de Clearinghouse -->
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-md font-medium text-blue-800">Important: FMCSA Clearinghouse Registration</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>As part of the driver onboarding process, you must register with the FMCSA Drug and Alcohol Clearinghouse.</p>
                                        <button
                                            type="button"
                                            onclick="document.getElementById('clearinghouseModal').classList.remove('hidden')"
                                            class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                            Learn More
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>