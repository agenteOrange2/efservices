<div class="mt-3.5">
    <!-- Mensajes de alerta -->
    @if (session()->has('message'))
        <div class="alert alert-success mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Información básica del conductor -->
    <div class="box box--stacked mb-5">
        <div class="box-header flex justify-between items-center p-5 border-b border-slate-200/60 bg-slate-50">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full overflow-hidden mr-3 bg-slate-100 flex items-center justify-center">
                    @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                        <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}" alt="Foto de perfil"
                            class="w-full h-full object-cover">
                    @else
                        <x-base.lucide class="h-6 w-6 text-slate-500" icon="User" />
                    @endif
                </div>
                <div>
                    <div class="text-lg font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                    <div class="flex items-center text-slate-500 text-sm">
                        <x-base.lucide class="h-4 w-4 mr-1" icon="Mail" />
                        {{ $driver->user->email }}
                        <span class="mx-2">|</span>
                        <x-base.lucide class="h-4 w-4 mr-1" icon="Phone" />
                        {{ $driver->phone }}
                    </div>
                </div>
            </div>

            <div class="flex items-center">
                @php
                    $status = $application->status ?? 'draft';
                    $statusClass = [
                        'draft' => 'text-slate-500 bg-slate-100',
                        'pending' => 'text-amber-500 bg-amber-100',
                        'approved' => 'text-success bg-success/20',
                        'rejected' => 'text-danger bg-danger/20',
                    ][$status];
                    $statusText = [
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ][$status];
                @endphp
                <div class="px-3 py-1 rounded-full {{ $statusClass }} mr-4">
                    <span class="text-sm font-medium">{{ $statusText }}</span>
                </div>

                <div class="text-sm">
                    Apply: {{ $driver->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <!-- Datos adicionales y barra de progreso -->
        <div class="p-5 grid grid-cols-1 gap-6">
            <div class="col-span-8">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-slate-50 p-4 rounded-lg">
                        <div class="text-sm text-slate-500">Carrier</div>
                        <div class="font-medium">{{ $driver->carrier->name ?? 'N/A' }}</div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-lg">
                        <div class="text-sm text-slate-500">Date Of Birthday</div>
                        <div class="font-medium">{{ $driver->date_of_birth->format('d/m/Y') }}</div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-lg">
                        <div class="text-sm text-slate-500">License</div>
                        <div class="font-medium">
                            @if ($driver->licenses->isNotEmpty())
                                {{ $driver->licenses->first()->license_number }}
                                ({{ $driver->licenses->first()->state_of_issue }})
                            @else
                                Not registered
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-4">
                <div class="bg-slate-50 p-4 rounded-lg">
                    <div class="text-sm font-medium mb-2">Application Progress</div>
                    <div class="flex items-center">
                        <div class="w-full bg-slate-200 rounded-full h-3 mr-4">
                            <div class="bg-primary h-3 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                        </div>
                        <div class="text-sm font-medium">{{ $completionPercentage }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal: Tabs y Contenido -->
    <div class="flex gap-5">
        <!-- Panel izquierdo: Tabs y datos de la solicitud -->
        <div class="w-4/5">
            <!-- Tabs de Navegación -->
            {{-- <div class="box box--stacked">
                <div class="nav-tabs">
                    <a href="javascript:;" 
                       class="nav-tabs__item {{ $currentTab === 'general' ? 'active' : '' }}" 
                       wire:click="changeTab('general')">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="User" />
                        <div>Información General</div>
                    </a>
                    <a href="javascript:;" 
                       class="nav-tabs__item {{ $currentTab === 'licenses' ? 'active' : '' }}" 
                       wire:click="changeTab('licenses')">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="CreditCard" />
                        <div>Licencias</div>
                    </a>
                    <a href="javascript:;" 
                       class="nav-tabs__item {{ $currentTab === 'medical' ? 'active' : '' }}" 
                       wire:click="changeTab('medical')">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="ActivitySquare" />
                        <div>Médico</div>
                    </a>
                    <a href="javascript:;" 
                       class="nav-tabs__item {{ $currentTab === 'training' ? 'active' : '' }}" 
                       wire:click="changeTab('training')">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="GraduationCap" />
                        <div>Capacitación</div>
                    </a>
                    <a href="javascript:;" 
                       class="nav-tabs__item {{ $currentTab === 'history' ? 'active' : '' }}" 
                       wire:click="changeTab('history')">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="History" />
                        <div>Historial</div>
                    </a>
                    <a href="javascript:;" 
                       class="nav-tabs__item {{ $currentTab === 'traffic' ? 'active' : '' }}" 
                       wire:click="changeTab('traffic')">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="AlertTriangle" />
                        <div>Infracciones</div>
                    </a>
                </div>
            </div> --}}

            <div class="flex flex-col gap-y-3 2xl:flex-row 2xl:items-center">
                <x-base.tab.list
                    class="box mr-auto w-full flex-col rounded-[0.6rem] border-slate-200 bg-white sm:flex-row 2xl:w-auto">
                    <x-base.tab
                        class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                        <x-base.tab.button
                            class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40 {{ $currentTab === 'general' ? 'active' : '' }}"
                            wire:click="changeTab('general')" as="button">
                            Profile
                        </x-base.tab.button>

                    </x-base.tab>
                    <x-base.tab
                        class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                        <x-base.tab.button
                            class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40 {{ $currentTab === 'licenses' ? 'active' : '' }}"
                            wire:click="changeTab('licenses')" as="button">
                            Licenses
                        </x-base.tab.button>
                    </x-base.tab>
                    <x-base.tab
                        class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                        <x-base.tab.button
                            class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40 {{ $currentTab === 'medical' ? 'active' : '' }}"
                            wire:click="changeTab('medical')" as="button">
                            Medical
                        </x-base.tab.button>
                    </x-base.tab>
                    <x-base.tab
                        class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                        <x-base.tab.button
                            class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40 {{ $currentTab === 'records' ? 'active' : '' }}"
                            wire:click="changeTab('records')" as="button">
                            Records
                        </x-base.tab.button>
                    </x-base.tab>
                    <x-base.tab
                        class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                        <x-base.tab.button
                            class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40 
                            {{ $currentTab === 'history' ? 'active' : '' }}"
                            wire:click="changeTab('history')" as="button">
                            History
                        </x-base.tab.button>
                    </x-base.tab>
                    <x-base.tab
                        class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                        <x-base.tab.button
                            class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40 {{ $currentTab === 'documents' ? 'active' : '' }}"
                            wire:click="changeTab('documents')" as="button">
                            Documents
                        </x-base.tab.button>
                    </x-base.tab>
                </x-base.tab.list>
            </div>

            <!-- Contenido de la pestaña seleccionada -->
            <div class="box box--stacked mt-5 p-5">
                <!-- Información General -->
                @if ($currentTab === 'general')
                    <div class="mb-5">
                        <h3 class="text-lg font-medium mb-4">DRIVER APPLICANT INFORMATION</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-slate-500">Applicant's Legal Name</div>
                                <div class="font-medium">{{ $driver->user->name }} {{ $driver->middle_name }}
                                    {{ $driver->last_name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-slate-500">Email</div>
                                <div class="font-medium">{{ $driver->user->email }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-slate-500">Phone</div>
                                <div class="font-medium">{{ $driver->phone }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-slate-500">Date of Birth</div>
                                <div class="font-medium">{{ $driver->date_of_birth->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Direcciones -->
                    <div class="mb-5 border-t pt-5">
                        <h3 class="text-lg font-medium mb-4">Address</h3>
                        @if ($driver->application && $driver->application->addresses->where('primary', true)->first())
                            @php
                                $address = $driver->application->addresses->where('primary', true)->first();
                            @endphp
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-slate-500">Address </div>
                                    <div class="font-medium">{{ $address->address_line1 }}</div>
                                    @if ($address->address_line2)
                                        <div class="text-sm">{{ $address->address_line2 }}</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">City, State, ZIP</div>
                                    <div class="font-medium">{{ $address->city }}, {{ $address->state }}
                                        {{ $address->zip_code }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Resident since</div>
                                    <div class="font-medium">{{ $address->from_date->format('m/Y') }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Time living at the address</div>
                                    <div class="font-medium">
                                        @php
                                            $fromDate = $address->from_date;
                                            $toDate = $address->to_date ?? now();
                                            $years = $fromDate->diffInYears($toDate);
                                            $months = $fromDate->copy()->addYears($years)->diffInMonths($toDate);
                                            echo $years > 0 ? $years . ' year(s) ' : '';
                                            echo $months > 0 ? $months . ' month(s)' : '';
                                        @endphp
                                    </div>
                                </div>
                            </div>

                            <!-- Direcciones previas -->
                            @if (!$address->lived_three_years && $driver->application->addresses->where('primary', false)->isNotEmpty())
                                <h3 class="text-lg font-medium mt-4 mb-4">Previous Addresses</h3>
                                @foreach ($driver->application->addresses->where('primary', false) as $prevAddress)
                                    <div class="bg-slate-50 p-3 rounded mb-2">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-sm text-slate-500">Address</div>
                                                <div class="font-medium">{{ $prevAddress->address_line1 }}</div>
                                                @if ($prevAddress->address_line2)
                                                    <div class="text-sm">{{ $prevAddress->address_line2 }}</div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">City, State, ZIP</div>
                                                <div class="font-medium">{{ $prevAddress->city }},
                                                    {{ $prevAddress->state }} {{ $prevAddress->zip_code }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Period of residence</div>
                                                <div class="font-medium">
                                                    {{ $prevAddress->from_date->format('m/Y') }} -
                                                    {{ $prevAddress->to_date ? $prevAddress->to_date->format('m/Y') : 'Presente' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="text-slate-500 italic">No address information recorded.</div>
                        @endif
                    </div>

                    <!-- Información de solicitud -->
                    @if ($driver->application && $driver->application->details)
                        <div class="mb-5 border-t pt-5">
                            <h3 class="text-lg font-medium mb-4">Application Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-slate-500">Requested position</div>
                                    <div class="font-medium">
                                        @php
                                            $details = $driver->application->details;
                                            $position = $details->applying_position;
                                            if ($position === 'other') {
                                                echo $details->applying_position_other;
                                            } else {
                                                echo ucfirst(str_replace('_', ' ', $position));
                                            }
                                        @endphp
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Preferred location</div>
                                    <div class="font-medium">{{ $details->applying_location }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Eligible to work in the U.S.</div>
                                    <div class="font-medium">{{ $details->eligible_to_work ? 'Yes' : 'No' }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Speaks English</div>
                                    <div class="font-medium">{{ $details->can_speak_english ? 'Yes' : 'No' }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Card TWIC</div>
                                    <div class="font-medium">
                                        @if ($details->has_twic_card)
                                            Yes, expires: {{ $details->twic_expiration_date->format('d/m/Y') }}
                                        @else
                                            No
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">How did you find out?</div>
                                    <div class="font-medium">
                                        @php
                                            $source = $details->how_did_hear;
                                            if ($source === 'other') {
                                                echo $details->how_did_hear_other;
                                            } elseif ($source === 'employee_referral') {
                                                echo 'Referred by employee: ' . $details->referral_employee_name;
                                            } else {
                                                echo ucfirst(str_replace('_', ' ', $source));
                                            }
                                        @endphp
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Licencias -->
                @if ($currentTab === 'licenses')
                    <div class="mb-5">
                        <h3 class="text-lg font-medium mb-4">Driver's License Information</h3>

                        @if ($driver->licenses->isNotEmpty())
                            @foreach ($driver->licenses as $license)
                                <div class="bg-slate-50 p-4 rounded-lg mb-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-sm text-slate-500">License Number</div>
                                            <div class="font-medium">{{ $license->license_number }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">State</div>
                                            <div class="font-medium">{{ $license->state_of_issue }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">Class</div>
                                            <div class="font-medium">{{ $license->license_class }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">Expiration</div>
                                            <div
                                                class="font-medium {{ $license->expiration_date < now() ? 'text-danger' : '' }}">
                                                {{ $license->expiration_date->format('d/m/Y') }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">Type</div>
                                            <div class="font-medium">{{ $license->is_cdl ? 'CDL' : 'No CDL' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">License Status</div>
                                            <div class="font-medium">{{ ucfirst($license->status) }}</div>
                                        </div>
                                    </div>

                                    @if ($license->is_cdl && $license->endorsements->isNotEmpty())
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <div class="text-sm text-slate-500 mb-1">Endorsements</div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($license->endorsements as $endorsement)
                                                    <span class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">
                                                        {{ $endorsement->code }}: {{ $endorsement->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Imágenes de la licencia -->
                                    <div class="mt-3 pt-3 border-t border-slate-200">
                                        <div class="text-sm text-slate-500 mb-2">License Images</div>
                                        <div class="flex gap-4">
                                            @if ($license->getFirstMediaUrl('license_front'))
                                                <div>
                                                    <div class="text-xs text-slate-500 mb-1">Front</div>
                                                    <a href="{{ $license->getFirstMediaUrl('license_front') }}"
                                                        target="_blank" class="block">
                                                        <img src="{{ $license->getFirstMediaUrl('license_front') }}"
                                                            alt="Frente de licencia"
                                                            class="h-32 border rounded object-contain bg-white">
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-danger text-sm">Front image not available</div>
                                            @endif

                                            @if ($license->getFirstMediaUrl('license_back'))
                                                <div>
                                                    <div class="text-xs text-slate-500 mb-1">Reverse</div>
                                                    <a href="{{ $license->getFirstMediaUrl('license_back') }}"
                                                        target="_blank" class="block">
                                                        <img src="{{ $license->getFirstMediaUrl('license_back') }}"
                                                            alt="Reverso de licencia"
                                                            class="h-32 border rounded object-contain bg-white">
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-danger text-sm">Reverse side image not available</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-slate-500 italic">No license information has been recorded.</div>
                        @endif

                        <!-- Experiencia de Conducción -->
                        @if ($driver->experiences->isNotEmpty())
                            <h3 class="text-lg font-medium mt-6 mb-4">Driving Experience</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100">
                                            <th class="border px-4 py-2 text-left">Equipment Type</th>
                                            <th class="border px-4 py-2 text-left">Years of Experience</th>
                                            <th class="border px-4 py-2 text-left">Total Miles Driven </th>
                                            <th class="border px-4 py-2 text-left">Requires CDL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($driver->experiences as $exp)
                                            <tr>
                                                <td class="border px-4 py-2">{{ $exp->equipment_type }}</td>
                                                <td class="border px-4 py-2">{{ $exp->years_experience }}</td>
                                                <td class="border px-4 py-2">{{ number_format($exp->miles_driven) }}
                                                </td>
                                                <td class="border px-4 py-2">{{ $exp->requires_cdl ? 'Yes' : 'No' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Información Médica -->
                @if ($currentTab === 'medical')
                    <div class="mb-5">
                        <h3 class="text-lg font-medium mb-4">Driver Medical Qualification</h3>

                        @if ($driver->medicalQualification)
                            @php $medical = $driver->medicalQualification; @endphp
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-slate-500">Medical Examiner Name</div>
                                    <div class="font-medium">{{ $medical->medical_examiner_name }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Medical Examiner Registry Number</div>
                                    <div class="font-medium">{{ $medical->medical_examiner_registry_number }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Medical Card Expiration Date</div>
                                    <div
                                        class="font-medium {{ $medical->medical_card_expiration_date < now() ? 'text-danger' : '' }}">
                                        {{ $medical->medical_card_expiration_date->format('d/m/Y') }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">SSN (last 4 digits)</div>
                                    <div class="font-medium">
                                        @if ($medical->social_security_number)
                                            XXX-XX-{{ substr($medical->social_security_number, -4) }}
                                        @else
                                            Not provided
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Estado del conductor -->
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                @if ($medical->is_suspended)
                                    <div class="bg-warning/20 p-3 rounded border border-warning/20">
                                        <div class="text-sm font-medium text-warning">Driver is Suspended</div>
                                        <div class="text-sm">From: {{ $medical->suspension_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                @endif

                                @if ($medical->is_terminated)
                                    <div class="bg-danger/20 p-3 rounded border border-danger/20">
                                        <div class="text-sm font-medium text-danger">Driver is Terminated</div>
                                        <div class="text-sm">From: {{ $medical->termination_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Tarjeta médica -->
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <div class="text-sm text-slate-500 mb-2">Medical Card</div>
                                @if ($medical->getFirstMediaUrl('medical_card'))
                                    <a href="{{ $medical->getFirstMediaUrl('medical_card') }}" target="_blank"
                                        class="block w-64">
                                        <img src="{{ $medical->getFirstMediaUrl('medical_card') }}"
                                            alt="Tarjeta médica" class="border rounded object-contain bg-white">
                                    </a>
                                @else
                                    <div class="text-danger text-sm">Medical card not uploaded</div>
                                @endif
                            </div>
                        @else
                            <div class="text-slate-500 italic">No medical information has been recorded.</div>
                        @endif
                    </div>
                @endif

                <!-- Capacitación -->
                @if ($currentTab === 'records')
                    <div class="mb-5">
                        <!-- Training Schools Section -->
                        <h3 class="text-lg font-medium mb-4">Commercial Driver Training Schools</h3>

                        @if ($driver->trainingSchools->isNotEmpty())
                            @foreach ($driver->trainingSchools as $school)
                                <div class="bg-slate-50 p-4 rounded-lg mb-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-sm text-slate-500">School Name</div>
                                            <div class="font-medium">{{ $school->school_name }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">Location</div>
                                            <div class="font-medium">{{ $school->city }}, {{ $school->state }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">Period</div>
                                            <div class="font-medium">
                                                {{ $school->date_start->format('m/Y') }} -
                                                {{ $school->date_end->format('m/Y') }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">Did you graduate?</div>
                                            <div class="font-medium">{{ $school->graduated ? 'Yes' : 'No' }}</div>
                                        </div>
                                    </div>

                                    <!-- Habilidades de capacitación -->
                                    @if ($school->training_skills && count($school->training_skills) > 0)
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <div class="text-sm text-slate-500 mb-1">Skills learned</div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($school->training_skills as $skill)
                                                    <span class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">
                                                        {{ ucfirst(str_replace('_', ' ', $skill)) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Certificados -->
                                    @if ($school->hasMedia('school_certificates'))
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <div class="text-sm text-slate-500 mb-2">School Certificates</div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($school->getMedia('school_certificates') as $certificate)
                                                    <a href="{{ $certificate->getUrl() }}" target="_blank"
                                                        class="block">
                                                        @if (strpos($certificate->mime_type, 'image/') === 0)
                                                            <img src="{{ $certificate->getUrl() }}" alt="Certificado"
                                                                class="h-24 border rounded object-contain bg-white">
                                                        @else
                                                            <div
                                                                class="h-24 w-24 border rounded flex items-center justify-center bg-white">
                                                                <x-base.lucide class="h-8 w-8 text-slate-400"
                                                                    icon="FileText" />
                                                            </div>
                                                        @endif
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-3 pt-3 border-t border-slate-200 text-warning">
                                            No certificates have been attached
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-slate-500 italic">No training schools are registered.</div>
                        @endif

                        <!-- Divider -->
                        <div class="border-t my-5"></div>

                        <!-- Traffic Convictions Section -->
                        <h3 class="text-lg font-medium mb-4">Traffic Violations</h3>

                        @if ($driver->trafficConvictions->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100">
                                            <th class="border px-4 py-2 text-left">Date</th>
                                            <th class="border px-4 py-2 text-left">Location</th>
                                            <th class="border px-4 py-2 text-left">Charge</th>
                                            <th class="border px-4 py-2 text-left">Penalty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($driver->trafficConvictions as $conviction)
                                            <tr>
                                                <td class="border px-4 py-2">
                                                    {{ $conviction->conviction_date->format('d/m/Y') }}</td>
                                                <td class="border px-4 py-2">{{ $conviction->location }}</td>
                                                <td class="border px-4 py-2">{{ $conviction->charge }}</td>
                                                <td class="border px-4 py-2">{{ $conviction->penalty }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-slate-500 italic mb-4">No traffic violations have been recorded.</div>
                        @endif

                        <!-- Divider -->
                        <div class="border-t my-5"></div>

                        <!-- Accidents Section -->
                        <h3 class="text-lg font-medium mb-4">Accident Record</h3>

                        @if ($driver->accidents->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($driver->accidents as $accident)
                                    <div class="bg-slate-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-sm text-slate-500">Accident Date</div>
                                                <div class="font-medium">
                                                    {{ $accident->accident_date->format('d/m/Y') }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Nature of Accident</div>
                                                <div class="font-medium">{{ $accident->nature_of_accident }}</div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 mt-2">
                                            @if ($accident->had_injuries)
                                                <div>
                                                    <div class="text-sm text-slate-500">Injuries</div>
                                                    <div class="font-medium text-warning">Yes,
                                                        {{ $accident->number_of_injuries }} person(s)</div>
                                                </div>
                                            @endif

                                            @if ($accident->had_fatalities)
                                                <div>
                                                    <div class="text-sm text-slate-500">Fatalities</div>
                                                    <div class="font-medium text-danger">Yes,
                                                        {{ $accident->number_of_fatalities }} person(s)</div>
                                                </div>
                                            @endif
                                        </div>

                                        @if ($accident->comments)
                                            <div class="mt-2">
                                                <div class="text-sm text-slate-500">Comments</div>
                                                <div class="text-sm">{{ $accident->comments }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 italic">No accidents have been recorded.</div>
                        @endif

                        <!-- FMCSR Data -->
                        @if ($driver->fmcsrData)
                            <h3 class="text-lg font-medium mb-4 mt-8">Datos FMCSR</h3>
                            <div class="bg-slate-50 p-4 rounded-lg">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm text-slate-500">Are you disqualified?</div>
                                        <div
                                            class="font-medium {{ $driver->fmcsrData->is_disqualified ? 'text-danger' : 'text-success' }}">
                                            {{ $driver->fmcsrData->is_disqualified ? 'Yes' : 'No' }}
                                        </div>
                                        @if ($driver->fmcsrData->is_disqualified && $driver->fmcsrData->disqualified_details)
                                            <div class="text-sm mt-1">{{ $driver->fmcsrData->disqualified_details }}
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-sm text-slate-500">License suspended?</div>
                                        <div
                                            class="font-medium {{ $driver->fmcsrData->is_license_suspended ? 'text-danger' : 'text-success' }}">
                                            {{ $driver->fmcsrData->is_license_suspended ? 'Yes' : 'No' }}
                                        </div>
                                        @if ($driver->fmcsrData->is_license_suspended && $driver->fmcsrData->suspension_details)
                                            <div class="text-sm mt-1">{{ $driver->fmcsrData->suspension_details }}
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-sm text-slate-500">License denied?</div>
                                        <div
                                            class="font-medium {{ $driver->fmcsrData->is_license_denied ? 'text-danger' : 'text-success' }}">
                                            {{ $driver->fmcsrData->is_license_denied ? 'Yes' : 'No' }}
                                        </div>
                                        @if ($driver->fmcsrData->is_license_denied && $driver->fmcsrData->denial_details)
                                            <div class="text-sm mt-1">{{ $driver->fmcsrData->denial_details }}</div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-sm text-slate-500">Positive drug test?</div>
                                        <div
                                            class="font-medium {{ $driver->fmcsrData->has_positive_drug_test ? 'text-danger' : 'text-success' }}">
                                            {{ $driver->fmcsrData->has_positive_drug_test ? 'Yes' : 'No' }}
                                        </div>
                                        @if ($driver->fmcsrData->has_positive_drug_test)
                                            <div class="text-sm mt-1">
                                                SAP: {{ $driver->fmcsrData->substance_abuse_professional }}
                                                @if ($driver->fmcsrData->sap_phone)
                                                    ({{ $driver->fmcsrData->sap_phone }})
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Historial de Empleo -->
                @if ($currentTab === 'history')
                    <div class="mb-5">
                        <h3 class="text-lg font-medium mb-4">Employment History</h3>

                        @if ($driver->employmentCompanies->isNotEmpty() || $driver->unemploymentPeriods->isNotEmpty())
                            <div class="flex items-center mb-3">
                                <div class="bg-slate-50 rounded-full px-3 py-1 text-sm">
                                    <span class="font-medium">Total:</span>
                                    @php
                                        $totalYears = 0;

                                        // Sumar años de empleo
                                        foreach ($driver->employmentCompanies as $company) {
                                            $fromDate = $company->employed_from;
                                            $toDate = $company->employed_to;
                                            $totalYears += $fromDate->diffInDays($toDate) / 365.25;
                                        }

                                        // Sumar periodos de desempleo
                                        foreach ($driver->unemploymentPeriods as $period) {
                                            $fromDate = $period->start_date;
                                            $toDate = $period->end_date;
                                            $totalYears += $fromDate->diffInDays($toDate) / 365.25;
                                        }

                                        echo number_format($totalYears, 1) . ' years';
                                    @endphp

                                    <span class="ml-2 {{ $totalYears >= 10 ? 'text-success' : 'text-danger' }}">
                                        {{ $totalYears >= 10 ? '✓ Meets requirement' : '✗ Does not meet 10-year requirement' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Línea de tiempo del historial -->
                            <div class="relative pb-10">
                                <!-- Línea vertical -->
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200"></div>

                                @php
                                    // Combinar empleos y desempleo en un solo array
                                    $historyItems = [];

                                    foreach ($driver->employmentCompanies as $company) {
                                        $historyItems[] = [
                                            'type' => 'employment',
                                            'entity' => $company,
                                            'start_date' => $company->employed_from,
                                            'end_date' => $company->employed_to,
                                        ];
                                    }

                                    foreach ($driver->unemploymentPeriods as $period) {
                                        $historyItems[] = [
                                            'type' => 'unemployment',
                                            'entity' => $period,
                                            'start_date' => $period->start_date,
                                            'end_date' => $period->end_date,
                                        ];
                                    }

                                    // Ordenar por fecha de fin (más reciente primero)
                                    usort($historyItems, function ($a, $b) {
                                        return $b['end_date']->timestamp - $a['end_date']->timestamp;
                                    });
                                @endphp

                                @foreach ($historyItems as $item)
                                    <div class="relative ml-8 pl-6 pb-6">
                                        <!-- Punto en la línea temporal -->
                                        <div
                                            class="absolute left-[-24px] w-8 h-8 rounded-full flex items-center justify-center {{ $item['type'] === 'employment' ? 'bg-primary' : 'bg-amber-400' }}">
                                            <x-base.lucide class="h-4 w-4 text-white"
                                                icon="{{ $item['type'] === 'employment' ? 'Briefcase' : 'Clock' }}" />
                                        </div>

                                        <div class="bg-slate-50 p-4 rounded-lg">
                                            <!-- Periodo -->
                                            <div class="mb-2 text-sm text-slate-500">
                                                {{ $item['start_date']->format('d/m/Y') }} -
                                                {{ $item['end_date']->format('d/m/Y') }}
                                                <span class="ml-2">
                                                    ({{ $item['start_date']->diffForHumans($item['end_date'], ['parts' => 2]) }})
                                                </span>
                                            </div>

                                            @if ($item['type'] === 'employment')
                                                @php $company = $item['entity']; @endphp
                                                <div class="font-medium">
                                                    {{ $company->masterCompany ? $company->masterCompany->company_name : $company->company_name }}
                                                </div>
                                                <div class="text-sm">Posición: {{ $company->positions_held }}</div>
                                                @if ($company->masterCompany)
                                                    <div class="text-sm">{{ $company->masterCompany->city }},
                                                        {{ $company->masterCompany->state }}</div>
                                                @endif
                                                @if ($company->reason_for_leaving)
                                                    <div class="text-sm mt-1">
                                                        <span class="text-slate-500">Reason for leaving:</span>
                                                        {{ ucfirst($company->reason_for_leaving === 'other' ? $company->other_reason_description : $company->reason_for_leaving) }}
                                                    </div>
                                                @endif
                                            @else
                                                @php $period = $item['entity']; @endphp
                                                <div class="font-medium">Period of Unemployment</div>
                                                @if ($period->comments)
                                                    <div class="text-sm mt-1">{{ $period->comments }}</div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 italic">No se ha registrado historial de empleo.</div>
                        @endif
                    </div>
                @endif

                <!-- Infracciones y Accidentes -->

                <!-- Contenido para la pestaña de documentos -->
                @if ($currentTab === 'documents')
                    <div class="mb-5">
                        <h3 class="text-lg font-medium mb-4">Documents</h3>

                        @if (count($generatedPdfs) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($generatedPdfs as $key => $pdf)
                                    <div class="bg-slate-50 p-4 rounded-lg">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="font-medium">{{ $pdf['name'] }}</div>
                                            <a href="{{ $pdf['url'] }}" target="_blank"
                                                class="text-primary hover:text-primary-focus">
                                                <x-base.lucide class="h-5 w-5" icon="ExternalLink" />
                                            </a>
                                        </div>
                                        <div class="flex mt-2">
                                            <a href="{{ $pdf['url'] }}" target="_blank"
                                                class="px-3 py-1 bg-primary text-white rounded text-sm hover:bg-primary-focus">
                                                View Document
                                            </a>
                                            <a href="{{ $pdf['url'] }}" download
                                                class="px-3 py-1 bg-slate-200 text-slate-700 rounded text-sm hover:bg-slate-300 ml-2">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Botón para descargar todos los documentos -->
                            <div class="mt-4 flex justify-center">
                                <button type="button" wire:click="downloadAllDocuments" wire:loading.attr="disabled"
                                    class="flex items-center px-4 py-2 bg-primary text-white rounded hover:bg-primary-focus">
                                    <x-base.lucide class="mr-2 h-5 w-5" icon="Download" />
                                    <span wire:loading.remove wire:target="downloadAllDocuments">Download All
                                        Documents</span>
                                    <span wire:loading wire:target="downloadAllDocuments">Generating ZIP...</span>
                                </button>
                            </div>
                        @else
                            <div class="text-slate-500 italic">No PDF documents have been generated for this
                                application.
                            </div>
                        @endif

                        <!-- Sección para solicitar documentos adicionales -->
                        {{-- <div class="mt-6 pt-6 border-t border-slate-200">
                            <h4 class="font-medium mb-3">Request Additional Documentation</h4>
                
                            <div class="mb-4">
                                <div class="mb-2">Select the documents that the driver must provide:</div>
                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="requestedDocuments" value="identification"
                                            id="req_identification"
                                            class="form-checkbox h-4 w-4 text-primary rounded border-slate-300">
                                        <label for="req_identification" class="ml-2 text-sm">Identification with Photo</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="requestedDocuments" value="ssn"
                                            id="req_ssn"
                                            class="form-checkbox h-4 w-4 text-primary rounded border-slate-300">
                                        <label for="req_ssn" class="ml-2 text-sm">Social Security Card</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="requestedDocuments" value="license"
                                            id="req_license"
                                            class="form-checkbox h-4 w-4 text-primary rounded border-slate-300">
                                        <label for="req_license" class="ml-2 text-sm">Updated Driver's License</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="requestedDocuments" value="medical_card"
                                            id="req_medical_card"
                                            class="form-checkbox h-4 w-4 text-primary rounded border-slate-300">
                                        <label for="req_medical_card" class="ml-2 text-sm">Updated Medical Card</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="requestedDocuments" value="proof_address"
                                            id="req_proof_address"
                                            class="form-checkbox h-4 w-4 text-primary rounded border-slate-300">
                                        <label for="req_proof_address" class="ml-2 text-sm">Proof of Address</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="requestedDocuments" value="employment"
                                            id="req_employment"
                                            class="form-checkbox h-4 w-4 text-primary rounded border-slate-300">
                                        <label for="req_employment" class="ml-2 text-sm">Previous Employment Verification</label>
                                    </div>
                                </div>
                            </div>
                
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Additional Requirements:</label>
                                <textarea wire:model="additionalRequirements" rows="3"
                                    class="form-textarea w-full border-slate-200 rounded-md text-sm"
                                    placeholder="Describe any additional information or documents needed from the driver..."></textarea>
                            </div>
                
                            <button type="button" wire:click="requestAdditionalDocuments"
                                class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-600">
                                Request Additional Documents
                            </button>
                        </div> --}}
                    </div>
                @endif
            </div>
            <div class="box box--stacked mt-5 p-5">
                <h3 class="text-lg font-medium mb-4">Recruiter Notes</h3>

                <div class="mb-4">
                    <textarea wire:model="verificationNotes" rows="4" class="form-textarea w-full border-slate-200 rounded-md"
                        placeholder="Enter notes about the verification of this application..."></textarea>
                </div>

                <button type="button" wire:click="saveVerification"
                    class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-focus w-full">
                    Save Verification
                </button>

                @if ($savedVerification)
                    <div class="mt-4 p-3 bg-slate-50 rounded border border-slate-200 text-sm">
                        <div class="font-medium mb-1">Last verification:</div>
                        <div class="text-slate-600">{{ $savedVerification->verified_at->format('d/m/Y H:i') }}</div>
                        <div class="text-slate-600">By: {{ $savedVerification->verifier->name }}</div>
                        @if ($savedVerification->notes)
                            <div class="mt-2 p-2 bg-white rounded">
                                <div class="font-medium text-xs text-slate-500">Notes:</div>
                                <div>{{ $savedVerification->notes }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Panel derecho: Checklist y acciones -->
        <div class="w-2/5">
            <div class="box box--stacked p-5">
                <h3 class="text-lg font-medium mb-4">Verification Checklist</h3>

                <div class="space-y-3 mb-6">
                    @foreach ($checklistItems as $key => $item)
                        <div class="flex items-center">
                            <input type="checkbox" id="check_{{ $key }}"
                                class="form-checkbox h-5 w-5 text-primary rounded border-slate-300"
                                wire:click="toggleChecklistItem('{{ $key }}')"
                                {{ $item['checked'] ? 'checked' : '' }}>
                            <label for="check_{{ $key }}"
                                class="ml-2 text-sm">{{ $item['label'] }}</label>
                        </div>
                    @endforeach
                </div>

                @error('checklist')
                    <div class="text-danger text-sm mb-4">{{ $message }}</div>
                @enderror

                <!-- Actions based on application status -->
                @if ($application->status === 'pending')
                    <div class="flex flex-col gap-3">
                        <button type="button"
                            class="btn btn-success w-full {{ $this->isChecklistComplete() ? '' : 'opacity-50 cursor-not-allowed' }}"
                            {{ $this->isChecklistComplete() ? '' : 'disabled' }} wire:click="approveApplication">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="CheckCircle" />
                            Approve Application
                        </button>

                        <button type="button" class="btn btn-outline-danger w-full" data-tw-toggle="modal"
                            data-tw-target="#reject-modal">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="XCircle" />
                            Reject Application
                        </button>
                    </div>
                @elseif($application->status === 'approved')
                    <div class="bg-success/20 p-4 rounded border border-success/40 mb-4">
                        <div class="flex items-center">
                            <x-base.lucide class="h-5 w-5 text-success mr-2" icon="CheckCircle" />
                            <div class="text-success font-medium">Application Approved</div>
                        </div>
                        <div class="text-sm mt-1">
                            Approved on
                            {{ is_string($application->completed_at)
                                ? \Carbon\Carbon::parse($application->completed_at)->format('d/m/Y H:i')
                                : $application->completed_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @elseif($application->status === 'rejected')
                    <div class="bg-danger/20 p-4 rounded border border-danger/40 mb-4">
                        <div class="flex items-center">
                            <x-base.lucide class="h-5 w-5 text-danger mr-2" icon="XCircle" />
                            <div class="text-danger font-medium">Application Rejected</div>
                        </div>
                        <div class="text-sm mt-1">
                            Rejected on {{ $application->completed_at->format('d/m/Y H:i') }}
                        </div>
                        @if ($application->rejection_reason)
                            <div class="mt-2 p-2 bg-white rounded text-sm">
                                <div class="font-medium">Reason for rejection:</div>
                                <div>{{ $application->rejection_reason }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-6 pt-6 border-t border-slate-200">
                    <h3 class="text-lg font-medium mb-4">Steps Status</h3>

                    <div class="space-y-2">
                        @foreach ($stepsStatus as $step => $status)
                            @php
                                $stepNames = [
                                    1 => 'General Information',
                                    2 => 'Licenses',
                                    3 => 'Medical',
                                    4 => 'Training',
                                    5 => 'Traffic',
                                    6 => 'Accident',
                                    7 => 'FMCSR',
                                    8 => 'Work History',
                                    9 => 'Company Policies',
                                    10 => 'Criminal History',
                                    11 => 'Application Certification',
                                ];

                                $statusColors = [
                                    'completed' => 'bg-success/20 text-success border-success/20',
                                    'pending' => 'bg-amber-50 text-amber-500 border-amber-100',
                                    'missing' => 'bg-danger/10 text-danger border-danger/10',
                                ];

                                $statusIcons = [
                                    'completed' => 'CheckCircle',
                                    'pending' => 'AlertCircle',
                                    'missing' => 'XCircle',
                                ];
                            @endphp

                            <div class="flex items-center p-2 rounded border {{ $statusColors[$status] }}">
                                <x-base.lucide class="h-4 w-4 mr-2" icon="{{ $statusIcons[$status] }}" />
                                <div class="text-sm">{{ $stepNames[$step] ?? "Step {$step}" }}</div>
                                <div class="ml-auto text-xs capitalize">{{ $status }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Modal de rechazo -->
    <x-base.dialog id="reject-modal" size="md">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">
                    Reject Driver Application
                </h2>
            </x-base.dialog.title>
            <form wire:submit.prevent="rejectApplication">
                <x-base.dialog.description>
                    <div class="mt-2 mb-4">
                        <x-base.form-label for="rejectionReason">Reason for Rejection</x-base.form-label>
                        <x-base.form-textarea id="rejectionReason" wire:model="rejectionReason" rows="4"
                            placeholder="Explain the reason why this application is being rejected..."></x-base.form-textarea>
                        @error('rejectionReason')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded p-3 text-sm text-amber-600">
                        <x-base.lucide class="h-4 w-4 inline-block mr-1" icon="AlertTriangle" />
                        This action will send a notification to the driver informing them that their application has
                        been
                        rejected.
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button"
                        variant="outline-secondary">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="danger">
                        Reject
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>
</div>
