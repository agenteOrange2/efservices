@extends('../themes/' . $activeTheme)
@section('title', 'Carrier Details')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Carrier Details', 'active' => true],
    ];
@endphp

@section('subcontent')

<div class="intro   -y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">
        <i data-lucide="truck" class="w-5 h-5 mr-2 text-primary inline"></i>
        Carrier Details: {{ $carrier->name }}
    </h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.carrier.index') }}" class="btn btn-secondary shadow-md mr-2">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to list
        </a>
        <a href="{{ route('admin.carrier.edit', $carrier) }}" class="btn btn-primary shadow-md">
            <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Edit Carrier
        </a>
    </div>
</div>

<div class="grid grid-cols-12 gap-6 mt-5">
    <!-- Información Principal -->
    <div class="col-span-12 lg:col-span-4">
        <div class="box p-5">
            <div class="flex items-center border-b pb-5 mb-5">
                <div class="font-medium text-base truncate">Main Information</div>
            </div>
            <div class="flex flex-col">
                <!-- Logo -->
                <div class="flex justify-center mb-5">
                    @if($carrier->hasMedia('logo_carrier'))
                        <img src="{{ $carrier->getFirstMediaUrl('logo_carrier') }}" alt="Logo" class="w-32 h-32 object-contain border rounded-md">
                    @else
                        <div class="w-32 h-32 flex items-center justify-center bg-gray-100 border rounded-md">
                            <i data-lucide="image" class="w-12 h-12 text-gray-400"></i>
                        </div>
                    @endif
                </div>

                <!-- Datos -->
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Nombre:</span>
                        <span class="font-medium">{{ $carrier->name }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Dirección:</span>
                        <span class="font-medium">{{ $carrier->address }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Estado:</span>
                        <span class="font-medium">{{ $carrier->state }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Código Postal:</span>
                        <span class="font-medium">{{ $carrier->zipcode }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Número EIN:</span>
                        <span class="font-medium">{{ $carrier->ein_number }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Número DOT:</span>
                        <span class="font-medium">{{ $carrier->dot_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Número MC:</span>
                        <span class="font-medium">{{ $carrier->mc_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">DOT Estatal:</span>
                        <span class="font-medium">{{ $carrier->state_dot ?? 'N/A' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Cuenta IFTA:</span>
                        <span class="font-medium">{{ $carrier->ifta_account ?? 'N/A' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Plan:</span>
                        <span class="font-medium">{{ $carrier->membership->name ?? 'Sin plan' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-600 text-xs">Estado:</span>
                        @if($carrier->status == 1)
                            <span class="py-1 px-2 rounded-full text-xs bg-success text-white font-medium">Activo</span>
                        @elseif($carrier->status == 0)
                            <span class="py-1 px-2 rounded-full text-xs bg-danger text-white font-medium">Inactivo</span>
                        @else
                            <span class="py-1 px-2 rounded-full text-xs bg-warning text-white font-medium">Pendiente</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="col-span-12 lg:col-span-8">
        <div class="grid grid-cols-12 gap-6">
            <!-- Total de usuarios -->
            <div class="col-span-12 sm:col-span-4">
                <div class="report-box zoom-in">
                    <div class="box p-5">
                        <div class="flex">
                            <i data-lucide="users" class="report-box__icon text-primary"></i>
                        </div>
                        <div class="text-3xl font-medium leading-8 mt-6">{{ $userCarriers->count() }}</div>
                        <div class="text-base text-gray-600 mt-1">Usuarios</div>
                    </div>
                </div>
            </div>
            <!-- Total de conductores -->
            <div class="col-span-12 sm:col-span-4">
                <div class="report-box zoom-in">
                    <div class="box p-5">
                        <div class="flex">
                            <i data-lucide="user-check" class="report-box__icon text-pending"></i>
                        </div>
                        <div class="text-3xl font-medium leading-8 mt-6">{{ $drivers->count() }}</div>
                        <div class="text-base text-gray-600 mt-1">Conductores</div>
                    </div>
                </div>
            </div>
            <!-- Total de documentos -->
            <div class="col-span-12 sm:col-span-4">
                <div class="report-box zoom-in">
                    <div class="box p-5">
                        <div class="flex">
                            <i data-lucide="file-text" class="report-box__icon text-warning"></i>
                        </div>
                        <div class="text-3xl font-medium leading-8 mt-6">{{ $documents->count() }}</div>
                        <div class="text-base text-gray-600 mt-1">Documentos</div>
                    </div>
                </div>
            </div>
            <!-- Progreso de documentación -->
            <div class="col-span-12">
                <div class="box p-5">
                    <div class="flex items-center border-b pb-3 mb-3">
                        <div class="font-medium text-base truncate">Estado de Documentación</div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <div class="w-2/3">Documentos Aprobados</div>
                            <div class="w-1/3 text-right">{{ $approvedDocuments->count() }} de {{ $documents->count() }}</div>
                        </div>
                        <div class="progress h-4 mt-2">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $documents->count() > 0 ? ($approvedDocuments->count() / $documents->count()) * 100 : 0 }}%" 
                                 aria-valuenow="{{ $approvedDocuments->count() }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $documents->count() }}"></div>
                        </div>
                        
                        <div class="flex items-center mt-4">
                            <div class="w-2/3">Documentos Pendientes</div>
                            <div class="w-1/3 text-right">{{ $pendingDocuments->count() }} de {{ $documents->count() }}</div>
                        </div>
                        <div class="progress h-4 mt-2">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ $documents->count() > 0 ? ($pendingDocuments->count() / $documents->count()) * 100 : 0 }}%" 
                                 aria-valuenow="{{ $pendingDocuments->count() }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $documents->count() }}"></div>
                        </div>
                        
                        <div class="flex items-center mt-4">
                            <div class="w-2/3">Documentos Rechazados</div>
                            <div class="w-1/3 text-right">{{ $rejectedDocuments->count() }} de {{ $documents->count() }}</div>
                        </div>
                        <div class="progress h-4 mt-2">
                            <div class="progress-bar bg-danger" role="progressbar" 
                                 style="width: {{ $documents->count() > 0 ? ($rejectedDocuments->count() / $documents->count()) * 100 : 0 }}%" 
                                 aria-valuenow="{{ $rejectedDocuments->count() }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $documents->count() }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pestañas -->
<div class="box p-5 mt-5">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item flex-1" role="presentation">
            <button class="nav-link w-full py-2 active" data-tw-toggle="tab" data-tw-target="#users-tab" type="button" role="tab" aria-controls="users-tab" aria-selected="true">Usuarios</button>
        </li>
        <li class="nav-item flex-1" role="presentation">
            <button class="nav-link w-full py-2" data-tw-toggle="tab" data-tw-target="#drivers-tab" type="button" role="tab" aria-controls="drivers-tab" aria-selected="false">Conductores</button>
        </li>
        <li class="nav-item flex-1" role="presentation">
            <button class="nav-link w-full py-2" data-tw-toggle="tab" data-tw-target="#documents-tab" type="button" role="tab" aria-controls="documents-tab" aria-selected="false">Documentos</button>
        </li>
    </ul>
    <div class="tab-content mt-5">
        <!-- Tab Usuarios -->
        <div class="tab-pane active" id="users-tab" role="tabpanel" aria-labelledby="users-tab">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">#</th>
                            <th class="whitespace-nowrap">Nombre</th>
                            <th class="whitespace-nowrap">Email</th>
                            <th class="whitespace-nowrap">Teléfono</th>
                            <th class="whitespace-nowrap">Rol</th>
                            <th class="whitespace-nowrap">Estado</th>
                            <th class="whitespace-nowrap text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userCarriers as $index => $userCarrier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $userCarrier->user->name }}</td>
                                <td>{{ $userCarrier->user->email }}</td>
                                <td>{{ $userCarrier->user->phone_number ?? 'N/A' }}</td>
                                <td>{{ $userCarrier->user->getRoleNames()->first() ?? 'Sin rol' }}</td>
                                <td>
                                    @if($userCarrier->status == 1)
                                        <span class="py-1 px-2 rounded-full text-xs bg-success text-white font-medium">Activo</span>
                                    @else
                                        <span class="py-1 px-2 rounded-full text-xs bg-danger text-white font-medium">Inactivo</span>
                                    @endif
                                </td>
                                <td class="table-report__action">
                                    <div class="flex justify-center">
                                        <a href="{{ route('admin.carrier.user_carriers.edit', ['carrier' => $carrier, 'userCarrierDetails' => $userCarrier]) }}" class="btn btn-sm btn-primary mr-2">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        {{-- No hay una ruta show definida para user_carriers, así que podemos omitir este botón por ahora --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay usuarios registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tab Conductores -->
        <div class="tab-pane" id="drivers-tab" role="tabpanel" aria-labelledby="drivers-tab">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">#</th>
                            <th class="whitespace-nowrap">Nombre</th>
                            <th class="whitespace-nowrap">Email</th>
                            <th class="whitespace-nowrap">Teléfono</th>
                            <th class="whitespace-nowrap">Licencia</th>
                            <th class="whitespace-nowrap">Estado</th>
                            <th class="whitespace-nowrap text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $index => $driver)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $driver->user->name }}</td>
                                <td>{{ $driver->user->email }}</td>
                                <td>{{ $driver->phone ?? $driver->user->phone_number ?? 'N/A' }}</td>
                                <td>{{ $driver->licenses->first()->license_number ?? 'Sin licencia' }}</td>
                                <td>
                                    @if($driver->status == 1)
                                        <span class="py-1 px-2 rounded-full text-xs bg-success text-white font-medium">Activo</span>
                                    @else
                                        <span class="py-1 px-2 rounded-full text-xs bg-danger text-white font-medium">Inactivo</span>
                                    @endif
                                </td>
                                <td class="table-report__action">
                                    <div class="flex justify-center">
                                        <a href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $carrier, 'userDriverDetail' => $driver]) }}" class="btn btn-sm btn-primary mr-2">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay conductores registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tab Documentos -->
        <div class="tab-pane" id="documents-tab" role="tabpanel" aria-labelledby="documents-tab">
            <div class="grid grid-cols-12 gap-6">
                <!-- Documentos Subidos -->
                <div class="col-span-12 lg:col-span-8">
                    <div class="box p-5">
                        <div class="flex items-center border-b pb-3 mb-3">
                            <div class="font-medium text-base truncate">Documentos Subidos</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">Tipo</th>
                                        <th class="whitespace-nowrap">Fecha</th>
                                        <th class="whitespace-nowrap">Estado</th>
                                        <th class="whitespace-nowrap">Archivo</th>
                                        <th class="whitespace-nowrap">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $document)
                                        <tr>
                                            <td>{{ $document->documentType->name }}</td>
                                            <td>{{ $document->date ? (is_string($document->date) ? $document->date : $document->date->format('d/m/Y')) : 'N/A' }}</td>
                                            <td>
                                                @if($document->status == 0)
                                                    <span class="py-1 px-2 rounded-full text-xs bg-warning text-white font-medium">Pendiente</span>
                                                @elseif($document->status == 1)
                                                    <span class="py-1 px-2 rounded-full text-xs bg-success text-white font-medium">Aprobado</span>
                                                @else
                                                    <span class="py-1 px-2 rounded-full text-xs bg-danger text-white font-medium">Rechazado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($document->hasMedia('carrier_documents'))
                                                    <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i data-lucide="file" class="w-4 h-4 mr-1"></i> Ver archivo
                                                    </a>
                                                @else
                                                    <span class="text-gray-500">Sin archivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex">
                                                    <a href="{{ route('admin.carriers.documents.edit', ['carrier' => $carrier, 'document' => $document]) }}" class="btn btn-sm btn-primary mr-2">
                                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                                    </a>
                                                    <form action="{{ route('admin.carriers.documents.destroy', ['carrier' => $carrier, 'document' => $document]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('\u00bfEst\u00e1 seguro de eliminar este documento?')">
                                                            <i data-lucide="trash" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No hay documentos registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Documentos Faltantes -->
                <div class="col-span-12 lg:col-span-4">
                    <div class="box p-5 h-full">
                        <div class="flex items-center border-b pb-3 mb-3">
                            <div class="font-medium text-base truncate">Documentos Faltantes</div>
                        </div>
                        
                        @if($missingDocumentTypes->count() > 0)
                            <div class="grid gap-3">
                                @foreach($missingDocumentTypes as $documentType)
                                    <div class="flex items-center p-3 border rounded-md">
                                        <div class="mr-auto">
                                            <div class="font-medium">{{ $documentType->name }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">Requerido: {{ $documentType->is_required ? 'Sí' : 'No' }}</div>
                                        </div>
                                        <a href="{{ route('admin.carriers.documents.create', ['carrier' => $carrier, 'document_type' => $documentType->id]) }}" class="btn btn-sm btn-primary">
                                            <i data-lucide="upload" class="w-4 h-4 mr-1"></i> Subir
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-success font-medium">¡Todos los tipos de documentos han sido registrados!</div>
                            </div>
                        @endif
                        
                        <div class="mt-5">
                            <a href="{{ route('admin.carriers.documents.index', ['carrier' => $carrier]) }}" class="btn btn-outline-primary w-full">
                                <i data-lucide="file-plus" class="w-4 h-4 mr-2"></i> Gestionar documentos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Inicializar los íconos de Lucide después de que el DOM esté listo
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
@endsection
