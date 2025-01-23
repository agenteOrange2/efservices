{{-- resources/views/livewire/admin/driver/create-driver.blade.php --}}
<div>
    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
            <li class="mr-2">
                <button
                    class="inline-block p-4 {{ $activeTab === 'general' ? 'text-blue-600 border-b-2 border-blue-600' : 'hover:text-gray-600 hover:border-gray-300' }}"
                    wire:click="setTab('general')">
                    General Information
                </button>
            </li>
            <li class="mr-2">
                <button
                    class="inline-block p-4 {{ $activeTab === 'licenses' ? 'text-blue-600 border-b-2 border-blue-600' : 'hover:text-gray-600 hover:border-gray-300' }}"
                    wire:click="setTab('licenses')">
                    Licenses
                </button>
            </li>
        </ul>
    </div>

    <form wire:submit.prevent="save">
        @if ($activeTab === 'general')
            <div class="">
                <!-- User Information -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">User Information</h3>
                    <div class="mt-7">
                        <div class="box--stacked flex flex-col">
                            <form wire:submit.prevent="save">
                                {{-- Photo --}}
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Profile Photo</div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Upload a clear and recent driver photo.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <div class="flex items-center">
                                            <input type="file" wire:model="photo" class="hidden" id="photo"
                                                accept="image/*">
                                            <label for="photo" class="cursor-pointer">
                                                @if ($photo)
                                                    <img src="{{ $photo->temporaryUrl() }}"
                                                        class="w-24 h-24 rounded-full object-cover">
                                                @else
                                                    <img src="{{ asset('build/default_profile.png') }}"
                                                        class="w-24 h-24 rounded-full">
                                                @endif
                                            </label>
                                        </div>
                                        @error('photo')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Name Fields -->
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Full Name</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <div class="grid grid-cols-2 gap-4">
                                            <x-base.form-input wire:model="name" type="text"
                                                placeholder="Enter Name" />
                                            @error('name')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror

                                            <x-base.form-input wire:model="middle_name" type="text"
                                                placeholder="Enter Middle name" />
                                            @error('middle_name')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror

                                            <div class="col-span-2">
                                                <x-base.form-input wire:model="last_name" type="text"
                                                    placeholder="Enter Last name" />
                                                @error('last_name')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div x-data
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Email</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Please provide a valid email address.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input wire:model="email" type="email"
                                            placeholder="Enter email" />
                                        @error('email')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Phone</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0" x-data="{ mask: null }"
                                        x-init="mask = IMask($refs.phone, { mask: '(000) 000-0000' })">
                                        <x-base.form-input x-ref="phone" wire:model="phone" type="text"
                                            placeholder="(555) 555-5555" />
                                        @error('phone')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Birth Day --}}
                                <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Birth Date</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.form-input wire:model="date_of_birth" type="date" />
                                    @error('date_of_birth')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                                <!-- State of ISSUE -->
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">State of Issue</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Enter your complete State of Issue
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <select wire:model="state_of_issue" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2">
                                            <option value="">Select State</option>
                                            @foreach ($usStates as $code => $name)
                                                <option value="{{ $code }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('state_of_issue')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>


                                <!-- Password -->
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">New Password</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Create a new password for your account.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <!-- Aquí usamos wire:model="password" -->
                                        <x-base.form-input wire:model="password" type="password"
                                            placeholder="Enter password" />
                                        @error('password')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Confirm Password</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Confirm the password you entered above.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input wire:model="password_confirmation" type="password"
                                            placeholder="Confirm password" />
                                        <div class="mt-4 text-slate-500">
                                            <div class="font-medium">
                                                Password requirements:
                                            </div>
                                            <ul class="mt-2.5 flex list-disc flex-col gap-1 pl-3 text-slate-500">
                                                <li class="pl-0.5">
                                                    Passwords must be at least 8 characters long.
                                                </li>
                                                <li class="pl-0.5">
                                                    Include at least one numeric digit (0-9).
                                                </li>
                                            </ul>
                                        </div>
                                        @error('password_confirmation')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        
                                        <div class="flex items-center">
                                            <div class="font-medium">Status</div>
                                            <div
                                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                            Required
                                        </div>

                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <div class="mt-3 w-full flex-1 xl:mt-0">
                                            <select data-tw-merge aria-label="Default select example"
                                                class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2"
                                                id="status" name="status">
                                                <option value="{{ App\Models\UserDriverDetail::STATUS_PENDING }}"
                                                    {{ old('status') == App\Models\UserDriverDetail::STATUS_PENDING ? 'selected' : '' }}>
                                                    Pending
                                                </option>
                                                <option value="{{ App\Models\UserDriverDetail::STATUS_ACTIVE }}"
                                                    {{ old('status') == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'selected' : '' }}>
                                                    Active
                                                </option>
                                                <option value="{{ App\Models\UserDriverDetail::STATUS_INACTIVE }}"
                                                    {{ old('status') == App\Models\UserDriverDetail::STATUS_INACTIVE ? 'selected' : '' }}>
                                                    Inactive
                                                </option>
                                            </select>
                                            @error('status')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <!-- Terms & Conditions -->
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Terms & Conditions</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center mr-auto">
                                        <x-base.form-check.input class="mr-2.5 border" id="remember-me"
                                            type="checkbox" name="terms_accepted" value="1" />
                                        <label class="cursor-pointer select-none" for="remember-me">
                                            I accept the terms and conditions
                                        </label>
                                    </div>
                                </div>


                            </form>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Address Details</h3>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <div class="space-y-4">
                            <!-- Current Address Duration Display -->
                            @if ($currentAddressDuration)
                                <div class="text-sm text-gray-600">Current residence: {{ $currentAddressDuration }}
                                </div>
                            @endif

                            <!-- Address Total Summary -->
                            <div class="text-sm {{ $isAddressValid || $lived_three_years ? 'text-green-600' : 'text-amber-600' }}">
                                Total years: {{ number_format($totalYears, 1) }}
                                @if ($remainingYears > 0)
                                    ({{ number_format($remainingYears, 1) }} more needed)
                                @endif
                            </div>

                            <!-- Previous Addresses Duration -->
                            @foreach ($previous_addresses as $index => $address)
                                @if (isset($address['duration']))
                                    <div class="text-sm text-gray-600 mt-2">
                                        {{ $address['duration'] }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-7">
                        <div class="box--stacked flex flex-col">
                            <!-- Address Information -->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Current Address</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="space-y-4">
                                        <x-base.form-input wire:model="address_line1" type="text"
                                            placeholder="Address Line 1" />
                                        <x-base.form-input wire:model="address_line2" type="text"
                                            placeholder="Address Line 2" />
                                        <div class="grid grid-cols-3 gap-4">
                                            <x-base.form-input wire:model="city" type="text" placeholder="City" />
                                            <x-base.form-input wire:model="state" type="text"
                                                placeholder="State" />
                                            <x-base.form-input wire:model="zip_code" type="text"
                                                placeholder="ZIP Code" />
                                        </div>
                                    </div>
                                    @error('address_line1')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Address Duration</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-sm mb-1">From Date</label>
                                                <x-base.form-input wire:model.live="from_date" type="date" />
                                            </div>
                                            <div>
                                                <label class="text-sm mb-1">To Date</label>
                                                <x-base.form-input wire:model.live="to_date" type="date" />
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <x-base.form-check.input wire:model.live="lived_three_years"
                                                type="checkbox" class="mr-2" />
                                            <span>I have lived at this address for 3+ years</span>
                                        </div>
                                        @error('lived_three_years')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if (!$lived_three_years)
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Previous Addresses</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        @foreach ($previous_addresses as $index => $address)
                                            <div class="border p-4 rounded-lg mb-4">
                                                <div class="space-y-4">
                                                    <x-base.form-input
                                                        wire:model="previous_addresses.{{ $index }}.address_line1"
                                                        type="text" placeholder="Address Line 1" />
                                                    <x-base.form-input
                                                        wire:model="previous_addresses.{{ $index }}.city"
                                                        type="text" placeholder="City" />
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <x-base.form-input
                                                            wire:model="previous_addresses.{{ $index }}.state"
                                                            type="text" placeholder="State" />
                                                        <x-base.form-input
                                                            wire:model="previous_addresses.{{ $index }}.zip_code"
                                                            type="text" placeholder="ZIP" />
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="text-sm mb-1">From Date</label>
                                                            <x-base.form-input
                                                                wire:model.live="previous_addresses.{{ $index }}.from_date"
                                                                type="date" />
                                                        </div>
                                                        <div>
                                                            <label class="text-sm mb-1">To Date</label>
                                                            <x-base.form-input
                                                                wire:model.live="previous_addresses.{{ $index }}.to_date"
                                                                type="date" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <button wire:click="removeAddress({{ $index }})"
                                                    type="button" class="text-red-500 mt-2">
                                                    Remove Address
                                                </button>
                                            </div>
                                        @endforeach

                                        

                                        <button wire:click="addAddress" type="button"
                                            class="btn btn-outline-primary {{ $isAddressValid || $lived_three_years ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            {{ $isAddressValid || $lived_three_years ? 'disabled' : '' }}>
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Plus" />
                                            Add Previous Address
                                        </button>
                                    </div>
                                </div>
                            @endif


                        </div>
                    </div>
                </div>


                <!-- Driver Details -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Driver Details</h3>
                    <div class="mt-7">
                        <div class="box--stacked flex flex-col">
                            <!-- Driver Details Box -->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Social Security Number</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required</div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter your SSN for identification
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0" x-data="{ mask: null }"
                                    x-init="mask = IMask($refs.ssn, { mask: '000-00-0000' })">
                                    <x-base.form-input x-ref="ssn" wire:model="social_security_number"
                                        type="text" placeholder="XXX-XX-XXXX" />
                                    @error('social_security_number')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">License Number</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.form-input wire:model="license_number" type="text"
                                        placeholder="Enter license number" />
                                    @error('license_number')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">TWIC Card</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="flex items-center">
                                        <x-base.form-check.input wire:model.live="has_twic_card" type="checkbox" class="mr-2" />
                                        <span>I have a TWIC card</span>
                                    </div>
                                    @if ($has_twic_card)
                                        <div class="mt-2">
                                            <x-base.form-input wire:model="twic_expiration_date" type="date"
                                                placeholder="Expiration Date" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Application Details</h3>
                    <div class="mt-7">
                        <div class="box--stacked flex flex-col">
                            <!-- Application Details -->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Position Applied For</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <select wire:model.live="applying_position" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1">
                                        <option value="">Select Position</option>
                                        @foreach($driverPositions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('applying_position')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                
                                    @if($applying_position === 'other')
                                        <div class="mt-2">
                                            <x-base.form-input wire:model.live="applying_position_other" 
                                                type="text" 
                                                placeholder="Specify position" />
                                            @error('applying_position_other')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Location Preference</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <select wire:model="applying_location" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1">
                                        <option value="">Select Location</option>
                                        @foreach($usStates as $code => $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('applying_location')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="font-medium">Eligibility Information</div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="space-y-3">
                                        <div class="flex flex-col">
                                            <label class="mb-2">Eligible to work in the United States</label>
                                            <select wire:model.live="eligible_to_work" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1">
                                                <option value="">Select</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                            @if($eligible_to_work === false)
                                                <p class="text-red-600 text-sm mt-1">According to U.S. law, you must be eligible to work in the United States to continue with this application.</p>
                                            @endif
                                        </div>
                                
                                        <div class="flex flex-col">
                                            <label class="mb-2">Can speak and understand English</label>
                                            <select wire:model="can_speak_english" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1">
                                                <option value="">Select</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Expected Pay Rate</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="flex items-center">
                                        <span class="mr-2">$</span>
                                        <x-base.form-input wire:model="expected_pay" type="number" step="0.01"
                                            min="0" placeholder="0.00" />
                                        <span class="ml-2">per hour</span>
                                    </div>
                                    @error('expected_pay')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Referral Source</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <select wire:model.live="how_did_hear" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1">
                                        <option value="">Select Source</option>
                                        @foreach($referralSources as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>                                
                                
                                    @if($how_did_hear === 'other')
                                        <div class="mt-2">
                                            <x-base.form-input wire:model.live="how_did_hear_other" 
                                                type="text" 
                                                placeholder="Specify source" />
                                        </div>
                                    @endif
                                
                                    @error('how_did_hear')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($activeTab === 'licenses')
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Licenses Information</h3>
                <p>License management functionality coming soon.</p>
            </div>
        @endif

        {{-- <div class="mt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Save Driver
            </button>            
        </div> --}}

        <!-- Submit Buttons -->
        <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end">
            <x-base.button type="submit" wire:click="save" class="w-full border-primary/50 px-10 md:w-auto"
                variant="outline-primary">
                <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                Save Driver
            </x-base.button>

            <x-base.button as="a" href="{{ route('admin.carrier.user_drivers.index', $carrier) }}"
                class="w-full border-primary/50 px-10 md:w-auto ml-2" variant="outline-secondary">
                Cancel
            </x-base.button>
        </div>
    </form>
</div>
