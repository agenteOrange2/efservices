@extends('../themes/' . $activeTheme)
@section('title', 'User Carriers for Carrier: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'User Carriers: ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">

            <div class="mb-10">
                @if ($carrier->userCarriers->count() < $carrier->membership->max_carrier)
                    <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                        <div class="text-base font-medium group-[.mode--light]:text-white">
                            <h2 class="text-2xl">User Carriers for Carrier: <span>{{ $carrier->name }}</span></h2>
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                            @if ($carrier->userCarriers->count() < $carrier->membership->max_drivers)
                                <x-base.button as="a"
                                    href="{{ route('admin.carrier.user_carriers.create', $carrier) }}"
                                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                                    variant="primary">                                    
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                        Add User Carrier                                        
                                    </div>
                                </x-base.button>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    Max User Carriers Reached
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="w-full mb-10">
                        <div role="alert"
                            class="alert relative border rounded-md px-5 py-4 bg-primary border-primary text-white dark:border-primary">
                            <div class="flex items-center">
                                <div class="text-lg font-medium">
                                    Max User Carriers Reached
                                </div>
                                <div class="ml-auto rounded-md bg-white px-1 text-xs text-slate-700">
                                    Notice
                                </div>
                            </div>
                            <div class="mt-3">
                                You have exceeded your user limit, if you need more carrier users, please upgrade your plan
                                or contact the administration to upgrade your plan.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!-- La lógica de validación para el límite de usuarios se maneja en el controlador -->
            <div class="px-0">
                <div class="box box--stacked flex flex-col">
                    <div class="overflow-auto xl:overflow-visible">
                        {{-- TABS --}}
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <ul class="flex flex-wrap md:flex-row flex-col text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                                <!-- Tab Carrier -->
                                <li class="flex-grow">
                                    <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.edit') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">
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
                                {{-- Uncomment if needed --}}
                                <li class="flex-grow">
                                    <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-blue-600 dark:text-primary dark:border-primary' : '' }}">
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
                        

                        <table class="w-full text-left border-b border-slate-200/60">
                            <thead>
                                <tr>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Name</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Email</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Job Position</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Status</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($userCarriers as $userCarrier)
                                    <tr>
                                        <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->name }}</td>
                                        <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->email }}</td>
                                        <td class="px-5 border-b border-dashed py-4">
                                            {{ $userCarrier->carrierDetails->job_position ?? 'N/A' }}</td>
                                        <td class="px-5 border-b border-dashed py-4">
                                            {{ $userCarrier->carrierDetails->status_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-5 border-b border-dashed py-4">
                                            <div class="flex items-center space-x-3">
                                                <a href="{{ route('admin.carrier.user_carriers.edit', ['carrier' => $carrier->slug, 'userCarrierDetails' => $userCarrier->carrierDetails->id]) }}"
                                                    class="flex items-center text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="w-4 h-4 mr-1">
                                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                                        <path d="m15 5 4 4"></path>
                                                    </svg>
                                                    Edit
                                                </a>

                                                <button type="button" data-tw-toggle="modal"
                                                    data-tw-target="#delete-modal-{{ $userCarrier->id }}"
                                                    class="flex items-center text-danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="w-4 h-4 mr-1">
                                                        <path d="M3 6h18"></path>
                                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                        <line x1="10" y1="11" x2="10"
                                                            y2="17"></line>
                                                        <line x1="14" y1="11" x2="14"
                                                            y2="17"></line>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </div>

                                            <!-- DELETE MODAL -->
                                            <x-base.dialog id="delete-modal-{{ $userCarrier->id }}" size="md">
                                                <x-base.dialog.panel>
                                                    <div class="p-5 text-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                            height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="mx-auto mt-3 h-16 w-16 text-danger">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <line x1="15" y1="9" x2="9"
                                                                y2="15"></line>
                                                            <line x1="9" y1="9" x2="15"
                                                                y2="15"></line>
                                                        </svg>
                                                        <div class="mt-5 text-2xl">Are you sure?</div>
                                                        <div class="mt-2 text-slate-500">
                                                            Do you really want to remove this user?
                                                            <br>
                                                            This process cannot be undone.
                                                        </div>
                                                    </div>
                                                    <div class="px-5 pb-8 text-center">
                                                        <form
                                                            action="{{ route('admin.carrier.user_carriers.destroy', ['carrier' => $carrier->slug, 'userCarrier' => $userCarrier->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-base.button class="mr-1 w-24" data-tw-dismiss="modal"
                                                                type="button" variant="outline-secondary">
                                                                Cancel
                                                            </x-base.button>
                                                            <x-base.button class="w-24" type="submit"
                                                                variant="danger">
                                                                Delete
                                                            </x-base.button>
                                                        </form>
                                                    </div>
                                                </x-base.dialog.panel>
                                            </x-base.dialog>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 border-b border-dashed py-4 text-center">No user
                                            carriers
                                            found for this carrier.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maxCarriers = {{ $carrier->membership->max_carrier ?? 1 }};
            const currentCarriers = {{ $carrier->userCarriers->count() }};

            if (currentCarriers >= maxCarriers) {
                document.querySelector('form').addEventListener('submit', function(event) {
                    event.preventDefault();
                    alert('No puedes agregar más usuarios. Actualiza tu plan o contacta al administrador.');
                });
            }
        });
    </script>
@endPushOnce
