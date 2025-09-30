<div class="box--stacked flex flex-col p-0">
    <div class="flex items-center px-5 py-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">Driver Information</h2>
    </div>
    <div class="p-5">
        <!-- Photo Upload -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Profile Photo</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Upload a clear and recent profile photo. Large images will be automatically optimized to reduce file size.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <div class="flex items-center space-x-4">
                    <div class="w-24 h-24 bg-gray-100 rounded-full overflow-hidden">
                        @if ($photo && $photo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile && $photo->isPreviewable())
                        <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif($photo_preview_url)
                        <img src="{{ $photo_preview_url }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    <input type="file" wire:model.live="photo" id="photo" class="hidden"
                        accept="image/jpeg,image/png,image/jpg,image/webp">
                    <label for="photo" class="px-4 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300">
                        Choose Photo
                    </label>
                </div>
                @error('photo')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <!-- First Name -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">First Name</div>
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Enter your legal first name.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input type="text" wire:model="name"
                    class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Middle Name -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Middle Name</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Enter your middle name if applicable.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input type="text" wire:model="middle_name"
                    class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                @error('middle_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Last Name -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Last Name</div>
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Enter your legal last name.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input type="text" wire:model="last_name"
                    class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                @error('last_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Email</div>
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Enter your email address.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input type="email" wire:model="email"
                    class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <!-- Phone -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Phone</div>
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Enter your primary contact phone number.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input type="number" wire:model="phone"
                    class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                @error('phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Date of Birth -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Date of Birth</div>
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        This information is required to verify your age and provide age-appropriate services.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <input type="text" id="date_of_birth" name="date_of_birth" value="{{ $date_of_birth }}"
                    onchange="@this.set('date_of_birth', this.value)"
                    class="driver-datepicker disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm"
                    placeholder="MM/DD/YYYY" />
                @error('date_of_birth')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Password</div>
                        @if (!$driverId)
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                        @endif
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Create a secure password for your account.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input type="password" wire:model="password"
                    class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Confirm Password</div>
                        @if (!$driverId)
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                        @endif
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Confirm your password to ensure it's entered correctly.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input type="password" wire:model="password_confirmation"
                    class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                @error('password_confirmation')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>


        <!-- Custom Dates Section -->
        <div class="mt-8 border-t border-slate-200/60 pt-8">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-slate-800 mb-2">Custom Dates (Only for Historical Drivers)</h3>
                <p class="text-sm text-slate-500">Enable this option if you are registering a historical driver with specific registration dates.</p>   
            </div>

            <!-- Use Custom Dates Checkbox -->
            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                    <div class="text-left">
                        <div class="flex items-center">
                            <div class="font-medium">Use Custom Dates</div>
                        </div>
                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                            Check this box to specify a custom registration date.
                        </div>
                    </div>
                </div>
                <div class="mt-3 w-full flex-1 xl:mt-0">
                    <label class="flex items-center">
                        <x-base.form-check.input type="checkbox" wire:model.live="use_custom_dates" class="mr-2" />
                        <span class="text-sm">Enable custom dates for historical driver</span>
                    </label>
                </div>
            </div>

            @if($use_custom_dates)
            <!-- Custom Date -->
            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                    <div class="text-left">
                        <div class="flex items-center">
                            <div class="font-medium">Registration Date</div>
                            <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                Required</div>
                        </div>
                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                            Date when the driver originally registered in the system.
                        </div>
                    </div>
                </div>
                <div class="mt-3 w-full flex-1 xl:mt-0">
                    <input type="text" id="custom_date" name="custom_date" value="{{ $custom_date }}"
                        onchange="@this.set('custom_date', this.value)"
                        class="driver-datepicker disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm"
                        placeholder="MM/DD/YYYY" />
                    @error('custom_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            @endif
        </div>

                <!-- Status -->
        <div class="mt-14 w-full flex-1">
            <select data-tw-merge aria-label="Default select example" wire:model="status"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"">
                <option value=" 1">Active</option>
                <option value="0">Inactive</option>
                <option value="2">Pending</option>
            </select>
            @error('status')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Terms and Conditions -->
        <div class="mb-4 mt-4">
            <label class="flex items-center">
                <x-base.form-check.input type="checkbox" wire:model="terms_accepted" class="mr-2" />
                <span>I accept the terms and conditions *</span>
            </label>
            @error('terms_accepted')
            <span class="text-red-500 text-sm block">{{ $message }}</span>
            @enderror
        </div>


        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
            <div class="w-full sm:w-auto"></div>
            <div class="flex flex-col sm:flex-row md:py-0 py-5 gap-4 w-full sm:w-auto">
                <x-base.button type="button" wire:click="saveAndExit" class="w-full sm:w-44 text-white"
                    variant="warning">
                    <i class="fas fa-save mr-2"></i> Save & Exit
                </x-base.button>
                <x-base.button type="button" wire:click="next" class="w-full sm:w-44" variant="primary">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </x-base.button>
            </div>
        </div>
    </div>