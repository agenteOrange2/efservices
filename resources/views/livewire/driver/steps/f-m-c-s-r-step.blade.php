<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">FMCSR Requirements</h3>

    <!-- Question 1: Disqualification -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ isDisqualified: @entangle('is_disqualified') }">
            <div class="flex items-center mb-2">
                <input type="checkbox" id="is_disqualified" wire:model="is_disqualified" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="is_disqualified" class="text-sm font-medium">
                    Under FMCSR 391.15, are you currently disqualified from driving a commercial motor vehicle? [49 CFR 391.15]
                </label>
            </div>
            <div x-show="isDisqualified" x-transition class="ml-6 mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional details</label>
                <textarea wire:model="disqualified_details" rows="2" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter details about disqualification..."></textarea>
                @error('disqualified_details')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Question 2: License Suspension -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ isSuspended: @entangle('is_license_suspended') }">
            <div class="flex items-center mb-2">
                <input type="checkbox" id="is_license_suspended" wire:model="is_license_suspended" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="is_license_suspended" class="text-sm font-medium">
                    Has your license, permit, or privilege to drive ever been suspended or revoked for any reason? [49 CFR 391.21(b)(9)]
                </label>
            </div>
            <div x-show="isSuspended" x-transition class="ml-6 mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional details</label>
                <textarea wire:model="suspension_details" rows="2" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter details about suspension..."></textarea>
                @error('suspension_details')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Question 3: License Denial -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ isDenied: @entangle('is_license_denied') }">
            <div class="flex items-center mb-2">
                <input type="checkbox" id="is_license_denied" wire:model="is_license_denied" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="is_license_denied" class="text-sm font-medium">
                    Have you ever been denied a license, permit, or privilege to operate a motor vehicle? [49 CFR 391.21(b)(9)]
                </label>
            </div>
            <div x-show="isDenied" x-transition class="ml-6 mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional details</label>
                <textarea wire:model="denial_details" rows="2" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter details about denial..."></textarea>
                @error('denial_details')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Question 4: Positive Drug Test -->
    <div class="mb-6 border-b pb-4">
        <div x-data="{ hasPositiveTest: @entangle('has_positive_drug_test') }">
            <div class="flex items-center mb-2">
                <input type="checkbox" id="has_positive_drug_test" wire:model="has_positive_drug_test" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="has_positive_drug_test" class="text-sm font-medium">
                    Within the past two years, have you tested positive, or refused to test, on a pre-employment drug or alcohol test by an employer to whom you applied, but did not obtain, safety-sensitive transportation work covered by DOT agency drug and alcohol testing rules? [49 CFR 40.25(j)]
                </label>
            </div>
            <div x-show="hasPositiveTest" x-transition class="ml-6 mt-2">
                <p class="mb-4 text-sm text-gray-600">If yes, please provide the name of the Substance Abuse Professional (SAP) that evaluated you below, along with the name of the agency that performed your return to duty test.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Substance Abuse Professional</label>
                        <input type="text" wire:model="substance_abuse_professional" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter name">
                        @error('substance_abuse_professional')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" wire:model="sap_phone" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter phone number">
                        @error('sap_phone')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Return to Duty Test Agency</label>
                    <input type="text" wire:model="return_duty_agency" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter agency name">
                    @error('return_duty_agency')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="mb-2 p-3 bg-gray-50 rounded-md">
                    <p class="text-xs text-gray-600 italic mb-2">*If you answered yes to the above question please agree to Consent for Release of Information regarding Previous Pre-Employment Controlled Substances or Alcohol Testing form.*</p>
                    <div class="flex items-center">
                        <input type="checkbox" id="consent_to_release" wire:model="consent_to_release" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
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
                <input type="checkbox" id="has_duty_offenses" wire:model="has_duty_offenses" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="has_duty_offenses" class="text-sm font-medium">
                    In the past three (3) years, have you ever been convicted of any of the following offenses committed during on-duty time [49 CFR 391.15 and 49 CFR 395.2]?
                </label>
            </div>
            <div x-show="hasDutyOffenses" x-transition class="ml-6 mt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of most recent conviction identified above</label>
                        <input type="date" wire:model="recent_conviction_date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('recent_conviction_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Please provide additional details</label>
                    <textarea wire:model="offense_details" rows="2" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Enter details about convictions..."></textarea>
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
            I understand that according to the Federal Motor Carrier Safety Regulations, my previous driving record will be investigated and that my employment is subject to satisfactory reports from previous employers and other sources.
        </p>
        <div class="flex items-center">
            <input type="checkbox" id="consent_driving_record" wire:model="consent_driving_record" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
            <label for="consent_driving_record" class="text-sm font-medium">
                Do you agree and consent to the above?
            </label>
        </div>
        @error('consent_driving_record')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <button type="button" wire:click="previous" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
            Previous
        </button>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            <button type="button" wire:click="next" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <span wire:loading.remove wire:target="next">Next</span>
                <span wire:loading wire:target="next" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </div>
    </div>
</div>