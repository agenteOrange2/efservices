@extends('../themes/' . $activeTheme)
@section('title', 'Detalles del Vehículo')

@php
    use Illuminate\Support\Facades\Storage;

    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
        ['label' => 'Detalles del Vehículo', 'active' => true],
    ];
@endphp


@section('subcontent')
    @push('styles')
        <style>
            .filter-btn.active {
                background-color: #1e40af;
                color: white;
            }
        </style>
    @endpush

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium">
                    Vehicle: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
                </div>
                <div class="flex flex-col gap-x-3 gap-y-6 sm:flex-row md:ml-auto">
                    {{-- <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary flex align-middle">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Editar Vehículo
                    </a> --}}
                    {{-- <a href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                        class="btn btn-outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Tool" />
                        Historial de Servicio
                    </a> --}}
                    <x-base.button as="a" href="{{ route('admin.vehicles.edit', $vehicle->id) }}"
                        class="w-full sm:w-44" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Edit Vehicle
                    </x-base.button>

                    <x-base.button as="a" href="{{ route('admin.vehicles.documents.index', $vehicle->id) }}"
                        class="w-full sm:w-44" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                        Documents
                    </x-base.button>

                    <x-base.button as="a" href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                        class="w-54" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="Activity" />
                        Service History
                    </x-base.button>
                </div>
            </div>

            <!-- Estado y alertas -->
            <div class="mt-3">
                @if ($vehicle->suspended)
                    <div class="alert alert-warning flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="AlertTriangle" />
                        <span>This vehicle is <strong>SUSPENDED</strong> from
                            {{ $vehicle->suspended_date->format('m/d/Y') }}</span>
                    </div>
                @endif

                @if ($vehicle->out_of_service)
                    <div class="alert alert-danger flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="XCircle" />
                        <span>This vehicle is <strong>OUT OF SERVICE</strong> from
                            {{ $vehicle->out_of_service_date->format('m/d/Y') }}</span>
                    </div>
                @endif

                @if ($vehicle->registration_expiration_date < now())
                    <div class="alert alert-danger flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="CalendarX" />
                        <span>The registration of this vehicle <strong>EXPIRED</strong> on
                            {{ $vehicle->registration_expiration_date->format('m/d/Y') }}</span>
                    </div>
                @elseif($vehicle->registration_expiration_date < now()->addDays(30))
                    <div class="alert alert-warning flex items-center mb-2">
                        <x-base.lucide class="mr-2 h-6 w-6" icon="Calendar" />
                        <span>Registration expires on
                            <strong>{{ $vehicle->registration_expiration_date->diffInDays(now()) }}
                                days</strong> ({{ $vehicle->registration_expiration_date->format('m/d/Y') }})</span>
                    </div>
                @endif

                @if (isset($vehicle->annual_inspection_expiration_date))
                    @if ($vehicle->annual_inspection_expiration_date < now())
                        <div class="alert alert-danger flex items-center mb-2">
                            <x-base.lucide class="mr-2 h-6 w-6" icon="ClipboardX" />
                            <span>Annual inspection <strong>EXPIRED</strong> on
                                {{ $vehicle->annual_inspection_expiration_date->format('m/d/Y') }}</span>
                        </div>
                    @elseif($vehicle->annual_inspection_expiration_date < now()->addDays(30))
                        <div class="alert alert-warning flex items-center mb-2">
                            <x-base.lucide class="mr-2 h-6 w-6" icon="Clipboard" />
                            <span>Annual inspection expires on
                                <strong>{{ $vehicle->annual_inspection_expiration_date->diffInDays(now()) }} days</strong>
                                ({{ $vehicle->annual_inspection_expiration_date->format('m/d/Y') }})</span>
                        </div>
                    @endif
                @endif
            </div>

            <div class="mt-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Información General -->
                    <div class="box box--stacked">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">General Information</div>
                        </div>
                        <div class="box-body p-5">
                            <table class="w-full">
                                <tr class="border-b border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Carrier:</td>
                                    <td class="py-2">{{ $vehicle->carrier->name }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Make/Brand:</td>
                                    <td class="py-2">{{ $vehicle->make }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Model:</td>
                                    <td class="py-2">{{ $vehicle->model }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Year:</td>
                                    <td class="py-2">{{ $vehicle->year }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Type:</td>
                                    <td class="py-2">{{ $vehicle->type }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Unit Number:</td>
                                    <td class="py-2">{{ $vehicle->company_unit_number ?? 'No asignado' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">VIN:</td>
                                    <td class="py-2 font-mono">{{ $vehicle->vin }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">GVWR:</td>
                                    <td class="py-2">{{ $vehicle->gvwr ?? 'No especificado' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Fuel Type:</td>
                                    <td class="py-2">{{ $vehicle->fuel_type }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Property Type:</td>
                                    <td class="py-2">
                                        @if ($vehicle->ownership_type == 'owned')
                                            Owner Operator
                                        @elseif($vehicle->ownership_type == 'leased')
                                            Company Driver
                                        @elseif($vehicle->ownership_type == 'third-party')
                                            Third Party Company Driver
                                        @else
                                            {{ $vehicle->ownership_type }}
                                        @endif
                                    </td>
                                </tr>
                                @if ($vehicle->driverApplicationDetail)
                                    <!-- Información específica según el tipo de propiedad -->
                                    @if ($vehicle->ownership_type == 'owned')
                                        <!-- Owner Operator -->
                                        <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                            <td class="py-2 font-medium">Owner Operator:</td>
                                            <td class="py-2">
                                                @php
                                                    // Intentar obtener datos de la nueva tabla primero
                                                    $ownerName = null;
                                                    $ownerPhone = null;
                                                    $ownerEmail = null;
                                                    $contractAgreed = false;

                                                    if (
                                                        $vehicle->driverApplicationDetail &&
                                                        $vehicle->driverApplicationDetail->application &&
                                                        $vehicle->driverApplicationDetail->application
                                                            ->ownerOperatorDetail
                                                    ) {
                                                        // Datos de la nueva tabla
                                                        $ownerDetail =
                                                            $vehicle->driverApplicationDetail->application
                                                                ->ownerOperatorDetail;
                                                        $ownerName = $ownerDetail->owner_name;
                                                        $ownerPhone = $ownerDetail->owner_phone;
                                                        $ownerEmail = $ownerDetail->owner_email;
                                                        $contractAgreed = $ownerDetail->contract_agreed;
                                                    } elseif ($vehicle->driverApplicationDetail) {
                                                        // Datos de la tabla anterior como respaldo
                                                        $ownerName = $vehicle->driverApplicationDetail->owner_name;
                                                        $ownerPhone = $vehicle->driverApplicationDetail->owner_phone;
                                                        $ownerEmail = $vehicle->driverApplicationDetail->owner_email;
                                                        $contractAgreed =
                                                            $vehicle->driverApplicationDetail->contract_agreed ?? false;
                                                    }
                                                @endphp

                                                @if ($ownerName)
                                                    {{ $ownerName }}<br>
                                                    @if ($ownerPhone)
                                                        <small class="text-slate-500">{{ $ownerPhone }}</small><br>
                                                    @endif
                                                    @if ($ownerEmail)
                                                        <small class="text-slate-500">{{ $ownerEmail }}</small>
                                                    @endif
                                                @else
                                                    Not available
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                            <td class="py-2 font-medium">Contract Accepted:</td>
                                            <td class="py-2">
                                                @if ($contractAgreed)
                                                    <span class="text-success">Yes</span>
                                                @else
                                                    <span class="text-danger">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @elseif($vehicle->ownership_type == 'third-party')
                                        <!-- Third Party Company Driver -->
                                        <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                            <td class="py-2 font-medium">Third Party Company:</td>
                                            <td class="py-2">
                                                @php
                                                    // Intentar obtener datos de la nueva tabla primero
                                                    $thirdPartyName = null;
                                                    $thirdPartyPhone = null;
                                                    $thirdPartyEmail = null;
                                                    $thirdPartyDba = null;
                                                    $thirdPartyAddress = null;
                                                    $thirdPartyContact = null;
                                                    $thirdPartyFein = null;
                                                    $emailSent = false;

                                                    if (
                                                        $vehicle->driverApplicationDetail &&
                                                        $vehicle->driverApplicationDetail->application &&
                                                        $vehicle->driverApplicationDetail->application->thirdPartyDetail
                                                    ) {
                                                        // Datos de la nueva tabla
                                                        $thirdPartyDetail =
                                                            $vehicle->driverApplicationDetail->application
                                                                ->thirdPartyDetail;
                                                        $thirdPartyName = $thirdPartyDetail->third_party_name;
                                                        $thirdPartyPhone = $thirdPartyDetail->third_party_phone;
                                                        $thirdPartyEmail = $thirdPartyDetail->third_party_email;
                                                        $thirdPartyDba = $thirdPartyDetail->third_party_dba;
                                                        $thirdPartyAddress = $thirdPartyDetail->third_party_address;
                                                        $thirdPartyContact = $thirdPartyDetail->third_party_contact;
                                                        $thirdPartyFein = $thirdPartyDetail->third_party_fein;
                                                        $emailSent = $thirdPartyDetail->email_sent;
                                                    } elseif ($vehicle->driverApplicationDetail) {
                                                        // Datos de la tabla anterior como respaldo
                                                        $thirdPartyName =
                                                            $vehicle->driverApplicationDetail->third_party_name;
                                                        $thirdPartyPhone =
                                                            $vehicle->driverApplicationDetail->third_party_phone;
                                                        $thirdPartyEmail =
                                                            $vehicle->driverApplicationDetail->third_party_email;
                                                        $thirdPartyDba =
                                                            $vehicle->driverApplicationDetail->third_party_dba;
                                                        $thirdPartyAddress =
                                                            $vehicle->driverApplicationDetail->third_party_address;
                                                        $thirdPartyContact =
                                                            $vehicle->driverApplicationDetail->third_party_contact;
                                                        $thirdPartyFein =
                                                            $vehicle->driverApplicationDetail->third_party_fein;
                                                        $emailSent =
                                                            $vehicle->driverApplicationDetail->email_sent ?? false;
                                                    }
                                                @endphp

                                                @if ($thirdPartyName)
                                                    {{ $thirdPartyName }}<br>
                                                    @if ($thirdPartyPhone)
                                                        <small class="text-slate-500">{{ $thirdPartyPhone }}</small><br>
                                                    @endif
                                                    @if ($thirdPartyEmail)
                                                        <small class="text-slate-500">{{ $thirdPartyEmail }}</small>
                                                    @endif
                                                @else
                                                    Not available
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                            <td class="py-2 font-medium">Verification Email:</td>
                                            <td class="py-2">
                                                @if ($emailSent)
                                                    <span class="text-success">Sent</span>
                                                @else
                                                    <span class="text-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if ($thirdPartyDba || $thirdPartyAddress || $thirdPartyContact || $thirdPartyFein)
                                            <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                                <td class="py-2 font-medium">Additional Details:</td>
                                                <td class="py-2">
                                                    @if ($thirdPartyDba)
                                                        <strong>DBA:</strong> {{ $thirdPartyDba }}<br>
                                                    @endif
                                                    @if ($thirdPartyAddress)
                                                        <strong>Address:</strong> {{ $thirdPartyAddress }}<br>
                                                    @endif
                                                    @if ($thirdPartyContact)
                                                        <strong>Contact:</strong> {{ $thirdPartyContact }}<br>
                                                    @endif
                                                    @if ($thirdPartyFein)
                                                        <strong>FEIN:</strong> {{ $thirdPartyFein }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif

                                        <!-- Documento de Consentimiento -->
                                        @php
                                            $driverId = null;
                                            if ($vehicle->driver) {
                                                $driverId = $vehicle->driver->id;
                                            } elseif (
                                                $vehicle->driverApplicationDetail &&
                                                $vehicle->driverApplicationDetail->driver_application_id
                                            ) {
                                                $driverApplication = \App\Models\Admin\Driver\DriverApplication::find(
                                                    $vehicle->driverApplicationDetail->driver_application_id,
                                                );
                                                if ($driverApplication && $driverApplication->user_id) {
                                                    $userDriverDetail = \App\Models\UserDriverDetail::where(
                                                        'user_id',
                                                        $driverApplication->user_id,
                                                    )->first();
                                                    if ($userDriverDetail) {
                                                        $driverId = $userDriverDetail->id;
                                                    }
                                                }
                                            }

                                            $documentPath = null;
                                            if ($driverId) {
                                                $documentPath = "driver/{$driverId}/vehicle_verifications/consentimiento_propietario_third_party.pdf";
                                            }
                                        @endphp

                                        <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                            <td class="py-2 font-medium">Consent document:</td>
                                            <td class="py-2">
                                                @if ($documentPath && Storage::disk('public')->exists($documentPath))
                                                    <a href="{{ Storage::disk('public')->url($documentPath) }}"
                                                        target="_blank" class="btn btn-sm btn-primary">
                                                        <x-base.lucide class="mr-1 h-4 w-4" icon="FileText" />
                                                        View Document
                                                    </a>
                                                @else
                                                    <span class="text-warning">Not available</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @elseif($vehicle->ownership_type == 'leased')
                                        <!-- Company Driver -->
                                        <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                            <td class="py-2 font-medium">Company Driver:</td>
                                            <td class="py-2">
                                                @if ($vehicle->driver)
                                                    {{ $vehicle->driver->user->name }}
                                                    {{ $vehicle->driver->last_name }}<br>
                                                    <small
                                                        class="text-slate-500">{{ $vehicle->driver->phone }}</small><br>
                                                    <small
                                                        class="text-slate-500">{{ $vehicle->driver->user->email }}</small>
                                                @else
                                                    <span class="text-warning">No driver assigned</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Location:</td>
                                    <td class="py-2">{{ $vehicle->location ?? 'No specified' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Tire Size:</td>
                                    <td class="py-2">{{ $vehicle->tire_size ?? 'No specified' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">IRP:</td>
                                    <td class="py-2">{{ $vehicle->irp_apportioned_plate ? 'Yes' : 'No' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Registro e Inspección -->
                    <div class="box box--stacked">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Registration and Inspection
                            </div>
                        </div>
                        <div class="box-body p-5">
                            <table class="w-full">
                                <tr class="border-b border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Registration status:</td>
                                    <td class="py-2">{{ $vehicle->registration_state }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Registration number:</td>
                                    <td class="py-2">{{ $vehicle->registration_number }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Expiration Registration:</td>
                                    <td class="py-2">
                                        <span
                                            class="{{ $vehicle->registration_expiration_date < now() ? 'text-danger' : ($vehicle->registration_expiration_date < now()->addDays(30) ? 'text-warning' : 'text-success') }}">
                                            {{ $vehicle->registration_expiration_date->format('m/d/Y') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Permanent Label:</td>
                                    <td class="py-2">{{ $vehicle->permanent_tag ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr class="border-b border-t border-slate-200/60 bg-slate-50">
                                    <td class="py-2 font-medium">Inspection:</td>
                                    <td class="py-2">
                                        @if (isset($vehicle->annual_inspection_expiration_date))
                                            <span
                                                class="{{ $vehicle->annual_inspection_expiration_date < now() ? 'text-danger' : ($vehicle->annual_inspection_expiration_date < now()->addDays(30) ? 'text-warning' : 'text-success') }}">
                                                {{ $vehicle->annual_inspection_expiration_date->format('m/d/Y') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Not registered</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Conductor Asignado -->
                            <div class="mt-6">
                                <h3 class="text-base font-medium mb-3 border-b border-slate-200/60 bg-slate-50 pb-3">
                                    Driver Assigned
                                </h3>
                                @if ($vehicle->driver)
                                    <div class="flex items-center">
                                        <div
                                            class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden">
                                            @if ($vehicle->driver->getFirstMediaUrl('profile_photo_driver'))
                                                <img src="{{ $vehicle->driver->getFirstMediaUrl('profile_photo_driver') }}"
                                                    alt="Foto de perfil" class="w-full h-full object-cover">
                                            @else
                                                <x-base.lucide class="h-6 w-6 text-slate-500" icon="User" />
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <div class="font-medium">
                                                {{ $vehicle->driver->user->name }}
                                                {{ $vehicle->driver->last_name }}
                                            </div>
                                            <div class="text-slate-500 text-xs">
                                                Phone:
                                                {{ $vehicle->driver->phone }}
                                            </div>
                                            <a href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $vehicle->carrier->slug, 'userDriverDetail' => $vehicle->driver->id]) }}"
                                                class="text-primary text-xs">View Profile</a>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                            <x-base.lucide class="h-6 w-6 text-slate-400" icon="UserX" />
                                        </div>
                                        <div class="ml-3 text-slate-500">
                                            Expiration Registration
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Estado -->
                            <div class="mt-6">
                                <h3 class="text-base font-medium mb-3 border-b border-slate-200/60 bg-slate-50 pb-3">
                                    Status
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-center">
                                        <div
                                            class="{{ $vehicle->out_of_service ? 'bg-danger text-white' : 'bg-slate-100 text-slate-500' }} w-8 h-8 rounded-full flex items-center justify-center">
                                            <x-base.lucide class="h-4 w-4" icon="XCircle" />
                                        </div>
                                        <div class="ml-2">
                                            <div class="text-sm">Out Of Services</div>
                                            @if ($vehicle->out_of_service)
                                                <div class="text-xs text-danger">From
                                                    {{ $vehicle->out_of_service_date->format('m/d/Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div
                                            class="{{ $vehicle->suspended ? 'bg-warning text-white' : 'bg-slate-100 text-slate-500' }} w-8 h-8 rounded-full flex items-center justify-center">
                                            <x-base.lucide class="h-4 w-4" icon="AlertTriangle" />
                                        </div>
                                        <div class="ml-2">
                                            <div class="text-sm">Suspended</div>
                                            @if ($vehicle->suspended)
                                                <div class="text-xs text-warning">From
                                                    {{ $vehicle->suspended_date->format('m/d/Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                @if ($vehicle->notes)
                    <div class="box box--stacked mt-5">
                        <div class="box-header">
                            <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">Notes</div>
                        </div>
                        <div class="box-body p-5">
                            <div class="whitespace-pre-line">{{ $vehicle->notes }}</div>
                        </div>
                    </div>
                @endif

                <!-- Historial de Servicio Reciente -->
                <div class="box box--stacked mt-5">
                    <div class="box-header">
                        <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">
                            <div class="flex justify-between items-center">
                                <span>Maintenance History</span>
                                <div>
                                    <x-base.button as="a"
                                        href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                                        class="w-full sm:w-auto" size="sm" variant="outline-primary">
                                        <x-base.lucide class="mr-1 h-3 w-3" icon="ListFilter" />
                                        View Complete History
                                    </x-base.button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body p-5">
                        <!-- Informational message about notifications -->
                        <div class="bg-primary/10 text-primary p-4 mb-4 rounded-lg">
                            <div class="flex items-center">
                                <x-base.lucide class="h-5 w-5 mr-2" icon="Info" />
                                <div>
                                    <p class="font-medium">Automatic Maintenance Notifications</p>
                                    <p class="text-sm">The system will automatically send notifications 14 and 7 days
                                        before the due date of each maintenance to:</p>
                                    <ul class="list-disc ml-5 text-sm mt-1">
                                        <li>System administrators and supervisors</li>
                                        <li>Users of the carrier to which the vehicle belongs</li>
                                        <li>The driver assigned to the vehicle</li>
                                    </ul>
                                    <p class="text-sm mt-1">You can send test notifications using the <x-base.lucide
                                            class="h-4 w-4 inline-block text-warning" icon="Bell" /> button on pending
                                        maintenances.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Filters -->
                        <div class="flex flex-wrap gap-3 mb-4">
                            <x-base.button as="a" href="#" data-filter="all"
                                class="w-full sm:w-auto filter-btn active" size="sm" variant="outline-secondary">
                                All
                            </x-base.button>
                            <x-base.button as="a" href="#" data-filter="pending"
                                class="w-full sm:w-auto filter-btn" size="sm" variant="outline-warning">
                                <x-base.lucide class="mr-1 h-3 w-3" icon="Clock" />
                                Pending
                            </x-base.button>
                            <x-base.button as="a" href="#" data-filter="completed"
                                class="w-full sm:w-auto filter-btn" size="sm" variant="outline-success">
                                <x-base.lucide class="mr-1 h-3 w-3" icon="CheckCircle" />
                                Completed
                            </x-base.button>
                            <x-base.button as="a" href="#" data-filter="overdue"
                                class="w-full sm:w-auto filter-btn" size="sm" variant="outline-danger">
                                <x-base.lucide class="mr-1 h-3 w-3" icon="AlertCircle" />
                                Overdue
                            </x-base.button>
                            <div class="ml-auto">
                                <x-base.button as="a" data-tw-toggle="modal" data-tw-target="#add-service-modal"
                                    class="w-full sm:w-auto" size="sm" variant="primary">
                                    <x-base.lucide class="mr-1 h-3 w-3" icon="Plus" />
                                    Add Maintenance
                                </x-base.button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr class="bg-slate-50/60">
                                        <th scope="col" class="px-1 py-3">Date</th>
                                        <th scope="col" class="px-1 py-3">Service</th>
                                        <th scope="col" class="px-1 py-3">Provider</th>
                                        <th scope="col" class="px-1 py-3">Cost</th>
                                        <th scope="col" class="px-1 py-3">Next</th>
                                        <th scope="col" class="px-1 py-3">Status</th>
                                        <th scope="col" class="px-1 py-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicle->maintenances as $item)
                                        <tr
                                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 maintenance-row {{ !$item->status && $item->isOverdue() ? 'overdue' : '' }} {{ !$item->status && !$item->isOverdue() ? 'pending' : '' }} {{ $item->status ? 'completed' : '' }}">
                                            <td>{{ $item->service_date->format('m/d/Y') }}</td>
                                            <td class="px-6 py-4">
                                                <div class="font-medium">{{ $item->service_tasks }}</div>
                                                @if ($item->odometer)
                                                    <div class="text-xs text-slate-500">Odometer:
                                                        {{ number_format($item->odometer) }} mi</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">{{ $item->vendor_mechanic }}</td>
                                            <td class="px-6 py-4">${{ number_format($item->cost, 2) }}</td>
                                            <td class="px-6 py-4">
                                                <div
                                                    class="{{ !$item->status && $item->isOverdue() ? 'text-danger' : (!$item->status && $item->isUpcoming() ? 'text-warning' : '') }}">
                                                    {{ $item->next_service_date->format('d/m/Y') }}
                                                    @if (!$item->status && $item->isOverdue())
                                                        <div class="flex items-center text-xs text-danger mt-1">
                                                            <x-base.lucide class="h-3 w-3 mr-1" icon="AlertTriangle" />
                                                            Overdue
                                                            ({{ (int) abs($item->next_service_date->diffInDays(now())) }}
                                                            days)
                                                        </div>
                                                    @elseif(!$item->status && $item->isUpcoming())
                                                        <div class="flex items-center text-xs text-warning mt-1">
                                                            <x-base.lucide class="h-3 w-3 mr-1" icon="Clock" />
                                                            In {{ (int) abs($item->next_service_date->diffInDays(now())) }}
                                                            days
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if ($item->status)
                                                    <div class="flex items-center text-success">
                                                        <x-base.lucide class="h-4 w-4 mr-1" icon="CheckCircle" />
                                                        Completed
                                                    </div>
                                                @else
                                                    <div
                                                        class="flex items-center {{ $item->isOverdue() ? 'text-danger' : 'text-warning' }}">
                                                        <x-base.lucide class="h-4 w-4 mr-1"
                                                            icon="{{ $item->isOverdue() ? 'AlertCircle' : 'Clock' }}" />
                                                        {{ $item->isOverdue() ? 'Overdue' : 'Pending' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="{{ route('admin.vehicles.maintenances.show', [$vehicle->id, $item->id]) }}"
                                                        class="btn btn-sm btn-primary p-1" title="View details">
                                                        <x-base.lucide class="h-4 w-4" icon="Eye" />
                                                    </a>

                                                    <form
                                                        action="{{ route('admin.vehicles.vehicle-maintenances.toggle-status', [$vehicle->id, $item->id]) }}"
                                                        method="POST" class="inline-block">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit"
                                                            class="btn btn-sm {{ $item->status ? 'btn-warning' : 'btn-success' }} p-1"
                                                            title="{{ $item->status ? 'Mark as pending' : 'Mark as completed' }}">
                                                            <x-base.lucide class="h-4 w-4"
                                                                icon="{{ $item->status ? 'RotateCcw' : 'Check' }}" />
                                                        </button>
                                                    </form>

                                                    @if (!$item->status && $item->isUpcoming())
                                                        <form
                                                            action="{{ route('admin.maintenance-notifications.send-test') }}"
                                                            method="POST" class="inline-block">
                                                            @csrf
                                                            <input type="hidden" name="maintenance_id"
                                                                value="{{ $item->id }}">
                                                            <input type="hidden" name="days"
                                                                value="{{ (int) abs($item->next_service_date->diffInDays(now())) }}">
                                                            <button type="submit" class="btn btn-sm btn-warning p-1"
                                                                title="Send reminder notification">
                                                                <x-base.lucide class="h-4 w-4" icon="Bell" />
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <button type="button"
                                                        class="btn btn-sm btn-danger p-1 delete-service-btn"
                                                        data-service-id="{{ $item->id }}"
                                                        data-vehicle-id="{{ $vehicle->id }}" data-tw-toggle="modal"
                                                        data-tw-target="#delete-service-modal" title="Delete">
                                                        <x-base.lucide class="h-4 w-4" icon="Trash" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="flex flex-col items-center justify-center py-4">
                                                    <x-base.lucide class="h-10 w-10 text-slate-300" icon="ClipboardX" />
                                                    <p class="mt-2 text-slate-500">There are no maintenance records for
                                                        this vehicle</p>
                                                    <x-base.button as="a" data-tw-toggle="modal"
                                                        data-tw-target="#add-service-modal" class="mt-3" size="sm"
                                                        variant="outline-primary">
                                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Plus" />
                                                        Register First Maintenance
                                                    </x-base.button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Maintenance statistics section -->
                        @if ($vehicle->maintenances->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium flex items-center">
                                        <svg class="mr-2 h-8 w-8 stroke-[1.3]" fill="#03045E"  version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 340.28 340.28" xml:space="preserve" stroke="#03045E" stroke-width="0.00340279"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M329.922,196.825l-19.937-11.511c0.538-4.986,0.821-10.048,0.821-15.175s-0.283-10.189-0.821-15.175l19.937-11.511 c4.261-2.46,7.308-6.433,8.582-11.187c1.272-4.754,0.618-9.719-1.842-13.979l-38.354-66.436c-3.283-5.685-9.402-9.216-15.974-9.216 c-3.216,0-6.393,0.855-9.192,2.472l-19.994,11.543c-8.126-5.959-16.912-11.068-26.227-15.195V18.423 C226.92,8.265,218.653,0,208.497,0h-76.715c-10.158,0-18.422,8.265-18.422,18.423v23.033c-9.316,4.127-18.102,9.235-26.229,15.196 L67.137,45.108c-2.799-1.616-5.977-2.471-9.191-2.471c-6.572,0-12.691,3.53-15.975,9.215L3.618,118.288 c-2.461,4.26-3.115,9.226-1.842,13.979c1.274,4.754,4.321,8.727,8.582,11.187l19.937,11.511 c-0.538,4.986-0.821,10.048-0.821,15.175s0.283,10.188,0.821,15.175l-19.937,11.511c-4.261,2.46-7.308,6.433-8.582,11.187 c-1.273,4.754-0.619,9.72,1.842,13.979l38.353,66.436c3.283,5.685,9.402,9.215,15.975,9.215c3.215,0,6.393-0.854,9.191-2.471 l19.994-11.543c8.127,5.96,16.913,11.069,26.229,15.196v23.034c0,4.92,1.916,9.546,5.396,13.025 c3.481,3.479,8.106,5.396,13.025,5.396h76.715c10.156,0,18.423-8.265,18.423-18.422v-23.035 c9.315-4.126,18.102-9.235,26.227-15.195l19.994,11.543c2.799,1.617,5.977,2.471,9.192,2.471c6.571,0,12.69-3.53,15.974-9.215 l38.354-66.436c2.46-4.26,3.114-9.226,1.842-13.979C337.229,203.258,334.182,199.285,329.922,196.825z M170.139,270.14 c-55.229,0-100-44.773-100-100s44.771-100,100-100c55.23,0,100,44.773,100,100S225.37,270.14,170.139,270.14z"></path> <path d="M239.083,117.796c-0.591-0.15-1.218,0.022-1.649,0.454l-18.058,18.114c-1.083-0.44-3.814-1.939-8.868-6.976 c-5.053-5.037-6.56-7.763-7.003-8.845l18.056-18.113c0.431-0.432,0.602-1.06,0.45-1.65c-0.152-0.591-0.605-1.058-1.191-1.228 c-2.604-0.756-5.302-1.139-8.02-1.139c-7.674,0-14.885,2.993-20.302,8.427c-5.408,5.425-8.38,12.63-8.37,20.289 c0.006,3.947,0.81,7.768,2.324,11.288l-21.887,21.888l-22.416-22.416c-0.02-0.038-0.034-0.079-0.056-0.117l-10.251-17.567 c-0.177-0.303-0.417-0.563-0.706-0.764l-17.285-11.994c-0.955-0.663-2.249-0.546-3.07,0.275l-7.703,7.702 c-0.822,0.822-0.938,2.115-0.275,3.071l11.993,17.285c0.2,0.288,0.461,0.529,0.764,0.706l17.571,10.248 c0.034,0.02,0.071,0.026,0.105,0.044l22.428,22.427l-16.868,16.868c-3.532-1.518-7.367-2.322-11.325-2.322 c-7.676,0-14.887,2.993-20.305,8.428c-7.398,7.418-10.158,18.279-7.202,28.343c0.172,0.585,0.64,1.037,1.232,1.187 c0.591,0.151,1.218-0.021,1.649-0.455l18.056-18.112c1.083,0.439,3.814,1.939,8.868,6.975c5.053,5.038,6.559,7.763,7,8.845 l-18.056,18.114c-0.431,0.432-0.602,1.06-0.449,1.65c0.152,0.591,0.605,1.058,1.191,1.228c2.603,0.755,5.301,1.138,8.018,1.138 c7.675,0,14.886-2.993,20.305-8.428c5.408-5.424,8.379-12.629,8.368-20.288c-0.006-3.947-0.81-7.769-2.324-11.288l16.863-16.863 l7.146,7.145l-4.872,4.872c-1.062,1.06-1.647,2.468-1.647,3.966c-0.001,1.499,0.584,2.908,1.645,3.967l34.413,34.412 c1.057,1.06,2.464,1.643,3.963,1.643c1.498,0,2.909-0.583,3.97-1.643l18.641-18.645c1.06-1.059,1.644-2.468,1.645-3.967 c0-1.499-0.584-2.907-1.644-3.966L199.535,177.6c-1.059-1.059-2.467-1.643-3.966-1.643c-1.499,0-2.908,0.584-3.967,1.645 l-4.869,4.869l-7.146-7.145l21.891-21.892c3.532,1.519,7.366,2.322,11.325,2.323c0.001,0,0.001,0,0.002,0 c7.674,0,14.885-2.994,20.302-8.43c7.397-7.415,10.158-18.275,7.206-28.342C240.143,118.398,239.675,117.946,239.083,117.796z M195.188,189.842c1.226-1.228,3.215-1.228,4.443,0l22.074,22.076c1.228,1.226,1.228,3.214,0,4.441 c-1.228,1.225-3.213,1.225-4.439,0l-22.078-22.076C193.967,193.055,193.967,191.067,195.188,189.842z M185.195,199.829 c1.23-1.224,3.221-1.224,4.448,0.002l22.075,22.077c1.225,1.225,1.226,3.214,0,4.439c-1.23,1.227-3.219,1.227-4.442,0 l-22.081-22.077C183.972,203.046,183.972,201.058,185.195,199.829z"></path> </g> </g></svg>
                                        {{ $vehicle->maintenances->count() }}
                                    </div>
                                    <div class="text-slate-500 text-sm">Total Maintenances</div>
                                </div>
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium text-success flex items-center">
                                        <svg class="mr-2 h-8 w-8 stroke-[1.3]" fill="#0d9488" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg" stroke="#228000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M960 1807.059c-467.125 0-847.059-379.934-847.059-847.059 0-467.125 379.934-847.059 847.059-847.059 467.125 0 847.059 379.934 847.059 847.059 0 467.125-379.934 847.059-847.059 847.059M960 0C430.645 0 0 430.645 0 960s430.645 960 960 960 960-430.645 960-960S1489.355 0 960 0M854.344 1157.975 583.059 886.69l-79.85 79.85 351.135 351.133L1454.4 717.617l-79.85-79.85-520.206 520.208Z" fill-rule="evenodd"></path> </g></svg>
                                        {{ $vehicle->maintenances->where('status', true)->count() }}
                                    </div>
                                    <div class="text-slate-500 text-sm">Completed</div>
                                </div>
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium text-warning flex items-center">
                                        <svg class="mr-2 h-8 w-8 stroke-[1.3]" viewBox="0 0 30 30" id="Layer_1" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#0d9488"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path class="st8" d="M15,4C8.9,4,4,8.9,4,15s4.9,11,11,11s11-4.9,11-11S21.1,4,15,4z M21.7,16.8c-0.1,0.4-0.5,0.6-0.9,0.5l-5.6-1.1 c-0.2,0-0.4-0.2-0.6-0.3C14.2,15.7,14,15.4,14,15c0,0,0,0,0,0l0.2-8c0-0.5,0.4-0.8,0.8-0.8c0.4,0,0.8,0.4,0.8,0.8l0.1,6.9l5.2,1.8 C21.6,15.8,21.8,16.3,21.7,16.8z"></path></g></svg>
                                        {{ $vehicle->maintenances->where('status', false)->count() }}
                                    </div>
                                    <div class="text-slate-500 text-sm">Pending</div>
                                </div>
                                <div class="box bg-slate-50 p-4 rounded">
                                    <div class="text-xl font-medium flex items-center">
                                        <svg class="mr-2 h-8 w-8 stroke-[1.3]" viewBox="0 0 24 24" role="img" xmlns="http://www.w3.org/2000/svg" aria-labelledby="dolarIconTitle" stroke="#03045E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" color="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title id="dolarIconTitle">Dolar</title> <path d="M12 4L12 6M12 18L12 20M15.5 8C15.1666667 6.66666667 14 6 12 6 9 6 8.5 7.95652174 8.5 9 8.5 13.140327 15.5 10.9649412 15.5 15 15.5 16.0434783 15 18 12 18 10 18 8.83333333 17.3333333 8.5 16"></path> </g></svg>
                                        ${{ number_format($vehicle->maintenances->sum('cost'), 2) }}
                                    </div>
                                    <div class="text-slate-500 text-sm">Total Expense</div>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <x-base.dialog id="delete-service-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="XCircle" />
                <div class="mt-5 text-2xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this service record? <br>
                    This process cannot be undone.
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <form id="delete-service-form" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-24" type="submit" variant="danger">
                        Delete
                    </x-base.button>
                </form>
            </div>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modificar el Modal para agregar servicio para incluir el campo status -->
    <x-base.dialog id="add-service-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">
                    Add New Maintenance Service
                </h2>
            </x-base.dialog.title>
            <form action="{{ route('admin.vehicles.maintenances.store', $vehicle->id) }}" method="POST"
                enctype="multipart/form-data" x-data="{ serviceStatus: false }">
                @csrf
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Primera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="service_date">Date of Service</x-base.form-label>
                        <x-base.form-input id="service_date" name="service_date" type="date" required
                            value="{{ date('Y-m-d') }}" />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="next_service_date">Next Service Date</x-base.form-label>
                        <x-base.form-input id="next_service_date" name="next_service_date" type="date" required
                            value="{{ date('Y-m-d', strtotime('+3 months')) }}" />
                        <div id="date-error" class="text-danger text-xs mt-1 hidden">
                            The date of the next service must be after the date of service.
                        </div>
                    </div>

                    <!-- Segunda fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="unit">Unit</x-base.form-label>
                        <x-base.form-input id="unit" name="unit" type="text" placeholder="Unit number"
                            value="{{ $vehicle->company_unit_number }}" required readonly />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="service_tasks">Service Tasks</x-base.form-label>
                        <x-base.form-input id="service_tasks" name="service_tasks" type="text"
                            placeholder="Ej: Oil change, brake inspection" required />
                    </div>

                    <!-- Tercera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="vendor_mechanic">Supplier/Mechanic</x-base.form-label>
                        <x-base.form-input id="vendor_mechanic" name="vendor_mechanic" type="text"
                            placeholder="Ex: Mechanic workshop" required />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="cost">Cost ($)</x-base.form-label>
                        <x-base.form-input id="cost" name="cost" type="number" step="0.01" min="0"
                            placeholder="0.00" required />
                    </div>

                    <!-- Cuarta fila -->
                    <div class="col-span-12 sm:col-span-12">
                        <x-base.form-label for="odometer">Odometer Reading</x-base.form-label>
                        <x-base.form-input id="odometer" name="odometer" type="number" min="0"
                            placeholder="Ej: 50000" />
                    </div>
                    <div class="col-span-12 sm:col-span-12">
                        <x-base.form-label for="description">Description</x-base.form-label>
                        <x-base.form-textarea id="description" name="description"
                            placeholder="Additional service details" rows="3"></x-base.form-textarea>
                    </div>

                    <!-- Drag and drop para subir tickets de servicio -->
                    <div class="col-span-12">
                        <x-base.form-label>Service Tickets</x-base.form-label>
                        <div x-data="{
                            files: [],
                            isUploading: false,
                            isDragging: false,
                            progress: 0,
                            handleFileSelect(e) {
                                if (e.target.files.length) {
                                    this.addFiles(e.target.files);
                                }
                            },
                            addFiles(fileList) {
                                for (let i = 0; i < fileList.length; i++) {
                                    if (this.validateFile(fileList[i])) {
                                        this.files.push({
                                            file: fileList[i],
                                            name: fileList[i].name,
                                            size: this.formatFileSize(fileList[i].size),
                                            type: fileList[i].type,
                                            preview: this.createPreview(fileList[i])
                                        });
                                    }
                                }
                            },
                            validateFile(file) {
                                // Validar tipo de archivo (PDF, imágenes)
                                const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                                if (!validTypes.includes(file.type)) {
                                    alert('Tipo de archivo no permitido. Solo se permiten PDF e imágenes.');
                                    return false;
                                }
                                // Validar tamaño (max 10MB)
                                if (file.size > 10 * 1024 * 1024) {
                                    alert('El archivo es demasiado grande. Tamaño máximo: 10MB');
                                    return false;
                                }
                                return true;
                            },
                            formatFileSize(size) {
                                if (size < 1024) return size + ' bytes';
                                else if (size < 1024 * 1024) return (size / 1024).toFixed(2) + ' KB';
                                else return (size / (1024 * 1024)).toFixed(2) + ' MB';
                            },
                            createPreview(file) {
                                if (file.type.startsWith('image/')) {
                                    return URL.createObjectURL(file);
                                }
                                return null;
                            },
                            removeFile(index) {
                                this.files.splice(index, 1);
                            },
                            handleDrop(e) {
                                e.preventDefault();
                                this.isDragging = false;
                                if (e.dataTransfer.files.length) {
                                    this.addFiles(e.dataTransfer.files);
                                }
                            }
                        }" class="border-2 border-dashed rounded-lg p-6 text-center"
                            :class="{ 'border-primary bg-primary/10': isDragging, 'border-slate-300': !isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                            @drop="handleDrop">
                            <div x-show="files.length === 0">
                                <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Upload" />
                                <p class="mt-2 text-sm text-slate-500">
                                   Drag & drop files here or
                                    <label class="relative cursor-pointer text-primary">
                                        <span>select files</span>
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect"
                                            name="maintenance_files[]">
                                    </label>
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    PDF, JPG, PNG (Máx. 10MB by file)
                                </p>
                            </div>

                            <div x-show="files.length > 0" class="mt-4">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="flex items-center justify-between p-2 border rounded mb-2 bg-white">
                                        <div class="flex items-center">
                                            <div class="mr-2">
                                                <template x-if="file.type === 'application/pdf'">
                                                    <x-base.lucide class="h-8 w-8 text-danger" icon="FileText" />
                                                </template>
                                                <template x-if="file.type.startsWith('image/')">
                                                    <img :src="file.preview" class="h-8 w-8 object-cover rounded" />
                                                </template>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-sm font-medium truncate" x-text="file.name"></p>
                                                <p class="text-xs text-slate-500" x-text="file.size"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="text-danger">
                                            <x-base.lucide class="h-5 w-5" icon="X" />
                                        </button>
                                    </div>
                                </template>

                                <button type="button" @click="files = []" class="btn btn-sm btn-outline-secondary mt-2">
                                    <x-base.lucide class="mr-1 h-4 w-4" icon="Trash" /> Limpiar todos
                                </button>

                                <div class="mt-2">
                                    <label class="relative cursor-pointer btn btn-sm btn-outline-primary">
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Plus" /> Agregar más archivos
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect"
                                            name="maintenance_files[]">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agregar campo de status -->
                    <div class="col-span-12">
                        <div class="form-check">
                            <input type="checkbox" id="status" name="status" value="1"
                                class="form-check-input" x-model="serviceStatus">
                            <label for="status" class="form-check-label">Mark as Completed</label>
                        </div>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="primary" id="submit-service">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Modificar el Modal para editar servicio para incluir el campo status -->
    <x-base.dialog id="edit-service-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">
                    Editar Servicio de Mantenimiento
                </h2>
            </x-base.dialog.title>
            <form id="edit-service-form" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <!-- Primera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_service_date">Fecha de Servicio</x-base.form-label>
                        <x-base.form-input id="edit_service_date" name="service_date" type="date" required />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_next_service_date">Fecha Próximo Servicio</x-base.form-label>
                        <x-base.form-input id="edit_next_service_date" name="next_service_date" type="date"
                            required />
                        <div id="edit-date-error" class="text-danger text-xs mt-1 hidden">
                            La fecha del próximo servicio debe ser posterior a la fecha de servicio.
                        </div>
                    </div>

                    <!-- Segunda fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_unit">Unidad</x-base.form-label>
                        <x-base.form-input id="edit_unit" name="unit" type="text" placeholder="Unit number"
                            required readonly />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_service_tasks">Tareas de Servicio</x-base.form-label>
                        <x-base.form-input id="edit_service_tasks" name="service_tasks" type="text"
                            placeholder="Ej: Oil change, brake inspection" required />
                    </div>

                    <!-- Tercera fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_vendor_mechanic">Proveedor/Mecánico</x-base.form-label>
                        <x-base.form-input id="edit_vendor_mechanic" name="vendor_mechanic" type="text"
                            placeholder="Ex: Mechanic workshop" required />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_cost">Costo ($)</x-base.form-label>
                        <x-base.form-input id="edit_cost" name="cost" type="number" step="0.01" min="0"
                            placeholder="0.00" required />
                    </div>

                    <!-- Cuarta fila -->
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_odometer">Lectura del Odómetro</x-base.form-label>
                        <x-base.form-input id="edit_odometer" name="odometer" type="number" min="0"
                            placeholder="Ej: 50000" />
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <x-base.form-label for="edit_description">Description</x-base.form-label>
                        <x-base.form-textarea id="edit_description" name="description"
                            placeholder="Additional service details" rows="3"></x-base.form-textarea>
                    </div>

                    <!-- Drag and drop para subir tickets de servicio (edición) -->
                    <div class="col-span-12">
                        <x-base.form-label>Service Tickets</x-base.form-label>
                        <div x-data="{
                            files: [],
                            isUploading: false,
                            isDragging: false,
                            progress: 0,
                            handleFileSelect(e) {
                                if (e.target.files.length) {
                                    this.addFiles(e.target.files);
                                }
                            },
                            addFiles(fileList) {
                                for (let i = 0; i < fileList.length; i++) {
                                    if (this.validateFile(fileList[i])) {
                                        this.files.push({
                                            file: fileList[i],
                                            name: fileList[i].name,
                                            size: this.formatFileSize(fileList[i].size),
                                            type: fileList[i].type,
                                            preview: this.createPreview(fileList[i])
                                        });
                                    }
                                }
                            },
                            validateFile(file) {
                                // Validar tipo de archivo (PDF, imágenes)
                                const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                                if (!validTypes.includes(file.type)) {
                                    alert('Tipo de archivo no permitido. Solo se permiten PDF e imágenes.');
                                    return false;
                                }
                                // Validar tamaño (max 10MB)
                                if (file.size > 10 * 1024 * 1024) {
                                    alert('El archivo es demasiado grande. Tamaño máximo: 10MB');
                                    return false;
                                }
                                return true;
                            },
                            formatFileSize(size) {
                                if (size < 1024) return size + ' bytes';
                                else if (size < 1024 * 1024) return (size / 1024).toFixed(2) + ' KB';
                                else return (size / (1024 * 1024)).toFixed(2) + ' MB';
                            },
                            createPreview(file) {
                                if (file.type.startsWith('image/')) {
                                    return URL.createObjectURL(file);
                                }
                                return null;
                            },
                            removeFile(index) {
                                this.files.splice(index, 1);
                            },
                            handleDrop(e) {
                                e.preventDefault();
                                this.isDragging = false;
                                if (e.dataTransfer.files.length) {
                                    this.addFiles(e.dataTransfer.files);
                                }
                            }
                        }" class="border-2 border-dashed rounded-lg p-6 text-center"
                            :class="{ 'border-primary bg-primary/10': isDragging, 'border-slate-300': !isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                            @drop="handleDrop">
                            <div x-show="files.length === 0">
                                <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Upload" />
                                <p class="mt-2 text-sm text-slate-500">
                                   Drag & drop files here or
                                    <label class="relative cursor-pointer text-primary">
                                        <span>select files</span>
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect"
                                            name="maintenance_files[]">
                                    </label>
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    PDF, JPG, PNG (Máx. 10MB by file)
                                </p>
                            </div>

                            <div x-show="files.length > 0" class="mt-4">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="flex items-center justify-between p-2 border rounded mb-2 bg-white">
                                        <div class="flex items-center">
                                            <div class="mr-2">
                                                <template x-if="file.type === 'application/pdf'">
                                                    <x-base.lucide class="h-8 w-8 text-danger" icon="FileText" />
                                                </template>
                                                <template x-if="file.type.startsWith('image/')">
                                                    <img :src="file.preview" class="h-8 w-8 object-cover rounded" />
                                                </template>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-sm font-medium truncate" x-text="file.name"></p>
                                                <p class="text-xs text-slate-500" x-text="file.size"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="text-danger">
                                            <x-base.lucide class="h-5 w-5" icon="X" />
                                        </button>
                                    </div>
                                </template>

                                <button type="button" @click="files = []" class="btn btn-sm btn-outline-secondary mt-2">
                                    <x-base.lucide class="mr-1 h-4 w-4" icon="Trash" /> Limpiar todos
                                </button>

                                <div class="mt-2">
                                    <label class="relative cursor-pointer btn btn-sm btn-outline-primary">
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Plus" /> Agregar más archivos
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect"
                                            name="maintenance_files[]">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agregar campo de status -->
                    <div class="col-span-12">
                        <div class="form-check">
                            <input type="checkbox" id="edit_status" name="status" value="1"
                                class="form-check-input">
                            <label for="edit_status" class="form-check-label">Mark as Completed</label>
                        </div>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="primary" id="update-service">
                        Updated
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Filtrado de mantenimientos
                const filterButtons = document.querySelectorAll('.filter-btn');
                const maintenanceRows = document.querySelectorAll('.maintenance-row');

                // Establecer 'all' como filtro activo por defecto
                let activeFilter = 'all';
                document.querySelector('[data-filter="all"]').classList.add('active');

                filterButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Remover clase activa de todos los botones
                        filterButtons.forEach(btn => {
                            btn.classList.remove('active');
                            btn.classList.remove('btn-primary');

                            // Restaurar el estilo original del botón
                            if (btn.getAttribute('data-filter') === 'all') {
                                btn.classList.add('btn-outline-secondary');
                            } else if (btn.getAttribute('data-filter') === 'pending') {
                                btn.classList.add('btn-outline-warning');
                            } else if (btn.getAttribute('data-filter') === 'completed') {
                                btn.classList.add('btn-outline-success');
                            } else if (btn.getAttribute('data-filter') === 'overdue') {
                                btn.classList.add('btn-outline-danger');
                            }
                        });

                        // Agregar clase activa al botón seleccionado y cambiar estilo
                        this.classList.add('active');
                        this.classList.remove('btn-outline-secondary');
                        this.classList.remove('btn-outline-warning');
                        this.classList.remove('btn-outline-success');
                        this.classList.remove('btn-outline-danger');
                        this.classList.add('btn-primary');

                        activeFilter = this.getAttribute('data-filter');

                        // Mostrar/ocultar filas según el filtro
                        maintenanceRows.forEach(row => {
                            if (activeFilter === 'all') {
                                row.style.display = '';
                            } else if (activeFilter === 'pending' && row.classList.contains(
                                    'pending')) {
                                row.style.display = '';
                            } else if (activeFilter === 'completed' && row.classList.contains(
                                    'completed')) {
                                row.style.display = '';
                            } else if (activeFilter === 'overdue' && row.classList.contains(
                                    'overdue')) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                });

                // Estilo para el botón de filtro activo
                if (document.querySelector('.filter-btn.active')) {
                    document.querySelector('.filter-btn.active').click();
                }

                // Configurar el modal de eliminación
                const deleteButtons = document.querySelectorAll('.delete-service-btn');
                const deleteForm = document.getElementById('delete-service-form');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const serviceId = this.getAttribute('data-service-id');
                        const vehicleId = this.getAttribute('data-vehicle-id');
                        const deleteUrl = `/admin/vehicles/${vehicleId}/maintenances/${serviceId}`;

                        console.log('Configurando eliminación:', deleteUrl);
                        deleteForm.action = deleteUrl;
                    });
                });
            });
        </script>
    @endpush
@endsection
