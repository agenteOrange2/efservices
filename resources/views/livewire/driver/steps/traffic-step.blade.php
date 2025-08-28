<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Traffic Convictions</h3>

    <div class="mb-6">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model.live="has_traffic_convictions" class="sr-only peer">
            <div
                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
            </div>
            <label for="has_traffic_convictions" class="text-sm ml-3">Have you had any traffic violation convictions or
                forfeitures
                (other than parking violations) in the past three years prior to the application date?</label>
        </label>
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

                @if(empty($conviction['id']))
                    <!-- Botón para crear traffic conviction -->
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-blue-700">
                                    <strong>Create this traffic conviction first to enable document uploads.</strong>
                                </p>
                            </div>
                            <button type="button" 
                                    wire:click="createTrafficConviction({{ $index }})"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition">
                                <i class="fas fa-plus mr-1"></i> Create Traffic
                            </button>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Conviction Date <span class="text-red-500">*</span>
                        </label>
                        <x-unified-date-picker
                            name="traffic_convictions.{{ $index }}.conviction_date"
                            wire:model="traffic_convictions.{{ $index }}.conviction_date"
                            :value="$conviction['conviction_date'] ?? ''"
                            placeholder="Select conviction date"
                            class="w-full"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="traffic_convictions.{{ $index }}.location"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter location">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Charge <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="traffic_convictions.{{ $index }}.charge"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter charge">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Penalty <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="traffic_convictions.{{ $index }}.penalty"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter penalty">
                    </div>
                </div>
                
                @php
                    $isConvictionComplete = !empty($conviction['conviction_date']) && 
                                          !empty($conviction['location']) && 
                                          !empty($conviction['charge']) && 
                                          !empty($conviction['penalty']);
                @endphp
                
                @if(!empty($conviction['id']))
                    @if(!$isConvictionComplete)
                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-yellow-700">
                                    <strong>Complete los datos de la convicción antes de subir archivos.</strong>
                                    Todos los campos marcados con * son obligatorios.
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Componente de carga de archivos para esta convicción específica -->
                    <livewire:components.file-uploader 
                        :key="'traffic-uploader-' . $index"
                        model-name="ticket_files"
                        :model-index="$index"
                        label="Upload Ticket Documents"
                        :existing-files="isset($conviction['documents']) ? $conviction['documents'] : []"
                    />
                @endif
            </div>
        @endforeach

        <button type="button" wire:click="addTrafficConviction"
            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
            <i class="fas fa-plus mr-1"></i> Add Another Conviction
        </button>
        
        <!-- Nota: La sección de carga general de tickets se ha eliminado ya que ahora cada convicción tiene su propia sección de carga -->
    </div>

    <!-- Navigation Buttons -->
    <div class="mt-8 px-5 py-5 border-t border-slate-200/60 dark:border-darkmode-400">
        <div class="flex flex-col sm:flex-row justify-between gap-4">
            <div class="w-full sm:w-auto">
                <x-base.button type="button" wire:click="previous" class="w-full sm:w-44" variant="secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg> Previous
                </x-base.button>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <x-base.button type="button" wire:click="saveAndExit" class="w-full sm:w-44 text-white"
                    variant="warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" />
                    </svg>
                    Save & Exit
                </x-base.button>
                <x-base.button type="button" wire:click="next" class="w-full sm:w-44" variant="primary">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </x-base.button>
            </div>
        </div>
    </div>
</div>
