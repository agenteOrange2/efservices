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
@push('styles')
<style>
    .filter-btn.active {
        background-color: #1e40af;
        color: white;
    }
</style>
@endpush

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

                    <x-base.button as="a" href="{{ route('admin.vehicles.documents.index', $vehicle->id) }}"
                        class="w-full sm:w-44" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                        Documentos
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
                        <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">
                            <div class="flex justify-between items-center">
                                <span>Historial de Mantenimiento</span>
                                <div>
                                    <x-base.button as="a" href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}"
                                        class="w-full sm:w-auto" size="sm" variant="outline-primary">
                                        <x-base.lucide class="mr-1 h-3 w-3" icon="ListFilter" />
                                        Ver Historial Completo
                                    </x-base.button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body p-5">
                        <!-- Filtros rápidos -->
                        <div class="flex flex-wrap gap-3 mb-4">
                            <x-base.button as="a" href="#" data-filter="all"
                                class="w-full sm:w-auto filter-btn active" size="sm" variant="outline-secondary">
                                Todos
                            </x-base.button>
                            <x-base.button as="a" href="#" data-filter="pending"
                                class="w-full sm:w-auto filter-btn" size="sm" variant="outline-warning">
                                <x-base.lucide class="mr-1 h-3 w-3" icon="Clock" />
                                Pendientes
                            </x-base.button>
                            <x-base.button as="a" href="#" data-filter="completed"
                                class="w-full sm:w-auto filter-btn" size="sm" variant="outline-success">
                                <x-base.lucide class="mr-1 h-3 w-3" icon="CheckCircle" />
                                Completados
                            </x-base.button>
                            <x-base.button as="a" href="#" data-filter="overdue"
                                class="w-full sm:w-auto filter-btn" size="sm" variant="outline-danger">
                                <x-base.lucide class="mr-1 h-3 w-3" icon="AlertCircle" />
                                Vencidos
                            </x-base.button>
                            <div class="ml-auto">
                                <x-base.button as="a" data-tw-toggle="modal" data-tw-target="#add-service-modal"
                                    class="w-full sm:w-auto" size="sm" variant="primary">
                                    <x-base.lucide class="mr-1 h-3 w-3" icon="Plus" />
                                    Agregar Mantenimiento
                                </x-base.button>
                            </div>
                        </div>
                
                        <div class="overflow-x-auto">
                            <table class="table border w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50/60">
                                        <th class="font-medium text-slate-800 py-5">Fecha</th>
                                        <th class="font-medium text-slate-800 py-5">Servicio</th>
                                        <th class="font-medium text-slate-800 py-5">Proveedor</th>
                                        <th class="font-medium text-slate-800 py-5">Costo</th>
                                        <th class="font-medium text-slate-800 py-5">Próximo</th>
                                        <th class="font-medium text-slate-800 py-5">Estado</th>
                                        <th class="font-medium text-slate-800 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicle->serviceItems as $item)
                                        <tr class="maintenance-row {{ !$item->status && $item->isOverdue() ? 'overdue' : '' }} {{ !$item->status && !$item->isOverdue() ? 'pending' : '' }} {{ $item->status ? 'completed' : '' }}">
                                            <td>{{ $item->service_date->format('d/m/Y') }}</td>
                                            <td class="py-6">
                                                <div class="font-medium">{{ $item->service_tasks }}</div>
                                                @if($item->odometer)
                                                    <div class="text-xs text-slate-500">Odómetro: {{ number_format($item->odometer) }} mi</div>
                                                @endif
                                            </td>
                                            <td class="py-6">{{ $item->vendor_mechanic }}</td>
                                            <td class="py-6">${{ number_format($item->cost, 2) }}</td>
                                            <td >
                                                <div class="{{ !$item->status && $item->isOverdue() ? 'text-danger' : (!$item->status && $item->isUpcoming() ? 'text-warning' : '') }}">
                                                    {{ $item->next_service_date->format('d/m/Y') }}
                                                    @if(!$item->status && $item->isOverdue())
                                                        <div class="flex items-center text-xs text-danger mt-1">
                                                            <x-base.lucide class="h-3 w-3 mr-1" icon="AlertTriangle" />
                                                            Vencido ({{ $item->next_service_date->diffInDays(now()) }} días)
                                                        </div>
                                                    @elseif(!$item->status && $item->isUpcoming())
                                                        <div class="flex items-center text-xs text-warning mt-1">
                                                            <x-base.lucide class="h-3 w-3 mr-1" icon="Clock" />
                                                            En {{ $item->next_service_date->diffInDays(now()) }} días
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($item->status)
                                                    <div class="flex items-center text-success">
                                                        <x-base.lucide class="h-4 w-4 mr-1" icon="CheckCircle" />
                                                        Completado
                                                    </div>
                                                @else
                                                    <div class="flex items-center {{ $item->isOverdue() ? 'text-danger' : 'text-warning' }}">
                                                        <x-base.lucide class="h-4 w-4 mr-1" icon="{{ $item->isOverdue() ? 'AlertCircle' : 'Clock' }}" />
                                                        {{ $item->isOverdue() ? 'Vencido' : 'Pendiente' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="{{ route('admin.vehicles.service-items.show', [$vehicle->id, $item->id]) }}" 
                                                       class="btn btn-sm btn-primary p-1" title="Ver detalles">
                                                        <x-base.lucide class="h-4 w-4" icon="Eye" />
                                                    </a>
                                                    
                                                    <form action="{{ route('admin.service-items.toggle-status', [$vehicle->id, $item->id]) }}" 
                                                          method="POST" class="inline-block">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $item->status ? 'btn-warning' : 'btn-success' }} p-1"
                                                                title="{{ $item->status ? 'Marcar como pendiente' : 'Marcar como completado' }}">
                                                            <x-base.lucide class="h-4 w-4" icon="{{ $item->status ? 'RotateCcw' : 'Check' }}" />
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger p-1 delete-service-btn" 
                                                            data-service-id="{{ $item->id }}"
                                                            data-tw-toggle="modal" 
                                                            data-tw-target="#delete-service-modal"
                                                            title="Eliminar">
                                                        <x-base.lucide class="h-4 w-4" icon="Trash" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="flex flex-col items-center justify-center py-4">
                                                    <x-base.lucide class="h-10 w-10 text-slate-300" icon="ClipboardX" />
                                                    <p class="mt-2 text-slate-500">No hay registros de mantenimiento para este vehículo</p>
                                                    <x-base.button as="a" data-tw-toggle="modal" data-tw-target="#add-service-modal"
                                                        class="mt-3" size="sm" variant="outline-primary">
                                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Plus" />
                                                        Registrar Primer Mantenimiento
                                                    </x-base.button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                
                        <!-- Sección de estadísticas de mantenimiento -->
                        @if($vehicle->serviceItems->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium">{{ $vehicle->serviceItems->count() }}</div>
                                    <div class="text-slate-500 text-sm">Total de mantenimientos</div>
                                </div>
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium text-success">{{ $vehicle->serviceItems->where('status', true)->count() }}</div>
                                    <div class="text-slate-500 text-sm">Completados</div>
                                </div>
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium text-warning">{{ $vehicle->serviceItems->where('status', false)->count() }}</div>
                                    <div class="text-slate-500 text-sm">Pendientes</div>
                                </div>
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium">${{ number_format($vehicle->serviceItems->sum('cost'), 2) }}</div>
                                    <div class="text-slate-500 text-sm">Gasto Total</div>
                                </div>
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

    <!-- Modificar el Modal para agregar servicio para incluir el campo status -->
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

                    <!-- Agregar campo de status -->
                    <div class="col-span-12">
                        <div class="form-check">
                            <input type="checkbox" id="status" name="status" value="1"
                                class="form-check-input">
                            <label for="status" class="form-check-label">Marcar como Completado</label>
                        </div>
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

    <!-- Modificar el Modal para editar servicio para incluir el campo status -->
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

                    <!-- Agregar campo de status -->
                    <div class="col-span-12">
                        <div class="form-check">
                            <input type="checkbox" id="edit_status" name="status" value="1"
                                class="form-check-input">
                            <label for="edit_status" class="form-check-label">Marcar como Completado</label>
                        </div>
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
            // Filtrado de mantenimientos
            const filterButtons = document.querySelectorAll('.filter-btn');
            const maintenanceRows = document.querySelectorAll('.maintenance-row');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remover clase activa de todos los botones
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Agregar clase activa al botón seleccionado
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    
                    // Mostrar/ocultar filas según el filtro
                    maintenanceRows.forEach(row => {
                        if (filter === 'all') {
                            row.style.display = '';
                        } else if (filter === 'pending' && row.classList.contains('pending')) {
                            row.style.display = '';
                        } else if (filter === 'completed' && row.classList.contains('completed')) {
                            row.style.display = '';
                        } else if (filter === 'overdue' && row.classList.contains('overdue')) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
            
            // Estilo para el botón de filtro activo
            document.querySelector('.filter-btn.active').click();
        });
    </script>
    @endpush
    

@endsection
