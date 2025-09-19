<div class="bg-white p-0 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Driver's License Information</h3>

    <!-- Current License Number -->
    <div class="mb-6 border-b pb-4">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Current License Number <span
                    class="text-red-500">*</span></label>
            <input type="text" wire:model="current_license_number"
                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('current_license_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- License Entries -->
        @foreach ($licenses as $index => $license)
            <div class="license-entry border rounded-lg p-4 mb-4">
                <!-- Header with Remove Button -->
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600">License #{{ $index + 1 }}</h5>
                    @if ($index > 0)
                        <button type="button" wire:click="removeLicense({{ $index }})"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                            Remove
                        </button>
                    @endif
                </div>

                <!-- License Number & State -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Number <span
                                class="text-red-500">*</span></label>
                        <input type="text" wire:model="licenses.{{ $index }}.license_number"
                            placeholder="Enter license number"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("licenses.{$index}.license_number")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State of Issue <span
                                class="text-red-500">*</span></label>
                        <select wire:model="licenses.{{ $index }}.state_of_issue"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select State</option>
                            @foreach ($usStates as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error("licenses.{$index}.state_of_issue")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- License Class & Expiration Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Class <span
                                class="text-red-500">*</span></label>
                        <select wire:model="licenses.{{ $index }}.license_class"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select Class</option>
                            <option value="A">Class A</option>
                            <option value="B">Class B</option>
                            <option value="C">Class C</option>
                        </select>
                        @error("licenses.{$index}.license_class")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date <span
                                class="text-red-500">*</span></label>
                        <input type="text" 
                            value="{{ $license['expiration_date'] ?? '' }}"
                            placeholder="MM/DD/YYYY" 
                            class="driver-datepicker w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" 
                            onchange="@this.set('licenses.{{ $index }}.expiration_date', this.value)"
                            required />
                        @error("licenses.{$index}.expiration_date")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- CDL Checkbox -->
                <div class="mb-4" x-data="{ isCDL: @entangle('licenses.' . $index . '.is_cdl') }">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" wire:model="licenses.{{ $index }}.is_cdl"
                            id="is_cdl_{{ $index }}" x-model="isCDL"
                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                        <label for="is_cdl_{{ $index }}" class="ml-2 text-sm">This is a Commercial Driver's
                            License (CDL)</label>
                    </div>

                    <!-- Endorsements Section -->
                    <div x-show="isCDL" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Endorsements</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_n_{{ $index }}" value="N"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_n_{{ $index }}" class="ml-2 text-sm">N (Tank)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_h_{{ $index }}" value="H"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_h_{{ $index }}" class="ml-2 text-sm">H (HAZMAT)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_x_{{ $index }}" value="X"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_x_{{ $index }}" class="ml-2 text-sm">X (Combo)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_t_{{ $index }}" value="T"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_t_{{ $index }}" class="ml-2 text-sm">T
                                    (Double/Triple)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_p_{{ $index }}" value="P"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_p_{{ $index }}" class="ml-2 text-sm">P
                                    (Passenger)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_s_{{ $index }}" value="S"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_s_{{ $index }}" class="ml-2 text-sm">S (School
                                    Bus)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create/Update License Button -->
                <div class="mb-4">
                    @if(empty($license['id']))
                        <button type="button" wire:click="createLicense({{ $index }})"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                            <i class="fas fa-save mr-1"></i> Crear Licencia
                        </button>
                        <p class="text-sm text-gray-600 mt-2">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Debe crear la licencia antes de poder subir las imágenes.
                        </p>
                    @else
                        <button type="button" wire:click="updateLicense({{ $index }})"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            <i class="fas fa-edit mr-1"></i> Actualizar Licencia
                        </button>
                        <p class="text-sm text-green-600 mt-2">
                            <i class="fas fa-check-circle mr-1"></i> 
                            Licencia creada. Ahora puede subir las imágenes.
                        </p>
                    @endif
                </div>

                <!-- License Images - Only show if license has been created -->
                @if(!empty($license['id']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">License Front Image</label>
                            <x-unified-image-upload 
     
                                :existing-image-url="$license['front_preview'] ?? ''"
                                :existing-image-name="$license['front_filename'] ?? ''"
                                :unique-id="$license['unique_id'] ?? ''"
                                side="front"
                                accept="image/*,application/pdf" 
                                max-size="10240" 
                                class="w-full"
                                :model-type="'user_driver'"
                                :model-id="$license['id']"
                                :driver-id="$driverId"
                                collection="license_documents"
                                document-type="license_front"
                                :show-preview="true" />

                        </div>

                        <!-- License Back Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">License Back Image</label>
                            <x-unified-image-upload 
 
                                :existing-image-url="$license['back_preview'] ?? ''"
                                :existing-image-name="$license['back_filename'] ?? ''"
                                :unique-id="$license['unique_id'] ?? ''"
                                side="back"
                                accept="image/*,application/pdf" 
                                max-size="10240" 
                                class="w-full"
                                :model-type="'user_driver'"
                                :model-id="$license['id']"
                                :driver-id="$driverId"
                                collection="license_documents"
                                document-type="license_back"
                                :show-preview="true" />

                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        <button type="button" wire:click="addLicense"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            <i class="fas fa-plus mr-1"></i> Add Another License
        </button>
    </div>

    <!-- Driving Experience Section -->
    <div class="mb-6 border-t pt-6">
        <h4 class="font-medium text-gray-700 mb-4">Driving Experience</h4>

        @foreach ($experiences as $index => $experience)
            <div class="experience-entry border rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600">Vehicle #{{ $index + 1 }}</h5>
                    @if ($index > 0)
                        <button type="button" wire:click="removeExperience({{ $index }})"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                            Remove
                        </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equipment Type <span
                                class="text-red-500">*</span></label>
                        <select wire:model="experiences.{{ $index }}.equipment_type"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select Equipment Type</option>
                            <option value="Straight Truck">Straight Truck</option>
                            <option value="Tractor & Semi-Trailer">Tractor & Semi-Trailer</option>
                            <option value="Tractor & Two Trailers">Tractor & Two Trailers</option>
                            <option value="Tractor & Triple Trailers">Tractor & Triple Trailers</option>
                            <option value="Other">Other</option>
                        </select>
                        @error("experiences.{$index}.equipment_type")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Years of Experience <span
                                class="text-red-500">*</span></label>
                        <input type="number" wire:model="experiences.{{ $index }}.years_experience"
                            min="0" max="10" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("experiences.{$index}.years_experience")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                        <span class="text-xs text-gray-500">Maximum 10 years</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Miles Driven <span
                                class="text-red-500">*</span></label>
                        <input type="number" wire:model="experiences.{{ $index }}.miles_driven"
                            min="0" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("experiences.{$index}.miles_driven")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <div class="h-8"></div> <!-- Spacer to align with label -->
                        <div class="flex items-center">
                            <input type="checkbox" id="requires_cdl_{{ $index }}"
                                wire:model="experiences.{{ $index }}.requires_cdl"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="requires_cdl_{{ $index }}" class="ml-2 text-sm">This vehicle requires
                                a CDL</label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <button type="button" wire:click="addExperience"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            <i class="fas fa-plus mr-1"></i> Add Another Vehicle Experience
        </button>
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

<script>
    // Escuchar eventos de eliminación de imágenes
    document.addEventListener('livewire:init', () => {
        Livewire.on('image-deleted', (event) => {
            // Mostrar mensaje de éxito
            if (event.message) {
                // Crear y mostrar notificación de éxito
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                notification.textContent = event.message;
                document.body.appendChild(notification);
                
                // Remover notificación después de 3 segundos
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        });
        
        Livewire.on('image-delete-error', (event) => {
            // Mostrar mensaje de error
            if (event.message) {
                // Crear y mostrar notificación de error
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
                notification.textContent = event.message;
                document.body.appendChild(notification);
                
                // Remover notificación después de 5 segundos
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }
        });
    });
</script>
