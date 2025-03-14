<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Traffic Convictions</h3>

    <div class="flex items-center mb-4">
        <input type="checkbox" wire:model="has_traffic_convictions" id="has_traffic_convictions"
            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
        <label for="has_traffic_convictions" class="text-sm">Have you had any traffic violation convictions or forfeitures
            (other than parking violations) in the past three years prior to the application date?</label>
    </div>

    <div x-data="{ show: @entangle('has_traffic_convictions') }" x-show="show" x-transition>
        @foreach ($traffic_convictions as $index => $conviction)
            <div class="border rounded-lg p-4 mb-6">
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
        <button type="button" wire:click="previous"
            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
            Previous
        </button>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit"
                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            <button type="button" wire:click="next" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <span wire:loading.remove wire:target="next">Next</span>
                <span wire:loading wire:target="next" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Processing...
                </span>
            </button>
        </div>
    </div>
</div>
