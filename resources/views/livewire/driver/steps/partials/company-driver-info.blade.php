<div class="company-driver-section">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Company Driver Information</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Years of Experience -->
        <div class="form-group">
            <label for="company_driver_experience_years" class="block text-sm font-medium text-gray-700 mb-1">
                Years of Experience <span class="text-red-500">*</span>
            </label>
            <select wire:model="company_driver_experience_years" 
                    id="company_driver_experience_years" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select experience level</option>
                <option value="0-1">0-1 years</option>
                <option value="1-3">1-3 years</option>
                <option value="3-5">3-5 years</option>
                <option value="5-10">5-10 years</option>
                <option value="10+">10+ years</option>
            </select>
            @error('company_driver_experience_years')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- Schedule Preference -->
        <div class="form-group">
            <label for="company_driver_schedule_preference" class="block text-sm font-medium text-gray-700 mb-1">
                Schedule Preference <span class="text-red-500">*</span>
            </label>
            <select wire:model="company_driver_schedule_preference" 
                    id="company_driver_schedule_preference" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select schedule preference</option>
                <option value="local">Local (Home daily)</option>
                <option value="regional">Regional (Home weekly)</option>
                <option value="otr">Over-the-Road (OTR)</option>
                <option value="dedicated">Dedicated routes</option>
                <option value="flexible">Flexible</option>
            </select>
            @error('company_driver_schedule_preference')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>
    
    <!-- Preferred Routes -->
    <div class="form-group mt-4">
        <label for="company_driver_preferred_routes" class="block text-sm font-medium text-gray-700 mb-1">
            Preferred Routes/Areas
        </label>
        <textarea wire:model="company_driver_preferred_routes" 
                  id="company_driver_preferred_routes" 
                  rows="3"
                  placeholder="Describe your preferred routes, areas, or any geographical preferences..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        @error('company_driver_preferred_routes')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Additional Certifications -->
    <div class="form-group mt-4">
        <label for="company_driver_additional_certifications" class="block text-sm font-medium text-gray-700 mb-1">
            Additional Certifications
        </label>
        <textarea wire:model="company_driver_additional_certifications" 
                  id="company_driver_additional_certifications" 
                  rows="3"
                  placeholder="List any additional certifications, endorsements, or special qualifications (HAZMAT, Tanker, Double/Triple, etc.)..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        @error('company_driver_additional_certifications')
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
                    As a company driver, you'll be driving company-owned vehicles. Please provide your experience level, 
                    schedule preferences, and any additional qualifications that make you a strong candidate.
                </p>
            </div>
        </div>
    </div>
</div>