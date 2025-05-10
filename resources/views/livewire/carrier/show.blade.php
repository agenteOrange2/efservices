@extends('../themes/' . $activeTheme)

@section('subhead')
    <title>Detalles del Conductor - EF Services</title>
@endsection

@section('subcontent')
    <div class="py-5">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h2 class="text-2xl font-medium">Detalles del Conductor</h2>
                <div class="mt-2 text-slate-500">
                    Información completa del conductor.
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex gap-2">
                <a href="{{ route('carrier.drivers.edit', $driver->id) }}" class="btn btn-primary">
                    <x-base.lucide class="h-4 w-4 mr-2" icon="Edit" />
                    Editar
                </a>
                <a href="{{ route('carrier.drivers.index') }}" class="btn btn-outline-secondary">
                    <x-base.lucide class="h-4 w-4 mr-2" icon="ArrowLeft" />
                    Volver
                </a>
            </div>
        </div>
        
        <!-- Información General -->
        <div class="box box--stacked mt-3.5 p-5">
            <div class="flex flex-col md:flex-row border-b pb-5 mb-5">
                <div class="md:w-64 flex justify-center">
                    <div class="image-fit h-40 w-40">
                        <img class="rounded-full border-4 border-slate-200/70" 
                            src="{{ $driver->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}"
                            alt="{{ $driver->user->name }} {{ $driver->last_name }}">
                    </div>
                </div>
                <div class="mt-6 md:mt-0 md:ml-6 flex-1">
                    <div class="text-xl font-medium mb-2">{{ $driver->user->name }} {{ $driver->middle_name }} {{ $driver->last_name }}</div>
                    
                    <div class="mt-1">
                        @if($driver->status === 1)
                            <div class="flex items-center text-success">
                                <x-base.lucide class="h-4 w-4 mr-1" icon="CheckCircle" />
                                Activo
                            </div>
                        @elseif($driver->status === 2)
                            <div class="flex items-center text-warning">
                                <x-base.lucide class="h-4 w-4 mr-1" icon="Clock" />
                                Pendiente
                            </div>
                        @else
                            <div class="flex items-center text-danger">
                                <x-base.lucide class="h-4 w-4 mr-1" icon="XCircle" />
                                Inactivo
                            </div>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 mt-6">
                        <div>
                            <div class="text-slate-500 text-sm">Email</div>
                            <div class="font-medium">{{ $driver->user->email }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-sm">Teléfono</div>
                            <div class="font-medium">{{ $driver->phone }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-sm">Fecha de Nacimiento</div>
                            <div class="font-medium">{{ $driver->date_of_birth ? $driver->date_of_birth->format('d/m/Y') : 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-sm">Fecha de Registro</div>
                            <div class="font-medium">{{ $driver->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Información adicional -->
            <div>
                <h3 class="text-lg font-medium mb-4">Documentos y Licencias</h3>
                
                @if($driver->licenses && $driver->licenses->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th class="border-b-2 whitespace-nowrap">Tipo</th>
                                    <th class="border-b-2 whitespace-nowrap">Número</th>
                                    <th class="border-b-2 whitespace-nowrap">Estado</th>
                                    <th class="border-b-2 whitespace-nowrap">Fecha de Expiración</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($driver->licenses as $license)
                                    <tr>
                                        <td class="border-b">
                                            {{ $license->license_class }} {{ $license->is_cdl ? '(CDL)' : '' }}
                                        </td>
                                        <td class="border-b">{{ $license->license_number }}</td>
                                        <td class="border-b">
                                            @if($license->status === 'active')
                                                <span class="text-success">Activa</span>
                                            @elseif($license->status === 'expired')
                                                <span class="text-danger">Expirada</span>
                                            @else
                                                <span class="text-warning">{{ ucfirst($license->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="border-b">
                                            {{ $license->expiration_date ? $license->expiration_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-slate-500 py-4">
                        No hay licencias registradas para este conductor.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection