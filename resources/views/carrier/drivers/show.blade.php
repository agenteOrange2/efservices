@extends('../themes/' . $activeTheme)
@section('title', 'Driver Details')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('carrier.dashboard')],
        ['label' => 'Drivers', 'url' => route('carrier.drivers.index')],
        ['label' => 'Driver Details', 'active' => true],
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
                Driver Details
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('carrier.drivers.edit', $driver->id) }}" class="btn btn-primary flex items-center mr-3">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                    Edit Driver
                </a>
                <a href="{{ route('carrier.drivers.index') }}" class="btn btn-outline-secondary flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to Drivers List
                </a>
            </div>
        </div>

        <!-- Información del Conductor -->
        <div class="grid grid-cols-12 gap-5 mt-5">
            <!-- Perfil Básico -->
            <div class="col-span-12 lg:col-span-4">
                <div class="box box--stacked">
                    <div class="box-header">
                        <h2 class="box-title">Profile</h2>
                    </div>
                    <div class="box-body p-5 text-center">
                        <div class="w-24 h-24 image-fit mx-auto">
                            <img alt="{{ $driver->user->name }} {{ $driver->last_name }}" class="rounded-full" src="{{ $driver->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}">
                        </div>
                        <div class="mt-3">
                            <h4 class="text-xl font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</h4>
                            <div class="text-slate-500 mt-1">Driver</div>
                            <div class="mt-3">
                                @if($driver->status === 1)
                                    <span class="px-2 py-1 rounded-full bg-success/20 text-success">Active</span>
                                @elseif($driver->status === 2)
                                    <span class="px-2 py-1 rounded-full bg-warning/20 text-warning">Pending</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-danger/20 text-danger">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-5 border-t border-slate-200/60 dark:border-darkmode-400 pt-5">
                            <div class="flex items-center justify-center">
                                <div class="mr-2 text-slate-500">
                                    <x-base.lucide class="w-4 h-4" icon="mail" />
                                </div>
                                <div class="text-slate-600 dark:text-slate-500">{{ $driver->user->email }}</div>
                            </div>
                            <div class="flex items-center justify-center mt-2">
                                <div class="mr-2 text-slate-500">
                                    <x-base.lucide class="w-4 h-4" icon="phone" />
                                </div>
                                <div class="text-slate-600 dark:text-slate-500">{{ $driver->phone }}</div>
                            </div>
                            <div class="flex items-center justify-center mt-2">
                                <div class="mr-2 text-slate-500">
                                    <x-base.lucide class="w-4 h-4" icon="map-pin" />
                                </div>
                                <div class="text-slate-600 dark:text-slate-500">{{ $driver->city }}, {{ $driver->state }}</div>
                            </div>
                        </div>
                        <div class="mt-5 flex justify-center">
                            <div class="flex flex-col items-center mr-5">
                                <div class="text-slate-500">Hire Date</div>
                                <div class="font-medium mt-1">{{ $driver->hire_date ? $driver->hire_date->format('M d, Y') : 'N/A' }}</div>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="text-slate-500">License Expires</div>
                                <div class="font-medium mt-1">{{ $driver->license_expiration ? $driver->license_expiration->format('M d, Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones Rápidas -->
                <div class="box box--stacked mt-5">
                    <div class="box-header">
                        <h2 class="box-title">Quick Actions</h2>
                    </div>
                    <div class="box-body p-5">
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('carrier.drivers.accidents.driver_history', $driver->id) }}" class="btn btn-outline-secondary w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="alert-triangle" />
                                Accidents
                            </a>
                            <a href="{{ route('carrier.drivers.testings.driver_history', $driver->id) }}" class="btn btn-outline-secondary w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="clipboard-check" />
                                Drug Tests
                            </a>
                            <a href="{{ route('carrier.drivers.inspections.driver_history', $driver->id) }}" class="btn btn-outline-secondary w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="clipboard-list" />
                                Inspections
                            </a>
                            <button data-tw-toggle="modal" data-tw-target="#delete-driver-modal" class="btn btn-outline-danger w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="trash" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Información Detallada -->
            <div class="col-span-12 lg:col-span-8">
                <div class="box box--stacked">
                    <div class="box-header">
                        <h2 class="box-title">Personal Information</h2>
                    </div>
                    <div class="box-body p-5">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">Full Name</div>
                                <div class="font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">Email</div>
                                <div class="font-medium">{{ $driver->user->email }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">Phone</div>
                                <div class="font-medium">{{ $driver->phone }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">Date of Birth</div>
                                <div class="font-medium">{{ $driver->date_of_birth ? $driver->date_of_birth->format('M d, Y') : 'N/A' }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">SSN</div>
                                <div class="font-medium">{{ $driver->ssn ? 'XXX-XX-' . substr($driver->ssn, -4) : 'N/A' }}</div>
                            </div>
                            <div class="col-span-12">
                                <div class="text-slate-500 mb-1">Address</div>
                                <div class="font-medium">{{ $driver->address }}</div>
                                <div class="font-medium">{{ $driver->city }}, {{ $driver->state }} {{ $driver->zip_code }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información de Licencia -->
                <div class="box box--stacked mt-5">
                    <div class="box-header">
                        <h2 class="box-title">License Information</h2>
                    </div>
                    <div class="box-body p-5">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">License Number</div>
                                <div class="font-medium">{{ $driver->license_number }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">License State</div>
                                <div class="font-medium">{{ $driver->license_state }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">License Class</div>
                                <div class="font-medium">Class {{ $driver->license_class }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 mb-1">License Expiration</div>
                                <div class="font-medium">{{ $driver->license_expiration ? $driver->license_expiration->format('M d, Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notas -->
                <div class="box box--stacked mt-5">
                    <div class="box-header">
                        <h2 class="box-title">Notes</h2>
                    </div>
                    <div class="box-body p-5">
                        <div class="text-slate-600 dark:text-slate-500">
                            {{ $driver->notes ?: 'No notes available.' }}
                        </div>
                    </div>
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
            <form action="{{ route('carrier.drivers.destroy', $driver->id) }}" method="POST" class="px-5 pb-8 text-center">
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
@endsection
