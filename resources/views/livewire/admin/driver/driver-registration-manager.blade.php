<div class="flex flex-col md:p-5 p-0 box box--stacked" >
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Modern Tabs Navigation -->
    <div class="mb-8 p-5 md:p-0">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Driver Registration</h1>
                    <p class="text-sm text-gray-500">Complete all sections to register the driver</p>
                </div>
            </div>
            <div class="hidden md:flex items-center space-x-2 text-sm text-gray-500">
                <span>Step {{ $currentStep }} of {{ $totalSteps }}</span>
                <div class="w-32 bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
                </div>
            </div>
        </div>
        
        <!-- Responsive Tab Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto scrollbar-hide">
                <nav class="flex min-w-max" aria-label="Tabs">
                    <!-- Tab 1: General Info -->
                    <button wire:click="goToTab(1)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 1 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 1 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="whitespace-nowrap">General Info</span>
                    </button>
                    
                    <!-- Tab 2: Address -->
                    <button wire:click="goToTab(2)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 2 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 2 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Address</span>
                    </button>
                    
                    <!-- Tab 3: Application -->
                    <button wire:click="goToTab(3)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 3 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 3 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Application</span>
                    </button>
                    
                    <!-- Tab 4: License -->
                    <button wire:click="goToTab(4)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 4 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 4 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        <span class="whitespace-nowrap">License</span>
                    </button>
                    
                    <!-- Tab 5: Medical -->
                    <button wire:click="goToTab(5)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 5 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 5 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Medical</span>
                    </button>
                    
                    <!-- Tab 6: Training -->
                    <button wire:click="goToTab(6)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 6 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 6 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="whitespace-nowrap">Training</span>
                    </button>
                    
                    <!-- Tab 7: Traffic -->
                    <button wire:click="goToTab(7)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 7 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 7 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Traffic</span>
                    </button>
                    
                    <!-- Tab 8: Accident -->
                    <button wire:click="goToTab(8)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 8 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 8 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Accident</span>
                    </button>
                    
                    <!-- Tab 9: FMCSR -->
                    <button wire:click="goToTab(9)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 9 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 9 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <span class="whitespace-nowrap">FMCSR</span>
                    </button>
                    
                    <!-- Tab 10: Employment -->
                    <button wire:click="goToTab(10)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 10 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 10 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6m8 0H8m0 0v.01M8 6v.01"></path>
                        </svg>
                        <span class="whitespace-nowrap">Employment</span>
                    </button>
                    
                    <!-- Tab 11: Policy -->
                    <button wire:click="goToTab(11)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 11 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 11 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Policy</span>
                    </button>
                    
                    <!-- Tab 12: Criminal -->
                    <button wire:click="goToTab(12)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 12 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 12 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Criminal</span>
                    </button>
                    
                    <!-- Tab 13: Certification -->
                    <button wire:click="goToTab(13)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 13 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 13 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Certification</span>
                    </button>
                    
                    <!-- Tab 14: Confirmation -->
                    <button wire:click="goToTab(14)" class="group relative flex items-center space-x-2 px-4 py-4 text-sm font-medium transition-all duration-200 {{ $currentStep == 14 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent' }}">
                        <svg class="w-4 h-4 {{ $currentStep == 14 ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="whitespace-nowrap">Confirmation</span>
                    </button>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Custom Scrollbar Styles -->
    <style>
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>

    <!-- Step Content -->
    @if ($currentStep == 1)
        <livewire:admin.driver.driver-general-info-step :driverId="$driverId" :carrier="$carrier" />
    @elseif ($currentStep == 2)
        <livewire:admin.driver.driver-address-step :driverId="$driverId" />
    @elseif ($currentStep == 3)
    <livewire:admin.driver.driver-application-step :driver-id="$driverId" :key="'app-step-'.$driverId" />
    @elseif ($currentStep == 4)
        <livewire:admin.driver.driver-license-step :driverId="$driverId" />
    @elseif ($currentStep == 5)
        <livewire:admin.driver.driver-medical-step :driverId="$driverId" />
    @elseif ($currentStep == 6)
        <livewire:admin.driver.driver-training-step :driverId="$driverId" />
    @elseif ($currentStep == 7)
        <livewire:admin.driver.driver-traffic-step :driverId="$driverId" />
    @elseif ($currentStep == 8)
        <livewire:admin.driver.driver-accident-step :driverId="$driverId" />
    @elseif ($currentStep == 9)
        @livewire('\\App\\Livewire\\Admin\\Driver\\DriverFMCSRStep', ['driverId' => $driverId])
    @elseif ($currentStep == 10)
        <livewire:admin.driver.driver-employment-history-step :driverId="$driverId" />
    @elseif ($currentStep == 11)
        <livewire:admin.driver.driver-company-policy-step :driverId="$driverId" />
    @elseif ($currentStep == 12)
        <livewire:admin.driver.driver-criminal-history-step :driverId="$driverId" />
    @elseif ($currentStep == 13)
        <livewire:admin.driver.driver-certification-step :driverId="$driverId" />
    @elseif ($currentStep == 14)
        <livewire:admin.driver.driver-confirmation :driverId="$driverId" />
    @endif
</div>