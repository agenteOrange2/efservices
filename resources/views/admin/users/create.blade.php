@extends('../themes/' . $activeTheme)

@section('title', 'New Users')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => 'New User', 'active' => true],
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
                    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" id="userForm">
                        @csrf
                        <div class="p-7">
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                        <x-image-preview name="profile_photo" id="profile_photo_input"
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
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                            <!-- Status -->
                            <div class="mt-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="font-medium">Status</div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="flex items-center" x-data="{ isActive: {{ old('status', $user->status ?? 0) ? 'true' : 'false' }} }">
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

                            <!-- Add this section to your create user form, before the submit button -->
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">User Roles</div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Assign one or more roles to this user.
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach ($roles as $role)
                                            <div class="flex items-center">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                    id="role_{{ $role->id }}"
                                                    {{ is_array(old('roles')) && in_array($role->id, old('roles')) ? 'checked' : '' }}
                                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                                <label for="role_{{ $role->id }}"
                                                    class="text-sm">{{ $role->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('roles')
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
                                Save User
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
        });
    </script>
@endpush


@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
@endPushOnce
