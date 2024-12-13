@extends('../themes/' . $activeTheme)

@section('title', 'Carrier: ' . $carrier->name)

@section('subcontent')
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Carrier Management
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.carrier.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New Carrier
                    </x-base.button>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="tabs mt-5">
                <ul class="border-b border-slate-200 w-full flex">
                    <!-- Tab Carrier -->
                    <li class="visible:outline-none flex-1 -mb-px">
                        <a class="cursor-pointer block px-3 py-2 text-slate-600 transition-colors border border-transparent rounded-t-md [&.active]:bg-white [&.active]:border-slate-200 [&.active]:border-b-transparent [&.active]:font-medium [&.active]:text-slate-700 [&.active]:dark:text-white [&.active]:dark:bg-transparent [&.active]:dark:border-t-darkmode-400 [&.active]:dark:border-b-darkmode-600 [&.active]:dark:border-x-darkmode-400 [&:not(.active)]:hover:bg-slate-100 [&:not(.active)]:dark:hover:bg-darkmode-400 [&:not(.active)]:dark:hover:border-transparent {{ request()->routeIs('admin.carrier.edit') ? 'active' : '' }}"
                            href="{{ route('admin.carrier.edit', $carrier->id) }}">
                            Carrier
                        </a>
                    </li>

                    <!-- Tab Users -->
                    <li class="visible:outline-none flex-1 -mb-px">
                        <a class="cursor-pointer block px-3 py-2 text-slate-600 transition-colors border border-transparent rounded-t-md [&.active]:bg-white [&.active]:border-slate-200 [&.active]:border-b-transparent [&.active]:font-medium [&.active]:text-slate-700 [&.active]:dark:text-white [&.active]:dark:bg-transparent [&.active]:dark:border-t-darkmode-400 [&.active]:dark:border-b-darkmode-600 [&.active]:dark:border-x-darkmode-400 [&:not(.active)]:hover:bg-slate-100 [&:not(.active)]:dark:hover:bg-darkmode-400 [&:not(.active)]:dark:hover:border-transparent {{ request()->routeIs('admin.carrier.users') ? 'active' : '' }}"
                            href="{{ route('admin.carrier.users', $carrier->id) }}">
                            Users
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Dynamic Content --}}
            <div class="box box--stacked flex flex-col mt-5">
                @if (request()->routeIs('admin.carrier.edit'))
                    {{-- Carrier Details --}}
                    <livewire:generic-table class="p-0" model="App\Models\Carrier" :columns="[
                        'name',
                        'state',
                        'status',
                        'created_at',
                    ]" :searchableFields="['name', 'state', 'status']" editRoute="admin.carrier.edit" />
                @elseif (request()->routeIs('admin.carrier.users'))
                    {{-- Users Associated --}}
                    <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                        <div class="relative">
                            <livewire:search-bar placeholder="Search users associated with this carrier..." />
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                            <livewire:menu-export :exportExcel="true" :exportPdf="true" wire:ignore />
                            <livewire:filter-popover :filter-options="[
                                'status' => [
                                    'type' => 'select',
                                    'label' => 'Status',
                                    'options' => [
                                        1 => 'Active',
                                        0 => 'Inactive',
                                        2 => 'Pending',
                                    ],
                                ],
                            ]" />
                        </div>
                    </div>

                    <table class="w-full text-left border-b border-slate-200/60">
                        <thead>
                            <tr>
                                <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Name</th>
                                <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Email</th>
                                <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Phone</th>
                                <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Job Position</th>
                                <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Status</th>
                                <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($userCarriers as $userCarrier)
                                <tr>
                                    <td class="px-5 border-b w-80 border-dashed py-4">{{ $userCarrier->name }}</td>
                                    <td class="px-5 border-b w-80 border-dashed py-4">{{ $userCarrier->email }}</td>
                                    <td class="px-5 border-b w-80 border-dashed py-4">{{ $userCarrier->phone }}</td>
                                    <td class="px-5 border-b w-80 border-dashed py-4">{{ $userCarrier->job_position }}</td>
                                    <td class="px-5 border-b w-80 border-dashed py-4">
                                        @if ($userCarrier->status === 1)
                                            Active
                                        @elseif ($userCarrier->status === 0)
                                            Inactive
                                        @else
                                            Pending
                                        @endif
                                    </td>
                                    <td class="px-5 border-b w-80 border-dashed py-4 text-right">
                                        <x-base.button as="a" href="{{ route('admin.user_carrier.edit', $userCarrier->id) }}" variant="outline-primary">
                                            Edit
                                        </x-base.button>
                                        <form action="{{ route('admin.user_carrier.destroy', $userCarrier->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <x-base.button type="submit" class="text-red-500">
                                                Delete
                                            </x-base.button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No users associated with this carrier.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $userCarriers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
