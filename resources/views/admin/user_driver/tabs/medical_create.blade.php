<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">FMCSA Driver Medical Qualification</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Social Security Number -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Social Security Number <span class="text-red-500">*</span></label>
            <input type="text" name="social_security_number" value="{{ old('social_security_number') }}" placeholder="XXX-XX-XXXX" 
                   class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                   pattern="\d{3}-\d{2}-\d{4}" x-mask="999-99-9999">
            <p class="mt-1 text-xs text-gray-500">Format: XXX-XX-XXXX</p>
            @error('social_security_number')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- Hire Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
            <input type="date" name="hire_date"  
                   class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Location -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <input type="text" name="location"  value="{{ old('location') }}" placeholder="Work location" 
                   class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
        </div>
        
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Suspension Status -->
        <div>
            <div class="flex items-center mb-2">
                <input type="checkbox" name="is_suspended" id="is_suspended" value="1" 
                       class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded"
                       onchange="toggleSuspensionDate(this)">
                <label for="is_suspended" class="ml-2 text-sm">Driver is Suspended</label>
            </div>
            
            <div id="suspension-date-container" class="hidden mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Suspension Date</label>
                <input type="date" name="suspension_date" 
                       class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
        </div>
        
        <!-- Termination Status -->
        <div>
            <div class="flex items-center mb-2">
                <input type="checkbox" name="is_terminated" id="is_terminated" value="1" 
                       class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded"
                       onchange="toggleTerminationDate(this)">
                <label for="is_terminated" class="ml-2 text-sm">Driver is Terminated</label>
            </div>
            
            <div id="termination-date-container" class="hidden mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Termination Date</label>
                <input type="date" name="termination_date" 
                       class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
        </div>
    </div>
    
    <div class="border-t border-gray-200 pt-6 mt-6">
        <h4 class="font-medium text-gray-700 mb-4">Medical Certification Information</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Medical Examiner Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Examiner Name <span class="text-red-500">*</span></label>
                <input type="text" name="medical_examiner_name"  value="{{ old('medical_examiner_name') }}"
                       class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
            
            <!-- Medical Examiner Registry Number -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Examiner Registry Number <span class="text-red-500">*</span></label>
                <input type="text" name="medical_examiner_registry_number"  value="{{ old('medical_examiner_registry_number') }}"
                       class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Medical Card Expiration Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Card Expiration Date <span class="text-red-500">*</span></label>
                <input type="date" name="medical_card_expiration_date" 
                       class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
        </div>
        
        <!-- Medical Card Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Medical Card <span class="text-red-500">*</span></label>
            <div class="flex items-center justify-center w-full">
                <label for="medical_card_file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-3 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-gray-500">PDF, PNG, JPG or JPEG (MAX. 2MB)</p>
                    </div>
                    <input id="medical_card_file" type="file" name="medical_card_file" class="hidden" accept=".pdf,.png,.jpg,.jpeg" />
                </label>
            </div>
            <div id="file-preview" class="mt-2 hidden">
                <p class="text-sm text-gray-500">Selected file: <span id="file-name"></span></p>
                <button type="button" onclick="clearFile()" class="text-sm text-red-500 hover:text-red-700">Remove</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSuspensionDate(checkbox) {
        const container = document.getElementById('suspension-date-container');
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            container.querySelector('input').value = '';
        }
    }
    
    function toggleTerminationDate(checkbox) {
        const container = document.getElementById('termination-date-container');
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            container.querySelector('input').value = '';
        }
    }
    
    // File upload preview
    document.getElementById('medical_card_file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            document.getElementById('file-name').textContent = fileName;
            document.getElementById('file-preview').classList.remove('hidden');
        }
    });
    
    function clearFile() {
        const fileInput = document.getElementById('medical_card_file');
        fileInput.value = '';
        document.getElementById('file-preview').classList.add('hidden');
        document.getElementById('file-name').textContent = '';
    }
</script>