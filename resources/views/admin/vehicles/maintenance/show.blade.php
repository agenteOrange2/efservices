@extends('../themes/' . $activeTheme)
@section('title', 'Mantenimiento de Vehículos')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Mantenimiento', 'active' => true],
    ];
@endphp


@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Detalle de Mantenimiento #{{ $maintenance->id }}
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            @if (!$maintenance->status)
                <button type="button" data-tw-toggle="modal" data-tw-target="#reschedule-modal"
                    class="btn btn-warning shadow-md mr-2">
                    <i class="w-4 h-4 mr-2" data-lucide="calendar"></i> Reprogramar
                </button>
            @endif
            <a href="{{ route('admin.maintenance.edit', $maintenance->id) }}" class="btn btn-primary shadow-md mr-2">
                <i class="w-4 h-4 mr-2" data-lucide="edit"></i> Editar
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-secondary shadow-md">
                <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col xl:flex-row gap-6">
            <!-- Información del Vehículo -->
            <div class="flex-1">
                <h2 class="text-lg font-medium truncate mr-5">
                    Información del Vehículo
                </h2>
                <div class="mt-4">
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 pb-4">
                        <div class="font-medium w-40">Vehículo:</div>
                        <div>{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</div>
                    </div>
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 py-4">
                        <div class="font-medium w-40">Placa:</div>
                        <div>{{ $vehicle->license_plate }}</div>
                    </div>
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 py-4">
                        <div class="font-medium w-40">VIN:</div>
                        <div>{{ $vehicle->vin }}</div>
                    </div>
                </div>
            </div>

            <!-- Información del Mantenimiento -->
            <div class="flex-1">
                <h2 class="text-lg font-medium truncate mr-5">
                    Detalles del Mantenimiento
                </h2>
                <div class="mt-4">
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 pb-4">
                        <div class="font-medium w-40">Tipo:</div>
                        <div>{{ $maintenance->service_type }}</div>
                    </div>
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 py-4">
                        <div class="font-medium w-40">Fecha de Servicio:</div>
                        <div>
                            {{ $maintenance->service_date ? $maintenance->service_date->format('d/m/Y') : 'No establecida' }}
                        </div>
                    </div>
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 py-4">
                        <div class="font-medium w-40">Próximo Servicio:</div>
                        <div>
                            {{ $maintenance->next_service_date ? $maintenance->next_service_date->format('d/m/Y') : 'No establecida' }}
                        </div>
                    </div>
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 py-4">
                        <div class="font-medium w-40">Kilometraje:</div>
                        <div>{{ number_format($maintenance->odometer_reading) }} km</div>
                    </div>
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 py-4">
                        <div class="font-medium w-40">Costo:</div>
                        <div>${{ number_format($maintenance->cost, 2) }}</div>
                    </div>
                    <div class="flex border-b border-slate-200 dark:border-darkmode-400 py-4">
                        <div class="font-medium w-40">Estado:</div>
                        <div>
                            @if ($maintenance->status)
                                <span class="px-2 py-1 rounded-full bg-success text-white">Completado</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-warning text-white">Pendiente</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notas y Descripción -->
        <div class="mt-6">
            <h2 class="text-lg font-medium truncate mr-5">
                Descripción y Notas
            </h2>
            <div class="mt-4 p-4 bg-slate-50 dark:bg-darkmode-600 rounded-md">
                {!! nl2br(e($maintenance->description)) !!}
            </div>
        </div>

        <!-- Documentos (si existen) -->
        @if ($maintenance->getMedia('maintenance_documents')->count() > 0)
            <div class="mt-6">
                <h2 class="text-lg font-medium truncate mr-5">
                    Documentos
                </h2>
                <div class="mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($maintenance->getMedia('maintenance_documents') as $media)
                            <div class="border rounded-md p-4 flex items-center">
                                <div class="mr-4">
                                    @if (str_contains($media->mime_type, 'image'))
                                        <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}"
                                            class="w-12 h-12 object-cover">
                                    @else
                                        <i data-lucide="file-text" class="w-12 h-12 text-primary"></i>
                                    @endif
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <p class="font-medium truncate">{{ $media->file_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $media->human_readable_size }}</p>
                                </div>
                                <div class="ml-4 flex">
                                    <a href="{{ $media->getUrl() }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary mr-1">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.maintenance.documents.download', $media->id) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal de Reprogramación -->
    <div id="reschedule-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Reprogramar Mantenimiento</h2>
                </div>
                <form action="{{ route('admin.maintenance.reschedule', $maintenance->id) }}" method="POST">
                    @csrf
                    <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12">
                            <label for="next_service_date" class="form-label">Nueva fecha de servicio</label>
                            <input type="date" id="next_service_date" name="next_service_date" class="form-control"
                                min="{{ now()->addDay()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-span-12">
                            <label for="reschedule_reason" class="form-label">Motivo de la reprogramación</label>
                            <textarea id="reschedule_reason" name="reschedule_reason" class="form-control" rows="4"
                                placeholder="Explique por qué se reprograma este mantenimiento..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-24 mr-1">Cancelar</button>
                        <button type="submit" class="btn btn-primary w-24">Reprogramar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

