<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Traffic Convictions</h3>

    <div class="mb-6">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model.live="has_traffic_convictions" class="sr-only peer">
            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
            <label for="has_traffic_convictions" class="text-sm ml-3">Have you had any traffic violation convictions or forfeitures
                (other than parking violations) in the past three years prior to the application date?</label>
        </label>
    </div>

    <div x-show="$wire.has_traffic_convictions" x-transition>
        @foreach ($traffic_convictions as $index => $conviction)
            <div class="border p-4 rounded-lg mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium">Conviction #{{ $index + 1 }}</h4>
                    @if (count($traffic_convictions) > 1)
                        <button type="button" wire:click="removeTrafficConviction({{ $index }})"
                            class="text-red-500 text-sm">
                            <i class="fas fa-trash mr-1"></i> Remove
                        </button>
                    @endif
                </div>

                <input type="hidden" wire:model="traffic_convictions.{{ $index }}.id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Conviction Date</label>
                        <input type="date" wire:model="traffic_convictions.{{ $index }}.conviction_date"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("traffic_convictions.{$index}.conviction_date")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Location</label>
                        <input type="text" wire:model="traffic_convictions.{{ $index }}.location"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="City, State">
                        @error("traffic_convictions.{$index}.location")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Charge</label>
                        <input type="text" wire:model="traffic_convictions.{{ $index }}.charge"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Violation charged">
                        @error("traffic_convictions.{$index}.charge")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Penalty</label>
                        <input type="text" wire:model="traffic_convictions.{{ $index }}.penalty"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Fine, points, etc.">
                        @error("traffic_convictions.{$index}.penalty")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        @endforeach

        <button type="button" wire:click="addTrafficConviction"
            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
            <i class="fas fa-plus mr-1"></i> Add Another Conviction
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
