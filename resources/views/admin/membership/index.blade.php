@extends('../themes/' . $activeTheme)
@section('title', 'Membership')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Membership', 'active' => true],
    ];
@endphp
@section('subcontent')
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Memberships
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.membership.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New Membership
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div class="relative">
                        <livewire:search-bar placeholder="Search Membership..." />
                    </div>
                    <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                        <livewire:menu-export :exportExcel="true" :exportPdf="true" wire:ignore />
                        <livewire:filter-popover :filter-options="[
                            'status' => [
                                'type' => 'select',
                                'label' => 'Status',
                                'options' => [
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ],
                            ],
                        ]" />
                    </div>
                </div>
                <livewire:generic-table class="p-0" model="App\Models\Membership" :columns="['name', 'description', 'status', 'created_at']" :searchableFields="['name', 'description', 'status', 'created_at']"
                    editRoute="admin.membership.edit" exportExcelRoute="admin.membership.export.excel"
                    exportPdfRoute="admin.membership.export.pdf" :customFilters="[
                        'status' => [
                            'type' => 'select',
                            'label' => 'Status',
                            'options' => [
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ],
                        ],
                    ]" />
            </div>
        </div>
    </div>
@endsection
