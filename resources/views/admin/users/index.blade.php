@extends('../themes/' . $activeTheme)
@section('title', 'Super Admins')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => 'Super Admins', 'active' => true],
    ];
@endphp
@section('subcontent')

    <x-base.notificationtoast.notification-toast :notification="session('notification')" />

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Users
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.users.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New User
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">

                <livewire:generic-table 
                model="App\Models\User" 
                :columns="['name', 'email', 'status', 'created_at']" 
                :searchableFields="['name', 'email']"
                editRoute="admin.users.edit" 
                exportExcelRoute="admin.users.export.excel"
                exportPdfRoute="admin.users.export.pdf"
                :customFilters="[
                    'status' => [
                        'type' => 'select',
                        'label' => 'Status',
                        'options' => [
                            'active' => 'Active',
                            'inactive' => 'Inactive'
                        ]
                    ]
                ]"
            />
            </div>
        </div>
    </div>
    @if (session('notification'))
        <x-base.notification id="dynamic-notification" :type="session('notification')['type']" :message="session('notification')['message']" :details="session('notification')['details'] ?? ''" />
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const notificationElement = document.getElementById('dynamic-notification');
                if (notificationElement) {
                    Toastify({
                        node: notificationElement.cloneNode(true),
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                    }).showToast();
                }
            });
        </script>
    @endif
@endsection
