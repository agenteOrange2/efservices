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
                    Maintenance Records                
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.maintenance.reports') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="BarChart2" />
                        reports
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.maintenance.calendar') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Calendar" />
                        Calendar
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.maintenance.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PlusCircle" />
                        New Maintenance
                    </x-base.button>
                </div>
            </div>

            <div class="intro-y box p-5 mt-5">
                <div class="flex items-center">
                    <h2 class="text-lg font-medium truncate mr-5">Upcoming Maintenance</h2>
                </div>
                <div class="mt-5">
                    <div class="flex flex-col sm:flex-row sm:items-center">
                        <div class="mr-auto">
                            <div class="flex items-center">
                                <div class="text-base font-bold">{{ Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
                            </div>
                            <div class="text-slate-500 mt-1">{{ $totalScheduled }} mantenimientos programados</div>
                        </div>
                        <div class="flex">
                            <a href="{{ route('admin.maintenance.calendar') }}"
                                class="btn btn-outline-secondary w-32 mt-5 sm:mt-0 sm:ml-1">
                                View Calendar
                            </a>
                        </div>
                    </div>
                    <div class="mt-5">
                        @forelse ($upcomingMaintenances as $maintenance)
                            <div class="intro-y">
                                <div class="flex items-center py-4 border-b border-slate-200 dark:border-darkmode-400">
                                    <div>
                                        <div class="text-slate-500 font-medium">{{ Carbon\Carbon::parse($maintenance->next_service_date)->format('d M') }}</div>
                                        <div class="mt-1">{{ $maintenance->service_tasks }} - {{ $maintenance->vehicle->unit }} {{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }}</div>
                                        <div class="text-xs text-slate-500">{{ $maintenance->vendor_mechanic }}</div>
                                    </div>
                                    <div class="flex items-center ml-auto">
                                        <div class="flex items-center justify-center {{ Carbon\Carbon::parse($maintenance->next_service_date)->isPast() ? 'bg-danger/20 text-danger' : 'bg-warning/20 text-warning' }} rounded-full p-2">
                                            <x-base.lucide class="w-4 h-4" icon="{{ Carbon\Carbon::parse($maintenance->next_service_date)->isPast() ? 'AlertTriangle' : 'Clock' }}" />
                                        </div>
                                        <a href="{{ route('admin.maintenance.show', $maintenance->id) }}" class="flex items-center ml-3 text-primary">
                                            <x-base.lucide class="w-4 h-4 mr-1" icon="Eye" /> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-slate-500">
                                No upcoming maintenances scheduled.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="box box--stacked mt-5">
                <div class="box-body p-5">
                    <div class="overflow-x-auto">
                        <h2 class="text-lg font-medium truncate mr-5">Recent Maintenance</h2>
                    </div>
                </div>
                <!-- Renderizar el componente Livewire -->
                <livewire:admin.vehicle.maintenance-list />
            </div>


        </div>
    </div>
@endsection
