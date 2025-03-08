@extends('../themes/' . $activeTheme)
@section('title', 'New Driver Application - Step 2')

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mb-6">
                <h2 class="text-2xl font-medium">Step 2: Address History</h2>
                <div class="mt-2 text-slate-500">
                    Please provide the current address and any previous addresses (covering at least 3 years).
                </div>
            </div>

            <div class="box box--stacked flex flex-col">
                <form
                    action="{{ route('admin.carrier.user_drivers.application.step2.store', [
                        'carrier' => $carrier,
                        'application' => $application,
                    ]) }}"
                    method="POST">
                    @csrf

                    <!-- Current Address -->
                    <div class="p-7">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium">Current Address</h3>
                        </div>

                        <!-- Address Lines -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Address Line 1</label>
                                <x-base.form-input name="address_line1"
                                    value="{{ old('address_line1', $currentAddress?->address_line1) }}" required />
                                @error('address_line1')
                                    <div class="text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label">Address Line 2</label>
                                <x-base.form-input name="address_line2"
                                    value="{{ old('address_line2', $currentAddress?->address_line2) }}" />
                            </div>
                        </div>

                        <!-- City and State -->
                        <div class="grid grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="form-label">City</label>
                                <x-base.form-input name="city" value="{{ old('city', $currentAddress?->city) }}"
                                    required />
                                @error('city')
                                    <div class="text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label">State</label>
                                <x-base.form-input name="state" value="{{ old('state', $currentAddress?->state) }}"
                                    required />
                                @error('state')
                                    <div class="text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- ZIP and From Date -->
                        <div class="grid grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="form-label">ZIP Code</label>
                                <x-base.form-input name="zip_code" value="{{ old('zip_code', $currentAddress?->zip_code) }}"
                                    required />
                                @error('zip_code')
                                    <div class="text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="form-label">Lived From</label>
                                <x-base.form-input type="date" name="from_date"
                                    value="{{ old('from_date', $currentAddress?->from_date?->format('Y-m-d')) }}"
                                    required />
                                @error('from_date')
                                    <div class="text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- To Date and Three Years Checkbox -->
                        <div class="grid grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="form-label">Lived To (if still living here, leave blank)</label>
                                <x-base.form-input type="date" name="to_date"
                                    value="{{ old('to_date', $currentAddress?->to_date?->format('Y-m-d')) }}" />
                                @error('to_date')
                                    <div class="text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="flex items-center mt-6">
                                <input type="checkbox" name="lived_three_years" id="lived_three_years" value="1"
                                    class="mr-2"
                                    {{ old('lived_three_years', $currentAddress?->lived_three_years) ? 'checked' : '' }} />
                                <label for="lived_three_years">
                                    I have lived here for 3 years (or more).
                                </label>
                            </div>
                        </div>
                        @error('lived_three_years')
                            <div class="text-red-500 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Previous Addresses Section -->
                    <div class="p-7 border-t" id="previous-addresses-section" style="display: none;">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium">Previous Addresses</h3>
                            <p class="text-slate-500 text-sm">
                                Please list all previous addresses in which you have lived in the last 3 years.
                            </p>
                        </div>

                        <div id="previous-addresses-container">
                            <!-- Dynamic addresses will be inserted here -->
                        </div>

                        <div>
                            <x-base.button type="button" variant="outline-primary" id="add-previous-address-btn"
                                class="mt-4">
                                <x-base.lucide icon="Plus" class="w-4 h-4 mr-2" />
                                Add Another Previous Address
                            </x-base.button>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-6 border-t">
                        <x-base.button type="submit" variant="primary">
                            Continue to Step 3
                            <x-base.lucide icon="ArrowRight" class="w-4 h-4 ml-2" />
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
            const livedThreeYearsCheckbox = document.getElementById('lived_three_years');
            const previousAddressesSection = document.getElementById('previous-addresses-section');
            const addPreviousAddressBtn = document.getElementById('add-previous-address-btn');
            const previousAddressesContainer = document.getElementById('previous-addresses-container');
            let addressIndex = 0;

            function getPreviousAddressTemplate(index) {
                return `
                <div class="border rounded-md p-4 mb-4 bg-slate-50 relative">
                    <button type="button" 
                            class="absolute top-2 right-2 text-red-500 hover:text-red-700"
                            onclick="this.closest('.border').remove()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Address Line 1</label>
                            <input type="text" 
                                   name="previous_addresses[${index}][address_line1]" 
                                   class="form-input w-full" 
                                   required />
                        </div>
                        <div>
                            <label class="form-label">Address Line 2</label>
                            <input type="text" 
                                   name="previous_addresses[${index}][address_line2]" 
                                   class="form-input w-full" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">City</label>
                            <input type="text" 
                                   name="previous_addresses[${index}][city]" 
                                   class="form-input w-full" 
                                   required />
                        </div>
                        <div>
                            <label class="form-label">State</label>
                            <input type="text" 
                                   name="previous_addresses[${index}][state]" 
                                   class="form-input w-full" 
                                   required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">ZIP Code</label>
                            <input type="text" 
                                   name="previous_addresses[${index}][zip_code]" 
                                   class="form-input w-full" 
                                   required />
                        </div>
                        <div>
                            <label class="form-label">Lived From</label>
                            <input type="date" 
                                   name="previous_addresses[${index}][from_date]" 
                                   class="form-input w-full" 
                                   required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">Lived To</label>
                            <input type="date" 
                                   name="previous_addresses[${index}][to_date]" 
                                   class="form-input w-full" 
                                   required />
                        </div>
                    </div>
                </div>
            `;
            }

            function togglePreviousAddressesSection(isChecked) {
                previousAddressesSection.style.display = isChecked ? 'none' : 'block';
            }

            // Función para calcular años entre fechas
            function calculateYearsBetweenDates(fromDate, toDate) {
                const start = new Date(fromDate);
                const end = toDate ? new Date(toDate) : new Date();
                return (end - start) / (1000 * 60 * 60 * 24 * 365);
            }

            // Función para calcular el total de años cubiertos
            function calculateTotalYearsCovered() {
                let totalYears = 0;

                // Calcular años de la dirección principal
                const mainFromDate = document.querySelector('input[name="from_date"]').value;
                const mainToDate = document.querySelector('input[name="to_date"]').value;
                totalYears += calculateYearsBetweenDates(mainFromDate, mainToDate);

                // Calcular años de direcciones previas
                const previousAddresses = document.querySelectorAll('#previous-addresses-container > div');
                previousAddresses.forEach(address => {
                    const fromDate = address.querySelector('input[name$="[from_date]"]').value;
                    const toDate = address.querySelector('input[name$="[to_date]"]').value;
                    if (fromDate && toDate) {
                        totalYears += calculateYearsBetweenDates(fromDate, toDate);
                    }
                });

                return totalYears;
            }

            // Función para validar el formulario
            function validateForm(e) {
                const mainFromDate = document.querySelector('input[name="from_date"]').value;
                if (!mainFromDate) {
                    alert('Please enter the start date for your current address');
                    e.preventDefault();
                    return false;
                }

                const totalYears = calculateTotalYearsCovered();

                if (totalYears < 3) {
                    alert('Your address history must cover at least 3 years in total. Currently covering: ' +
                        totalYears.toFixed(1) + ' years');
                    e.preventDefault();
                    return false;
                }

                return true;
            }

            function togglePreviousAddressesSection(isChecked) {
                previousAddressesSection.style.display = isChecked ? 'none' : 'block';

                // Si está marcado que vivió 3 años, validar que la dirección actual cubra 3 años
                if (isChecked) {
                    const mainFromDate = document.querySelector('input[name="from_date"]').value;
                    const mainToDate = document.querySelector('input[name="to_date"]').value || new Date()
                        .toISOString().split('T')[0];
                    const years = calculateYearsBetweenDates(mainFromDate, mainToDate);

                    if (years < 3) {
                        alert('If you select that you have lived at your current address for 3 years, ' +
                            'the dates must reflect at least 3 years of residence. ' +
                            'Current duration: ' + years.toFixed(1) + ' years');
                        livedThreeYearsCheckbox.checked = false;
                        previousAddressesSection.style.display = 'block';
                    }
                }
            }

            // Event listeners
            livedThreeYearsCheckbox.addEventListener('change', function(e) {
                togglePreviousAddressesSection(e.target.checked);
            });

            addPreviousAddressBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const template = getPreviousAddressTemplate(addressIndex);
                previousAddressesContainer.insertAdjacentHTML('beforeend', template);
                addressIndex++;
            });

            // Añadir validación al envío del formulario
            const form = document.querySelector('form');
            form.addEventListener('submit', validateForm);

            // Validar fechas cuando cambien
            document.querySelector('input[name="from_date"]').addEventListener('change', function() {
                if (livedThreeYearsCheckbox.checked) {
                    togglePreviousAddressesSection(true);
                }
            });

            document.querySelector('input[name="to_date"]').addEventListener('change', function() {
                if (livedThreeYearsCheckbox.checked) {
                    togglePreviousAddressesSection(true);
                }
            });

            // Estado inicial
            togglePreviousAddressesSection(livedThreeYearsCheckbox.checked);
        });
    </script>
@endpush
