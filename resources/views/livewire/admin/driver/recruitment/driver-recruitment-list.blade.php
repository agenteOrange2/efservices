<div class="mt-3.5">
    <div class="box box--stacked flex flex-col">
        <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
            <!-- Buscador -->
            <div>
                <div class="relative">
                    <x-base.lucide
                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                        icon="Search" />
                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" type="text"
                        placeholder="Search for drivers..." wire:model.live.debounce.300ms="search" />
                </div>
            </div>
            <!-- Filtros -->
            <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                <x-base.form-select class="rounded-[0.5rem] sm:w-36" wire:model.live="statusFilter">
                    <option value="">All states</option>
                    @foreach($applicationStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-base.form-select>
                
                <x-base.form-select class="rounded-[0.5rem] sm:w-48" wire:model.live="carrierFilter">
                    <option value="">All carriers</option>
                    @foreach($carriers as $carrier)
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
                            Progress
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
                                    <div class="w-10 h-10 rounded-full overflow-hidden mr-3 bg-slate-100 flex items-center justify-center">
                                        @if($driver->getFirstMediaUrl('profile_photo_driver'))
                                            <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}" alt="Foto de perfil" class="w-full h-full object-cover">
                                        @else
                                            <x-base.lucide class="h-5 w-5 text-slate-500" icon="User" />
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                                        <div class="text-slate-500 text-xs">
                                            {{ $driver->middle_name ? $driver->middle_name.' ' : '' }}
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
                            <x-base.table.td class="border-dashed py-4 text-center">
                                <div class="flex items-center justify-center">
                                    @php
                                        $stepService = new App\Services\Admin\DriverStepService();
                                        $completionPercentage = $stepService->calculateCompletionPercentage($driver);
                                    @endphp
                                    <div class="w-16 h-16 rounded-full flex items-center justify-center" 
                                        style="background: conic-gradient(#3b82f6 {{ $completionPercentage }}%, #f1f5f9 0)">
                                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-sm font-medium">
                                            {{ $completionPercentage }}%
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
                                            'draft' => 'Borrador',
                                            'pending' => 'Pendiente',
                                            'approved' => 'Aprobado',
                                            'rejected' => 'Rechazado',
                                        ][$status];
                                        $statusIcon = [
                                            'draft' => 'FileEdit',
                                            'pending' => 'Clock',
                                            'approved' => 'CheckCircle',
                                            'rejected' => 'XCircle',
                                        ][$status];
                                    @endphp
                                    <div class="flex items-center justify-center px-2 py-1 rounded-full {{ $statusClass }}">
                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7] mr-1" icon="{{ $statusIcon }}" />
                                        <span class="text-xs font-medium">{{ $statusText }}</span>
                                    </div>
                                </div>
                            </x-base.table.td>
                            
                            <!-- Acciones -->
                            <x-base.table.td class="border-dashed py-4 text-center">
                                <div class="flex justify-center">
                                    <a href="{{ route('admin.driver-recruitment.show', $driver->id) }}" 
                                       class="btn btn-primary btn-sm flex items-center gap-1">
                                        <x-base.lucide class="h-4 w-4" icon="ClipboardCheck" />
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
        <div class="flex flex-col-reverse items-center gap-y-2 p-5 sm:flex-row">
            {{ $drivers->links() }}
            <x-base.form-select class="rounded-[0.5rem] sm:w-20">
                <option>10</option>
                <option>25</option>
                <option>35</option>
                <option>50</option>
            </x-base.form-select>
        </div>
    </div>
</div>