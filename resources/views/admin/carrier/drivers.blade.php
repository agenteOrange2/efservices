@extends('../themes/' . $activeTheme)
@section('title', 'Drivers for ' . $carrier->name)
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Drivers for ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')
    <!-- Notification Toast Component -->
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />

    <!-- Success Notification Content -->
    <div id="success-notification-content" class="hidden">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-green-100 border border-green-400 text-green-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
            </svg>
            <span>Operation completed successfully!</span>
        </div>
    </div>

    <!-- Error Notification Content -->
    <div id="error-notification-content" class="hidden">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-red-100 border border-red-400 text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 8v4m0 4h.01M12 2a10 10 0 11-10 10A10 10 0 0112 2z"></path>
            </svg>
            <span>An error occurred. Please try again.</span>
        </div>
    </div>

    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-semibold">Drivers for {{ $carrier->name }}</h1>
        
        @if(!$exceeded_limit)
            <a href="{{ route('admin.carrier.user_drivers.create', $carrier->slug) }}" 
               class="btn btn-primary shadow-md">
                <x-base.lucide class="w-4 h-4 mr-2" icon="Plus" />
                Add New Driver
            </a>
        @else
            <button type="button" class="btn btn-outline-secondary" disabled>
                <x-base.lucide class="w-4 h-4 mr-2" icon="AlertCircle" />
                Driver Limit Reached
            </button>
        @endif
    </div>

    <!-- Plan Information -->
    <div class="intro-y box p-4 mb-5 bg-primary/5 border border-primary/10 rounded-md">
        <div class="flex items-center">
            <x-base.lucide class="h-6 w-6 mr-2 text-primary" icon="Info" />
            <div>
                <span class="font-medium">Plan Information:</span> 
                <span>{{ $currentDrivers }} of {{ $maxDrivers }} drivers used</span>
                @if($exceeded_limit)
                    <div class="text-danger mt-1">
                        <strong>You've reached your driver limit.</strong> Please upgrade your plan to add more drivers.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <div class="intro-y box">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap text-sm font-medium text-center bg-white text-gray-500 dark:text-gray-400">
                    <!-- Tab Carrier -->
                    <li class="flex-grow">
                        <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                            class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
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
                            <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-primary dark:text-primary' : '' }}"
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
                            class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.user_drivers.*') ? 'text-primary border-primary ' : '' }}">
                            <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_drivers.*') ? 'text-primary dark:text-primary' : '' }}"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
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
                            class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                    {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">
                            <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-primary dark:text-primary' : '' }}"
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

            <div class="p-5">
                @if($userDrivers->count() > 0)
                    <div class="overflow-auto lg:overflow-visible">
                        <x-base.table class="border-separate border-spacing-y-[10px]">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="whitespace-nowrap">Driver</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Contact</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">License</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Actions</x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach($userDrivers as $driver)
                                    <x-base.table.tr>
                                        <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 image-fit zoom-in">
                                                    @if($driver->getFirstMediaUrl('profile_photo_driver'))
                                                        <img class="rounded-full" src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}" alt="{{ $driver->user->name }}">
                                                    @else
                                                        <div class="flex items-center justify-center h-10 w-10 rounded-full bg-primary/10 text-primary">
                                                            {{ substr($driver->user->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium whitespace-nowrap">{{ $driver->user->name }}</div>
                                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                                        ID: {{ $driver->user->id }}
                                                    </div>
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                                Email
                                            </div>
                                            <div class="flex items-center">
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Mail" />
                                                <div class="whitespace-nowrap">{{ $driver->user->email }}</div>
                                            </div>
                                            <div class="mb-1 mt-2 text-xs whitespace-nowrap text-slate-500">
                                                Phone
                                            </div>
                                            <div class="flex items-center">
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Phone" />
                                                <div class="whitespace-nowrap">{{ $driver->phone ?? 'Not provided' }}</div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                                License Number
                                            </div>
                                            <div class="flex items-center">
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="CreditCard" />
                                                <div class="whitespace-nowrap">{{ $driver->license_number ?? 'Not provided' }}</div>
                                            </div>
                                            <div class="mb-1 mt-2 text-xs whitespace-nowrap text-slate-500">
                                                Expiration
                                            </div>
                                            <div class="flex items-center">
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Calendar" />
                                                <div class="whitespace-nowrap">
                                                    @if($driver->license_expiration)
                                                        {{ \Carbon\Carbon::parse($driver->license_expiration)->format('M d, Y') }}
                                                    @else
                                                        Not provided
                                                    @endif
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                                Status
                                            </div>
                                            <div class="flex items-center">
                                                @if($driver->status == 'active')
                                                    <div class="flex items-center text-success">
                                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="CheckCircle" />
                                                        <span>Active</span>
                                                    </div>
                                                @elseif($driver->status == 'inactive')
                                                    <div class="flex items-center text-danger">
                                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="XCircle" />
                                                        <span>Inactive</span>
                                                    </div>
                                                @else
                                                    <div class="flex items-center text-warning">
                                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="AlertCircle" />
                                                        <span>Pending</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="box relative w-20 rounded-l-none rounded-r-none border-x-0 py-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                            <div class="flex items-center justify-center">
                                                <x-base.menu class="h-5">
                                                    <x-base.menu.button class="w-5 h-5 text-slate-500">
                                                        <x-base.lucide class="w-5 h-5 fill-slate-400/70 stroke-slate-400/70"
                                                            icon="MoreVertical" />
                                                    </x-base.menu.button>
                                                    <x-base.menu.items class="w-40">
                                                        <x-base.menu.item href="{{ route('admin.carrier.user_drivers.edit', [$carrier->slug, $driver->id]) }}">
                                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Edit" />
                                                            Edit
                                                        </x-base.menu.item>
                                                        <x-base.menu.item class="text-danger" data-tw-toggle="modal"
                                                            data-tw-target="#delete-modal-{{ $driver->id }}">
                                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Trash2" />
                                                            Delete
                                                        </x-base.menu.item>
                                                    </x-base.menu.items>
                                                </x-base.menu>
                                            </div>
                                        </x-base.table.td>
                                    </x-base.table.tr>

                                    <!-- DELETE MODAL -->
                                    <x-base.dialog id="delete-modal-{{ $driver->id }}" size="md">
                                        <x-base.dialog.panel>
                                            <div class="p-5 text-center">
                                                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger"
                                                    icon="XCircle" />
                                                <div class="mt-5 text-2xl">¿Estás seguro?</div>
                                                <div class="mt-2 text-slate-500">
                                                    ¿Realmente quieres eliminar a este conductor?
                                                    <br>
                                                    Este proceso no se puede deshacer.
                                                </div>
                                            </div>
                                            <div class="px-5 pb-8 text-center">
                                                <form action="{{ route('admin.carrier.user_drivers.destroy', [$carrier->slug, $driver->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-base.button class="mr-1 w-24" data-tw-dismiss="modal"
                                                        type="button"
                                                        variant="outline-secondary">
                                                        Cancelar
                                                    </x-base.button>
                                                    <x-base.button class="w-24" type="submit" variant="danger">
                                                        Eliminar
                                                    </x-base.button>
                                                </form>
                                            </div>
                                        </x-base.dialog.panel>
                                    </x-base.dialog>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-5">
                        {{ $userDrivers->links() }}
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16">
                        <x-base.lucide class="h-16 w-16 text-slate-400" icon="Users" />
                        <div class="mt-5 text-center">
                            <div class="text-xl font-medium">No Drivers Found</div>
                            <div class="text-slate-500 mt-2">This carrier doesn't have any drivers yet.</div>
                            @if(!$exceeded_limit)
                                <a href="{{ route('admin.carrier.user_drivers.create', $carrier->slug) }}" 
                                class="btn btn-primary mt-4">
                                    <x-base.lucide class="w-4 h-4 mr-2" icon="Plus" />
                                    Add First Driver
                                </a>
                            @else
                                <div class="text-danger mt-4">
                                    <strong>Driver limit reached.</strong> Please upgrade your plan to add drivers.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@pushOnce('scripts')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Show notification if exists in session
            if (document.querySelector('.notification-content')) {
                setTimeout(() => {
                    document.querySelector('.notification-content').classList.add('hidden');
                }, 5000);
            }
        });
    </script>
@endPushOnce
