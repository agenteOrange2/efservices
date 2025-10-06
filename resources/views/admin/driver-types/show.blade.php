@extends('../layout/' . $layout)

@section('subhead')
    <title>Ver Driver Type - {{ config('app.name') }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Driver Type #{{ $driverApplication->id }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.driver-types.index') }}" class="btn btn-outline-secondary mr-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Volver al Listado
            </a>
            <a href="{{ route('admin.driver-types.edit', $driverApplication->id) }}" class="btn btn-primary">
                <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                Editar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- Vehicle Information -->
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">
                <div class="flex items-center pb-5 mb-5 border-b border-slate-200/60">
                    <div class="font-medium text-base mr-auto">Información del Vehículo</div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label font-medium">Vehículo:</label>
                    <div class="mt-1">
                        @if($driverApplication->vehicle)
                            <div class="font-medium">{{ $driverApplication->vehicle->unit_number }}</div>
                            <div class="text-slate-500 text-sm">{{ $driverApplication->vehicle->carrier ? $driverApplication->vehicle->carrier->name : 'Sin Carrier' }}</div>
                        @else
                            <span class="text-slate-500">N/A</span>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label font-medium">Tipo de Ownership:</label>
                    <div class="mt-1">
                        @php
                            $ownershipLabels = [
                                'company_driver' => 'Company Driver',
                                'owner_operator' => 'Owner Operator', 
                                'third_party' => 'Third Party',
                                'other' => 'Other'
                            ];
                            $badgeClasses = [
                                'company_driver' => 'badge-primary',
                                'owner_operator' => 'badge-success',
                                'third_party' => 'badge-warning',
                                'other' => 'badge-secondary'
                            ];
                        @endphp
                        <span class="badge {{ $badgeClasses[$driverApplication->ownership_type] ?? 'badge-secondary' }}">
                            {{ $ownershipLabels[$driverApplication->ownership_type] ?? 'N/A' }}
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label font-medium">Fecha de Creación:</label>
                    <div class="mt-1 text-slate-600">
                        {{ $driverApplication->created_at ? $driverApplication->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label font-medium">Última Actualización:</label>
                    <div class="mt-1 text-slate-600">
                        {{ $driverApplication->updated_at ? $driverApplication->updated_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver Details -->
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">
                <div class="flex items-center pb-5 mb-5 border-b border-slate-200/60">
                    <div class="font-medium text-base mr-auto">Detalles del Conductor</div>
                </div>
                
                @if($driverApplication->ownership_type == 'company_driver' && $driverApplication->userDriverDetail)
                    <div class="mb-4">
                        <label class="form-label font-medium">Nombre del Conductor:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->userDriverDetail->driver_name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Teléfono:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->userDriverDetail->driver_phone ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Número de Licencia:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->userDriverDetail->license_number ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Fecha de Expiración de Licencia:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->userDriverDetail->license_expiration ? date('d/m/Y', strtotime($driverApplication->userDriverDetail->license_expiration)) : 'N/A' }}
                        </div>
                    </div>
                @elseif($driverApplication->ownership_type == 'owner_operator' && $driverApplication->ownerOperatorDetail)
                    <div class="mb-4">
                        <label class="form-label font-medium">Nombre del Owner Operator:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->ownerOperatorDetail->owner_name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Teléfono:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->ownerOperatorDetail->owner_phone ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Número de Licencia:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->ownerOperatorDetail->license_number ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Fecha de Expiración de Licencia:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->ownerOperatorDetail->license_expiration ? date('d/m/Y', strtotime($driverApplication->ownerOperatorDetail->license_expiration)) : 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Nombre de la Compañía:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->ownerOperatorDetail->company_name ?? 'N/A' }}
                        </div>
                    </div>
                @elseif($driverApplication->ownership_type == 'third_party' && $driverApplication->thirdPartyDetail)
                    <div class="mb-4">
                        <label class="form-label font-medium">Nombre del Third Party:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->thirdPartyDetail->third_party_name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Teléfono:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->thirdPartyDetail->third_party_phone ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Número de Licencia:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->thirdPartyDetail->license_number ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Fecha de Expiración de Licencia:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->thirdPartyDetail->license_expiration ? date('d/m/Y', strtotime($driverApplication->thirdPartyDetail->license_expiration)) : 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-medium">Nombre de la Compañía:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->thirdPartyDetail->company_name ?? 'N/A' }}
                        </div>
                    </div>
                @elseif($driverApplication->ownership_type == 'other')
                    <div class="mb-4">
                        <label class="form-label font-medium">Descripción:</label>
                        <div class="mt-1 text-slate-600">
                            {{ $driverApplication->other_description ?? 'N/A' }}
                        </div>
                    </div>
                @else
                    <div class="text-slate-500 text-center py-8">
                        No hay detalles disponibles para este tipo de ownership.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="intro-y flex items-center justify-center sm:justify-end mt-5">
        <a href="{{ route('admin.driver-types.index') }}" class="btn btn-outline-secondary w-24 mr-1">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Volver
        </a>
        <a href="{{ route('admin.driver-types.edit', $driverApplication->id) }}" class="btn btn-primary w-24">
            <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
            Editar
        </a>
    </div>
@endsection

@section('script')
    <script type="module">
        import { createIcons, Lucide } from "@/base-components/Lucide";
        
        // Recreate icons
        createIcons({
            icons: {
                "Lucide": Lucide,
            },
        });
    </script>
@endsection