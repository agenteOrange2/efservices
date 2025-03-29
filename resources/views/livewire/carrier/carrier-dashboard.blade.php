<div>
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <!-- Estadísticas de cabecera -->
        <div class="col-span-12">
            <div class="grid grid-cols-4 gap-2">
                <div class="box relative col-span-4 flex-1 overflow-hidden rounded-[0.6rem] border-0 border-slate-200/60 bg-slate-50 bg-gradient-to-b from-theme-2/90 to-theme-1/[0.85] p-5 before:absolute before:right-0 before:top-0 before:-mr-[62%] before:h-[130%] before:w-full before:rotate-45 before:bg-gradient-to-b before:from-black/[0.15] before:to-transparent before:content-[''] sm:col-span-2 xl:col-span-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/10 bg-white/10">
                        <x-base.lucide class="h-6 w-6 fill-white/10 text-white" icon="Users" />
                    </div>
                    <div class="mt-12 flex items-center">
                        <div class="text-2xl font-medium text-white">{{ $driversCount }}</div>
                        <div class="ml-3.5 flex items-center rounded-full border border-success/50 bg-success/50 py-[2px] pl-[7px] pr-1 text-xs font-medium text-white/90">
                            {{ $this->membershipLimits['driversPercentage'] }}%
                            <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5]" icon="ChevronUp" />
                        </div>
                    </div>
                    <div class="mt-1 text-base text-white/70">
                        Drivers
                    </div>
                </div>
                
                <div class="relative col-span-4 flex-1 overflow-hidden rounded-[0.6rem] border bg-white p-5 sm:col-span-2 xl:col-span-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-primary/10 bg-primary/10">
                        <x-base.lucide class="h-6 w-6 fill-primary/10 text-primary" icon="Truck" />
                    </div>
                    <div class="mt-12 flex items-center">
                        <div class="text-2xl font-medium">{{ $vehiclesCount }}</div>
                        <div class="ml-3.5 flex items-center rounded-full border border-success/50 bg-success/70 py-[2px] pl-[7px] pr-1 text-xs font-medium text-white/90">
                            {{ $this->membershipLimits['vehiclesPercentage'] }}%
                            <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5]" icon="ChevronUp" />
                        </div>
                    </div>
                    <div class="mt-1 text-base text-slate-500">
                        Vehicles
                    </div>
                </div>
                
                <div class="relative col-span-4 flex-1 overflow-hidden rounded-[0.6rem] border bg-white p-5 sm:col-span-2 xl:col-span-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-info/10 bg-info/10">
                        <x-base.lucide class="h-6 w-6 fill-info/10 text-info" icon="FileCheck" />
                    </div>
                    <div class="mt-12 flex items-center">
                        <div class="text-2xl font-medium">{{ $documentStats['approved'] }}</div>
                        <div class="ml-3.5 flex items-center rounded-full border border-success/50 bg-success/70 py-[2px] pl-[7px] pr-1 text-xs font-medium text-white/90">
                            {{ $documentStats['total'] > 0 ? round(($documentStats['approved'] / $documentStats['total']) * 100) : 0 }}%
                            <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5]" icon="ChevronUp" />
                        </div>
                    </div>
                    <div class="mt-1 text-base text-slate-500">
                        Documents Approved
                    </div>
                </div>
                
                <div class="relative col-span-4 flex-1 overflow-hidden rounded-[0.6rem] border bg-white p-5 sm:col-span-2 xl:col-span-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-primary/10 bg-primary/10">
                        <x-base.lucide class="h-6 w-6 fill-primary/10 text-primary" icon="FileClock" />
                    </div>
                    <div class="mt-12 flex items-center">
                        <div class="text-2xl font-medium">{{ $documentStats['pending'] }}</div>
                        <div class="ml-3.5 flex items-center rounded-full border border-warning/50 bg-warning/70 py-[2px] pl-[7px] pr-1 text-xs font-medium text-white/90">
                            {{ $documentStats['total'] > 0 ? round(($documentStats['pending'] / $documentStats['total']) * 100) : 0 }}%
                            <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5]" icon="AlertTriangle" />
                        </div>
                    </div>
                    <div class="mt-1 text-base text-slate-500">
                        Pending Documents
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Columnas de contenido principal -->
        <div class="col-span-12 flex flex-col gap-y-10 xl:col-span-8">
            <!-- Límites de Membresía -->
            <div>
                <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                    <div class="text-base font-medium">Membership Plan Limits</div>
                </div>
                <div class="box box--stacked mt-3.5 p-5">
                    <div class="flex flex-col gap-y-5">
                        <div>
                            <div class="flex justify-between">
                                <div class="text-slate-500">Drivers</div>
                                <div class="font-medium">{{ $driversCount }} / {{ $this->membershipLimits['maxDrivers'] }}</div>
                            </div>
                            <div class="mt-2 h-2 w-full rounded bg-slate-200">
                                <div
                                    class="h-full rounded bg-success"
                                    style="width: {{ $this->membershipLimits['driversPercentage'] }}%"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between">
                                <div class="text-slate-500">Vehicles</div>
                                <div class="font-medium">{{ $vehiclesCount }} / {{ $this->membershipLimits['maxVehicles'] }}</div>
                            </div>
                            <div class="mt-2 h-2 w-full rounded bg-slate-200">
                                <div
                                    class="h-full rounded bg-primary"
                                    style="width: {{ $this->membershipLimits['vehiclesPercentage'] }}%"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conductores Recientes -->
            <div>
                <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                    <div class="text-base font-medium">Recent Drivers</div>
                    {{-- <a href="{{ route('carrier.user_drivers.index', $carrier) }}" class="md:ml-auto text-sm font-medium text-primary">Ver Todos</a> --}}
                </div>
                <div class="box box--stacked mt-3.5 p-5">
                    <div class="overflow-x-auto">
                        <table class="table w-full text-left">
                            <thead>
                                <tr>
                                    <th class="border-b-2 whitespace-nowrap">Driver</th>
                                    <th class="border-b-2 whitespace-nowrap">Status</th>
                                    <th class="border-b-2 whitespace-nowrap text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDrivers as $driver)
                                    <tr>
                                        <td class="border-b">
                                            <div class="flex items-center">
                                                <div class="image-fit zoom-in w-10 h-10">
                                                    <img class="rounded-full" src="{{ $driver->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png') }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                                                    <div class="text-slate-500 text-xs">{{ $driver->user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="border-b">
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
                                        </td>
                                        <td class="border-b text-right">
                                            {{-- <a href="{{ route('carrier.user_drivers.edit', [$carrier, $driver]) }}" class="text-primary mr-2">
                                                <x-base.lucide class="h-4 w-4" icon="Edit" />
                                            </a> --}}
                                            {{-- <a href="{{ route('carrier.user_drivers.show', [$carrier, $driver]) }}" class="text-info">
                                                <x-base.lucide class="h-4 w-4" icon="Eye" />
                                            </a> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center border-b py-4">
                                            No se encontraron conductores
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Documentos Recientes -->
            <div>
                <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                    <div class="text-base font-medium">Recent Documents</div>
                    <a href="{{ route('carrier.documents.index', $carrier) }}" class="md:ml-auto text-sm font-medium text-primary">View All</a>
                </div>
                <div class="box box--stacked mt-3.5 p-5">
                    <div class="overflow-x-auto">
                        <table class="table w-full text-left">
                            <thead>
                                <tr>
                                    <th class="border-b-2 whitespace-nowrap">Documents</th>
                                    <th class="border-b-2 whitespace-nowrap">Type</th>
                                    <th class="border-b-2 whitespace-nowrap">Date</th>
                                    <th class="border-b-2 whitespace-nowrap">Status</th>
                                    <th class="border-b-2 whitespace-nowrap text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $document)
                                    <tr>
                                        <td class="border-b">
                                            <div class="font-medium">{{ $document->filename ?: 'Documento #' . $document->id }}</div>
                                        </td>
                                        <td class="border-b">{{ $document->documentType->name }}</td>
                                        {{-- <td class="border-b">{{ $document->date ? $document->date->format('d/m/Y') : $document->created_at->format('d/m/Y') }}</td> --}}
                                        <td class="border-b">
                                            @if($document->status === 1)
                                                <div class="flex items-center text-success">
                                                    <x-base.lucide class="h-4 w-4 mr-1" icon="CheckCircle" />
                                                    Aprobado
                                                </div>
                                            @elseif($document->status === 0)
                                                <div class="flex items-center text-warning">
                                                    <x-base.lucide class="h-4 w-4 mr-1" icon="Clock" />
                                                    Pendiente
                                                </div>
                                            @elseif($document->status === 2)
                                                <div class="flex items-center text-danger">
                                                    <x-base.lucide class="h-4 w-4 mr-1" icon="XCircle" />
                                                    Rechazado
                                                </div>
                                            @else
                                                <div class="flex items-center text-info">
                                                    <x-base.lucide class="h-4 w-4 mr-1" icon="AlertCircle" />
                                                    En Proceso
                                                </div>
                                            @endif
                                        </td>
                                        <td class="border-b text-right">
                                            <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" target="_blank" class="text-primary">
                                                <x-base.lucide class="h-4 w-4" icon="ExternalLink" />
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center border-b py-4">
                                            No se encontraron documentos
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido de la barra lateral -->
        <div class="col-span-12 flex flex-col gap-y-10 xl:col-span-4">
            <!-- Detalles del Carrier -->
            <div>
                <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                    <div class="text-base font-medium">Detalles del Carrier</div>
                </div>
                <div class="box box--stacked mt-3.5 p-5">
                    <div class="mb-5 flex flex-col items-center gap-y-2 border-b border-dashed border-slate-300/70 pb-5 sm:flex-row">
                        <div class="image-fit h-14 w-14 overflow-hidden rounded-full border-[3px] border-slate-200/70">
                            <img src="{{ $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/default_company.png') }}" alt="{{ $carrier->name }}">
                        </div>
                        <div class="text-center sm:ml-4 sm:text-left">
                            <div class="text-base font-medium">
                                {{ $carrier->name }}
                            </div>
                            <div class="mt-0.5 text-slate-500">
                                {{ $carrier->address }}, {{ $carrier->state }} {{ $carrier->zipcode }}
                            </div>
                        </div>
                        <div class="flex items-center rounded-full border border-{{ $carrier->status === 1 ? 'success' : 'warning' }}/10 bg-{{ $carrier->status === 1 ? 'success' : 'warning' }}/10 px-3 py-1 font-medium text-{{ $carrier->status === 1 ? 'success' : 'warning' }} sm:ml-auto">
                            <div class="mr-2 h-1.5 w-1.5 rounded-full border border-{{ $carrier->status === 1 ? 'success' : 'warning' }}/50 bg-{{ $carrier->status === 1 ? 'success' : 'warning' }}/50"></div>
                            {{ $carrier->statusName }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between">
                            <div class="text-slate-500">Número DOT</div>
                            <div class="font-medium">{{ $carrier->dot_number ?: 'N/A' }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-slate-500">Número MC</div>
                            <div class="font-medium">{{ $carrier->mc_number ?: 'N/A' }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-slate-500">Número EIN</div>
                            <div class="font-medium">{{ $carrier->ein_number ?: 'N/A' }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-slate-500">DOT Estatal</div>
                            <div class="font-medium">{{ $carrier->state_dot ?: 'N/A' }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-slate-500">Cuenta IFTA</div>
                            <div class="font-medium">{{ $carrier->ifta_account ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-center">
                        <a href="{{ route('carrier.profile.edit') }}" class="btn btn-primary w-40">Editar Perfil</a>
                    </div>
                </div>
            </div>
            
            <!-- Estado de los Documentos -->
            <div>
                <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                    <div class="text-base font-medium">Estado de Documentos</div>
                </div>
                <div class="box box--stacked mt-3.5 p-5">
                    <div class="flex flex-col gap-4">
                        @foreach($documentStatusCounts as $status => $count)
                            <div>
                                <div class="flex justify-between">
                                    <div class="text-slate-500">{{ $status }}</div>
                                    <div class="font-medium">{{ $count }}</div>
                                </div>
                                <div class="mt-2 h-2 w-full rounded bg-slate-200">
                                    <div
                                        class="h-full rounded bg-{{ $status === 'Aprobado' ? 'success' : ($status === 'Pendiente' ? 'warning' : ($status === 'Rechazado' ? 'danger' : 'info')) }}"
                                        style="width: {{ $documentStats['total'] > 0 ? ($count / $documentStats['total'] * 100) : 0 }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-5">
                        <div class="text-base font-medium mb-3">Documentos por Tipo</div>
                        <div class="flex flex-col gap-4">
                            @forelse($documentTypeCounts as $type => $count)
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="h-3 w-3 rounded-full bg-primary mr-2"></div>
                                        <div class="text-slate-500">{{ $type }}</div>
                                    </div>
                                    <div class="font-medium">{{ $count }}</div>
                                </div>
                            @empty
                                <div class="text-center text-slate-500 py-4">
                                    No se encontraron documentos
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>