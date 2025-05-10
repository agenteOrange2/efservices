
<div class="overflow-auto lg:overflow-visible">
    <x-base.table class="border-separate border-spacing-y-[10px]">
        <x-base.table.thead>
            <x-base.table.tr>
                <x-base.table.th class="whitespace-nowrap">Driver</x-base.table.th>
                <x-base.table.th class="whitespace-nowrap">Contact</x-base.table.th>
                <x-base.table.th class="whitespace-nowrap">License</x-base.table.th>
                <x-base.table.th class="whitespace-nowrap">Vehicle</x-base.table.th>
                <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                <x-base.table.th class="whitespace-nowrap">Actions</x-base.table.th>
            </x-base.table.tr>
        </x-base.table.thead>
        <x-base.table.tbody>
            @forelse ($userDrivers as $userDriver)
                <x-base.table.tr>
                    <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                        <div class="flex items-center">
                            <div class="h-10 w-10 image-fit zoom-in">
                                @if($userDriver->getFirstMediaUrl('profile_photo_driver'))
                                    <img class="rounded-full" src="{{ $userDriver->getFirstMediaUrl('profile_photo_driver') }}" alt="{{ $userDriver->user->name }}">
                                @else
                                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-primary/10 text-primary">
                                        {{ substr($userDriver->user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="font-medium whitespace-nowrap">{{ $userDriver->user->name }}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                    ID: {{ $userDriver->user->id }}
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
                            <div class="whitespace-nowrap">{{ $userDriver->user->email }}</div>
                        </div>
                        <div class="mb-1 mt-2 text-xs whitespace-nowrap text-slate-500">
                            Phone
                        </div>
                        <div class="flex items-center">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Phone" />
                            <div class="whitespace-nowrap">{{ $userDriver->phone ?? 'Not provided' }}</div>
                        </div>
                    </x-base.table.td>
                    <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                        <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                            License Number
                        </div>
                        <div class="flex items-center">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="CreditCard" />
                            <div class="whitespace-nowrap">
                                @if($userDriver->primaryLicense)
                                    {{ $userDriver->primaryLicense->license_number }}
                                @else
                                    Not provided
                                @endif
                            </div>
                        </div>
                        <div class="mb-1 mt-2 text-xs whitespace-nowrap text-slate-500">
                            Expiration
                        </div>
                        <div class="flex items-center">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Calendar" />
                            <div class="whitespace-nowrap">
                                @if($userDriver->primaryLicense && $userDriver->primaryLicense->expiration_date)
                                    {{ \Carbon\Carbon::parse($userDriver->primaryLicense->expiration_date)->format('M d, Y') }}
                                @else
                                    Not provided
                                @endif
                            </div>
                        </div>
                        <div class="mb-1 mt-2 text-xs whitespace-nowrap text-slate-500">
                            Class
                        </div>
                        <div class="flex items-center">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Tag" />
                            <div class="whitespace-nowrap">
                                @if($userDriver->primaryLicense && $userDriver->primaryLicense->license_class)
                                    {{ $userDriver->primaryLicense->license_class }}
                                @else
                                    Not provided
                                @endif
                            </div>
                        </div>
                    </x-base.table.td>
                    <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                        <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                            Vehicle
                        </div>
                        @if($userDriver->assignedVehicle)
                            <div class="flex items-center">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Truck" />
                                <div class="whitespace-nowrap font-medium">
                                    {{ $userDriver->assignedVehicle->make->name ?? '' }} {{ $userDriver->assignedVehicle->model ?? '' }}
                                </div>
                            </div>
                            <div class="mb-1 mt-2 text-xs whitespace-nowrap text-slate-500">
                                Year / VIN
                            </div>
                            <div class="flex items-center">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Calendar" />
                                <div class="whitespace-nowrap">
                                    {{ $userDriver->assignedVehicle->year ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="flex items-center mt-1">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Hash" />
                                <div class="whitespace-nowrap text-xs overflow-hidden text-ellipsis" style="max-width: 120px;" title="{{ $userDriver->assignedVehicle->vin ?? 'No VIN' }}">
                                    {{ $userDriver->assignedVehicle->vin ?? 'No VIN' }}
                                </div>
                            </div>
                        @elseif($userDriver->vehicles && $userDriver->vehicles->count() > 0)
                            <div class="flex items-center">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Truck" />
                                <div class="whitespace-nowrap font-medium">
                                    {{ $userDriver->vehicles->first()->make->name ?? '' }} {{ $userDriver->vehicles->first()->model ?? '' }}
                                </div>
                            </div>
                            <div class="mb-1 mt-2 text-xs whitespace-nowrap text-slate-500">
                                Year / VIN
                            </div>
                            <div class="flex items-center">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Calendar" />
                                <div class="whitespace-nowrap">
                                    {{ $userDriver->vehicles->first()->year ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="flex items-center mt-1">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="Hash" />
                                <div class="whitespace-nowrap text-xs overflow-hidden text-ellipsis" style="max-width: 120px;" title="{{ $userDriver->vehicles->first()->vin ?? 'No VIN' }}">
                                    {{ $userDriver->vehicles->first()->vin ?? 'No VIN' }}
                                </div>
                            </div>
                            @if($userDriver->vehicles->count() > 1)
                                <div class="mt-2 text-xs text-primary">
                                    + {{ $userDriver->vehicles->count() - 1 }} more vehicles
                                </div>
                            @endif
                        @else
                            <div class="flex items-center">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="AlertCircle" />
                                <div class="whitespace-nowrap text-slate-500">No Vehicle Assigned</div>
                            </div>
                        @endif
                    </x-base.table.td>
                    <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                        <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                            Status
                        </div>
                        <div class="flex items-center">
                            @if($userDriver->status == 'active')
                                <div class="flex items-center text-success">
                                    <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="CheckCircle" />
                                    <span>Active</span>
                                </div>
                            @elseif($userDriver->status == 'inactive')
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
                                    <x-base.menu.item href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $carrier->slug, 'userDriverDetail' => $userDriver->id]) }}">
                                        <x-base.lucide class="w-4 h-4 mr-2" icon="Edit" />
                                        Edit
                                    </x-base.menu.item>
                                    <x-base.menu.item class="text-danger" data-tw-toggle="modal"
                                        data-tw-target="#delete-modal-{{ $userDriver->id }}">
                                        <x-base.lucide class="w-4 h-4 mr-2" icon="Trash2" />
                                        Delete
                                    </x-base.menu.item>
                                </x-base.menu.items>
                            </x-base.menu>
                        </div>
                    </x-base.table.td>
                </x-base.table.tr>

                <!-- DELETE MODAL -->
                <x-base.dialog id="delete-modal-{{ $userDriver->id }}" size="md">
                    <x-base.dialog.panel>
                        <div class="p-5 text-center">
                            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger"
                                icon="XCircle" />
                            <div class="mt-5 text-2xl">Are you sure?</div>
                            <div class="mt-2 text-slate-500">
                                Do you really want to remove this driver?
                                <br>
                                This process can not be undone.
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <form action="{{ route('admin.carrier.user_drivers.destroy', ['carrier' => $carrier->slug, 'userDriverDetail' => $userDriver->id]) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <x-base.button class="mr-1 w-24" data-tw-dismiss="modal"
                                    type="button"
                                    variant="outline-secondary">
                                    Cancel
                                </x-base.button>
                                <x-base.button class="w-24" type="submit" variant="danger">
                                    Delete
                                </x-base.button>
                            </form>
                        </div>
                    </x-base.dialog.panel>
                </x-base.dialog>
            @empty
                <x-base.table.tr>
                    <x-base.table.td colspan="6" class="text-center">
                        <div class="flex flex-col items-center justify-center py-16">
                            <x-base.lucide class="h-8 w-8 text-slate-400" icon="Users" />
                            <div class="mt-5 text-center">
                                <div class="text-xl font-medium">No Drivers Found</div>
                                <div class="text-slate-500 mt-2">This carrier doesn't have any drivers yet.</div>
                                @if(!$exceeded_limit)
                                    <a href="{{ route('admin.carrier.user_drivers.create', $carrier->slug) }}" 
                                    class="btn btn-primary mt-4 flex items-center justify-center">
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
                    </x-base.table.td>
                </x-base.table.tr>
            @endforelse
        </x-base.table.tbody>
    </x-base.table>
</div>

<!-- Pagination -->
@if($userDrivers->count() > 0)
    <div class="mt-5">
        {{ $userDrivers->links() }}
    </div>
@endif