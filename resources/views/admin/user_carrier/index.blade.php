@extends('../themes/' . $activeTheme)
@section('title', 'User Carrier')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'User Carrier', 'active' => true],
    ];
@endphp
@section('subcontent')
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Users Carrier
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.user_carrier.create') }}"
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
    </div>
@endsection
