@extends('../themes/' . $activeTheme)
@section('title', 'Editar Mantenimiento')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Mantenimiento', 'url' => route('admin.maintenance.index')],
    ['label' => 'Editar Mantenimiento', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Editar Registro de Mantenimiento
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.maintenance.index') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                    Volver a la Lista
                </x-base.button>
            </div>
        </div>
        
        <div class="mt-3.5">
            <!-- Renderizar el componente Livewire para editar mantenimiento -->
            <livewire:admin.vehicle.maintenance-form :id="$id" />
        </div>
    </div>
</div>
@endsection