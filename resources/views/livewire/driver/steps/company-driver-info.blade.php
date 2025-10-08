<!-- Company Driver Information -->
<div class="mb-6 bg-gray-50 p-4 rounded-lg">
    <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
            <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
        </svg>
        Company Driver Information
    </h3>

    <!-- Experience Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-2 font-medium text-gray-700">
                Years of Experience h2<span class="text-red-500">*</span>
            </label>
            <select wire:model="company_driver_experience_years" 
                    class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                <option value="">Select Experience Level</option>
                <option value="0-1">0-1 years (Entry Level)</option>
                <option value="1-3">1-3 years (Some Experience)</option>
                <option value="3-5">3-5 years (Experienced)</option>
                <option value="5-10">5-10 years (Very Experienced)</option>
                <option value="10+">10+ years (Expert Level)</option>
            </select>
            @error('company_driver_experience_years')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block mb-2 font-medium text-gray-700">
                Schedule Preference <span class="text-red-500">*</span>
            </label>
            <select wire:model="company_driver_schedule_preference" 
                    class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                <option value="">Select Schedule</option>
                <option value="full_time">Full Time (40+ hours/week)</option>
                <option value="part_time">Part Time (20-39 hours/week)</option>
                <option value="flexible">Flexible Schedule</option>
                <option value="weekends">Weekends Only</option>
                <option value="nights">Night Shifts</option>
                <option value="seasonal">Seasonal Work</option>
            </select>
            @error('company_driver_schedule_preference')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Preferred Routes -->
    <div class="mb-6">
        <label class="block mb-2 font-medium text-gray-700">
            Preferred Routes/Areas
        </label>
        <textarea wire:model="company_driver_preferred_routes" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-slate-300/60 rounded-md shadow-sm" 
                  placeholder="Describe your preferred routes, areas, or types of deliveries (e.g., Local deliveries within city, Interstate routes, Specific regions)"></textarea>
        @error('company_driver_preferred_routes')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Additional Certifications -->
    <div class="mb-6">
        <label class="block mb-2 font-medium text-gray-700">
            Additional Certifications
        </label>
        <textarea wire:model="company_driver_additional_certifications" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-slate-300/60 rounded-md shadow-sm" 
                  placeholder="List any additional certifications, endorsements, or special qualifications (e.g., Hazmat, Passenger, School Bus, Forklift, etc.)"></textarea>
        @error('company_driver_additional_certifications')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Company Driver Benefits Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h4 class="font-medium text-blue-800 mb-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            Company Driver Benefits
        </h4>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• Company-provided vehicle and fuel</li>
            <li>• Health insurance and benefits package</li>
            <li>• Paid time off and holidays</li>
            <li>• Regular maintenance and repairs covered</li>
            <li>• Stable weekly schedule</li>
            <li>• Performance bonuses and incentives</li>
        </ul>
    </div>

    <!-- Experience Level Guide -->
    <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 mb-6">
        <h4 class="font-medium text-gray-800 mb-2">Experience Level Guide:</h4>
        <div class="text-sm text-gray-600 space-y-1">
            <div><strong>Entry Level (0-1 years):</strong> New to commercial driving, may have CDL but limited experience</div>
            <div><strong>Some Experience (1-3 years):</strong> Basic commercial driving experience, familiar with regulations</div>
            <div><strong>Experienced (3-5 years):</strong> Solid driving record, comfortable with various vehicle types</div>
            <div><strong>Very Experienced (5-10 years):</strong> Extensive experience, leadership potential, specialized skills</div>
            <div><strong>Expert Level (10+ years):</strong> Master driver, mentor capability, extensive industry knowledge</div>
        </div>
    </div>

    <!-- Important Requirements -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="font-medium text-yellow-800 mb-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Important Requirements
        </h4>
        <ul class="text-sm text-yellow-700 space-y-1">
            <li>• Valid CDL with clean driving record required</li>
            <li>• Must pass DOT physical and drug screening</li>
            <li>• Background check and employment verification</li>
            <li>• Ability to work flexible hours as needed</li>
            <li>• Professional appearance and customer service skills</li>
        </ul>
    </div>
</div>