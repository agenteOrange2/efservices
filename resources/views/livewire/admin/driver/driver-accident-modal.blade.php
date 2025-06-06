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
                                    {{ $accidentId ? 'Edit' : 'Add' }} Accident Record
                                </h3>
                                
                                <!-- Formulario -->
                                <form wire:submit.prevent="save" class="mt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Fecha del accidente -->
                                        <div>
                                            <label for="accident_date" class="block text-sm font-medium text-gray-700">Date of Accident *</label>
                                            <input type="date" id="accident_date" wire:model="accident_date" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('accident_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>


                                        
                                        <!-- Naturaleza del accidente -->
                                        <div>
                                            <label for="nature_of_accident" class="block text-sm font-medium text-gray-700">Nature of Accident *</label>
                                            <input type="text" id="nature_of_accident" wire:model="nature_of_accident" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                                placeholder="Describe the nature of the accident">
                                            @error('nature_of_accident') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Fatalidades -->
                                        <div>
                                            <label class="flex items-center mt-4">
                                                <input type="checkbox" wire:model.live="had_fatalities" 
                                                    class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="ml-2 text-sm text-gray-700">Fatalities?</span>
                                            </label>
                                            @error('had_fatalities') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Número de fatalidades -->
                                        @if($had_fatalities)
                                        <div>
                                            <label for="number_of_fatalities" class="block text-sm font-medium text-gray-700">Number of Fatalities</label>
                                            <input type="number" id="number_of_fatalities" wire:model="number_of_fatalities" min="0"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('number_of_fatalities') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        @endif
                                        
                                        <!-- Heridos -->
                                        <div>
                                            <label class="flex items-center mt-4">
                                                <input type="checkbox" wire:model="had_injuries" 
                                                    class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="ml-2 text-sm text-gray-700">Injuries?</span>
                                            </label>
                                            @error('had_injuries') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Número de heridos -->
                                        @if($had_injuries)
                                        <div>
                                            <label for="number_of_injuries" class="block text-sm font-medium text-gray-700">Number of Injuries</label>
                                            <input type="number" id="number_of_injuries" wire:model="number_of_injuries" min="0"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('number_of_injuries') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        @endif
                                        
                                        <!-- Comentarios / Descripción -->
                                        <div class="col-span-2">
                                            <label for="comments" class="block text-sm font-medium text-gray-700">Description / Comments *</label>
                                            <textarea id="comments" wire:model="comments" rows="4"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                                            @error('comments') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Documentos (subida de archivos) -->
                                        <div class="col-span-2 mt-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Documents</label>
                                            
                                            <!-- Componente de carga de archivos -->
                                            <div class="mb-3">
                                                @livewire('components.file-uploader', [
                                                    'modelName' => 'accident-images',
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
