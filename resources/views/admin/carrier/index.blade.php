@extends('../themes/' . $activeTheme)
@section('title', 'All Carriers Registered')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'active' => true],
    ];
@endphp

@section('subcontent')
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />
    @if (isset($notification))
        <div class="alert alert-{{ $notification['type'] }} alert-dismissible fade show" role="alert">
            <strong>{{ $notification['message'] }}</strong>
            @if (isset($notification['details']))
                <p class="mb-0">{{ $notification['details'] }}</p>
            @endif
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    <h1>All Carriers Registered</h1>
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

            <!-- Reemplaza el contenido de la tabla con el componente Livewire -->
            <div class="box box--stacked flex flex-col mt-5">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div class="relative">
                        <livewire:search-bar placeholder="Search users..." />
                    </div>

                    <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                        <livewire:menu-export :exportExcel="true" :exportPdf="true" wire:ignore />
                        <livewire:filter-popover :filter-options="[
                            'status' => [
                                'type' => 'select',
                                'label' => 'Status',
                                'options' => [
                                    'pending' => 'Pending',
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ],
                            ],
                        ]" />
                    </div>
                </div>
                {{-- <livewire:carrier-manager /> --}}

                <livewire:generic-table class="p-0" model="App\Models\Carrier" :columns="['name', 'address', 'status', 'created_at']" :searchableFields="['name', 'address', 'status', 'created_at']"
                    editRoute="admin.carrier.edit" showSlugRoute="admin.carrier.show" exportExcelRoute="admin.carrier.export.excel"
                    exportPdfRoute="admin.carrier.export.pdf" :customFilters="[
                        'status' => [
                            'type' => 'select',
                            'label' => 'Status',
                            'options' => [
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ],
                        ],
                    ]" />
            </div>
        </div>
    </div>
@endsection
