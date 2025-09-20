@extends('../themes/' . $activeTheme)
@section('title', 'Driver Carriers for: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Driver Carriers: ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')

<x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="mb-10">
                @if ($currentDrivers < $maxDrivers)
                    <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                        <div class="text-base font-medium group-[.mode--light]:text-white">
                            <h2 class="text-2xl">Driver for Carrier: <span>{{ $carrier->name }}</span></h2>
                        </div>
                        <!-- Add New Driver Button -->                        
                        <div class="md:ml-auto text-right">
                            <div x-data="{open: false}" class="relative inline-block">

                                <button @click="open = !open" type="button" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary ">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                        Add New Driver
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 ml-2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                    </div>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                                    <div class="bg-white rounded-md shadow-lg border border-slate-200">
                                        <div class="p-2 text-xs font-medium text-slate-500 border-b border-slate-200/60 bg-slate-50">
                                            Driver Registration Options
                                        </div>
                                        <div class="p-2">
                                            <a href="{{ route('admin.carrier.user_drivers.create', $carrier->slug) }}" 
                                               class="flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-100 hover:text-primary">
                                                <div class="flex items-center justify-center w-8 h-8 mr-2 text-white rounded-full bg-primary/90">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                                </div>
                                                <div>
                                                    <div class="font-medium">Quick Register</div>
                                                    <div class="text-xs text-slate-500 mt-0.5">Simple driver registration</div>
                                                </div>
                                            </a>
                                            {{-- <a href="{{ route('admin.carrier.user_drivers.application.step1', $carrier->slug) }}" 
                                               class="flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-100 hover:text-primary">
                                                <div class="flex items-center justify-center w-8 h-8 mr-2 text-white rounded-full bg-success/90">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="m9 14 2 2 4-4"></path></svg>
                                                </div>
                                                <div>
                                                    <div class="font-medium">Full Application</div>
                                                    <div class="text-xs text-slate-500 mt-0.5">Complete driver onboarding</div>
                                                </div>
                                            </a> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="w-full mb-10">
                        <div role="alert"
                            class="alert relative border rounded-md px-5 py-4 bg-primary border-primary text-white dark:border-primary">
                            <div class="flex items-center">
                                <div class="text-lg font-medium">
                                    Max Drivers Reached
                                </div>
                                <div class="ml-auto rounded-md bg-white px-1 text-xs text-slate-700">
                                    Notice
                                </div>
                            </div>
                            <div class="mt-3">
                                You have exceeded your driver limit, if you need more drivers, please upgrade your plan
                                or contact the administration to upgrade your plan.
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if (session('exceeded_limit'))
                <!-- Modal -->
                <div id="limitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full">
                        <div class="p-6">
                            <h2 class="text-lg font-bold text-red-600">Driver Limit Reached</h2>
                            <p class="mt-4 text-gray-600">
                                You have reached the maximum number of drivers allowed. Please upgrade your plan or contact
                                the administrator
                            </p>
                            <div class="mt-6 flex justify-end">
                                <button id="closeModal" class="px-4 py-2 bg-gray-300 rounded-lg text-gray-800">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById('closeModal').addEventListener('click', function() {
                        document.getElementById('limitModal').style.display = 'none';
                    });
                </script>
            @endif

            <div class="px-0">
                <div class="box box--stacked flex flex-col">
                    <div class="overflow-auto xl:overflow-visible">
                        {{-- TABS --}}
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <ul class="flex flex-wrap md:flex-row flex-col text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                                <!-- Tab Carrier -->
                                <li class="flex-grow">
                                    <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                                    {{ request()->routeIs('admin.carrier.edit') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">
                                        <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.edit') ? 'text-primary dark:text-primary' : '' }}"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
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
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2  rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-primary ' : '' }}">
                                        <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary dark:text-primary' : '' }}"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
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
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.user_drivers.*') ? 'text-primary border-primary ' : '' }}">
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
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">
                                        <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.documents') ? 'text-primary dark:text-primary' : '' }}"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="m3 15 2 2 4-4" />
                                        </svg>
                                        Documents
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Contenido de la tabla -->
                        @if (request()->routeIs('admin.carrier.user_drivers.*'))
                            @include('admin.user_driver._table')
                        @else
                            @include('admin.user_carrier._table')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maxDrivers = {{ $maxDrivers }};
            const currentDrivers = {{ $currentDrivers }};

            if (currentDrivers >= maxDrivers) {
                document.querySelector('form')?.addEventListener('submit', function(event) {
                    event.preventDefault();
                    alert(
                        'You cannot add more drivers. Please upgrade your plan or contact the administrator.'
                        );
                });
            }
        });
    </script>
@endPushOnce