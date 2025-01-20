@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard EF Services ')


@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('carrier.dashboard')],
        ['label' => 'Dashboard', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="box box--stacked flex flex-col p-1.5">
                <div class="relative h-48 w-full rounded-[0.6rem] bg-gradient-to-b from-theme-1/95 to-theme-2/95">
                    <div @class([
                        'w-full h-full relative overflow-hidden',
                        "before:content-[''] before:absolute before:inset-0 before:bg-texture-white before:-mt-[50rem]",
                        "after:content-[''] after:absolute after:inset-0 after:bg-texture-white after:-mt-[50rem]",
                    ])></div>
                    <div class="absolute inset-x-0 top-0 mx-auto mt-24 h-32 w-32">
                        <div class="box image-fit h-full w-full overflow-hidden rounded-full border-[6px] border-white">
                            <img src="{{ $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/assets/images/placeholders/200x200.jpg') }}"
                                alt="{{ $carrier->name }}">
                        </div>
                    </div>
                </div>
                <div class="rounded-[0.6rem] bg-slate-50 pb-6 pt-12">
                    <div class="flex items-center justify-center text-xl font-medium">
                        {{ $carrier->name }}
                        <x-base.lucide class="ml-2 h-5 w-5 fill-blue-500/30 text-blue-500" icon="BadgeCheck" />
                    </div>
                    <div class="mt-2.5 flex flex-col items-center justify-center gap-x-5 gap-y-2 sm:flex-row">
                        <div class="flex items-center text-slate-500">
                            <x-base.lucide class="mr-1.5 h-3.5 w-3.5 stroke-[1.3]" icon="Building" />
                            {{ $carrier->address }}
                        </div>
                        <div class="flex items-center text-slate-500">
                            <x-base.lucide class="mr-1.5 h-3.5 w-3.5 stroke-[1.3]" icon="MapPin" />
                            <a href="">{{ $carrier->state }}</a>
                        </div>
                        <div class="flex items-center text-slate-500">
                            <x-base.lucide class="mr-1.5 h-3.5 w-3.5 stroke-[1.3]" icon="Phone" />
                            {{ $carrierDetail->phone }}
                        </div>
                    </div>
                </div>
            </div>
            <x-base.tab.group class="mt-10">
                <div class="flex flex-col gap-y-3 2xl:flex-row 2xl:items-center">
                    <x-base.tab.list
                        class="box mr-auto w-full flex-col rounded-[0.6rem] border-slate-200 bg-white sm:flex-row 2xl:w-auto"
                        variant="boxed-tabs">
                        <!-- Tab Perfil -->
                        <x-base.tab
                            class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current"
                            id="profile-tab" selected="{{ is_null(request()->query('page')) }}">
                            <x-base.tab.button
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40"
                                as="button">
                                Company Profile
                            </x-base.tab.button>
                        </x-base.tab>
        
                        <!-- Tab Usuarios -->
                        <x-base.tab class="bg-slate-50 [&[aria-selected='true']_button]:text-current" id="users-tab"
                            selected="{{ request()->query('page') === 'users' }}">
                            <x-base.tab.button
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40"
                                as="button">
                                Users
                                <span
                                    class="ml-2 flex h-5 items-center justify-center rounded-full border border-theme-1/10 bg-theme-1/10 px-1.5 text-xs font-medium text-theme-1/70">
                                    {{ $userCarriers->count() }}
                                </span>
                            </x-base.tab.button>
                        </x-base.tab>
        
                        <!-- Tab Documentos -->
                        <x-base.tab class="bg-slate-50 [&[aria-selected='true']_button]:text-current" id="documents-tab"
                            selected="{{ request()->query('page') === 'documents' }}">
                            <x-base.tab.button
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40"
                                as="button">
                                Documents
                                <span
                                    class="ml-2 flex h-5 items-center justify-center rounded-full border border-warning/10 bg-warning/10 px-1.5 text-xs font-medium text-warning/70">
                                    {{ $pendingDocuments->count() }}
                                </span>
                            </x-base.tab.button>
                        </x-base.tab>
        
                        <!-- Tab Conductores -->
                        <x-base.tab class="bg-slate-50 [&[aria-selected='true']_button]:text-current" id="drivers-tab"
                            selected="{{ request()->query('page') === 'drivers' }}">
                            <x-base.tab.button
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40"
                                as="button">
                                Drivers
                                <span
                                    class="ml-2 flex h-5 items-center justify-center rounded-full border border-success/10 bg-success/10 px-1.5 text-xs font-medium text-success/70">
                                    5
                                </span>
                            </x-base.tab.button>
                        </x-base.tab>
        
                        <!-- Tab Vehículos -->
                        <x-base.tab class="bg-slate-50 [&[aria-selected='true']_button]:text-current" id="vehicles-tab"
                            selected="{{ request()->query('page') === 'vehicles' }}">
                            <x-base.tab.button
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40"
                                as="button">
                                Vehicles
                                <span
                                    class="ml-2 flex h-5 items-center justify-center rounded-full border border-primary/10 bg-primary/10 px-1.5 text-xs font-medium text-primary/70">
                                    3
                                </span>
                            </x-base.tab.button>
                        </x-base.tab>
                    </x-base.tab.list>
        
                    <!-- Botones de acción -->
                    <div class="flex items-center gap-3 2xl:ml-auto">
                        <!-- Botón de crear nuevo -->
                        <x-base.button class="rounded-[0.6rem] bg-primary py-3" variant="primary">
                            <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Plus" />
                            Add New
                        </x-base.button>
                    </div>
                </div>
        
                <!-- Contenido de los tabs -->
                <x-base.tab.panels>
                    <!-- Panel Perfil -->
                    <x-base.tab.panel id="profile-panel" selected="{{ is_null(request()->query('page')) }}">
                        <!-- Aquí va el contenido del perfil que ya teníamos -->
                        <div class="mt-5 grid grid-cols-12 gap-6">
                            <!-- ... contenido anterior ... -->
                        </div>
                    </x-base.tab.panel>
        
                    <!-- Panel Usuarios -->
                    <x-base.tab.panel id="users-panel" selected="{{ request()->query('page') === 'users' }}">
                        <div class="mt-5">
                            <div class="box box--stacked">
                                <!-- Tabla de usuarios -->
                                <div class="overflow-auto lg:overflow-visible">
                                    <table class="table table-report">
                                        <thead>
                                            <tr>
                                                <th class="whitespace-nowrap">User</th>
                                                <th class="whitespace-nowrap">Role</th>
                                                <th class="whitespace-nowrap">Phone</th>
                                                <th class="whitespace-nowrap">Status</th>
                                                <th class="text-center whitespace-nowrap">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($userCarriers as $userCarrier)
                                                <tr>
                                                    <td class="w-40">
                                                        <div class="flex items-center">
                                                            <div class="h-10 w-10 image-fit zoom-in">
                                                                <img class="rounded-full"
                                                                    src="{{ $userCarrier->user->profile_photo_url }}"
                                                                    alt="user photo">
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="font-medium whitespace-nowrap">
                                                                    {{ $userCarrier->user->name }}</div>
                                                                <div class="text-slate-500 text-xs whitespace-nowrap">
                                                                    {{ $userCarrier->user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $userCarrier->job_position }}</td>
                                                    <td>{{ $userCarrier->phone }}</td>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <div @class([
                                                                'flex items-center justify-center rounded px-2 py-1',
                                                                'bg-success/20 text-success' => $userCarrier->status === 1,
                                                                'bg-warning/20 text-warning' => $userCarrier->status === 2,
                                                                'bg-danger/20 text-danger' => $userCarrier->status === 0,
                                                            ])>
                                                                {{ $userCarrier->status_name }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="table-report__action w-56">
                                                        <div class="flex justify-center items-center">
                                                            <x-base.button class="mr-2" variant="primary">
                                                                <x-base.lucide class="w-4 h-4" icon="Edit" />
                                                            </x-base.button>
                                                            <x-base.button variant="danger">
                                                                <x-base.lucide class="w-4 h-4" icon="Trash" />
                                                            </x-base.button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </x-base.tab.panel>
        
                    <!-- Panel Documentos -->
                    <x-base.tab.panel id="documents-panel" selected="{{ request()->query('page') === 'documents' }}">
                        <div class="mt-5">
                            <div class="box box--stacked p-5">
                                <!-- Grid de documentos -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @foreach ($pendingDocuments as $document)
                                        <div class="box p-5 border rounded-lg">
                                            <div class="flex items-center">
                                                <x-base.lucide class="w-12 h-12 text-primary" icon="FileText" />
                                                <div class="ml-4">
                                                    <div class="font-medium">{{ $document->documentType->name }}</div>
                                                    <div @class([
                                                        'text-xs mt-0.5',
                                                        'text-success' => $document->status === 1,
                                                        'text-warning' => $document->status === 2,
                                                        'text-danger' => $document->status === 0,
                                                    ])>
                                                        {{ $document->status_name }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex mt-4">
                                                <x-base.button class="w-full" variant="primary">
                                                    <x-base.lucide class="w-4 h-4 mr-2" icon="Upload" />
                                                    Upload Document
                                                </x-base.button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-base.tab.panel>
        
                    <!-- Panel Conductores -->
                    <x-base.tab.panel id="drivers-panel" selected="{{ request()->query('page') === 'drivers' }}">
                        <div class="mt-5">
                            <div class="box box--stacked p-5">
                                <!-- Grid de conductores (datos estáticos) -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @foreach (range(1, 5) as $index)
                                        <div class="box p-5 border rounded-lg">
                                            <div class="flex items-center">
                                                <div class="w-16 h-16 image-fit rounded-full overflow-hidden">
                                                    <img src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}"
                                                        alt="Driver">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium">Driver {{ $index }}</div>
                                                    <div class="text-slate-500 text-xs mt-0.5">License:
                                                        DL-{{ str_pad($index, 6, '0', STR_PAD_LEFT) }}</div>
                                                </div>
                                            </div>
                                            <div class="mt-4 flex justify-between items-center">
                                                <div class="text-xs">
                                                    <div>Experience: {{ rand(1, 10) }} years</div>
                                                    <div class="mt-1">Status: Active</div>
                                                </div>
                                                <x-base.button variant="primary">
                                                    <x-base.lucide class="w-4 h-4" icon="Edit" />
                                                </x-base.button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-base.tab.panel>
        
                    <!-- Panel Vehículos -->
                    <x-base.tab.panel id="vehicles-panel" selected="{{ request()->query('page') === 'vehicles' }}">
                        <div class="mt-5">
                            <div class="box box--stacked p-5">
                                <!-- Grid de vehículos (datos estáticos) -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @foreach (range(1, 3) as $index)
                                        <div class="box p-5 border rounded-lg">
                                            <div class="flex items-center">
                                                <x-base.lucide class="w-12 h-12 text-primary" icon="Truck" />
                                                <div class="ml-4">
                                                    <div class="font-medium">Vehicle {{ $index }}</div>
                                                    <div class="text-slate-500 text-xs mt-0.5">
                                                        Plate: ABC-{{ str_pad($index, 3, '0', STR_PAD_LEFT) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <div class="grid grid-cols-2 gap-3 text-xs">
                                                    <div>Make: Freightliner</div>
                                                    <div>Model: {{ 2020 + $index }}</div>
                                                    <div>Type: Semi-Truck</div>
                                                    <div>Status: Active</div>
                                                </div>
                                                <div class="mt-4 flex justify-end">
                                                    <x-base.button variant="primary">
                                                        <x-base.lucide class="w-4 h-4" icon="Edit" />
                                                    </x-base.button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-base.tab.panel>
                </x-base.tab.panels>
            </x-base.tab.group>
            <!-- Profile Stats -->
            <div class="mt-5 grid grid-cols-12 gap-6">


                <!-- Stats Panel -->
                
                <div class="col-span-12 xl:col-span-4">
                    <!-- Documents Progress Box -->
                    <div class="box box--stacked flex flex-col p-5 mb-5">
                        <div class="pb-5 text-base font-medium">Documents Progress</div>
                        <div class="mt-2">
                            <div class="flex items-center">
                                <div class="mr-3 font-medium">{{ number_format($documentProgress, 1) }}%</div>
                                <div class="relative flex-1">
                                    <div class="h-2 w-full rounded-full bg-slate-200">
                                        <div class="h-full rounded-full bg-primary"
                                            style="width: {{ $documentProgress }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 flex items-center justify-between text-xs text-slate-500">
                                <div>{{ $uploadedDocuments }} Uploaded</div>
                                <div>{{ $totalDocuments }} Total Required</div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Stats Box -->
                    <div class="box box--stacked flex flex-col p-5">
                        <div class="text-base font-medium mb-5">Company Stats</div>
                        <div class="flex flex-col gap-4">
                            <!-- Membership -->
                            <div class="flex items-center justify-between">
                                <div class="text-slate-500">Current Plan</div>
                                <div class="flex items-center">
                                    <span class="font-medium">{{ $membership->name }}</span>
                                    <div class="ml-2 px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full">
                                        Active
                                    </div>
                                </div>
                            </div>

                            <!-- Users Count -->
                            <div class="flex items-center justify-between">
                                <div class="text-slate-500">Team Members</div>
                                <div class="flex items-center">
                                    <span class="font-medium">{{ $userCarriers->count() }}</span>
                                    <span class="text-slate-400 text-xs ml-1">/ {{ $membership->max_carrier }}</span>
                                </div>
                            </div>

                            <!-- Vehicles Stats -->
                            <div class="flex items-center justify-between">
                                <div class="text-slate-500">Vehicles</div>
                                <div class="flex items-center">
                                    <span class="font-medium">0</span>
                                    <span class="text-slate-400 text-xs ml-1">/ {{ $membership->max_vehicles }}</span>
                                </div>
                            </div>

                            <!-- Referral Token -->
                            <div class="mt-2 p-3 bg-slate-50 rounded-lg">
                                <div class="text-slate-500 text-xs mb-1">Referral Token</div>
                                <div class="flex items-center gap-2">
                                    <input type="text" class="form-control form-control-sm bg-white"
                                        value="{{ $carrier->referrer_token }}" readonly>
                                    <button class="btn btn-sm btn-primary"
                                        onclick="copyToClipboard('{{ $carrier->referrer_token }}')">
                                        <x-base.lucide icon="Copy" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Feed -->
                <div class="col-span-12 xl:col-span-8">
                    <div class="box box--stacked flex flex-col p-5">
                        <div class="border-b border-slate-200/60 pb-5">
                            <div class="flex items-center">
                                <div class="text-base font-medium">Activity Feed</div>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex flex-col gap-4">
                                <!-- Activity Items -->
                                @foreach (range(1, 5) as $i)
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-primary/10">
                                            <x-base.lucide icon="FileText" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm">
                                                <span class="font-medium">Document uploaded</span>
                                                <span class="text-slate-500">- MC Authority</span>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">
                                                {{ now()->subHours($i)->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


@endsection
