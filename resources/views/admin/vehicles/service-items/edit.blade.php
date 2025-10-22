@extends('../themes/' . $activeTheme)
@section('title', 'Edit Service Item')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
['label' => $vehicle->make . ' ' . $vehicle->model, 'url' => route('admin.vehicles.show', $vehicle->id)],
['label' => 'Maintenances', 'url' => route('admin.vehicles.maintenances.index', $vehicle->id)],
['label' => 'Edit Maintenance', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Edit Service Item: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                    class="w-full sm:w-auto" variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Maintenances
                </x-base.button>
            </div>
        </div>

        <div class="box box--stacked mt-5">
            <div class="box-header">
                <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">
                    Service Item Details
                </div>
            </div>
            <div class="box-body p-5">
                @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="ml-4 list-disc">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.vehicles.maintenances.update', [$vehicle->id, $serviceItem->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="unit">Unit/System <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="unit" name="unit" value="{{ old('unit', $serviceItem->unit) }}" required />
                            <small class="text-slate-500">Identifies the system or part of the vehicle serviced</small>
                        </div>
                        <div>
                            <x-base.form-label for="service_tasks">Service Tasks <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="service_tasks" name="service_tasks"
                                value="{{ old('service_tasks', $serviceItem->service_tasks) }}" required />
                            <small class="text-slate-500">List of tasks performed during the service</small>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="service_date">Service Date <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="service_date" name="service_date"
                                value="{{ old('service_date', $serviceItem->service_date->format('Y-m-d')) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="next_service_date">Next Service Date <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="next_service_date" name="next_service_date"
                                value="{{ old('next_service_date', $serviceItem->next_service_date->format('Y-m-d')) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="odometer">Odometer (miles)</x-base.form-label>
                            <x-base.form-input type="number" id="odometer" name="odometer"
                                value="{{ old('odometer', $serviceItem->odometer) }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="vendor_mechanic">Vendor/Mechanic <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="vendor_mechanic" name="vendor_mechanic"
                                value="{{ old('vendor_mechanic', $serviceItem->vendor_mechanic) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="cost">Cost ($) <span class="text-danger">*</span></x-base.form-label>
                            <div class="input-group">                                
                                <x-base.form-input type="number" step="0.01" id="cost" name="cost"
                                    value="{{ old('cost', $serviceItem->cost) }}" min="0" required />
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-base.form-label for="description">Description/Notes</x-base.form-label>
                        <x-base.form-textarea id="description" name="description" rows="4">{{ old('description', $serviceItem->description) }}</x-base.form-textarea>
                    </div>

                    <!-- Simple file upload -->
                    <div class="mb-4">
                        <x-base.form-label>Service Tickets</x-base.form-label>
                        <div class="border-2 border-dashed rounded-lg p-6 text-center border-slate-300">
                            <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Upload" />
                            <p class="mt-2 text-sm text-slate-500">
                                Select files to upload
                            </p>
                            <input type="file" class="mt-2" multiple name="maintenance_files[]" accept=".pdf,.jpg,.jpeg,.png">
                            <p class="mt-1 text-xs text-slate-400">
                                PDF, JPG, PNG (Max. 10MB per file)
                            </p>
                        </div>

                        <!-- Mostrar archivos existentes -->
                        @if($serviceItem->getMedia('maintenance_files')->count() > 0)
                        <div class="mt-4">
                            <h4 class="font-medium text-sm mb-2"> Current attachments::</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($serviceItem->getMedia('maintenance_files') as $media)
                                <div class="border rounded p-2 flex items-center justify-between bg-slate-50">
                                    <div class="flex items-center">
                                        @if(Str::contains($media->mime_type, 'pdf'))
                                        <x-base.lucide class="h-8 w-8 text-danger mr-2" icon="FileText" />
                                        @elseif(Str::contains($media->mime_type, 'image'))
                                        <img src="{{ $media->getUrl() }}" class="h-8 w-8 object-cover rounded mr-2" />
                                        @else
                                        <x-base.lucide class="h-8 w-8 text-primary mr-2" icon="File" />
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium truncate">{{ $media->file_name }}</p>
                                            <p class="text-xs text-slate-500">{{ number_format($media->size / 1024, 2) }} KB</p>
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary p-1 mr-1">
                                            <x-base.lucide class="h-4 w-4" icon="Eye" />
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1"
                                            onclick="if(confirm('¿Estás seguro de eliminar este archivo?')) { document.getElementById('delete-file-{{ $media->id }}').submit(); }">
                                            <x-base.lucide class="h-4 w-4" icon="Trash" />
                                        </button>
                                        <form id="delete-file-{{ $media->id }}" action="{{ route('admin.vehicles.maintenances.delete-file', [$vehicle->id, $serviceItem->id, $media->id]) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="flex items-center mt-5 pt-5 border-t border-slate-200/60">
                        <div class="form-check mr-4">                            
                            <input type="checkbox" id="status" name="status"  value="1" 
                                {{ old('status', $serviceItem->status) ? 'checked' : '' }}                               
                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary">
                            <label for="status" class="form-check-label ml-2">Mark as completed</label>

                        </div>
                        <div class="ml-auto">
                            <x-base.button as="a" href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                                variant="outline-secondary" class="mr-1 w-24">
                                Cancel
                            </x-base.button>
                            <x-base.button type="submit" variant="primary" class="w-24">
                                Update
                            </x-base.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Validación para asegurar que la fecha del próximo servicio sea posterior a la fecha de servicio
    document.addEventListener('DOMContentLoaded', function() {
        const serviceDateInput = document.getElementById('service_date');
        const nextServiceDateInput = document.getElementById('next_service_date');

        function validateDates() {
            if (serviceDateInput.value && nextServiceDateInput.value) {
                const serviceDate = new Date(serviceDateInput.value);
                const nextServiceDate = new Date(nextServiceDateInput.value);

                if (nextServiceDate <= serviceDate) {
                    nextServiceDateInput.setCustomValidity('Next service date must be after service date');
                } else {
                    nextServiceDateInput.setCustomValidity('');
                }
            }
        }

        serviceDateInput.addEventListener('change', validateDates);
        nextServiceDateInput.addEventListener('change', validateDates);
    });
</script>
@endpush