@extends('../themes/' . $activeTheme)
@section('title', 'Mantenimiento de Vehículos')
@php
$breadcrumbLinks = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
    ['label' => 'Mantenimiento', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Registros de Mantenimiento
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.maintenance.reports') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="BarChart2" />
                    Reportes
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.maintenance.calendar') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Calendar" />
                    Calendario
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.maintenance.create') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PlusCircle" />
                    Nuevo Mantenimiento
                </x-base.button>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="flex items-center">
                <h2 class="text-lg font-medium truncate mr-5">Próximos Mantenimientos</h2>
            </div>
            <div class="mt-5">
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <div class="mr-auto">
                        <div class="flex items-center">
                            <div class="text-base font-bold">Junio 2025</div>
                        </div>
                        <div class="text-slate-500 mt-1">5 mantenimientos programados</div>
                    </div>
                    <div class="flex">
                        <a href="{{ route('admin.maintenance.calendar') }}" class="btn btn-outline-secondary w-32 mt-5 sm:mt-0 sm:ml-1">
                            Ver Calendario
                        </a>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="intro-y">
                        <div class="flex items-center pb-4 border-b border-slate-200 dark:border-darkmode-400">
                            <div>
                                <div class="text-slate-500 font-medium">15 JUN</div>
                                <div class="mt-1">Cambio de aceite - Ford F-150</div>
                            </div>
                            <div class="flex items-center ml-auto">
                                <div class="flex items-center justify-center bg-success/20 text-success rounded-full p-2">
                                    <x-base.lucide class="w-4 h-4" icon="CheckCircle" />
                                </div>
                                <a href="#" class="flex items-center ml-3 text-primary">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="Eye" /> Ver
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="flex items-center py-4 border-b border-slate-200 dark:border-darkmode-400">
                            <div>
                                <div class="text-slate-500 font-medium">22 JUN</div>
                                <div class="mt-1">Inspección - Kenworth T680</div>
                            </div>
                            <div class="flex items-center ml-auto">
                                <div class="flex items-center justify-center bg-warning/20 text-warning rounded-full p-2">
                                    <x-base.lucide class="w-4 h-4" icon="Clock" />
                                </div>
                                <a href="#" class="flex items-center ml-3 text-primary">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="Eye" /> Ver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="flex flex-col xl:flex-row xl:items-center">
                <div class="flex">
                    <div>
                        <h2 class="text-lg font-medium truncate mr-5">Mantenimientos Recientes</h2>
                    </div>
                    <div class="ml-auto flex items-center truncate">
                        <a href="javascript:;" class="flex items-center text-primary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="RefreshCw" /> Actualizar
                        </a>
                    </div>
                </div>
            </div>            
            <!-- Renderizar el componente Livewire -->
            <livewire:admin.vehicle.maintenance-list />
        </div>
        

    </div>
</div>
@endsection