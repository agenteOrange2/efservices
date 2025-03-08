<div>
    <h2 class="text-xl font-bold mb-4">Driver Information</h2>
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
            <input type="file" wire:model.live="photo" id="photo" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp">
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
            <label class="block mb-1">First Name *</label>
            <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded">
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Middle Name</label>
            <input type="text" wire:model="middle_name" class="w-full px-3 py-2 border rounded">
            @error('middle_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Last Name *</label>
            <input type="text" wire:model="last_name" class="w-full px-3 py-2 border rounded">
            @error('last_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Contact Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block mb-1">Email *</label>
            <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded">
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Phone *</label>
            <input type="number" wire:model="phone" class="w-full px-3 py-2 border rounded">
            @error('phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Date of Birth -->
    <div class="mb-4">
        <label class="block mb-1">Date of Birth *</label>
        <input type="date" wire:model="date_of_birth" class="w-full px-3 py-2 border rounded">
        @error('date_of_birth')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Password -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block mb-1">Password {{ $driverId ? '' : '*' }}</label>
            <input type="password" wire:model="password" class="w-full px-3 py-2 border rounded">
            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Confirm Password {{ $driverId ? '' : '*' }}</label>
            <input type="password" wire:model="password_confirmation" class="w-full px-3 py-2 border rounded">
            @error('password_confirmation')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Status -->
    <div class="mb-4">
        <label class="block mb-1">Status</label>
        <select wire:model="status" class="w-full px-3 py-2 border rounded">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
            <option value="2">Pending</option>
        </select>
        @error('status')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Terms and Conditions -->
    <div class="mb-4">
        <label class="flex items-center">
            <input type="checkbox" wire:model="terms_accepted" class="mr-2">
            <span>I accept the terms and conditions *</span>
        </label>
        @error('terms_accepted')
            <span class="text-red-500 text-sm block">{{ $message }}</span>
        @enderror
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <div></div>
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
