@extends('../themes/' . $activeTheme)
@section('title', 'Edit Carrier ' . $carrier->name)
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Edit Carrier ' . $carrier->name, 'active' => true],
    ];
@endphp

@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce

@section('subcontent')

    <x-base.notificationtoast.notification-toast :notification="session('notification')" />

    @if (isset($notification))
        <div class="alert alert-{{ $notification['type'] }} alert-dismissible fade show" role="alert">
            <strong>{{ $notification['message'] }}</strong>
            @if (isset($notification['details']))
                <p class="mb-0">{{ $notification['details'] }}</p>
            @endif
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    {{-- TABS --}}
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap md:flex-row flex-col text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                            <!-- Tab Carrier -->
                            <li class="flex-grow">
                                <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                                    class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                                    {{ request()->routeIs('admin.carrier.edit') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">
                                    <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.edit') ? 'text-primary dark:text-primary' : '' }}"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M18 20a6 6 0 0 0-12 0" />
                                        <circle cx="12" cy="10" r="4" />
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                    Profile Carrier
                                </a>
                            </li>
                            <!-- Tab Users -->
                            <li class="flex-grow">
                                <a href="{{ route('admin.carrier.user_carriers.index', $carrier->slug) }}"
                                    class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                                    {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-blue-600 dark:text-primary dark:border-primary' : '' }}">
                                    <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary dark:text-primary' : '' }}"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    Users
                                </a>
                            </li>
                            <!-- Tab Drivers -->
                            <li class="flex-grow">
                                <a href="{{ route('admin.carrier.user_drivers.index', $carrier->slug) }}"
                                    class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group 
                                    {{ request()->routeIs('admin.carrier.user_drivers.*') ? 'text-primary border-primary ' : '' }}">
                                    <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_drivers.*') ? 'text-primary dark:text-primary' : '' }}"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="16" height="16" x="4" y="4" rx="2" />
                                        <path d="M12 3v18" />
                                        <path d="M3 12h18" />
                                        <path d="m13 8-2-2-2 2" />
                                        <path d="m13 16-2 2-2-2" />
                                        <path d="m8 13-2-2 2-2" />
                                        <path d="m16 13 2-2-2-2" />
                                    </svg>
                                    Drivers
                                </a>
                            </li>
                            <!-- Tab Documents -->
                            <li class="flex-grow">
                                <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                    class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                                    {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-blue-600 dark:text-primary dark:border-primary' : '' }}">
                                    <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.documents') ? 'text-primary dark:text-primary' : '' }}"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                        <path d="m3 15 2 2 4-4" />
                                    </svg>
                                    Documents
                                </a>
                            </li>
                        </ul>
                    </div>
                    <form action="{{ route('admin.carrier.update', $carrier) }}" method="POST"
                        enctype="multipart/form-data" id="userForm">
                        @csrf
                        @method('PUT')
                        <div class="p-7">
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
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="flex items-center">
                                        <x-image-preview name="logo_carrier" id="logo_carrier_input"
                                            currentPhotoUrl="{{ $carrier->getFirstMediaUrl('logo_carrier') ?? asset('build/default_profile.png') }}"
                                            defaultPhotoUrl="{{ asset('build/default_profile.png') }}"
                                            deleteUrl="{{ route('admin.carrier.delete-photo', ['carrier' => $carrier->id]) }}" />
                                    </div>
                                </div>
                            </div>

                            <!-- REFER TOKEN -->
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
                                    <div class="flex items-center">
                                        <x-base.form-input name="referrer_token" type="text" id="referrer_token"
                                            readonly value="{{ old('referrer_token', $carrier->referrer_token) }}"
                                            class="w-full bg-gray-100 cursor-not-allowed" />
                                        <button type="button" id="regenerateToken"
                                            class="ml-4 bg-blue-500 text-white px-3 py-1 rounded-md">
                                            Regenerate
                                        </button>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-1">
                        <small class="text-gray-500 flex-1">
                            Example: <span id="driver-url" class="select-all">{{ url("/driver/register/{$carrier->slug}?token={$carrier->referrer_token}") }}</span>
                        </small>
                        <button type="button" id="copyUrlBtn" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200" title="Copy URL">
                            <svg id="copyIcon" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span id="copyText" class="ml-1">Copy</span>
                        </button>
                    </div>
                                </div>
                            </div>

                            <!-- Full Name -->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                    <x-base.form-input name="name" type="text" placeholder="Enter full name"
                                        id="name" value="{{ old('name', $carrier->name) }}" />
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Address -->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Carrier Address</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter your Address
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.form-input name="address" type="text" placeholder="Enter full Address"
                                        id="address" value="{{ old('address', $carrier->address) }}" />
                                    @error('address')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- State-->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                            Select a State
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <select data-tw-merge aria-label="Default select example"
                                        class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2"
                                        name="state" id="state">
                                        <option value="">Select a State</option>
                                        @foreach ($usStates as $abbr => $name)
                                            <option value="{{ $abbr }}"
                                                {{ old('state', $carrier->state ?? '') == $abbr ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>


                            <!-- Zip Code-->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Zip Code</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter your Address
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.input-group>
                                        <x-base.input-group.text>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search stroke-[1] h-[18px] w-[18px]">
                                                <path
                                                    d="M15 22a1 1 0 0 1-1-1v-4a1 1 0 0 1 .445-.832l3-2a1 1 0 0 1 1.11 0l3 2A1 1 0 0 1 22 17v4a1 1 0 0 1-1 1z" />
                                                <path
                                                    d="M18 10a8 8 0 0 0-16 0c0 4.993 5.539 10.193 7.399 11.799a1 1 0 0 0 .601.2" />
                                                <path d="M18 22v-3" />
                                                <circle cx="10" cy="10" r="3" />
                                            </svg></x-base.input-group.text>
                                        <x-base.form-input class="w-full" type="text" name="zipcode" id="zipcode"
                                            value="{{ old('zipcode', $carrier->zipcode) }}" placeholder="ZIP Code" />
                                    </x-base.input-group>
                                    @error('zipcode')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- EIN Number-->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                            Enter your Address
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.input-group>
                                        <x-base.input-group.text>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search stroke-[1] h-[18px] w-[18px]">
                                                <line x1="4" x2="20" y1="9" y2="9" />
                                                <line x1="4" x2="20" y1="15" y2="15" />
                                                <line x1="10" x2="8" y1="3" y2="21" />
                                                <line x1="16" x2="14" y1="3" y2="21" />
                                            </svg>
                                        </x-base.input-group.text>
                                        <x-base.form-input class="w-full" type="text" name="ein_number"
                                            id="ein_number" value="{{ old('ein_number', $carrier->ein_number) }}"
                                            placeholder="EIN Number" />
                                    </x-base.input-group>
                                    @error('ein_number')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Dot Number-->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">DOT Number</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Optional
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter your Address
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.input-group>
                                        <x-base.input-group.text>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search stroke-[1] h-[18px] w-[18px]">
                                                <line x1="4" x2="20" y1="9" y2="9" />
                                                <line x1="4" x2="20" y1="15" y2="15" />
                                                <line x1="10" x2="8" y1="3" y2="21" />
                                                <line x1="16" x2="14" y1="3" y2="21" />
                                            </svg>
                                        </x-base.input-group.text>
                                        <x-base.form-input class="w-full" type="text" name="dot_number"
                                            id="dot_number" value="{{ old('dot_number', $carrier->dot_number) }}"
                                            placeholder="DOT Number" />
                                    </x-base.input-group>
                                    @error('dot_number')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- MC Number-->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">MC Number</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Optional
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter your MC Number
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.input-group>
                                        <x-base.input-group.text>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search stroke-[1] h-[18px] w-[18px]">
                                                <line x1="4" x2="20" y1="9" y2="9" />
                                                <line x1="4" x2="20" y1="15" y2="15" />
                                                <line x1="10" x2="8" y1="3" y2="21" />
                                                <line x1="16" x2="14" y1="3" y2="21" />
                                            </svg>
                                        </x-base.input-group.text>
                                        <x-base.form-input class="w-full" type="text" name="mc_number" id="mc_number"
                                            value="{{ old('mc_number', $carrier->mc_number) }}"
                                            placeholder="MC Number" />
                                    </x-base.input-group>
                                    @error('mc_number')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- State DOT-->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">State DOT</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Optional
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter your MC Number
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.input-group>
                                        <x-base.input-group.text>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search stroke-[1] h-[18px] w-[18px]">
                                                <path d="M2 6h4" />
                                                <path d="M2 10h4" />
                                                <path d="M2 14h4" />
                                                <path d="M2 18h4" />
                                                <rect width="16" height="20" x="4" y="2" rx="2" />
                                                <path d="M15 2v20" />
                                                <path d="M15 7h5" />
                                                <path d="M15 12h5" />
                                                <path d="M15 17h5" />
                                            </svg>
                                        </x-base.input-group.text>
                                        <x-base.form-input class="w-full" type="text" name="state_dot" id="state_dot"
                                            value="{{ old('state_dot', $carrier->state_dot) }}"
                                            placeholder="State DOT" />
                                    </x-base.input-group>
                                    @error('state_dot')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- IFTA -->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">IFTA</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Optional
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Please provide a valid email address that you have access
                                            to.
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.input-group>
                                        <x-base.input-group.text>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search stroke-[1] h-[18px] w-[18px]">
                                                <path d="M18 20a6 6 0 0 0-12 0" />
                                                <circle cx="12" cy="10" r="4" />
                                                <circle cx="12" cy="12" r="10" />
                                            </svg>
                                        </x-base.input-group.text>
                                        <x-base.form-input class="w-full" type="text" name="ifta_account"
                                            id="ifta_account" value="{{ old('ifta_account', $carrier->ifta_account) }}"
                                            placeholder="Enter IFTA account" />
                                    </x-base.input-group>

                                </div>
                            </div>

                            <!-- Membership -->
                            <div
                                class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                        id="id_plan" name="id_plan">
                                        <option value="">Select a Membership Plan</option>
                                        @foreach ($memberships as $membership)
                                            <option value="{{ $membership->id }}"
                                                {{ old('id_plan', $carrier->id_plan) == $membership->id ? 'selected' : '' }}>
                                                {{ $membership->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_plan')
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
                                    <div class="mt-3 w-full flex-1 xl:mt-0">
                                        <select data-tw-merge aria-label="Default select example"
                                            class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2"
                                            id="status" name="status">
                                            <option value="{{ App\Models\Carrier::STATUS_PENDING }}"
                                                {{ old('status', $carrier->status) == App\Models\Carrier::STATUS_PENDING ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="{{ App\Models\Carrier::STATUS_ACTIVE }}"
                                                {{ old('status', $carrier->status) == App\Models\Carrier::STATUS_ACTIVE ? 'selected' : '' }}>
                                                Active</option>
                                            <option value="{{ App\Models\Carrier::STATUS_INACTIVE }}"
                                                {{ old('status', $carrier->status) == App\Models\Carrier::STATUS_INACTIVE ? 'selected' : '' }}>
                                                Inactive</option>
                                        </select>
                                        @error('status')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col sm:flex-row gap-3 border-t border-slate-200/80 px-7 py-5 md:justify-end">
                            <x-base.button type="submit" class="w-full border-primary/50 px-10 md:w-auto"
                                variant="outline-primary">
                                <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                                Save Carrier
                            </x-base.button>

                            <x-base.button as="a" href="{{ route('admin.carrier.index') }}"
                                class="w-full border-primary/50 px-10 md:w-auto" variant="outline-primary">
                                <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                                Cancel
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
        document.addEventListener('DOMContentLoaded', function() {
            

            // Regenerate Token functionality
            const regenerateTokenBtn = document.getElementById('regenerateToken');
            const tokenField = document.getElementById('referrer_token');
            
            if (regenerateTokenBtn && tokenField) {
                regenerateTokenBtn.addEventListener('click', function() {
                    tokenField.value = Math.random().toString(36).substring(2, 18);
                    
                });
            } else {
                console.error('Regenerate token elements not found');
            }

            // Copy URL functionality
            const copyUrlBtn = document.getElementById('copyUrlBtn');
            const urlElement = document.getElementById('driver-url');
            const copyText = document.getElementById('copyText');
            const copyIcon = document.getElementById('copyIcon');
            
            if (copyUrlBtn && urlElement && copyText && copyIcon) {
                copyUrlBtn.addEventListener('click', function() {
                    
                    
                    // Get the URL text
                    const urlText = urlElement.textContent.trim();
                    
                    
                    // Copy to clipboard
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(urlText).then(function() {                            
                            showCopySuccess();
                        }).catch(function(err) {
                            console.error('Clipboard API failed:', err);
                            fallbackCopy(urlText);
                        });
                    } else {                        
                        fallbackCopy(urlText);
                    }
                });
            } else {
                console.error('Copy URL elements not found:', {
                    copyUrlBtn: copyUrlBtn ? 'found' : 'missing',
                    urlElement: urlElement ? 'found' : 'missing',
                    copyText: copyText ? 'found' : 'missing',
                    copyIcon: copyIcon ? 'found' : 'missing'
                });
            }
            
            function showCopySuccess() {
                // Success - show copied state
                copyText.textContent = 'Copied!';
                copyUrlBtn.classList.add('text-green-600', 'bg-green-50', 'border-green-300');
                copyUrlBtn.classList.remove('text-gray-500', 'bg-white', 'border-gray-300');
                
                // Change icon to checkmark
                copyIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                
                // Reset after 2 seconds
                setTimeout(function() {
                    copyText.textContent = 'Copy';
                    copyUrlBtn.classList.remove('text-green-600', 'bg-green-50', 'border-green-300');
                    copyUrlBtn.classList.add('text-gray-500', 'bg-white', 'border-gray-300');
                    
                    // Reset icon to copy
                    copyIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>';
                }, 2000);
            }
            
            function fallbackCopy(text) {
                // Try alternative method for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {                        
                        showCopySuccess();
                    } else {
                        throw new Error('execCommand returned false');
                    }
                } catch (fallbackErr) {
                    console.error('Fallback copy failed:', fallbackErr);
                    copyText.textContent = 'Failed';
                    setTimeout(function() {
                        copyText.textContent = 'Copy';
                    }, 2000);
                }
                
                document.body.removeChild(textArea);
            }


        });
    </script>
@endPushOnce

@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
@endPushOnce
