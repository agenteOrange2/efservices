@extends('../themes/' . $activeTheme)

@section('title', 'Permissions')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],        
        ['label' => 'Permissions', 'active' => true],
    ];
@endphp
@section('subcontent')

<x-base.notificationtoast.notification-toast :notification="session('notification')" />

<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Permissions
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.permissions.create') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                    Add New Permission
                </x-base.button>
            </div>
        </div>
        <div class="box box--stacked flex flex-col mt-5">
            <livewire:generic-table 
            model="Spatie\Permission\Models\Permission"
            :columns="['name','created_at', 'updated_at']"
            :searchableFields="['name']"
            editRoute="admin.permissions.edit"
        />
            </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        console.log('Livewire is loaded and listening for notify events');
        Livewire.on('notify', notification => {
            console.log('Notification received:', notification);
            Toastify({
                text: `${notification.message}\n${notification.details}`,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: notification.type === 'success' ? "green" : "orange",
                stopOnFocus: true,
            }).showToast();
        });
    });
</script>
@endpush