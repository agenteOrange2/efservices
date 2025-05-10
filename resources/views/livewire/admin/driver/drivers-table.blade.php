<div>
    <div class="mb-10">
        @if ($currentDrivers < $maxDrivers)
            <div class="flex justify-between items-center">
                <div class="text-base font-medium">
                    <h2 class="text-2xl">Driver for Carrier: <span>{{ $carrier->name }}</span></h2>
                </div>
                <div class="flex gap-4">
                    <x-base.form-input wire:model.live.debounce.300ms="search" placeholder="Search..." />

                    <div class="dropdown">
                        <x-base.button wire:click="create" variant="primary">
                            Add New Driver
                            <x-base.lucide class="w-full h-4 ml-2" icon="ChevronDown" />
                        </x-base.button>
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

    <div class="box box--stacked">
        
        {{-- TABS --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
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
                        class="inline-flex items-center justify-center w-full p-4 border-b-2  rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-primary ' : '' }}">
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
                        class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">
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

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th wire:click="sortBy('user.name')" class="cursor-pointer">Name</th>
                        <th wire:click="sortBy('user.email')" class="cursor-pointer">Email</th>
                        <th>License Number</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($drivers as $driver)
                        <tr>
                            <td>{{ $driver->user->name }}</td>
                            <td>{{ $driver->user->email }}</td>
                            <td>{{ $driver->license_number }}</td>
                            <td>{{ $driver->assignedVehicle?->model ?? 'No Vehicle' }}</td>
                            <td>{{ $driver->status_name }}</td>
                            <td>
                                <button wire:click="edit({{ $driver->id }})" class="btn btn-primary btn-sm">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $driver->id }})" class="btn btn-danger btn-sm ml-2">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No drivers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $drivers->links() }}
        </div>
    </div>
</div>
