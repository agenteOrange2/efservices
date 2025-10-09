<div class="company-driver-section">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Company Driver Information</h3>
    
    <!-- Company Driver Notes -->
    <div class="form-group">
        <label for="company_driver_notes" class="block text-sm font-medium text-gray-700 mb-1">
            Company Driver Information
        </label>
        <textarea wire:model="company_driver_notes" 
                  id="company_driver_notes" 
                  rows="6"
                  placeholder="Please provide any relevant information about your company driver application, including experience level, schedule preferences, preferred routes, additional certifications, or any other details that would be helpful for your application..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        @error('company_driver_notes')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Information Note -->
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-blue-800">Company Driver Information</h4>
                <p class="text-sm text-blue-700 mt-1">
                    As a company driver, you'll be driving company-owned vehicles. Please provide any relevant information 
                    about your experience, preferences, and qualifications in the notes field above.
                </p>
            </div>
        </div>
    </div>
</div>