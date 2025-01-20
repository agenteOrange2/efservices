@extends('../themes/' . $activeTheme)
@section('title', 'Edit Driver: ' . $userDriver->user->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Edit Driver', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <div class="box-body">
                        <form action="{{ route('admin.carrier.user_drivers.update', [$carrier, $userDriver]) }}" 
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="p-7">
                                <input type="hidden" name="carrier_id" value="{{ $carrier->id }}">

                                {{-- Photo --}}
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                            <x-image-preview 
                                            name="profile_photo_driver" 
                                            id="profile_photo_driver_input"
                                            currentPhotoUrl="{{ $userDriver->getFirstMedia('profile_photo_driver')?->getUrl() }}"
                                            defaultPhotoUrl="{{ asset('build/default_profile.png') }}"
                                            deleteUrl="{{ route('admin.carrier.user_drivers.delete-photo', ['carrier' => $carrier, 'userDriverDetail' => $userDriver]) }}" 
                                        />
                                        </div>
                                    </div>
                                </div>

                                <!-- Name Fields -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Full Name</div>
                                                <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <x-base.form-input name="name" type="text"
                                                    placeholder="Enter full name" value="{{ old('name', $userDriver->user->name) }}" />
                                                @error('name')
                                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Email</div>
                                                <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="email" type="email" 
                                            placeholder="Enter email"
                                            value="{{ old('email', $userDriver->user->email) }}" />
                                        @error('email')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Password (opcional en edición) -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Change Password</div>
                                                <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Optional
                                                </div>
                                            </div>
                                            <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                                Leave blank to keep current password
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="password" type="password" 
                                            placeholder="Enter new password if you want to change it" />
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
                                                <div class="font-medium">Confirm New Password</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="password_confirmation" type="password"
                                            placeholder="Confirm new password" />
                                    </div>
                                </div>

                                <!-- Phone Number -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Phone Number</div>
                                                <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="phone" type="text"
                                            value="{{ old('phone', $userDriver->phone) }}"
                                            placeholder="(XXX) XXX-XXXX" />
                                        @error('phone')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- License Number -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">License Number</div>
                                                <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="license_number" type="text"
                                            value="{{ old('license_number', $userDriver->license_number) }}"
                                            class="license-mask" />
                                        @error('license_number')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Birth Date -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Birth Date</div>
                                                <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="birth_date" type="date"
                                            value="{{ old('birth_date', $userDriver->birth_date) }}"
                                            max="{{ now()->subYears(18)->format('Y-m-d') }}" />
                                        @error('birth_date')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Years Experience -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Years of Experience</div>
                                                <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                    Required
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <x-base.form-input name="years_experience" type="number"
                                            min="0" max="50"
                                            value="{{ old('years_experience', $userDriver->years_experience) }}" />
                                        @error('years_experience')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                    <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                        <div class="text-left">
                                            <div class="flex items-center">
                                                <div class="font-medium">Status</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <select name="status" class="form-select">
                                            <option value="1" {{ old('status', $userDriver->status) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $userDriver->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                            <option value="2" {{ old('status', $userDriver->status) == 2 ? 'selected' : '' }}>Pending</option>
                                        </select>
                                        @error('status')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end">
                                    <x-base.button type="submit" class="w-full border-primary/50 px-10 md:w-auto"
                                        variant="outline-primary">
                                        <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Save" />
                                        Update Driver
                                    </x-base.button>

                                    <x-base.button as="a" 
                                    href="{{ route('admin.carrier.user_drivers.index', $carrier) }}"
                                    class="w-full border-primary/50 px-10 md:w-auto ml-2" 
                                    variant="outline-secondary">
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
<!-- Incluir IMask para las máscaras -->
<script src="https://unpkg.com/imask"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara para el teléfono
        const phoneMask = IMask(document.querySelector('input[name="phone"]'), {
            mask: '(000) 000-0000'
        });

        // Máscara para la licencia
        const licenseMask = IMask(document.querySelector('input[name="license_number"]'), {
            mask: 'AA-000000'
        });
    });
</script>
@endpush

@pushOnce('scripts')
@vite('resources/js/app.js')
@vite('resources/js/pages/notification.js')
@endPushOnce