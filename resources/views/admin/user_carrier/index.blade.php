@extends('../themes/' . $activeTheme)
@section('title', 'User Carriers for Carrier: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'User Carriers for Carrier: ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="p-7">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    <h2 class="text-2xl">User Carriers for Carrier: <span>{{ $carrier->name }}</span></h2>
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    @if ($carrier->userCarriers->count() < $carrier->membership->max_drivers)
                        <x-base.button as="a" href="{{ route('admin.carrier.user_carriers.create', $carrier) }}"
                            class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                            variant="primary">
                            Add User Carrier
                        </x-base.button>
                    @else
                        <button class="btn btn-secondary" disabled>
                            Max User Carriers Reached
                        </button>
                    @endif
                </div>
            </div>

            <div class="box box--stacked flex flex-col mt-5">
                <table class="w-full text-left border-b border-slate-200/60">
                    <thead>
                        <tr>
                            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Name</th>
                            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500" >Email</th>                            
                            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Job Position</th>
                            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Status</th>
                            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($userCarriers as $userCarrier)
                            <tr>
                                <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->name }}</td>
                                <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->email }}</td>                                
                                <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->job_position }}</td>
                                <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->status_name }}</td>
                                <td class="px-5 border-b border-dashed py-4">
                                    <a href="{{ route('admin.carrier.user_carriers.edit', [$carrier->slug, $userCarrier->id]) }}"
                                        class="btn btn-sm btn-warning">Edit</a>
                                    <form
                                        action="{{ route('admin.carrier.user_carriers.destroy', [$carrier->id, $userCarrier->id]) }}"
                                        method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 border-b border-dashed py-4 text-center">No user carriers found for this carrier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>

    {{-- <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Users Carrier
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.carrier.user_carriers.create', $carrier) }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New User Carrier
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">
                <livewire:generic-table class="p-0" model="App\Models\UserCarrier" :columns="[
                    'name' => 'Name',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'job_position' => 'Job Position',
                    'carrier.name' => 'Carrier Name',
                    'status' => 'Status',
                    'created_at' => 'Created At',
                ]" :searchableFields="['name', 'email', 'phone', 'job_position', 'status', 'carrier.name']"
                    editRoute="admin.user_carrier.edit" :filters="[
                        'status' => [
                            'type' => 'select',
                            'label' => 'Status',
                            'options' => [
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ],
                        ],
                    ]" />
            </div>
        </div>
    </div> --}}
@endsection
