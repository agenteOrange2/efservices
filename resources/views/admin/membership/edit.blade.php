@extends('../themes/' . $activeTheme)
@section('title', 'Edit Membership ' . $membership->name)
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Membership', 'url' => route('admin.membership.index')],
        ['label' => 'Edit '. $membership->name, 'active' => true],
    ];
@endphp
@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce

@section('subcontent')
<x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <form action="{{ route('admin.membership.update', $membership->id) }}" method="POST" enctype="multipart/form-data" id="userForm">
                        @csrf
                        @method('PUT')
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
                                        <x-image-preview
                                        name="image_membership"
                                        id="image_membership_input"
                                        currentPhotoUrl="{{ $membership->getFirstMediaUrl('image_membership') ?? asset('build/default_profile.png') }}"
                                        defaultPhotoUrl="{{ asset('build/default_profile.png') }}"
                                        deleteUrl="{{ route('admin.membership.delete-photo', ['membership' => $membership->id]) }}" />
                                    
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
                                    <x-base.form-input name="name" type="text"
                                        placeholder="Enter full name membership" id="name"
                                        value="{{ old('name', $membership->name) }}" />
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <!-- Email -->
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
                                    <textarea class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10"
                                     name="description" type="description"
                                        placeholder="Enter description" id="description">{{ old('description', $membership->description) }}</textarea>
                                    @error('description')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Price --}}
                            <div class="flex-col block pt-5 mt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="inline-block mb-2 sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Price</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Please enter the price of membership
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1 w-full mt-3 xl:mt-0">
                                    <div class="grid-cols-1 gap-2 sm:grid">
                                        <x-base.input-group>
                                            <x-base.input-group.text>$</x-base.input-group.text>
                                            <x-base.form-input class="w-full" type="number" name="price" id="price"
                                                value="{{ old('price', $membership->price) }}" step="0.01" placeholder="Price USD" />
                                        </x-base.input-group>
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
                                                value="{{ old('max_carrier', $membership->max_carrier) }}" placeholder="Max Carrier" />
                                        </x-base.input-group>
                                        <x-base.input-group>
                                            <x-base.input-group.text>$</x-base.input-group.text>
                                            <x-base.form-input class="w-full" type="number" name="max_drivers" id="max_drivers"
                                                value="{{ old('max_drivers', $membership->max_drivers) }}" placeholder="Max Driver" />
                                        </x-base.input-group>
                                        <x-base.input-group>
                                            <x-base.input-group.text>$</x-base.input-group.text>
                                            <x-base.form-input class="w-full" type="number" name="max_vehicles" id="max_vehicles"
                                                value="{{ old('max_vehicles', $membership->max_vehicles) }}" placeholder="Max Vehicles" />
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
                                    <div x-data="{ isActive: {{ $membership->status ? 'true' : 'false' }} }" class="flex items-center">
                                        <input type="checkbox" name="status" id="status"
                                            class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50 w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white"
                                            value="1" x-on:change="isActive = !isActive" x-bind:checked="isActive">
                                        <label for="status" class="ml-3"
                                            x-text="isActive ? 'Active' : 'Inactive'"></label>
                                    </div>
                                    @error('status')
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
                                Update Membership
                            </x-base.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@pushOnce('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deletePhotoButton = document.getElementById('deletePhotoButton');
        deletePhotoButton.addEventListener('click', function (event) {
            event.preventDefault();
            fetch('{{ route('admin.membership.delete-photo', ['membership' => $membership->id]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to delete the photo.');
                return response.json();
            })
            .then(data => {
                // Actualiza la vista
                document.querySelector('[x-data]').__x.$data.originalPhoto = data.defaultPhotoUrl;
                document.querySelector('[x-data]').__x.$data.photoPreview = null;
                console.log('Photo deleted successfully.');
            })
            .catch(error => console.error('Error:', error));
        });
    });
</script>
@endPushOnce