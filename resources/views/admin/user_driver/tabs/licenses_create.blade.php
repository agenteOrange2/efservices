<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Driver's License Information</h3>

    <div class="mb-6 border-b pb-4">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-medium text-gray-700">License Details</h4>
            <button type="button" onclick="addLicense()"
                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                Add Another License
            </button>
        </div>

        {{-- License Number --}}
        <div class="my-16 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Current License Number</div>
                        <div class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input name="current_license_number" type="text" 
                placeholder="Enter Current license number"
                value="{{ old('current_license_number') }}" />
                @error('current_license_number	')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div id="licenses-container">
            <div class="license-entry border rounded-lg p-4 mb-4">
                <!-- License Number -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Number <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="licenses[0][license_number]" value="{{ old('licenses.0.license_number') }}" placeholder="Enter license number"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State of Issue <span
                                class="text-red-500">*</span></label>
                        <select name="licenses[0][state_of_issue]"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-primary">
                            <option value="">Select State</option>
                            @foreach ($usStates as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Class <span
                                class="text-red-500">*</span></label>
                        <select name="licenses[0][license_class]"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">Select Class</option>
                            <option value="A">Class A</option>
                            <option value="B">Class B</option>
                            <option value="C">Class C</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="licenses[0][expiration_date]"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="licenses[0][is_cdl]" value="1" {{ old('licenses.0.is_cdl') ? 'checked' : '' }} id="is_cdl_0"
                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded"
                            onchange="toggleEndorsements(this, 0)">
                        <label for="is_cdl_0" class="ml-2 text-sm">This is a Commercial Driver's License (CDL)</label>
                    </div>
                </div>

                <div id="endorsements-section-0" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endorsements</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="licenses[0][endorsements][]" value="N"
                                id="endorsement_n_0" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="endorsement_n_0" class="ml-2 text-sm">N (Tank)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="licenses[0][endorsements][]" value="H"
                                id="endorsement_h_0" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="endorsement_h_0" class="ml-2 text-sm">H (HAZMAT)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="licenses[0][endorsements][]" value="X"
                                id="endorsement_x_0" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="endorsement_x_0" class="ml-2 text-sm">X (Combo)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="licenses[0][endorsements][]" value="T"
                                id="endorsement_t_0" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="endorsement_t_0" class="ml-2 text-sm">T (Double/Triple)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="licenses[0][endorsements][]" value="P"
                                id="endorsement_p_0"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="endorsement_p_0" class="ml-2 text-sm">P (Passenger)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="licenses[0][endorsements][]" value="S"
                                id="endorsement_s_0"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="endorsement_s_0" class="ml-2 text-sm">S (School Bus)</label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Front Image</label>
                        <input type="file" name="licenses[0][license_front]" accept="image/*"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Back Image</label>
                        <input type="file" name="licenses[0][license_back]" accept="image/*"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 border-b pb-4">
        <h4 class="font-medium text-gray-700 mb-4">Driving Experience</h4>

        <div id="experiences-container">
            <div class="experience-entry border rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600">Vehicle #1</h5>
                    <button type="button" onclick="addExperience()"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                        Add Another Vehicle
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equipment Type</label>
                        <select name="experiences[0][equipment_type]"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">Select Equipment Type</option>
                            <option value="Straight Truck">Straight Truck</option>
                            <option value="Tractor & Semi-Trailer">Tractor & Semi-Trailer</option>
                            <option value="Tractor & Two Trailers">Tractor & Two Trailers</option>
                            <option value="Tractor & Triple Trailers">Tractor & Triple Trailers</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Years of Experience</label>
                        <input type="number" name="experiences[0][years_experience]" min="0" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Miles Driven</label>
                        <input type="number" name="experiences[0][miles_driven]" min="0" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                    <div>
                        <div class="h-8"></div> <!-- Spacer -->
                        <div class="flex items-center">
                            <input type="checkbox" name="experiences[0][requires_cdl]" value="1"
                                id="requires_cdl_0"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="requires_cdl_0" class="ml-2 text-sm">This vehicle requires a CDL</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let licenseCounter = 0;
    let experienceCounter = 1;

    function toggleEndorsements(checkbox, index) {
        const endorsementsSection = document.getElementById(`endorsements-section-${index}`);
        if (endorsementsSection) {
            if (checkbox.checked) {
                endorsementsSection.classList.remove('hidden');
            } else {
                endorsementsSection.classList.add('hidden');
                // Limpiar las selecciones de endorsements cuando se desmarca
                endorsementsSection.querySelectorAll('input[type="checkbox"]').forEach(check => {
                    check.checked = false;
                });
            }
        } else {
            console.error(`No se encontró la sección de endorsements con ID endorsements-section-${index}`);
        }
    }

    function addLicense() {
        const currentIndex = licenseCounter + 1; // Usar el siguiente índice
        const container = document.getElementById('licenses-container');
        const template = document.querySelector('.license-entry').cloneNode(true);

        // Update all name attributes with new index
        template.querySelectorAll('input, select').forEach(element => {
            if (element.name) {
                element.name = element.name.replace(/\[0\]/g, `[${currentIndex}]`);
            }
            if (element.id) {
                element.id = element.id.replace(/_0$/g, `_${currentIndex}`);
            }
        });

        // Update any label for attributes
        template.querySelectorAll('label').forEach(label => {
            if (label.htmlFor) {
                label.htmlFor = label.htmlFor.replace(/_0$/g, `_${licenseCounter}`);
            }
        });

        // Update endorsements section ID
        const endorsementsSection = template.querySelector('[id^="endorsements-section-"]');
        if (endorsementsSection) {
            endorsementsSection.id = `endorsements-section-${licenseCounter}`;
            endorsementsSection.classList.add('hidden');
        }

        // Reset values
        template.querySelectorAll('input[type="text"], input[type="date"], input[type="file"], select').forEach(
            element => {
                element.value = '';
            });

        template.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Add remove button if it's not the first license
        const headerDiv = document.createElement('div');
        headerDiv.className = 'flex justify-between items-center mb-4';

        const header = document.createElement('h5');
        header.className = 'font-medium text-gray-600';
        header.textContent = `License #${licenseCounter + 1}`;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm';
        removeBtn.textContent = 'Remove';
        removeBtn.onclick = function() {
            container.removeChild(template);
        };

        headerDiv.appendChild(header);
        headerDiv.appendChild(removeBtn);

        // Insertar el nuevo header al principio del template
        template.insertBefore(headerDiv, template.firstChild);

        container.appendChild(template);
        licenseCounter = currentIndex;
    }

    function addExperience() {
        const currentIndex = experienceCounter + 1;
        const container = document.getElementById('experiences-container');
        const template = document.querySelector('.experience-entry').cloneNode(true);

        // Update header
        const header = template.querySelector('h5');
        if (header) {
            header.textContent = `Vehicle #${currentIndex + 1}`;
        }

        // Update all name attributes with new index
        template.querySelectorAll('input, select').forEach(element => {
            if (element.name) {
                element.name = element.name.replace(/\[0\]/g, `[${currentIndex}]`);
            }
            if (element.id) {
                element.id = element.id.replace(/_0$/g, `_${currentIndex}`);
            }
        });

        // Update any label for attributes
        template.querySelectorAll('label').forEach(label => {
            if (label.htmlFor) {
                label.htmlFor = label.htmlFor.replace(/_0$/g, `_${experienceCounter}`);
            }
        });

        // Reset values
        template.querySelectorAll('input[type="text"], input[type="number"], select').forEach(element => {
            element.value = '';
        });

        template.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Add remove button
        const buttonContainer = template.querySelector('h5').parentNode;
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm ml-2';
        removeBtn.textContent = 'Remove';
        removeBtn.onclick = function() {
            container.removeChild(template);
        };
        buttonContainer.appendChild(removeBtn);

        container.appendChild(template);        
        experienceCounter = currentIndex;

    }
</script>
