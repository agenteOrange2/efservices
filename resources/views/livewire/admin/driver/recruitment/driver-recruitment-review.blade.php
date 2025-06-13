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
                        <svg class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            stroke="#9a9a9a" stroke-width="0.00024000000000000003">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M20 4C21.6569 4 23 5.34315 23 7V17C23 18.6569 21.6569 20 20 20H4C2.34315 20 1 18.6569 1 17V7C1 5.34315 2.34315 4 4 4H20ZM19.2529 6H4.74718L11.3804 11.2367C11.7437 11.5236 12.2563 11.5236 12.6197 11.2367L19.2529 6ZM3 7.1688V17C3 17.5523 3.44772 18 4 18H20C20.5523 18 21 17.5523 21 17V7.16882L13.8589 12.8065C12.769 13.667 11.231 13.667 10.1411 12.8065L3 7.1688Z"
                                    fill="#9a9a9a"></path>
                            </g>
                        </svg>
                        {{ $driver->user->email }}
                        <span class="mx-2">|</span>
                        <svg class="h-5 w-5 mr-1" viewBox="0 0 24.00 24.00" fill="none"
                            xmlns="http://www.w3.org/2000/svg" stroke="#9a9a9a" stroke-width="0.00024000000000000003">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M16.1007 13.359L15.5719 12.8272H15.5719L16.1007 13.359ZM16.5562 12.9062L17.085 13.438H17.085L16.5562 12.9062ZM18.9728 12.5894L18.6146 13.2483L18.9728 12.5894ZM20.8833 13.628L20.5251 14.2869L20.8833 13.628ZM21.4217 16.883L21.9505 17.4148L21.4217 16.883ZM20.0011 18.2954L19.4723 17.7636L20.0011 18.2954ZM18.6763 18.9651L18.7459 19.7119H18.7459L18.6763 18.9651ZM8.81536 14.7266L9.34418 14.1947L8.81536 14.7266ZM4.00289 5.74561L3.2541 5.78816L3.2541 5.78816L4.00289 5.74561ZM10.4775 7.19738L11.0063 7.72922H11.0063L10.4775 7.19738ZM10.6342 4.54348L11.2346 4.09401L10.6342 4.54348ZM9.37326 2.85908L8.77286 3.30855V3.30855L9.37326 2.85908ZM6.26145 2.57483L6.79027 3.10667H6.79027L6.26145 2.57483ZM4.69185 4.13552L4.16303 3.60368H4.16303L4.69185 4.13552ZM12.0631 11.4972L12.5919 10.9654L12.0631 11.4972ZM16.6295 13.8909L17.085 13.438L16.0273 12.3743L15.5719 12.8272L16.6295 13.8909ZM18.6146 13.2483L20.5251 14.2869L21.2415 12.9691L19.331 11.9305L18.6146 13.2483ZM20.8929 16.3511L19.4723 17.7636L20.5299 18.8273L21.9505 17.4148L20.8929 16.3511ZM18.6067 18.2184C17.1568 18.3535 13.4056 18.2331 9.34418 14.1947L8.28654 15.2584C12.7186 19.6653 16.9369 19.8805 18.7459 19.7119L18.6067 18.2184ZM9.34418 14.1947C5.4728 10.3453 4.83151 7.10765 4.75168 5.70305L3.2541 5.78816C3.35456 7.55599 4.14863 11.144 8.28654 15.2584L9.34418 14.1947ZM10.7195 8.01441L11.0063 7.72922L9.9487 6.66555L9.66189 6.95073L10.7195 8.01441ZM11.2346 4.09401L9.97365 2.40961L8.77286 3.30855L10.0338 4.99296L11.2346 4.09401ZM5.73263 2.04299L4.16303 3.60368L5.22067 4.66736L6.79027 3.10667L5.73263 2.04299ZM10.1907 7.48257C9.66189 6.95073 9.66117 6.95144 9.66045 6.95216C9.66021 6.9524 9.65949 6.95313 9.659 6.95362C9.65802 6.95461 9.65702 6.95561 9.65601 6.95664C9.65398 6.95871 9.65188 6.96086 9.64972 6.9631C9.64539 6.96759 9.64081 6.97245 9.63599 6.97769C9.62634 6.98816 9.61575 7.00014 9.60441 7.01367C9.58174 7.04072 9.55605 7.07403 9.52905 7.11388C9.47492 7.19377 9.41594 7.2994 9.36589 7.43224C9.26376 7.70329 9.20901 8.0606 9.27765 8.50305C9.41189 9.36833 10.0078 10.5113 11.5343 12.0291L12.5919 10.9654C11.1634 9.54499 10.8231 8.68059 10.7599 8.27309C10.7298 8.07916 10.761 7.98371 10.7696 7.96111C10.7748 7.94713 10.7773 7.9457 10.7709 7.95525C10.7677 7.95992 10.7624 7.96723 10.7541 7.97708C10.75 7.98201 10.7451 7.98759 10.7394 7.99381C10.7365 7.99692 10.7335 8.00019 10.7301 8.00362C10.7285 8.00534 10.7268 8.00709 10.725 8.00889C10.7241 8.00979 10.7232 8.0107 10.7223 8.01162C10.7219 8.01208 10.7212 8.01278 10.7209 8.01301C10.7202 8.01371 10.7195 8.01441 10.1907 7.48257ZM11.5343 12.0291C13.0613 13.5474 14.2096 14.1383 15.0763 14.2713C15.5192 14.3392 15.8763 14.285 16.1472 14.1841C16.28 14.1346 16.3858 14.0763 16.4658 14.0227C16.5058 13.9959 16.5392 13.9704 16.5663 13.9479C16.5799 13.9367 16.5919 13.9262 16.6024 13.9166C16.6077 13.9118 16.6126 13.9073 16.6171 13.903C16.6194 13.9008 16.6215 13.8987 16.6236 13.8967C16.6246 13.8957 16.6256 13.8947 16.6266 13.8937C16.6271 13.8932 16.6279 13.8925 16.6281 13.8923C16.6288 13.8916 16.6295 13.8909 16.1007 13.359C15.5719 12.8272 15.5726 12.8265 15.5733 12.8258C15.5735 12.8256 15.5742 12.8249 15.5747 12.8244C15.5756 12.8235 15.5765 12.8226 15.5774 12.8217C15.5793 12.82 15.581 12.8183 15.5827 12.8166C15.5862 12.8133 15.5895 12.8103 15.5926 12.8074C15.5988 12.8018 15.6044 12.7969 15.6094 12.7929C15.6192 12.7847 15.6265 12.7795 15.631 12.7764C15.6403 12.7702 15.6384 12.773 15.6236 12.7785C15.5991 12.7876 15.501 12.8189 15.3038 12.7886C14.8905 12.7253 14.02 12.3853 12.5919 10.9654L11.5343 12.0291ZM9.97365 2.40961C8.95434 1.04802 6.94996 0.83257 5.73263 2.04299L6.79027 3.10667C7.32195 2.578 8.26623 2.63181 8.77286 3.30855L9.97365 2.40961ZM4.75168 5.70305C4.73201 5.35694 4.89075 4.9954 5.22067 4.66736L4.16303 3.60368C3.62571 4.13795 3.20329 4.89425 3.2541 5.78816L4.75168 5.70305ZM19.4723 17.7636C19.1975 18.0369 18.9029 18.1908 18.6067 18.2184L18.7459 19.7119C19.4805 19.6434 20.0824 19.2723 20.5299 18.8273L19.4723 17.7636ZM11.0063 7.72922C11.9908 6.7503 12.064 5.2019 11.2346 4.09401L10.0338 4.99295C10.4373 5.53193 10.3773 6.23938 9.9487 6.66555L11.0063 7.72922ZM20.5251 14.2869C21.3429 14.7315 21.4703 15.7769 20.8929 16.3511L21.9505 17.4148C23.2908 16.0821 22.8775 13.8584 21.2415 12.9691L20.5251 14.2869ZM17.085 13.438C17.469 13.0562 18.0871 12.9616 18.6146 13.2483L19.331 11.9305C18.2474 11.3414 16.9026 11.5041 16.0273 12.3743L17.085 13.438Z"
                                    fill="#9a9a9a"></path>
                            </g>
                        </svg>
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
                    Apply: {{ $driver->created_at->format('m/d/Y') }}
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
                        <div class="font-medium">{{ $driver->date_of_birth->format('m/d/Y') }}</div>
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
            <div class="flex flex-col gap-y-3 2xl:flex-row 2xl:items-center">
                <!-- Navegación simple con botones en lugar de tabs -->
                <div class="flex flex-wrap border rounded-lg bg-white overflow-hidden">
                    <button type="button"
                        class="px-4 py-3 text-sm font-medium {{ $currentTab === 'general' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        wire:click="changeTab('general')">
                        Profile
                    </button>
                    <button type="button"
                        class="px-4 py-3 text-sm font-medium {{ $currentTab === 'licenses' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        wire:click="changeTab('licenses')">
                        Licenses
                    </button>
                    <button type="button"
                        class="px-4 py-3 text-sm font-medium {{ $currentTab === 'medical' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        wire:click="changeTab('medical')">
                        Medical
                    </button>
                    <button type="button"
                        class="px-4 py-3 text-sm font-medium {{ $currentTab === 'records' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        wire:click="changeTab('records')">
                        Records
                    </button>
                    <button type="button"
                        class="px-4 py-3 text-sm font-medium {{ $currentTab === 'training' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        wire:click="changeTab('training')">
                        Training
                    </button>
                    <button type="button"
                        class="px-4 py-3 text-sm font-medium {{ $currentTab === 'history' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        wire:click="changeTab('history')">
                        History
                    </button>
                    <button type="button"
                        class="px-4 py-3 text-sm font-medium {{ $currentTab === 'documents' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        wire:click="changeTab('documents')">
                        Documents
                    </button>
                </div>
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
                                <div class="font-medium">{{ $driver->date_of_birth->format('m/d/Y') }}</div>
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
                                    <div class="font-medium">{{ $address->from_date->format('m/d/Y') }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Time living at the address</div>
                                    <div class="font-medium">
                                        @php
                                            $fromDate = $address->from_date;
                                            $toDate = $address->to_date ?? now();
                                            $years = (int) $fromDate->diffInYears($toDate);
                                            $months = (int) $fromDate->copy()->addYears($years)->diffInMonths($toDate);
                                            echo $years > 0 ? $years . ' year(s) ' : '';
                                            echo $months > 0 ? $months . ' month(s)' : '';
                                            echo $years == 0 && $months == 0 ? 'Less than a month' : '';
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
                                                    {{ $prevAddress->to_date ? $prevAddress->to_date->format('m/Y') : 'Present' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500 mt-1">Time living at this address
                                                </div>
                                                <div class="font-medium">
                                                    @php
                                                        $fromDate = $prevAddress->from_date;
                                                        $toDate = $prevAddress->to_date ?? now();
                                                        $years = (int) $fromDate->diffInYears($toDate);
                                                        $months = (int) $fromDate
                                                            ->copy()
                                                            ->addYears($years)
                                                            ->diffInMonths($toDate);
                                                        echo $years > 0 ? $years . ' year(s) ' : '';
                                                        echo $months > 0 ? $months . ' month(s)' : '';
                                                        echo $years == 0 && $months == 0 ? 'Less than a month' : '';
                                                    @endphp
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
                                            Yes, expires: {{ $details->twic_expiration_date->format('m/d/Y') }}
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
                                                {{ $license->expiration_date->format('m/d/Y') }}
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
                                            <div>
                                                <div class="text-xs text-slate-500 mb-1">Front</div>
                                                @if ($license->getFirstMediaUrl('license_front'))
                                                    <div class="relative group">
                                                        <a href="{{ $license->getFirstMediaUrl('license_front') }}"
                                                            target="_blank" class="block">
                                                            <img src="{{ $license->getFirstMediaUrl('license_front') }}"
                                                                alt="Frente de licencia"
                                                                class="h-32 border rounded object-contain bg-white">
                                                        </a>
                                                        <button
                                                            wire:click="editLicenseFrontImage({{ $license->id }})"
                                                            class="absolute bottom-2 right-2 bg-primary text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-md"
                                                            title="Update front license image">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" class="w-4 h-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21h-9.5A2.25 2.25 0 014 18.75V8.25A2.25 2.25 0 016.25 6H8" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @else
                                                    <div
                                                        class="flex flex-col items-center border border-dashed border-slate-300 rounded p-4 bg-slate-50">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="w-8 h-8 text-slate-400 mb-2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                                        </svg>
                                                        <button
                                                            wire:click="editLicenseFrontImage({{ $license->id }})"
                                                            class="text-sm text-primary hover:underline">
                                                            Upload Front Image
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>

                                            <div>
                                                <div class="text-xs text-slate-500 mb-1">Reverse</div>
                                                @if ($license->getFirstMediaUrl('license_back'))
                                                    <div class="relative group">
                                                        <a href="{{ $license->getFirstMediaUrl('license_back') }}"
                                                            target="_blank" class="block">
                                                            <img src="{{ $license->getFirstMediaUrl('license_back') }}"
                                                                alt="Reverso de licencia"
                                                                class="h-32 border rounded object-contain bg-white">
                                                        </a>
                                                        <button wire:click="editLicenseBackImage({{ $license->id }})"
                                                            class="absolute bottom-2 right-2 bg-primary text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-md"
                                                            title="Update back license image">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" class="w-4 h-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21h-9.5A2.25 2.25 0 014 18.75V8.25A2.25 2.25 0 016.25 6H8" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @else
                                                    <div
                                                        class="flex flex-col items-center border border-dashed border-slate-300 rounded p-4 bg-slate-50">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="w-8 h-8 text-slate-400 mb-2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                                        </svg>
                                                        <button wire:click="editLicenseBackImage({{ $license->id }})"
                                                            class="text-sm text-primary hover:underline">
                                                            Upload Back Image
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
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

                        <!-- Record de Manejo -->
                        <h3 class="text-lg font-medium mt-6 mb-4">Driving Record</h3>
                        <div class="bg-slate-50 p-4 rounded-lg mb-4">
                            <div class="flex justify-between items-center mb-3">
                                <div class="text-sm font-medium">Driving Record Files (English)</div>
                                <button type="button" wire:click="editDrivingRecord" class="btn btn-sm btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Upload Driving Record
                                </button>
                            </div>

                            

                            <!-- Lista de archivos subidos -->
                            @if ($driver->getMedia('driving_records')->isNotEmpty())
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-3">
                                    @foreach ($driver->getMedia('driving_records') as $media)
                                        <div class="relative group border rounded p-2 bg-white">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5 text-slate-500 mr-2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                                <a href="{{ $media->getUrl() }}" target="_blank"
                                                    class="text-sm text-primary hover:underline truncate">
                                                    {{ $media->file_name }}
                                                </a>
                                            </div>
                                            <div
                                                class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button type="button"
                                                    wire:click="deleteDrivingRecord({{ $media->id }})"
                                                    wire:confirm="Are you sure you want to delete this document?"
                                                    class="text-red-500 hover:text-red-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-slate-500 italic">No driving record files uploaded.</div>
                            @endif
                        </div
                        
                            @if ($driver->getMedia('driving_records')->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                                    @foreach ($driver->getMedia('driving_records') as $media)
                                        <div class="border rounded-lg overflow-hidden bg-white shadow-sm">
                                            <div class="p-3 border-b bg-slate-50 flex justify-between items-center">
                                                <div class="font-medium truncate" title="{{ $media->file_name }}">
                                                    {{ $media->file_name }}
                                                </div>
                                                <button wire:click="deleteDrivingRecord({{ $media->id }})"
                                                    wire:confirm="Are you sure you want to delete this document?"
                                                    class="text-red-500 hover:text-red-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-3">                                                                                                
                                                <div class="text-xs text-slate-500 mb-1">Uploaded:</div>
                                                <div class="text-sm mb-3">
                                                    {{ Carbon\Carbon::parse($media->getCustomProperty('upload_date'))->format('M d, Y') }}
                                                </div>
                                                <a href="{{ $media->getUrl() }}" target="_blank"
                                                    class="block text-center text-sm px-3 py-1.5 bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors">
                                                    View Document
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-slate-500 italic">No driving record documents have been uploaded.</div>
                            @endif
                        

                        <!-- Criminal Record -->
                        <h3 class="text-lg font-medium mt-6 mb-4">Criminal Record</h3>
                        <div class="bg-slate-50 p-4 rounded-lg mb-4">
                            <div class="flex justify-between items-center mb-3">
                                <div class="text-sm font-medium">Criminal Record Files</div>
                                <button type="button" wire:click="editCriminalRecord"
                                    class="btn btn-sm btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Upload Criminal Record
                                </button>
                            </div>

                            <!-- Lista de archivos subidos -->
                            @if ($driver->getMedia('criminal_records')->isNotEmpty())
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-3">
                                    @foreach ($driver->getMedia('criminal_records') as $media)
                                        <div class="relative group border rounded p-2 bg-white">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5 text-slate-500 mr-2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                                <a href="{{ $media->getUrl() }}" target="_blank"
                                                    class="text-sm text-primary hover:underline truncate">
                                                    {{ $media->file_name }}
                                                </a>
                                            </div>
                                            <div
                                                class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button type="button"
                                                    wire:click="deleteCriminalRecord({{ $media->id }})"
                                                    wire:confirm="Are you sure you want to delete this document?"
                                                    class="text-red-500 hover:text-red-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-slate-500 italic">No criminal record files uploaded.</div>
                            @endif
                        </div>

                        <div class="mb-6">
                            @if ($driver->getMedia('criminal_records')->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                                    @foreach ($driver->getMedia('criminal_records') as $media)
                                        <div class="border rounded-lg overflow-hidden bg-white shadow-sm">
                                            <div class="p-3 border-b bg-slate-50 flex justify-between items-center">
                                                <div class="font-medium truncate" title="{{ $media->file_name }}">
                                                    {{ $media->file_name }}
                                                </div>
                                                <button wire:click="deleteCriminalRecord({{ $media->id }})"
                                                    wire:confirm="Are you sure you want to delete this document?"
                                                    class="text-red-500 hover:text-red-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-3">                                                                                                
                                                <div class="text-xs text-slate-500 mb-1">Uploaded:</div>
                                                <div class="text-sm mb-3">
                                                    {{ Carbon\Carbon::parse($media->getCustomProperty('upload_date'))->format('M d, Y') }}
                                                </div>
                                                <a href="{{ $media->getUrl() }}" target="_blank"
                                                    class="block text-center text-sm px-3 py-1.5 bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors">
                                                    View Document
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-slate-500 italic">No driving record documents have been uploaded.
                                </div>
                            @endif
                        </div>
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
                                        {{ $medical->medical_card_expiration_date->format('m/d/Y') }}
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
                                        <div class="text-sm">From: {{ $medical->suspension_date->format('m/d/Y') }}
                                        </div>
                                    </div>
                                @endif

                                @if ($medical->is_terminated)
                                    <div class="bg-danger/20 p-3 rounded border border-danger/20">
                                        <div class="text-sm font-medium text-danger">Driver is Terminated</div>
                                        <div class="text-sm">From: {{ $medical->termination_date->format('m/d/Y') }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Tarjeta médica -->
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <div class="text-sm text-slate-500 mb-2">Medical Card</div>
                                @if ($medical->getFirstMediaUrl('medical_card'))
                                    <div class="relative group">
                                        <a href="{{ $medical->getFirstMediaUrl('medical_card') }}" target="_blank"
                                            class="block">
                                            <img src="{{ $medical->getFirstMediaUrl('medical_card') }}"
                                                alt="Tarjeta médica"
                                                class="h-32 border rounded object-contain bg-white">
                                        </a>
                                        <button wire:click="editMedicalImage()"
                                            class="absolute bottom-2 right-2 bg-primary text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-md"
                                            title="Update medical card image">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21h-9.5A2.25 2.25 0 014 18.75V8.25A2.25 2.25 0 016.25 6H8" />
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div
                                        class="flex flex-col items-center border border-dashed border-slate-300 rounded p-4 bg-slate-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor"
                                            class="w-8 h-8 text-slate-400 mb-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>
                                        <button wire:click="editMedicalImage()"
                                            class="text-sm text-primary hover:underline">
                                            Upload Medical Card
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-slate-500 italic">No medical information has been recorded.</div>
                        @endif
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <!-- Medical Record -->
                        <h3 class="text-lg font-medium mt-6 mb-4">Medical Record</h3>
                        <div class="bg-slate-50 p-4 rounded-lg mb-4">
                            <div class="flex justify-between items-center mb-3">
                                <div class="text-sm font-medium">Medical Record Files</div>
                                <button type="button" wire:click="openMedicalRecordModal"
                                    class="btn btn-sm btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Upload Medical Record
                                </button>
                            </div>

                            <!-- Lista de archivos subidos -->
                            @if ($driver->getMedia('medical_records')->isNotEmpty())
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-3">
                                    @foreach ($driver->getMedia('medical_records') as $media)
                                        <div class="relative group border rounded p-2 bg-white">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5 text-slate-500 mr-2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                                <a href="{{ $media->getUrl() }}" target="_blank"
                                                    class="text-sm text-primary hover:underline truncate">
                                                    {{ $media->file_name }}
                                                </a>
                                            </div>
                                            <div
                                                class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button type="button"
                                                    wire:click="deleteMedicalRecord({{ $media->id }})"
                                                    wire:confirm="Are you sure you want to delete this document?"
                                                    class="text-red-500 hover:text-red-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-slate-500 italic">No medical record files uploaded.</div>
                            @endif
                        </div>

                    </div>
                @endif

                <!-- Capacitación -->
                @if ($currentTab === 'records')
                    <div class="mb-5">

                        <!-- Training Schools Section -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Commercial Driver Training Schools</h3>

                            <button type="button"
                                wire:click="$dispatch('openTrainingModal', { driverId: {{ $driver->id }} })"
                                class="bg-primary hover:bg-primary-dark text-white py-1 px-3 rounded text-sm flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Training
                            </button>
                        </div>

                        @if (isset($driver->trainingSchools) && $driver->trainingSchools->isNotEmpty())
                            @foreach ($driver->trainingSchools as $school)
                                <div class="bg-slate-50 p-4 rounded-lg mb-4 relative">
                                    <div class="absolute bottom-4 right-4 flex space-x-2">
                                        <a href="#"
                                            wire:click.prevent="$dispatch('openTrainingModal', { driverId: {{ $driver->id }}, trainingSchoolId: {{ $school->id }} })"
                                            class="text-slate-500 hover:text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>

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
                                                {{ $school->date_start ? $school->date_start->format('m/Y') : 'N/A' }}
                                                -
                                                {{ $school->date_end ? $school->date_end->format('m/Y') : 'N/A' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-slate-500">Did you graduate?</div>
                                            <div class="font-medium">{{ $school->graduated ? 'Yes' : 'No' }}</div>
                                        </div>
                                    </div>

                                    <!-- Habilidades de capacitación -->
                                    @php
                                        $trainingSkills = is_string($school->training_skills)
                                            ? json_decode($school->training_skills, true)
                                            : $school->training_skills;
                                    @endphp
                                    @if ($trainingSkills && is_array($trainingSkills) && count($trainingSkills) > 0)
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <div class="text-sm text-slate-500 mb-1">Skills learned</div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($trainingSkills as $skill)
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
                                                            <img src="{{ $certificate->getUrl() }}"
                                                                alt="Certificado"
                                                                class="h-24 border rounded object-contain bg-white">
                                                        @else
                                                            <div
                                                                class="h-24 w-24 border rounded flex items-center justify-center bg-white">
                                                                <svg class="h-5 w-5" viewBox="0 0 24 24"
                                                                    fill="currentColor">
                                                                    <path
                                                                        d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20M13 13V17H10V13H13Z">
                                                                    </path>
                                                                </svg>
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

                        <!-- Componente Modal para Agregar/Editar Escuelas de Capacitación -->
                        @livewire('admin.driver.driver-training-modal')

                        <!-- Divider -->
                        <div class="border-t my-5"></div>

                        <!-- Courses Section -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium">Courses</h3>
                            <button type="button"
                                wire:click="$dispatch('openDriverCourseModal', { driverId: {{ $driver->id }} })"
                                class="bg-primary hover:bg-primary-dark text-white py-1 px-3 rounded text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add Course
                            </button>
                        </div>

                        @if (isset($driver->courses) && $driver->courses->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($driver->courses as $course)
                                    <div class="bg-slate-50 p-4 rounded-lg">
                                        <!-- Botones de acción -->
                                        <div class="flex justify-end mb-2">
                                            <button type="button"
                                                wire:click="$dispatch('openDriverCourseModal', { driverId: {{ $driver->id }}, courseId: {{ $course->id }} })"
                                                class="text-slate-500 hover:text-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-sm text-slate-500">Organization Name</div>
                                                <div class="font-medium">{{ $course->organization_name }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Contact Phone</div>
                                                <div class="font-medium">{{ $course->phone }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Location</div>
                                                <div class="font-medium">{{ $course->city }}, {{ $course->state }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Certification Date</div>
                                                <div class="font-medium">
                                                    {{ $course->certification_date ? $course->certification_date->format('m/d/Y') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Expiration Date</div>
                                                <div class="font-medium">
                                                    {{ $course->expiration_date ? $course->expiration_date->format('m/d/Y') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Experience</div>
                                                <div class="font-medium">{{ $course->experience }}</div>
                                            </div>
                                            @if ($course->hasMedia('course_certificates'))
                                                <div class="col-span-2">
                                                    <div class="text-sm text-slate-500 mb-2">Certificates</div>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($course->getMedia('course_certificates') as $certificate)
                                                            <a href="{{ $certificate->getUrl() }}" target="_blank"
                                                                class="block">
                                                                @if (strpos($certificate->mime_type, 'image/') === 0)
                                                                    <img src="{{ $certificate->getUrl() }}"
                                                                        alt="Certificado"
                                                                        class="h-24 border rounded object-contain bg-white">
                                                                @else
                                                                    <div
                                                                        class="h-24 w-24 border rounded flex items-center justify-center bg-white">
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24"
                                                                            fill="currentColor">
                                                                            <path
                                                                                d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20M13 13V17H10V13H13Z">
                                                                            </path>
                                                                        </svg>
                                                                    </div>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 italic mb-4">No courses have been recorded.</div>
                        @endif

                        <!-- Componente Modal para Agregar/Editar Cursos -->
                        @livewire('admin.driver.recruitment.modal.driver-course-modal')

                        <!-- Divider -->
                        <div class="border-t my-5"></div>

                        <!-- Testing Section -->
                        <h3 class="text-lg font-medium mb-4">Testing</h3>

                        @if (isset($driver->testings) && $driver->testings->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($driver->testings as $test)
                                    <div class="bg-slate-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-sm text-slate-500">Test Type</div>
                                                <div class="font-medium">{{ $test->test_type }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Test Date</div>
                                                <div class="font-medium">
                                                    {{ $test->test_date ? $test->test_date->format('m/d/Y') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Test Result</div>
                                                <div class="font-medium">{{ $test->test_result }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Status</div>
                                                <div class="font-medium">{{ $test->status }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Administered By</div>
                                                <div class="font-medium">{{ $test->administered_by }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Requester</div>
                                                <div class="font-medium">{{ $test->requester_name }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Location</div>
                                                <div class="font-medium">{{ $test->location }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Scheduled Time</div>
                                                <div class="font-medium">
                                                    {{ $test->scheduled_time ? $test->scheduled_time->format('m/d/Y H:i') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Next Test Due</div>
                                                <div class="font-medium">
                                                    {{ $test->next_test_due ? $test->next_test_due->format('m/d/Y') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Bill To</div>
                                                <div class="font-medium">{{ $test->bill_to }}</div>
                                            </div>
                                            @if ($test->notes)
                                                <div class="col-span-2">
                                                    <div class="text-sm text-slate-500">Notes</div>
                                                    <div class="font-medium">{{ $test->notes }}</div>
                                                </div>
                                            @endif

                                            @if (
                                                $test->is_random_test ||
                                                    $test->is_post_accident_test ||
                                                    $test->is_reasonable_suspicion_test ||
                                                    $test->is_pre_employment_test ||
                                                    $test->is_follow_up_test ||
                                                    $test->is_return_to_duty_test ||
                                                    $test->is_other_reason_test)
                                                <div class="col-span-2 mt-2">
                                                    <div class="text-sm text-slate-500 mb-2">Test Reasons</div>
                                                    <div class="flex flex-wrap gap-2">
                                                        @if ($test->is_random_test)
                                                            <span
                                                                class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">Random
                                                                Test</span>
                                                        @endif
                                                        @if ($test->is_post_accident_test)
                                                            <span
                                                                class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">Post
                                                                Accident</span>
                                                        @endif
                                                        @if ($test->is_reasonable_suspicion_test)
                                                            <span
                                                                class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">Reasonable
                                                                Suspicion</span>
                                                        @endif
                                                        @if ($test->is_pre_employment_test)
                                                            <span
                                                                class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">Pre-Employment</span>
                                                        @endif
                                                        @if ($test->is_follow_up_test)
                                                            <span
                                                                class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">Follow-up</span>
                                                        @endif
                                                        @if ($test->is_return_to_duty_test)
                                                            <span
                                                                class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">Return
                                                                to Duty</span>
                                                        @endif
                                                        @if ($test->is_other_reason_test)
                                                            <span
                                                                class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">Other:
                                                                {{ $test->other_reason_description }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Documentos relacionados con el test -->
                                            @if ($test->hasMedia())
                                                <div class="col-span-2 mt-2">
                                                    <div class="text-sm text-slate-500 mb-2">Test Documents</div>
                                                    <div class="flex flex-wrap gap-3">
                                                        @if ($test->hasMedia('drug_test_pdf'))
                                                            <a href="{{ $test->getFirstMedia('drug_test_pdf')->getUrl() }}"
                                                                target="_blank"
                                                                class="flex items-center px-3 py-2 bg-slate-100 rounded hover:bg-slate-200">
                                                                <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24"
                                                                    fill="currentColor">
                                                                    <path
                                                                        d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20Z">
                                                                    </path>
                                                                </svg>
                                                                Drug Test Report
                                                            </a>
                                                        @endif
                                                        @if ($test->hasMedia('test_results'))
                                                            <a href="{{ $test->getFirstMedia('test_results')->getUrl() }}"
                                                                target="_blank"
                                                                class="flex items-center px-3 py-2 bg-slate-100 rounded hover:bg-slate-200">
                                                                <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24"
                                                                    fill="currentColor">
                                                                    <path
                                                                        d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20Z">
                                                                    </path>
                                                                </svg>
                                                                Test Results
                                                            </a>
                                                        @endif
                                                        @if ($test->hasMedia('test_certificates'))
                                                            <a href="{{ $test->getFirstMedia('test_certificates')->getUrl() }}"
                                                                target="_blank"
                                                                class="flex items-center px-3 py-2 bg-slate-100 rounded hover:bg-slate-200">
                                                                <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24"
                                                                    fill="currentColor">
                                                                    <path
                                                                        d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20Z">
                                                                    </path>
                                                                </svg>
                                                                Certificate
                                                            </a>
                                                        @endif
                                                        @if ($test->hasMedia('test_authorization'))
                                                            <a href="{{ $test->getFirstMedia('test_authorization')->getUrl() }}"
                                                                target="_blank"
                                                                class="flex items-center px-3 py-2 bg-slate-100 rounded hover:bg-slate-200">
                                                                <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24"
                                                                    fill="currentColor">
                                                                    <path
                                                                        d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20Z">
                                                                    </path>
                                                                </svg>
                                                                Authorization
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 italic mb-4">No tests have been recorded.</div>
                        @endif

                        <!-- Divider -->
                        <div class="border-t my-5"></div>

                        <!-- Inspections Section -->
                        <h3 class="text-lg font-medium mb-4">Inspections</h3>

                        @if (isset($driver->inspections) && $driver->inspections->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($driver->inspections as $inspection)
                                    <div class="bg-slate-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-sm text-slate-500">Inspection Date</div>
                                                <div class="font-medium">
                                                    {{ $inspection->inspection_date ? $inspection->inspection_date->format('m/d/Y') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Inspection Type</div>
                                                <div class="font-medium">{{ $inspection->inspection_type }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Inspector Name</div>
                                                <div class="font-medium">{{ $inspection->inspector_name }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Location</div>
                                                <div class="font-medium">{{ $inspection->location }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Status</div>
                                                <div class="font-medium">{{ $inspection->status }}</div>
                                            </div>
                                            @if ($inspection->vehicle)
                                                <div>
                                                    <div class="text-sm text-slate-500">Vehicle</div>
                                                    <div class="font-medium">
                                                        {{ $inspection->vehicle->name ?? $inspection->vehicle->make . ' ' . $inspection->vehicle->model }}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($inspection->defects_found)
                                                <div class="col-span-2">
                                                    <div class="text-sm text-slate-500">Defects Found</div>
                                                    <div class="font-medium">{{ $inspection->defects_found }}</div>
                                                </div>
                                            @endif
                                            @if ($inspection->corrective_actions)
                                                <div class="col-span-2">
                                                    <div class="text-sm text-slate-500">Corrective Actions</div>
                                                    <div class="font-medium">{{ $inspection->corrective_actions }}
                                                    </div>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm text-slate-500">Defects Corrected</div>
                                                <div class="font-medium">
                                                    {{ $inspection->is_defects_corrected ? 'Yes' : 'No' }}</div>
                                            </div>
                                            @if ($inspection->is_defects_corrected && $inspection->defects_corrected_date)
                                                <div>
                                                    <div class="text-sm text-slate-500">Correction Date</div>
                                                    <div class="font-medium">
                                                        {{ $inspection->defects_corrected_date->format('m/d/Y') }}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($inspection->corrected_by)
                                                <div>
                                                    <div class="text-sm text-slate-500">Corrected By</div>
                                                    <div class="font-medium">{{ $inspection->corrected_by }}</div>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm text-slate-500">Safe to Operate</div>
                                                <div
                                                    class="font-medium {{ $inspection->is_vehicle_safe_to_operate ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $inspection->is_vehicle_safe_to_operate ? 'Yes' : 'No' }}
                                                </div>
                                            </div>
                                            @if ($inspection->notes)
                                                <div class="col-span-2">
                                                    <div class="text-sm text-slate-500">Notes</div>
                                                    <div class="font-medium">{{ $inspection->notes }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 italic mb-4">No inspections have been recorded.</div>
                        @endif

                        <!-- Divider -->
                        <div class="border-t my-5"></div>


                        <!-- Traffic Convictions Section -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Traffic Convictions</h3>
                            <button type="button"
                                class="px-3 py-1.5 bg-primary text-white rounded-md text-sm flex items-center hover:bg-primary-focus"
                                wire:click="$dispatch('openTrafficModal', { driverId: {{ $driver->id }} })">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add Traffic Conviction
                            </button>
                        </div>

                        @if ($driver->trafficConvictions && $driver->trafficConvictions->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($driver->trafficConvictions as $traffic)
                                    <div class="bg-slate-50 p-4 rounded-lg">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="font-medium">
                                                {{ $traffic->conviction_date ? (is_string($traffic->conviction_date) ? $traffic->conviction_date : $traffic->conviction_date->format('m-d-Y')) : 'N/A' }}
                                                - {{ $traffic->location }}</div>
                                            <button type="button" class="text-slate-500 hover:text-primary"
                                                wire:click="$dispatch('openTrafficModal', { driverId: {{ $driver->id }}, trafficId: {{ $traffic->id }} })">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-sm text-slate-500">Conviction Date</div>
                                                <div class="font-medium">
                                                    {{ $traffic->conviction_date ? $traffic->conviction_date->format('m/d/Y') : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Location</div>
                                                <div class="font-medium">{{ $traffic->location }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Charge</div>
                                                <div class="font-medium">{{ $traffic->charge }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-500">Penalty</div>
                                                <div class="font-medium">{{ $traffic->penalty }}</div>
                                            </div>

                                        </div>

                                        @if (method_exists($traffic, 'getMedia') && $traffic->getMedia('traffic_images')->count() > 0)
                                            <div class="mt-3 pt-3 border-t border-slate-200">
                                                <div class="text-sm text-slate-500 mb-2">Documents</div>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($traffic->getMedia('traffic_images') as $document)
                                                        <a href="{{ $document->getUrl() }}" target="_blank"
                                                            class="block">
                                                            @if (Str::startsWith($document->mime_type, 'image/'))
                                                                <img src="{{ $document->getUrl() }}"
                                                                    alt="{{ $document->name }}"
                                                                    class="h-24 w-24 border rounded flex items-center justify-center bg-white">
                                                            @else
                                                                <div class="h-24 w-24 border rounded flex items-center justify-center bg-white"
                                                                    title="{{ $document->name }}">
                                                                    <div>
                                                                        <div
                                                                            class="h-24 w-24 border rounded flex items-center justify-center bg-white">
                                                                            <svg class="h-5 w-5" viewBox="0 0 24 24"
                                                                                fill="currentColor">
                                                                                <path
                                                                                    d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20M13 13V17H10V13H13Z">
                                                                                </path>
                                                                            </svg>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 italic mb-4">No traffic convictions have been registered.</div>
                        @endif

                        <!-- Divider -->
                        <div class="border-t my-5"></div>

                        <!-- Accidents Section -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Accident Record</h3>
                            <button type="button"
                                class="px-3 py-1.5 bg-primary text-white rounded-md text-sm flex items-center hover:bg-primary-focus"
                                wire:click="$dispatch('openAccidentModal', { driverId: {{ $driver->id }} })">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add Accident
                            </button>
                        </div>

                        @if ($driver->accidents->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($driver->accidents as $accident)
                                    <div class="bg-slate-50 p-4 rounded-lg">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="font-medium">
                                                {{ $accident->accident_date ? (is_string($accident->accident_date) ? $accident->accident_date : $accident->accident_date->format('m-d-Y')) : 'N/A' }}
                                                - {{ $accident->location }}</div>
                                            <button type="button" class="text-slate-500 hover:text-primary"
                                                wire:click="$dispatch('openAccidentModal', { driverId: {{ $driver->id }}, accidentId: {{ $accident->id }} })">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-sm text-slate-500">Nature of Accident</div>
                                                <div class="font-medium">{{ $accident->nature_of_accident }}</div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-4 mt-2">
                                            @if ($accident->had_fatalities)
                                                <div>
                                                    <div class="text-sm text-slate-500">Fatalities</div>
                                                    <div class="font-medium text-danger">Yes,
                                                        {{ $accident->number_of_fatalities }} person(s)</div>
                                                </div>
                                            @endif

                                            @if ($accident->had_injuries)
                                                <div>
                                                    <div class="text-sm text-slate-500">Injuries</div>
                                                    <div class="font-medium text-warning">Yes,
                                                        {{ $accident->number_of_injuries }} person(s)</div>
                                                </div>
                                            @endif
                                        </div>

                                        @if ($accident->comments)
                                            <div class="mt-3">
                                                <div class="text-sm text-slate-500">Comments</div>
                                                <div class="text-sm mt-1">{{ $accident->comments }}</div>
                                            </div>
                                        @endif

                                        @if (method_exists($accident, 'getMedia') && $accident->getMedia('accident-images')->count() > 0)
                                            <div class="mt-3 pt-3 border-t border-slate-200">
                                                <div class="text-sm text-slate-500 mb-2">Documents</div>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($accident->getMedia('accident-images') as $document)
                                                        <a href="{{ $document->getUrl() }}" target="_blank"
                                                            class="block">
                                                            @if (Str::startsWith($document->mime_type, 'image/'))
                                                                <img src="{{ $document->getUrl() }}"
                                                                    alt="{{ $document->name }}"
                                                                    class="h-24 w-24 border rounded flex items-center justify-center bg-white">
                                                            @else
                                                                <div class="h-24 w-24 border rounded flex items-center justify-center bg-white"
                                                                    title="{{ $document->name }}">
                                                                    <div>
                                                                        <div
                                                                            class="h-24 w-24 border rounded flex items-center justify-center bg-white">
                                                                            <svg class="h-5 w-5" viewBox="0 0 24 24"
                                                                                fill="currentColor">
                                                                                <path
                                                                                    d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2M18 20H6V4H13V9H18V20M13 13V17H10V13H13Z">
                                                                                </path>
                                                                            </svg>
                                                                        </div>
                                                                        {{-- <span>{{ Str::limit($document->name, 10) }}</span> --}}
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 italic mb-4">No accidents have been registered.</div>
                        @endif

                        <div class="border-t my-5"></div>


                        <!-- FMCSR Data -->
                        @if ($driver->fmcsrData)
                            <h3 class="text-lg font-medium mb-4 mt-8">FMCSR Data</h3>
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
                @if ($currentTab === 'training')
                    <div class="mb-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Training</h3>
                            <button type="button" wire:click="openTrainingModal"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Assign Training
                            </button>
                        </div>

                        @if ($driver->driverTrainings && $driver->driverTrainings->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Training</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Assigned Date</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Due Date</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($driver->driverTrainings as $trainingAssignment)
                                            <tr>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $trainingAssignment->training->title ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $trainingAssignment->assigned_date ? $trainingAssignment->assigned_date->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $trainingAssignment->due_date ? $trainingAssignment->due_date->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $trainingAssignment->status === 'completed'
                                                            ? 'bg-green-100 text-green-800'
                                                            : ($trainingAssignment->isOverdue()
                                                                ? 'bg-red-100 text-red-800'
                                                                : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ ucfirst($trainingAssignment->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        @if ($trainingAssignment->status !== 'completed')
                                                            <button type="button"
                                                                wire:click="completeTraining({{ $trainingAssignment->id }})"
                                                                class="text-primary hover:text-primary-dark">
                                                                Mark Complete
                                                            </button>
                                                        @endif
                                                        <a href="{{ url('/admin/trainings/' . $trainingAssignment->training_id) }}"
                                                            target="_blank" class="text-blue-600 hover:text-blue-900">
                                                            View Details
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 text-right">
                                <a href="{{ url('/admin/trainings') }}" target="_blank"
                                    class="text-primary hover:text-primary-dark text-sm font-medium">
                                    Manage All Trainings
                                </a>
                            </div>
                        @else
                            <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                                <div class="text-slate-500 italic mb-4">No training assignments have been recorded for
                                    this driver.</div>
                                <p class="text-sm text-gray-500 mb-4">Assign a training from the available options or
                                    create new trainings in the training management section.</p>
                                <a href="{{ url('/admin/trainings') }}" target="_blank"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    Go to Training Management
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Historial de Empleo -->
                @if ($currentTab === 'history')
                    <div class="mb-5">
                        <h3 class="text-lg font-medium mb-4">Employment History</h3>

                        @if (
                            $driver->employmentCompanies->isNotEmpty() ||
                                $driver->unemploymentPeriods->isNotEmpty() ||
                                $driver->relatedEmployments->isNotEmpty())
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

                                        // Sumar empleos relacionados (driver_related_employments)
                                        foreach ($driver->relatedEmployments as $relatedEmployment) {
                                            $fromDate = $relatedEmployment->start_date;
                                            $toDate = $relatedEmployment->end_date;
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

                                    // Agregar empleos relacionados (driver_related_employments)
                                    foreach ($driver->relatedEmployments as $relatedEmployment) {
                                        $historyItems[] = [
                                            'type' => 'related_employment',
                                            'entity' => $relatedEmployment,
                                            'start_date' => $relatedEmployment->start_date,
                                            'end_date' => $relatedEmployment->end_date,
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
                                            class="absolute left-[-24px] w-8 h-8 rounded-full flex items-center justify-center {{ $item['type'] === 'employment' ? 'bg-primary' : ($item['type'] === 'related_employment' ? 'bg-green-500' : 'bg-amber-400') }}">
                                            {{-- <x-base.lucide class="h-4 w-4 text-white"
                                                icon="{{ $item['type'] === 'employment' ? 'Briefcase' : 'Clock' }}" /> --}}
                                            <svg fill="#ffffff" class="h-10 w-10 mr-1" viewBox="0 0 100.00 100.00"
                                                xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"
                                                stroke-width="0.001">
                                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                    stroke-linejoin="round"></g>
                                                <g id="SVGRepo_iconCarrier">
                                                    <path
                                                        d="M69.6,59.3A10.2,10.2,0,1,0,79.8,69.5,10.24,10.24,0,0,0,69.6,59.3Zm6.6,7.4-7.5,7.5a1.45,1.45,0,0,1-2,0L63,70.6a.67.67,0,0,1,0-1l1-1a.67.67,0,0,1,1,0l2.6,2.6,6.5-6.5a.67.67,0,0,1,1,0l1,1A.76.76,0,0,1,76.2,66.7Z">
                                                    </path>
                                                    <path
                                                        d="M44.5,30H62.3a1.58,1.58,0,0,0,1.6-1.6V25.1A4.91,4.91,0,0,0,59,20.2H47.7a4.91,4.91,0,0,0-4.9,4.9v3.3A1.73,1.73,0,0,0,44.5,30Z">
                                                    </path>
                                                    <path
                                                        d="M56.1,73.3H31.4a4.89,4.89,0,0,1-4.8-4.9v-34H25a4.89,4.89,0,0,0-4.8,4.9V74.7A4.89,4.89,0,0,0,25,79.6H59.9A12.11,12.11,0,0,1,56.1,73.3Z">
                                                    </path>
                                                    <path
                                                        d="M35.6,69.5H55.7a15.14,15.14,0,0,1,.7-3.7,13.68,13.68,0,0,1,2.3-4.5H49.3a1.58,1.58,0,0,1-1.6-1.6V58a1.58,1.58,0,0,1,1.6-1.6H65.5c.2,0,.3,0,.4.1a13.61,13.61,0,0,1,3.6-.5A13.89,13.89,0,0,1,76,57.6V29.2a4.91,4.91,0,0,0-4.9-4.9H69.5a.74.74,0,0,0-.8.8v3.3A6.57,6.57,0,0,1,62.2,35H44.5A6.64,6.64,0,0,1,38,28.4V25.1a.74.74,0,0,0-.8-.8H35.6a4.91,4.91,0,0,0-4.9,4.9V64.5A5.06,5.06,0,0,0,35.6,69.5Zm12.1-28a1.58,1.58,0,0,1,1.6-1.6H65.5a1.58,1.58,0,0,1,1.6,1.6v1.6a1.58,1.58,0,0,1-1.6,1.6H49.4a1.58,1.58,0,0,1-1.6-1.6V41.5Zm0,8.2a1.58,1.58,0,0,1,1.6-1.6H65.5a1.58,1.58,0,0,1,1.6,1.6v1.6a1.58,1.58,0,0,1-1.6,1.6H49.4a1.58,1.58,0,0,1-1.6-1.6V49.7Zm-8-8.2a1.58,1.58,0,0,1,1.6-1.6h1.6a1.58,1.58,0,0,1,1.6,1.6v1.6a1.58,1.58,0,0,1-1.6,1.6H41.3a1.58,1.58,0,0,1-1.6-1.6Zm0,8.2a1.58,1.58,0,0,1,1.6-1.6h1.6a1.58,1.58,0,0,1,1.6,1.6v1.6a1.58,1.58,0,0,1-1.6,1.6H41.3a1.58,1.58,0,0,1-1.6-1.6Zm0,8.3a1.58,1.58,0,0,1,1.6-1.6h1.6A1.58,1.58,0,0,1,44.5,58v1.6a1.58,1.58,0,0,1-1.6,1.6H41.3a1.58,1.58,0,0,1-1.6-1.6Z">
                                                    </path>
                                                </g>
                                            </svg>

                                        </div>

                                        <div class="bg-slate-50 p-4 rounded-lg">
                                            <!-- Periodo -->
                                            <div class="mb-2 text-sm text-slate-500">
                                                {{ $item['start_date']->format('m/d/Y') }} -
                                                {{ $item['end_date']->format('m/d/Y') }}
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
                                            @elseif ($item['type'] === 'related_employment')
                                                @php $relatedEmployment = $item['entity']; @endphp
                                                <div class="font-medium">Driver Related Employment</div>
                                                <div class="text-sm">Posición: {{ $relatedEmployment->position }}
                                                </div>
                                                @if ($relatedEmployment->comments)
                                                    <div class="text-sm mt-1">
                                                        <span class="text-slate-500">Comments:</span>
                                                        {{ $relatedEmployment->comments }}
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
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Documents</h3>

                            <!-- Botón para regenerar documentos -->
                            <button type="button" wire:click="regenerateDocuments" wire:loading.attr="disabled"
                                class="flex items-center px-3 py-1 bg-amber-500 text-white rounded hover:bg-amber-600 text-sm">
                                <svg fill="#ffffff" class="h-4 w-4 mr-2" version="1.1" id="Layer_1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    viewBox="0 0 512 512" xml:space="preserve">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g>
                                            <g>
                                                <path
                                                    d="M416.563,324.702c-4.497-1.493-9.31,0.93-10.795,5.393l-2.432,7.279C389.658,318.259,369.178,307.2,345.6,307.2 c-35.055,0-65.638,23.689-74.385,57.6c-1.178,4.565,1.579,9.216,6.144,10.394c0.708,0.188,1.425,0.273,2.133,0.273 c3.797,0,7.27-2.551,8.26-6.4c6.793-26.377,30.583-44.8,57.847-44.8c16.239,0,30.234,6.724,40.542,18.739l-3.712-1.237 c-4.514-1.51-9.301,0.93-10.795,5.393c-1.493,4.471,0.922,9.301,5.393,10.795l25.6,8.533c0.905,0.307,1.818,0.444,2.705,0.444 c3.575,0,6.903-2.261,8.09-5.837l8.533-25.6C423.45,331.025,421.035,326.195,416.563,324.702z">
                                                </path>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M414.404,384.06c-4.685-0.614-8.943,2.731-9.523,7.415c-3.72,29.79-29.21,52.258-59.281,52.258 c-21.282,0-41.387-11.494-52.548-28.433l7.177,2.389c4.506,1.502,9.301-0.93,10.795-5.393c1.493-4.471-0.922-9.301-5.393-10.795 l-25.6-8.533c-4.506-1.51-9.301,0.93-10.795,5.393l-8.533,25.6c-1.493,4.471,0.922,9.301,5.393,10.795 c0.905,0.307,1.818,0.444,2.705,0.444c3.576,0,6.904-2.261,8.09-5.837l1.621-4.872c14.165,21.888,39.543,36.309,67.089,36.309 c38.664,0,71.433-28.894,76.211-67.217C422.391,388.907,419.072,384.649,414.404,384.06z">
                                                </path>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M345.6,256c-70.579,0-128,57.421-128,128s57.421,128,128,128s128-57.421,128-128S416.179,256,345.6,256z M345.6,494.933 c-61.167,0-110.933-49.766-110.933-110.933S284.433,273.067,345.6,273.067S456.533,322.833,456.533,384 S406.767,494.933,345.6,494.933z">
                                                </path>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M226.133,469.333H55.467V409.6c0-4.71-3.823-8.533-8.533-8.533c-4.71,0-8.533,3.823-8.533,8.533v68.267 c0,4.71,3.823,8.533,8.533,8.533h179.2c4.71,0,8.533-3.823,8.533-8.533S230.844,469.333,226.133,469.333z">
                                                </path>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M46.933,366.933c-4.71,0-8.533,3.823-8.533,8.533S42.223,384,46.933,384h0.085c4.71,0,8.491-3.823,8.491-8.533 S51.644,366.933,46.933,366.933z">
                                                </path>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M394.3,139.034L257.766,2.5c-1.596-1.604-3.772-2.5-6.033-2.5h-204.8C42.223,0,38.4,3.823,38.4,8.533v332.8 c0,4.71,3.823,8.533,8.533,8.533c4.71,0,8.533-3.823,8.533-8.533V17.067H243.2v128c0,4.71,3.823,8.533,8.533,8.533h128v76.8 c0,4.71,3.823,8.533,8.533,8.533s8.533-3.823,8.533-8.533v-85.333C396.8,142.805,395.904,140.629,394.3,139.034z M260.267,136.533 V29.133l107.401,107.401H260.267z">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                                <span wire:loading.remove wire:target="regenerateDocuments">Regenerate Documents</span>
                                <span wire:loading wire:target="regenerateDocuments">Regenerating...</span>
                            </button>
                        </div>

                        @if (count($generatedPdfs) > 0)
                            <!-- Botón para descargar todos los documentos -->
                            <div class="mt-4 flex justify-center mb-4">
                                <button type="button" wire:click="downloadAllDocuments"
                                    class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-focus flex items-center">
                                    <svg fill="#ffffff" class="h-4 w-4 mr-2" version="1.1" id="Layer_1"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="0 0 512 512" xml:space="preserve" stroke="#ffffff">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <g>
                                                    <path
                                                        d="M385.766,403.567c-3.337-3.337-8.73-3.337-12.066,0l-19.567,19.567v-98.867c0-4.71-3.823-8.533-8.533-8.533 c-4.71,0-8.533,3.823-8.533,8.533v98.867L317.5,403.567c-3.337-3.337-8.73-3.337-12.066,0c-3.336,3.336-3.336,8.73,0,12.066 l34.133,34.133c1.664,1.664,3.849,2.5,6.033,2.5c2.185,0,4.369-0.836,6.033-2.5l34.133-34.133 C389.103,412.297,389.103,406.904,385.766,403.567z">
                                                    </path>
                                                </g>
                                            </g>
                                            <g>
                                                <g>
                                                    <path
                                                        d="M345.6,256c-70.579,0-128,57.421-128,128s57.421,128,128,128s128-57.421,128-128S416.179,256,345.6,256z M345.6,494.933 c-61.167,0-110.933-49.766-110.933-110.933S284.433,273.067,345.6,273.067S456.533,322.833,456.533,384 S406.767,494.933,345.6,494.933z">
                                                    </path>
                                                </g>
                                            </g>
                                            <g>
                                                <g>
                                                    <path
                                                        d="M226.133,469.333H55.467V409.6c0-4.71-3.823-8.533-8.533-8.533c-4.71,0-8.533,3.823-8.533,8.533v68.267 c0,4.71,3.823,8.533,8.533,8.533h179.2c4.71,0,8.533-3.823,8.533-8.533S230.844,469.333,226.133,469.333z">
                                                    </path>
                                                </g>
                                            </g>
                                            <g>
                                                <g>
                                                    <path
                                                        d="M46.933,366.933c-4.71,0-8.533,3.823-8.533,8.533S42.223,384,46.933,384h0.085c4.71,0,8.491-3.823,8.491-8.533 S51.644,366.933,46.933,366.933z">
                                                    </path>
                                                </g>
                                            </g>
                                            <g>
                                                <g>
                                                    <path
                                                        d="M394.3,139.034L257.766,2.5c-1.596-1.604-3.772-2.5-6.033-2.5h-204.8C42.223,0,38.4,3.823,38.4,8.533v332.8 c0,4.71,3.823,8.533,8.533,8.533c4.71,0,8.533-3.823,8.533-8.533V17.067H243.2v128c0,4.71,3.823,8.533,8.533,8.533h128v76.8 c0,4.71,3.823,8.533,8.533,8.533s8.533-3.823,8.533-8.533v-85.333C396.8,142.805,395.904,140.629,394.3,139.034z M260.267,136.533 V29.133l107.401,107.401H260.267z">
                                                    </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <span wire:loading.remove wire:target="downloadAllDocuments">Descargar Todos los
                                        Documentos</span>
                                    <span wire:loading wire:target="downloadAllDocuments">Generando ZIP...</span>
                                </button>
                            </div>

                            <!-- Lista de documentos -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @foreach ($generatedPdfs as $key => $pdf)
                                    <div
                                        class="border rounded p-3 flex items-center bg-white hover:bg-slate-50 transition-colors">
                                        <div class="mr-3 text-slate-400">
                                            <svg class="h-5 w-5 mr-1" fill="#000000" version="1.1" id="Capa_1"
                                                xmlns="http://www.w3.org/2000/svg"
                                                xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 487.89 487.89"
                                                xml:space="preserve" stroke="#000000" stroke-width="0.00487887">
                                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                    stroke-linejoin="round"></g>
                                                <g id="SVGRepo_iconCarrier">
                                                    <path
                                                        d="M409.046,453.807c0,2.762-2.239,5-5,5H69.414c-2.761,0-5-2.238-5-5s2.239-5,5-5h334.632 C406.808,448.807,409.046,451.045,409.046,453.807z M404.046,462.643H69.414c-2.761,0-5,2.238-5,5s2.239,5,5,5h334.632 c2.761,0,5-2.238,5-5S406.808,462.643,404.046,462.643z M124.073,17.067c-2.761,0-5,2.238-5,5v342.819c0,2.762,2.239,5,5,5 s5-2.238,5-5V22.067C129.073,19.306,126.834,17.067,124.073,17.067z M124.073,394.021c-2.761,0-5,2.238-5,5v15.588 c0,2.762,2.239,5,5,5s5-2.238,5-5v-15.588C129.073,396.259,126.834,394.021,124.073,394.021z M261.382,343.332v-36.878 c0-0.009,0-0.018,0-0.026V269.98c0-2.762,2.239-5,5-5h18.398c12.838,0,23.283,10.444,23.283,23.282 c0,6.244-2.438,12.108-6.867,16.511c-4.396,4.37-10.219,6.771-16.412,6.771c-0.046,0-0.092,0-0.138,0l-13.265-0.076v31.863 c0,2.762-2.239,5-5,5S261.382,346.094,261.382,343.332z M271.382,301.469l13.322,0.076c0.026,0,0.053,0,0.079,0 c3.533,0,6.855-1.37,9.363-3.862c2.526-2.512,3.917-5.857,3.917-9.42c0-7.324-5.958-13.282-13.283-13.282h-13.398V301.469z M316.404,343.332V269.97c0-2.762,2.239-5,5-5c22.983,0,41.681,18.698,41.681,41.681c0,22.983-18.698,41.682-41.681,41.682 C318.643,348.332,316.404,346.094,316.404,343.332z M326.404,337.938c15.102-2.403,26.681-15.518,26.681-31.286 s-11.579-28.884-26.681-31.287V337.938z M376.425,348.332c2.761,0,5-2.238,5-5v-31.67h22.511c2.761,0,5-2.238,5-5s-2.239-5-5-5 h-22.511V274.98h31.681c2.761,0,5-2.238,5-5s-2.239-5-5-5h-36.681c-2.761,0-5,2.238-5,5v73.352 C371.425,346.094,373.664,348.332,376.425,348.332z M449.271,244.319v124.675c0,2.762-2.239,5-5,5h-17.3v42.674v21.273v44.945 c0,2.762-2.239,5-5,5H43.616c-2.761,0-5-2.238-5-5v-44.933v-0.013V5c0-2.762,2.239-5,5-5h54.075h324.28c2.761,0,5,2.238,5,5v234.319 h17.3C447.032,239.319,449.271,241.558,449.271,244.319z M48.616,432.941h44.075V10H48.616V432.941z M416.971,477.887v-34.945 H97.817c-0.043,0.001-0.083,0.013-0.126,0.013H48.616v34.933H416.971z M416.971,373.994H226.115c-2.761,0-5-2.238-5-5V244.319 c0-2.762,2.239-5,5-5h190.855V10h-314.28v422.941h314.28v-16.273V373.994z M439.271,249.319H231.115v114.675h208.156V249.319z">
                                                    </path>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium">{{ $pdf['name'] }}</div>
                                            <div class="text-xs text-slate-500">{{ $pdf['size'] }} - Generated:
                                                {{ $pdf['date'] }}</div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ $pdf['url'] }}" target="_blank"
                                                class="px-3 py-1 bg-slate-100 text-slate-700 rounded hover:bg-slate-200 text-sm flex items-center">
                                                <svg class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                        stroke-linejoin="round"></g>
                                                    <g id="SVGRepo_iconCarrier">
                                                        <circle cx="12" cy="12" r="2.5"
                                                            stroke="#222222"></circle>
                                                        <path
                                                            d="M18.2265 11.3805C18.3552 11.634 18.4195 11.7607 18.4195 12C18.4195 12.2393 18.3552 12.366 18.2265 12.6195C17.6001 13.8533 15.812 16.5 12 16.5C8.18799 16.5 6.39992 13.8533 5.77348 12.6195C5.64481 12.366 5.58048 12.2393 5.58048 12C5.58048 11.7607 5.64481 11.634 5.77348 11.3805C6.39992 10.1467 8.18799 7.5 12 7.5C15.812 7.5 17.6001 10.1467 18.2265 11.3805Z"
                                                            stroke="#222222"></path>
                                                        <path
                                                            d="M17.5 3.5H17.7C19.4913 3.5 20.387 3.5 20.9435 4.0565C21.5 4.61299 21.5 5.50866 21.5 7.3V7.5M17.5 20.5H17.7C19.4913 20.5 20.387 20.5 20.9435 19.9435C21.5 19.387 21.5 18.4913 21.5 16.7V16.5M6.5 3.5H6.3C4.50866 3.5 3.61299 3.5 3.0565 4.0565C2.5 4.61299 2.5 5.50866 2.5 7.3V7.5M6.5 20.5H6.3C4.50866 20.5 3.61299 20.5 3.0565 19.9435C2.5 19.387 2.5 18.4913 2.5 16.7V16.5"
                                                            stroke="#2A4157" stroke-opacity="0.24"
                                                            stroke-linecap="round"></path>
                                                    </g>
                                                </svg>
                                                Ver
                                            </a>
                                            <!-- Botón para descargar el documento -->
                                            <a href="{{ $pdf['url'] }}" download
                                                class="p-1 text-slate-500 hover:text-primary">
                                                <svg fill="#000000" class="h-5 w-5 mr-1" version="1.1"
                                                    id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                                                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512"
                                                    xml:space="preserve">
                                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                        stroke-linejoin="round"></g>
                                                    <g id="SVGRepo_iconCarrier">
                                                        <g>
                                                            <g>
                                                                <path
                                                                    d="M385.766,403.567c-3.337-3.337-8.73-3.337-12.066,0l-19.567,19.567v-98.867c0-4.71-3.823-8.533-8.533-8.533 c-4.71,0-8.533,3.823-8.533,8.533v98.867L317.5,403.567c-3.337-3.337-8.73-3.337-12.066,0c-3.336,3.336-3.336,8.73,0,12.066 l34.133,34.133c1.664,1.664,3.849,2.5,6.033,2.5c2.185,0,4.369-0.836,6.033-2.5l34.133-34.133 C389.103,412.297,389.103,406.904,385.766,403.567z">
                                                                </path>
                                                            </g>
                                                        </g>
                                                        <g>
                                                            <g>
                                                                <path
                                                                    d="M345.6,256c-70.579,0-128,57.421-128,128s57.421,128,128,128s128-57.421,128-128S416.179,256,345.6,256z M345.6,494.933 c-61.167,0-110.933-49.766-110.933-110.933S284.433,273.067,345.6,273.067S456.533,322.833,456.533,384 S406.767,494.933,345.6,494.933z">
                                                                </path>
                                                            </g>
                                                        </g>
                                                        <g>
                                                            <g>
                                                                <path
                                                                    d="M226.133,469.333H55.467V409.6c0-4.71-3.823-8.533-8.533-8.533c-4.71,0-8.533,3.823-8.533,8.533v68.267 c0,4.71,3.823,8.533,8.533,8.533h179.2c4.71,0,8.533-3.823,8.533-8.533S230.844,469.333,226.133,469.333z">
                                                                </path>
                                                            </g>
                                                        </g>
                                                        <g>
                                                            <g>
                                                                <path
                                                                    d="M46.933,366.933c-4.71,0-8.533,3.823-8.533,8.533S42.223,384,46.933,384h0.085c4.71,0,8.491-3.823,8.491-8.533 S51.644,366.933,46.933,366.933z">
                                                                </path>
                                                            </g>
                                                        </g>
                                                        <g>
                                                            <g>
                                                                <path
                                                                    d="M394.3,139.034L257.766,2.5c-1.596-1.604-3.772-2.5-6.033-2.5h-204.8C42.223,0,38.4,3.823,38.4,8.533v332.8 c0,4.71,3.823,8.533,8.533,8.533c4.71,0,8.533-3.823,8.533-8.533V17.067H243.2v128c0,4.71,3.823,8.533,8.533,8.533h128v76.8 c0,4.71,3.823,8.533,8.533,8.533s8.533-3.823,8.533-8.533v-85.333C396.8,142.805,395.904,140.629,394.3,139.034z M260.267,136.533 V29.133l107.401,107.401H260.267z">
                                                                </path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Sección para solicitar documentos adicionales -->
                            <div class="mt-8 mb-5 bg-white p-4 rounded-lg shadow-sm border">
                                <h4 class="font-medium mb-3">Solicitar Documentos Adicionales</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                    <!-- Botones para seleccionar documentos -->
                                    <button type="button" wire:click="selectDocument('ssn_card')"
                                        class="flex items-center p-2 border rounded hover:bg-slate-50 transition-colors
                                            {{ in_array('ssn_card', $requestedDocuments) ? 'bg-primary-50 border-primary' : '' }}">
                                        <svg class="h-5 w-5 mr-2 {{ in_array('ssn_card', $requestedDocuments) ? 'text-primary' : 'text-slate-400' }}"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01"
                                                    stroke="#03045E" stroke-width="1.44" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                        <span>Tarjeta de Seguro Social</span>
                                    </button>

                                    <button type="button" wire:click="selectDocument('license')"
                                        class="flex items-center p-2 border rounded hover:bg-slate-50 transition-colors
                                            {{ in_array('license', $requestedDocuments) ? 'bg-primary-50 border-primary' : '' }}">
                                        <svg class="h-5 w-5 mr-2 {{ in_array('license', $requestedDocuments) ? 'text-primary' : 'text-slate-400' }}"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01"
                                                    stroke="#03045E" stroke-width="1.44" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                        <span>Licencia de Conducir</span>
                                    </button>

                                    <button type="button" wire:click="selectDocument('medical_card')"
                                        class="flex items-center p-2 border rounded hover:bg-slate-50 transition-colors
                                            {{ in_array('medical_card', $requestedDocuments) ? 'bg-primary-50 border-primary' : '' }}">
                                        <svg class="h-5 w-5 mr-2 {{ in_array('medical_card', $requestedDocuments) ? 'text-primary' : 'text-slate-400' }}"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01"
                                                    stroke="#03045E" stroke-width="1.44" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                        <span>Tarjeta Médica</span>
                                    </button>

                                    <button type="button" wire:click="selectDocument('proof_address')"
                                        class="flex items-center p-2 border rounded hover:bg-slate-50 transition-colors
                                            {{ in_array('proof_address', $requestedDocuments) ? 'bg-primary-50 border-primary' : '' }}">
                                        <svg class="h-5 w-5 mr-2 {{ in_array('proof_address', $requestedDocuments) ? 'text-primary' : 'text-slate-400' }}"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01"
                                                    stroke="#03045E" stroke-width="1.44" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                        <span>Comprobante de Domicilio</span>
                                    </button>

                                    <button type="button" wire:click="selectDocument('employment_verification')"
                                        class="flex items-center p-2 border rounded hover:bg-slate-50 transition-colors
                                            {{ in_array('employment_verification', $requestedDocuments) ? 'bg-primary-50 border-primary' : '' }}">
                                        <svg class="h-5 w-5 mr-2 {{ in_array('employment_verification', $requestedDocuments) ? 'text-primary' : 'text-slate-400' }}"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01"
                                                    stroke="#03045E" stroke-width="1.44" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                        <span>Verificación de Empleo Anterior</span>
                                    </button>
                                </div>

                                <!-- Lista de documentos seleccionados -->
                                @if (count($requestedDocuments) > 0)
                                    <div class="mb-4">
                                        <h5 class="text-sm font-medium mb-2">Documentos solicitados:</h5>
                                        <div class="flex flex-wrap gap-2">
                                            @php
                                                $documentLabels = [
                                                    'ssn_card' => 'Tarjeta de Seguro Social',
                                                    'license' => 'Licencia de Conducir',
                                                    'medical_card' => 'Tarjeta Médica',
                                                    'proof_address' => 'Comprobante de Domicilio',
                                                    'employment_verification' => 'Verificación de Empleo Anterior',
                                                ];
                                            @endphp

                                            @foreach ($requestedDocuments as $doc)
                                                <div
                                                    class="inline-flex items-center bg-slate-100 px-2 py-1 rounded text-sm">
                                                    <span>{{ $documentLabels[$doc] ?? $doc }}</span>
                                                    <button type="button"
                                                        wire:click="removeRequestedDocument('{{ $doc }}')"
                                                        class="ml-1 text-slate-500 hover:text-red-500">
                                                        <x-base.lucide class="h-4 w-4" icon="X" />
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Requisitos adicionales -->
                                <div class="mb-4">
                                    <label for="additionalRequirements"
                                        class="block text-sm font-medium mb-1">Requisitos adicionales
                                        (opcional):</label>
                                    <textarea id="additionalRequirements" wire:model.live="additionalRequirements" rows="3"
                                        class="w-full border rounded px-3 py-2 text-sm"
                                        placeholder="Ingrese cualquier requisito adicional o instrucciones para el conductor..."></textarea>
                                </div>

                                <!-- Botón para enviar la solicitud -->
                                <div class="flex justify-end">
                                    <button type="button" wire:click="requestAdditionalDocuments"
                                        class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-focus flex items-center"
                                        {{ count($requestedDocuments) === 0 ? 'disabled' : '' }}
                                        {{ count($requestedDocuments) === 0 ? 'opacity-50 cursor-not-allowed' : '' }}>
                                        {{-- <x-base.lucide class="h-4 w-4 mr-2" icon="Send" /> --}}
                                        <svg class="h-8 w-8 mr-2" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M20 4L13 21L10 14M20 4L12 7.29412M20 4L10 14M10 14L3 11L7 9.35294"
                                                    stroke="#fafafa" stroke-width="0.4800000000000001"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                        <span wire:loading.remove wire:target="requestAdditionalDocuments">Enviar
                                            Solicitud</span>
                                        <span wire:loading wire:target="requestAdditionalDocuments">Enviando...</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-8 text-center">
                                <div class="text-slate-400 mb-3">
                                    <x-base.lucide class="h-12 w-12 mx-auto" icon="FileX" />
                                </div>
                                <div class="text-slate-700 font-medium mb-2">No hay documentos generados</div>
                                <div class="text-slate-500 text-sm mb-4">No se han generado documentos PDF para esta
                                    aplicación.</div>
                                <button type="button" wire:click="regenerateDocuments"
                                    class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-focus inline-flex items-center">
                                    <x-base.lucide class="h-4 w-4 mr-2" icon="FileText" />
                                    <span wire:loading.remove wire:target="regenerateDocuments">Generar
                                        Documentos</span>
                                    <span wire:loading wire:target="regenerateDocuments">Procesando...</span>
                                </button>
                            </div>

                            <script>
                                document.addEventListener('livewire:initialized', function() {
                                    // Manejar el evento fileUploaded desde el componente Livewire
                                    @this.on('fileUploaded', (data) => {
                                        // Actualizar la vista previa del archivo usando los campos correctos
                                        // Usar los mismos nombres de campo que el componente Livewire emite
                                        if (data.tempPath) {
                                            // Crear URL para la vista previa del archivo
                                            const fileUrl = '/storage/' + data.tempPath;

                                            // Aquí podrías actualizar una vista previa del PDF si es necesario
                                            console.log('Archivo cargado temporalmente:', {
                                                tempPath: data.tempPath,
                                                originalName: data.originalName,
                                                size: data.size
                                            });
                                        }
                                    });
                                });
                            </script>
                        @endif



                    </div>
                @endif


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
                            <div class="text-slate-600">{{ $savedVerification->verified_at->format('m/d/Y H:i') }}
                            </div>
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

        </div>
        <!-- Panel derecho: Checklist y acciones -->
        <div class="w-2/5">
            <div class="box box--stacked p-5">
                <h3 class="text-lg font-medium mb-4">Lista de Verificación</h3>

                <!-- Progreso de verificación -->
                <div class="mb-4">
                    @php
                        $totalItems = count($checklistItems);
                        $checkedItems = collect($checklistItems)->where('checked', true)->count();
                        $checklistPercentage = $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0;
                    @endphp

                    <div class="flex justify-between items-center mb-1">
                        <div class="text-sm font-medium">Progreso de verificación</div>
                        <div class="text-sm font-medium">{{ $checkedItems }}/{{ $totalItems }}
                            ({{ $checklistPercentage }}%)</div>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-primary h-2.5" style="width: {{ $checklistPercentage }}%"></div>
                    </div>
                </div>

                <!-- Grupos de checklist -->
                <div class="space-y-4 mb-6">
                    <!-- Información general -->
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 font-medium text-sm border-b border-slate-200">Información
                            General</div>
                        <div class="p-3 space-y-2">
                            @foreach (['general_info', 'contact_info', 'address_info'] as $key)
                                @if (isset($checklistItems[$key]))
                                    <div class="flex items-center hover:bg-slate-50 p-1 rounded">
                                        <input type="checkbox" id="checklist-{{ $key }}"
                                            wire:model.live="checklistItems.{{ $key }}.checked"
                                            class="form-checkbox h-5 w-5 text-primary rounded border-slate-300">
                                        <label for="checklist-{{ $key }}"
                                            class="ml-2 text-sm cursor-pointer w-full">{{ $checklistItems[$key]['label'] }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Licencias y documentos -->
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 font-medium text-sm border-b border-slate-200">Licencias y
                            Documentos</div>
                        <div class="p-3 space-y-2">
                            @foreach (['license_info', 'license_image', 'medical_info', 'medical_image'] as $key)
                                @if (isset($checklistItems[$key]))
                                    <div class="flex items-center hover:bg-slate-50 p-1 rounded">
                                        <input type="checkbox" id="checklist-{{ $key }}"
                                            wire:model.live="checklistItems.{{ $key }}.checked"
                                            class="form-checkbox h-5 w-5 text-primary rounded border-slate-300">
                                        <label for="checklist-{{ $key }}"
                                            class="ml-2 text-sm cursor-pointer w-full">{{ $checklistItems[$key]['label'] }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Experiencia y registros -->
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 font-medium text-sm border-b border-slate-200">Experiencia y
                            Registros</div>
                        <div class="p-3 space-y-2">
                            @foreach (['experience_info', 'training_verified', 'traffic_verified', 'accident_verified', 'history_info'] as $key)
                                @if (isset($checklistItems[$key]))
                                    <div class="flex items-center hover:bg-slate-50 p-1 rounded">
                                        <input type="checkbox" id="checklist-{{ $key }}"
                                            wire:model.live="checklistItems.{{ $key }}.checked"
                                            class="form-checkbox h-5 w-5 text-primary rounded border-slate-300">
                                        <label for="checklist-{{ $key }}"
                                            class="ml-2 text-sm cursor-pointer w-full">{{ $checklistItems[$key]['label'] }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Certificación de la Aplicación -->
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 font-medium text-sm border-b border-slate-200">Certificación
                            de la Aplicación</div>
                        <div class="p-3 space-y-2">
                            @if (isset($checklistItems['application_certification']))
                                <div class="flex items-center hover:bg-slate-50 p-1 rounded">
                                    <input type="checkbox" id="checklist-application_certification"
                                        wire:model.live="checklistItems.application_certification.checked"
                                        class="form-checkbox h-5 w-5 text-primary rounded border-slate-300">
                                    <label for="checklist-application_certification"
                                        class="ml-2 text-sm cursor-pointer w-full">{{ $checklistItems['application_certification']['label'] }}</label>
                                </div>
                            @endif
                            @if (isset($checklistItems['documents_checked']))
                                <div class="flex items-center hover:bg-slate-50 p-1 rounded">
                                    <input type="checkbox" id="checklist-documents_checked"
                                        wire:model.live="checklistItems.documents_checked.checked"
                                        class="form-checkbox h-5 w-5 text-primary rounded border-slate-300">
                                    <label for="checklist-documents_checked"
                                        class="ml-2 text-sm cursor-pointer w-full">{{ $checklistItems['documents_checked']['label'] }}</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Verificaciones adicionales -->
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 font-medium text-sm border-b border-slate-200">
                            Verificaciones
                            Adicionales</div>
                        <div class="p-3 space-y-2">
                            @foreach (['criminal_check', 'drug_test', 'mvr_check', 'policy_agreed', 'vehicle_info'] as $key)
                                @if (isset($checklistItems[$key]))
                                    <div class="flex items-center hover:bg-slate-50 p-1 rounded">
                                        <input type="checkbox" id="checklist-{{ $key }}"
                                            wire:model.live="checklistItems.{{ $key }}.checked"
                                            class="form-checkbox h-5 w-5 text-primary rounded border-slate-300">
                                        <label for="checklist-{{ $key }}"
                                            class="ml-2 text-sm cursor-pointer w-full">{{ $checklistItems[$key]['label'] }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                @error('checklist')
                    <div class="text-danger text-sm mb-4">{{ $message }}</div>
                @enderror

                <!-- Información de ayuda -->
                <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-600 mb-4">
                    <x-base.lucide class="h-4 w-4 inline-block mr-1" icon="Info" />
                    Complete todos los elementos de verificación antes de aprobar la aplicación. Esto asegura que toda
                    la información del conductor ha sido revisada y validada.
                </div>

                <!-- Actions based on application status -->
                @if ($application->status === 'pending')
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 font-medium text-sm border-b border-slate-200">Acciones
                            Disponibles</div>
                        <div class="p-4">
                            <div class="text-sm text-slate-600 mb-4">
                                Después de verificar todos los elementos de la lista, puede aprobar o rechazar esta
                                solicitud.
                            </div>

                            <div class="flex flex-col gap-3">
                                <button type="button" wire:click="approveApplication"
                                    class="px-4 py-3 bg-success text-white rounded-lg hover:bg-success-focus flex items-center justify-center transition-colors"
                                    {{ $this->isChecklistComplete() ? '' : 'disabled' }}>
                                    <x-base.lucide class="h-5 w-5 mr-2" icon="CheckCircle" />
                                    Aprobar Solicitud
                                </button>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#reject-modal"
                                    class="px-4 py-3 bg-danger text-white rounded-lg hover:bg-danger-focus flex items-center justify-center transition-colors">
                                    <x-base.lucide class="h-5 w-5 mr-2" icon="XCircle" />
                                    Rechazar Solicitud
                                </button>
                            </div>
                        </div>
                    </div>
                @elseif($application->status === 'approved')
                    <div class="border border-success/30 rounded-lg overflow-hidden">
                        <div class="bg-success/10 px-4 py-2 font-medium text-success border-b border-success/30">
                            Solicitud Aprobada</div>
                        <div class="p-4 bg-success/5">
                            <div class="flex items-start">
                                <x-base.lucide class="h-5 w-5 text-success mr-2 mt-0.5" icon="CheckCircle" />
                                <div>
                                    <div class="font-medium text-success">Esta solicitud ha sido aprobada</div>
                                    <div class="text-sm text-slate-600 mt-1">Fecha de aprobación:
                                        {{ is_string($application->completed_at)
                                            ? \Carbon\Carbon::parse($application->completed_at)->format('m/d/Y')
                                            : $application->completed_at->format('m/d/Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($application->status === 'rejected')
                    <div class="border border-danger/30 rounded-lg overflow-hidden">
                        <div class="bg-danger/10 px-4 py-2 font-medium text-danger border-b border-danger/30">
                            Solicitud
                            Rechazada</div>
                        <div class="p-4 bg-danger/5">
                            <div class="flex items-start">
                                <x-base.lucide class="h-5 w-5 text-danger mr-2 mt-0.5" icon="XCircle" />
                                <div>
                                    <div class="font-medium text-danger">Esta solicitud ha sido rechazada</div>
                                    <div class="text-sm text-slate-600 mt-1">Fecha de rechazo:
                                        {{ is_string($application->completed_at)
                                            ? \Carbon\Carbon::parse($application->completed_at)->format('m/d/Y')
                                            : $application->completed_at->format('m/d/Y') }}
                                    </div>

                                    @if ($application->rejection_reason)
                                        <div class="mt-3 p-3 border border-slate-200 rounded bg-white text-sm">
                                            <div class="font-medium mb-1">Motivo del rechazo:</div>
                                            <div class="text-slate-700">{{ $application->rejection_reason }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
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

    <!-- Modal para subir imágenes de licencia (frontal/trasera) -->
    <div x-data="{ open: false }" x-init="$wire.on('open-license-image-modal', () => { open = true });
    $wire.on('closeUploadModal', () => { open = false });" x-show="open"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
        style="display: none;"
        class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4">

        <div class="w-full max-w-md bg-white rounded-md shadow-lg">
            <!-- Header -->
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 rounded-t-md">
                <h3 class="text-lg font-medium">
                    {{ $licenseImageType === 'license_front' ? 'Upload Front License Image' : ($licenseImageType === 'license_back' ? 'Upload Back License Image' : 'Upload Medical Card Image') }}
                </h3>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Uploader Component -->
                <div class="mb-5">
                    <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:bg-slate-50 transition-colors cursor-pointer"
                        x-data="licenseUploader()">

                        <input type="file" accept="image/*" class="hidden" id="license-image-upload"
                            @change="handleFileUpload($event)">

                        <div x-show="!isUploading" @click="document.getElementById('license-image-upload').click()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor"
                                class="w-16 h-16 text-slate-400 mx-auto mb-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <p class="text-slate-600 font-medium">Click to select or drag image here</p>
                            <p class="text-sm text-slate-500 mt-1">JPG, PNG or GIF (max 5MB)</p>
                        </div>

                        <div x-show="isUploading" class="text-center">
                            <div class="w-full bg-slate-200 rounded-full h-2.5 mb-4">
                                <div class="bg-primary h-2.5 rounded-full" :style="{ width: progress + '%' }"></div>
                            </div>
                            <p class="text-slate-600">Uploading... <span x-text="progress + '%'"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Información -->
                <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-600 mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="h-4 w-4 inline-block mr-1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    {{ $licenseImageType === 'license_front' ? 'Please upload a clear image of the front of the driver license.' : ($licenseImageType === 'license_back' ? 'Please upload a clear image of the back of the driver license.' : 'Please upload a clear image of the medical card.') }}
                </div>

                <!-- Botones -->
                <div class="flex justify-end">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para asignar entrenamiento (Alpine.js) -->
    <div x-data="{ open: false }" x-on:open-training-modal.window="open = true"
        x-on:close-training-modal.window="open = false" x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
        style="display: none;"
        class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4">

        <!-- Contenedor del modal -->
        <div class="w-full max-w-md bg-white rounded-md shadow-lg p-5">

            <!-- Encabezado del modal -->
            <div class="mb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Assign Training</h3>
            </div>

            <!-- Contenido del modal -->
            <div class="mb-4">
                <label for="training" class="block text-sm font-medium text-gray-700 mb-1">Select Training</label>
                <select id="training" wire:model="selectedTrainingId"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                    <option value="">-- Select Training --</option>
                    @foreach ($availableTrainings as $training)
                        <option value="{{ $training->id }}">{{ $training->title }}</option>
                    @endforeach
                </select>
                @error('selectedTrainingId')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="dueDate" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input type="date" id="dueDate" wire:model="trainingDueDate"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                @error('trainingDueDate')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="initialStatus" class="block text-sm font-medium text-gray-700 mb-1">Initial
                    Status</label>
                <select id="initialStatus" wire:model="trainingStatus"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                    <option value="assigned">Assigned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                @error('trainingStatus')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="trainingNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="trainingNotes" wire:model="trainingNotes" rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    placeholder="Add any notes about this training assignment..."></textarea>
            </div>

            <!-- Pie del modal con botones -->
            <div class="mt-5 flex justify-end">
                <button type="button" wire:click="assignTraining"
                    class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:col-start-2">
                    Assign Training
                </button>
                <button type="button" wire:click="closeTrainingModal"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                    Cancel
                </button>
            </div>

        </div>
    </div>

    <!-- Modal para ingresar motivo de documento solicitado -->
    <!-- Modal simple con Alpine.js -->


    <div x-data="{ open: false }" x-init="$wire.on('open-document-reason-modal', () => { open = true });
    $wire.on('close-document-reason-modal', () => { open = false });" x-show="open"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
        class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s] overflow-y-auto show"
        style="display: none;">

        <div
            class="w-[90%] mx-auto bg-white relative rounded-sm shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-4 group-[.show]:mt-40 group-[.modal-static]:scale-[1.05] sm:w-[750px] p-4">
            <!-- Header -->
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                <h3 class="text-lg font-medium">Motivo de solicitud de documento</h3>
            </div>

            <!-- Body -->
            <div class="p-4">
                <form wire:submit.prevent="saveDocumentReason">
                    @php
                        $documentLabels = [
                            'ssn_card' => 'Tarjeta de Seguro Social',
                            'license' => 'Licencia de Conducir',
                            'medical_card' => 'Tarjeta Médica',
                            'proof_address' => 'Comprobante de Domicilio',
                            'employment_verification' => 'Verificación de Empleo Anterior',
                        ];
                    @endphp

                    <!-- Documento seleccionado -->
                    <div class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded">
                        <p class="text-sm font-medium">Documento: <span
                                class="text-primary">{{ $documentLabels[$selectedDocument] ?? $selectedDocument }}</span>
                        </p>
                    </div>

                    <!-- Campo de motivo -->
                    <div class="mb-4">
                        <label for="documentReason" class="block text-sm font-medium mb-1">Motivo por el que
                            solicita
                            este documento</label>
                        <textarea id="documentReason" wire:model.live="documentReason" rows="4"
                            class="w-full border rounded px-3 py-2 text-sm"
                            placeholder="Explique por qué necesita este documento adicional..."></textarea>
                        @error('documentReason')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mensaje informativo -->
                    <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 inline-block mr-1">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        Este motivo será incluido en la notificación enviada al conductor y al transportista.
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex justify-end">
                        <button type="button" @click="open = false" wire:click="cancelDocumentReason"
                            class="px-4 py-2 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 mr-2">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-focus">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Modal Record de Manejo -->
    @include('livewire.admin.driver.recruitment.modal.driver-driving-record-modal')

    <!-- Modal Record Criminal -->
    @include('livewire.admin.driver.recruitment.modal.driver-criminal-record-modal')

    <!-- Modal Record Médico -->
    @include('livewire.admin.driver.recruitment.modal.driver-medical-record-modal')
</div>



<script>
    function licenseUploader() {
        return {
            open: false,
            isUploading: false,
            progress: 0,
            init() {
                this.$watch('open', value => {
                    if (!value) this.isUploading = false;
                });
                window.addEventListener('open-license-image-modal', event => {
                    this.open = true;
                });
                window.addEventListener('closeUploadModal', () => {
                    this.open = false;
                });
            },
            closeUploadModal() {
                if (!this.isUploading) {
                    this.open = false;
                }
            },
            handleFileUpload(e) {
                const self = this;
                self.isUploading = true;
                const file = e.target.files[0];
                const formData = new FormData();
                formData.append('file', file);

                // AJAX Upload with progress tracking
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/admin/upload-temp', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        self.progress = Math.round((e.loaded * 100) / e.total);
                    }
                });

                xhr.onload = () => {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Emit event to Livewire component with file details
                            if (@this.documentCategory === 'medical') {
                                @this.call('handleMedicalImageUploaded', {
                                    tempPath: response.path,
                                    originalName: file.name,
                                    size: file.size
                                });
                            } else {
                                @this.call('handleLicenseImageUploaded', {
                                    tempPath: response.path,
                                    originalName: file.name,
                                    size: file.size
                                });
                            }
                        } else {
                            alert('Error uploading file: ' + response.message);
                        }
                    } else {
                        alert('Error uploading file. Please try again.');
                    }
                    self.isUploading = false;
                    self.progress = 0;
                };

                xhr.send(formData);
            }
        };
    }

    // Solo se usa licenseUploader() para todo tipo de imágenes (licencia y médica)
</script>
