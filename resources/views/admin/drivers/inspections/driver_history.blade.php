@extends('../themes/' . $activeTheme)
@section('title', 'Driver Inspection History')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Driver Inspections', 'url' => route('admin.inspections.index')],
    ['label' => 'Driver Inspection History', 'active' => true],
];
@endphp
@section('subcontent')
<div>
    <!-- Mensajes Flash -->
    @if(session()->has('success'))
    <div class="alert alert-success flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
        {{ session('success') }}
    </div>
    @endif

    <!-- Cabecera -->
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Inspection History for {{ $driver->user->name }} {{ $driver->last_name }}
        </h2>
        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
            <x-base.button as="a" href="{{ route('admin.drivers.show', $driver->id) }}"
                class="w-full sm:w-auto" variant="outline-primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="user" />
                Driver Profile
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.inspections.index') }}"
                class="w-full sm:w-auto" variant="primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                All Inspections
            </x-base.button>
        </div>
    </div>

    <!-- Info del Conductor -->
    <div class="box box--stacked p-5 mt-5">
        <div class="flex flex-col md:flex-row items-center">
            <div class="w-24 h-24 md:w-16 md:h-16 rounded-full overflow-hidden mr-5 mb-4 md:mb-0">
                @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}" alt="{{ $driver->user->name }}"
                    class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-500">
                    <x-base.lucide class="h-8 w-8" icon="user" />
                </div>
                @endif
            </div>
            <div class="text-center md:text-left md:mr-auto">
                <div class="text-lg font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                <div class="text-gray-500">{{ $driver->phone }}</div>
                <div class="text-gray-500">{{ $driver->carrier->name }}</div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="flex items-center">
                    <div class="text-gray-500 mr-2">Total Inspections:</div>
                    <div class="text-lg font-medium">{{ $inspections->total() }}</div>
                </div>
                @if ($inspections->count() > 0)
                <div class="flex items-center mt-1">
                    <div class="text-gray-500 mr-2">Last Inspection:</div>
                    <div class="text-blue-600">
                        {{ $inspections->first()->inspection_date->format('M d, Y') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cabecera y Búsqueda -->
    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Driver Inspection Records
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <form action="{{ route('admin.drivers.inspection-history', $driver->id) }}" method="GET"
                class="mr-2 flex gap-2">
                <div class="relative">
                    <x-base.lucide
                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                        icon="Search" />
                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                        value="{{ request('search_term') }}" type="text" placeholder="Search inspections..." />
                </div>
                
                @if(count($driverVehicles) > 0)
                <select name="vehicle_filter" class="mr-2 text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All Vehicles</option>
                    @foreach ($driverVehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ request('vehicle_filter') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->license_plate }} - {{ $vehicle->brand }}
                    </option>
                    @endforeach
                </select>
                @endif
                
                <select name="inspection_type" class="mr-2 text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All Types</option>
                    @foreach ($inspectionTypes as $type)
                    <option value="{{ $type }}" {{ request('inspection_type') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                    @endforeach
                </select>
                
                <select name="status" class="mr-2 text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                    @endforeach
                </select>
                
                <x-base.button type="submit" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                    Filter
                </x-base.button>
            </form>
            
            <x-base.button as="a" href="{{ route('admin.inspections.create') }}"
                variant="primary" class="flex items-center">
                <x-base.lucide class="h-4 w-4 mr-2" icon="plus" />
                Add Inspection
            </x-base.button>
        </div>
    </div>

    <!-- Tabla de Inspecciones -->
    <div class="box box--stacked p-5 mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead>
                    <tr class="bg-slate-50/60">
                        <th scope="col" class="px-6 py-3">
                            <a href="{{ route('admin.drivers.inspection-history', [
                                'driver' => $driver->id,
                                'sort_field' => 'inspection_date',
                                'sort_direction' => request('sort_field') == 'inspection_date' &&
                                request('sort_direction') == 'asc' ? 'desc' : 'asc',
                                'search_term' => request('search_term'),
                                'vehicle_filter' => request('vehicle_filter'),
                                'inspection_type' => request('inspection_type'),
                                'status' => request('status')
                            ]) }}" class="flex items-center">
                                Date
                                @if(request('sort_field') == 'inspection_date' || !request('sort_field'))
                                <x-base.lucide class="w-4 h-4 ml-1" icon="{{ request('sort_direction') == 'asc'
                                    ? 'chevron-up' : 'chevron-down' }}" />
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Vehicle</th>
                        <th scope="col" class="px-6 py-3">Inspection Type</th>
                        <th scope="col" class="px-6 py-3">Documents</th>
                        <th scope="col" class="px-6 py-3">Inspector</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Safe to Operate</th>
                        <th scope="col" class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inspections as $inspection)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <td class="px-6 py-4">{{ $inspection->inspection_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            @if ($inspection->vehicle)
                                {{ $inspection->vehicle->license_plate }} - {{ $inspection->vehicle->brand }} {{ $inspection->vehicle->model }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $inspection->inspection_type }}</td>
                        <td class="px-6 py-4">{{ $inspection->getMedia('inspection_documents')->count() }}</td>
                        <td class="px-6 py-4">{{ $inspection->inspector_name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $inspection->status == 'Passed' ? 'bg-success/20 text-success' : 
                                   ($inspection->status == 'Failed' ? 'bg-danger/20 text-danger' : 
                                   'bg-warning/20 text-warning') }}">
                                {{ $inspection->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($inspection->is_vehicle_safe_to_operate)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-success/20 text-success">
                                    Yes
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger/20 text-danger">
                                    No
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center items-center">
                                <a href="{{ route('admin.inspections.edit', $inspection->id) }}"
                                    class="btn btn-primary mr-2 p-1">
                                    <x-base.lucide class="w-4 h-4" icon="edit" />
                                </a>
                                <x-base.button data-tw-toggle="modal" data-tw-target="#delete-inspection-modal"
                                    variant="danger" class="p-1 delete-inspection"
                                    data-inspection-id="{{ $inspection->id }}">
                                    <x-base.lucide class="w-4 h-4" icon="trash" />
                                </x-base.button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10">
                            <div class="flex flex-col items-center">
                                <x-base.lucide class="h-16 w-16 text-gray-300" icon="alert-triangle" />
                                <p class="mt-2 text-gray-500">No inspection records found for this driver</p>
                                <a href="{{ route('admin.inspections.create', ['driver_id' => $driver->id]) }}"
                                    class="btn btn-primary mt-4">
                                    <x-base.lucide class="h-4 w-4 mr-1" icon="plus" />
                                    Add First Inspection
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="mt-5">
            {{ $inspections->appends(request()->query())->links() }}
        </div>
    </div>
    
    <!-- Modal Añadir Inspección -->
    <x-base.dialog id="add-inspection-modal" size="xl">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Inspection Record</h2>
            </x-base.dialog.title>
            <form action="{{ route('admin.inspections.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Vehículo -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="vehicle_id">Vehicle (Optional)</x-base.form-label>
                        <select id="vehicle_id" name="vehicle_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">Select Vehicle</option>
                            @foreach($driverVehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->license_plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha de inspección -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="inspection_date">Inspection Date</x-base.form-label>
                        <x-base.form-input id="inspection_date" name="inspection_date" type="date"
                            value="{{ date('Y-m-d') }}" required />
                    </div>

                    <!-- Tipo de inspección -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="inspection_type">Inspection Type</x-base.form-label>
                        <select id="inspection_type" name="inspection_type" 
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Inspection Type</option>
                            <option value="Pre-trip">Pre-trip</option>
                            <option value="Post-trip">Post-trip</option>
                            <option value="DOT">DOT</option>
                            <option value="Annual">Annual</option>
                            <option value="Random">Random</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Inspector -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="inspector_name">Inspector Name</x-base.form-label>
                        <x-base.form-input id="inspector_name" name="inspector_name" type="text"
                            placeholder="Name of inspector" required />
                    </div>

                    <!-- Location -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="location">Location</x-base.form-label>
                        <x-base.form-input id="location" name="location" type="text"
                            placeholder="Inspection location" />
                    </div>

                    <!-- Status -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="status">Status</x-base.form-label>
                        <select id="status" name="status" 
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Status</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                            <option value="Pending Repairs">Pending Repairs</option>
                        </select>
                    </div>

                    <!-- Safe to Operate -->
                    <div class="col-span-12 sm:col-span-6">
                        <div class="flex items-center h-full">
                            <label for="is_vehicle_safe_to_operate" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_vehicle_safe_to_operate"
                                    name="is_vehicle_safe_to_operate" value="1" type="checkbox" checked />
                                <span class="cursor-pointer select-none">Vehicle is Safe to Operate</span>
                            </label>
                        </div>
                    </div>

                    <!-- Defects Found -->
                    <div class="col-span-12">
                        <x-base.form-label for="defects_found">Defects Found</x-base.form-label>
                        <x-base.form-textarea id="defects_found" name="defects_found"
                            placeholder="List any defects found during inspection"></x-base.form-textarea>
                    </div>

                    <!-- Corrective Actions -->
                    <div class="col-span-12">
                        <x-base.form-label for="corrective_actions">Corrective Actions</x-base.form-label>
                        <x-base.form-textarea id="corrective_actions" name="corrective_actions"
                            placeholder="Describe corrective actions needed"></x-base.form-textarea>
                    </div>

                    <!-- Defects Corrected -->
                    <div class="col-span-12 sm:col-span-4">
                        <div class="flex items-center">
                            <label for="is_defects_corrected" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="is_defects_corrected"
                                    name="is_defects_corrected" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Defects Corrected</span>
                            </label>
                        </div>
                    </div>

                    <!-- Corrected Date -->
                    <div class="col-span-12 sm:col-span-4" id="defects_corrected_date_container" style="display: none;">
                        <x-base.form-label for="defects_corrected_date">Date Corrected</x-base.form-label>
                        <x-base.form-input id="defects_corrected_date" name="defects_corrected_date" type="date" />
                    </div>

                    <!-- Corrected By -->
                    <div class="col-span-12 sm:col-span-4" id="corrected_by_container" style="display: none;">
                        <x-base.form-label for="corrected_by">Corrected By</x-base.form-label>
                        <x-base.form-input id="corrected_by" name="corrected_by" type="text"
                            placeholder="Name of person who made corrections" />
                    </div>

                    <!-- Files Upload -->
                    <div class="col-span-12">
                        <x-base.form-label>Inspection Reports</x-base.form-label>
                        <input type="file" name="inspection_reports[]" class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary" multiple>
                        <div class="mt-1 text-xs text-gray-500">Upload inspection reports (PDF, JPG, PNG)</div>
                    </div>

                    <div class="col-span-12">
                        <x-base.form-label>Defect Photos</x-base.form-label>
                        <input type="file" name="defect_photos[]" class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary" multiple>
                        <div class="mt-1 text-xs text-gray-500">Upload photos of defects (JPG, PNG)</div>
                    </div>

                    <div class="col-span-12" id="repair_documents_container" style="display: none;">
                        <x-base.form-label>Repair Documents</x-base.form-label>
                        <input type="file" name="repair_documents[]" class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary" multiple>
                        <div class="mt-1 text-xs text-gray-500">Upload repair documentation (PDF, JPG, PNG)</div>
                    </div>

                    <!-- Notas -->
                    <div class="col-span-12">
                        <x-base.form-label for="notes">Notes</x-base.form-label>
                        <x-base.form-textarea id="notes" name="notes"
                            placeholder="Additional notes about the inspection"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                        class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Editar Inspección -->
    <x-base.dialog id="edit-inspection-modal" size="xl">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Edit Inspection Record</h2>
            </x-base.dialog.title>
            <form id="edit_inspection_form" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Vehículo -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_vehicle_id">Vehicle (Optional)</x-base.form-label>
                        <select id="edit_vehicle_id" name="vehicle_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">Select Vehicle</option>
                            @foreach($driverVehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->license_plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha de inspección -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_inspection_date">Inspection Date</x-base.form-label>
                        <x-base.form-input id="edit_inspection_date" name="inspection_date" type="date" required />
                    </div>

                    <!-- Tipo de inspección -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_inspection_type">Inspection Type</x-base.form-label>
                        <select id="edit_inspection_type" name="inspection_type" 
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Inspection Type</option>
                            <option value="Pre-trip">Pre-trip</option>
                            <option value="Post-trip">Post-trip</option>
                            <option value="DOT">DOT</option>
                            <option value="Annual">Annual</option>
                            <option value="Random">Random</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Inspector -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_inspector_name">Inspector Name</x-base.form-label>
                        <x-base.form-input id="edit_inspector_name" name="inspector_name" type="text"
                            placeholder="Name of inspector" required />
                    </div>

                    <!-- Location -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_location">Location</x-base.form-label>
                        <x-base.form-input id="edit_location" name="location" type="text"
                            placeholder="Inspection location" />
                    </div>

                    <!-- Status -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_status">Status</x-base.form-label>
                        <select id="edit_status" name="status" 
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">Select Status</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                            <option value="Pending Repairs">Pending Repairs</option>
                        </select>
                    </div>

                    <!-- Safe to Operate -->
                    <div class="col-span-12 sm:col-span-6">
                        <div class="flex items-center h-full">
                            <label for="edit_is_vehicle_safe_to_operate" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_vehicle_safe_to_operate"
                                    name="is_vehicle_safe_to_operate" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Vehicle is Safe to Operate</span>
                            </label>
                        </div>
                    </div>

                    <!-- Defects Found -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_defects_found">Defects Found</x-base.form-label>
                        <x-base.form-textarea id="edit_defects_found" name="defects_found"
                            placeholder="List any defects found during inspection"></x-base.form-textarea>
                    </div>

                    <!-- Corrective Actions -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_corrective_actions">Corrective Actions</x-base.form-label>
                        <x-base.form-textarea id="edit_corrective_actions" name="corrective_actions"
                            placeholder="Describe corrective actions needed"></x-base.form-textarea>
                    </div>

                    <!-- Defects Corrected -->
                    <div class="col-span-12 sm:col-span-4">
                        <div class="flex items-center">
                            <label for="edit_is_defects_corrected" class="flex items-center">
                                <x-base.form-check.input class="mr-2.5 border" id="edit_is_defects_corrected"
                                    name="is_defects_corrected" value="1" type="checkbox" />
                                <span class="cursor-pointer select-none">Defects Corrected</span>
                            </label>
                        </div>
                    </div>

                    <!-- Corrected Date -->
                    <div class="col-span-12 sm:col-span-4" id="edit_defects_corrected_date_container" style="display: none;">
                        <x-base.form-label for="edit_defects_corrected_date">Date Corrected</x-base.form-label>
                        <x-base.form-input id="edit_defects_corrected_date" name="defects_corrected_date" type="date" />
                    </div>

                    <!-- Corrected By -->
                    <div class="col-span-12 sm:col-span-4" id="edit_corrected_by_container" style="display: none;">
                        <x-base.form-label for="edit_corrected_by">Corrected By</x-base.form-label>
                        <x-base.form-input id="edit_corrected_by" name="corrected_by" type="text"
                            placeholder="Name of person who made corrections" />
                    </div>

                    <!-- Existing Files -->
                    <div class="col-span-12" id="edit_existing_files_container">
                        <x-base.form-label>Existing Files</x-base.form-label>
                        <div id="existing_files_list" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Files will be loaded dynamically -->
                        </div>
                    </div>

                    <!-- Files Upload -->
                    <div class="col-span-12">
                        <x-base.form-label>Additional Inspection Reports</x-base.form-label>
                        <input type="file" name="inspection_reports[]" class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary" multiple>
                        <div class="mt-1 text-xs text-gray-500">Upload inspection reports (PDF, JPG, PNG)</div>
                    </div>

                    <div class="col-span-12">
                        <x-base.form-label>Additional Defect Photos</x-base.form-label>
                        <input type="file" name="defect_photos[]" class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary" multiple>
                        <div class="mt-1 text-xs text-gray-500">Upload photos of defects (JPG, PNG)</div>
                    </div>

                    <div class="col-span-12" id="edit_repair_documents_container" style="display: none;">
                        <x-base.form-label>Additional Repair Documents</x-base.form-label>
                        <input type="file" name="repair_documents[]" class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary" multiple>
                        <div class="mt-1 text-xs text-gray-500">Upload repair documentation (PDF, JPG, PNG)</div>
                    </div>

                    <!-- Notas -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_notes">Notes</x-base.form-label>
                        <x-base.form-textarea id="edit_notes" name="notes"
                            placeholder="Additional notes about the inspection"></x-base.form-textarea>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                        class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Update
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Eliminar Inspección -->
    <x-base.dialog id="delete-inspection-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
                <div class="mt-5 text-2xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this inspection record? <br>
                    This process cannot be undone.
                </div>
            </div>
            <form id="delete_inspection_form" action="" method="POST" class="px-5 pb-8 text-center">
                @csrf
                @method('DELETE')
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                    class="mr-1 w-24">
                    Cancel
                </x-base.button>
                <x-base.button type="submit" variant="danger" class="w-24">
                    Delete
                </x-base.button>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Funcionalidad para mostrar/ocultar campos de corrección y documentos
    const isDefectsCorrectedCheckbox = document.getElementById('is_defects_corrected');
    const defectsCorrectedDateContainer = document.getElementById('defects_corrected_date_container');
    const correctedByContainer = document.getElementById('corrected_by_container');
    const repairDocumentsContainer = document.getElementById('repair_documents_container');

    isDefectsCorrectedCheckbox.addEventListener('change', function() {
        defectsCorrectedDateContainer.style.display = this.checked ? 'block' : 'none';
        correctedByContainer.style.display = this.checked ? 'block' : 'none';
        repairDocumentsContainer.style.display = this.checked ? 'block' : 'none';
    });

    // Misma funcionalidad para el formulario de edición
    const editIsDefectsCorrectedCheckbox = document.getElementById('edit_is_defects_corrected');
    const editDefectsCorrectedDateContainer = document.getElementById('edit_defects_corrected_date_container');
    const editCorrectedByContainer = document.getElementById('edit_corrected_by_container');
    const editRepairDocumentsContainer = document.getElementById('edit_repair_documents_container');

    editIsDefectsCorrectedCheckbox.addEventListener('change', function() {
        editDefectsCorrectedDateContainer.style.display = this.checked ? 'block' : 'none';
        editCorrectedByContainer.style.display = this.checked ? 'block' : 'none';
        editRepairDocumentsContainer.style.display = this.checked ? 'block' : 'none';
    });

    // Configuración del modal de edición
    const editButtons = document.querySelectorAll('.edit-inspection');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const inspection = JSON.parse(this.getAttribute('data-inspection'));
            
            // Establecer la acción del formulario
            document.getElementById('edit_inspection_form').action = 
                `/admin/inspections/${inspection.id}`;
            
            // Establecer valores en el formulario
            document.getElementById('edit_inspection_date').value = inspection.inspection_date.split('T')[0];
            document.getElementById('edit_inspection_type').value = inspection.inspection_type;
            document.getElementById('edit_inspector_name').value = inspection.inspector_name;
            document.getElementById('edit_location').value = inspection.location || '';
            document.getElementById('edit_status').value = inspection.status;
            document.getElementById('edit_is_vehicle_safe_to_operate').checked = inspection.is_vehicle_safe_to_operate;
            document.getElementById('edit_defects_found').value = inspection.defects_found || '';
            document.getElementById('edit_corrective_actions').value = inspection.corrective_actions || '';
            document.getElementById('edit_notes').value = inspection.notes || '';
            
            // Configurar el vehículo si existe
            if (inspection.vehicle_id) {
                document.getElementById('edit_vehicle_id').value = inspection.vehicle_id;
            }
            
            // Configurar correcciones
            document.getElementById('edit_is_defects_corrected').checked = inspection.is_defects_corrected;
            editDefectsCorrectedDateContainer.style.display = inspection.is_defects_corrected ? 'block' : 'none';
            editCorrectedByContainer.style.display = inspection.is_defects_corrected ? 'block' : 'none';
            editRepairDocumentsContainer.style.display = inspection.is_defects_corrected ? 'block' : 'none';
            
            if (inspection.is_defects_corrected) {
                if (inspection.defects_corrected_date) {
                    document.getElementById('edit_defects_corrected_date').value = 
                        inspection.defects_corrected_date.split('T')[0];
                }
                document.getElementById('edit_corrected_by').value = inspection.corrected_by || '';
            }
            
            // Cargar archivos existentes
            const existingFilesList = document.getElementById('existing_files_list');
            existingFilesList.innerHTML = '';
            
            if (inspection.media && inspection.media.length > 0) {
                inspection.media.forEach(media => {
                    const fileCard = document.createElement('div');
                    fileCard.className = 'p-3 border rounded-lg';
                    
                    const fileType = media.collection_name;
                    let typeLabel = '';
                    
                    switch(fileType) {
                        case 'inspection_reports':
                            typeLabel = 'Inspection Report';
                            break;
                        case 'defect_photos':
                            typeLabel = 'Defect Photo';
                            break;
                        case 'repair_documents':
                            typeLabel = 'Repair Document';
                            break;
                        default:
                            typeLabel = 'Document';
                    }
                    
                    const fileIcon = media.mime_type.startsWith('image') ? 'image' : 'file-text';
                    
                    fileCard.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-base.lucide class="h-6 w-6 mr-2 text-primary" icon="${fileIcon}" />
                                <div>
                                    <div class="font-medium">${typeLabel}</div>
                                    <div class="text-xs text-gray-500 truncate max-w-[150px]">${media.file_name}</div>
                                </div>
                            </div>
                            <button type="button" class="text-danger delete-file" data-inspection-id="${inspection.id}" data-media-id="${media.id}">
                                <x-base.lucide class="h-4 w-4" icon="trash" />
                            </button>
                        </div>
                    `;
                    
                    existingFilesList.appendChild(fileCard);
                });
                
                // Agregar funcionalidad para eliminar archivos
                document.querySelectorAll('.delete-file').forEach(button => {
                    button.addEventListener('click', function() {
                        const inspectionId = this.getAttribute('data-inspection-id');
                        const mediaId = this.getAttribute('data-media-id');
                        
                        if (confirm("Are you sure you want to delete this file?")) {
                            fetch(`/admin/inspections/${inspectionId}/files/${mediaId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.closest('.p-3').remove();
                                } else {
                                    alert('Error deleting file');
                                }
                            });
                        }
                    });
                });
            } else {
                existingFilesList.innerHTML = '<div class="col-span-3 text-center text-gray-500">No files attached</div>';
            }
        });
    });
    
    // Configuración del modal de eliminación
    const deleteButtons = document.querySelectorAll('.delete-inspection');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const inspectionId = this.getAttribute('data-inspection-id');
            document.getElementById('delete_inspection_form').action = 
                `/admin/inspections/${inspectionId}`;
        });
    });
});
</script>
@endpush
@endsection