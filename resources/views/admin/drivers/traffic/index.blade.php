@extends('../themes/' . $activeTheme)
@section('title', 'Traffic Convictions')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Traffic Convictions', 'active' => true],
    ];
@endphp

@section('subcontent')
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div>
        <!-- Mensajes Flash -->
        @if (session()->has('success'))
            <div class="alert alert-success flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Traffic Convictions
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.traffic.create') }}" class="w-full sm:w-auto"
                    variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="PlusCircle" />
                    Add Conviction
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title p-3">Filters</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.traffic.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-base.form-label for="search_term">Search</x-base.form-label>
                            <x-base.form-input id="search_term" name="search_term" type="text"
                                placeholder="Search by charge, location or penalty" value="{{ request('search_term') }}" />
                        </div>
                        <div>
                            <x-base.form-label for="carrier_filter">Carrier</x-base.form-label>
                            <select id="carrier_filter" name="carrier_filter"
                                class="select2 w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">All Carriers</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}"
                                        {{ request('carrier_filter') == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-base.form-label for="driver_filter">Driver</x-base.form-label>
                            <select id="driver_filter" name="driver_filter"
                                class="select2 w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">All Drivers</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}"
                                        {{ request('driver_filter') == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->user->name }} {{ $driver->user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-base.form-label for="date_from">From Date</x-base.form-label>
                            <x-base.litepicker id="date_from" name="date_from" class="w-full"
                                value="{{ request('date_from') }}" />
                        </div>
                        <div>
                            <x-base.form-label for="date_to">To Date</x-base.form-label>
                            <x-base.litepicker id="date_to" name="date_to" class="w-full" value="{{ request('date_to') }}" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                                Filter
                            </button>
                            <a href="{{ route('admin.traffic.index') }}" class="btn btn-outline-secondary">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="refresh-cw" />
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Infracciones de Tráfico -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <div class="overflow-x-auto">
                    <x-base.table class="border-separate border-spacing-y-[10px]">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="whitespace-nowrap">Registration Date</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Driver</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Carrier</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Date</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Location</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Charge</x-base.table.th>                                
                                <x-base.table.th class="whitespace-nowrap">Actions</x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @forelse ($convictions as $conviction)
                                <x-base.table.tr>                                    
                                    <x-base.table.td>
                                        {{ $conviction->created_at->format('m/d/Y') }}
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        {{ $conviction->userDriverDetail->user->name }}
                                        {{ $conviction->userDriverDetail->user->last_name }}
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        {{ $conviction->userDriverDetail->carrier->name ?? 'N/A' }}
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        {{ $conviction->conviction_date->format('m/d/Y') }}
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        {{ $conviction->location }}
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        {{ $conviction->charge }}
                                    </x-base.table.td>                                    
                                    <x-base.table.td class="flex">
                                        <div class="flex items-center">
                                            <a href="{{ route('admin.traffic.documents', $conviction->id) }}"
                                                class="btn-sm btn-danger  p-1 mr-2 flex" title="View Documents">
                                                <x-base.lucide class="w-4 h-4" icon="file-text" />                                                
                                            </a>
                                        </div>
                                        <x-base.menu class="h-5">
                                            <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                    icon="MoreVertical" />
                                            </x-base.menu.button>                                            
                                            <x-base.menu.items class="w-40">
                                                <a href="{{ route('admin.traffic.edit', $conviction->id) }}"
                                                    class="btn btn-sm btn-rounded-primary mr-1 flex gap-2 items-center text-primary p-3">
                                                    <x-base.lucide class="w-4 h-4" icon="edit" />
                                                    Edit
                                                </a>
                                                <a href="{{ route('admin.drivers.traffic-history', $conviction->userDriverDetail->id) }}"
                                                    class="btn-sm btn-danger mr-2 flex gap-2 items-center text-primary p-3">
                                                    <x-base.lucide class="w-4 h-4" icon="eye" />
                                                    View
                                                </a>
                                                <button data-tw-toggle="modal"
                                                    data-tw-target="#delete-conviction-modal-{{ $conviction->id }}"
                                                    class="btn-sm btn-danger mr-2 flex gap-2 items-center text-danger p-3">
                                                    <x-base.lucide class="w-4 h-4" icon="trash" />
                                                    Delete
                                                </button>

                                                <!-- Modal Eliminar Infracción de Tráfico para cada registro -->
                                                <x-base.dialog id="delete-conviction-modal-{{ $conviction->id }}"
                                                    size="md">
                                                    <x-base.dialog.panel>
                                                        <div class="p-5 text-center">
                                                            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger"
                                                                icon="x-circle" />
                                                            <div class="mt-5 text-2xl">Are you sure?</div>
                                                            <div class="mt-2 text-slate-500">
                                                                Do you really want to delete this traffic conviction record?
                                                                <br>
                                                                This process cannot be undone.
                                                            </div>
                                                        </div>
                                                        <form
                                                            action="{{ route('admin.traffic.destroy', $conviction->id) }}"
                                                            method="POST" class="px-5 pb-8 text-center">
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-base.button data-tw-dismiss="modal" type="button"
                                                                variant="outline-secondary" class="mr-1 w-24">
                                                                Cancel
                                                            </x-base.button>
                                                            <x-base.button type="submit" variant="danger"
                                                                class="w-24">
                                                                Delete
                                                            </x-base.button>
                                                        </form>
                                                    </x-base.dialog.panel>
                                                </x-base.dialog>

                                            </x-base.menu.items>
                                        </x-base.menu>

                                    </x-base.table.td>
                                </x-base.table.tr>
                            @empty
                                <x-base.table.tr>
                                    <x-base.table.td colspan="6" class="text-center">
                                        <div class="flex flex-col items-center justify-center py-16">
                                            <x-base.lucide class="h-8 w-8 text-slate-400" icon="Users" />
                                            No traffic convictions found
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                            @endforelse
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
                <div class="mt-5">
                    {{ $convictions->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Los modales de eliminación ahora están incluidos para cada registro en la tabla -->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar select2 para los filtros
                $('.select2').select2();

                // Manejar cambio de carrier para filtrar conductores
                $('#carrier').on('change', function() {
                    const carrierId = $(this).val();
                    if (carrierId) {
                        // Hacer una petición AJAX para obtener los conductores de esta transportista
                        $.ajax({
                            url: `/admin/traffic/carriers/${carrierId}/drivers`,
                            type: 'GET',
                            success: function(data) {
                                // Limpiar y actualizar el select de conductores
                                $('#user_driver_detail_id').empty();
                                $('#user_driver_detail_id').append(
                                    '<option value="">Select Driver</option>');

                                data.forEach(function(driver) {
                                    $('#user_driver_detail_id').append(
                                        `<option value="${driver.id}">${driver.user.name} ${driver.user.last_name || ''}</option>`
                                    );
                                });
                            },
                            error: function(xhr) {
                                console.error('Error loading drivers:', xhr);
                            }
                        });
                    } else {
                        // Si no hay carrier seleccionado, limpiar el select de conductores
                        $('#user_driver_detail_id').empty();
                        $('#user_driver_detail_id').append('<option value="">Select Driver</option>');
                    }
                });

                // Manejar edición de infracción de tráfico
                $('.edit-conviction').on('click', function() {
                    const conviction = $(this).data('conviction');

                    // Configurar la acción del formulario
                    $('#edit_conviction_form').attr('action', `/admin/traffic/${conviction.id}`);

                    // Rellenar los campos del formulario
                    $('#edit_user_driver_detail_id').val(conviction.user_driver_detail_id);
                    $('#edit_conviction_date').val(conviction.conviction_date.split('T')[0]); // Formatear fecha
                    $('#edit_location').val(conviction.location);
                    $('#edit_charge').val(conviction.charge);
                    $('#edit_penalty').val(conviction.penalty);

                    // Configurar el enlace para ver documentos
                    $('#view_documents_link').attr('href', `/admin/traffic/${conviction.id}/documents`);

                    // Configurar carrier y driver
                    const carrierId = conviction.user_driver_detail.carrier_id;
                    $('#edit_carrier').val(carrierId).trigger('change');

                    // Asignar el driver una vez que se carguen las opciones (con un pequeño delay)
                    setTimeout(() => {
                        $('#edit_user_driver_detail_id').val(conviction.user_driver_detail_id);
                    }, 500);
                });
            });

            document.addEventListener('livewire:load', function() {
                console.log('Livewire is loaded and listening for notify events');
                Livewire.on('notify', notification => {
                    console.log('Notification received:', notification);
                    Toastify({
                        text: `${notification.message}\n${notification.details}`,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: notification.type === 'success' ? "green" : "orange",
                        stopOnFocus: true,
                    }).showToast();
                });
            });
        </script>
    @endpush
@endsection
