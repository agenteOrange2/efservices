@extends('../themes/' . $activeTheme)
@section('title', 'All Drivers Overview')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers Overview', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container px-6 mx-auto grid">
    <!-- Page Header -->
    <div class="flex justify-between items-center py-4 mb-4 border-b">
        <div class="flex items-center">
            <a href="{{ route('admin.drivers.index') }}" class="mr-4 text-slate-500 hover:text-slate-700">
                <i data-lucide="ArrowLeft" class="w-5 h-5"></i>
            </a>
            <h2 class="text-xl font-medium">Driver Details</h2>
        </div>
        {{-- <div>
            <a href="{{ route('admin.drivers.documents.download', $driver->id) }}" class="btn btn-primary">
                <i data-lucide="Download" class="w-4 h-4 mr-1"></i>
                Download Documents
            </a>
        </div> --}}
    </div>
    
    <!-- Driver Profile -->
    <div class="box box--stacked flex flex-col  p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <div class="w-24 h-24 flex-shrink-0">
                <img src="{{ $driver->getProfilePhotoUrlAttribute() }}" 
                     alt="{{ $driver->user->name ?? 'Unknown' }}" 
                     class="w-full h-full rounded-full object-cover border-4 border-white shadow">
            </div>
            <div class="flex-grow text-center md:text-left">
                <h3 class="text-2xl font-bold">
                    {{ $driver->user->name ?? 'Unknown' }} {{ $driver->middle_name }} {{ $driver->last_name }}
                </h3>
                <p class="text-slate-500">{{ $driver->user->email ?? 'No email' }}</p>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start mt-2">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i data-lucide="Circle" class="w-3 h-3 mr-1"></i>
                            {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex items-center text-slate-500 text-sm">
                        <i data-lucide="Calendar" class="w-4 h-4 mr-1"></i>
                        Joined {{ $driver->created_at->format('M d, Y') }}
                    </div>
                    <div class="flex items-center text-slate-500 text-sm">
                        <i data-lucide="Building" class="w-4 h-4 mr-1"></i>
                        {{ $driver->carrier->name ?? 'No carrier' }}
                    </div>
                </div>
            </div>
            <div class="w-full md:w-auto md:ml-auto">
                <div class="bg-slate-50 rounded p-4 text-center">
                    <p class="text-slate-500 text-sm">Profile Completion</p>
                    <div class="text-2xl font-bold mt-1">{{ $driver->completion_percentage ?? 0 }}%</div>
                    <div class="w-full bg-slate-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $driver->completion_percentage ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabbed Content -->
    <div class="box box--stacked flex flex-col mb-6">
        <div class="border-b border-slate-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="driverTabs" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-blue-600 rounded-t-lg active" 
                            id="general-tab" 
                            data-tabs-target="#general" 
                            type="button" 
                            role="tab" 
                            aria-controls="general" 
                            aria-selected="true">General Info</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300" 
                            id="licenses-tab" 
                            data-tabs-target="#licenses" 
                            type="button" 
                            role="tab" 
                            aria-controls="licenses" 
                            aria-selected="false">Licenses</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300" 
                            id="medical-tab" 
                            data-tabs-target="#medical" 
                            type="button" 
                            role="tab" 
                            aria-controls="medical" 
                            aria-selected="false">Medical</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300" 
                            id="history-tab" 
                            data-tabs-target="#history" 
                            type="button" 
                            role="tab" 
                            aria-controls="history" 
                            aria-selected="false">Employment</button>
                </li>
                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300" 
                            id="documents-tab" 
                            data-tabs-target="#documents" 
                            type="button" 
                            role="tab" 
                            aria-controls="documents" 
                            aria-selected="false">Documents</button>
                </li>
            </ul>
        </div>
        
        <div id="driverTabsContent">
            <!-- General Information Tab -->
            <div class="p-6" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Personal Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-slate-500 text-sm">Full Name</p>
                                <p>{{ $driver->user->name ?? 'Unknown' }} {{ $driver->middle_name }} {{ $driver->last_name }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Email Address</p>
                                <p>{{ $driver->user->email ?? 'No email' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Phone Number</p>
                                <p>{{ $driver->phone ?? 'No phone' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Date of Birth</p>
                                <p>{{ $driver->date_of_birth ? $driver->date_of_birth->format('M d, Y') : 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carrier Information -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Carrier Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-slate-500 text-sm">Carrier</p>
                                <p>{{ $driver->carrier->name ?? 'No carrier' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Status</p>
                                <p class="{{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'Active' : 'Inactive' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Joined Date</p>
                                <p>{{ $driver->created_at->format('M d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Application Status</p>
                                <p class="{{ 
                                    $driver->application ? 
                                    ($driver->application->status == 'approved' ? 'text-green-600' : 
                                    ($driver->application->status == 'pending' ? 'text-amber-600' : 'text-red-600')) : 
                                    'text-slate-500' 
                                }}">
                                    {{ $driver->application ? ucfirst($driver->application->status) : 'No Application' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Information -->
                    <div class="border rounded-lg p-4 md:col-span-2">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Address Information</h4>
                        @if($driver->application && $driver->application->addresses->count() > 0)
                            <div class="space-y-4">
                                @foreach($driver->application->addresses as $address)
                                    <div class="{{ !$loop->last ? 'pb-4 border-b' : '' }}">
                                        <p class="font-medium">{{ $address->primary ? 'Current Address' : 'Previous Address' }}</p>
                                        <p>{{ $address->address_line1 }}</p>
                                        @if($address->address_line2)
                                            <p>{{ $address->address_line2 }}</p>
                                        @endif
                                        <p>{{ $address->city }}, {{ $address->state }} {{ $address->zip_code }}</p>
                                        <p class="text-slate-500 text-xs mt-1">
                                            {{ $address->from_date ? $address->from_date->format('M Y') : '' }} 
                                            {{ $address->to_date ? ' - ' . $address->to_date->format('M Y') : ' - Present' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-500">No address information provided</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Licenses Tab -->
            <div class="hidden p-6" id="licenses" role="tabpanel" aria-labelledby="licenses-tab">
                <!-- License Information -->
                <div class="border rounded-lg p-4 mb-6">
                    <h4 class="font-medium text-lg mb-4 pb-2 border-b">License Information</h4>
                    @if($driver->licenses && $driver->licenses->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-2">License Number</th>
                                        <th class="px-4 py-2">State</th>
                                        <th class="px-4 py-2">Class</th>
                                        <th class="px-4 py-2">Type</th>
                                        <th class="px-4 py-2">Expiration</th>
                                        <th class="px-4 py-2">Images</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driver->licenses as $license)
                                        <tr class="border-b">
                                            <td class="px-4 py-2">{{ $license->license_number }}</td>
                                            <td class="px-4 py-2">{{ $license->state_of_issue }}</td>
                                            <td class="px-4 py-2">{{ $license->license_class }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $license->is_cdl ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">
                                                    {{ $license->is_cdl ? 'CDL' : 'Standard' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ $license->expiration_date ? $license->expiration_date->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <div class="flex space-x-2">
                                                    @if($license->getFirstMediaUrl('license_front'))
                                                        <a href="{{ $license->getFirstMediaUrl('license_front') }}" target="_blank" class="text-blue-600 hover:underline">Front</a>
                                                    @endif
                                                    @if($license->getFirstMediaUrl('license_back'))
                                                        <a href="{{ $license->getFirstMediaUrl('license_back') }}" target="_blank" class="text-blue-600 hover:underline">Back</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Endorsements -->
                        @if($driver->licenses->first() && $driver->licenses->first()->endorsements->count() > 0)
                            <div class="mt-4 pt-4 border-t">
                                <h5 class="font-medium mb-2">Endorsements</h5>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($driver->licenses->first()->endorsements as $endorsement)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $endorsement->code }} - {{ $endorsement->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-slate-500">No license information provided</p>
                    @endif
                </div>
                
                <!-- Driving Experience -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-medium text-lg mb-4 pb-2 border-b">Driving Experience</h4>
                    @if($driver->experiences && $driver->experiences->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-2">Equipment Type</th>
                                        <th class="px-4 py-2">Years Experience</th>
                                        <th class="px-4 py-2">Miles Driven</th>
                                        <th class="px-4 py-2">Requires CDL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driver->experiences as $experience)
                                        <tr class="border-b">
                                            <td class="px-4 py-2">{{ $experience->equipment_type }}</td>
                                            <td class="px-4 py-2">{{ $experience->years_experience }}</td>
                                            <td class="px-4 py-2">{{ number_format($experience->miles_driven) }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $experience->requires_cdl ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">
                                                    {{ $experience->requires_cdl ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-slate-500">No driving experience information provided</p>
                    @endif
                </div>
            </div>
            
            <!-- Medical Tab -->
            <div class="hidden p-6" id="medical" role="tabpanel" aria-labelledby="medical-tab">
                <div class="border rounded-lg p-4">
                    <h4 class="font-medium text-lg mb-4 pb-2 border-b">Medical Qualification</h4>
                    @if($driver->medicalQualification)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-slate-500 text-sm">Medical Examiner</p>
                                <p>{{ $driver->medicalQualification->medical_examiner_name }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Registry Number</p>
                                <p>{{ $driver->medicalQualification->medical_examiner_registry_number }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Medical Card Expiration</p>
                                <p class="flex items-center">
                                    {{ $driver->medicalQualification->medical_card_expiration_date ? $driver->medicalQualification->medical_card_expiration_date->format('M d, Y') : 'N/A' }}
                                    @if($driver->medicalQualification->medical_card_expiration_date)
                                        @if($driver->medicalQualification->medical_card_expiration_date->isPast())
                                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>
                                        @elseif($driver->medicalQualification->medical_card_expiration_date->diffInDays(now()) < 30)
                                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Expiring Soon</span>
                                        @else
                                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Valid</span>
                                        @endif
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm">Social Security (Last 4)</p>
                                <p>
                                    @if($driver->medicalQualification->social_security_number)
                                        XXX-XX-{{ substr($driver->medicalQualification->social_security_number, -4) }}
                                    @else
                                        Not provided
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- Medical Card -->
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-slate-500 text-sm mb-2">Medical Card</p>
                            @if($driver->medicalQualification->getFirstMediaUrl('medical_card'))
                                <a href="{{ $driver->medicalQualification->getFirstMediaUrl('medical_card') }}" 
                                   target="_blank" 
                                   class="text-blue-600 hover:underline flex items-center">
                                    <i data-lucide="ExternalLink" class="w-4 h-4 mr-1"></i>
                                    View Medical Card
                                </a>
                            @else
                                <p class="text-slate-500">No medical card uploaded</p>
                            @endif
                        </div>
                    @else
                        <p class="text-slate-500">No medical qualification information provided</p>
                    @endif
                </div>
            </div>
            
            <!-- Employment History Tab -->
            <div class="hidden p-6" id="history" role="tabpanel" aria-labelledby="history-tab">
                <div class="border rounded-lg p-4">
                    <h4 class="font-medium text-lg mb-4 pb-2 border-b">Employment History</h4>
                    @if($driver->employmentCompanies && $driver->employmentCompanies->count() > 0)
                        <div class="space-y-4">
                            @foreach($driver->employmentCompanies as $company)
                                <div class="{{ !$loop->last ? 'pb-4 border-b' : '' }}">
                                    <div class="">
                                        <div>
                                            <p class="font-medium">
                                                {{ $company->masterCompany ? $company->masterCompany->company_name : $company->company_name }}
                                            </p>
                                            <p class="text-slate-500">{{ $company->positions_held }}</p>
                                        </div>
                                        <p class="text-slate-500 text-sm mt-1 md:mt-0">
                                            {{ $company->employed_from ? $company->employed_from->format('M Y') : '' }} 
                                            {{ $company->employed_to ? ' - ' . $company->employed_to->format('M Y') : ' - Present' }}
                                        </p>
                                    </div>
                                    
                                    <div >
                                        <div>
                                            <p class="text-slate-500 text-sm">Reason for Leaving:</p>
                                            <p>
                                                {{ ucfirst(str_replace('_', ' ', $company->reason_for_leaving)) }}
                                                @if($company->reason_for_leaving == 'other' && $company->other_reason_description)
                                                    - {{ $company->other_reason_description }}
                                                @endif
                                            </p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-slate-500 text-sm">Safety Regulations:</p>
                                            <div class="space-y-1 mt-1">
                                                <p class="flex items-center">
                                                    <span class="mr-2">Subject to FMCSR:</span>
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $company->subject_to_fmcsr ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800' }}">
                                                        {{ $company->subject_to_fmcsr ? 'Yes' : 'No' }}
                                                    </span>
                                                </p>
                                                
                                                <p class="flex items-center">
                                                    <span class="mr-2">Safety-Sensitive Functions:</span>
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $company->safety_sensitive_function ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800' }}">
                                                        {{ $company->safety_sensitive_function ? 'Yes' : 'No' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500">No employment history available</p>
                    @endif
                </div>
            </div>
            
            <!-- Documents Tab -->
            <div class="hidden p-6" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Driver Documents -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Driver Documents</h4>
                        
                        <ul class="space-y-3">
                            <!-- License Documents -->
                            @if($driver->licenses && $driver->licenses->count() > 0)
                                @foreach($driver->licenses as $license)
                                    @if($license->getFirstMediaUrl('license_front') || $license->getFirstMediaUrl('license_back'))
                                        <li class="pb-2 border-b last:border-b-0 last:pb-0">
                                            <p class="font-medium">Driver's License ({{ $license->license_number }})</p>
                                            <div class="flex space-x-4 mt-1">
                                                @if($license->getFirstMediaUrl('license_front'))
                                                    <a href="{{ $license->getFirstMediaUrl('license_front') }}" 
                                                       target="_blank" 
                                                       class="text-blue-600 hover:underline flex items-center">
                                                        <i data-lucide="Image" class="w-4 h-4 mr-1"></i>
                                                        Front Image
                                                    </a>
                                                @endif
                                                
                                                @if($license->getFirstMediaUrl('license_back'))
                                                    <a href="{{ $license->getFirstMediaUrl('license_back') }}" 
                                                       target="_blank" 
                                                       class="text-blue-600 hover:underline flex items-center">
                                                        <i data-lucide="Image" class="w-4 h-4 mr-1"></i>
                                                        Back Image
                                                    </a>
                                                @endif
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                            
                            <!-- Medical Card -->
                            @if($driver->medicalQualification && $driver->medicalQualification->getFirstMediaUrl('medical_card'))
                                <li class="pb-2 border-b last:border-b-0 last:pb-0">
                                    <p class="font-medium">Medical Card</p>
                                    <a href="{{ $driver->medicalQualification->getFirstMediaUrl('medical_card') }}" 
                                       target="_blank" 
                                       class="text-blue-600 hover:underline flex items-center mt-1">
                                        <i data-lucide="FileText" class="w-4 h-4 mr-1"></i>
                                        View Medical Card
                                    </a>
                                </li>
                            @endif
                            
                            <!-- Training Certificates -->
                            @php
                                $hasCertificates = false;
                                foreach($driver->trainingSchools as $school) {
                                    if($school->getMedia('school_certificates')->count() > 0) {
                                        $hasCertificates = true;
                                        break;
                                    }
                                }
                            @endphp
                            
                            @if($hasCertificates)
                                <li class="pb-2 border-b last:border-b-0 last:pb-0">
                                    <p class="font-medium">Training Certificates</p>
                                    <div class="space-y-2 mt-1">
                                        @foreach($driver->trainingSchools as $school)
                                            @if($school->getMedia('school_certificates')->count() > 0)
                                                <div>
                                                    <p class="text-slate-500 text-sm">{{ $school->school_name }}</p>
                                                    <div class="flex flex-wrap gap-2 mt-1">
                                                        @foreach($school->getMedia('school_certificates') as $certificate)
                                                            <a href="{{ $certificate->getUrl() }}" 
                                                               target="_blank" 
                                                               class="text-blue-600 hover:underline flex items-center text-sm">
                                                                <i data-lucide="FileText" class="w-3 h-3 mr-1"></i>
                                                                {{ Str::limit($certificate->file_name, 20) }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            @endif
                            
                            <!-- Download All -->
                            <li class="pt-2 mt-2 border-t">
                                <a href="{{ route('admin.drivers.documents.download', $driver->id) }}" 
                                   class="text-blue-600 hover:underline flex items-center font-medium">
                                    <i data-lucide="Download" class="w-4 h-4 mr-1"></i>
                                    Download All Documents (ZIP)
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Application Documents -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-lg mb-4 pb-2 border-b">Application Documents</h4>
                        
                        <div class="space-y-3">
                            @if($driver->application && $driver->application->hasMedia('application_pdf'))
                                <div class="pb-3 mb-3 border-b">
                                    <p class="font-medium">Complete Application PDF</p>
                                    <a href="{{ $driver->application->getFirstMediaUrl('application_pdf') }}" 
                                       target="_blank" 
                                       class="text-blue-600 hover:underline flex items-center mt-1">
                                        <i data-lucide="FileText" class="w-4 h-4 mr-1"></i>
                                        View Complete Application
                                    </a>
                                </div>
                            @endif
                            
                            <!-- Lease Agreement Documents -->
                            @php
                                $basePath = storage_path('app/public/driver/' . $driver->id . '/vehicle_verifications/');
                                $leaseAgreementThirdPartyPath = $basePath . 'lease_agreement_third_party.pdf';
                                $leaseAgreementOwnerPath = $basePath . 'lease_agreement_owner_operator.pdf';
                                $hasLeaseAgreementThirdParty = file_exists($leaseAgreementThirdPartyPath);
                                $hasLeaseAgreementOwner = file_exists($leaseAgreementOwnerPath);
                            @endphp
                            
                            @if($hasLeaseAgreementThirdParty || $hasLeaseAgreementOwner)
                                <div>
                                    <p class="font-medium">Lease Agreements</p>
                                    <div class="flex flex-col space-y-2 mt-1">
                                        @if($hasLeaseAgreementThirdParty)
                                            <a href="{{ asset('storage/driver/' . $driver->id . '/vehicle_verifications/lease_agreement_third_party.pdf') }}" 
                                               target="_blank" 
                                               class="text-blue-600 hover:underline flex items-center">
                                                <i data-lucide="FileText" class="w-4 h-4 mr-1"></i>
                                                Third Party Lease Agreement
                                            </a>
                                        @endif
                                        
                                        @if($hasLeaseAgreementOwner)
                                            <a href="{{ asset('storage/driver/' . $driver->id . '/vehicle_verifications/lease_agreement_owner_operator.pdf') }}" 
                                               target="_blank" 
                                               class="text-blue-600 hover:underline flex items-center">
                                                <i data-lucide="FileText" class="w-4 h-4 mr-1"></i>
                                                Owner Operator Lease Agreement
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if(!$driver->application && !$hasLeaseAgreementThirdParty && !$hasLeaseAgreementOwner)
                            <p class="text-slate-500">No application documents available</p>
                        @endif
                        
                        <!-- Application Status -->
                        @if($driver->application)
                            <div class="mt-4 pt-4 border-t">
                                <p class="font-medium mb-2">Application Status</p>
                                
                                <div class="space-y-2">
                                    <div class="flex">
                                        <span class="text-slate-500 w-1/3">Status:</span>
                                        <span class="{{ 
                                            $driver->application->status == 'approved' ? 'text-green-600' : 
                                            ($driver->application->status == 'pending' ? 'text-amber-600' : 'text-red-600') 
                                        }}">
                                            {{ ucfirst($driver->application->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex">
                                        <span class="text-slate-500 w-1/3">Submitted:</span>
                                        <span>{{ $driver->application->created_at->format('M d, Y') }}</span>
                                    </div>
                                    
                                    @if($driver->application->completed_at)
                                        <div class="flex">
                                            <span class="text-slate-500 w-1/3">Completed:</span>
                                            {{-- <span>{{ $driver->application->completed_at->format('M d, Y') }}</span> --}}
                                        </div>
                                    @endif
                                    
                                    @if($driver->application->status == 'rejected' && $driver->application->rejection_reason)
                                        <div class="mt-2 pt-2 border-t">
                                            <p class="text-red-600 font-medium">Rejection Reason:</p>
                                            <p class="mt-1 text-sm bg-red-50 p-2 rounded">
                                                {{ $driver->application->rejection_reason }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Records Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Traffic Convictions -->
        <div class="box box--stacked flex flex-col p-6">
            <h3 class="font-medium text-lg mb-4 pb-2 border-b flex items-center">
                <i data-lucide="AlertTriangle" class="w-5 h-5 mr-2 text-amber-500"></i>
                Traffic Convictions
            </h3>
            
            @if($driver->trafficConvictions && $driver->trafficConvictions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Location</th>
                                <th class="px-4 py-2">Charge</th>
                                <th class="px-4 py-2">Penalty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($driver->trafficConvictions as $conviction)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        {{ $conviction->conviction_date ? $conviction->conviction_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2">{{ $conviction->location }}</td>
                                    <td class="px-4 py-2">{{ $conviction->charge }}</td>
                                    <td class="px-4 py-2">{{ $conviction->penalty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-slate-500">No traffic convictions reported</p>
            @endif
        </div>
        
        <!-- Accidents -->
        <div class="box box--stacked flex flex-col p-6">
            <h3 class="font-medium text-lg mb-4 pb-2 border-b flex items-center">
                <i data-lucide="Car" class="w-5 h-5 mr-2 text-red-500"></i>
                Accidents
            </h3>
            
            @if($driver->accidents && $driver->accidents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Nature</th>
                                <th class="px-4 py-2">Injuries</th>
                                <th class="px-4 py-2">Fatalities</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($driver->accidents as $accident)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        {{ $accident->accident_date ? $accident->accident_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2">{{ $accident->nature_of_accident }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $accident->had_injuries ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $accident->had_injuries ? $accident->number_of_injuries : 'None' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $accident->had_fatalities ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $accident->had_fatalities ? $accident->number_of_fatalities : 'None' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-slate-500">No accidents reported</p>
            @endif
        </div>
    </div>
</div>

<!-- JavaScript for Tab Functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('[role="tab"]');
        const tabPanels = document.querySelectorAll('[role="tabpanel"]');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Deactivate all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-blue-600');
                    btn.classList.add('border-transparent');
                    btn.setAttribute('aria-selected', 'false');
                });
                
                // Hide all panels
                tabPanels.forEach(panel => {
                    panel.classList.add('hidden');
                });
                
                // Activate current tab
                button.classList.remove('border-transparent');
                button.classList.add('border-blue-600');
                button.setAttribute('aria-selected', 'true');
                
                // Show current panel
                const panelId = button.getAttribute('data-tabs-target').substring(1);
                const panel = document.getElementById(panelId);
                panel.classList.remove('hidden');
            });
        });
    });
</script>
@endsection
                                    