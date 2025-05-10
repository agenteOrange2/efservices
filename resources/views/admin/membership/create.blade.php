@extends('../themes/' . $activeTheme)
@section('title', 'New Membership')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Membership', 'url' => route('admin.membership.index')],
        ['label' => 'New Membership', 'active' => true],
    ];
@endphp
@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <form action="{{ route('admin.membership.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-7">
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Plan image</div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Please upload an image to show on the plan.
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="flex items-center">
                                        <x-image-preview name="image_membership" id="image_membership"
                                            currentPhotoUrl="{{ null }}"
                                            defaultPhotoUrl="{{ asset('build/default_profile.png') }}"
                                            deleteUrl="{{ null }}" />
                                    </div>
                                </div>
                            </div>
                            <!-- Full Name -->
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Membership name</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Please enter the full name of membership
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.form-input name="name" type="text" placeholder="Enter full name membership" id="name" value="{{ old('name') }}" />
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <!-- Description -->
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Description</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Please enter a brief description of the membership contents
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.form-textarea name="description" type="text"
                                        placeholder="Enter description" id="description" value="{{ old('description') }}" />
                                    @error('description')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div x-data="{ pricingType: '{{ old('pricing_type', 'plan') }}' }" class="mt-5">
                                {{-- Pricing Type --}}
                                <div class="flex-col block pt-5 mt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="inline-block mb-2 sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Pricing Type</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Select the pricing model for this membership
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1 w-full mt-3 xl:mt-0">
                                        <select name="pricing_type" id="pricing_type" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2" x-model="pricingType">
                                            <option value="plan">Plan (Bundle pricing)</option>
                                            <option value="individual">Individual (Separate pricing)</option>
                                        </select>
                                        @error('pricing_type')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Plan Price --}}
                                <div class="flex-col block pt-5 mt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center" x-show="pricingType === 'plan'" x-transition>
                                    <div class="inline-block mb-2 sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Plan Price</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required for Plan
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Enter the total price for the plan bundle
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1 w-full mt-3 xl:mt-0">
                                        <div class="grid-cols-1 gap-2 sm:grid">
                                            <x-base.input-group>
                                                <x-base.input-group.text>$</x-base.input-group.text>
                                                <x-base.form-input type="number" step="0.01" name="price" id="price"
                                                    value="{{ old('price') }}" placeholder="Plan Price USD" />
                                            </x-base.input-group>
                                        </div>
                                        @error('price')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Individual Prices --}}
                                <div x-show="pricingType === 'individual'" x-transition>
                                    {{-- Carrier Price --}}
                                    <div class="flex-col block pt-5 mt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                        <div class="inline-block mb-2 sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium">Carrier Price</div>
                                                    <div
                                                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required for Individual
                                                    </div>
                                                </div>
                                                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                    Price per carrier
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-1 w-full mt-3 xl:mt-0">
                                            <div class="grid-cols-1 gap-2 sm:grid">
                                                <x-base.input-group>
                                                    <x-base.input-group.text>$</x-base.input-group.text>
                                                    <x-base.form-input type="number" step="0.01" name="carrier_price" id="carrier_price"
                                                        value="{{ old('carrier_price') }}" placeholder="Price per carrier" />
                                                </x-base.input-group>
                                            </div>
                                            @error('carrier_price')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Driver Price --}}
                                    <div class="flex-col block pt-5 mt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                        <div class="inline-block mb-2 sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium">Driver Price</div>
                                                    <div
                                                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required for Individual
                                                    </div>
                                                </div>
                                                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                    Price per driver
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-1 w-full mt-3 xl:mt-0">
                                            <div class="grid-cols-1 gap-2 sm:grid">
                                                <x-base.input-group>
                                                    <x-base.input-group.text>$</x-base.input-group.text>
                                                    <x-base.form-input type="number" step="0.01" name="driver_price" id="driver_price"
                                                        value="{{ old('driver_price') }}" placeholder="Price per driver" />
                                                </x-base.input-group>
                                            </div>
                                            @error('driver_price')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Vehicle Price --}}
                                    <div class="flex-col block pt-5 mt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                        <div class="inline-block mb-2 sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                            <div class="text-left">
                                                <div class="flex items-center">
                                                    <div class="font-medium">Vehicle Price</div>
                                                    <div
                                                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                        Required for Individual
                                                    </div>
                                                </div>
                                                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                    Price per vehicle
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-1 w-full mt-3 xl:mt-0">
                                            <div class="grid-cols-1 gap-2 sm:grid">
                                                <x-base.input-group>
                                                    <x-base.input-group.text>$</x-base.input-group.text>
                                                    <x-base.form-input type="number" step="0.01" name="vehicle_price" id="vehicle_price"
                                                        value="{{ old('vehicle_price') }}" placeholder="Price per vehicle" />
                                                </x-base.input-group>
                                            </div>
                                            @error('vehicle_price')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MAX --}}
                            <div class="flex-col block pt-5 mt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="inline-block mb-2 sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Units allowed for the plan</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter the values you want the plan to have                          
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1 w-full mt-3 xl:mt-0">
                                    <div class="grid-cols-3 gap-2 sm:grid">
                                        <x-base.input-group>
                                            <x-base.input-group.text>$</x-base.input-group.text>
                                            <x-base.form-input class="w-full" type="number" name="max_carrier" id="max_carrier"
                                                value="{{ old('max_carrier') }}" placeholder="Max Carrier" />
                                        </x-base.input-group>
                                        <x-base.input-group>
                                            <x-base.input-group.text>$</x-base.input-group.text>
                                            <x-base.form-input class="w-full" type="number" name="max_drivers" id="max_drivers"
                                                value="{{ old('max_drivers') }}" placeholder="Max Driver" />
                                        </x-base.input-group>
                                        <x-base.input-group>
                                            <x-base.input-group.text>$</x-base.input-group.text>
                                            <x-base.form-input class="w-full" type="number" name="max_vehicles" id="max_vehicles"
                                                value="{{ old('max_vehicles') }}" placeholder="Max Vehicles" />
                                        </x-base.input-group>
                                    </div>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="font-medium">Status</div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="flex items-center" x-data="{ isActive: {{ old('status', $membership->status ?? 0) ? 'true' : 'false' }} }">
                                        <input type="checkbox" name="status" id="status"
                                            class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50 w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white"
                                            value="1" x-bind:checked="isActive" x-on:change="isActive = !isActive">
                                        <label for="status" class="ml-3"
                                            x-text="isActive ? 'Active' : 'Inactive'"></label>
                                    </div>
                                    @error('status')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <!-- Show in Register Toggle -->
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center" x-data="{ showInRegister: true }">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Show in Registration</div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Display this plan in the public registration form
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="show_in_register" id="show_in_register"
                                            class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:border-darkmode-800 dark:bg-darkmode-600 dark:focus:ring-slate-700 dark:focus:ring-opacity-50 w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white"
                                            value="1" x-bind:checked="showInRegister" x-on:change="showInRegister = !showInRegister">
                                        <label for="show_in_register" class="ml-3"
                                            x-text="showInRegister ? 'Visible in Registration' : 'Hidden from Registration'"></label>
                                    </div>
                                    @error('show_in_register')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end">
                            <x-base.button type="submit" class="w-full border-primary/50 px-10 md:w-auto"
                                variant="outline-primary">
                                <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                                Save Membership
                            </x-base.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deletePhotoButton = document.querySelector('#deletePhotoButton');

            if (!deletePhotoButton) {
                console.warn('Delete button not found in Create form.');
                return;
            }

            deletePhotoButton.addEventListener('click', function(event) {
                event.preventDefault();
                console.log('No photo to delete in Create form.');
            });

            // Asegurar que Alpine.js actualice la visibilidad de los campos cuando cambie el tipo de precio
            const pricingTypeSelect = document.getElementById('pricing_type');
            if (pricingTypeSelect) {
                pricingTypeSelect.addEventListener('change', function() {
                    // Forzar a Alpine.js a actualizar la visibilidad
                    const event = new Event('input', { bubbles: true });
                    pricingTypeSelect.dispatchEvent(event);
                });
            }
        });
    </script>
@endpush
