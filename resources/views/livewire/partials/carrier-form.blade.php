<div>
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <h3 class="font-bold">{{ isset($carrier['id']) ? 'Edit Carrier' : 'Create Carrier' }}</h3>

                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">Profile Photo Carrier</div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Upload a clear and recent profile photo.
                            </div>
                        </div>
                    </div>
                    <div>
                        <!-- Foto del Carrier -->
                        <div class="mt-3 w-full flex-1 xl:mt-0">
                            <div x-data="{
                                photoPreview: null,
                                originalPhoto: '{{ $this->getLogoUrl() }}',
                                defaultPhoto: '{{ asset('build/default_profile.png') }}',
                                updatePreview(event) {
                                    const file = event.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = (e) => {
                                            this.photoPreview = e.target.result;
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                },
                                clearPhoto() {
                                    @this.call('deletePhoto'); // Llama al método Livewire para eliminar la foto
                                    this.photoPreview = null;
                                    this.originalPhoto = this.defaultPhoto;
                                }
                            }" class="flex items-center">
                                <!-- Imagen actual o vista previa -->
                                <div
                                    class="relative flex h-24 w-24 items-center justify-center rounded-full border border-primary/10 bg-primary/5">
                                    <!-- Vista previa -->
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" alt="Preview"
                                            class="h-full w-full rounded-full object-cover">
                                    </template>
                                    <!-- Imagen actual -->
                                    <template x-if="!photoPreview && originalPhoto">
                                        <img :src="originalPhoto" alt="Original Photo"
                                            class="h-full w-full rounded-full object-cover">
                                    </template>
                                    <!-- Imagen predeterminada -->
                                    <template x-if="!photoPreview && !originalPhoto">
                                        <img :src="defaultPhoto" alt="Default Photo"
                                            class="h-full w-full rounded-full object-cover">
                                    </template>

                                    <!-- Botón para subir imagen -->
                                    <label for="logoFileInput"
                                        class="box absolute bottom-0 right-0 flex h-7 w-7 items-center justify-center rounded-full cursor-pointer">
                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.3] text-slate-500" icon="Pencil" />
                                    </label>
                                </div>

                                <!-- Input de archivo -->
                                <input type="file" id="logoFileInput" class="hidden" accept="image/*"
                                    wire:model="logoFile" @change="updatePreview">

                                <!-- Botón para eliminar imagen -->
                                <x-base.button class="ml-8 mr-2 h-8 pl-3.5 pr-4" variant="outline-secondary"
                                    size="sm" @click="clearPhoto">
                                    <x-base.lucide class="mr-1.5 h-3.5 w-3.5 stroke-[1.3]" icon="Trash2" />
                                    Remove
                                </x-base.button>
                            </div>

                            <!-- Mensaje de error -->
                            @error('logoFile')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Full Name -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">Carrier Name</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter your full legal name as it appears on your official
                                identification.
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input id="name" wire:model="carrier.name" type="text"
                            placeholder="Enter full name" value="{{ old('name') }}" />
                        @error('carrier.name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Address --}}
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">Address Line 1</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter the primary line of your physical address,
                                typically including your house or building number and
                                street name.
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="address" wire:model="carrier.address"
                            placeholder="Enter your Address" />
                        @error('carrier.address')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <!-- State -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">State</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter your state
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="state" wire:model="carrier.state"
                            placeholder="Enter your State" />
                        @error('carrier.state')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- ZIP Code -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">ZIP Code</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter ZIP Code
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="zipcode" wire:model="carrier.zipcode"
                            placeholder="Enter your Zip Code" />
                        @error('carrier.zipcode')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- EIN Number -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">EIN Number</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter your EIN Number
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="ein_number" wire:model="carrier.ein_number"
                            placeholder="Enter your State" />
                        @error('carrier.ein_number')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- DOT Number -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">DOT Number</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter the your DOT Number
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="dot_number" wire:model="carrier.dot_number"
                            placeholder="Enter your DOT Number #" />
                        @error('carrier.dot_number')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- MC Number -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">MC Number</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter the MC Number
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="mc_number" wire:model="carrier.mc_number"
                            placeholder="Enter your MC Number" />
                        @error('carrier.mc_number')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- State DOT -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">State DOT</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter the State DOT
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="state_dot" wire:model="carrier.state_dot"
                            placeholder="Enter your State DOT" />
                        @error('carrier.ein_number')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- IFTA Account -->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">State DOT</div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter the State DOT
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-input type="text" id="ifta_account" wire:model="carrier.ifta_account"
                            placeholder="Enter your State IFTA Account" />
                        @error('carrier.ein_number')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>


                <!-- Membership-->
                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="flex items-center">
                                <div class="font-medium">Membership </div>
                                <div
                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    Required
                                </div>
                            </div>
                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                Enter your full legal name as it appears on your official
                                identification.
                            </div>
                        </div>
                    </div>
                    <!-- Membership Plan -->
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <select data-tw-merge aria-label="Default select example"
                            class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2"
                            id="id_plan" wire:model="carrier.id_plan">
                            <option value="">Select a Membership Plan</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                        @error('carrier.id_plan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                        <div class="text-left">
                            <div class="font-medium">Status</div>
                        </div>
                    </div>
                    <!-- Status -->
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <div class="mt-3 w-full flex-1 xl:mt-0">
                            <select data-tw-merge aria-label="Default select example"
                                class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2"
                                id="status" wire:model="carrier.status">
                                <option value="{{ App\Models\Carrier::STATUS_PENDING }}"
                                    {{ old('status') == App\Models\Carrier::STATUS_PENDING ? 'selected' : '' }}>Pending
                                </option>
                                <option value="{{ App\Models\Carrier::STATUS_ACTIVE }}"
                                    {{ old('status') == App\Models\Carrier::STATUS_ACTIVE ? 'selected' : '' }}>Active
                                </option>
                                <option value="{{ App\Models\Carrier::STATUS_INACTIVE }}"
                                    {{ old('status') == App\Models\Carrier::STATUS_INACTIVE ? 'selected' : '' }}>
                                    Inactive</option>
                            </select>
                            @error('carrier.status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end">
                    <x-base.button wire:click="saveCarrier" type="submit"
                        class="w-full border-primary/50 px-10 md:w-auto" variant="outline-primary">
                        <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                        Save Carrier
                    </x-base.button>

                    <x-base.button wire:click="$set('isCreating', false)" type="submit"
                        class="w-full border-primary/50 px-10 md:w-auto" variant="outline-primary">
                        <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                        Cancel
                    </x-base.button>
                </div>
            </div>
        </div>
    </div>
</div>
