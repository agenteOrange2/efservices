<div>
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="box box--stacked">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div>
                        <!-- Búsqueda -->
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
                                placeholder="Search carriers..."
                                class="disabled:bg-slate-100 disabled:cursor-not-allowed [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 rounded-[0.5rem] pl-9 sm:w-64">
                        </div>
                    </div>

                    <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                        <!-- Filtro de estado -->
                        <div x-data="{ open: $wire.entangle('openPopover').live }" class="relative inline-block w-full">
                            <!-- Botón para abrir/cerrar el popover -->
                            <button @click="open = !open"
                                class="w-full sm:w-auto flex items-center justify-between border rounded-md px-4 py-2">
                                <span class="flex items-center">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m3 16 4 4 4-4" />
                                        <path d="M7 20V4" />
                                        <path d="M11 4h4" />
                                        <path d="M11 8h7" />
                                        <path d="M11 12h10" />
                                    </svg>
                                    Filter
                                </span>
                            </button>

                            <!-- Panel de filtros -->
                            <div x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2" @click.away="open = false"
                                class="dropdown-menu absolute left-0 bg-white border rounded-md shadow-lg mt-2 w-72 z-10">
                                <!-- Contenido del popover -->
                                <div class="p-4">
                                    <!-- Rango de fechas con Litepicker -->
                                    <label for="date-range-picker" class="block font-medium text-sm text-gray-700">Date
                                        Range</label>
                                    <div class="flex gap-2 mt-2">
                                        <input id="date-range-picker" type="text"
                                            class="datepicker mx-auto block w-full rounded border-gray-300"
                                            placeholder="Select a date range" />
                                    </div>
                                    <!-- Filtro de estado -->
                                    <select wire:model.live="filters.status" class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 flex-1">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="pending">Pending</option>
                                    </select>

                                    <!-- Botón para limpiar filtros -->
                                    <button wire:click="resetFilters"
                                        class="mt-4 bg-red-500 text-white px-4 py-2 rounded w-full">
                                        Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-auto xl:overflow-visible">
                    <x-base.table class="border-b border-slate-200/60">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.td wire:click="sortBy('name')" class="cursor-pointer"
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 cursor-pointer">
                                    Carrier Name
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    User Carrier
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Progress
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                    Status
                                </x-base.table.td>
                                <x-base.table.td wire:click="sortBy('created_at')"
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500 cursor-pointer">
                                    Joined Date
                                </x-base.table.td>
                                <x-base.table.td
                                    class="w-20 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                    Action
                                </x-base.table.td>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @forelse ($carriers as $carrier)
                                <x-base.table.tr>
                                    <x-base.table.td class="border-dashed py-4">
                                        <div class="flex items-center">
                                            <div class="image-fit zoom-in h-9 w-9">
                                                <img class="rounded-full shadow-md"
                                                    src="{{ $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/default_profile.png') }}"
                                                    alt="Logo {{ $carrier->name }}">
                                            </div>
                                            <div class="ml-3.5">
                                                <a class="whitespace-nowrap font-medium text-primary hover:underline"
                                                    href="{{ route('admin.carrier.documents', $carrier->slug) }}">
                                                    {{ $carrier->name }}
                                                </a>
                                                <div class="text-xs text-slate-500">Carrier</div>
                                            </div>
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        <a class="whitespace-nowrap font-medium text-primary hover:underline">
                                            {{ optional($carrier->userCarriers->first())->name ?? 'N/A' }}
                                        </a>
                                    </x-base.table.td>

                                    <x-base.table.td class="border-dashed py-4">
                                        <div class="w-40">
                                            <div class="text-xs text-slate-500">
                                                {{ round($carrier->completion_percentage) }}%</div>
                                            <div class="mt-1.5 flex h-1 rounded-sm border bg-slate-50">
                                                <div class="first:rounded-l-sm last:rounded-r-sm border border-primary/20 -m-px bg-primary/40"
                                                    style="width: {{ $carrier->completion_percentage }}%;"></div>
                                            </div>
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        <div class="flex items-center justify-center">
                                            @if ($carrier->document_status == 'active')
                                                <span
                                                    class="px-3 py-1 text-sm font-semibold text-green-700 bg-green-100 rounded-full">Active</span>
                                            @elseif ($carrier->document_status == 'pending')
                                                <span
                                                    class="px-3 py-1 text-sm font-semibold text-yellow-700 bg-yellow-100 rounded-full">Pending</span>
                                            @else
                                                <span
                                                    class="px-3 py-1 text-sm font-semibold text-red-700 bg-red-100 rounded-full">Inactive</span>
                                            @endif
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        <div class="text-xs text-slate-500">{{ $carrier->created_at->format('d M Y') }}
                                        </div>
                                    </x-base.table.td>

                                    <x-base.table.td>
                                        <div class="flex items-center justify-center">
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" @click.outside="open = false"
                                                    class="cursor-pointer h-5 w-5 text-slate-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-more-vertical stroke-[1] h-5 w-5 stroke-current">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>

                                                <div x-show="open"
                                                    class="absolute z-10 w-90 mt-2 bg-white border border-gray-200 rounded shadow-lg">
                                                    <a href="{{ route('admin.carrier.admin_documents.review', $carrier->slug) }}"
                                                        class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dropdown-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                            height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-edit3 stroke-[1] mr-2 h-4 w-4">
                                                            <path d="M12 20h9"></path>
                                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z">
                                                            </path>
                                                        </svg>
                                                        Review Documents
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </x-base.table.td>

                                </x-base.table.tr>
                            @empty
                                <x-base.table.td>
                                    No hay registros
                                </x-base.table.td>
                            @endforelse

                        </x-base.table.tbody>
                    </x-base.table>
                </div>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $carriers->links() }}
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
                const litepickerInstance = datePicker._litepicker;
                if (litepickerInstance) {
                    litepickerInstance.clearSelection(); // Limpiar el rango de fechas seleccionado
                }
                new Litepicker({
                    element: datePicker,
                    singleMode: false,
                    format: 'YYYY-MM-DD',
                    autoApply: true,
                    dropdowns: {
                        minYear: 2000,
                        maxYear: new Date().getFullYear(),
                        months: true,
                        years: true,
                    },
                    setup: (picker) => {
                        picker.on('selected', (startDate, endDate) => {
                            console.log('Enviando evento a Livewire:', {
                                start: startDate.format('YYYY-MM-DD'),
                                end: endDate.format('YYYY-MM-DD'),
                            });
                            Livewire.dispatch('updateDateRange', {
                                dates: {
                                    start: startDate.format('YYYY-MM-DD'),
                                    end: endDate.format('YYYY-MM-DD'),
                                }
                            });
                        });
                    },
                });
            }
        });
    </script>
@endPushOnce
