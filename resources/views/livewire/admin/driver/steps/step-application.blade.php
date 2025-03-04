<div>
    <h2 class="text-xl font-bold mb-4">Application Details</h2>

    <!-- Position Applied For -->
    <div class="mb-4">
        <label class="block mb-1">Position Applied For *</label>
        <select x-model="applying_position" wire:model="applying_position"
            class="w-full px-3 py-2 border rounded">
            <option value="">Select Position</option>
            @foreach ($driverPositions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('applying_position')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror

        <div x-show="applying_position === 'other'" x-transition class="mt-2">
            <label class="block mb-1">Specify Other Position *</label>
            <input type="text" wire:model="applying_position_other"
                class="w-full px-3 py-2 border rounded">
            @error('applying_position_other')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Location Preference -->
    <div class="mb-4">
        <label class="block mb-1">Location Preference *</label>
        <select wire:model="applying_location" class="w-full px-3 py-2 border rounded">
            <option value="">Select State</option>
            @foreach ($usStates as $code => $name)
                <option value="{{ $code }}">{{ $name }}</option>
            @endforeach
        </select>
        @error('applying_location')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Eligibility Information -->
    <div class="p-4 border rounded bg-gray-50 mb-6">
        <h3 class="text-lg font-medium mb-3">Eligibility Information</h3>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" wire:model="eligible_to_work" class="mr-2">
                <span>Eligible to work in the United States *</span>
            </label>
            @error('eligible_to_work')
                <span class="text-red-500 text-sm block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" wire:model="can_speak_english" class="mr-2">
                <span>Can speak and understand English</span>
            </label>
        </div>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" x-model="has_twic_card" wire:model="has_twic_card" class="mr-2">
                <span>I have a TWIC Card</span>
            </label>

            <div x-show="has_twic_card" x-transition class="mt-2">
                <label class="block mb-1">TWIC Card Expiration Date *</label>
                <input type="date" wire:model="twic_expiration_date"
                    class="w-full px-3 py-2 border rounded">
                @error('twic_expiration_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Expected Pay Rate -->
    <div class="mb-4">
        <label class="block mb-1">Expected Pay Rate</label>
        <input type="text" wire:model="expected_pay" class="w-full px-3 py-2 border rounded"
            placeholder="e.g. $25/hour">
    </div>

    <!-- Referral Source -->
    <div class="mb-6">
        <label class="block mb-1">Referral Source *</label>
        <select x-model="how_did_hear" wire:model="how_did_hear" class="w-full px-3 py-2 border rounded">
            <option value="">Select Source</option>
            @foreach ($referralSources as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('how_did_hear')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror

        <div x-show="how_did_hear === 'employee_referral'" x-transition class="mt-2">
            <label class="block mb-1">Employee Name *</label>
            <input type="text" wire:model="referral_employee_name"
                class="w-full px-3 py-2 border rounded">
            @error('referral_employee_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div x-show="how_did_hear === 'other'" x-transition class="mt-2">
            <label class="block mb-1">Specify Other Source *</label>
            <input type="text" wire:model="how_did_hear_other" class="w-full px-3 py-2 border rounded">
            @error('how_did_hear_other')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Work History -->
    <div class="mb-6">
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" wire:model="has_work_history" class="mr-2">
                <span>Work History with this company</span>
            </label>
        </div>

        <div x-show="$wire.has_work_history" x-transition>
            @foreach ($work_histories as $index => $history)
                <div class="p-4 border rounded bg-gray-50 mb-4">
                    <div class="flex justify-between mb-2">
                        <h4 class="font-medium">Work History #{{ $index + 1 }}</h4>
                        @if (count($work_histories) > 1)
                            <button type="button" wire:click="removeWorkHistory({{ $index }})"
                                class="text-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-1">Previous Company *</label>
                            <input type="text"
                                wire:model.defer="work_histories.{{ $index }}.previous_company"
                                class="w-full px-3 py-2 border rounded">
                            @error('work_histories.' . $index . '.previous_company')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Position *</label>
                            <input type="text"
                                wire:model.defer="work_histories.{{ $index }}.position"
                                class="w-full px-3 py-2 border rounded">
                            @error('work_histories.' . $index . '.position')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-1">Start Date *</label>
                            <input type="date"
                                wire:model.defer="work_histories.{{ $index }}.start_date"
                                class="w-full px-3 py-2 border rounded">
                            @error('work_histories.' . $index . '.start_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">End Date *</label>
                            <input type="date"
                                wire:model.defer="work_histories.{{ $index }}.end_date"
                                class="w-full px-3 py-2 border rounded">
                            @error('work_histories.' . $index . '.end_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Location *</label>
                        <input type="text" wire:model.defer="work_histories.{{ $index }}.location"
                            class="w-full px-3 py-2 border rounded">
                        @error('work_histories.' . $index . '.location')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Reason for Leaving</label>
                        <input type="text"
                            wire:model.defer="work_histories.{{ $index }}.reason_for_leaving"
                            class="w-full px-3 py-2 border rounded">
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Reference Contact</label>
                        <input type="text"
                            wire:model.defer="work_histories.{{ $index }}.reference_contact"
                            class="w-full px-3 py-2 border rounded" placeholder="Name and phone number">
                    </div>
                </div>
            @endforeach

            <button type="button" wire:click="addWorkHistory"
                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                Add Work History
            </button>
        </div>
    </div>
</div>