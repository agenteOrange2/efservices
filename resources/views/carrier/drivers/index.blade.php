@extends('../themes/' . $activeTheme)
@section('title', 'Driver Management')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('carrier.dashboard')],
        ['label' => 'Driver Management', 'active' => true],
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
                Driver Management
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('carrier.drivers.create') }}" class="btn btn-primary flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Driver
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('carrier.drivers.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search drivers..." />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status_filter"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status_filter') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status_filter') == '0' ? 'selected' : '' }}>Inactive</option>
                            <option value="2" {{ request('status_filter') == '2' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <x-base.button type="submit" variant="outline-primary" class="mr-2">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                            Apply Filters
                        </x-base.button>
                        <a href="{{ route('carrier.drivers.index') }}" class="btn btn-outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="x" />
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead>
                            <tr class="bg-slate-50/60">
                                <th class="whitespace-nowrap px-6 py-3">Driver</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Phone</th>
                                <th scope="col" class="px-6 py-3">License</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drivers as $driver)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="image-fit zoom-in w-10 h-10">
                                                <img class="rounded-full" src="{{ $driver->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">{{ $driver->user->email }}</td>
                                    <td class="px-6 py-4">{{ $driver->phone }}</td>
                                    <td class="px-6 py-4">
                                        {{ $driver->license_number }} ({{ $driver->license_state }})
                                        <div class="text-xs text-slate-500">
                                            Expires: {{ $driver->license_expiration ? $driver->license_expiration->format('M d, Y') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($driver->status === 1)
                                            <div class="flex items-center text-success">
                                                <x-base.lucide class="h-4 w-4 mr-1" icon="CheckCircle" />
                                                Active
                                            </div>
                                        @elseif($driver->status === 2)
                                            <div class="flex items-center text-warning">
                                                <x-base.lucide class="h-4 w-4 mr-1" icon="Clock" />
                                                Pending
                                            </div>
                                        @else
                                            <div class="flex items-center text-danger">
                                                <x-base.lucide class="h-4 w-4 mr-1" icon="XCircle" />
                                                Inactive
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center">
                                            <a href="{{ route('carrier.drivers.edit', $driver->id) }}" class="btn btn-primary mr-2 p-1">
                                                <x-base.lucide class="w-4 h-4" icon="edit" />
                                            </a>
                                            <a href="{{ route('carrier.drivers.show', $driver->id) }}" class="btn btn-info mr-2 p-1">
                                                <x-base.lucide class="w-4 h-4" icon="eye" />
                                            </a>
                                            <button data-tw-toggle="modal" data-tw-target="#delete-driver-modal" 
                                                class="btn btn-danger p-1 delete-driver"
                                                data-driver-id="{{ $driver->id }}">
                                                <x-base.lucide class="w-4 h-4" icon="trash" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="flex flex-col items-center justify-center py-4">
                                            <x-base.lucide class="w-10 h-10 text-slate-300" icon="users" />
                                            <p class="mt-2 text-slate-500">No drivers found</p>
                                            <a href="{{ route('carrier.drivers.create') }}" class="btn btn-outline-primary mt-3">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
                                                Add First Driver
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
                    {{ $drivers->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Conductor -->
    <x-base.dialog id="delete-driver-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
                <div class="mt-5 text-2xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this driver? <br>
                    This process cannot be undone.
                </div>
            </div>
            <form id="delete_driver_form" action="" method="POST" class="px-5 pb-8 text-center">
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
                // Configuración del modal de eliminación
                const deleteButtons = document.querySelectorAll('.delete-driver');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const driverId = this.getAttribute('data-driver-id');
                        document.getElementById('delete_driver_form').action =
                            `/carrier/drivers/${driverId}`;
                    });
                });
            });
        </script>
    @endpush
@endsection
