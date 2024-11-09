<div class="p-5">
    <!-- Barra de búsqueda -->
    <div class="relative">
        <svg class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 10l6 6m-6-6l6-6m-2.828 6.828a4 4 0 110 5.656 4 4 0 010-5.656z" />
        </svg>
        <input wire:model.live.debounce.250ms="search" type="text" placeholder="Search users..." class="rounded-[0.5rem] pl-9 sm:w-64 border border-gray-300 px-4 py-2">
    </div>

    <!-- Botón de eliminación masiva -->
    @if(count($selected) > 0)
        <button wire:click="deleteSelected" class="mt-2 mb-4 px-4 py-2 bg-red-500 text-white rounded">
            Delete Selected ({{ count($selected) }})
        </button>
    @endif

    <!-- Tabla -->
    <table class="w-full border-b border-slate-200/60 mt-10">
        <thead>
            <tr>
                <th class="w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-center">
                    <input type="checkbox" wire:model="selectAll" class="shadow-sm border-slate-200 cursor-pointer rounded transition-all duration-100 ease-in-out focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20">
                </th>
                @foreach ($columns as $column)
                    <th class="px-4 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 text-left">
                        {{ $column }}
                    </th>
                @endforeach
                <th class="w-20 px-4 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                    Actions
                </th>
            </tr>
        </thead>

        <tbody>
            @foreach ($data as $item)
                <tr class="hover:bg-gray-50">
                    <td class="border-dashed py-4 px-4 text-center">
                        <input type="checkbox" wire:model="selected" value="{{ $item->id }}" class="shadow-sm border-slate-200 cursor-pointer rounded transition-all duration-100 ease-in-out focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20">
                    </td>
                    
                    @foreach ($columns as $column)
                        <td class="border-dashed py-4 px-4">
                            {{ $item[$column] }}
                        </td>
                    @endforeach

                    <td class="relative border-dashed py-4 px-4">
                        <div class="flex items-center justify-center relative">
                            <!-- Botón de menú -->
                            <button wire:click="toggleMenu({{ $item->id }})" class="cursor-pointer h-5 w-5 text-slate-500">
                                <svg class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5v14" />
                                </svg>
                            </button>
                            
                            <!-- Menú desplegable -->
                            @if (!empty($openMenu[$item->id]))
                                <div class="w-40 bg-white shadow rounded mt-2 absolute z-10">
                                    <div class="py-2">
                                        <a href="{{ route('admin.users.edit', $item['id']) }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Edit
                                        </a>
                                        <button wire:click="deleteSingle({{ $item->id }})" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50">
                                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Paginación -->
    <div class="mt-4">
        {{ $data->links('vendor.pagination.custom-pagination') }}        
    </div>
</div>
