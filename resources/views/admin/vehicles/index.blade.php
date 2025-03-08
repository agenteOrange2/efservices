@extends('../themes/' . $activeTheme)
@section('title', 'Vehículos')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehículos', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Vehículos
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary" href="{{ route('admin.vehicles.create') }}">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Agregar Nuevo Vehículo
                    </x-base.button>
                </div>
            </div>
            <div class="mt-3.5">
                <div class="box box--stacked flex flex-col">
                    <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                        <div>
                            <div class="relative">
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search" />
                                <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" type="text"
                                    placeholder="Buscar vehículos..." />
                            </div>
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                            <x-base.menu>
                                <x-base.menu.button class="w-full sm:w-auto" as="x-base.button" variant="outline-secondary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Download" />
                                    Exportar
                                    <x-base.lucide class="ml-2 h-4 w-4 stroke-[1.3]" icon="ChevronDown" />
                                </x-base.menu.button>
                                <x-base.menu.items class="w-40">
                                    <x-base.menu.item>
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                                        PDF
                                    </x-base.menu.item>
                                    <x-base.menu.item>
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                                        CSV
                                    </x-base.menu.item>
                                </x-base.menu.items>
                            </x-base.menu>
                            <x-base.popover class="inline-block">
                                <x-base.popover.button class="w-full sm:w-auto" as="x-base.button"
                                    variant="outline-secondary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowDownWideNarrow" />
                                    Filtrar
                                    <span
                                        class="ml-2 flex h-5 items-center justify-center rounded-full border bg-slate-100 px-1.5 text-xs font-medium">
                                        3
                                    </span>
                                </x-base.popover.button>
                                <x-base.popover.panel>
                                    <div class="p-2">
                                        <div>
                                            <div class="text-left text-slate-500">
                                                Estado
                                            </div>
                                            <x-base.form-select class="mt-2 flex-1">
                                                <option value="all">Todos</option>
                                                <option value="active">Activo</option>
                                                <option value="out_of_service">Fuera de Servicio</option>
                                                <option value="suspended">Suspendido</option>
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Tipo de Vehículo
                                            </div>
                                            <x-base.form-select class="mt-2 flex-1">
                                                <option value="all">Todos</option>
                                                @foreach ($vehicleTypes as $type)
                                                    <option value="{{ $type->name }}">{{ $type->name }}</option>
                                                @endforeach
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Marca
                                            </div>
                                            <x-base.form-select class="mt-2 flex-1">
                                                <option value="all">Todas</option>
                                                @foreach ($vehicleMakes as $make)
                                                    <option value="{{ $make->name }}">{{ $make->name }}</option>
                                                @endforeach
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-4 flex items-center">
                                            <x-base.button class="ml-auto w-32" variant="secondary">
                                                Cerrar
                                            </x-base.button>
                                            <x-base.button class="ml-2 w-32" variant="primary">
                                                Aplicar
                                            </x-base.button>
                                        </div>
                                    </div>
                                </x-base.popover.panel>
                            </x-base.popover>
                        </div>
                    </div>
                    <div class="overflow-auto xl:overflow-visible">
                        <x-base.table class="border-b border-slate-200/60">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.td
                                        class="w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        <x-base.form-check.input type="checkbox" />
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Unidad
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Marca/Modelo
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Tipo
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        VIN
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Registro
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Conductor
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                        Estado
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="w-36 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                        Acción
                                    </x-base.table.td>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach ($vehicles as $vehicle)
                                    <x-base.table.tr class="[&_td]:last:border-b-0">
                                        <x-base.table.td class="border-dashed py-4">
                                            <x-base.form-check.input type="checkbox" />
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="font-medium">{{ $vehicle->company_unit_number }}</div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4"

                                            class="whitespace-nowrap font-medium"
                                            href="{{ route('admin.vehicles.show', $vehicle->id) }}"
                                            >
                                            {{ $vehicle->make }} {{ $vehicle->model }}
                                            </a>
                                            <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">
                                                Año: {{ $vehicle->year }}
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="whitespace-nowrap">
                                                {{ $vehicle->type }}
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="whitespace-nowrap font-mono text-xs">
                                                {{ $vehicle->vin }}
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div>
                                                <div class="whitespace-nowrap">{{ $vehicle->registration_number }}</div>
                                                <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">
                                                    Vence: {{ $vehicle->registration_expiration_date->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="whitespace-nowrap">
                                                @if ($vehicle->driver)
                                                    {{ $vehicle->driver->user->name }}
                                                @else
                                                    <span class="text-slate-400">Sin asignar</span>
                                                @endif
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div @class([
                                                'flex items-center justify-center',
                                                'text-success' => !$vehicle->out_of_service && !$vehicle->suspended,
                                                'text-warning' => $vehicle->suspended,
                                                'text-danger' => $vehicle->out_of_service,
                                            ])>
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                                    icon="{{ !$vehicle->out_of_service && !$vehicle->suspended ? 'CheckCircle' : ($vehicle->suspended ? 'AlertTriangle' : 'XCircle') }}" />
                                                <div class="ml-1.5 whitespace-nowrap">
                                                    @if ($vehicle->out_of_service)
                                                        Fuera de Servicio
                                                    @elseif($vehicle->suspended)
                                                        Suspendido
                                                    @else
                                                        Activo
                                                    @endif
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="relative border-dashed py-4">
                                            <div class="flex items-center justify-center">
                                                <x-base.menu class="h-5">
                                                    <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                        <x-base.lucide
                                                            class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                            icon="MoreVertical" />
                                                    </x-base.menu.button>
                                                    <x-base.menu.items class="w-40">
                                                        <x-base.menu.item
                                                            href="{{ route('admin.vehicles.show', $vehicle->id) }}">
                                                            <x-base.lucide class="mr-2 h-4 w-4" icon="Eye" />
                                                            Ver Detalles
                                                        </x-base.menu.item>
                                                        <x-base.menu.item
                                                            href="{{ route('admin.vehicles.edit', $vehicle->id) }}">
                                                            <x-base.lucide class="mr-2 h-4 w-4" icon="CheckSquare" />
                                                            Editar
                                                        </x-base.menu.item>
                                                        <x-base.menu.item
                                                            href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}">
                                                            <x-base.lucide class="mr-2 h-4 w-4" icon="Tool" />
                                                            Servicio
                                                        </x-base.menu.item>
                                                        <x-base.menu.divider />
                                                        <x-base.menu.item class="text-danger" data-tw-toggle="modal"
                                                            data-tw-target="#delete-confirmation-modal-{{ $vehicle->id }}">
                                                            <x-base.lucide class="mr-2 h-4 w-4" icon="Trash2" />
                                                            Eliminar
                                                        </x-base.menu.item>
                                                    </x-base.menu.items>
                                                </x-base.menu>
                                            </div>
                                        </x-base.table.td>
                                    </x-base.table.tr>

                                    <!-- DELETE MODAL -->
                                    <x-base.dialog id="delete-confirmation-modal-{{ $vehicle->id }}" size="md">
                                        <x-base.dialog.panel>
                                            <div class="p-5 text-center">
                                                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger"
                                                    icon="XCircle" />
                                                <div class="mt-5 text-2xl">¿Estás seguro?</div>
                                                <div class="mt-2 text-slate-500">
                                                    ¿Realmente quieres eliminar este vehículo? <br>
                                                    Este proceso no se puede deshacer.
                                                </div>
                                            </div>
                                            <div class="px-5 pb-8 text-center">
                                                <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-base.button class="mr-1 w-24" data-tw-dismiss="modal"
                                                        type="button" variant="outline-secondary">
                                                        Cancelar
                                                    </x-base.button>
                                                    <x-base.button class="w-24" type="submit" variant="danger">
                                                        Eliminar
                                                    </x-base.button>
                                                </form>
                                            </div>
                                        </x-base.dialog.panel>
                                    </x-base.dialog>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    <div class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 p-5 sm:flex-row">
                        {{ $vehicles->links() }}
                        <x-base.form-select class="rounded-[0.5rem] sm:w-20">
                            <option>10</option>
                            <option>25</option>
                            <option>35</option>
                            <option>50</option>
                        </x-base.form-select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
