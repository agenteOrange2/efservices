@extends('../themes/' . $activeTheme)
@section('title', 'Driver Traffic Convictions History')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],        
        ['label' => 'Driver', 'url' => route('admin.drivers.show', $driver->id)],
        ['label' => 'Traffic Convictions History', 'active' => true],
    ];
@endphp

@section('subcontent')
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
                Traffic Convictions History for {{ $driver->user->name }} {{ $driver->user->last_name }}
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-outline-secondary mr-2">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to Driver
                </a>
                <x-base.button as="a" href="{{ route('admin.traffic.create') }}" class="w-full sm:w-auto" variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="PlusCircle" />
                    Add Conviction
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Filters</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.drivers.traffic-history', $driver->id) }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-base.form-label for="search_term">Search</x-base.form-label>
                            <x-base.form-input id="search_term" name="search_term" type="text"
                                placeholder="Search by charge, location or penalty" value="{{ request('search_term') }}" />
                        </div>
                        <div>
                            <x-base.form-label for="date_from">From Date</x-base.form-label>
                            <x-base.form-input id="date_from" name="date_from" type="date"
                                value="{{ request('date_from') }}" />
                        </div>
                        <div>
                            <x-base.form-label for="date_to">To Date</x-base.form-label>
                            <x-base.form-input id="date_to" name="date_to" type="date"
                                value="{{ request('date_to') }}" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                                Filter
                            </button>
                            <a href="{{ route('admin.drivers.traffic-history', $driver->id) }}" class="btn btn-outline-secondary">
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
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="border-b-2 dark:border-darkmode-300 whitespace-nowrap">Date</th>
                                <th class="border-b-2 dark:border-darkmode-300 whitespace-nowrap">Location</th>
                                <th class="border-b-2 dark:border-darkmode-300 whitespace-nowrap">Charge</th>
                                <th class="border-b-2 dark:border-darkmode-300 whitespace-nowrap">Penalty</th>
                                <th class="border-b-2 dark:border-darkmode-300 whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($convictions as $conviction)
                                <tr>
                                    <td>{{ $conviction->conviction_date->format('M d, Y') }}</td>
                                    <td>{{ $conviction->location }}</td>
                                    <td>{{ $conviction->charge }}</td>
                                    <td>{{ $conviction->penalty }}</td>
                                    <td>
                                        <div class="flex">
                                            <a href="{{ route('admin.traffic.edit', $conviction->id) }}" 
                                                class="btn btn-sm btn-rounded-primary mr-1">
                                                <x-base.lucide class="w-4 h-4" icon="edit" />
                                            </a>
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#delete-conviction-modal"
                                                class="btn-sm btn-rounded-danger mr-1 delete-conviction"
                                                data-conviction-id="{{ $conviction->id }}">
                                                <x-base.lucide class="w-4 h-4" icon="trash" />
                                            </x-base.button>
                                            <a href="{{ route('admin.traffic.documents', $conviction->id) }}"
                                                class="btn btn-sm btn-rounded-success mr-1" title="View Documents">
                                                <x-base.lucide class="w-4 h-4" icon="file-text" />
                                            </a>
                                            <span class="badge bg-primary text-white" title="Document Count">
                                                {{ \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\DriverTrafficConviction::class)->where('model_id', $conviction->id)->count() }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        No traffic convictions found for this driver
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">
                    {{ $convictions->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Infracción de Tráfico -->
    <x-base.dialog id="add-conviction-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Traffic Conviction</h2>
            </x-base.dialog.title>

            <form action="{{ route('admin.traffic.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Fecha de la infracción -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="conviction_date">Conviction Date</x-base.form-label>
                        <x-base.form-input id="conviction_date" name="conviction_date" type="date" required />
                    </div>

                    <!-- Ubicación -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="location">Location</x-base.form-label>
                        <x-base.form-input id="location" name="location" type="text"
                            placeholder="City, State" required />
                    </div>

                    <!-- Cargo -->
                    <div class="col-span-12">
                        <x-base.form-label for="charge">Charge</x-base.form-label>
                        <x-base.form-input id="charge" name="charge" type="text"
                            placeholder="Speeding, Reckless Driving, etc." required />
                    </div>

                    <!-- Penalización -->
                    <div class="col-span-12">
                        <x-base.form-label for="penalty">Penalty</x-base.form-label>
                        <x-base.form-input id="penalty" name="penalty" type="text"
                            placeholder="Fine, License Suspension, etc." required />
                    </div>
                    
                    <!-- Documentos -->
                    <div class="col-span-12">
                        <x-base.form-label for="documents">Documents</x-base.form-label>
                        <div class="border-2 border-dashed rounded-md p-6 text-center">
                            <div class="mx-auto cursor-pointer relative">
                                <input type="file" name="documents[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="w-full h-full opacity-0 absolute inset-0 cursor-pointer z-50">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Drag and drop files here or click to browse</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">You can add more documents later</p>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Editar Infracción de Tráfico -->
    <x-base.dialog id="edit-conviction-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Edit Traffic Conviction</h2>
            </x-base.dialog.title>

            <form id="edit_conviction_form" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_driver_detail_id" value="{{ $driver->id }}">
                <input type="hidden" name="redirect_to_driver" value="1">
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Fecha de la infracción -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_conviction_date">Conviction Date</x-base.form-label>
                        <x-base.form-input id="edit_conviction_date" name="conviction_date" type="date" required />
                    </div>

                    <!-- Ubicación -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_location">Location</x-base.form-label>
                        <x-base.form-input id="edit_location" name="location" type="text"
                            placeholder="City, State" required />
                    </div>

                    <!-- Cargo -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_charge">Charge</x-base.form-label>
                        <x-base.form-input id="edit_charge" name="charge" type="text"
                            placeholder="Speeding, Reckless Driving, etc." required />
                    </div>

                    <!-- Penalización -->
                    <div class="col-span-12">
                        <x-base.form-label for="edit_penalty">Penalty</x-base.form-label>
                        <x-base.form-input id="edit_penalty" name="penalty" type="text"
                            placeholder="Fine, License Suspension, etc." required />
                    </div>
                    
                    <!-- Documentos -->
                    <div class="col-span-12">
                        <x-base.form-label for="documents">Documents</x-base.form-label>
                        <div class="border-2 border-dashed rounded-md p-6 text-center">
                            <div class="mx-auto cursor-pointer relative">
                                <input type="file" name="documents[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="w-full h-full opacity-0 absolute inset-0 cursor-pointer z-50">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Drag and drop files here or click to browse</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <p class="text-xs text-gray-500">You can add more documents later</p>
                            <a href="#" id="view_documents_link" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                <i class="fas fa-eye mr-1"></i> View existing documents
                            </a>
                        </div>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Update
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modal Eliminar Infracción de Tráfico -->
    <x-base.dialog id="delete-conviction-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
                <div class="mt-5 text-2xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this traffic conviction record? <br>
                    This process cannot be undone.
                </div>
            </div>
            <form id="delete_conviction_form" action="" method="POST" class="px-5 pb-8 text-center">
                @csrf
                @method('DELETE')
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-24">
                    Cancel
                </x-base.button>
                <x-base.button type="submit" variant="danger" class="w-24">
                    Delete
                </x-base.button>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar edición de infracción de tráfico
            $('.edit-conviction').on('click', function() {
                const conviction = $(this).data('conviction');
                
                // Configurar la acción del formulario
                $('#edit_conviction_form').attr('action', `/admin/traffic/${conviction.id}`);
                
                // Rellenar los campos del formulario
                $('#edit_conviction_date').val(conviction.conviction_date.split('T')[0]); // Formatear fecha
                $('#edit_location').val(conviction.location);
                $('#edit_charge').val(conviction.charge);
                $('#edit_penalty').val(conviction.penalty);
                
                // Configurar el enlace para ver documentos
                $('#view_documents_link').attr('href', `/admin/traffic/${conviction.id}/documents`);
            });

            // Manejar eliminación de infracción de tráfico
            $('.delete-conviction').on('click', function() {
                const convictionId = $(this).data('conviction-id');
                $('#delete_conviction_form').attr('action', `/admin/traffic/${convictionId}`);
            });
        });
    </script>
    @endpush
@endsection
