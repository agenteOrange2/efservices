<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay de fondo -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-35 transition-opacity" aria-hidden="true"></div>

                <!-- Centrar modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Contenido del Modal -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $courseId ? 'Edit' : 'Add' }} Course
                                </h3>
                                
                                <!-- Formulario -->
                                <form wire:submit.prevent="save" class="mt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Nombre de la organización -->
                                        <div class="col-span-2">
                                            <label for="organization_name" class="block text-sm font-medium text-gray-700">Organization Name *</label>
                                            <input type="text" id="organization_name" wire:model="organization_name" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('organization_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>                                        

                                        <!-- Ciudad -->
                                        <div>
                                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                            <input type="text" id="city" wire:model="city" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Estado -->
                                        <div>
                                            <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                            <input type="text" id="state" wire:model="state" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('state') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Certification Date -->
                                        <div>
                                            <label for="certification_date" class="block text-sm font-medium text-gray-700">Certification Date</label>
                                            <input type="date" id="certification_date" wire:model="certification_date" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('certification_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Expiration Date -->
                                        <div>
                                            <label for="expiration_date" class="block text-sm font-medium text-gray-700">Expiration Date</label>
                                            <input type="date" id="expiration_date" wire:model="expiration_date" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                            @error('expiration_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Course Status -->
                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-700">Course Status</label>
                                            <select id="status" wire:model="status" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                                <option value="Active">Active</option>
                                                <option value="Expired">Expired</option>
                                                <option value="Pending">Pending</option>
                                            </select>
                                            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Experience/Notes -->
                                        <div class="col-span-2">
                                            <label for="experience" class="block text-sm font-medium text-gray-700">Experience/Notes</label>
                                            <textarea id="experience" wire:model="experience" rows="3"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                                            @error('experience') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Certificates (file upload) -->
                                        <div class="col-span-2 mt-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Certificates</label>
                                            
                                            <!-- File upload component -->
                                            <div class="mb-3">
                                                @livewire('components.file-uploader', [
                                                    'modelName' => 'course_certificates',
                                                    'modelIndex' => 0,
                                                    'label' => 'Certificados',
                                                    'acceptedFileTypes' => ['image/jpeg', 'image/png', 'application/pdf'],
                                                    'maxFileSize' => 5120,
                                                    'multiple' => true,
                                                    'inputLabel' => 'Arrastre o haga clic para seleccionar certificados',
                                                    'existingFiles' => $existingFiles
                                                ])
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action buttons -->
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                                            Save
                                        </button>
                                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm">
                                            Cancel
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
