<!-- Guardar en resources/views/livewire/admin/driver/steps/step-employment-history.blade.php -->
<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Employment History</h3>

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
    <div class="mb-6 border-b pb-4">
        <div x-data="{ hasUnemploymentPeriods: @entangle('has_unemployment_periods') }">
            <div class="flex items-center mb-4">
                <input type="checkbox" id="has_unemployment_periods" wire:model="has_unemployment_periods"
                    x-model="hasUnemploymentPeriods"
                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                <label for="has_unemployment_periods" class="text-sm font-medium">
                    Have you been unemployed at any time within the last 10 years?
                </label>
            </div>

            <div x-show="hasUnemploymentPeriods" x-transition class="mt-2">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Start Date</th>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    End Date</th>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Comments</th>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($unemployment_periods as $index => $period)
                                <tr>
                                    <td class="py-2 px-3 text-sm">
                                        <input type="date"
                                            wire:model="unemployment_periods.{{ $index }}.start_date"
                                            class="block w-full text-sm border-slate-200 shadow-sm rounded-md py-1.5 px-2">
                                        @error("unemployment_periods.{$index}.start_date")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="py-2 px-3 text-sm">
                                        <input type="date"
                                            wire:model="unemployment_periods.{{ $index }}.end_date"
                                            class="block w-full text-sm border-slate-200 shadow-sm rounded-md py-1.5 px-2">
                                        @error("unemployment_periods.{$index}.end_date")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="py-2 px-3 text-sm">
                                        <input type="text"
                                            wire:model="unemployment_periods.{{ $index }}.comments"
                                            class="block w-full text-sm border-slate-200 shadow-sm rounded-md py-1.5 px-2"
                                            placeholder="Add comments">
                                    </td>
                                    <td class="py-2 px-3 text-sm">
                                        <button type="button"
                                            wire:click="removeUnemploymentPeriod({{ $index }})"
                                            class="text-red-500 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="button" wire:click="addUnemploymentPeriod"
                    class="mt-3 bg-gray-200 text-gray-700 py-1.5 px-3 rounded text-sm hover:bg-gray-300 transition">
                    Add Unemployment Period
                </button>
            </div>
        </div>
    </div>


    <!-- Employment History Summary -->
    <div class="mb-6">
        <h4 class="font-medium text-lg mb-3">Employment History Summary</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-gray-100">
                    <tr>
                        <th
                            class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            Status</th>
                        <th
                            class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            Note</th>
                        <th
                            class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            Start Date</th>
                        <th
                            class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            End Date</th>
                        <th
                            class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($combinedEmploymentHistory as $item)
                        <tr>
                            <td class="py-2 px-3 text-sm border">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $item['type'] == 'employed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-sm border">
                                {{ $item['note'] }}
                            </td>
                            <td class="py-2 px-3 text-sm border">
                                {{ \Carbon\Carbon::parse($item['from_date'])->format('m/d/Y') }}
                            </td>
                            <td class="py-2 px-3 text-sm border">
                                {{ \Carbon\Carbon::parse($item['to_date'])->format('m/d/Y') }}
                            </td>
                            <td class="py-2 px-3 text-sm border">
                                <button type="button"
                                    wire:click="{{ $item['type'] == 'employed' ? 'editEmploymentCompany(' . $item['original_index'] . ')' : 'editUnemploymentPeriod(' . $item['original_index'] . ')' }}"
                                    class="text-blue-500 hover:text-blue-700 mr-2">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 px-3 text-sm text-center text-gray-500 border">
                                No employment records found. Please add your employment history below.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                <button type="button" wire:click="openSearchCompanyModal"
                    class="bg-green-600 text-white py-1.5 px-3 rounded text-sm hover:bg-green-700 transition">
                    <i class="fas fa-search mr-1"></i> Search Company
                </button>
                <button type="button" wire:click="addEmploymentCompany"
                    class="bg-blue-600 text-white py-1.5 px-3 rounded text-sm hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-1"></i> Add New Employment
                </button>
            </div>
        </div>
    </div>

    <!-- Company Form Modal -->
    @if ($showCompanyForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto p-6">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name*</label>
                        <input type="text" wire:model="company_form.company_name"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter company name">
                        @error('company_form.company_name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" wire:model="company_form.phone"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter phone number">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" wire:model="company_form.address"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="Enter address">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" wire:model="company_form.city"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter city">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <!-- En el select de estado -->
                        <select wire:model="company_form.state"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select State</option>
                            @foreach ($usStates as $code => $name)
                                <!-- Cambiar $states por $usStates -->
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ZIP</label>
                        <input type="text" wire:model="company_form.zip"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter ZIP code">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                        <input type="text" wire:model="company_form.contact"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter contact name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fax</label>
                        <input type="text" wire:model="company_form.fax"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Enter fax number">
                    </div>
                </div>

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
                            Was this job designated as a safety sensitive function in any D.O.T. regulated mode subject
                            to alcohol and controlled substance testing requirements as required by 49 CFR Part 40?
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">If other, please describe*</label>
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

    <!-- Search Company Modal -->
    @if ($showSearchCompanyModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto p-6">
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
                        <input type="text" wire:model.debounce.300ms="companySearchTerm"
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
                    <table class="min-w-full bg-white border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                    Company Name</th>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                    City</th>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                    State</th>
                                <th
                                    class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @if (count($searchResults) > 0)
                                @foreach ($searchResults as $company)
                                    <tr>
                                        <td class="py-2 px-3 text-sm border">{{ $company['company_name'] }}</td>
                                        <td class="py-2 px-3 text-sm border">{{ $company['city'] }}</td>
                                        <td class="py-2 px-3 text-sm border">{{ $company['state'] }}</td>
                                        <td class="py-2 px-3 text-sm border">
                                            <button wire:click="selectCompany({{ $company['id'] }})"
                                                class="bg-blue-500 hover:bg-blue-600 text-white rounded px-3 py-1 text-xs">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-gray-500 border">
                                        @if ($companySearchTerm)
                                            No companies found matching "{{ $companySearchTerm }}"
                                        @else
                                            Start typing to search for companies
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end">
                    <button wire:click="closeSearchCompanyModal"
                        class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400 transition">
                        Cancel
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
            Is the information above correct and contains no missing information?
        </label>
    </div>

    @if ($years_of_history < 10)
        <div class="mt-2 p-3 bg-red-50 rounded-md border border-red-200">
            <p class="text-red-500 text-sm font-medium">
                You must enter the required number of years of work/unemployment with no gaps.
            </p>
        </div>
    @endif
</div>
