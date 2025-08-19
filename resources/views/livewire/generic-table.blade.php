<div>
<div class="p-5 overflow-x-auto">
    <div class="flex justify-between items-center mb-4">
        <div>
            <!-- Botón de eliminación masiva -->
            @if (count($selected) > 0)
                <button wire:click="confirmDeleteSelected" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
                    Delete Selected ({{ count($selected) }})
                </button>
            @endif
        </div>
    
    </div>

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
                                @elseif ($item[$column] == 3)
                                    <!-- Status Pending Validation -->
                                    <div class="flex items-center justify-start text-info text-start">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 6v6l4 2"></path>
                                        </svg>
                                        <div class="ml-1 whitespace-nowrap">Pending Validation</div>
                                    </div>
                                @else
                                    <!-- Status desconocido -->
                                    <div class="flex items-center justify-start text-slate-500 text-start">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                            <path d="M12 17h.01"></path>
                                        </svg>
                                        <div class="ml-1 whitespace-nowrap">Unknown ({{ $item[$column] }})</div>
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
                                    @if($showRoute)
                                    <button wire:click="showRecord({{ $item->id }})"
                                        class="flex w-full items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 0 0 16 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </button>
                                    @endif
                                    @if($showSlugRoute)
                                    <button wire:click="showSlugRecord('{{ $item->slug }}')"
                                        class="flex w-full items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 0 0 16 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View Details
                                    </button>
                                    @endif
                                    <a href="{{ route($editRoute, $item) }}"
                                        class="flex w-full items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <button wire:click="confirmDeleteSingle({{ $item->id }})"
                                        class="flex w-full items-center px-4 py-2 text-red-600 hover:bg-red-50">
                                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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

<!-- Modal de confirmación para eliminación individual -->
<div x-data="{ open: false }" 
     x-on:opendeleteconfirmation.window="open = true"
     x-on:opendeleteconfirmation.window="console.log('Modal activado')"
     x-on:closemodal.window="open = false"
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Record</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this record? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="deleteSingle({{ $recordToDelete ?? '' }})" x-on:click="open = false" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Delete
                </button>
                <button x-on:click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminación múltiple -->
<div x-data="{ open: false }" 
     x-on:opendeleteconfirmationmultiple.window="open = true" 
     x-on:closemodal.window="open = false"
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Selected Records</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete the selected records? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="deleteSelected" x-on:click="open = false" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Delete All Selected
                </button>
                <button x-on:click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar detalles de un registro -->
<div x-data="{ open: false, record: {}, columns: [] }" 
     x-on:showrecorddetail.window="open = true; record = $event.detail.record; columns = $event.detail.columns"
     x-on:closemodal.window="open = false"
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="mt-3 sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Record Details</h3>
                        <div class="mt-4 space-y-3 max-h-96 overflow-y-auto">
                            <template x-for="column in columns" :key="column">
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="font-semibold text-gray-700 capitalize" x-text="column"></div>
                                    <div class="col-span-2 text-gray-600" x-text="record[column] !== null ? record[column] : 'N/A'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button x-on:click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

</div>
