<div x-data="{ open: $wire.entangle('openPopover').live }" class="relative inline-block w-full">
    <!-- Botón para abrir/cerrar el popover -->
    <button @click="open = !open" class="w-full sm:w-auto flex items-center justify-between border rounded-md px-4 py-2">
        <span class="flex items-center">
            <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
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
            <label for="date-range-picker" class="block font-medium text-sm text-gray-700">Date Range</label>
            <div class="flex gap-2 mt-2">
                <input id="date-range-picker" type="text"
                    class="datepicker mx-auto block w-full rounded border-gray-300" placeholder="Select a date range" />
            </div>

            <!-- Filtros personalizados -->
            @foreach ($filterOptions as $key => $option)
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">{{ $option['label'] }}</label>
                    @if ($option['type'] === 'select')
                        <select wire:model.live="filters.{{ $key }}"
                            class="w-full rounded border-gray-300 mt-2">
                            <option value="">{{ $option['placeholder'] ?? 'Select an option' }}</option>
                            @foreach ($option['options'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    @elseif ($option['type'] === 'input')
                        <input type="text" wire:model.live="filters.{{ $key }}"
                            class="w-full rounded border-gray-300 mt-2"
                            placeholder="{{ $option['placeholder'] ?? '' }}">
                    @endif
                </div>
            @endforeach

            <!-- Botón para limpiar filtros -->
            <button wire:click="clearFilters" class="mt-4 bg-red-500 text-white px-4 py-2 rounded w-full">
                Clear Filters
            </button>
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
