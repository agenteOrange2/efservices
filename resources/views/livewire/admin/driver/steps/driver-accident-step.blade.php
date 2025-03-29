<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Accident Record</h3>


    <div class="mb-6">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model.live="has_accidents" class="sr-only peer">
            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
            <label for="has_accidents" class="text-sm ml-3">
                Have you had any accidents in the previous three years?
            </label>
    </div>

    <div x-show="$wire.has_accidents" x-transition>
        @foreach ($accidents as $index => $accident)
            <div class="border p-4 rounded-lg mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium">Accident #{{ $index + 1 }}</h4>
                    @if (count($accidents) > 1)
                        <button type="button" wire:click="removeAccident({{ $index }})"
                            class="text-red-500 text-sm">
                            <i class="fas fa-trash mr-1"></i> Remove
                        </button>
                    @endif
                </div>

                <input type="hidden" wire:model="accidents.{{ $index }}.id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Accident Date</label>
                        <input type="date" wire:model="accidents.{{ $index }}.accident_date"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("accidents.{$index}.accident_date")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nature of Accident</label>
                        <input type="text" wire:model="accidents.{{ $index }}.nature_of_accident"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Head-on, rear-end, etc.">
                        @error("accidents.{$index}.nature_of_accident")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div x-data="{ hadInjuries: @json(isset($accident['had_injuries']) && $accident['had_injuries']) }">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" wire:model="accidents.{{ $index }}.had_injuries"
                                x-model="hadInjuries" id="had_injuries_{{ $index }}"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                            <label for="had_injuries_{{ $index }}" class="text-sm">Injuries</label>
                        </div>
                        <div x-show="hadInjuries">
                            <label class="block text-sm font-medium mb-1">Number of Injuries</label>
                            <input type="number" wire:model="accidents.{{ $index }}.number_of_injuries"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" min="0">
                            @error("accidents.{$index}.number_of_injuries")
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div x-data="{ hadFatalities: @json(isset($accident['had_fatalities']) && $accident['had_fatalities']) }">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" wire:model="accidents.{{ $index }}.had_fatalities"
                                x-model="hadFatalities" id="had_fatalities_{{ $index }}"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                            <label for="had_fatalities_{{ $index }}" class="text-sm">Fatalities</label>
                        </div>
                        <div x-show="hadFatalities">
                            <label class="block text-sm font-medium mb-1">Number of Fatalities</label>
                            <input type="number" wire:model="accidents.{{ $index }}.number_of_fatalities"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" min="0">
                            @error("accidents.{$index}.number_of_fatalities")
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Comments</label>
                    <textarea wire:model="accidents.{{ $index }}.comments"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" rows="3"
                        placeholder="Additional details about the accident"></textarea>
                </div>
            </div>
        @endforeach

        <button type="button" wire:click="addAccident"
            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
            <i class="fas fa-plus mr-1"></i> Add Another Accident
        </button>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <div>
            <button type="button" wire:click="previous" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Previous
            </button>
        </div>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit"
                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            <button type="button" wire:click="next" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Next
            </button>
        </div>
    </div>
</div>
