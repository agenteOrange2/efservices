<div class="box--stacked flex flex-col p-0" 
     x-data="stepGeneralForm()" 
     x-init="init()" 
     @sync-form-data.window="syncFormData($event.detail)"
     @photo-uploaded.window="handlePhotoUploaded($event.detail)"
     @photo-removed.window="clearPhotoData()"
     @file-removed="$wire.fileRemoved($event.detail.fieldName, $event.detail.index)">
    <div class="flex items-center px-5 py-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">Driver Information</h2>
    </div>

    <!-- Photo Upload -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Profile Photo</div>
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Upload a clear and recent profile photo.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <!-- Photo Upload Input -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                @if($photo_preview_url)
                    <div class="mb-4">
                        <img src="{{ $photo_preview_url }}" alt="Profile Photo" class="mx-auto h-32 w-32 object-cover rounded-full">
                        <p class="mt-2 text-sm text-gray-600">Current Photo</p>
                    </div>
                @endif
                
                <div class="mb-4">
                    <input type="file" wire:model="photo" accept="image/*" 
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div wire:loading wire:target="photo" class="text-blue-600">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Uploading photo...
                </div>
                
                <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF up to 5MB</p>
            </div>
            
            @error('photo')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <!-- First Name -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">First Name</div>
                    <div
                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                        Required</div>
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Enter your legal first name.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input type="text" wire:model="name"
                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Middle Name -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Middle Name</div>
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Enter your middle name if applicable.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input type="text" wire:model="middle_name"
                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
            @error('middle_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Last Name -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Last Name</div>
                    <div
                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                        Required</div>
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Enter your legal last name.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input type="text" wire:model="last_name"
                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
            @error('last_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Email -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Email</div>
                    <div
                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                        Required</div>
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Enter your email address.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input type="email" wire:model="email"
                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Phone -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Phone</div>
                    <div
                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                        Required</div>
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Enter your primary contact phone number.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input type="number" wire:model="phone"
                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
            @error('phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Date of Birth -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Date of Birth</div>
                    <div
                        class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                        Required</div>
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    This information is required to verify your age and provide age-appropriate services.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-unified-date-picker
                wire:model="date_of_birth"
                placeholder="MM/DD/YYYY"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            @error('date_of_birth')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Password -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Password</div>
                    @if (!$driverId)
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    @endif
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Create a secure password for your account.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input type="password" wire:model="password"
                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Confirm Password -->
    <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
        <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
            <div class="text-left">
                <div class="flex items-center">
                    <div class="font-medium">Confirm Password</div>
                    @if (!$driverId)
                        <div
                            class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    @endif
                </div>
                <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                    Confirm your password to ensure it's entered correctly.
                </div>
            </div>
        </div>
        <div class="mt-3 w-full flex-1 xl:mt-0">
            <x-base.form-input type="password" wire:model="password_confirmation"
                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
            @error('password_confirmation')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Terms and Conditions -->
    <div class="mb-4 mt-4">
        <label class="flex items-center">
            <x-base.form-check.input type="checkbox" wire:model="terms_accepted" class="mr-2" />
            <span>I accept the terms and conditions *</span>
        </label>
        @error('terms_accepted')
            <span class="text-red-500 text-sm block">{{ $message }}</span>
        @enderror
    </div>

    <!-- Submit Button -->
    <div class="flex flex-col sm:flex-row md:py-0 py-5 gap-4 w-full sm:w-auto">
        <x-base.button type="button" wire:click="save" class="w-full sm:w-44 text-white" variant="primary">
            <span wire:loading.remove>Next</span>
            <span wire:loading wire:target="save" class="flex items-center">
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
        </x-base.button>

    </div>


    @if ($showCredentialsModal)
        <x-base.dialog id="button-modal-preview">
            <x-base.dialog.panel>
                <a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="#">
                    <x-base.lucide class="h-8 w-8 text-slate-400" icon="X" />
                </a>
                <div class="p-5 text-center">
                    <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-success" icon="CheckCircle" />
                    <div class="mt-5 text-3xl">Modal Example</div>
                    <div class="mt-2 text-slate-500">
                        Modal with close button
                    </div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <x-base.button class="w-24" data-tw-dismiss="modal" type="button" variant="primary">
                        Ok
                    </x-base.button>
                </div>
            </x-base.dialog.panel>
        </x-base.dialog>
        <div class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s] overflow-y-auto show"
            x-data>
            <div
                class="w-[90%] mx-auto bg-white relative rounded-md shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-16 group-[.show]:mt-16 group-[.modal-static]:scale-[1.05] sm:w-[460px]">
                <div class="p-6">
                    <div class="text-center mb-4">
                        <svg class="h-16 w-16 text-green-500 mx-auto" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Registration Started Successfully!</h3>
                    </div>

                    <div class="mb-6">
                        <p class="mt-2 text-slate-500">
                            We've sent your login credentials to <strong>{{ $email }}</strong> so you can
                            continue
                            your registration later if needed.
                        </p>

                        <div class="bg-gray-50 p-4 border rounded-md">
                            <p class="mt-2 text-slate-500">Your login information:</p>
                            <p class="mt-2 text-slate-500">Email: {{ $email }}</p>
                            <p class="mt-2 text-slate-500">Password: {{ $plainPassword }}</p>
                        </div>

                        <p class="mt-4 text-slate-500">
                            We recommend saving these credentials in case you need to continue your registration process
                            later.
                        </p>
                    </div>

                    <div class="flex justify-between space-x-4">
                        <button type="button" wire:click="saveAndExitFromModal"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Save & Exit
                        </button>
                        <x-base.button wire:click="continueToNextStep" data-tw-dismiss="modal" type="button" variant="primary">
                            Continue Registration
                        </x-base.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function stepGeneralForm() {
        return {
            init() {
                this.restoreFormData();
                this.setupEventListeners();
            },
            
            setupEventListeners() {
                // Escuchar evento de foto removida
                document.addEventListener('photo-removed', () => {
                    this.clearPhotoData();
                });
            },
            
            syncFormData(data) {
                // Sincronizar datos del formulario con sessionStorage
                if (data) {
                    sessionStorage.setItem('step_general_form_data', JSON.stringify(data));
                }
            },
            
            handlePhotoUploaded(data) {
                // Manejar foto subida desde Livewire
                if (data && data.url) {
                    const photoData = {
                        url: data.url,
                        name: data.name || 'photo.jpg',
                        id: Date.now()
                    };
                    
                    // Actualizar el componente de imagen
                    const imageUpload = document.querySelector('[x-data*="unifiedImageUpload"]');
                    if (imageUpload && imageUpload._x_dataStack) {
                        const component = imageUpload._x_dataStack[0];
                        if (component) {
                            component.files = [photoData];
                            component.storeFilesInSession([photoData]);
                        }
                    }
                }
            },
            
            clearPhotoData() {
                // Limpiar datos de foto del sessionStorage
                sessionStorage.removeItem('unified_image_upload_photo');
                
                // Limpiar también del componente de imagen
                const imageUpload = document.querySelector('[x-data*="unifiedImageUpload"]');
                if (imageUpload && imageUpload._x_dataStack) {
                    const component = imageUpload._x_dataStack[0];
                    if (component) {
                        component.files = [];
                        component.clearSessionStorage();
                    }
                }
            },
            
            restoreFormData() {
                // Restaurar datos del formulario desde sessionStorage
                const savedData = sessionStorage.getItem('step_general_form_data');
                if (savedData) {
                    try {
                        const data = JSON.parse(savedData);
                        
                        // Restaurar fecha de nacimiento
                        if (data.date_of_birth) {
                            const dateInput = document.querySelector('input[wire\\:model="date_of_birth"]');
                            if (dateInput) {
                                dateInput.value = data.date_of_birth;
                                dateInput.dispatchEvent(new Event('input'));
                            }
                        }
                        
                        // Restaurar otros campos si es necesario
                        ['name', 'middle_name', 'last_name', 'email', 'phone'].forEach(field => {
                            if (data[field]) {
                                const input = document.querySelector(`input[wire\\:model="${field}"]`);
                                if (input && !input.value) {
                                    input.value = data[field];
                                    input.dispatchEvent(new Event('input'));
                                }
                            }
                        });
                        
                    } catch (e) {
                        console.error('Error restoring form data:', e);
                    }
                }
            },
            
            clearStoredData() {
                // Limpiar datos almacenados
                sessionStorage.removeItem('step_general_form_data');
                sessionStorage.removeItem('unified_image_upload_photo');
            }
        }
    }
</script>
