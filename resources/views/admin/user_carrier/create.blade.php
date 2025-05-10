@extends('../themes/' . $activeTheme)
@section('title', 'Add User Carrier for Carrier: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],

        ['label' => 'Create User Carrier', 'active' => true],
    ];
@endphp

@section('subcontent')
    {{-- <div class="box-header">
            <h3 class="box-title">Add User Carrier for Carrier: {{ $carrier->name }}</h3>
        </div> --}}


    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <div class="box-body">
                        <form action="{{ route('admin.carrier.user_carriers.store', $carrier) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="p-7">

                                <input type="hidden" name="carrier_id" value="{{ $carrier->id }}">

                                {{-- Photo --}}
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Profile Photo</div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Upload a clear and recent profile photo.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <div class="flex items-center">
                                            <x-image-preview name="profile_photo_carrier" id="profile_photo_carrier_input"
                                                currentPhotoUrl="{{ null }}"
                                                defaultPhotoUrl="{{ asset('build/default_profile.png') }}"
                                                deleteUrl="{{ null }}" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Full Name -->
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
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Enter your full legal name as it appears on your official
                                                identification.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="name" type="text" placeholder="Enter full name"
                                            id="name" value="{{ old('name') }}" />
                                        @error('name')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Email -->
                                <div
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
                                                Please provide a valid email address that you have access
                                                to.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="email" type="email" placeholder="Enter email"
                                            id="email" value="{{ old('email') }}" />
                                        @error('email')
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
                                        <x-base.form-input name="password" type="password" placeholder="Enter password" />
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
                                        <x-base.form-input name="password_confirmation" type="password"
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
                                {{-- Phone Number --}}
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Phone Number</div>
                                                <div
                                                    class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Please provide a valid phone number where we can reach you
                                                if needed.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <div class="flex flex-col items-center md:flex-row">
                                            <x-base.form-input
                                                class="first:rounded-b-none last:-mt-px last:rounded-t-none focus:z-10 first:md:rounded-r-none first:md:rounded-bl-md last:md:-ml-px last:md:mt-0 last:md:rounded-l-none last:md:rounded-tr-md [&:not(:first-child):not(:last-child)]:-mt-px [&:not(:first-child):not(:last-child)]:rounded-none [&:not(:first-child):not(:last-child)]:md:-ml-px [&:not(:first-child):not(:last-child)]:md:mt-0"
                                                type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                                placeholder="+1 (123) 456-7890" />
                                            {{-- <x-base.form-select
                                            class="first:rounded-b-none last:-mt-px last:rounded-t-none focus:z-10 md:w-36 first:md:rounded-r-none first:md:rounded-bl-md last:md:-ml-px last:md:mt-0 last:md:rounded-l-none last:md:rounded-tr-md [&:not(:first-child):not(:last-child)]:-mt-px [&:not(:first-child):not(:last-child)]:rounded-none [&:not(:first-child):not(:last-child)]:md:-ml-px [&:not(:first-child):not(:last-child)]:md:mt-0">
                                            <option value="office">Office</option>
                                            <option value="home">Home</option>
                                        </x-base.form-select> --}}
                                        </div>
                                        {{-- <a class="-mb-1 mt-3.5 flex items-center font-medium text-primary" href="">
                                        <x-base.lucide class="mr-1 h-4 w-4 stroke-[1.3]" icon="Plus" />
                                        Add phone
                                    </a> --}}
                                    </div>
                                </div>
                                <!-- Job Position -->
                                <div
                                    class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Job Position</div>
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
                                        <x-base.form-input name="job_position" type="text"
                                            placeholder="Example: Engineer," id="job_position"
                                            value="{{ old('job_position') }}" />
                                        @error('job_position')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Status -->
                                <div class="my-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="font-medium">Status</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <select data-tw-merge aria-label="Default select example"
                                            class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2"
                                            id="status" name="status">
                                            <option value="{{ App\Models\Carrier::STATUS_PENDING }}"
                                                {{ old('status') == App\Models\Carrier::STATUS_PENDING ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="{{ App\Models\Carrier::STATUS_ACTIVE }}"
                                                {{ old('status') == App\Models\Carrier::STATUS_ACTIVE ? 'selected' : '' }}>
                                                Active</option>
                                            <option value="{{ App\Models\Carrier::STATUS_INACTIVE }}"
                                                {{ old('status') == App\Models\Carrier::STATUS_INACTIVE ? 'selected' : '' }}>
                                                Inactive</option>
                                        </select>
                                        @error('status')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Submit Button -->
                                <div class="flex flex-col sm:flex-row gap-3 border-t border-slate-200/80 px-7 py-5 md:justify-end">
                                    <x-base.button type="submit" class="w-full border-primary/50 px-10 md:w-auto"
                                        variant="outline-primary">
                                        <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                                        Save User
                                    </x-base.button>
                                    
                                    <x-base.button as="a" href="{{ route('admin.carrier.user_carriers.index', $carrier) }}"
                                    class="w-full border-primary/50 px-10 md:w-auto" variant="outline-primary">
                                    <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                                    Cancel
                                </x-base.button>
                                </div>
                            </div>
                        </form>
                    </div>
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
        });
    </script>
@endpush


@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
@endPushOnce
