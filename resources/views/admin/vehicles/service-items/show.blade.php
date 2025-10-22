@extends('../themes/' . $activeTheme)
@section('title', 'Detail Maintenance')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => $vehicle->make . ' ' . $vehicle->model, 'url' => route('admin.vehicles.show', $vehicle->id)],
        ['label' => 'Maintenances', 'url' => route('admin.vehicles.maintenances.index', $vehicle->id)],
        ['label' => 'Detail Maintenance', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Detail Maintenance
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                    class="w-full sm:w-auto" variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Maintenances
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.vehicles.maintenances.edit', [$vehicle->id, $serviceItem->id]) }}"
                    class="w-full sm:w-auto" variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="Edit" />
                    Edit
                </x-base.button>
            </div>
        </div>

        <div class="row mt-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <div class="box box--stacked">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Service Information</div>
                        </div>
                        <div class="box-body p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <h5 class="font-medium">Vehicle Unit/System:</h5>
                                    <p>{{ $serviceItem->unit }}</p>
                                </div>
                                <div>
                                    <h5 class="font-medium">Performed Tasks:</h5>
                                    <p>{{ $serviceItem->service_tasks }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-4">
                                <div>
                                    <h5 class="font-medium">Service Date:</h5>
                                    <p>{{ $serviceItem->service_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <h5 class="font-medium">Next Service Date:</h5>
                                    <p>
                                        {{ $serviceItem->next_service_date->format('d/m/Y') }}
                                        @if($serviceItem->isOverdue() && !$serviceItem->status)
                                            <span class="badge bg-danger text-white">Overdue</span>
                                        @elseif($serviceItem->isUpcoming() && !$serviceItem->status)
                                            <span class="badge bg-warning text-white">Upcoming</span>
                                        @elseif($serviceItem->status)
                                            <span class="badge bg-success text-white">Completed</span>
                                        @else
                                            <span class="badge bg-success text-white">On Time</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <h5 class="font-medium">Odometer:</h5>
                                    <p>{{ $serviceItem->odometer ? number_format($serviceItem->odometer) . ' miles' : 'Not registered' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <h5 class="font-medium">Provider/Mechanic:</h5>
                                    <p>{{ $serviceItem->vendor_mechanic }}</p>
                                </div>
                                <div>
                                    <h5 class="font-medium">Cost:</h5>
                                    <p>${{ number_format($serviceItem->cost, 2) }}</p>
                                </div>
                            </div>

                            @if($serviceItem->description)
                                <div class="border-t border-slate-200/60 pt-4 mt-4">
                                    <h5 class="font-medium">Description/Notes:</h5>
                                    <p class="mt-2">{{ $serviceItem->description }}</p>
                                </div>
                            @endif

                            <!-- Archivos de mantenimiento -->                            
                            @if($serviceItem->getMedia('maintenance_files')->count() > 0)
                            <div class="mt-4 pt-3 border-t border-slate-200/60">
                                <h5 class="font-medium mb-3">Tickets de Servicio:</h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach($serviceItem->getMedia('maintenance_files') as $media)
                                        <div class="border rounded p-3 bg-slate-50 flex flex-col">
                                            <div class="flex items-center mb-2">
                                                @if(Str::contains($media->mime_type, 'image'))
                                                    <img src="{{ $media->getUrl() }}" class="h-12 w-12 object-cover rounded mr-3" />
                                                @else
                                                    <x-base.lucide class="h-12 w-12 text-danger mr-3" icon="FileText" />
                                                @endif
                                                <div class="overflow-hidden">
                                                    <p class="text-sm font-medium truncate">{{ $media->file_name }}</p>
                                                    <p class="text-xs text-slate-500">{{ $media->human_readable_size }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-between mt-auto pt-2">
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="flex items-center btn btn-sm btn-outline-primary">
                                                    <x-base.lucide class="h-4 w-4 mr-1" icon="Eye" /> View
                                                </a>
                                                <a href="{{ $media->getUrl() }}" download class="flex items-center btn btn-sm btn-outline-secondary">
                                                    <x-base.lucide class="h-4 w-4 mr-1" icon="Download" /> Download
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="mt-4 pt-3 border-t border-slate-200/60 flex justify-between items-center">
                                <div>
                                    <small class="text-slate-500">Creado: {{ $serviceItem->created_at->format('d/m/Y H:i') }}</small>
                                    @if($serviceItem->updated_at->ne($serviceItem->created_at))
                                        <br>
                                        <small class="text-slate-500">Updated: {{ $serviceItem->updated_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                </div>
                                
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.vehicles.maintenances.toggle-status', [$vehicle->id, $serviceItem->id]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn flex {{ $serviceItem->status ? 'btn-warning' : 'btn-success' }} mr-2">
                                            <x-base.lucide class="h-4 w-4 mr-1" icon="{{ $serviceItem->status ? 'RotateCcw' : 'CheckCircle' }}" />
                                            {{ $serviceItem->status ? 'Mark as Pending' : 'Mark as Completed' }}
                                        </button>
                                    </form>
                                    
                                    <button type="button" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal" class="btn btn-danger flex">
                                        <x-base.lucide class="h-4 w-4 mr-1" icon="Trash" />
                                        Delete Record
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1">
                    <div class="box box--stacked mb-5">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Vehicle Details</div>
                        </div>
                        <div class="box-body p-5">
                            <div class="text-center mb-3">
                                <x-base.lucide class="h-16 w-16 mx-auto text-slate-300" icon="Truck" />
                            </div>
                            
                            <h5 class="font-medium text-center">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h5>
                            <p class="text-center text-slate-500 mb-4">VIN: {{ $vehicle->vin }}</p>
                            
                            <div class="border-t border-slate-200/60 pt-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="text-slate-500">Type:</div>
                                    <div class="text-right">{{ $vehicle->type }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div class="text-slate-500">Plate:</div>
                                    <div class="text-right">{{ $vehicle->registration_number }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div class="text-slate-500">Status:</div>
                                    <div class="text-right">{{ $vehicle->status }}</div>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-outline-secondary btn-sm w-full flex items-center">
                                    <x-base.lucide class="h-4 w-4 mr-1" icon="Info" />
                                    View Vehicle Details
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="box box--stacked">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Maintenance Reminder</div>
                        </div>
                        <div class="box-body p-5">
                            <div class="text-center mb-3">
                                @if($serviceItem->status)
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success/20 text-success">
                                        <x-base.lucide class="h-8 w-8" icon="CheckCircle" />
                                    </div>
                                    <h5 class="mt-2 font-medium text-success">Maintenance Completed</h5>
                                @elseif($serviceItem->isOverdue())
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-danger/20 text-danger">
                                        <x-base.lucide class="h-8 w-8" icon="AlertOctagon" />
                                    </div>
                                    <h5 class="mt-2 font-medium text-danger">Maintenance Overdue</h5>
                                    <p class="text-slate-500 text-sm">
                                        Should have been performed {{ $serviceItem->next_service_date->diffInDays(now()) }} days ago.
                                    </p>
                                @elseif($serviceItem->isUpcoming())
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-warning/20 text-warning">
                                        <x-base.lucide class="h-8 w-8" icon="AlertTriangle" />
                                    </div>
                                    <h5 class="mt-2 font-medium text-warning">Upcoming Maintenance</h5>
                                    <p class="text-slate-500 text-sm">
                                        Scheduled for {{ $serviceItem->next_service_date->diffInDays(now()) }} days.
                                    </p>
                                @else
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success/20 text-success">
                                        <x-base.lucide class="h-8 w-8" icon="Calendar" />
                                    </div>
                                    <h5 class="mt-2 font-medium text-success">Maintenance on Schedule</h5>
                                    <p class="text-slate-500 text-sm">
                                        Next service is scheduled for {{ $serviceItem->next_service_date->diffInDays(now()) }} days.
                                    </p>
                                @endif
                            </div>
                            
                            <div class="mt-4">
                                <!-- Acceso al historial centralizado -->
                                <a href="{{ route('admin.maintenance.show', $serviceItem->id) }}" class="btn btn-outline-primary btn-sm w-full flex items-center">
                                    <x-base.lucide class="h-4 w-4 mr-1" icon="ExternalLink" />
                                    View in Centralized Maintenance
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<x-base.dialog id="delete-confirmation-modal" size="md">
    <x-base.dialog.panel>
        <div class="p-5 text-center">
            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="XCircle" />
            <div class="mt-5 text-2xl">Are you sure?</div>
            <div class="mt-2 text-slate-500">
                Do you really want to delete this service record? <br>
                This action cannot be undone.
            </div>
        </div>
        <div class="px-5 pb-8 text-center">
            <form action="{{ route('admin.vehicles.maintenances.destroy', [$vehicle->id, $serviceItem->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                    Cancel
                </x-base.button>
                <x-base.button class="w-24" type="submit" variant="danger">
                    Delete
                </x-base.button>
            </form>
        </div>
    </x-base.dialog.panel>
</x-base.dialog>
@endsection