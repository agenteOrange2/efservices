<div class="mt-3.5">
    <div class="box box--stacked flex flex-col">
        <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
            <!-- Buscador -->
            <div>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        data-lucide="search"
                        class="lucide lucide-search absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" type="text"
                        placeholder="Search for drivers..." wire:model.live.debounce.300ms="search" />
                </div>
            </div>
            <!-- Filtros -->
            <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                <x-base.form-select class="rounded-[0.5rem] sm:w-36" wire:model.live="statusFilter">
                    <option value="">All states</option>
                    @foreach ($applicationStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-base.form-select>

                <x-base.form-select class="rounded-[0.5rem] sm:w-48" wire:model.live="carrierFilter">
                    <option value="">All carriers</option>
                    @foreach ($carriers as $carrier)
                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                    @endforeach
                </x-base.form-select>
            </div>
        </div>

        <!-- Tabla de conductores -->
        <div class="overflow-auto xl:overflow-visible">
            <x-base.table class="border-b border-slate-200/60">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.td
                            class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                            Driver
                        </x-base.table.td>
                        <x-base.table.td
                            class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                            Contact
                        </x-base.table.td>
                        <x-base.table.td
                            class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                            Carrier
                        </x-base.table.td>
                        <x-base.table.td
                            class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-center">
                            Verification progress
                        </x-base.table.td>
                        <x-base.table.td
                            class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-center">
                            Status
                        </x-base.table.td>
                        <x-base.table.td
                            class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-center">
                            Actions
                        </x-base.table.td>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @forelse($drivers as $driver)
                        <x-base.table.tr class="[&_td]:last:border-b-0">
                            <!-- Información del conductor -->
                            <x-base.table.td class="border-dashed py-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 rounded-full overflow-hidden mr-3 bg-slate-100 flex items-center justify-center">
                                        @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                                            <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}"
                                                alt="Foto de perfil" class="w-full h-full object-cover">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                data-lucide="user"
                                                class="lucide lucide-user stroke-[1] h-5 w-5 text-slate-500">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $driver->user->name }} {{ $driver->last_name }}
                                        </div>
                                        <div class="text-slate-500 text-xs">
                                            {{ $driver->middle_name ? $driver->middle_name . ' ' : '' }}
                                            Apply: {{ $driver->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </x-base.table.td>

                            <!-- Contacto -->
                            <x-base.table.td class="border-dashed py-4">
                                <div class="flex flex-col text-sm">
                                    <span>{{ $driver->user->email }}</span>
                                    <span>{{ $driver->phone }}</span>
                                </div>
                            </x-base.table.td>

                            <!-- Transportista -->
                            <x-base.table.td class="border-dashed py-4">
                                <div class="font-medium">{{ $driver->carrier->name ?? 'N/A' }}</div>
                            </x-base.table.td>

                            <!-- Avance -->
                            <x-base.table.td class="border-dashed py-4 text-center" wire:poll.visible.3s>
                                {{-- <div class="flex items-center justify-center" wire:key="progress-{{ $driver->id }}">
                                    @php
                                        // Usar el método dedicado del componente para calcular el porcentaje
                                        $checklistData = $this->getChecklistPercentage($driver);
                                        $checklistPercentage = $checklistData['percentage'];
                                    @endphp
                                    
                                    <!-- Implementación alternativa del círculo de progreso -->
                                    <div class="relative w-20 h-20">
                                        <!-- Círculo base gris (fondo) -->
                                        <div class="absolute inset-0 rounded-full bg-slate-100"></div>
                                        
                                        <!-- Círculo de progreso (animado para asegurar renderizado) -->
                                        <div class="absolute inset-0">
                                            <svg class="w-20 h-20" viewBox="0 0 36 36">
                                                <path
                                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                    fill="none"
                                                    stroke="#3b82f6"
                                                    stroke-width="3"
                                                    stroke-dasharray="{{ $checklistPercentage }}, 100"
                                                />
                                            </svg>
                                        </div>
                                        
                                        <!-- Texto central con porcentaje -->
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="text-sm font-medium">{{ $checklistPercentage }}%</div>
                                        </div>
                                    </div>
                                </div> --}}
                                <div class="flex items-center justify-center" wire:key="progress-{{ $driver->id }}">
                                    @php
                                        // Usar el método dedicado del componente para calcular el porcentaje
                                        $checklistData = $this->getChecklistPercentage($driver);
                                        $checklistPercentage = $checklistData['percentage'];
                                    @endphp
                                    <div class="w-16 h-16 rounded-full flex items-center justify-center"
                                        style="background: conic-gradient(#3b82f6 {{ $checklistPercentage }}%, #f1f5f9 0)">
                                        <div
                                            class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-sm font-medium">
                                            {{ $checklistPercentage }}%
                                        </div>
                                    </div>
                                </div>
                            </x-base.table.td>

                            <!-- Estado -->
                            <x-base.table.td class="border-dashed py-4 text-center">
                                <div class="flex justify-center">
                                    @php
                                        $status = $driver->application->status ?? 'draft';
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
                                        $statusIconMap = [
                                            'draft' => 'FileEdit',
                                            'pending' => 'Clock',
                                            'approved' => 'CheckCircle',
                                            'rejected' => 'XCircle',
                                        ];
                                        
                                        $iconPaths = [
                                            'FileEdit' => '<path d="M20 11.5v-2a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10c0 1.1.9 2 2 2h10a2 2 0 0 0 2-2v-2"></path><path d="M17.42 15.61a2.1 2.1 0 1 1 2.97 2.97L16.95 22 13 21l.99-3.95 3.43-3.44Z"></path>',
                                            'Clock' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
                                            'CheckCircle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>',
                                            'XCircle' => '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>',
                                        ];
                                        
                                        $currentIcon = $statusIconMap[$status] ?? 'FileEdit';
                                        $iconPath = $iconPaths[$currentIcon] ?? '';
                                    @endphp
                                    <div
                                        class="flex items-center justify-center px-2 py-1 rounded-full {{ $statusClass }}">
                                        {{-- <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1"
                                            icon="{{ $statusIcon }}" /> --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 stroke-[1.7] mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                {!! $iconPath !!}
                                            </svg>
                                        <span class="text-xs font-medium">{{ $statusText }}</span>
                                    </div>
                                </div>
                            </x-base.table.td>

                            <!-- Acciones -->
                            <x-base.table.td class="border-dashed py-4 text-center">
                                <div class="flex justify-center">
                                    <a href="{{ route('admin.driver-recruitment.show', $driver->id) }}"
                                        class="btn btn-primary btn-sm flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" data-lucide="clipboard-check"
                                            class="lucide lucide-clipboard-check stroke-[1] h-4 w-4">
                                            <rect width="8" height="4" x="8" y="2" rx="1"
                                                ry="1"></rect>
                                            <path
                                                d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2">
                                            </path>
                                            <path d="m9 14 2 2 4-4"></path>
                                        </svg>
                                        Review
                                    </a>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @empty
                        <x-base.table.tr>
                            <x-base.table.td colspan="6" class="border-dashed py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500">
                                    <x-base.lucide class="h-12 w-12 mb-2 text-slate-300" icon="UserX" />
                                    <p>No driver applications were found</p>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @endforelse
                </x-base.table.tbody>
            </x-base.table>
        </div>
        <!-- Paginación -->
        <div class="w-full">
            {{ $drivers->links('custom.livewire-pagination') }}
        </div>
    </div>
</div>
