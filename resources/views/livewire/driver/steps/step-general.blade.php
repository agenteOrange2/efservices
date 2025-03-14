<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Driver Information</h3>
    
    <!-- Photo Upload -->
    <div class="mb-6">
        <label class="block mb-2">Profile Photo</label>
        <div class="flex items-center space-x-4">
            <div class="w-24 h-24 bg-gray-100 rounded-full overflow-hidden">
                @if ($photo && $photo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile && $photo->isPreviewable())
                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif($photo_preview_url)
                    <img src="{{ $photo_preview_url }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                @endif
            </div>
            <input type="file" wire:model="photo" id="photo" class="hidden">
            <label for="photo" class="px-4 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300">
                Choose Photo
            </label>
        </div>
        @error('photo')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Name Fields -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
            <input type="text" wire:model="name" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
            <input type="text" wire:model="middle_name" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('middle_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
            <input type="text" wire:model="last_name" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('last_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Contact Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" wire:model="email" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
            <input type="text" wire:model="phone" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Date of Birth -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth <span class="text-red-500">*</span></label>
        <input type="date" wire:model="date_of_birth" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
        @error('date_of_birth')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Password -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
            <input type="password" wire:model="password" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
            <input type="password" wire:model="password_confirmation" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            @error('password_confirmation')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Terms and Conditions -->
    <div class="mb-4">
        <label class="flex items-center">
            <input type="checkbox" wire:model="terms_accepted" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
            <span class="text-sm">I accept the terms and conditions <span class="text-red-500">*</span></span>
        </label>
        @error('terms_accepted')
            <span class="text-red-500 text-sm block">{{ $message }}</span>
        @enderror
    </div>

    <!-- Submit Button -->
    <div class="mt-6">
        <button type="button" wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            <span wire:loading.remove>Next</span>
            <span wire:loading wire:target="save" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            </span>
        </button>
    </div>


    @if($showCredentialsModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-data>
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <div class="text-center mb-4">
                <svg class="h-16 w-16 text-green-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mt-4">Registration Started Successfully!</h3>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-600 mb-4">
                    We've sent your login credentials to <strong>{{ $email }}</strong> so you can continue your registration later if needed.
                </p>
                
                <div class="bg-gray-50 p-4 border rounded-md">
                    <p class="text-sm text-gray-700 mb-1">Your login information:</p>
                    <p class="font-medium">Email: {{ $email }}</p>
                    <p class="font-medium">Password: {{ $plainPassword }}</p>
                </div>
                
                <p class="text-gray-600 mt-4">
                    We recommend saving these credentials in case you need to continue your registration process later.
                </p>
            </div>
            
            <div class="flex justify-between space-x-4">
                <button type="button" wire:click="saveAndExitFromModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Save & Exit
                </button>
                <button type="button" wire:click="continueToNextStep" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Continue Registration
                </button>
            </div>
        </div>
    </div>
    @endif
</div>