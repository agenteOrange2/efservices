<x-guest-layout>
    <div x-data="{ selectedPlan: '{{ old('id_plan', '') }}', showMobile: false }" x-cloak>
        <div
            class="container grid grid-cols-12 px-5 py-10 sm:px-10 sm:py-14 md:px-36 lg:h-screen lg:max-w-[1550px] lg:py-0 lg:pl-14 lg:pr-12 xl:px-24 2xl:max-w-[1750px]">
            <div @class([
                'relative z-50 h-full col-span-12 p-7 sm:p-14 bg-white rounded-2xl lg:bg-transparent lg:pr-10 lg:col-span-5 xl:pr-24 2xl:col-span-4 lg:p-0',
                "before:content-[''] before:absolute before:inset-0 before:-mb-3.5 before:bg-white/40 before:rounded-2xl before:mx-5",
            ])>
                <div class="relative z-10 flex flex-col justify-center w-full h-full py-2 lg:py-32">
                    <div
                        class="flex h-[55px] w-[55px] items-center justify-center rounded-[0.8rem] border border-primary/30">
                        <div
                            class="relative flex h-[50px] w-[50px] items-center justify-center rounded-[0.6rem] bg-white bg-gradient-to-b from-theme-1/90 to-theme-2/90">
                            <div class="relative h-[26px] w-[26px] -rotate-45 [&_div]:bg-white">
                                <div class="absolute inset-y-0 left-0 my-auto h-[75%] w-[20%] rounded-full opacity-50"></div>
                                <div class="absolute inset-0 m-auto h-[120%] w-[20%] rounded-full"></div>
                                <div class="absolute inset-y-0 right-0 my-auto h-[75%] w-[20%] rounded-full opacity-50">
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- JETSTREAM --}}

                    <div class="mt-10">
                        <div class="text-2xl font-medium">Complete Your Carrier Registration</div>
                        <div class="mt-7">


                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form class="max-w-md mx-auto" method="POST"
                                action="{{ route('carrier.complete_registration') }}">
                                @csrf

                                <div class="relative z-0 w-full mb-5 group">
                                    <x-label for="email" value="{{ __('Carrier Name') }}" />
                                    <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        type="text" name="name" id="name" value="{{ old('name') }}"
                                        placeholder="Company Name" required />
                                </div>

                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="carrier_address">Address</label>
                                    <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        type="text" name="address" id="address" value="{{ old('address') }}"
                                        required />
                                </div>

                                <!-- State -->
                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="state">State</label>
                                    <select name="state" id="state"
                                        class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full" required>
                                        <option value="">{{ __('Select State') }}</option>
                                        @foreach ($usStates as $abbr => $name)
                                            <option value="{{ $abbr }}"
                                                {{ old('state') === $abbr ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="zipcode">Zip Code</label>
                                    <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        type="number" name="zipcode" id="zipcode" value="{{ old('zipcode') }}"
                                        required />
                                </div>

                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="ein_number">EIN Number</label>
                                    <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        type="number" name="ein_number" id="ein_number" value="{{ old('ein_number') }}"
                                        required />
                                </div>

                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="dot_number">Dot Number</label>
                                    <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        type="number" name="dot_number" id="dot_number" value="{{ old('dot_number') }}"
                                        required />
                                </div>

                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="mc_number">MC Number</label>
                                    <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        type="number" name="mc_number" id="mc_number" value="{{ old('mc_number') }}"
                                        required />
                                </div>

                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="state_dot">State Dot</label>
                                    <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        type="text" name="state_dot" id="state_dot" value="{{ old('state_dot') }}"
                                        required />
                                </div>

                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="ifta_account">IFTA Account</label>
                                    <x-input type="text" name="ifta_account" id="ifta_account"
                                        class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                        value="{{ old('ifta_account') }}" required />
                                </div>

                                <!-- Membership Selection -->
                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="id_plan">Membership</label>
                                    <input type="hidden" name="id_plan" id="id_plan" x-model="selectedPlan" required>
                                    <div class="p-3 border border-slate-200 rounded-md bg-slate-50">
                                        <div class="flex justify-between items-center">
                                            <p class="text-sm font-medium text-slate-700">Please select a membership plan</p>
                                            <button type="button" @click="showMobile = !showMobile" class="lg:hidden px-3 py-1 text-xs font-medium text-white bg-primary rounded-md">
                                                <span x-text="showMobile ? 'Hide Plans' : 'Show Plans'"></span>
                                            </button>
                                        </div>
                                        <p class="text-sm text-slate-500 mt-1" x-show="!selectedPlan">No plan selected</p>
                                        @foreach ($memberships as $membership)
                                            <div x-show="selectedPlan == '{{ $membership->id }}'" class="mt-2 p-2 bg-white rounded-md border border-primary/30">
                                                <p class="font-medium text-primary">{{ $membership->name }}</p>
                                                <div class="flex justify-between mt-1">
                                                    <span class="text-sm text-slate-600">Price:</span>
                                                    <span class="text-sm font-medium">
                                                        @if($membership->pricing_type == 'plan')
                                                            ${{ $membership->price }}
                                                        @else
                                                            @php
                                                                $carrierTotal = $membership->carrier_price * $membership->max_carrier;
                                                                $driverTotal = $membership->driver_price * $membership->max_drivers;
                                                                $vehicleTotal = $membership->vehicle_price * $membership->max_vehicles;
                                                                $totalPrice = $carrierTotal + $driverTotal + $vehicleTotal;
                                                            @endphp
                                                            ${{ $totalPrice }}
                                                        @endif
                                                    </span>
                                                </div>
                                                @if($membership->pricing_type == 'individual')
                                                <div class="mt-1 text-xs text-slate-500">
                                                    (U: ${{ $membership->carrier_price }} × {{ $membership->max_carrier }} = ${{ $carrierTotal }} +
                                                    D: ${{ $membership->driver_price }} × {{ $membership->max_drivers }} = ${{ $driverTotal }} +
                                                    V: ${{ $membership->vehicle_price }} × {{ $membership->max_vehicles }} = ${{ $vehicleTotal }})
                                                </div>
                                                @endif
                                                <div class="flex justify-between mt-1">
                                                    <span class="text-sm text-slate-600">Max Users:</span>
                                                    <span class="text-sm font-medium">{{ $membership->max_carrier }}</span>
                                                </div>
                                                <div class="flex justify-between mt-1">
                                                    <span class="text-sm text-slate-600">Max Drivers:</span>
                                                    <span class="text-sm font-medium">{{ $membership->max_drivers }}</span>
                                                </div>
                                                <div class="flex justify-between mt-1">
                                                    <span class="text-sm text-slate-600">Max Vehicles:</span>
                                                    <span class="text-sm font-medium">{{ $membership->max_vehicles }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Document Selection -->
                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="has_documents">Do you have documents ready to upload?</label>
                                    <select data-tw-merge aria-label="Default select example"
                                        class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2"
                                        id="has_documents" name="has_documents" required>
                                        <option value="">Select an option</option>
                                        <option value="yes" {{ old('has_documents') == 'yes' ? 'selected' : '' }}>
                                            Yes, I have documents ready to upload
                                        </option>
                                        <option value="no" {{ old('has_documents') == 'no' ? 'selected' : '' }}>
                                            No, I'll upload documents later
                                        </option>
                                    </select>
                                </div>

                                <div class="mt-5 text-center xl:mt-8 xl:text-left">
                                    <x-base.button type="submit"
                                        class="w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3 text-white">
                                        {{ __('Complete Registration') }}
                                    </x-base.button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div
            class="container fixed inset-0 grid h-screen w-screen grid-cols-12 pl-4 pr-4 lg:pl-14 lg:pr-12 lg:max-w-[1550px] xl:px-24 2xl:max-w-[1750px]" :class="{'z-50': showMobile}">
            <div @class([
                'relative h-screen col-span-12 lg:col-span-5 2xl:col-span-4 z-20',
                "after:bg-white after:hidden after:lg:block after:content-[''] after:absolute after:right-0 after:inset-y-0 after:bg-gradient-to-b after:from-white after:to-slate-100/80 after:w-[800%] after:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0]",
                "before:content-[''] before:hidden before:lg:block before:absolute before:right-0 before:inset-y-0 before:my-6 before:bg-gradient-to-b before:from-white/10 before:to-slate-50/10 before:bg-white/50 before:w-[800%] before:-mr-4 before:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0]",
            ])></div>
            <!-- Overlay para móvil -->
            <div class="fixed inset-0 bg-black/50 z-40" x-show="showMobile" x-transition @click="showMobile = false" style="display: none;"></div>
            
            <div @class([
                'h-full col-span-12 lg:col-span-7 2xl:col-span-8 lg:relative',
                "before:content-[''] before:absolute before:lg:-ml-10 before:left-0 before:inset-y-0 before:bg-gradient-to-b before:from-theme-1 before:to-theme-2 before:w-screen before:lg:w-[800%]",
                "after:content-[''] after:absolute after:inset-y-0 after:left-0 after:w-screen after:lg:w-[800%] after:bg-texture-white after:bg-fixed after:bg-center after:lg:bg-[25rem_-25rem] after:bg-no-repeat",
            ]) 
            x-show="showMobile || window.innerWidth >= 1024" 
            :class="{'z-50': showMobile, 'fixed inset-0 overflow-y-auto': showMobile, 'lg:relative': !showMobile}" 
            style="display: none;" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90">
                <div x-show="showMobile" @click="showMobile = false" class="absolute top-4 right-4 lg:hidden z-50 bg-white/20 rounded-full p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="sticky top-0 z-10 flex-col justify-center h-screen px-4 py-8 lg:py-0 lg:ml-16 flex xl:ml-28 2xl:ml-36 overflow-y-auto">
                    <div class="text-2xl md:text-[2.6rem] font-medium leading-[1.4] text-white xl:text-5xl xl:leading-[1.2] mb-6">
                        Choose Your Membership Plan
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 overflow-y-auto pr-3 lg:pr-6 max-h-[60vh] md:max-h-[60vh] pb-20 md:pb-0">
                        @foreach ($memberships as $membership)
                        <div 
                            @click="selectedPlan = '{{ $membership->id }}'"
                            :class="{'border-primary shadow-lg': selectedPlan == '{{ $membership->id }}', 'border-white/30 hover:border-white/60': selectedPlan != '{{ $membership->id }}'}" 
                            class="box mt-3.5 p-5 transition-all duration-300 cursor-pointer bg-white/10 backdrop-blur-sm rounded-xl border-2">
                            <div class="flex flex-col gap-y-8 rounded-lg border border-dashed" 
                                :class="{'bg-primary/10 border-primary/30': selectedPlan == '{{ $membership->id }}', 'bg-white/10 border-white/20': selectedPlan != '{{ $membership->id }}'}">
                                <div class="relative flex flex-col gap-5 p-5 lg:gap-8 lg:flex-row lg:items-center">
                                    <div class="relative flex flex-col gap-5 lg:gap-8">
                                        <div>
                                            <div class="text-xs uppercase text-white/60">
                                                Membership plan:
                                            </div>
                                            <div class="mt-1.5 text-lg font-medium text-white" :class="{'text-primary-light': selectedPlan == '{{ $membership->id }}'}">
                                                {{ $membership->name }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase text-white/60">
                                                @if($membership->pricing_type == 'plan')
                                                    Total price
                                                @else
                                                    Pricing model
                                                @endif
                                            </div>
                                            <div class="mt-1.5 text-lg font-medium text-white" :class="{'text-primary-light': selectedPlan == '{{ $membership->id }}'}">
                                                @if($membership->pricing_type == 'plan')
                                                    ${{ $membership->price }} USD
                                                @else
                                                    @php
                                                        $carrierTotal = $membership->carrier_price * $membership->max_carrier;
                                                        $driverTotal = $membership->driver_price * $membership->max_drivers;
                                                        $vehicleTotal = $membership->vehicle_price * $membership->max_vehicles;
                                                        $totalPrice = $carrierTotal + $driverTotal + $vehicleTotal;
                                                    @endphp
                                                    ${{ $totalPrice }} USD
                                                    <div class="text-xs text-white/60 mt-1">
                                                        (Users: ${{ $membership->carrier_price }} × {{ $membership->max_carrier }} = ${{ $carrierTotal }} + 
                                                        Drivers: ${{ $membership->driver_price }} × {{ $membership->max_drivers }} = ${{ $driverTotal }} + 
                                                        Vehicles: ${{ $membership->vehicle_price }} × {{ $membership->max_vehicles }} = ${{ $vehicleTotal }})
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="lg:ml-auto">
                                        <div class="flex flex-col gap-3 md:flex-row">
                                            <button type="button" 
                                                @click="selectedPlan = '{{ $membership->id }}'"
                                                :class="{'border-primary bg-primary/20 text-white': selectedPlan == '{{ $membership->id }}', 'border-white/30 bg-white/10 text-white hover:bg-white/20': selectedPlan != '{{ $membership->id }}'}" 
                                                class="px-4 py-2 rounded-md font-medium border transition-all duration-300">
                                                <span x-text="selectedPlan == '{{ $membership->id }}' ? 'Selected Plan' : 'Select Plan'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="px-5 pb-5">
                                    <div class="text-sm text-white/80 mb-4">
                                        {{ $membership->description }}
                                    </div>
                                    
                                    <!-- Limits bar -->
                                    <div class="mt-3.5 flex">
                                        <div class="font-medium text-white">Plan limits</div>
                                        <div class="ml-auto">
                                            <span class="font-medium text-white">{{ $membership->max_carrier + $membership->max_drivers + $membership->max_vehicles }}</span>
                                            <span class="text-white/60">total resources</span>
                                        </div>
                                    </div>
                                    <div class="mt-3.5 flex h-2 bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full border border-primary/50 bg-primary/70 first:rounded-l" 
                                            style="width: {{ ($membership->max_carrier / ($membership->max_carrier + $membership->max_drivers + $membership->max_vehicles)) * 100 }}%">
                                        </div>
                                        <div class="h-full border border-blue-300/50 bg-blue-400/70 first:rounded-l" 
                                            style="width: {{ ($membership->max_drivers / ($membership->max_carrier + $membership->max_drivers + $membership->max_vehicles)) * 100 }}%">
                                        </div>
                                        <div class="h-full border border-white/30 bg-white/30 first:rounded-l last:rounded-r" 
                                            style="width: {{ ($membership->max_vehicles / ($membership->max_carrier + $membership->max_drivers + $membership->max_vehicles)) * 100 }}%">
                                        </div>
                                    </div>
                                    <div class="mt-3.5 flex flex-wrap items-center gap-x-5 gap-y-2">
                                        <div class="flex items-center">
                                            <div class="h-2 w-2 rounded-full bg-primary/70"></div>
                                            <div class="ml-2.5 text-white">{{ $membership->max_carrier }} Users</div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="h-2 w-2 rounded-full bg-blue-400/70"></div>
                                            <div class="ml-2.5 text-white">{{ $membership->max_drivers }} Drivers</div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="h-2 w-2 rounded-full bg-white/50"></div>
                                            <div class="ml-2.5 text-white">{{ $membership->max_vehicles }} Vehicles</div>
                                        </div>
                                    </div>
                                    
                                    @if($membership->pricing_type == 'individual')
                                    <!-- Individual pricing -->
                                    <div class="mt-5 grid grid-cols-3 gap-3">
                                        <div class="p-3 rounded-lg border border-white/20 bg-white/10">
                                            <div class="text-xs uppercase text-white/60">Users</div>
                                            <div class="mt-1 font-medium text-white">${{ $membership->carrier_price }}</div>
                                        </div>
                                        <div class="p-3 rounded-lg border border-white/20 bg-white/10">
                                            <div class="text-xs uppercase text-white/60">Driver</div>
                                            <div class="mt-1 font-medium text-white">${{ $membership->driver_price }}</div>
                                        </div>
                                        <div class="p-3 rounded-lg border border-white/20 bg-white/10">
                                            <div class="text-xs uppercase text-white/60">Vehicle</div>
                                            <div class="mt-1 font-medium text-white">${{ $membership->vehicle_price }}</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        <!-- Mensaje sobre planes personalizados -->
                        <div class="mt-6 p-5 bg-white/10 backdrop-blur-sm rounded-xl border-2 border-primary/30">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-full bg-primary/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-white font-medium">Need a customized plan?</div>
                            </div>
                            <div class="mt-3 text-white/80">
                                If none of these plans fit your needs, select any of them and contact us after registration. We will be happy to create a customized plan specifically for your business.
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-base text-white/70 my-3">
                        Select the plan that best fits your business needs
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Estilos para mejorar la visualización en móvil */
        @media (max-width: 1023px) {
            .mobile-membership-panel {
                height: 100vh;
                overflow-y: auto;
                padding-bottom: 2rem;
            }
            
            /* Asegurar que los planes sean más pequeños en móvil */
            .mobile-plan-card {
                padding: 0.75rem !important;
            }
        }
    </style>
    
    <script>
        // No es necesario inicializar Alpine.js manualmente
        // El framework Laravel ya lo inicializa por nosotros
        
        // Detectar cambios de tamaño de pantalla para Alpine
        window.addEventListener('resize', function() {
            // Disparar un evento Alpine para que se actualice la condición de visualización
            if (window.Alpine) {
                window.dispatchEvent(new CustomEvent('resize-alpine'));
            }
        });
    </script>
</x-guest-layout>
