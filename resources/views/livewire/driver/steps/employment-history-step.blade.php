<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Employment History</h3>

    <div class="bg-amber-50 p-4 mb-6 rounded-lg border border-amber-200">
        <p class="text-sm text-gray-700">
            <strong>All driver applicants must provide the following information on all work references during the
                preceding <span class="font-bold">three (3) years</span></strong> from the date application is submitted.
            Those drivers applying to operate a
            <strong>commercial motor vehicle</strong> as defined in §383.5 (requiring a CDL) shall provide
            <strong>ten (10) years</strong> of employment history.
        </p>
        <p class="text-sm text-gray-700 mt-2">
            <strong>NOTE: Please list companies in reverse order starting with the most recent and leave no gaps in
                employment history.</strong>
        </p>
    </div>

    <!-- Unemployment Periods -->
    <div class="mb-6 pb-4">
        <div x-data="{ hasUnemploymentPeriods: @entangle('has_unemployment_periods') }">
            <div class="flex items-center mb-4">
                <input type="checkbox" id="has_unemployment_periods" wire:model.live="has_unemployment_periods"
                    x-model="hasUnemploymentPeriods"
                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="has_unemployment_periods" class="text-sm font-medium">
                    Have you been unemployed at any time within the last 10 years?
                </label>
            </div>

            <div x-show="hasUnemploymentPeriods" x-transition class="mt-2">
                <!-- Botón para agregar un nuevo período de desempleo -->
                <button type="button" wire:click="addUnemploymentPeriod"
                    class="mb-4 bg-primary text-white py-1.5 px-3 rounded-md text-sm hover:bg-blue-800 transition">
                    <i class="fas fa-plus mr-1"></i> Add Unemployment Period
                </button>

                <!-- Tabla de períodos de desempleo existentes -->
                @if (count($unemployment_periods) > 0)
                    <div class="overflow-x-auto">
                        <x-base.table>
                            <x-base.table.thead>
                                <x-base.table.tr>

                                    <x-base.table.th class="whitespace-nowrap">
                                        Start Date
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        End Date
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Comment
                                    </x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach ($unemployment_periods as $index => $period)
                                    <x-base.table.tr>
                                        <x-base.table.td>{{ !empty($period['start_date']) ? \Carbon\Carbon::parse($period['start_date'])->format('m/d/Y') : '-' }}</x-base.table.td>
                                        <x-base.table.td>{{ !empty($period['end_date']) ? \Carbon\Carbon::parse($period['end_date'])->format('m/d/Y') : '-' }}</x-base.table.td>
                                        <x-base.table.td>{{ $period['comments'] ?? '-' }}</x-base.table.td>
                                    </x-base.table.tr>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                @else
                    <p class="text-gray-500 italic text-sm">No unemployment periods added yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Other Driving-Related Employment -->
    <div class="mb-6 border-b pb-4">
        <h4 class="font-medium text-md mb-3">Other employment</h4>
        <p class="text-sm text-gray-700 mb-4">
            Include any other job positions (e.g., Cook, Warehouseman, Carpenter, Clerk) that are not part of your
            previous regular employment history. These positions also count toward the 10-year work history requirement.
        </p>

        <!-- Button to add new related employment -->
        <button type="button" wire:click="addRelatedEmployment"
            class="mb-4 bg-primary text-white py-1.5 px-3 rounded-md text-sm hover:bg-blue-800 transition">
            <i class="fas fa-plus mr-1"></i> Add another job position
        </button>

        <!-- Table of related employments -->
        @if (count($related_employments) > 0)
            <div class="overflow-x-auto">
                <x-base.table bordered hover>
                    <x-base.table.thead>
                        <x-base.table.tr>

                            <x-base.table.th class="whitespace-nowrap">
                                Start Date
                            </x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">
                                End Date
                            </x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">
                                Position
                            </x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">
                                Comment
                            </x-base.table.th>
                            {{-- <x-base.table.th class="whitespace-nowrap">
                                Actions
                            </x-base.table.th> --}}
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($related_employments as $index => $employment)
                            <x-base.table.tr>
                                <x-base.table.td>{{ !empty($employment['start_date']) ? \Carbon\Carbon::parse($employment['start_date'])->format('m/d/Y') : '-' }}</x-base.table.td>
                                <x-base.table.td>{{ !empty($employment['end_date']) ? \Carbon\Carbon::parse($employment['end_date'])->format('m/d/Y') : '-' }}</x-base.table.td>
                                <x-base.table.td>{{ $employment['position'] ?? '-' }}</x-base.table.td>
                                <x-base.table.td>{{ $employment['comments'] ?? '-' }}</x-base.table.td>
                                {{-- <x-base.table.td>
                                    <div class="flex space-x-2">
                                        <button type="button" wire:click="editRelatedEmployment({{ $index }})"
                                            class="text-blue-600 hover:text-blue-800">
                                            edit
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            wire:click="forceDeleteRelatedEmployment({{ !empty($employment['id']) ? $employment['id'] : 0 }})"
                                            class="text-red-600 hover:text-red-800">
                                            delete
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </x-base.table.td> --}}
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
        @else
            <p class="text-gray-500 italic text-sm">No driving-related positions added yet.</p>
        @endif
    </div>
    <!-- Employment History Summary -->
    <div class="mb-6">
        <h4 class="font-medium text-lg mb-3">Employment History Summary</h4>
        <div class="overflow-x-auto">
            <x-base.table bordered hover>
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.th class="whitespace-nowrap">
                            Status
                        </x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">
                            Note
                        </x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">
                            Start Date
                        </x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">
                            End Date
                        </x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap">
                            Actions
                        </x-base.table.th>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @forelse ($combinedEmploymentHistory as $item)
                        <x-base.table.tr>
                            <x-base.table.td>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                  {{ $item['type'] == 'employed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $item['status'] }}
                                </span></x-base.table.td>
                            <x-base.table.td>{{ $item['note'] }}</x-base.table.td>
                            <x-base.table.td>{{ \Carbon\Carbon::parse($item['from_date'])->format('m/d/Y') }}</x-base.table.td>
                            <x-base.table.td>{{ \Carbon\Carbon::parse($item['to_date'])->format('m/d/Y') }}</x-base.table.td>
                            <x-base.table.td>
                                <div class="flex space-x-2">
                                    <button type="button"
                                        wire:click="@if ($item['type'] == 'employed') editEmploymentCompany({{ $item['original_index'] }}) @elseif($item['type'] == 'related') editRelatedEmployment({{ $item['original_index'] }}) @else editUnemploymentPeriod({{ $item['original_index'] }}) @endif"
                                        class="text-blue-500 hover:text-blue-700">
                                        edit
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                        wire:click="@if ($item['type'] == 'employed') confirmDeleteEmploymentCompany({{ $item['original_index'] }}) @elseif($item['type'] == 'related') forceDeleteRelatedEmployment({{ !empty($related_employments[$item['original_index']]['id']) ? $related_employments[$item['original_index']]['id'] : 0 }}) @else confirmDeleteUnemploymentPeriod({{ $item['original_index'] }}) @endif"
                                        class="text-red-500 hover:text-red-700">
                                        Delete
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @empty
                        <x-base.table.tr>
                            <x-base.table.td colspan="5" class="text-center">No employment records found. Please add
                                your employment history below.</x-base.table.td>
                        </x-base.table.tr>
                    @endforelse
                </x-base.table.tbody>
            </x-base.table>

        </div>
        <div class="flex justify-between items-center mt-4">
            <div>
                @if ($years_of_history < 10)
                    <p class="text-red-500 text-sm">
                        You have to enter a minimum of 10 years before continuing. Currently: {{ $years_of_history }}
                        years.
                    </p>
                @else
                    <p class="text-green-500 text-sm">
                        You have entered {{ $years_of_history }} years of history. Minimum required: 10 years.
                    </p>
                @endif
            </div>
            <div class="flex space-x-2">
                <x-base.button class="" variant="outline-success" wire:click="openSearchCompanyModal">
                    Search Company
                </x-base.button>
                <button type="button" wire:click="addEmploymentCompany"
                    class="bg-primary text-white py-1.5 px-3 rounded text-sm hover:bg-blue-800 transition">
                    <i class="fas fa-plus mr-1"></i> Add New Employment
                </button>

            </div>
        </div>
    </div>

    <!-- Modal para Unemployment Periods -->
    @if ($showUnemploymentForm)
        <div class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s] overflow-y-auto show">            
            <div class="w-[90%] mx-auto bg-white relative rounded-md shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-4 group-[.show]:mt-40 group-[.modal-static]:scale-[1.05] sm:w-[750px] p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="mr-auto text-base font-medium">{{ $editing_unemployment_index !== null ? 'Edit' : 'Add' }}
                        Unemployment Period</h3>
                    <button wire:click="closeUnemploymentForm" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>                        
                        <x-base.form-label for="unemployment_form.start_date">Start Date*</x-base.form-label>   
                        <input type="date" wire:model="unemployment_form.start_date"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('unemployment_form.start_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>                        
                        <x-base.form-label for="unemployment_form.end_date">Start Date*</x-base.form-label>   
                        <input type="date" wire:model="unemployment_form.end_date"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('unemployment_form.end_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <x-base.form-label for="unemployment_form.comments">Comments</x-base.form-label> 
                    <x-base.form-textarea wire:model="unemployment_form.comments" class="w-full px-3 py-2 border rounded"
                    rows="3" placeholder="Add any relevant details about this unemployment period" />                    
                </div>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary" wire:click="closeUnemploymentForm">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="primary" id="submit-service" wire:click="saveUnemploymentPeriod">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </div>
        </div>
    @endif

    <!-- Modal para Employment Companies -->
    @if ($showCompanyForm && !$showSearchCompanyModal)
        <div class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s] overflow-y-auto show">
            <div class="w-[90%] mx-auto bg-white relative rounded-md shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-4 group-[.show]:mt-4 group-[.modal-static]:scale-[1.05] sm:w-[750px] p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ $editing_company_index !== null ? 'Edit' : 'Add' }} Employment
                        Information</h3>
                    <button wire:click="closeCompanyForm" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Información de la empresa (MasterCompany) -->
                <div class="border p-4 rounded-lg bg-gray-50 mb-4">
                    <h4 class="text-md font-medium mb-3">Company Information</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name*</label>
                            <input type="text" wire:model="company_form.company_name"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                                {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                                placeholder="Enter company name">
                            @error('company_form.company_name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" wire:model="company_form.phone"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                                {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                                placeholder="Enter phone number">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model="company_form.email"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                            {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                            placeholder="Enter company email">
                        @error('company_form.email')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" wire:model="company_form.address"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                            {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                            placeholder="Enter address">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" wire:model="company_form.city"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                                {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                                placeholder="Enter city">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <select wire:model="company_form.state"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                                {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'disabled' : '' }}>
                                <option value="">Select State</option>
                                @foreach ($usStates as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ZIP</label>
                            <input type="text" wire:model="company_form.zip"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                                {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                                placeholder="Enter ZIP code">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                            <input type="text" wire:model="company_form.contact"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                                {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                                placeholder="Enter contact name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fax</label>
                            <input type="text" wire:model="company_form.fax"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'bg-gray-100' : '' }}"
                                {{ isset($company_form['is_from_master']) && $company_form['is_from_master'] ? 'readonly' : '' }}
                                placeholder="Enter fax number">
                        </div>
                    </div>
                </div>

                <!-- Campos de Employment Information -->
                <div class="border p-4 rounded-lg mb-4">
                    <h4 class="text-md font-medium mb-3">Employment Details</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employed From*</label>
                            <input type="date" wire:model="company_form.employed_from"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            @error('company_form.employed_from')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employed To*</label>
                            <input type="date" wire:model="company_form.employed_to"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            @error('company_form.employed_to')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position(s) Held*</label>
                        <input type="text" wire:model="company_form.positions_held"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter positions held">
                        @error('company_form.positions_held')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="subject_to_fmcsr" wire:model="company_form.subject_to_fmcsr"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                            <label for="subject_to_fmcsr" class="text-sm">
                                Were you subject to the Federal Motor Carrier Safety Regulations while employed by this
                                employer?
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="safety_sensitive_function"
                                wire:model="company_form.safety_sensitive_function"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                            <label for="safety_sensitive_function" class="text-sm">
                                Was this job designated as a safety sensitive function in any D.O.T. regulated mode
                                subject to alcohol and controlled substance testing requirements as required by 49 CFR
                                Part 40?
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Leaving*</label>
                        <select wire:model="company_form.reason_for_leaving"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select Reason</option>
                            <option value="resignation">Resignation</option>
                            <option value="termination">Termination</option>
                            <option value="layoff">Layoff</option>
                            <option value="retirement">Retirement</option>
                            <option value="other">Other</option>
                        </select>
                        @error('company_form.reason_for_leaving')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($company_form['reason_for_leaving'] === 'other')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">If other, please
                                describe*</label>
                            <input type="text" wire:model="company_form.other_reason_description"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                placeholder="Describe reason for leaving">
                            @error('company_form.other_reason_description')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Explanation</label>
                        <textarea wire:model="company_form.explanation" rows="2"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="Additional explanation..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button wire:click="closeCompanyForm"
                        class="bg-gray-300 text-gray-700 py-2 px-4 rounded mr-2 hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button wire:click="saveCompany"
                        class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para Búsqueda de Empresas -->
    @if ($showSearchCompanyModal && !$showCompanyForm)
        <div class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s] overflow-y-auto show">
            <div class="w-[90%] mx-auto bg-white relative rounded-md shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-4 group-[.show]:mt-40 group-[.modal-static]:scale-[1.05] sm:w-[750px] p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Search Previous Employer</h3>
                    <button wire:click="closeSearchCompanyModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search Box -->
                <div class="mb-6">
                    <div class="flex items-center border rounded-md overflow-hidden">
                        <input type="text" wire:model.live.debounce.300ms="companySearchTerm"
                            placeholder="Search by company name..."
                            class="w-full p-2 border-none focus:outline-none focus:ring-0">
                        <div class="bg-gray-100 p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Search Results -->
                <div class="overflow-x-auto">

                    <x-base.table bordered hover>
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="whitespace-nowrap">
                                    COMPANY NAME
                                </x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">
                                    CITY
                                </x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">
                                    STATE
                                </x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">
                                    Action
                                </x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @if (count($searchResults) > 0)
                                @foreach ($searchResults as $company)
                                    <x-base.table.tr>
                                        <x-base.table.td>{{ $company['company_name'] }}</x-base.table.td>
                                        <x-base.table.td>{{ $company['city'] }}</x-base.table.td>
                                        <x-base.table.td>{{ $company['state'] }}</x-base.table.td>
                                        <x-base.table.td>                                                
                                            <x-base.button class="" variant="primary" wire:click="selectCompany({{ $company['id'] }})">
                                                Select
                                            </x-base.button>                                       
                                        </x-base.table.td>
                                    </x-base.table.tr>
                                @endforeach
                            @else
                                <x-base.table.tr>
                                    <x-base.table.td colspan="4" class="text-center">
                                        @if ($companySearchTerm)
                                            No companies found matching "{{ $companySearchTerm }}"
                                        @else
                                            Start typing to search for companies
                                        @endif
                                    </x-base.table.td>
                                </x-base.table.tr>
                            @endif
                        </x-base.table.tbody>
                    </x-base.table>
                </div>

                <div class="mt-4 flex justify-end">                    
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-primary" wire:click="closeSearchCompanyModal">
                        Cancel
                    </x-base.button>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Modal for Related Employment Form -->
    @if ($showRelatedEmploymentForm)
        <div class="modal group bg-gradient-to-b from-theme-1/50 via-theme-2/50 to-black/50 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s] overflow-y-auto show">
            <div class="w-[90%] mx-auto bg-white relative rounded-sm shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-4 group-[.show]:mt-40 group-[.modal-static]:scale-[1.05] sm:w-[750px] p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="mr-auto text-base font-medium">{{ $editing_related_employment_index !== null ? 'Edit' : 'Add' }}
                        Other Job Position</h3>
                    <button wire:click="closeRelatedEmploymentForm" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="mt-3 w-full flex-1 xl:mt-0">
                        <x-base.form-label for="related_employment_form.start_date">Start Date*</x-base.form-label>                        
                        <input type="date" wire:model="related_employment_form.start_date"
                            class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm">
                        @error('related_employment_form.start_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>                        
                        <x-base.form-label for="related_employment_form.end_date">End Date*</x-base.form-label>
                        <input type="date" wire:model="related_employment_form.end_date"
                            class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm">
                        @error('related_employment_form.end_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">                    
                    <x-base.form-label for="related_employment_form.position">Position*</x-base.form-label>
                    {{-- <input type="text" wire:model="related_employment_form.position"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="E.g. Taxi Driver, Forklift Operator"> --}}
                    <x-base.form-input type="text" wire:model="related_employment_form.position"
                        class="w-full px-3 py-2 border rounded" placeholder="E.g. Taxi Driver, Forklift Operator" />
                    @error('related_employment_form.position')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-4">
                    <x-base.form-label for="related_employment_form.comments">Comments</x-base.form-label>                    
                    {{-- <textarea wire:model="related_employment_form.comments" rows="3"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="Add any relevant details about this position"></textarea> --}}
                        <x-base.form-textarea wire:model="related_employment_form.comments" class="w-full px-3 py-2 border rounded"
                        rows="3" placeholder="Add any relevant details about this position" />
                </div>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary" wire:click="closeRelatedEmploymentForm">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="primary" id="submit-service" wire:click="saveRelatedEmployment">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </div>
        </div>
    @endif

    @if ($showDeleteConfirmationModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Delete</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        Are you sure you want to delete this
                        @if ($deleteType === 'employment')
                            employment
                        @elseif ($deleteType === 'unemployment')
                            unemployment
                        @else
                            driving-related employment
                        @endif
                        record? This action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="cancelDelete"
                        class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmDelete"
                        class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Employment history validation -->
    <div class="flex items-center mb-6">
        <input type="checkbox" id="has_completed_employment_history" wire:model="has_completed_employment_history"
            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
            {{ $years_of_history < 10 ? 'disabled' : '' }}>
        <label for="has_completed_employment_history"
            class="text-sm font-medium {{ $years_of_history < 10 ? 'text-gray-400' : 'text-gray-700' }}">
            <span class="text-red-500">*</span> Is the information above correct and contains no missing information?
        </label>
    </div>

    @if ($years_of_history < 10)
        <div class="mt-2 p-3 bg-red-50 rounded-md border border-red-200">
            <p class="text-red-500 text-sm font-medium">
                You must enter the required number of years of work/unemployment with no gaps.
            </p>
        </div>
    @endif

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
