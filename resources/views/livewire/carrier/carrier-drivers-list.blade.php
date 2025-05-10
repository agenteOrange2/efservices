<div>
    <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
        <div class="text-base font-medium">Driver Management</div>
        <div class="md:ml-auto flex flex-col sm:flex-row gap-3">
            <div class="flex items-center">
                <x-base.form-input type="text" class="w-64" wire:model.live.debounce.300ms="search"
                    placeholder="Search drivers..." />

                <x-base.form-select class="w-40 ml-2" wire:model.live="statusFilter">
                    <option value="">All statuses</option>
                    <option value="1">Active</option>
                    <option value="2">Pending</option>
                    <option value="0">Inactive</option>
                </x-base.form-select>
            </div>

            <a href="{{ route('carrier.drivers.create') }}" class="btn btn-primary sm:ml-3">
                <x-base.lucide class="h-4 w-4 mr-2" icon="Plus" />
                New Driver
            </a>
        </div>
    </div>

    <!-- Membership Statistics -->
    <div class="box box--stacked mt-3.5 p-5 mb-5">
        <div class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between">
            <div>
                <h3 class="text-lg font-medium">Driver Limit</h3>
                <p class="text-slate-500 mt-1">
                    Your current plan allows you to have up to {{ $membershipStats['maxDrivers'] }} drivers.
                </p>
            </div>

            <div class="mt-3 sm:mt-0 flex items-center">
                <span class="font-medium">{{ $membershipStats['currentDrivers'] }} /
                    {{ $membershipStats['maxDrivers'] }}</span>
                <div class="ml-3 w-36 h-2 rounded bg-slate-200">
                    <div class="h-full rounded {{ $membershipStats['percentage'] > 90 ? 'bg-danger' : 'bg-success' }}"
                        style="width: {{ min(100, $membershipStats['percentage']) }}%"></div>
                </div>
            </div>
        </div>

        @if ($membershipStats['exceededLimit'])
            <div class="mt-3 bg-warning/20 text-warning rounded p-3 flex items-start">
                <x-base.lucide class="h-5 w-5 mr-2 mt-0.5" icon="AlertTriangle" />
                <div>
                    <p class="font-medium">You have reached the driver limit for your plan</p>
                    <p class="mt-1">To add more drivers, upgrade your membership plan.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Drivers Table -->
    <div class="box box--stacked mt-3.5 p-5">
        <div class="overflow-x-auto">
            <table class="table w-full text-left">
                <thead>
                    <tr>
                        <th class="border-b-2 whitespace-nowrap">Driver</th>
                        <th class="border-b-2 whitespace-nowrap">Contact</th>
                        <th class="border-b-2 whitespace-nowrap">Registration Date</th>
                        <th class="border-b-2 whitespace-nowrap">Status</th>
                        <th class="border-b-2 whitespace-nowrap text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                        <tr>
                            <td class="border-b">
                                <div class="flex items-center">
                                    <div class="image-fit zoom-in w-10 h-10">
                                        <img class="rounded-full"
                                            src="{{ $driver->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium">{{ $driver->user->name }} {{ $driver->last_name }}
                                        </div>
                                        <div class="text-slate-500 text-xs">
                                            {{ $driver->date_of_birth ? $driver->date_of_birth->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="border-b">
                                <div>{{ $driver->user->email }}</div>
                                <div class="text-slate-500 text-xs">{{ $driver->phone }}</div>
                            </td>
                            <td class="border-b">
                                {{ $driver->created_at->format('d/m/Y') }}
                            </td>
                            <td class="border-b">
                                @if ($driver->status === 1)
                                    <div class="flex items-center text-success">
                                        <x-base.lucide class="h-4 w-4 mr-1" icon="CheckCircle" />
                                        Active
                                    </div>
                                @elseif($driver->status === 2)
                                    <div class="flex items-center text-warning">
                                        <x-base.lucide class="h-4 w-4 mr-1" icon="Clock" />
                                        Pending
                                    </div>
                                @else
                                    <div class="flex items-center text-danger">
                                        <x-base.lucide class="h-4 w-4 mr-1" icon="XCircle" />
                                        Inactive
                                    </div>
                                @endif
                            </td>
                            <td class="border-b text-right">
                                <div class="flex justify-end items-center">
                                    <a href="{{ route('carrier.drivers.edit', $driver->id) }}" class="btn btn-outline-primary mr-2">
                                        <svg class="w-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title></title> <g id="Complete"> <g id="edit"> <g> <path d="M20,16v4a2,2,0,0,1-2,2H4a2,2,0,0,1-2-2V6A2,2,0,0,1,4,4H8" fill="none" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> <polygon fill="none" points="12.5 15.8 22 6.2 17.8 2 8.3 11.5 8 16 12.5 15.8" stroke="#666666" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></polygon> </g> </g> </g> </g></svg>
                                    </a>
                                    <a href="{{ route('carrier.drivers.show', $driver->id) }}" class="btn btn-outline-secondary mr-2">
                                        <x-base.lucide class="w-4 h-4" icon="Eye" />
                                    </a>
                                    <form action="{{ route('carrier.drivers.destroy', $driver->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this driver?')">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ff0000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M4.99997 8H6.5M6.5 8V18C6.5 19.1046 7.39543 20 8.5 20H15.5C16.6046 20 17.5 19.1046 17.5 18V8M6.5 8H17.5M17.5 8H19M9 5H15M9.99997 11.5V16.5M14 11.5V16.5" stroke="#c20000" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center border-b py-4">
                                No drivers found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-5">
            {{ $drivers->links() }}
        </div>
    </div>
</div>