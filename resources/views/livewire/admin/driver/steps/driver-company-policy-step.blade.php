{{-- resources/views/livewire/admin/driver/steps/step-company-policy.blade.php --}}
<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Company Policies</h3>

    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h4 class="text-md font-medium mb-3">Company Policies Document</h4>
        
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-md font-medium mb-3">Company Policies Document</h4>
            
            <div class="mb-6">
                <div class="mb-3">
                    <a href="{{ asset('storage/documents/company_policy.pdf') }}" target="_blank" class="text-blue-600 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Policy Document
                    </a>
                </div>
                <p class="text-sm text-gray-600">Please review the company policy document before proceeding.</p>
            </div>

            <div class="flex items-center mt-4">
                <input type="checkbox" id="consent_all_policies_attached" wire:model="consent_all_policies_attached" 
                    class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-2">
                <label for="consent_all_policies_attached" class="text-sm font-medium text-gray-700">
                    I agree and consent to all policies attached above.  
                </label>
            </div>
            @error('consent_all_policies_attached')
                <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Sección 1: Controlled Substances & Alcohol Testing Consent -->
    <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
        <h4 class="font-medium text-lg mb-2">Controlled Substances & Alcohol Testing Consent</h4>
        
        <div class="prose prose-sm max-w-none mb-4 text-gray-700">
            <p>I understand that as required by the Federal Motor Carrier Safety Regulations or company policy, all drivers must submit to alcohol and controlled substances testing.</p>
            <p>I consent to all such testing as a condition of my employment. I understand that if I test positive for illegal drugs or alcohol misuse, I will not be eligible for employment with this company.</p>
            <!-- Aquí iría todo el texto de la política -->
        </div>
        
        <div class="flex items-center mt-4">
            <input type="checkbox" id="substance_testing_consent" wire:model="substance_testing_consent" 
                class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-2">
            <label for="substance_testing_consent" class="text-sm font-medium text-gray-700">
                Do you agree and consent to the above?
            </label>
        </div>
        @error('substance_testing_consent')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Sección 2: Authorization -->
    <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
        <h4 class="font-medium text-lg mb-2">Authorization</h4>
        
        <div class="prose prose-sm max-w-none mb-4 text-gray-700">
            <p>I authorize you to make such investigations and inquiries of my personal, employment, financial or medical history and other related matters as may be necessary in arriving at an employment decision.</p>
            <p>I hereby release employers, schools, health care providers and other persons from all liability in responding to inquiries and releasing information in connection with my application.</p>
            <!-- Aquí iría todo el texto de la política -->
        </div>
        
        <div class="flex items-center mt-4">
            <input type="checkbox" id="authorization_consent" wire:model="authorization_consent" 
                class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-2">
            <label for="authorization_consent" class="text-sm font-medium text-gray-700">
                Do you agree and consent to the above?
            </label>
        </div>
        @error('authorization_consent')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Sección 3: FMCSA Drug & Alcohol Clearinghouse -->
    <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
        <h4 class="font-medium text-lg mb-2">General Consent for Limited Queries of the FMCSA Drug & Alcohol Clearinghouse</h4>
        
        <div class="prose prose-sm max-w-none mb-4 text-gray-700">
            <p>I hereby consent to EF Services conducting limited queries of the Federal Motor Carrier Safety Administration (FMCSA) Commercial Driver's License Drug and Alcohol Clearinghouse to determine whether drug or alcohol violation information about me exists in the Clearinghouse.</p>
            <!-- Aquí iría todo el texto de la política -->
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee Name</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $company_name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Commercial Driver's License Number</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $license_number ?? 'Not available' }}</p>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">State of Issuance</label>
            <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $license_state ?? 'Not available' }}</p>
        </div>
        
        <div class="flex items-center mt-4">
            <input type="checkbox" id="fmcsa_clearinghouse_consent" wire:model="fmcsa_clearinghouse_consent" 
                class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-2">
            <label for="fmcsa_clearinghouse_consent" class="text-sm font-medium text-gray-700">
                Do you agree and consent to the above?
            </label>
        </div>
        @error('fmcsa_clearinghouse_consent')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <div>
            <button type="button" wire:click="previous" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Previous
            </button>
        </div>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit"
                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            <button type="button" wire:click="next"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Next
            </button>
        </div>
    </div>
</div>