@extends('../themes/' . $activeTheme)
@section('title', 'Document Type Carriers')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Document Type Carriers', 'active' => true],
    ];
@endphp


@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce
@section('subcontent')



    <x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Document Type Carriers
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.document-types.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New Document Type
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div class="relative">
                        <livewire:search-bar placeholder="Search Document Type..." />
                    </div>

                    <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                        <livewire:filter-popover :filter-options="[
                            'requirement' => [
                                'type' => 'select',
                                'label' => 'Requirement',
                                'options' => [
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ],
                                'default' => null,
                            ],
                        ]" />
                    </div>
                </div>
                <livewire:generic-table class="p-0" model="App\Models\DocumentType" :columns="['name', 'requirement', 'created_at']" :searchableFields="['name', 'requirement', 'created_at']"
                    editRoute="admin.document-types.edit" :customFilters="[
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

@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
@endPushOnce
