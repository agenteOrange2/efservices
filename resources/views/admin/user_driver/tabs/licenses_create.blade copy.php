<div class="bg-white p-4 rounded-lg shadow">

    <h3 class="text-lg font-semibold mb-4">Licenses Information</h3>
    <p>License management functionality coming soon.</p>


    {{-- Driver Details --}}
    <div class="bg-white p-4 rounded-lg shadow mt-6">
        <button type="button" @click="toggleSection('driver')"
            class="w-full p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
            <h3 class="text-lg font-semibold">Driver Details</h3>
            <svg :class="{ 'transform rotate-180': openSections.driver }"
                class="w-5 h-5 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        {{-- Contenido colapsable --}}
        <div x-show="openSections.driver" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="p-4 border-t border-gray-100">



            {{-- License Number --}}
            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                    <div class="text-left">
                        <div class="flex items-center">
                            <div class="font-medium">License Number</div>
                            <div class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                Required
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 w-full flex-1 xl:mt-0">
                    <x-base.form-input name="license_number" type="text" placeholder="Enter license number"
                        value="{{ old('license_number') }}" />
                    @error('license_number')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- State of Issue --}}
            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                    <div class="text-left">
                        <div class="flex items-center">
                            <div class="font-medium">State of Issue</div>
                            <div class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                Required
                            </div>
                        </div>
                        <div class="mt-1.5 text-xs text-slate-500/80 xl:mt-3">
                            Enter your complete State of Issue
                        </div>
                    </div>
                </div>
                <div class="mt-3 w-full flex-1 xl:mt-0">
                    <select name="state_of_issue"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-primary focus:ring-opacity-20">
                        <option value="">Select State</option>
                        @foreach ($usStates as $code => $name)
                            <option value="{{ $code }}" {{ old('state_of_issue') == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('state_of_issue')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>


    {{-- License Class --}}
    <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">License Class</div>
                    <div class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                        Required
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <select name="license_class" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                <option value="">Select Class</option>
                <option value="A" {{ old('license_class') == 'A' ? 'selected' : '' }}>Class A</option>
                <option value="B" {{ old('license_class') == 'B' ? 'selected' : '' }}>Class B</option>
                <option value="C" {{ old('license_class') == 'C' ? 'selected' : '' }}>Class C</option>
            </select>
            @error('license_class')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Expiration Date --}}
    <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Expiration Date</div>
                    <div class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                        Required
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input name="license_expiration" type="date" value="{{ old('license_expiration') }}" />
            @error('license_expiration')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Endorsements --}}
    <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Endorsements</div>
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="endorsements[]" value="H"
                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded"
                        {{ in_array('H', old('endorsements', [])) ? 'checked' : '' }}>
                    <label class="ml-2">H - Hazardous Materials</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="endorsements[]" value="N"
                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded"
                        {{ in_array('N', old('endorsements', [])) ? 'checked' : '' }}>
                    <label class="ml-2">N - Tank Vehicle</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="endorsements[]" value="P"
                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded"
                        {{ in_array('P', old('endorsements', [])) ? 'checked' : '' }}>
                    <label class="ml-2">P - Passenger Transport</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="endorsements[]" value="T"
                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded"
                        {{ in_array('T', old('endorsements', [])) ? 'checked' : '' }}>
                    <label class="ml-2">T - Double/Triple Trailers</label>
                </div>
            </div>
        </div>
    </div>
</div>
