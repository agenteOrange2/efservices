<div class="p-5 overflow-x-auto">
    {{--  
    <div class="flex flex-col gap-y-2 sm:flex-row sm:items-center">
        <div>
            <!-- Barra de búsqueda -->
            <div class="relative">
                <svg class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <g clip-path="url(#clip0_15_152)">
                            <rect width="24" height="24" fill="white"></rect>
                            <circle cx="10.5" cy="10.5" r="6.5" stroke="#ababab" stroke-linejoin="round">
                            </circle>
                            <path
                                d="M19.6464 20.3536C19.8417 20.5488 20.1583 20.5488 20.3536 20.3536C20.5488 20.1583 20.5488 19.8417 20.3536 19.6464L19.6464 20.3536ZM20.3536 19.6464L15.3536 14.6464L14.6464 15.3536L19.6464 20.3536L20.3536 19.6464Z"
                                fill="#ababab"></path>
                        </g>
                        <defs>
                            <clipPath id="clip0_15_152">
                                <rect width="24" height="24" fill="white"></rect>
                            </clipPath>
                        </defs>
                    </g>
                </svg>
                <input wire:model.live.debounce.250ms="search" type="text" placeholder="Search users..."
                    class="rounded-[0.5rem] pl-9 sm:w-64 border border-gray-300 px-4 py-2 w-full">
            </div> 
        </div>
        <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
            <livewire:menu-export :exportExcel="true" :exportPdf="true" wire:ignore /> 
            <livewire:filter-popover :filterOptions="$customFilters"  wire:ignore/> 
        </div>

    </div>
    --}}
    <!-- Botón de eliminación masiva -->
    @if (count($selected) > 0)
        <button wire:click="deleteSelected" class="mt-2 mb-4 px-4 py-2 bg-red-500 text-white rounded">
            Delete Selected ({{ count($selected) }})
        </button>
    @endif

    <!-- Tabla -->
    <table class="w-full border-b border-slate-200/60">
        <thead>
            <tr>
                <th class="w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-center">
                    <input type="checkbox" wire:model.live="selectAll"
                        class="shadow-sm border-slate-200 cursor-pointer rounded transition-all duration-100 ease-in-out focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20">
                </th>
                @foreach ($columns as $column)
                    <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-start cursor-pointer"
                        wire:click="sortBy('{{ $column }}')">
                        {{ $column }}
                        @if ($sortField === $column)
                            @if ($sortDirection === 'asc')
                                <!-- Ícono de orden ascendente -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="inline h-4 w-4 ml-2">
                                    <rect width="18" height="18" x="3" y="3" rx="2" />
                                    <path d="m8 14 4-4 4 4" />
                                </svg>
                            @else
                                <!-- Ícono de orden descendente -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="inline h-4 w-4 ml-2">
                                    <rect width="18" height="18" x="3" y="3" rx="2" />
                                    <path d="m16 10-4 4-4-4" />
                                </svg>
                            @endif
                        @else
                            <!-- Ícono inactivo por defecto -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="inline h-4 w-4 ml-2 text-gray-400">
                                <rect width="18" height="18" x="3" y="3" rx="2" />
                                <path d="m16 10-4 4-4-4" />
                            </svg>
                        @endif
                    </th>
                @endforeach
                <th
                    class="w-20 px-4 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                    Actions
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($data as $item)
                <tr class="[&_td]:last:border-b-0">
                    <td class="px-5 border-b border-dashed py-4 text-center">
                        <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}"
                            class="shadow-sm border-slate-200 cursor-pointer rounded transition-all duration-100 ease-in-out focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20">
                    </td>
                    @foreach ($columns as $column)
                        <td class="px-5 border-b border-dashed py-4">
                            @if ($column === 'status')
                                @if ($item[$column] == 1)
                                    <!-- Status Activo -->
                                    <div class="flex items-center justify-start text-success text-start">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <ellipse cx="12" cy="5" rx="9" ry="3">
                                            </ellipse>
                                            <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                                            <path d="M3 12A9 3 0 0 0 21 12"></path>
                                        </svg>
                                        <div class="ml-1 whitespace-nowrap">Active</div>
                                    </div>
                                @elseif ($item[$column] == 0)
                                    <!-- Status Inactivo -->
                                    <div class="flex items-center justify-start text-danger text-start">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <ellipse cx="12" cy="5" rx="9" ry="3">
                                            </ellipse>
                                            <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                                            <path d="M3 12A9 3 0 0 0 21 12"></path>
                                        </svg>
                                        <div class="ml-1 whitespace-nowrap">Inactive</div>
                                    </div>
                                @elseif ($item[$column] == 2)
                                    <!-- Status Pending -->
                                    <div class="flex items-center justify-start text-warning text-start">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <ellipse cx="12" cy="5" rx="9" ry="3">
                                            </ellipse>
                                            <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                                            <path d="M3 12A9 3 0 0 0 21 12"></path>
                                        </svg>
                                        <div class="ml-1 whitespace-nowrap">Pending</div>
                                    </div>
                                @endif
                            @elseif ($column === 'requirement')
                                {{-- Nuevo bloque para el campo "requirement" --}}
                                @if ($item[$column] == 1)
                                    <div class="flex items-center justify-start text-success text-start">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path d="M9 12l2 2 4-4"></path>
                                        </svg>
                                        <div class="ml-1 whitespace-nowrap">Required</div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-start text-danger text-start">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path d="M9 12l2 2 4-4"></path>
                                        </svg>
                                        <div class="ml-1 whitespace-nowrap">Not Required</div>
                                    </div>
                                @endif
                            @elseif (in_array($column, ['created_at', 'updated_at']) && $item[$column])
                                <div class="whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item[$column])->format('d/m/Y') }}
                                </div>
                            @else
                                {{ $item[$column] }}
                            @endif
                        </td>
                    @endforeach

                    <td class="relative border-b border-dashed py-4 px-4">
                        <div x-data="{ openMenu: false }" class="flex items-center justify-center relative">
                            <button @click="openMenu = !openMenu" class="cursor-pointer h-5 w-5 text-slate-500">
                                <svg class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 12h14M12 5v14" />
                                </svg>
                            </button>

                            <!-- Menú desplegable -->
                            <div x-show="openMenu" @click.away="openMenu = false"
                                class="w-40 bg-white shadow rounded mt-2 absolute z-10">
                                <div class="py-2">
                                    <a href="{{ route($editRoute, $item) }}"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Edit
                                    </a>
                                    <button wire:click="deleteSingle({{ $item->id }})"
                                        class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50">
                                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="{{ count($columns) + 2 }}" class="text-center py-4">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <!-- Paginación -->
    <div class="mt-4">
        {{ $data->links('vendor.pagination.custom-pagination', ['perPageOptions' => $perPageOptions]) }}
    </div>
</div>


@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
@endPushOnce
