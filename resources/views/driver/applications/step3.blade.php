@extends('../themes/' . $activeTheme)
@section('title', 'New Driver Application - Step 3')

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12 sm:col-span-10 sm:col-start-2">
        <div class="mb-6">
            <h2 class="text-2xl font-medium">Step 3: Employment Details</h2>
            <div class="mt-2 text-slate-500">
                Please provide information about the position you are applying for and other relevant details.
            </div>
        </div>

        <div class="box box--stacked flex flex-col">
            <form action="{{ route('admin.carrier.user_drivers.application.step3.store', [
                'carrier' => $carrier,
                'application' => $application
            ]) }}" method="POST">
                @csrf

                <div class="p-7">
                    <!-- Position and Location -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Position Applying For</label>
                            <x-base.form-input 
                                name="applying_position" 
                                value="{{ old('applying_position', $details?->applying_position) }}"
                                required />
                            @error('applying_position')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Preferred Location</label>
                            <x-base.form-input 
                                name="applying_location" 
                                value="{{ old('applying_location', $details?->applying_location) }}"
                                required />
                            @error('applying_location')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Work Eligibility and English -->
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label block mb-2">Are you eligible to work in the United States?</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" 
                                           class="form-radio"
                                           name="eligible_to_work" 
                                           value="1"
                                           {{ old('eligible_to_work', $details?->eligible_to_work) ? 'checked' : '' }}
                                           required>
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" 
                                           class="form-radio"
                                           name="eligible_to_work" 
                                           value="0"
                                           {{ old('eligible_to_work', $details?->eligible_to_work) === false ? 'checked' : '' }}
                                           required>
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                            @error('eligible_to_work')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label block mb-2">Can you speak and understand English?</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" 
                                           class="form-radio"
                                           name="can_speak_english" 
                                           value="1"
                                           {{ old('can_speak_english', $details?->can_speak_english) ? 'checked' : '' }}
                                           required>
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" 
                                           class="form-radio"
                                           name="can_speak_english" 
                                           value="0"
                                           {{ old('can_speak_english', $details?->can_speak_english) === false ? 'checked' : '' }}
                                           required>
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                            @error('can_speak_english')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- TWIC Card -->
                    <div class="mt-4">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" 
                                   id="has_twic_card"
                                   name="has_twic_card"
                                   value="1"
                                   class="form-checkbox"
                                   {{ old('has_twic_card', $details?->has_twic_card) ? 'checked' : '' }}>
                            <label class="form-label ml-2" for="has_twic_card">
                                Do you have a TWIC Card?
                            </label>
                        </div>
                        
                        <div id="twic_expiration_container" class="mt-2" style="display: none;">
                            <label class="form-label">TWIC Card Expiration Date</label>
                            <x-base.form-input
                                type="date"
                                name="twic_expiration_date"
                                value="{{ old('twic_expiration_date', $details?->twic_expiration_date?->format('Y-m-d')) }}"
                            />
                            @error('twic_expiration_date')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Other Names -->
                    <div class="mt-4">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" 
                                   id="known_by_other_name"
                                   name="known_by_other_name"
                                   value="1"
                                   class="form-checkbox"
                                   {{ old('known_by_other_name', $details?->known_by_other_name) ? 'checked' : '' }}>
                            <label class="form-label ml-2" for="known_by_other_name">
                                Have you been known by any other names?
                            </label>
                        </div>
                        
                        <div id="other_names_container" class="mt-2" style="display: none;">
                            <label class="form-label">Other Names Used</label>
                            <x-base.form-input
                                name="other_names"
                                value="{{ old('other_names', $details?->other_names) }}"
                                placeholder="Enter other names you have used"
                            />
                            @error('other_names')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- How Did You Hear About Us -->
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">How did you hear about us?</label>
                            <select name="how_did_hear" class="form-select" required>
                                <option value="">Select an option</option>
                                <option value="internet" {{ old('how_did_hear', $details?->how_did_hear) === 'internet' ? 'selected' : '' }}>Internet Search</option>
                                <option value="social_media" {{ old('how_did_hear', $details?->how_did_hear) === 'social_media' ? 'selected' : '' }}>Social Media</option>
                                <option value="referral" {{ old('how_did_hear', $details?->how_did_hear) === 'referral' ? 'selected' : '' }}>Employee Referral</option>
                                <option value="job_board" {{ old('how_did_hear', $details?->how_did_hear) === 'job_board' ? 'selected' : '' }}>Job Board</option>
                                <option value="other" {{ old('how_did_hear', $details?->how_did_hear) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('how_did_hear')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="referral_container" style="display: none;">
                            <label class="form-label">Employee Who Referred You</label>
                            <x-base.form-input
                                name="referral_employee_name"
                                value="{{ old('referral_employee_name', $details?->referral_employee_name) }}"
                            />
                            @error('referral_employee_name')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Expected Pay -->
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">Expected Pay (Annual)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2">$</span>
                                <x-base.form-input
                                    name="expected_pay"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ old('expected_pay', $details?->expected_pay) }}"
                                    class="pl-8"
                                    required
                                />
                            </div>
                            @error('expected_pay')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6 pt-6 border-t p-7">
                    <x-base.button type="submit" variant="primary">
                        Submit Application
                        <x-base.lucide icon="CheckCircle" class="w-4 h-4 ml-2" />
                    </x-base.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // TWIC Card Toggle
        const hasTwicCard = document.getElementById('has_twic_card');
        const twicExpirationContainer = document.getElementById('twic_expiration_container');
        
        function toggleTwicExpiration(show) {
            twicExpirationContainer.style.display = show ? 'block' : 'none';
            const twicExpirationInput = twicExpirationContainer.querySelector('input');
            twicExpirationInput.required = show;
        }
        
        hasTwicCard.addEventListener('change', function() {
            toggleTwicExpiration(this.checked);
        });
        
        // Initial state for TWIC
        toggleTwicExpiration(hasTwicCard.checked);

        // Other Names Toggle
        const knownByOtherName = document.getElementById('known_by_other_name');
        const otherNamesContainer = document.getElementById('other_names_container');
        
        function toggleOtherNames(show) {
            otherNamesContainer.style.display = show ? 'block' : 'none';
            const otherNamesInput = otherNamesContainer.querySelector('input');
            otherNamesInput.required = show;
        }
        
        knownByOtherName.addEventListener('change', function() {
            toggleOtherNames(this.checked);
        });
        
        // Initial state for other names
        toggleOtherNames(knownByOtherName.checked);

        // Referral Toggle
        const howDidHearSelect = document.querySelector('select[name="how_did_hear"]');
        const referralContainer = document.getElementById('referral_container');
        
        function toggleReferral(value) {
            const show = value === 'referral';
            referralContainer.style.display = show ? 'block' : 'none';
            const referralInput = referralContainer.querySelector('input');
            referralInput.required = show;
        }
        
        howDidHearSelect.addEventListener('change', function() {
            toggleReferral(this.value);
        });
        
        // Initial state for referral
        toggleReferral(howDidHearSelect.value);

        // Format expected pay input
        const expectedPayInput = document.querySelector('input[name="expected_pay"]');
        expectedPayInput.addEventListener('input', function(e) {
            let value = e.target.value;
            // Remove any characters that aren't numbers or decimal point
            value = value.replace(/[^\d.]/g, '');
            // Ensure only one decimal point
            const decimalCount = (value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                value = value.replace(/\.+$/, '');
            }
            // Limit to 2 decimal places
            if (value.includes('.')) {
                const parts = value.split('.');
                value = parts[0] + '.' + parts[1].slice(0, 2);
            }
            e.target.value = value;
        });

        // Form validation before submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            // Validate radio buttons
            const radioGroups = ['eligible_to_work', 'can_speak_english'];
            for (const group of radioGroups) {
                const checked = form.querySelector(`input[name="${group}"]:checked`);
                if (!checked) {
                    e.preventDefault();
                    alert(`Please select an option for ${group.replace(/_/g, ' ')}`);
                    return;
                }
            }

            // Validate TWIC date if TWIC card is checked
            if (hasTwicCard.checked) {
                const twicDate = twicExpirationContainer.querySelector('input').value;
                if (!twicDate) {
                    e.preventDefault();
                    alert('Please enter TWIC card expiration date');
                    return;
                }
            }

            // Validate other names if checked
            if (knownByOtherName.checked) {
                const otherNames = otherNamesContainer.querySelector('input').value.trim();
                if (!otherNames) {
                    e.preventDefault();
                    alert('Please enter your other names');
                    return;
                }
            }

            // Validate referral name if referral is selected
            if (howDidHearSelect.value === 'referral') {
                const referralName = referralContainer.querySelector('input').value.trim();
                if (!referralName) {
                    e.preventDefault();
                    alert('Please enter the name of the employee who referred you');
                    return;
                }
            }
        });
    });
</script>
@endpush