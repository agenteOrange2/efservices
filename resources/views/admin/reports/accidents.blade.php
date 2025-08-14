@extends('../themes/' . $activeTheme)
@section('title', 'Accidents Report')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Accidents Report', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Accidents Report
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.reports.index') }}" class="w-full sm:w-auto" variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Reports
                </x-base.button>
                <x-base.button id="export-pdf" class="w-full sm:w-auto" variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                    Export PDF
                </x-base.button>
                <x-base.menu>
                    <x-base.menu.button class="w-full px-2 sm:w-auto" as="x-base.button">
                        <x-base.lucide class="h-4 w-4" icon="Filter" />
                    </x-base.menu.button>
                    <x-base.menu.items class="w-80">
                        <form id="filterForm" method="GET" action="{{ route('admin.reports.accidents') }}" class="p-4">
                            <h3 class="font-medium text-base mb-4">Filters</h3>
                            
                            <!-- Search -->
                            <div class="mb-4">
                                <x-base.form-label>Search</x-base.form-label>
                                <x-base.form-input
                                    type="text"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="Location, description..."
                                />
                            </div>
                            
                            <!-- Carrier Filter -->
                            <div class="mb-4">
                                <x-base.form-label>Carrier</x-base.form-label>
                                <x-base.form-select name="carrier" id="filter_carrier_id">
                                    <option value="">All Carriers</option>
                                    @foreach($carriers as $carrier)
                                        <option value="{{ $carrier->id }}" {{ $carrierFilter == $carrier->id ? 'selected' : '' }}>
                                            {{ $carrier->name }}
                                        </option>
                                    @endforeach
                                </x-base.form-select>
                            </div>
                            
                            <!-- Driver Filter -->
                            <div class="mb-4">
                                <x-base.form-label>Driver</x-base.form-label>
                                <x-base.form-select name="driver" id="filter_driver_id">
                                    <option value="">All Drivers</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ request('driver') == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name ?? '' }} {{ $driver->last_name ?? '' }}
                                        </option>
                                    @endforeach
                                </x-base.form-select>
                            </div>
                            
                            <!-- Date Range -->
                            <div class="mb-4">
                                <x-base.form-label>Date Range</x-base.form-label>
                                <div class="grid grid-cols-2 gap-2">
                                    <x-base.form-input
                                        type="date"
                                        name="date_from"
                                        value="{{ $dateFrom }}"
                                        placeholder="From"
                                    />
                                    <x-base.form-input
                                        type="date"
                                        name="date_to"
                                        value="{{ $dateTo }}"
                                        placeholder="To"
                                    />
                                </div>
                            </div>
                            
                            <!-- Per Page -->
                            {{-- <div class="mb-4">
                                <x-base.form-label>Per Page</x-base.form-label>
                                <x-base.form-select name="per_page">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                </x-base.form-select>
                            </div> --}}
                            
                            <div class="flex justify-between">
                                <x-base.button type="button" onclick="clearFilters()" variant="outline-secondary">Clear</x-base.button>
                                <x-base.button type="submit" variant="primary">Apply</x-base.button>
                            </div>
                        </form>
                    </x-base.menu.items>
                </x-base.menu>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="mt-3.5 flex flex-col gap-8">
    <div class="box box--stacked flex flex-col p-5">
        <div class="grid grid-cols-4 gap-5">
            <!-- Total Accidents -->
            <div class="box col-span-4 rounded-[0.6rem] border border-dashed border-slate-300/80 p-5 shadow-sm md:col-span-2 xl:col-span-1">
                <div class="text-base text-slate-500">Total Accidents</div>
                <div class="mt-1.5 flex items-center">
                    <div class="text-2xl font-medium">{{ number_format($totalAccidents) }}</div>
                    <div class="flex items-center rounded-full bg-success/10 p-1 text-xs text-success ml-2">
                        <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5] mr-1" icon="FileSpreadsheet" />
                        Accidents
                    </div>
                </div>
            </div>
            
            <!-- Preventable Accidents -->
            <div class="box col-span-4 rounded-[0.6rem] border border-dashed border-slate-300/80 p-5 shadow-sm md:col-span-2 xl:col-span-1">
                <div class="text-base text-slate-500">Preventable</div>
                <div class="mt-1.5 flex items-center">
                    <div class="text-2xl font-medium">{{ number_format($preventableAccidents) }}</div>
                    <div class="flex items-center rounded-full bg-danger/10 p-1 text-xs text-danger ml-2">
                        <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5] mr-1" icon="AlertTriangle" />
                        Preventable
                    </div>
                </div>
            </div>
            
            <!-- Non-Preventable Accidents -->
            <div class="box col-span-4 rounded-[0.6rem] border border-dashed border-slate-300/80 p-5 shadow-sm md:col-span-2 xl:col-span-1">
                <div class="text-base text-slate-500">Non-Preventable</div>
                <div class="mt-1.5 flex items-center">
                    <div class="text-2xl font-medium">{{ number_format($nonPreventableAccidents) }}</div>
                    <div class="flex items-center rounded-full bg-info/10 p-1 text-xs text-info ml-2">
                        <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5] mr-1" icon="AlertCircle" />
                        Non-Preventable
                    </div>
                </div>
            </div>
            
            <!-- Citations Issued -->
            <div class="box col-span-4 rounded-[0.6rem] border border-dashed border-slate-300/80 p-5 shadow-sm md:col-span-2 xl:col-span-1">
                <div class="text-base text-slate-500">With Citations</div>
                <div class="mt-1.5 flex items-center">
                    <div class="text-2xl font-medium">{{ number_format($withCitations) }}</div>
                    <div class="flex items-center rounded-full bg-warning/10 p-1 text-xs text-warning ml-2">
                        <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5] mr-1" icon="FileWarning" />
                        Citations
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Accidents List Table -->
    <div class="box box--stacked mt-5">
        <!-- Header and search bar -->
        <div class="flex flex-col items-center gap-y-4 p-5 border-b border-slate-200/60 md:flex-row md:items-center">
            <h2 class="text-base font-medium">Accident Records</h2>
            <div class="flex w-full items-center gap-x-3 md:ml-auto md:w-auto">
                <div class="ml-auto w-full md:w-56">
                    <div class="relative w-full">
                        <input id="table-search" type="text" class="form-input form-input--sm pl-9 pr-4" placeholder="Search..." value="{{ $search }}" onkeypress="searchOnEnter(event)">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-base.lucide class="h-4 w-4 stroke-[1.3] text-slate-500" icon="Search" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Accidents Table -->
        @if($accidents->count() > 0)
        <div class="overflow-x-auto">
            <x-base.table class="border-separate border-spacing-0">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.th class="whitespace-nowrap">Driver</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">Carrier</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">Date</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">Location</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">Nature of Accident</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap text-center">Fatalities</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap text-center">Injuries</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap text-center">Actions</x-base.table.th>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @foreach($accidents as $accident)
                        <x-base.table.tr>
                            <x-base.table.td>
                                <div class="flex items-center">
                                    <div class="h-9 w-9 image-fit zoom-in rounded-full">
                                        <img src="{{ $accident->userDriverDetail->user->avatar ?? asset('assets/images/profile-placeholder.jpg') }}" class="rounded-full">
                                    </div>
                                    <div class="ml-4">
                                        <span class="font-medium whitespace-nowrap">{{ $accident->userDriverDetail->user->name ?? 'N/A' }}</span>
                                        <div class="text-slate-500 text-xs whitespace-nowrap">{{ $accident->userDriverDetail->last_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td>
                                <div class="font-medium whitespace-nowrap">{{ $accident->carrier->name ?? 'N/A' }}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap">DOT: {{ $accident->carrier->dot_number ?? 'N/A' }}</div>
                            </x-base.table.td>
                            <x-base.table.td>
                                <div class="font-medium whitespace-nowrap">{{ $accident->accident_date ? $accident->accident_date->format('m/d/Y') : 'N/A' }}</div>
                            </x-base.table.td>
                            <x-base.table.td>
                                <div class="font-medium whitespace-nowrap">{{ $accident->location ?? 'N/A' }}</div>
                            </x-base.table.td>
                            <x-base.table.td>
                                <div class="whitespace-nowrap">{{ $accident->nature_of_accident ?? 'N/A' }}</div>
                            </x-base.table.td>
                            <x-base.table.td class="text-center">
                                @if($accident->had_fatalities)
                                    <div class="whitespace-nowrap text-danger font-medium">{{ $accident->number_of_fatalities }}</div>
                                @else
                                    <div class="whitespace-nowrap text-slate-500">0</div>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="text-center">
                                @if($accident->had_injuries)
                                    <div class="whitespace-nowrap text-warning font-medium">{{ $accident->number_of_injuries }}</div>
                                @else
                                    <div class="whitespace-nowrap text-slate-500">0</div>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="text-center">
                                <div class="flex justify-center items-center">
                                    <!-- View Details -->
                                    <button type="button" class="flex items-center mr-3 view-accident" data-id="{{ $accident->id }}" data-driver-id="{{ $accident->driver_id }}" title="View Details">
                                        <x-base.lucide class="h-4 w-4 mr-1" icon="Eye" /> View
                                    </button>
                                    
                                    <!-- Delete Accident -->
                                    <button type="button" class="flex items-center text-danger delete-accident" data-id="{{ $accident->id }}" title="Delete Accident">
                                        <x-base.lucide class="h-4 w-4 mr-1" icon="Trash2" /> Delete
                                    </button>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @endforeach
                </x-base.table.tbody>
            </x-base.table>
        </div>
        
        <!-- Pagination -->
        <div class="p-5 border-t border-slate-200/60">
            {{ $accidents->appends(request()->query())->links() }}
        </div>
        @else
        <div class="p-10 text-center">
            <div class="text-slate-500">
                <x-base.lucide class="h-16 w-16 mx-auto mb-4 text-slate-300" icon="ClipboardX" />
                <p class="text-lg font-medium">No accidents found</p>
                <p class="mt-2">Try adjusting your search criteria or filters.</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add New Accident Modal -->
<x-base.dialog id="new-accident-modal">
    <x-base.dialog.panel>
        <!-- Modal Header -->
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">Register New Accident</h2>
                        <label for="damage_description" class="form-label mb-1">Damage Description</label>
                        <textarea name="damage_description" id="damage_description" class="form-control" rows="3">{{ old('damage_description') }}</textarea>
                    </div>
                    
                    <!-- File Upload -->
                    <div class="mt-4">
                        <label for="files" class="form-label mb-1">Upload Files</label>
                        <input type="file" name="files[]" id="files" class="form-control" multiple>
                        <div class="text-slate-500 mt-1 text-xs">You can upload multiple files (max 5MB each)</div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-5 py-3 text-right border-t border-slate-200/60">
                    <div class="flex items-center justify-end space-x-3">
                        <x-base.button
                            type="button"
                            variant="outline-secondary"
                            @click="$dispatch('close')"
                        >
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Save Accident Record
                        </x-base.button>
                    </div>
                </div>
            </form>
        </x-base.dialog.description>
    </x-base.dialog.panel>
</x-base.dialog>

<!-- JavaScript para el modal y funcionalidad de accidentes -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Abrir modal desde botón de nueva entrada
        const newAccidentButton = document.getElementById('new-accident-button');
        if (newAccidentButton) {
            newAccidentButton.addEventListener('click', function() {
                const modal = document.getElementById('new-accident-modal');
                if (modal && modal._x_dataStack && modal._x_dataStack[0]) {
                    modal._x_dataStack[0].show = true;
                } else {
                    console.error('Modal not initialized correctly');
                    // Fallback para abrir modal con Alpine
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'new-accident-modal' } }));
                }
            });
        }
        
        // Exportar a PDF
        const exportBtn = document.getElementById('export-pdf');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                // Obtener parámetros actuales de filtro
                const params = new URLSearchParams(window.location.search);
                params.append('export', 'pdf');
                
                // Redireccionar a la ruta de exportación con los filtros
                window.location.href = `{{ route('admin.reports.accidents') }}?${params.toString()}`;
            });
        }
        
        // Botones Ver accidente
        const viewButtons = document.querySelectorAll('.view-accident');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const accidentId = this.dataset.id;
                const driverId = this.dataset.driverId;
                if (accidentId && driverId) {
                    // Redireccionar al historial de accidentes del conductor
                    window.location.href = `/admin/drivers/${driverId}/accident-history`;
                } else if (accidentId) {
                    // Redireccionar a editar accidente
                    window.location.href = `/admin/accidents/${accidentId}/edit`;
                }
            });
        });
        
        // Búsqueda por enter - asegurar que funcione
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    document.getElementById('filterForm').submit();
                }
            });
        }
        
        // Botones Eliminar accidente
        const deleteButtons = document.querySelectorAll('.delete-accident');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const accidentId = this.getAttribute('data-id');
                if (accidentId && confirm('Are you sure you want to delete this accident record? This action cannot be undone.')) {
                    // Crear formulario para eliminar
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/reports/accident/${accidentId}`;
                    form.style.display = 'none';
                    
                    const csrfField = document.createElement('input');
                    csrfField.type = 'hidden';
                    csrfField.name = '_token';
                    csrfField.value = '{{ csrf_token() }}';
                    
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    
                    form.appendChild(csrfField);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
        
        // Cargar conductores basados en carrier seleccionado
        const carrierSelect = document.getElementById('carrier_id');
        if (carrierSelect) {
            carrierSelect.addEventListener('change', function() {
                const carrierId = this.value;
                const driverSelect = document.getElementById('driver_id');
                
                // Limpiar opciones actuales
                driverSelect.innerHTML = '<option value="">Select driver...</option>';
                
                if (carrierId) {
                    // Cargar conductores para este carrier via AJAX
                    fetch(`/admin/api/carriers/${carrierId}/drivers`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(driver => {
                                const option = document.createElement('option');
                                option.value = driver.id;
                                option.textContent = `${driver.name} ${driver.last_name}`;
                                driverSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error loading drivers:', error));
                }
            });
        }
    });
</script>
@endpush

@endsection