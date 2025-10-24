<div>
    <!-- Analytics Cards -->
    @if(isset($analytics))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Carriers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $analytics['total_carriers'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Carriers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $analytics['active_carriers'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Carriers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $analytics['pending_carriers'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completion Rate</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $analytics['completion_rate'] }}%</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="intro-y box p-5 ">
        <div class="text-slate-500 text-xs">Review and manage carrier documents. Use the filters to find specific carriers and check their document status.</div>
    </div>
    <div class="grid grid-cols-12 gap-x-6 gap-y-10 mt-5">
        <div class="col-span-12">
            <div class="box box--stacked">                
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div class="flex-1 ">
                        <!-- Búsqueda mejorada -->
                        <div class="relative">
                            <svg class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="10.5" cy="10.5" r="6.5" stroke="#ababab" stroke-linejoin="round">
                                </circle>
                                <path
                                    d="m15.3536 14.6464 4.2938 4.2939c.1953.1953.5118.1953.7071.7071-.1953-.1953-.5118-.1953-.7071 0l-4.2939-4.2938"
                                    stroke="#ababab" fill="#ababab"></path>
                            </svg>
                            <input type="text" wire:model.live.debounce.500ms="search"
                                placeholder="Buscar por nombre, email, teléfono o usuario..."
                                class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 rounded-[0.5rem] pl-9 sm:w-80">
                        </div>
                    </div>

                    <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                        <!-- Selector de registros por página -->                        
                        <!-- Botones de exportación -->
                        <div class="flex gap-2">
                            <button wire:click="exportData('excel')" 
                                class="flex items-center px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Excel
                            </button>
                            <button wire:click="exportData('pdf')" 
                                class="flex items-center px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                PDF
                            </button>
                        </div>

                        <!-- Filtros avanzados -->
                        <div x-data="{ open: $wire.entangle('openPopover').live }" class="relative inline-block w-full">
                            <!-- Botón para abrir/cerrar el popover -->
                            <button @click="open = !open"
                                class="w-full sm:w-auto flex items-center justify-between border rounded-md px-4 py-2 hover:bg-gray-50 transition-colors">
                                <span class="flex items-center">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m3 16 4 4 4-4" />
                                        <path d="M7 20V4" />
                                        <path d="M11 4h4" />
                                        <path d="M11 8h7" />
                                        <path d="M11 12h10" />
                                    </svg>
                                    Filtros
                                    @if($filters['status'] || $filters['date_range']['start'] || $filters['expiring_soon'])
                                        <span class="ml-2 px-2 py-1 text-xs bg-primary text-white rounded-full">
                                            Activos
                                        </span>
                                    @endif
                                </span>
                            </button>

                            <!-- Panel de filtros mejorado -->
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2" @click.away="open = false"
                                class="dropdown-menu absolute right-0 bg-white border rounded-md shadow-lg mt-2 w-80 z-50">
                                <div class="p-4 space-y-4">
                                    <h3 class="font-medium text-gray-900">Filtros Avanzados</h3>
                                    
                                    <!-- Filtro de estado -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                        <select wire:model.live="filters.status" class="w-full text-sm border-slate-200 shadow-sm rounded-md">
                                            <option value="">Todos los estados</option>
                                            <option value="active">Activo (Completo)</option>
                                            <option value="pending">Pendiente</option>
                                            <option value="incomplete">Incompleto</option>
                                        </select>
                                    </div>

                                    <!-- Rango de fechas -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Rango de Fechas</label>
                                        <input id="date-range-picker" type="text"
                                            class="w-full text-sm border-slate-200 shadow-sm rounded-md"
                                            placeholder="Seleccionar rango de fechas" />
                                    </div>

                                    <!-- Documentos que expiran pronto -->
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model.live="filters.expiring_soon" 
                                            class="rounded border-gray-300 text-primary focus:ring-primary" id="expiring-soon">
                                        <label for="expiring-soon" class="ml-2 text-sm text-gray-700">
                                            Documentos que expiran en 30 días
                                        </label>
                                    </div>

                                    <!-- Tipos de documentos -->
                                    @if(isset($documentTypes) && $documentTypes->count() > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipos de Documentos</label>
                                        <div class="space-y-2 max-h-32 overflow-y-auto">
                                            @foreach($documentTypes as $docType)
                                            <div class="flex items-center">
                                                <input type="checkbox" wire:model.live="filters.document_types" 
                                                    value="{{ $docType->id }}" 
                                                    class="rounded border-gray-300 text-primary focus:ring-primary" 
                                                    id="doc-type-{{ $docType->id }}">
                                                <label for="doc-type-{{ $docType->id }}" class="ml-2 text-sm text-gray-700">
                                                    {{ $docType->name }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Botones de acción -->
                                    <div class="flex gap-2 pt-2 border-t">
                                        <button wire:click="resetFilters" @click="open = false"
                                            class="flex-1 bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 transition-colors">
                                            Limpiar Filtros
                                        </button>
                                        <button @click="open = false"
                                            class="flex-1 bg-primary text-white px-4 py-2 rounded text-sm hover:bg-primary-dark transition-colors">
                                            Aplicar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tabla responsiva -->
                <div class="overflow-auto xl:overflow-visible">
                    <!-- Vista de escritorio -->
                    <div class="hidden md:block">
                        <x-base.table class="border-b border-slate-200/60">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.td wire:click="sortBy('name')" 
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 cursor-pointer hover:bg-slate-100 transition-colors">
                                        <div class="flex items-center">
                                            Carrier Name
                                            @if($sortField === 'name')
                                                @if($sortDirection === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">                                        
                                        User Carrier
                                    </x-base.table.td>
                                    <x-base.table.td wire:click="sortBy('completion_percentage')"
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 cursor-pointer hover:bg-slate-100 transition-colors">
                                        <div class="flex items-center">
                                            Progress
                                            @if($sortField === 'completion_percentage')
                                                @if($sortDirection === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                        Status
                                    </x-base.table.td>
                                    <x-base.table.td wire:click="sortBy('created_at')"
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 cursor-pointer hover:bg-slate-100 transition-colors">
                                        <div class="flex items-center">
                                            Register Date
                                            @if($sortField === 'created_at')
                                                @if($sortDirection === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="w-20 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                        Actions
                                    </x-base.table.td>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @forelse ($carriers as $carrier)
                                <x-base.table.tr class="hover:bg-slate-50 transition-colors">
                                    <x-base.table.td class="border-dashed py-4">
                                        <div class="flex items-center">
                                            <div class="image-fit zoom-in h-10 w-10">
                                                <img class="rounded-full shadow-md border-2 border-white"
                                                    src="{{ $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/default_profile.png') }}"
                                                    alt="Logo {{ $carrier->name }}">
                                            </div>
                                            <div class="ml-3.5">
                                                <a class="whitespace-nowrap font-medium text-primary hover:underline"
                                                    href="{{ route('admin.carrier.documents', $carrier->slug) }}">
                                                    {{ $carrier->name }}
                                                </a>                                                
                                                @if($carrier->expiring_documents > 0)
                                                    <div class="text-xs text-red-600 font-medium">
                                                        ⚠️ {{ $carrier->expiring_documents }} doc(s) expiring soon
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        @if($carrier->userCarriers->first())
                                            <div>
                                                <a class="whitespace-nowrap font-medium text-primary hover:underline">
                                                    {{ $carrier->userCarriers->first()->user->name ?? 'N/A' }}
                                                </a>
                                                <div class="text-xs text-slate-500">
                                                    {{ $carrier->userCarriers->first()->user->email ?? '' }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Unassigned</span>
                                        @endif
                                    </x-base.table.td>

                                    <x-base.table.td class="border-dashed py-4">
                                        <div class="w-48">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-xs text-slate-500">
                                                    {{ $carrier->completion_percentage }}%
                                                </span>
                                                <span class="text-xs text-slate-500">
                                                    {{ $carrier->documents_summary['approved'] }}/{{ $carrier->documents_summary['total'] }}
                                                </span>
                                            </div>
                                            <div class="flex h-2 rounded-full border bg-slate-50 overflow-hidden">
                                                <div class="bg-gradient-to-r from-green-400 to-green-600 transition-all duration-500 ease-out"
                                                    style="width: {{ $carrier->completion_percentage }}%;"></div>
                                            </div>
                                            <div class="flex justify-between text-xs text-slate-400 mt-1">
                                                <span>Approved: {{ $carrier->documents_summary['approved'] }}</span>
                                                <span>Pending: {{ $carrier->documents_summary['pending'] }}</span>
                                            </div>
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        <div class="flex items-center justify-center">
                                            @if ($carrier->document_status == 'active')
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold text-green-700 bg-green-100 rounded-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Active
                                            </span>
                                            @elseif ($carrier->document_status == 'pending')
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-700 bg-yellow-100 rounded-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Pending
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold text-red-700 bg-red-100 rounded-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Incomplete
                                            </span>
                                            @endif
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        <div class="text-sm text-slate-600">
                                            {{ $carrier->created_at->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ $carrier->created_at->diffForHumans() }}
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        <div class="flex items-center justify-center">
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" @click.outside="open = false"
                                                    class="cursor-pointer h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="text-slate-500">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>

                                                <div x-show="open" x-transition
                                                    class="absolute right-0 z-10 w-48 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg">
                                                    <div class="py-1">
                                                        <a href="{{ route('admin.carrier.admin_documents.review', $carrier->slug) }}"
                                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                            </svg>
                                                            Review Documents
                                                        </a>
                                                        <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                            View Documents
                                                        </a>
                                                        <button wire:click="viewCarrierDocuments({{ $carrier->id }})"
                                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                            </svg>
                                                            Manage Carrier
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                                @empty
                                <x-base.table.tr>
                                    <x-base.table.td colspan="6" class="text-center py-12">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">There are no registered carriers.</h3>
                                            <p class="text-gray-500 text-center max-w-sm">
                                                No carriers found that match the current search criteria.
                                            </p>
                                            @if($search || array_filter($filters))
                                            <button wire:click="resetFilters" class="mt-4 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                                                Clear Filters
                                            </button>
                                            @endif
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                                @endforelse
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>

                    <!-- Vista móvil (tarjetas) -->
                    <div class="md:hidden space-y-4 p-4">
                        @forelse ($carriers as $carrier)
                        <div class="bg-white border rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-3">
                                <div class="image-fit h-12 w-12">
                                    <img class="rounded-full shadow-md border-2 border-white"
                                        src="{{ $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/default_profile.png') }}"
                                        alt="Logo {{ $carrier->name }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">
                                            {{ $carrier->name }}
                                        </h3>
                                        @if ($carrier->document_status == 'active')
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">
                                            Active
                                        </span>
                                        @elseif ($carrier->document_status == 'pending')
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">
                                            Pending
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">
                                            Incomplete
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $carrier->email ?? 'Sin email' }}
                                    </p>
                                    
                                    @if($carrier->userCarriers->first())
                                    <p class="text-xs text-gray-500">
                                        User: {{ $carrier->userCarriers->first()->user->name ?? 'N/A' }}
                                    </p>
                                    @endif

                                    <!-- Progreso -->
                                    <div class="mt-3">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs text-gray-500">Progress</span>
                                            <span class="text-xs font-medium">{{ $carrier->completion_percentage }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-500"
                                                style="width: {{ $carrier->completion_percentage }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                                            <span>{{ $carrier->documents_summary['approved'] }}/{{ $carrier->documents_summary['total'] }}</span>
                                            <span>{{ $carrier->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="flex space-x-2 mt-3">
                                        <a href="{{ route('admin.carrier.admin_documents.review', $carrier->slug) }}"
                                            class="flex-1 text-center px-3 py-1 text-xs bg-primary text-white rounded hover:bg-primary-dark transition-colors">
                                            Review
                                        </a>
                                        <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                            class="flex-1 text-center px-3 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                                            View Docs
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No carriers found</h3>
                            <p class="text-gray-500 text-sm">No carriers found that match your search.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Paginación mejorada -->
                    @if($carriers->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                @if ($carriers->onFirstPage())
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md">
                                        Previous
                                    </span>
                                @else
                                    <button wire:click="previousPage" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Previous
                                    </button>
                                @endif

                                @if ($carriers->hasMorePages())
                                    <button wire:click="nextPage" class="ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Next
                                    </button>
                                @else
                                    <span class="ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md">
                                        Next
                                    </span>
                                @endif
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{ $carriers->firstItem() }}</span>
                                        a
                                        <span class="font-medium">{{ $carriers->lastItem() }}</span>
                                        de
                                        <span class="font-medium">{{ $carriers->total() }}</span>
                                        resultadosresults
                                    </p>
                                </div>
                                <div>
                                    {{ $carriers->links('custom.livewire-pagination') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <!-- Loading Overlay -->
                <div wire:loading class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50 rounded-lg">
                    <div class="flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        <span class="text-gray-600">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('styles')
@vite('resources/css/vendors/litepicker.css')
@endPushOnce

@pushOnce('vendors')
@vite('resources/js/vendors/dayjs.js')
@vite('resources/js/vendors/litepicker.js')
@endPushOnce

@pushOnce('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const datePicker = document.getElementById('date-range-picker');

        if (datePicker) {
            // Limpiar instancia existente si existe
            if (datePicker._litepicker) {
                datePicker._litepicker.destroy();
            }

            const litepickerInstance = new Litepicker({
                element: datePicker,
                singleMode: false,
                format: 'YYYY-MM-DD',
                autoApply: true,
                showTooltip: true,
                tooltipText: {
                    one: 'día',
                    other: 'días'
                },
                dropdowns: {
                    minYear: 2000,
                    maxYear: new Date().getFullYear(),
                    months: true,
                    years: true,
                },
                buttonText: {
                    apply: 'Aplicar',
                    cancel: 'Cancelar',
                    previousMonth: 'Mes anterior',
                    nextMonth: 'Mes siguiente',
                },
                setup: (picker) => {
                    picker.on('selected', (startDate, endDate) => {
                        if (startDate && endDate) {
                            console.log('Rango seleccionado:', {
                                start: startDate.format('YYYY-MM-DD'),
                                end: endDate.format('YYYY-MM-DD'),
                            });
                            
                            // Enviar evento a Livewire
                            Livewire.dispatch('updateDateRange', {
                                dates: {
                                    start: startDate.format('YYYY-MM-DD'),
                                    end: endDate.format('YYYY-MM-DD'),
                                }
                            });
                        }
                    });

                    picker.on('clear', () => {
                        console.log('Fechas limpiadas');
                        Livewire.dispatch('updateDateRange', {
                            dates: {
                                start: null,
                                end: null,
                            }
                        });
                    });
                },
            });

            // Guardar referencia para poder destruir después
            datePicker._litepicker = litepickerInstance;
        }
    });

    // Escuchar eventos de Livewire para actualizar el picker
    document.addEventListener('livewire:navigated', function() {
        // Reinicializar el date picker después de navegación
        setTimeout(() => {
            const datePicker = document.getElementById('date-range-picker');
            if (datePicker && !datePicker._litepicker) {
                // Reinicializar si no existe
                window.dispatchEvent(new Event('DOMContentLoaded'));
            }
        }, 100);
    });
</script>
@endPushOnce