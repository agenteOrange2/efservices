<div>
    <x-base.popover class="inline-block">
        <x-base.popover.button class="w-full sm:w-auto" as="x-base.button" variant="outline-secondary">
            <svg class="mr-2 h-4 w-4 stroke-[1.3]" xmlns="http://www.w3.org/2000/svg"  width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="M11 4h4"/><path d="M11 8h7"/><path d="M11 12h10"/></svg>            
            
            Filter
            <span class="ml-2 flex h-5 items-center justify-center rounded-full border bg-slate-100 px-1.5 text-xs font-medium">
                {{ count(array_filter($filters)) }}
            </span>
        </x-base.popover.button>
        <x-base.popover.panel>
            <div class="p-2">
                {{-- Filtros comunes --}}
                <div class="mb-4">
                    <label class="text-left text-slate-500 block">Date Range</label>
                    <div class="flex gap-2 mt-2">
                        <input type="date" class="w-1/2 rounded border-gray-300" 
                               wire:model.live="filters.date_range.start">
                        <input type="date" class="w-1/2 rounded border-gray-300" 
                               wire:model.live="filters.date_range.end">
                    </div>
                </div>
                {{-- Filtros personalizados --}}
                @foreach ($filterOptions as $key => $option)
                    <div class="mb-4">
                        <label class="text-left text-slate-500 block">{{ $option['label'] }}</label>
                        @if ($option['type'] === 'select')
                            <x-base.form-select class="mt-2 flex-1" wire:model.live="filters.{{ $key }}">
                                <option value="">{{ $option['placeholder'] ?? 'Select an option' }}</option>
                                @foreach ($option['options'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-base.form-select>
                        @elseif ($option['type'] === 'input')
                            <input type="text" class="w-full rounded border-gray-300 mt-2" 
                                   placeholder="{{ $option['placeholder'] ?? '' }}" 
                                   wire:model.live="filters.{{ $key }}">
                        @endif
                    </div>
                @endforeach

                <div class="mt-4 flex items-center">
                    <x-base.button class="ml-auto w-32" wire:click="applyFilters" variant="primary">
                        Apply
                    </x-base.button>
                </div>
            </div>
        </x-base.popover.panel>
    </x-base.popover>
</div>
