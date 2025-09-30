<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">FMCSR Requirements</h3>

    <!-- Pregunta 1: Descalificación -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ isDisqualified: @entangle('is_disqualified') }">
            <div class="flex items-center mb-2">                 
                    <div class="flex items-center">
                        <span class="mr-2 text-sm {{ $is_disqualified ? 'text-gray-400' : 'text-gray-700 font-medium' }}">No</span>
                        <label class="inline-flex items-center cursor-pointer">                            
                            <input type="checkbox" wire:model="is_disqualified" x-model="isDisqualified" class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                            </div>
                        </label>
                        <span class="ml-2 text-sm {{ $is_disqualified ? 'text-gray-700 font-medium' : 'text-gray-400' }}">Yes</span>
                    </div>
                <label for="is_disqualified" class="text-sm font-medium ml-5">
                    Under FMCSR 391.15, are you currently disqualified from driving a commercial motor vehicle? [49 CFR
                    391.15]
                </label>
            </div>
            <div x-show="isDisqualified" x-transition class="ml-6 mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional details</label>
                <textarea wire:model="disqualified_details" rows="2"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                    placeholder="Enter details about disqualification..."></textarea>
                @error('disqualified_details')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
                
            </div>
        </div>
    </div>

    <!-- Pregunta 2: Suspensión de licencia -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ isSuspended: @entangle('is_license_suspended') }">
            <div class="flex items-center mb-2">                
                    <div class="flex items-center">
                        <span class="mr-2 text-sm {{ $is_license_suspended ? 'text-gray-400' : 'text-gray-700 font-medium' }}">No</span>
                        <label class="inline-flex items-center cursor-pointer">                            
                            <input type="checkbox" wire:model="is_license_suspended" x-model="isSuspended" class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                            </div>
                        </label>
                        <span class="ml-2 text-sm {{ $is_license_suspended ? 'text-gray-700 font-medium' : 'text-gray-400' }}">Yes</span>
                    </div>
                <label for="is_license_suspended" class="text-sm font-medium ml-5">
                    Has your license, permit, or privilege to drive ever been suspended or revoked for any reason? [49
                    CFR 391.21(b)(9)]
                </label>
            </div>
            <div x-show="isSuspended" x-transition class="ml-6 mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional details</label>
                <textarea wire:model="suspension_details" rows="2"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                    placeholder="Enter details about suspension..."></textarea>
                @error('suspension_details')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Pregunta 3: Denegación de licencia -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ isDenied: @entangle('is_license_denied') }">
            <div class="flex items-center mb-2">
                {{-- <input type="checkbox" id="is_license_denied" wire:model="is_license_denied" x-model="isDenied"
                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"> --}}
                <div class="flex items-center">
                    <span class="mr-2 text-sm {{ $is_license_denied ? 'text-gray-400' : 'text-gray-700 font-medium' }}">No</span>
                    <label class="inline-flex items-center cursor-pointer">                            
                        <input type="checkbox" wire:model="is_license_denied" x-model="isDenied" class="sr-only peer">
                        <div
                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                        </div>
                        </label>
                        <span class="ml-2 text-sm {{ $is_license_suspended ? 'text-gray-700 font-medium' : 'text-gray-400' }}">Yes</span>
                    </div>
                <label for="is_license_denied" class="text-sm font-medium ml-5">
                    Have you ever been denied a license, permit, or privilege to operate a motor vehicle? [49 CFR
                    391.21(b)(9)]
                </label>
            </div>
            <div x-show="isDenied" x-transition class="ml-6 mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional details</label>
                <textarea wire:model="denial_details" rows="2"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter details about denial..."></textarea>
                @error('denial_details')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Pregunta 4: Prueba de drogas positiva -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ hasPositiveTest: @entangle('has_positive_drug_test') }">
            <div class="flex items-center mb-2">
                    <div class="flex items-center">
                        <span class="mr-2 text-sm {{ $has_positive_drug_test ? 'text-gray-400' : 'text-gray-700 font-medium' }}">No</span>
                        <label class="inline-flex items-center cursor-pointer">                            
                            <input type="checkbox" wire:model="has_positive_drug_test" x-model="hasPositiveTest" class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                            </div>
                        </label>
                        <span class="ml-2 text-sm {{ $has_positive_drug_test ? 'text-gray-700 font-medium' : 'text-gray-400' }}">Yes</span>
                    </div>                    
                <label for="has_positive_drug_test" class="text-sm font-medium ml-5">
                    Within the past two years, have you tested positive, or refused to test, on a pre-employment drug or
                    alcohol test by an employer to whom you applied, but did not obtain, safety-sensitive transportation
                    work covered by DOT agency drug and alcohol testing rules? [49 CFR 40.25(j)]
                </label>
            </div>
            <div x-show="hasPositiveTest" x-transition class="ml-6 mt-2">
                <p class="mb-4 text-sm text-gray-600">If yes, please provide the name of the Substance Abuse
                    Professional (SAP) that evaluated you below, along with the name of the agency that performed your
                    return to duty test.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Substance Abuse Professional</label>
                        <input type="text" wire:model="substance_abuse_professional"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter name">
                        @error('substance_abuse_professional')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" wire:model="sap_phone"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter phone number">
                        @error('sap_phone')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Return to Duty Test Agency</label>
                    <input type="text" wire:model="return_duty_agency"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="Enter agency name">
                    @error('return_duty_agency')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-2 p-3 bg-gray-50 rounded-md">
                    <p class="text-xs text-gray-600 italic mb-2">*If you answered yes to the above question please agree
                        to Consent for Release of Information regarding Previous Pre-Employment Controlled Substances or
                        Alcohol Testing form.*</p>
                    <div class="flex items-center">
                        <input type="checkbox" id="consent_to_release" wire:model="consent_to_release"
                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                        <label for="consent_to_release" class="text-sm font-medium">
                            Do you agree and consent to the above?
                        </label>
                    </div>
                    @error('consent_to_release')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Question 5: On-Duty Offenses -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ hasDutyOffenses: @entangle('has_duty_offenses') }">
            <div class="flex items-center mb-2">
                <input type="checkbox" id="has_duty_offenses" wire:model="has_duty_offenses"
                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="has_duty_offenses" class="text-sm font-medium">
                    In the past three (3) years, have you ever been convicted of any of the following offenses committed
                    during on-duty time [49 CFR 391.15 and 49 CFR 395.2]?
                </label>
            </div>
            <div x-show="hasDutyOffenses" x-transition class="ml-6 mt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of most recent conviction
                            identified above</label>
                        <input type="text" value="{{ $recent_conviction_date }}" onchange="@this.set('recent_conviction_date', this.value)" class="driver-datepicker w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="MM/DD/YYYY">
                        @error('recent_conviction_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional
                        details</label>
                    <textarea wire:model="offense_details" rows="2"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="Enter details about convictions..."></textarea>
                    @error('offense_details')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Request for Check of Driving Record -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="font-medium text-lg mb-3">Request for Check of Driving Record</h4>
        <p class="text-sm text-gray-600 mb-4">
            I understand that according to the Federal Motor Carrier Safety Regulations, my previous driving record will
            be investigated and that my employment is subject to satisfactory reports from previous employers and other
            sources.
        </p>
        <div class="flex items-center">
            <input type="checkbox" id="consent_driving_record" wire:model="consent_driving_record"
                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
            <label for="consent_driving_record" class="text-sm font-medium">
                Do you agree and consent to the above?
            </label>
        </div>
        @error('consent_driving_record')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Navigation Buttons -->
    <div class="mt-8 px-5 py-5 border-t border-slate-200/60 dark:border-darkmode-400">
        <div class="flex flex-col sm:flex-row justify-between gap-4">
            <div class="w-full sm:w-auto">
                <x-base.button type="button" wire:click="previous" class="w-full sm:w-44" variant="secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg> Previous
                </x-base.button>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <x-base.button type="button" wire:click="saveAndExit" class="w-full sm:w-44 text-white"
                    variant="warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" />
                    </svg>
                    Save & Exit
                </x-base.button>
                <x-base.button type="button" wire:click="next" class="w-full sm:w-44" variant="primary">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </x-base.button>
            </div>
        </div>
    </div>
</div>
