{{-- resources/views/livewire/admin/driver/steps/step-criminal-history.blade.php --}}
<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Criminal History Investigation</h3>

    <!-- Criminal Record Section -->
    <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
        <h4 class="font-medium text-lg mb-3">Criminal Record</h4>

        <div class="mb-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">Do you have criminal charges pending?</p>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="has_criminal_charges" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            @error('has_criminal_charges')
                <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Felonies Section -->
    <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
        <h4 class="font-medium text-lg mb-3">Felonies</h4>

        <div class="mb-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">Have you ever pled 'guilty' to, been convicted of, or pled 'no contest'
                    to a felony?</p>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="has_felony_conviction" class="sr-only peer">
                    <div
                        class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                    </div>
                </label>
            </div>
            @error('has_felony_conviction')
                <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">If you have any felony convictions, do you currently hold a minister's
                    permit to enter or exit Canada?</p>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="has_minister_permit" class="sr-only peer">
                    <div
                        class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                    </div>
                </label>
            </div>
            @error('has_minister_permit')
                <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Criminal Background Check Release Section -->
    <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
        <h4 class="font-medium text-lg mb-3">Criminal Background Check Release</h4>

        <div class="prose prose-sm max-w-none mb-4 text-gray-700">
            <h5 class="text-base font-medium">Fair Credit Reporting Act Disclosure and Authorization Form For Employment
                Purposes</h5>
            <p>I understand that, pursuant to the federal Fair Credit Reporting Act (FCRA), if any adverse action is to
                be taken based upon the consumer report, a copy of the report and a summary of the consumer's rights
                will be provided to me.</p>
            <!-- Aquí iría todo el texto de la política -->
        </div>

        <div class="flex items-center mt-4">
            <input type="checkbox" id="fcra_consent" wire:model="fcra_consent"
                class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-2">
            <label for="fcra_consent" class="text-sm font-medium text-gray-700">
                I agree and consent to the above
            </label>
        </div>
        @error('fcra_consent')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Background Information Form Section -->
    <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
        <h4 class="font-medium text-lg mb-3">Background Information Form</h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $full_name ?? 'Not available' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $middle_name ?? 'Not available' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $last_name ?? 'Not available' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last 4 Digits of SSN</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $ssn_last_four ?? 'Not available' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">
                    {{ $date_of_birth ? \Carbon\Carbon::parse($date_of_birth)->format('m/d/Y') : 'Not available' }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Driver's License Number</label>
                <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $license_number ?? 'Not available' }}</p>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">License State of Issuance</label>
            <p class="px-3 py-2 bg-gray-100 rounded-md text-sm">{{ $license_state ?? 'Not available' }}</p>
        </div>

        <div class="mb-4">
            <h5 class="text-sm font-medium text-gray-700 mb-2">Address History</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Address
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                City
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                State
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Zip
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                From
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                To
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($addresses as $address)
                            <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50' }}">
                                <td class="px-6 py-2 text-sm text-gray-500">
                                    {{ $address['address_line1'] }}
                                    @if (!empty($address['address_line2']))
                                        <br>{{ $address['address_line2'] }}
                                    @endif
                                </td>
                                <td class="px-6 py-2 text-sm text-gray-500">
                                    {{ $address['city'] }}
                                </td>
                                <td class="px-6 py-2 text-sm text-gray-500">
                                    {{ $address['state'] }}
                                </td>
                                <td class="px-6 py-2 text-sm text-gray-500">
                                    {{ $address['zip_code'] }}
                                </td>
                                <td class="px-6 py-2 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($address['from_date'])->format('m/d/Y') }}
                                </td>
                                <td class="px-6 py-2 text-sm text-gray-500">
                                    {{ empty($address['to_date']) ? 'Present' : \Carbon\Carbon::parse($address['to_date'])->format('m/d/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500">
                                    No address history available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center mt-4">
            <input type="checkbox" id="background_info_consent" wire:model="background_info_consent"
                class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-2">
            <label for="background_info_consent" class="text-sm font-medium text-gray-700">
                By signing below, you are certifying that the above information is true and correct.
            </label>
        </div>
        @error('background_info_consent')
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
