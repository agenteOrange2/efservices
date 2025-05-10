@extends('../themes/' . $activeTheme)
@section('title', 'Create Carrier')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Create Carrier', 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <div class="tabs">
                        <ul class="border-b border-slate-200 w-full flex">
                            <li class="visible:outline-none flex-1 -mb-px">
                                <a class="cursor-pointer block appearance-none px-3 py-2 border border-transparent text-slate-600 transition-colors [&amp;.active]:text-slate-700 [&amp;.active]:dark:text-white block border-transparent rounded-t-md [&amp;.active]:bg-white [&amp;.active]:border-slate-200 [&amp;.active]:border-b-transparent [&amp;.active]:font-medium [&amp;.active]:dark:bg-transparent [&amp;.active]:dark:border-t-darkmode-400 [&amp;.active]:dark:border-b-darkmode-600 [&amp;.active]:dark:border-x-darkmode-400 [&amp;:not(.active)]:hover:bg-slate-100 [&amp;:not(.active)]:dark:hover:bg-darkmode-400 [&amp;:not(.active)]:dark:hover:border-transparent active w-full py-2 {{ request()->routeIs('admin.carrier.create') ? 'active' : '' }}"
                                    href="{{ route('admin.carrier.create') }}">
                                    Carrier
                                </a>
                            </li>
                            <li class="visible:outline-none flex-1 -mb-px">
                                <a class="cursor-pointer appearance-none px-3 border  text-slate-600 transition-colors [&amp;.active]:text-slate-700 [&amp;.active]:dark:text-white block border-transparent rounded-t-md [&amp;.active]:bg-gray-200  [&amp;.active]:border-slate-200 [&amp;.active]:border-b-transparent [&amp;.active]:font-medium [&amp;.active]:dark:bg-transparent [&amp;.active]:dark:border-t-darkmode-400 [&amp;.active]:dark:border-b-darkmode-600 [&amp;.active]:dark:border-x-darkmode-400 [&amp;:not(.active)]:hover:bg-slate-100 [&amp;:not(.active)]:dark:hover:bg-darkmode-400 [&amp;:not(.active)]:dark:hover:border-transparent active w-full py-2 {{ isset($carrier) ? '' : 'disabled pointer-events-none' }}"
                                    href="{{ isset($carrier) ? route('admin.carrier.edit', $carrier->id) : '#' }}">
                                    Users
                                </a>
                            </li>
                            <li class="visible:outline-none flex-1 -mb-px">
                                <a class="cursor-pointer appearance-none px-3 border  text-slate-600 transition-colors [&amp;.active]:text-slate-700 [&amp;.active]:dark:text-white block border-transparent rounded-t-md [&amp;.active]:bg-gray-200  [&amp;.active]:border-slate-200 [&amp;.active]:border-b-transparent [&amp;.active]:font-medium [&amp;.active]:dark:bg-transparent [&amp;.active]:dark:border-t-darkmode-400 [&amp;.active]:dark:border-b-darkmode-600 [&amp;.active]:dark:border-x-darkmode-400 [&amp;:not(.active)]:hover:bg-slate-100 [&amp;:not(.active)]:dark:hover:bg-darkmode-400 [&amp;:not(.active)]:dark:hover:border-transparent active w-full py-2 {{ isset($carrier) ? '' : 'disabled pointer-events-none' }}"
                                    href="{{ isset($carrier) ? route('admin.carrier.documents', $carrier->id) : '#' }}">
                                    Documents
                                </a>
                            </li>
                        </ul>
                    </div>
                    <form action="{{ route('admin.carrier.store') }}" method="POST" enctype="multipart/form-data"
                        id="userForm">
                        @csrf
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
                                        <x-image-preview name="logo_carrier" id="logo_carrier"
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
                                        id="name" value="{{ old('name') }}" />
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <!-- Address -->
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                        id="address" value="{{ old('address') }}" />
                                    @error('address')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- State-->
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
                                            Select a State
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
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
                            </div>

                            <!-- Zip Code-->
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
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
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search stroke-[1] h-[18px] w-[18px]">
                                                <path
                                                    d="M15 22a1 1 0 0 1-1-1v-4a1 1 0 0 1 .445-.832l3-2a1 1 0 0 1 1.11 0l3 2A1 1 0 0 1 22 17v4a1 1 0 0 1-1 1z" />
                                                <path
                                                    d="M18 10a8 8 0 0 0-16 0c0 4.993 5.539 10.193 7.399 11.799a1 1 0 0 0 .601.2" />
                                                <path d="M18 22v-3" />
                                                <circle cx="10" cy="10" r="3" />
                                            </svg></x-base.input-group.text>
                                        <x-base.form-input class="w-full" type="text" name="zipcode" id="zipcode"
                                            value="{{ old('zipcode') }}" placeholder="ZIP Code" />
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
                                            id="ein_number" value="{{ old('ein_number') }}" placeholder="EIN Number" />
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
                                        <x-base.form-input class="w-full" type="text" name="dot_number"
                                            id="dot_number" value="{{ old('dot_number') }}" placeholder="DOT Number" />
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
                                                Required
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
                                            value="{{ old('mc_number') }}" placeholder="MC Number" />
                                    </x-base.input-group>
                                    @error('mc_number')
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
                                            <div class="font-medium">State DOT</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
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
                                            value="{{ old('state_dot') }}" placeholder="State DOT" />
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
                                            id="ifta_account" value="{{ old('ifta_account') }}"
                                            placeholder="Enter IFTA account" />
                                    </x-base.input-group>

                                </div>
                            </div>

                            <!-- Membership-->
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
                                                {{ old('id_plan') == $membership->id ? 'selected' : '' }}>
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
