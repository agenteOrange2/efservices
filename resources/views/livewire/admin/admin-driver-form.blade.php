<div>
    <!-- Tab Navigation -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="switchTab('personal')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $currentTab === 'personal' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                Personal Info
            </button>
            <button wire:click="switchTab('address')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $currentTab === 'address' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                Address
            </button>
            <button wire:click="switchTab('application')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $currentTab === 'application' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                Application Details
            </button>
        </nav>
    </div>

    <!-- Personal Info Tab -->
    @if($currentTab === 'personal')
    <div class="space-y-6">
        <!-- Profile Photo Upload -->
        <div class="flex flex-col items-center space-y-4">
            <div class="relative">
                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="Profile Photo" class="w-32 h-32 rounded-full object-cover border-4 border-slate-200">
                @elseif($photo_preview_url)
                    <img src="{{ $photo_preview_url }}" alt="Profile Photo" class="w-32 h-32 rounded-full object-cover border-4 border-slate-200">
                @else
                    <div class="w-32 h-32 rounded-full bg-slate-100 border-4 border-slate-200 flex items-center justify-center">
                        <x-base.lucide class="w-12 h-12 text-slate-400" icon="user" />
                    </div>
                @endif
                <label for="photo" class="absolute bottom-0 right-0 bg-primary text-white rounded-full p-2 cursor-pointer hover:bg-primary/90">
                    <x-base.lucide class="w-4 h-4" icon="camera" />
                </label>
                <input type="file" id="photo" wire:model="photo" accept="image/*" class="hidden">
            </div>
            @error('photo')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
            <div class="text-xs text-slate-500/80 text-center">
                Upload a clear and recent profile photo. Large images will be automatically optimized to reduce file size.
            </div>
        </div>

        <!-- Personal Information Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First Name -->
            <div>
                <x-base.form-label for="first_name">First Name *</x-base.form-label>
                <x-base.form-input id="first_name" type="text" wire:model="first_name" placeholder="Enter first name" class="@error('first_name') border-red-500 @enderror" />
                @error('first_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Middle Name -->
            <div>
                <x-base.form-label for="middle_name">Middle Name</x-base.form-label>
                <x-base.form-input id="middle_name" type="text" wire:model="middle_name" placeholder="Enter middle name" class="@error('middle_name') border-red-500 @enderror" />
                @error('middle_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Last Name -->
            <div>
                <x-base.form-label for="last_name">Last Name *</x-base.form-label>
                <x-base.form-input id="last_name" type="text" wire:model="last_name" placeholder="Enter last name" class="@error('last_name') border-red-500 @enderror" />
                @error('last_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <x-base.form-label for="email">Email *</x-base.form-label>
                <x-base.form-input id="email" type="email" wire:model="email" placeholder="Enter email address" class="@error('email') border-red-500 @enderror" />
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <x-base.form-label for="phone">Phone *</x-base.form-label>
                <x-base.form-input id="phone" type="tel" wire:model="phone" placeholder="Enter phone number" class="@error('phone') border-red-500 @enderror" />
                @error('phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Date of Birth -->
            <div>
                <x-base.form-label for="date_of_birth">Date of Birth *</x-base.form-label>
                <x-base.litepicker 
                    id="date_of_birth"
                    name="date_of_birth"
                    wire:model="date_of_birth" 
                    placeholder="MM/DD/YYYY" 
                    class="@error('date_of_birth') border-red-500 @enderror"
                />
                @error('date_of_birth')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <x-base.form-label for="password">Password *</x-base.form-label>
                <x-base.form-input id="password" type="password" wire:model="password" placeholder="Enter password" class="@error('password') border-red-500 @enderror" />
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <x-base.form-label for="password_confirmation">Confirm Password *</x-base.form-label>
                <x-base.form-input id="password_confirmation" type="password" wire:model="password_confirmation" placeholder="Confirm password" class="@error('password_confirmation') border-red-500 @enderror" />
                @error('password_confirmation')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    @endif

    <!-- Address Tab -->
    @if($currentTab === 'address')
    <div class="space-y-6">
        <!-- Current Address Section -->
        <div class="bg-slate-50 p-6 rounded-lg">
            <h3 class="text-lg font-medium text-slate-900 mb-4">Current Address</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Address Line 1 -->
                <div class="md:col-span-2">
                    <x-base.form-label for="current_address_line1">Address Line 1 *</x-base.form-label>
                    <x-base.form-input id="current_address_line1" type="text" wire:model="current_address_line1" placeholder="Enter street address" class="@error('current_address_line1') border-red-500 @enderror" />
                    @error('current_address_line1')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Address Line 2 -->
                <div class="md:col-span-2">
                    <x-base.form-label for="current_address_line2">Address Line 2</x-base.form-label>
                    <x-base.form-input id="current_address_line2" type="text" wire:model="current_address_line2" placeholder="Apartment, suite, etc." class="@error('current_address_line2') border-red-500 @enderror" />
                    @error('current_address_line2')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <x-base.form-label for="current_city">City *</x-base.form-label>
                    <x-base.form-input id="current_city" type="text" wire:model="current_city" placeholder="Enter city" class="@error('current_city') border-red-500 @enderror" />
                    @error('current_city')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- State -->
                <div>
                    <x-base.form-label for="current_state">State *</x-base.form-label>
                    <x-base.form-select id="current_state" wire:model="current_state" class="@error('current_state') border-red-500 @enderror">
                        <option value="">Select State</option>
                        @foreach(\App\Helpers\Constants::usStates() as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </x-base.form-select>
                    @error('current_state')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- ZIP Code -->
                <div>
                    <x-base.form-label for="current_zip_code">ZIP Code *</x-base.form-label>
                    <x-base.form-input id="current_zip_code" type="text" wire:model="current_zip_code" placeholder="Enter ZIP code" class="@error('current_zip_code') border-red-500 @enderror" />
                    @error('current_zip_code')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- From Date -->
                <div>
                    <x-base.form-label for="current_from_date">From Date *</x-base.form-label>
                    <x-base.litepicker 
                        id="current_from_date"
                        name="current_from_date"
                        wire:model="current_from_date" 
                        placeholder="MM/DD/YYYY"
                        class="@error('current_from_date') border-red-500 @enderror"
                    />
                    @error('current_from_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- 3 Years Checkbox -->
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="lived_three_years" class="rounded border-slate-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-slate-700">I have lived at this address for 3 years or more</span>
                </label>
            </div>
        </div>

        <!-- Previous Addresses Section -->
        @if(!$lived_three_years)
        <div class="bg-slate-50 p-6 rounded-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-slate-900">Previous Addresses</h3>
                <x-base.button type="button" wire:click="addPreviousAddress" variant="outline-primary" size="sm">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Address
                </x-base.button>
            </div>

            @foreach($previous_addresses as $index => $address)
            <div class="border border-slate-200 rounded-lg p-4 mb-4 bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium text-slate-700">Previous Address {{ $index + 1 }}</h4>
                    <x-base.button type="button" wire:click="removePreviousAddress({{ $index }})" variant="outline-danger" size="sm">
                        <x-base.lucide class="w-4 h-4" icon="trash-2" />
                    </x-base.button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Address Line 1 -->
                    <div class="md:col-span-2">
                        <x-base.form-label for="previous_address_line1_{{ $index }}">Address Line 1 *</x-base.form-label>
                        <x-base.form-input id="previous_address_line1_{{ $index }}" type="text" wire:model="previous_addresses.{{ $index }}.address_line1" placeholder="Enter street address" class="@error('previous_addresses.'.$index.'.address_line1') border-red-500 @enderror" />
                        @error('previous_addresses.'.$index.'.address_line1')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Address Line 2 -->
                    <div class="md:col-span-2">
                        <x-base.form-label for="previous_address_line2_{{ $index }}">Address Line 2</x-base.form-label>
                        <x-base.form-input id="previous_address_line2_{{ $index }}" type="text" wire:model="previous_addresses.{{ $index }}.address_line2" placeholder="Apartment, suite, etc." />
                    </div>

                    <!-- City -->
                    <div>
                        <x-base.form-label for="previous_city_{{ $index }}">City *</x-base.form-label>
                        <x-base.form-input id="previous_city_{{ $index }}" type="text" wire:model="previous_addresses.{{ $index }}.city" placeholder="Enter city" class="@error('previous_addresses.'.$index.'.city') border-red-500 @enderror" />
                        @error('previous_addresses.'.$index.'.city')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- State -->
                    <div>
                        <x-base.form-label for="previous_state_{{ $index }}">State *</x-base.form-label>
                        <x-base.form-select id="previous_state_{{ $index }}" wire:model="previous_addresses.{{ $index }}.state" class="@error('previous_addresses.'.$index.'.state') border-red-500 @enderror">
                            <option value="">Select State</option>
                            @foreach(\App\Helpers\Constants::usStates() as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </x-base.form-select>
                        @error('previous_addresses.'.$index.'.state')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- ZIP Code -->
                    <div>
                        <x-base.form-label for="previous_zip_code_{{ $index }}">ZIP Code *</x-base.form-label>
                        <x-base.form-input id="previous_zip_code_{{ $index }}" type="text" wire:model="previous_addresses.{{ $index }}.zip_code" placeholder="Enter ZIP code" class="@error('previous_addresses.'.$index.'.zip_code') border-red-500 @enderror" />
                        @error('previous_addresses.'.$index.'.zip_code')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- From Date -->
                    <div>
                        <x-base.form-label for="previous_from_date_{{ $index }}">From Date *</x-base.form-label>
                        <x-base.litepicker 
                            id="previous_from_date_{{ $index }}"
                            name="previous_from_date_{{ $index }}"
                            wire:model="previous_addresses.{{ $index }}.from_date" 
                            placeholder="MM/DD/YYYY"
                            class="@error('previous_addresses.'.$index.'.from_date') border-red-500 @enderror"
                            />
                        @error('previous_addresses.'.$index.'.from_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- To Date -->
                    <div>
                        <x-base.form-label for="previous_to_date_{{ $index }}">To Date *</x-base.form-label>
                        <x-base.litepicker 
                            id="previous_to_date_{{ $index }}"
                            name="previous_to_date_{{ $index }}"
                            wire:model="previous_addresses.{{ $index }}.to_date" 
                            placeholder="MM/DD/YYYY"
                            class="@error('previous_addresses.'.$index.'.to_date') border-red-500 @enderror"
                        />
                        @error('previous_addresses.'.$index.'.to_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <!-- Application Details Tab -->
    @if($currentTab === 'application')
    <div class="space-y-6">
        <!-- Position Applied For -->
        <div>
            <x-base.form-label for="applying_position">Position Applied For *</x-base.form-label>
            <x-base.form-select id="applying_position" wire:model="applying_position" class="@error('applying_position') border-red-500 @enderror">
                <option value="">Select Position</option>
                <option value="company_driver">Company Driver</option>
                <option value="owner_operator">Owner Operator</option>
                <option value="third_party_company_driver">Third Party Company Driver</option>
                <option value="other">Other</option>
            </x-base.form-select>
            @error('applying_position')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Other Position Field -->
        @if($applying_position === 'other')
        <div>
            <x-base.form-label for="applying_position_other">Specify Other Position *</x-base.form-label>
            <x-base.form-input id="applying_position_other" type="text" wire:model="applying_position_other" placeholder="Enter position" class="@error('applying_position_other') border-red-500 @enderror" />
            @error('applying_position_other')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        @endif

        <!-- Owner Operator Information -->
        @if($applying_position === 'owner_operator')
        <div class="bg-slate-50 p-6 rounded-lg">
            <h3 class="text-lg font-medium text-slate-900 mb-4">Owner Operator Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-base.form-label for="owner_name">Owner Name *</x-base.form-label>
                    <x-base.form-input id="owner_name" type="text" wire:model="owner_name" placeholder="Enter owner name" class="@error('owner_name') border-red-500 @enderror" />
                    @error('owner_name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <x-base.form-label for="owner_phone">Owner Phone *</x-base.form-label>
                    <x-base.form-input id="owner_phone" type="tel" wire:model="owner_phone" placeholder="Enter owner phone" class="@error('owner_phone') border-red-500 @enderror" />
                    @error('owner_phone')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <x-base.form-label for="owner_email">Owner Email *</x-base.form-label>
                    <x-base.form-input id="owner_email" type="email" wire:model="owner_email" placeholder="Enter owner email" class="@error('owner_email') border-red-500 @enderror" />
                    @error('owner_email')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        @endif

        <!-- Basic Application Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Location Preference -->
            <div>
                <x-base.form-label for="location_preference">Location Preference</x-base.form-label>
                <x-base.form-input id="location_preference" type="text" wire:model="location_preference" placeholder="Enter preferred location" class="@error('location_preference') border-red-500 @enderror" />
                @error('location_preference')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Eligibility Information -->
            <div>
                <x-base.form-label for="eligibility_information">Eligibility Information</x-base.form-label>
                <x-base.form-input id="eligibility_information" type="text" wire:model="eligibility_information" placeholder="Enter eligibility info" class="@error('eligibility_information') border-red-500 @enderror" />
                @error('eligibility_information')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Expected Pay Rate -->
            <div>
                <x-base.form-label for="expected_pay_rate">Expected Pay Rate</x-base.form-label>
                <x-base.form-input id="expected_pay_rate" type="text" wire:model="expected_pay_rate" placeholder="Enter expected pay rate" class="@error('expected_pay_rate') border-red-500 @enderror" />
                @error('expected_pay_rate')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Referral Source -->
            <div>
                <x-base.form-label for="referral_source">Referral Source</x-base.form-label>
                <x-base.form-input id="referral_source" type="text" wire:model="referral_source" placeholder="How did you hear about us?" class="@error('referral_source') border-red-500 @enderror" />
                @error('referral_source')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Work History Section -->
        <div class="bg-slate-50 p-6 rounded-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-slate-900">Work History</h3>
                <x-base.button type="button" wire:click="addWorkHistory" variant="outline-primary" size="sm">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Work History
                </x-base.button>
            </div>

            @foreach($work_history as $index => $work)
            <div class="border border-slate-200 rounded-lg p-4 mb-4 bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium text-slate-700">Employment {{ $index + 1 }}</h4>
                    @if(count($work_history) > 1)
                        <x-base.button type="button" wire:click="removeWorkHistory({{ $index }})" variant="outline-danger" size="sm">
                            <x-base.lucide class="w-4 h-4" icon="trash-2" />
                        </x-base.button>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Company Name -->
                    <div>
                        <x-base.form-label for="work_company_{{ $index }}">Company Name *</x-base.form-label>
                        <x-base.form-input id="work_company_{{ $index }}" type="text" wire:model="work_history.{{ $index }}.company_name" placeholder="Enter company name" class="@error('work_history.'.$index.'.company_name') border-red-500 @enderror" />
                        @error('work_history.'.$index.'.company_name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Position -->
                    <div>
                        <x-base.form-label for="work_position_{{ $index }}">Position *</x-base.form-label>
                        <x-base.form-input id="work_position_{{ $index }}" type="text" wire:model="work_history.{{ $index }}.position" placeholder="Enter position" class="@error('work_history.'.$index.'.position') border-red-500 @enderror" />
                        @error('work_history.'.$index.'.position')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- From Date -->
                    <div>
                        <x-base.form-label for="work_from_{{ $index }}">From Date *</x-base.form-label>
                        <x-base.litepicker 
                            id="work_from_{{ $index }}"
                            name="work_from_{{ $index }}"
                            wire:model="work_history.{{ $index }}.from_date" 
                            placeholder="MM/DD/YYYY"
                            class="@error('work_history.'.$index.'.from_date') border-red-500 @enderror"
                        />
                        @error('work_history.'.$index.'.from_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- To Date -->
                    <div>
                        <x-base.form-label for="work_to_{{ $index }}">To Date</x-base.form-label>
                        <x-base.litepicker 
                            id="work_to_{{ $index }}"
                            name="work_to_{{ $index }}"
                            wire:model="work_history.{{ $index }}.to_date" 
                            placeholder="MM/DD/YYYY"
                            class="@error('work_history.'.$index.'.to_date') border-red-500 @enderror"
                        />
                        @error('work_history.'.$index.'.to_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-slate-500 mt-1">Leave blank if current job</p>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <x-base.form-label for="work_description_{{ $index }}">Job Description</x-base.form-label>
                        <x-base.form-textarea id="work_description_{{ $index }}" wire:model="work_history.{{ $index }}.description" placeholder="Describe your responsibilities" rows="3" class="@error('work_history.'.$index.'.description') border-red-500 @enderror"></x-base.form-textarea>
                        @error('work_history.'.$index.'.description')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-slate-200">
        <x-base.button type="button" variant="outline-secondary" wire:click="$dispatch('closeModal')">
            Cancel
        </x-base.button>
        <x-base.button type="button" wire:click="save" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Save Driver</span>
            <span wire:loading>Saving...</span>
        </x-base.button>
    </div>

    <!-- Auto-save feedback -->
    <div id="auto-save-feedback" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg hidden z-50">
        <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Data saved automatically
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to format date input as MM/DD/YYYY
    function formatDateInput(input) {
        let value = input.value.replace(/\D/g, ''); // Remove non-digits
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '/' + value.substring(5, 9);
        }
        
        input.value = value;
        
        // Trigger Livewire update
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Function to validate date format
    function isValidDate(dateString) {
        const regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/(19|20)\d{2}$/;
        if (!regex.test(dateString)) return false;
        
        const [month, day, year] = dateString.split('/').map(Number);
        const date = new Date(year, month - 1, day);
        
        return date.getFullYear() === year &&
               date.getMonth() === month - 1 &&
               date.getDate() === day;
    }
    
    // Apply date formatting to existing date inputs
    function applyDateFormatting() {
        const dateInputs = document.querySelectorAll('input[placeholder="MM/DD/YYYY"]');
        
        dateInputs.forEach(input => {
            // Remove existing event listeners to avoid duplicates
            if (input._dateFormatHandler) {
                input.removeEventListener('input', input._dateFormatHandler);
            }
            if (input._dateValidateHandler) {
                input.removeEventListener('blur', input._dateValidateHandler);
            }
            
            // Add input event listener for formatting
            input._dateFormatHandler = function(e) {
                formatDateInput(e.target);
            };
            input.addEventListener('input', input._dateFormatHandler);
            
            // Add blur event listener for validation
            input._dateValidateHandler = function(e) {
                const value = e.target.value;
                if (value && !isValidDate(value)) {
                    e.target.classList.add('border-red-500');
                    // Show error message if not already present
                    let errorMsg = e.target.parentNode.querySelector('.date-error-msg');
                    if (!errorMsg) {
                        errorMsg = document.createElement('span');
                        errorMsg.className = 'text-red-500 text-sm date-error-msg';
                        errorMsg.textContent = 'Please enter a valid date in MM/DD/YYYY format';
                        e.target.parentNode.appendChild(errorMsg);
                    }
                } else {
                    e.target.classList.remove('border-red-500');
                    // Remove error message
                    const errorMsg = e.target.parentNode.querySelector('.date-error-msg');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            };
            input.addEventListener('blur', input._dateValidateHandler);
        });
    }
    
    // Apply formatting on page load
    applyDateFormatting();
    
    // Reapply formatting when Livewire updates the DOM
    document.addEventListener('livewire:navigated', applyDateFormatting);
    document.addEventListener('livewire:load', applyDateFormatting);
    
    // Listen for Livewire updates and reapply formatting
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('message.processed', (message, component) => {
            setTimeout(applyDateFormatting, 100);
        });
    }
    
    // Show auto-save feedback
    function showAutoSaveFeedback() {
        const feedback = document.getElementById('auto-save-feedback');
        if (feedback) {
            feedback.classList.remove('hidden');
            setTimeout(() => {
                feedback.classList.add('hidden');
            }, 3000);
        }
    }
    
    // Listen for auto-save events from Livewire
    window.addEventListener('auto-save-success', function() {
        showAutoSaveFeedback();
    });
    
    // Listen for Livewire events that might indicate auto-save
    if (typeof Livewire !== 'undefined') {
        Livewire.on('autoSaveSuccess', function() {
            showAutoSaveFeedback();
        });
    }
});
</script>
