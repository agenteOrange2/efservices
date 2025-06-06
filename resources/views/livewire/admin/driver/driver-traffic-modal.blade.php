<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay de fondo -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Centrar modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Contenido del Modal -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $trafficId ? 'Edit' : 'Add' }} Traffic Violation Record
                                </h3>
                                
                                <!-- Formulario -->
                                <form wire:submit.prevent="save" class="mt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Fecha de la infracción -->
                                        <div>
                                            <label for="conviction_date" class="block text-sm font-medium text-gray-700">Date of Conviction *</label>
                                            <input type="date" id="conviction_date" wire:model="conviction_date" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('conviction_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        

                                        
                                        <!-- Cargo/Infracción -->
                                        <div>
                                            <label for="charge" class="block text-sm font-medium text-gray-700">Charge/Violation *</label>
                                            <input type="text" id="charge" wire:model="charge" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('charge') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Penalización -->
                                        <div>
                                            <label for="penalty" class="block text-sm font-medium text-gray-700">Penalty</label>
                                            <input type="text" id="penalty" wire:model="penalty" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('penalty') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Ubicación -->
                                        <div class="col-span-2">
                                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                            <input type="text" id="location" wire:model="location"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                                placeholder="Enter location of the violation">
                                            @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        


                                        <!-- Documentos (subida de archivos) -->
                                        <div class="col-span-2 mt-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Documents</label>
                                            
                                            <!-- Componente de carga de archivos -->
                                            <div class="mb-3">
                                                @livewire('components.file-uploader', [
                                                    'modelName' => 'traffic_images',
                                                    'modelIndex' => 0,
                                                    'label' => 'Documents',
                                                    'acceptedFileTypes' => ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                                                    'maxFileSize' => 5120,
                                                    'multiple' => true,
                                                    'inputLabel' => 'Arrastre o haga clic para seleccionar documentos',
                                                    'existingFiles' => $existingFiles
                                                ])
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                                            Guardar
                                        </button>
                                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
