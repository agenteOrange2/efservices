@extends('../themes/' . $activeTheme)
@section('title', 'Detalles del Vehículo')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
        ['label' => 'Detalles del Vehículo', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium">
                    Vehículo: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
                </div>
                <div class="flex flex-col gap-x-3 gap-y-6 sm:flex-row md:ml-auto">
                    {{-- <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary flex align-middle">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Editar Vehículo
                    </a> --}}
                    {{-- <a href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}"
                        class="btn btn-outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Tool" />
                        Historial de Servicio
                    </a> --}}
                    <x-base.button as="a" href="{{ route('admin.vehicles.edit', $vehicle->id) }}"
                        class="w-full sm:w-44" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Editar Vehículo
                    </x-base.button>

                    <x-base.button as="a" href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}"
                        class="w-54" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="Activity" />
                        Historial de Servicio
                    </x-base.button>
                </div>
            </div>

            <!-- Estado y alertas -->
            <div class="mt-3">
                @if ($vehicle->suspended)
                    <div class="alert alert-warning flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="AlertTriangle" />
                        <span>Este vehículo está <strong>SUSPENDIDO</strong> desde
                            {{ $vehicle->suspended_date->format('d/m/Y') }}</span>
                    </div>
                @endif

                @if ($vehicle->out_of_service)
                    <div class="alert alert-danger flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="XCircle" />
                        <span>Este vehículo está <strong>FUERA DE SERVICIO</strong> desde
                            {{ $vehicle->out_of_service_date->format('d/m/Y') }}</span>
                    </div>
                @endif

                @if ($vehicle->registration_expiration_date < now())
                    <div class="alert alert-danger flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="CalendarX" />
                        <span>El registro de este vehículo <strong>EXPIRÓ</strong> el
                            {{ $vehicle->registration_expiration_date->format('d/m/Y') }}</span>
                    </div>
                @elseif($vehicle->registration_expiration_date < now()->addDays(30))
                    <div class="alert alert-warning flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="Calendar" />
                        <span>El registro expira en <strong>{{ $vehicle->registration_expiration_date->diffInDays(now()) }}
                                días</strong> ({{ $vehicle->registration_expiration_date->format('d/m/Y') }})</span>
                    </div>
                @endif

                @if (isset($vehicle->annual_inspection_expiration_date))
                    @if ($vehicle->annual_inspection_expiration_date < now())
                        <div class="alert alert-danger flex items-center mb-2">
                            <x-base.lucide class="mr-2 h-6 w-6" icon="ClipboardX" />
                            <span>La inspección anual <strong>EXPIRÓ</strong> el
                                {{ $vehicle->annual_inspection_expiration_date->format('d/m/Y') }}</span>
                        </div>
                    @elseif($vehicle->annual_inspection_expiration_date < now()->addDays(30))
                        <div class="alert alert-warning flex items-center mb-2">
                            <x-base.lucide class="mr-2 h-6 w-6" icon="Clipboard" />
                            <span>La inspección anual expira en
                                <strong>{{ $vehicle->annual_inspection_expiration_date->diffInDays(now()) }} días</strong>
                                ({{ $vehicle->annual_inspection_expiration_date->format('d/m/Y') }})</span>
                        </div>
                    @endif
                @endif
            </div>

            <div class="mt-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Información General -->
                    <div class="box box--stacked">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Información General</div>
                        </div>
                        <div class="box-body p-5">
                            <table class="w-full">
                                <tr class="border-b border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Transportista:</td>
                                    <td class="py-2">{{ $vehicle->carrier->name }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Marca:</td>
                                    <td class="py-2">{{ $vehicle->make }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Modelo:</td>
                                    <td class="py-2">{{ $vehicle->model }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Año:</td>
                                    <td class="py-2">{{ $vehicle->year }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Tipo:</td>
                                    <td class="py-2">{{ $vehicle->type }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Número de Unidad:</td>
                                    <td class="py-2">{{ $vehicle->company_unit_number ?? 'No asignado' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">VIN:</td>
                                    <td class="py-2 font-mono">{{ $vehicle->vin }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">GVWR:</td>
                                    <td class="py-2">{{ $vehicle->gvwr ?? 'No especificado' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Tipo de Combustible:</td>
                                    <td class="py-2">{{ $vehicle->fuel_type }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Tipo de Propiedad:</td>
                                    <td class="py-2">{{ $vehicle->ownership_type == 'owned' ? 'Propio' : 'Arrendado' }}
                                    </td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Ubicación:</td>
                                    <td class="py-2">{{ $vehicle->location ?? 'No especificada' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Tamaño de Neumáticos:</td>
                                    <td class="py-2">{{ $vehicle->tire_size ?? 'No especificado' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">IRP:</td>
                                    <td class="py-2">{{ $vehicle->irp_apportioned_plate ? 'Sí' : 'No' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Registro e Inspección -->
                    <div class="box box--stacked">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Registro e Inspección</div>
                        </div>
                        <div class="box-body p-5">
                            <table class="w-full">
                                <tr class="border-b border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Estado de Registro:</td>
                                    <td class="py-2">{{ $vehicle->registration_state }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Número de Registro:</td>
                                    <td class="py-2">{{ $vehicle->registration_number }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Vencimiento Registro:</td>
                                    <td class="py-2">
                                        <span
                                            class="{{ $vehicle->registration_expiration_date < now() ? 'text-danger' : ($vehicle->registration_expiration_date < now()->addDays(30) ? 'text-warning' : 'text-success') }}">
                                            {{ $vehicle->registration_expiration_date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Etiqueta Permanente:</td>
                                    <td class="py-2">{{ $vehicle->permanent_tag ? 'Sí' : 'No' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Inspección Anual:</td>
                                    <td class="py-2">
                                        @if (isset($vehicle->annual_inspection_expiration_date))
                                            <span
                                                class="{{ $vehicle->annual_inspection_expiration_date < now() ? 'text-danger' : ($vehicle->annual_inspection_expiration_date < now()->addDays(30) ? 'text-warning' : 'text-success') }}">
                                                {{ $vehicle->annual_inspection_expiration_date->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">No registrada</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Conductor Asignado -->
                            <div class="mt-6">
                                <h3 class="text-base font-medium mb-3 border-b border-slate-200/60 bg-slate-50 pb-3">
                                    Conductor Asignado</h3>
                                @if ($vehicle->driver)
                                    <div class="flex items-center">
                                        <div
                                            class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden">
                                            @if ($vehicle->driver->getFirstMediaUrl('profile_photo_driver'))
                                                <img src="{{ $vehicle->driver->getFirstMediaUrl('profile_photo_driver') }}"
                                                    alt="Foto de perfil" class="w-full h-full object-cover">
                                            @else
                                                <x-base.lucide class="h-6 w-6 text-slate-500" icon="User" />
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <div class="font-medium">{{ $vehicle->driver->user->name }}
                                                {{ $vehicle->driver->last_name }}</div>
                                            <div class="text-slate-500 text-xs">{{ $vehicle->driver->phone }}</div>
                                            <a href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $vehicle->carrier->slug, 'userDriverDetail' => $vehicle->driver->id]) }}"
                                                class="text-primary text-xs">Ver perfil</a>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                            <x-base.lucide class="h-6 w-6 text-slate-400" icon="UserX" />
                                        </div>
                                        <div class="ml-3 text-slate-500">
                                            Sin conductor asignado
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Estado -->
                            <div class="mt-6">
                                <h3 class="text-base font-medium mb-3 border-b border-slate-200/60 bg-slate-50 pb-3">Estado
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-center">
                                        <div
                                            class="{{ $vehicle->out_of_service ? 'bg-danger text-white' : 'bg-slate-100 text-slate-500' }} w-8 h-8 rounded-full flex items-center justify-center">
                                            <x-base.lucide class="h-4 w-4" icon="XCircle" />
                                        </div>
                                        <div class="ml-2">
                                            <div class="text-sm">Fuera de Servicio</div>
                                            @if ($vehicle->out_of_service)
                                                <div class="text-xs text-danger">Desde
                                                    {{ $vehicle->out_of_service_date->format('d/m/Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div
                                            class="{{ $vehicle->suspended ? 'bg-warning text-white' : 'bg-slate-100 text-slate-500' }} w-8 h-8 rounded-full flex items-center justify-center">
                                            <x-base.lucide class="h-4 w-4" icon="AlertTriangle" />
                                        </div>
                                        <div class="ml-2">
                                            <div class="text-sm">Suspendido</div>
                                            @if ($vehicle->suspended)
                                                <div class="text-xs text-warning">Desde
                                                    {{ $vehicle->suspended_date->format('d/m/Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                @if ($vehicle->notes)
                    <div class="box box--stacked mt-5">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Notas</div>
                        </div>
                        <div class="box-body p-5">
                            <div class="whitespace-pre-line">{{ $vehicle->notes }}</div>
                        </div>
                    </div>
                @endif

                <!-- Historial de Servicio Reciente -->
                <div class="box box--stacked mt-5">
                    <div class="box-header">
                        <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Historial de Servicio Reciente
                        </div>
                    </div>
                    <div class="box-body p-5">
                        <!-- Botones arriba de la tabla -->
                        <div class="flex flex-wrap gap-8 mb-4">
                            {{-- <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary flex align-middle">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                                Volver a la lista
                            </a> --}}
                            {{-- <button type="button" class="btn btn-outline-primary flex align-middle" data-tw-toggle="modal"
                                data-tw-target="#add-service-modal">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="Plus" />
                                Agregar Servicio
                            </button> --}}

                            <x-base.button as="a" href="{{ route('admin.vehicles.index') }}"
                                class="w-full sm:w-44" variant="primary">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                                Volver a la lista
                            </x-base.button>

                            <x-base.button as="a" data-tw-toggle="modal" data-tw-target="#add-service-modal"
                                class="w-full sm:w-44" variant="primary">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="Plus" />
                                Agregar Servicio
                            </x-base.button>
                        </div>

                        @if ($vehicle->serviceItems->count() > 0)
                            <div class="overflow-auto xl:overflow-visible">
                                <x-base.table class="border-b border-slate-200/60">
                                    <x-base.table.thead>
                                        <x-base.table.tr>
                                            <x-base.table.td
                                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                                Servicio
                                            </x-base.table.td>

                                            <x-base.table.td
                                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                                Proveedor
                                            </x-base.table.td>

                                            <x-base.table.td
                                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                                Costo
                                            </x-base.table.td>

                                            <x-base.table.td
                                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                                Fecha de Servicio
                                            </x-base.table.td>
                                            <x-base.table.td
                                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                                Próximo Servicio
                                            </x-base.table.td>
                                            <x-base.table.td
                                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                                Action
                                            </x-base.table.td>
                                        </x-base.table.tr>
                                    </x-base.table.thead>
                                    <x-base.table.tbody>
                                        @foreach ($vehicle->serviceItems->take(5) as $item)
                                            <x-base.table.tr class="[&_td]:last:border-b-0">
                                                <x-base.table.td class="border-dashed py-4">
                                                    <div class="whitespace-nowrap">
                                                        {{ $item->service_tasks }}
                                                    </div>
                                                </x-base.table.td>

                                                <x-base.table.td class="border-dashed py-4">
                                                    <div class="whitespace-nowrap">
                                                        {{ $item->vendor_mechanic }}
                                                    </div>
                                                </x-base.table.td>

                                                <x-base.table.td class="border-dashed py-4">
                                                    <div class="whitespace-nowrap">
                                                        ${{ number_format($item->cost, 2) }}
                                                    </div>
                                                </x-base.table.td>

                                                <x-base.table.td class="border-dashed py-4">
                                                    <div class="whitespace-nowrap">
                                                        {{ $item->service_date->format('d/m/Y') }}
                                                    </div>
                                                </x-base.table.td>


                                                <x-base.table.td class="border-dashed py-4">
                                                    <div
                                                        class="{{ $item->next_service_date < now() ? 'text-danger' : '' }}">
                                                        {{ $item->next_service_date->format('d/m/Y') }}
                                                    </div>
                                                </x-base.table.td>
                                                <x-base.table.td class="relative border-dashed py-4">
                                                    <div class="flex items-center justify-center">
                                                        <!-- Botones de acción en cada fila -->
                                                        <div class="flex flex-wrap gap-2">
                                                            {{-- <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}"
                                                                class="btn btn-primary btn-sm">
                                                                <x-base.lucide class="h-4 w-4" icon="PenLine" />
                                                            </a> --}}

                                                            <button type="button"
                                                                class="btn btn-primary btn-sm edit-service-btn"
                                                                data-service-id="{{ $item->id }}"
                                                                data-service-date="{{ $item->service_date->format('Y-m-d') }}"
                                                                data-next-service-date="{{ $item->next_service_date->format('Y-m-d') }}"
                                                                data-unit="{{ $item->unit }}"
                                                                data-service-tasks="{{ $item->service_tasks }}"
                                                                data-vendor-mechanic="{{ $item->vendor_mechanic }}"
                                                                data-cost="{{ $item->cost }}"
                                                                data-odometer="{{ $item->odometer }}"
                                                                data-description="{{ $item->description }}"
                                                                data-tw-toggle="modal"
                                                                data-tw-target="#edit-service-modal">
                                                                <x-base.lucide class="h-4 w-4" icon="PenLine" />
                                                            </button>
                                                            {{-- <button type="button" class="btn btn-outline-danger btn-sm"
                                                                data-tw-toggle="modal"
                                                                data-tw-target="#delete-confirmation-modal">
                                                                <x-base.lucide class="h-4 w-4" icon="Trash2" />
                                                            </button> --}}
                                                            <button type="button" class="btn btn-outline-danger btn-sm delete-service-btn"
                                                            data-service-id="{{ $item->id }}"
                                                            data-tw-toggle="modal"
                                                            data-tw-target="#delete-service-modal">
                                                        <x-base.lucide class="h-4 w-4" icon="Trash2" />
                                                    </button>
                                                        </div>
                                                    </div>
                                                </x-base.table.td>
                                            </x-base.table.tr>
                                        @endforeach
                                    </x-base.table.tbody>
                                </x-base.table>

                                @if ($vehicle->serviceItems->count() > 5)
                                    <div class="mt-3 text-center">
                                        <a href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            Ver Historial Completo
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-6 text-slate-500">
                                    <x-base.lucide class="mx-auto h-12 w-12 text-slate-300" icon="ClipboardList" />
                                    <p class="mt-2">No hay registros de servicio</p>
                                    {{-- <button type="button" class="btn btn-outline-primary btn-sm mt-3"
                                        data-tw-toggle="modal" data-tw-target="#add-service-modal">
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Plus" />
                                        Agregar Servicio
                                    </button> --}}
                                </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <x-base.dialog id="delete-service-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="XCircle" />
                <div class="mt-5 text-2xl">¿Estás seguro?</div>
                <div class="mt-2 text-slate-500">
                    ¿Realmente quieres eliminar este registro de servicio? <br>
                    Este proceso no se puede deshacer.
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <form id="delete-service-form" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancelar
                    </x-base.button>
                    <x-base.button class="w-24" type="submit" variant="danger">
                        Eliminar
                    </x-base.button>
                </form>
            </div>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal para agregar servicio -->
    <x-base.dialog id="add-service-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">
                    Agregar Nuevo Servicio de Mantenimiento
                </h2>
            </x-base.dialog.title>
            <form action="{{ route('admin.vehicles.service-items.store', $vehicle->id) }}" method="POST">
                @csrf
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Primera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="service_date">Fecha de Servicio</x-base.form-label>
                        <x-base.form-input id="service_date" name="service_date" type="date" required
                            value="{{ date('Y-m-d') }}" />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="next_service_date">Fecha Próximo Servicio</x-base.form-label>
                        <x-base.form-input id="next_service_date" name="next_service_date" type="date" required
                            value="{{ date('Y-m-d', strtotime('+3 months')) }}" />
                        <div id="date-error" class="text-danger text-xs mt-1 hidden">
                            La fecha del próximo servicio debe ser posterior a la fecha de servicio.
                        </div>
                    </div>

                    <!-- Segunda fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="unit">Unidad</x-base.form-label>
                        <x-base.form-input id="unit" name="unit" type="text" placeholder="Número de unidad"
                            value="{{ $vehicle->company_unit_number }}" required readonly />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="service_tasks">Tareas de Servicio</x-base.form-label>
                        <x-base.form-input id="service_tasks" name="service_tasks" type="text"
                            placeholder="Ej: Cambio de aceite, inspección de frenos" required />
                    </div>

                    <!-- Tercera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="vendor_mechanic">Proveedor/Mecánico</x-base.form-label>
                        <x-base.form-input id="vendor_mechanic" name="vendor_mechanic" type="text"
                            placeholder="Ej: Taller Mecánico XYZ" required />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="cost">Costo ($)</x-base.form-label>
                        <x-base.form-input id="cost" name="cost" type="number" step="0.01" min="0"
                            placeholder="0.00" required />
                    </div>

                    <!-- Cuarta fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="odometer">Lectura del Odómetro</x-base.form-label>
                        <x-base.form-input id="odometer" name="odometer" type="number" min="0"
                            placeholder="Ej: 50000" />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="description">Descripción</x-base.form-label>
                        <x-base.form-textarea id="description" name="description"
                            placeholder="Detalles adicionales sobre el servicio" rows="3"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancelar
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="primary" id="submit-service">
                        Guardar
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal para editar servicio -->
    <x-base.dialog id="edit-service-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">
                    Editar Servicio de Mantenimiento
                </h2>
            </x-base.dialog.title>
            <form id="edit-service-form" action="" method="POST">
                @csrf
                @method('PUT')
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Primera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_service_date">Fecha de Servicio</x-base.form-label>
                        <x-base.form-input id="edit_service_date" name="service_date" type="date" required />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_next_service_date">Fecha Próximo Servicio</x-base.form-label>
                        <x-base.form-input id="edit_next_service_date" name="next_service_date" type="date"
                            required />
                        <div id="edit-date-error" class="text-danger text-xs mt-1 hidden">
                            La fecha del próximo servicio debe ser posterior a la fecha de servicio.
                        </div>
                    </div>

                    <!-- Segunda fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_unit">Unidad</x-base.form-label>
                        <x-base.form-input id="edit_unit" name="unit" type="text" placeholder="Número de unidad"
                            required readonly />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_service_tasks">Tareas de Servicio</x-base.form-label>
                        <x-base.form-input id="edit_service_tasks" name="service_tasks" type="text"
                            placeholder="Ej: Cambio de aceite, inspección de frenos" required />
                    </div>

                    <!-- Tercera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_vendor_mechanic">Proveedor/Mecánico</x-base.form-label>
                        <x-base.form-input id="edit_vendor_mechanic" name="vendor_mechanic" type="text"
                            placeholder="Ej: Taller Mecánico XYZ" required />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_cost">Costo ($)</x-base.form-label>
                        <x-base.form-input id="edit_cost" name="cost" type="number" step="0.01" min="0"
                            placeholder="0.00" required />
                    </div>

                    <!-- Cuarta fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_odometer">Lectura del Odómetro</x-base.form-label>
                        <x-base.form-input id="edit_odometer" name="odometer" type="number" min="0"
                            placeholder="Ej: 50000" />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_description">Descripción</x-base.form-label>
                        <x-base.form-textarea id="edit_description" name="description"
                            placeholder="Detalles adicionales sobre el servicio" rows="3"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancelar
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="primary" id="update-service">
                        Actualizar
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Validación de fechas para el modal de agregar
                const serviceDateInput = document.getElementById('service_date');
                const nextServiceDateInput = document.getElementById('next_service_date');
                const dateError = document.getElementById('date-error');
                const submitBtn = document.getElementById('submit-service');

                function validateDates() {
                    if (serviceDateInput.value && nextServiceDateInput.value) {
                        const serviceDate = new Date(serviceDateInput.value);
                        const nextServiceDate = new Date(nextServiceDateInput.value);

                        if (nextServiceDate <= serviceDate) {
                            dateError.classList.remove('hidden');
                            submitBtn.disabled = true;
                            return false;
                        } else {
                            dateError.classList.add('hidden');
                            submitBtn.disabled = false;
                            return true;
                        }
                    }
                    return true;
                }

                serviceDateInput.addEventListener('change', validateDates);
                nextServiceDateInput.addEventListener('change', validateDates);

                // Validar al cargar la página
                validateDates();

                // Validación de fechas para el modal de edición
                const editServiceDateInput = document.getElementById('edit_service_date');
                const editNextServiceDateInput = document.getElementById('edit_next_service_date');
                const editDateError = document.getElementById('edit-date-error');
                const updateBtn = document.getElementById('update-service');

                function validateEditDates() {
                    if (editServiceDateInput.value && editNextServiceDateInput.value) {
                        const serviceDate = new Date(editServiceDateInput.value);
                        const nextServiceDate = new Date(editNextServiceDateInput.value);

                        if (nextServiceDate <= serviceDate) {
                            editDateError.classList.remove('hidden');
                            updateBtn.disabled = true;
                            return false;
                        } else {
                            editDateError.classList.add('hidden');
                            updateBtn.disabled = false;
                            return true;
                        }
                    }
                    return true;
                }

                editServiceDateInput.addEventListener('change', validateEditDates);
                editNextServiceDateInput.addEventListener('change', validateEditDates);

                // Manejar los botones de edición
                document.querySelectorAll('.edit-service-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const serviceId = this.getAttribute('data-service-id');
                        const formAction =
                            "{{ route('admin.vehicles.service-items.index', $vehicle->id) }}/" +
                            serviceId;

                        // Actualizar acción del formulario
                        document.getElementById('edit-service-form').action = formAction;

                        // Rellenar el formulario con los datos del servicio
                        document.getElementById('edit_service_date').value = this.getAttribute(
                            'data-service-date');
                        document.getElementById('edit_next_service_date').value = this.getAttribute(
                            'data-next-service-date');
                        document.getElementById('edit_unit').value = this.getAttribute('data-unit');
                        document.getElementById('edit_service_tasks').value = this.getAttribute(
                            'data-service-tasks');
                        document.getElementById('edit_vendor_mechanic').value = this.getAttribute(
                            'data-vendor-mechanic');
                        document.getElementById('edit_cost').value = this.getAttribute('data-cost');
                        document.getElementById('edit_odometer').value = this.getAttribute(
                            'data-odometer') || '';
                        document.getElementById('edit_description').value = this.getAttribute(
                            'data-description') || '';

                        // Validar fechas
                        validateEditDates();
                    });
                });

                // Manejar los botones de eliminación
                document.querySelectorAll('.delete-service-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const serviceId = this.getAttribute('data-service-id');
                        const formAction =
                            "{{ route('admin.vehicles.service-items.index', $vehicle->id) }}/" +
                            serviceId;

                        // Actualizar acción del formulario
                        document.getElementById('delete-service-form').action = formAction;
                    });
                });
            });
        </script>
    @endpush
@endsection
