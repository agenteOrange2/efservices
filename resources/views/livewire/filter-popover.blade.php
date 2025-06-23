<div x-data="{ open: $wire.entangle('openPopover').live }" class="relative inline-block w-full">
    <!-- Botón para abrir/cerrar el popover -->
    <button @click="open = !open" class="w-full sm:w-auto flex items-center justify-between border rounded-md px-4 py-2 {{ $buttonClass }}">
        <span class="flex items-center">
            <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="m3 16 4 4 4-4" />
                <path d="M7 20V4" />
                <path d="M11 4h4" />
                <path d="M11 8h7" />
                <path d="M11 12h10" />
            </svg>
            {{ $buttonLabel }}
            @if(isset($activeFiltersCount) && $activeFiltersCount > 0)
                <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-600 rounded-full">{{ $activeFiltersCount }}</span>
            @endif
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
        class="dropdown-menu absolute left-0 bg-white border rounded-md shadow-lg mt-2 w-72 z-10 {{ $popoverClass }}">
        <!-- Contenido del popover -->
        <div class="p-4">
            <!-- Rango de fechas con Litepicker -->
            @if($showDateRange)
                <label for="date-range-picker" class="block font-medium text-sm text-gray-700">{{ $dateRangeLabel }}</label>
                <div class="flex gap-2 mt-2">
                    <input id="date-range-picker" type="text"
                        class="datepicker mx-auto block w-full rounded border-gray-300" placeholder="Select a date range" />
                </div>
            @endif
            
            <!-- Filtro de estado -->
            @if($showStatus && !empty($statusOptions))
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">{{ $statusLabel }}</label>
                    <select wire:model.live="status" class="w-full rounded border-gray-300 mt-2">
                        <option value="">All statuses</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

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

            <!-- Botón para aplicar filtros si no es automático -->
            @if(!$applyFilterImmediately)
                <button wire:click="applyFilters" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full transition-colors">
                    Apply Filters
                </button>
            @endif

            <!-- Botón para limpiar filtros -->
            @if($showClearButton)
                <button wire:click="clearFilters" class="mt-4 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded w-full transition-colors">
                    {{ $clearButtonLabel }}
                </button>
            @endif
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
        // Función para inicializar el datepicker
        function initializeDatepicker() {
            const datePicker = document.getElementById('date-range-picker');
            const dateFormat = @js($datePickerFormat) || 'm/d/Y';
            
            if (!datePicker) return;                                
            // Limpiar instancia existente si hay una
            if (datePicker._litepicker) {
                datePicker._litepicker.destroy();
            }
            
            // Inicializar nueva instancia con opciones configurables
            const picker = new Litepicker({
                element: datePicker,
                singleMode: false,
                format: dateFormat,
                autoApply: true,
                dropdowns: {
                    minYear: 2000,
                    maxYear: new Date().getFullYear(),
                    months: true,
                    years: true,
                },
                setup: (picker) => {
                    // Al seleccionar un rango de fechas
                    picker.on('selected', (startDate, endDate) => {
                        if (!startDate || !endDate) return;
                        
                        console.log('Fechas seleccionadas:', {
                            start: startDate.format(dateFormat),
                            end: endDate.format(dateFormat)
                        });
                        
                        // Enviar evento a Livewire con el formato adecuado
                        @this.updateDateRange({
                            dates: {
                                start: startDate.format(dateFormat),
                                end: endDate.format(dateFormat),
                            }
                        });
                    });

                    // Establecer el valor inicial si existe
                    const initialStartDate = @js(!empty($dateRange['start']) ? $dateRange['start'] : null);
                    const initialEndDate = @js(!empty($dateRange['end']) ? $dateRange['end'] : null);
                    
                    if (initialStartDate && initialEndDate) {
                        picker.setDateRange(initialStartDate, initialEndDate);
                    }
                },
            });
            
            // Guardar referencia al picker para acceso global
            window.currentLitepicker = picker;
            
            return picker;
        }
        
        // Inicializar el datepicker cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            initializeDatepicker();
        });
        
        // Reinicializar el datepicker cuando Livewire actualice el DOM
        document.addEventListener('livewire:initialized', function() {
            initializeDatepicker();
            
            // Escuchar eventos de Livewire para reinicializar el datepicker
            Livewire.hook('morph.updated', ({ el }) => {
                if (el.contains(document.getElementById('date-range-picker'))) {
                    console.log('Reinicializando datepicker después de actualización Livewire');
                    setTimeout(initializeDatepicker, 100);
                }
            });
            
            // Escuchar evento para limpiar filtros
            Livewire.on('resetFilters', () => {
                if (window.currentLitepicker) {
                    console.log('Limpiando selección de fechas');
                    window.currentLitepicker.clearSelection();
                }
            });
        });
    </script>
@endPushOnce
