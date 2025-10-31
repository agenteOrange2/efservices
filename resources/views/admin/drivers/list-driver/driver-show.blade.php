@extends('../themes/' . $activeTheme)
@section('title', 'Driver Details')

@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Drivers', 'url' => route('admin.drivers.index')],
['label' => 'Driver Details', 'active' => true],
];

// Calculate document statistics based on ALL document categories from Documents tab
$totalDocuments = 0;
$approvedDocuments = 0;
$pendingDocuments = 0;
$rejectedDocuments = 0;
$expiringDocuments = 0;
$expiredDocuments = 0;

// 1. LICENSES - Count all license documents
foreach ($driver->licenses as $license) {
$totalDocuments += $license->getMedia('license_front')->count();
$totalDocuments += $license->getMedia('license_back')->count();
$totalDocuments += $license->getMedia('license_documents')->count();
}

// 2. MEDICAL DOCUMENTS - Count all medical documents
if ($driver->medicalQualification) {
$medicalCollections = ['medical_certificate', 'test_results', 'additional_documents', 'medical_documents', 'medical_card'];
foreach ($medicalCollections as $collection) {
$totalDocuments += $driver->medicalQualification->getMedia($collection)->count();
}
}

// 3. TRAINING SCHOOLS - Count school certificates
foreach ($driver->trainingSchools as $school) {
$totalDocuments += $school->getMedia('school_certificates')->count();
}

// 4. COURSES - Count course certificates
foreach ($driver->courses as $course) {
$totalDocuments += $course->getMedia('course_certificates')->count();
}

// 5. ACCIDENTS - Count accident images
foreach ($driver->accidents as $accident) {
$totalDocuments += $accident->getMedia('accident-images')->count();
}

// 6. TRAFFIC VIOLATIONS - Count traffic images
foreach ($driver->trafficConvictions as $conviction) {
$totalDocuments += $conviction->getMedia('traffic_images')->count();
}

// 7. TESTING - Count all testing documents
if ($driver->testings) {
foreach ($driver->testings as $testing) {
$totalDocuments += $testing->getMedia('drug_test_pdf')->count();
$totalDocuments += $testing->getMedia('test_results')->count();
$totalDocuments += $testing->getMedia('test_certificates')->count();
}
}

// 8. INSPECTIONS - Count inspection documents
if ($driver->inspections) {
foreach ($driver->inspections as $inspection) {
$totalDocuments += $inspection->getMedia('inspection_documents')->count();
$totalDocuments += $inspection->getMedia()->count(); // All media from inspections
}
}

// 9. VEHICLE VERIFICATIONS - Count PDF files from storage
$vehicleVerificationsPath = "driver/{$driver->id}/vehicle_verifications";
if (\Storage::disk('public')->exists($vehicleVerificationsPath)) {
$vehicleFiles = \Storage::disk('public')->files($vehicleVerificationsPath);
foreach ($vehicleFiles as $filePath) {
$fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
if (strtolower($fileExtension) === 'pdf') {
$totalDocuments++;
}
}
}

// 10. RECORDS - Count all record types
$recordCollections = ['driving_records', 'criminal_records', 'medical_records', 'clearing_house', 'records', 'general', 'documents'];
foreach ($recordCollections as $collection) {
$totalDocuments += $driver->getMedia($collection)->count();
}

// 11. EMPLOYMENT VERIFICATION - Count employment documents
if ($driver->employmentCompanies && $driver->employmentCompanies->count() > 0) {
foreach ($driver->employmentCompanies as $empCompany) {
$totalDocuments += $empCompany->getMedia('employment_verification_documents')->count();

// Count verification tokens with documents
$tokens = \App\Models\Admin\Driver\EmploymentVerificationToken::where('employment_company_id', $empCompany->id)
->whereNotNull('verified_at')
->where('document_path', '!=', null)
->get();
foreach ($tokens as $token) {
if (\Storage::disk('public')->exists($token->document_path)) {
$totalDocuments++;
}
}
}
}

// 12. APPLICATION FORMS - Count all application documents
if ($driver->application) {
$totalDocuments += $driver->application->getMedia('application_pdf')->count();
$totalDocuments += $driver->application->getMedia('signed_application')->count();
}

// Individual application media
$individualApplicationCollections = ['signed_application', 'application_pdf', 'lease_agreement', 'contract_documents', 'application_forms', 'individual_forms'];
foreach ($individualApplicationCollections as $collection) {
$totalDocuments += $driver->getMedia($collection)->count();
}

// Application files from storage
$driverApplicationsPath = "driver/{$driver->id}/driver_applications";
if (\Storage::disk('public')->exists($driverApplicationsPath)) {
$individualFiles = \Storage::disk('public')->files($driverApplicationsPath);
foreach ($individualFiles as $filePath) {
$fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
if (strtolower($fileExtension) === 'pdf') {
$totalDocuments++;
}
}
}

// OTHER DOCUMENTS - Count other/miscellaneous documents
$otherCollections = ['other', 'miscellaneous'];
foreach ($otherCollections as $collection) {
$totalDocuments += $driver->getMedia($collection)->count();
}

// Calculate records uploaded count
$recordsUploaded = $driver->getMedia('driving_records')->count() +
$driver->getMedia('medical_records')->count() +
$driver->getMedia('criminal_records')->count() +
$driver->getMedia('clearing_house')->count();

// Calculate medical status
$medicalStatus = 'Expired';
if ($driver->medicalQualification && $driver->medicalQualification->dot_medical_expiry_date) {
$expiryDate = \Carbon\Carbon::parse($driver->medicalQualification->dot_medical_expiry_date);
if ($expiryDate->isFuture()) {
$medicalStatus = 'Valid';
}
}

// Calculate testing count and status
$testingCount = $driver->testings ? $driver->testings->count() : 0;
$testingStatus = $testingCount > 0 ? 'Tests Completed' : 'No Tests';

// Calculate associated vehicles count
$associatedVehiclesCount = 0;
if ($driver->vehicles) {
$associatedVehiclesCount = $driver->vehicles->count();
} elseif (method_exists($driver, 'assignedVehicles')) {
$associatedVehiclesCount = $driver->assignedVehicles->count();
} elseif (method_exists($driver, 'vehicleAssignments')) {
$associatedVehiclesCount = $driver->vehicleAssignments->count();
}
$vehiclesStatus = $associatedVehiclesCount > 0 ? 'Vehicles Assigned' : 'No Vehicles';

$stats = [
'total_documents' => $totalDocuments,
'approved_documents_count' => $approvedDocuments,
'pending_documents_count' => $pendingDocuments,
'rejected_documents_count' => $rejectedDocuments,
'expiring_documents_count' => $expiringDocuments,
'expired_documents_count' => $expiredDocuments,
'records_uploaded' => $recordsUploaded,
'medical_status' => $medicalStatus,
'testing_count' => $testingCount,
'testing_status' => $testingStatus,
'vehicles_count' => $associatedVehiclesCount,
'vehicles_status' => $vehiclesStatus,
];
@endphp

@section('subcontent')

<!-- Professional Breadcrumbs using x-base component -->
<div class="mb-6">
    <x-base.breadcrumb :links="$breadcrumbLinks" />
</div>

<!-- Professional Header with x-base components -->
<div class="box box--stacked p-8 mb-8">
    <div class="flex flex-col lg:flex-row items-center lg:items-center justify-between gap-6">
        <div class="flex flex-col lg:flex-row  items-center gap-4">
            <div class="p-3">
                @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                <img
                    class="w-20 h-20 rounded-full object-cover border-2 border-white shadow"
                    src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}"
                    alt="{{ $driver->user->name ?? 'Unknown' }}" as="img"
                    content="{{ $driver->user->name ?? 'Unknown' }} {{ $driver->last_name }}" />
                @else
                <x-base.lucide class="w-8 h-8 text-primary" icon="User" />
                @endif
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mb-2">{{ $driver->user->name ?? 'Unknown' }} {{ $driver->middle_name }} {{ $driver->last_name }}</h1>
                    <div class="flex items-center gap-3">
                        <x-base.lucide class="w-5 h-5 text-primary" icon="mail" />
                        <p class="text-slate-500">{{ $driver->user->email ?? 'No email' }}</p>

                    </div>
                    <div class="flex">
                        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start mt-2">
                            <div class="flex items-center text-slate-500 text-sm">
                                <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                                Joined {{ $driver->created_at->format('M d, Y') }}
                            </div>
                            <div class="flex items-center text-slate-500 text-sm">
                                <i data-lucide="Building" class="w-4 h-4 mr-1"></i>
                                {{ $driver->carrier->name ?? 'No carrier' }}
                            </div>

                            <div class="flex items-center text-slate-500 text-sm">
                                @if ($driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE)
                                <x-base.badge variant="success" class="gap-1.5">
                                    <span class="w-2 h-2 bg-success rounded-full"></span>
                                    Active
                                </x-base.badge>
                                @elseif ($driver->status == App\Models\UserDriverDetail::STATUS_INACTIVE)
                                <x-base.badge variant="danger" class="gap-1.5">
                                    <span class="w-2 h-2 bg-danger rounded-full"></span>
                                    Inactive
                                </x-base.badge>
                                @elseif ($driver->status == App\Models\UserDriverDetail::STATUS_PENDING)
                                <x-base.badge variant="warning" class="gap-1.5">
                                    <span class="w-2 h-2 bg-warning rounded-full"></span>
                                    Pending
                                </x-base.badge>
                                @else
                                <x-base.badge variant="secondary" class="gap-1.5">
                                    <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                                    Unknown
                                </x-base.badge>
                                @endif
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <x-base.button as="a" href="{{ route('admin.drivers.index') }}" variant="secondary" class="gap-2">
                <x-base.lucide class="w-4 h-4" icon="ArrowLeft" />
                Back to List
            </x-base.button>
            @if($stats['total_documents'] > 0)
            <form action="{{ route('admin.drivers.documents.download', $driver->id) }}" method="GET" class="inline">
                <x-base.button type="submit" variant="primary" class="gap-2">
                    <x-base.lucide class="w-4 h-4" icon="Download" />
                    Download All Documents
                </x-base.button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-6 mt-5">
    <!-- Professional Information Section -->
    <div class="col-span-12 lg:col-span-6">
        <div class="box box--stacked flex flex-col p-6 h-fit">
            <div class="flex items-center gap-3 mb-6">
                <x-base.lucide class="w-5 h-5 text-primary" icon="Info" />
                <h2 class="text-lg font-semibold text-slate-800">Main Information</h2>
            </div>

            <!-- Professional Photo Section -->
            <div class="flex justify-center md:justify-start mb-6">
                @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                <div class="relative group">
                    <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}" alt="Driver Photo"
                        class="w-32 h-32 object-cover border-2 border-dashed border-primary/20 rounded-xl p-1 bg-slate-50/50 group-hover:border-primary/40 transition-colors">
                </div>
                @else
                <div class="w-32 h-32 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl flex items-center justify-center border-2 border-dashed border-slate-200">
                    <x-base.lucide class="w-12 h-12 text-slate-400" icon="User" />
                </div>
                @endif
            </div>

            <!-- Professional Information Grid -->
            <div class="space-y-3">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Name</label>
                        <p class="text-sm font-semibold text-slate-800"> {{ $driver->user->name ?? 'Unknown' }} {{ $driver->middle_name }} {{ $driver->last_name }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Email</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $driver->user->email ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Phone</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $driver->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Date of Birth</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $driver->date_of_birth ? $driver->date_of_birth->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Carrier Information</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Carrier Name</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $driver->carrier->name ?? 'No carrier' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Address</label>
                        <p class="text-sm font-semibold text-slate-800">{{ $driver->carrier->address ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Status</label>
                        <div class="mt-2">
                            @if ($driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE)
                            <x-base.badge variant="success" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-success rounded-full"></span>
                                Active
                            </x-base.badge>
                            @elseif ($driver->status == App\Models\UserDriverDetail::STATUS_INACTIVE)
                            <x-base.badge variant="danger" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-danger rounded-full"></span>
                                Inactive
                            </x-base.badge>
                            @elseif ($driver->status == App\Models\UserDriverDetail::STATUS_PENDING)
                            <x-base.badge variant="warning" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-warning rounded-full"></span>
                                Pending
                            </x-base.badge>
                            @else
                            <x-base.badge variant="secondary" class="gap-1.5">
                                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                Unknown
                            </x-base.badge>
                            @endif
                        </div>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1 block">Application Status</label>
                        <div class="mt-2">
                            <x-base.badge class="gap-1.5">
                                <span class="w-1.5 h-1.5 {{ $driver->application
                                            ? ($driver->application->status == 'approved'
                                                ? 'text-green-600'
                                                : ($driver->application->status == 'pending'
                                                    ? 'text-amber-600'
                                                    : 'text-red-600'))
                                            : 'text-slate-500' }}"></span>
                                {{ $driver->application ? ucfirst($driver->application->status) : 'No Application' }}
                            </x-base.badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Central - Estadísticas y Pestañas -->
    <div class="col-span-12 lg:col-span-6 space-y-6">
        <!-- Professional Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Total Documents Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Documents</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-primary transition-colors">{{ $stats['total_documents'] }}</h3>
                    </div>
                    <div class="p-3 bg-primary/10 rounded-xl group-hover:bg-primary/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-primary" icon="FileText" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="TrendingUp" />
                    Documents uploaded
                </div>
            </div>

            <!-- Licences Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Licences</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-info transition-colors">{{ $driver->licenses ? $driver->licenses->count() : 0 }}</h3>
                    </div>
                    <div class="p-3 bg-info/10 rounded-xl group-hover:bg-info/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-info" icon="CreditCard" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="Award" />
                    Licenses issued
                </div>
            </div>

            <!-- Medical Status Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Medical Status</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-{{ $stats['medical_status'] == 'Valid' ? 'success' : 'danger' }} transition-colors">
                            {{ $stats['medical_status'] }}
                        </h3>
                    </div>
                    <div class="p-3 bg-{{ $stats['medical_status'] == 'Valid' ? 'success' : 'danger' }}/10 rounded-xl group-hover:bg-{{ $stats['medical_status'] == 'Valid' ? 'success' : 'danger' }}/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-{{ $stats['medical_status'] == 'Valid' ? 'success' : 'danger' }}" icon="Heart" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="Calendar" />
                    @if($driver->medicalQualification && $driver->medicalQualification->dot_medical_expiry_date)
                    Expires: {{ \Carbon\Carbon::parse($driver->medicalQualification->dot_medical_expiry_date)->format('M d, Y') }}
                    @else
                    DOT medical status
                    @endif
                </div>
            </div>

            <!-- Records Uploaded Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Records Uploaded</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-primary transition-colors">{{ $stats['records_uploaded'] }}</h3>
                    </div>
                    <div class="p-3 bg-primary/10 rounded-xl group-hover:bg-primary/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-primary" icon="FileText" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="Database" />
                    Background records
                </div>
            </div>

            <!-- Testing Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Testing</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-warning transition-colors">{{ $stats['testing_count'] }}</h3>
                    </div>
                    <div class="p-3 bg-warning/10 rounded-xl group-hover:bg-warning/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-warning" icon="TestTube" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="Activity" />
                    {{ $stats['testing_status'] }}
                </div>
            </div>

            <!-- Associated Vehicles Card -->
            <div class="box box--stacked p-6 hover:shadow-lg transition-all duration-200 group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 mb-1">Associated Vehicles</p>
                        <h3 class="text-3xl font-bold text-slate-800 group-hover:text-info transition-colors">{{ $stats['vehicles_count'] }}</h3>
                    </div>
                    <div class="p-3 bg-info/10 rounded-xl group-hover:bg-info/20 transition-colors">
                        <x-base.lucide class="w-7 h-7 text-info" icon="Truck" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <x-base.lucide class="w-3 h-3 mr-1" icon="Link" />
                    {{ $stats['vehicles_status'] }}
                </div>
            </div>

        </div>

        <!-- Professional Document Status -->
        <div class="box box--stacked flex flex-col p-6">
            <div class="flex items-center gap-3 mb-6">
                <x-base.lucide class="w-5 h-5 text-primary" icon="BarChart2" />
                <h2 class="text-lg font-semibold text-success">Upload Files</h2>
            </div>

            <div class="space-y-6">
                <!-- Approved Documents -->
                <div class="bg-slate-50/50 rounded-lg p-4 border border-slate-100">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <x-base.lucide class="w-4 h-4 text-success" icon="CheckCircle" />
                            <p class="text-sm font-medium text-slate-700">Approved Documents</p>
                        </div>
                        <x-base.badge variant="success" class="text-xs">
                            {{ $stats['total_documents'] }} / {{ $stats['total_documents'] }}
                        </x-base.badge>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3">
                        <div class="bg-success h-3 rounded-full transition-all duration-300"
                            style="width: 100%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  Columna tabs -->
    <div class="col-span-12 lg:col-span-12">
        <!-- Professional Tabs Section -->
        <div class="box box--stacked flex flex-col p-6 mt-6">
            <div class="flex items-center gap-3 mb-6">
                <x-base.lucide class="w-5 h-5 text-primary" icon="LayoutGrid" />
                <h2 class="text-lg font-semibold text-slate-800">Detailed Information</h2>
            </div>

            <!-- Professional Tab Navigation -->
            <div class="border-b border-slate-200">
                <nav class="flex space-x-1 overflow-x-auto scrollbar-hide flex-col md:flex-row" aria-label="Tabs">
                    <x-base.tab id="general-tab" :selected="true">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-general"
                            aria-controls="tab-content-general"
                            aria-selected="true">
                            <x-base.lucide class="w-4 h-4" icon="User" />
                            <span class="hidden sm:inline">General</span>
                            <span class="sm:hidden">General</span>
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="licenses-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-licenses"
                            aria-controls="tab-content-licenses"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="CreditCard" />
                            <span class="hidden sm:inline">Licenses</span>
                            <span class="sm:hidden">Licenses</span>
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="medical-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-medical"
                            aria-controls="tab-content-medical"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="Heart" />
                            <span class="hidden sm:inline">Medical</span>
                            <span class="sm:hidden">Medical</span>
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="employment-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-employment"
                            aria-controls="tab-content-employment"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="Briefcase" />
                            <span class="hidden sm:inline">Employment</span>
                            <span class="sm:hidden">Work</span>
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="training-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-training"
                            aria-controls="tab-content-training"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="GraduationCap" />
                            <span class="hidden sm:inline">Training</span>
                            <span class="sm:hidden">Training</span>
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="testing-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-testing"
                            aria-controls="tab-content-testing"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="ClipboardCheck" />
                            <span class="hidden sm:inline">Testing</span>
                            <span class="sm:hidden">Tests</span>
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="inspections-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-inspections"
                            aria-controls="tab-content-inspections"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="Search" />
                            <span class="hidden sm:inline">Inspections</span>
                            <span class="sm:hidden">Inspect</span>
                        </x-base.tab.button>
                    </x-base.tab>

                    <x-base.tab id="documents-tab">
                        <x-base.tab.button
                            class="tab-button flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap"
                            data-target="#tab-content-documents"
                            aria-controls="tab-content-documents"
                            aria-selected="false">
                            <x-base.lucide class="w-4 h-4" icon="FileText" />
                            <span class="hidden sm:inline">Documents</span>
                            <span class="sm:hidden">Docs</span>
                            @if($stats['total_documents'] > 0)
                            <x-base.badge variant="success" class="ml-1 text-xs">{{ $stats['total_documents'] }}</x-base.badge>
                            @endif
                        </x-base.tab.button>
                    </x-base.tab>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="tab-content mt-6">
                <!-- Loading Indicator -->
                <div id="tab-loading" class="hidden flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Loading...</span>
                </div>

                <!-- Tab General -->
                <div id="tab-content-general" class="tab-pane active transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-general">
                    @include('admin.drivers.list-driver.tabs.general', ['driver' => $driver])
                </div>

                <!-- Tab Licenses -->
                <div id="tab-content-licenses" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-licenses">
                    @include('admin.drivers.list-driver.tabs.licenses', ['driver' => $driver])
                </div>

                <!-- Tab Medical -->
                <div id="tab-content-medical" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-medical">
                    @include('admin.drivers.list-driver.tabs.medical', ['driver' => $driver])
                </div>

                <!-- Tab Employment -->
                <div id="tab-content-employment" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-employment">
                    @include('admin.drivers.list-driver.tabs.employment', ['driver' => $driver])
                </div>

                <!-- Tab Training -->
                <div id="tab-content-training" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-training">
                    @include('admin.drivers.list-driver.tabs.training', ['driver' => $driver])
                </div>

                <!-- Tab Testing -->
                <div id="tab-content-testing" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-testing">
                    @include('admin.drivers.list-driver.tabs.testing', ['driver' => $driver])
                </div>

                <!-- Tab Inspections -->
                <div id="tab-content-inspections" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-inspections">
                    @include('admin.drivers.list-driver.tabs.inspections', ['driver' => $driver])
                </div>

                <!-- Tab Documents -->
                <div id="tab-content-documents" class="tab-pane hidden transition-opacity duration-300 ease-in-out" role="tabpanel" aria-labelledby="tab-documents">
                    @include('admin.drivers.list-driver.tabs.documents', ['driver' => $driver])
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        /**
         * Professional Driver Tab System
         * Handles tab switching, loading states, and active class management
         */
        class DriverTabManager {
            constructor() {
                this.activeTab = null;
                this.tabButtons = document.querySelectorAll('.tab-button');
                this.tabContents = document.querySelectorAll('.tab-pane');
                this.loadingIndicator = document.getElementById('tab-loading');

                this.init();
            }

            init() {
                // Add click event listeners to tab buttons
                this.tabButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetId = button.getAttribute('data-target');
                        this.switchTab(targetId, button);
                    });
                });

                // Initialize active tab based on URL hash or default to general
                this.initializeActiveTab();

                // Handle browser back/forward navigation
                window.addEventListener('popstate', () => {
                    this.initializeActiveTab();
                });

                // Mobile responsive tab selection
                this.handleMobileTabSelection();

                // Keyboard navigation support
                this.addKeyboardNavigation();
            }

            showLoading() {
                if (this.loadingIndicator) {
                    this.loadingIndicator.classList.remove('hidden');
                }
            }

            hideLoading() {
                if (this.loadingIndicator) {
                    this.loadingIndicator.classList.add('hidden');
                }
            }

            switchTab(targetId, button) {
                // Show loading state
                this.showLoading();

                // Update URL hash without triggering page scroll
                const tabName = targetId.replace('#tab-content-', '');
                history.pushState(null, null, `#${tabName}`);

                // Remove active class from all buttons and contents
                this.tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                    btn.parentElement.classList.remove('active');
                });

                this.tabContents.forEach(content => {
                    content.classList.remove('active');
                    content.classList.add('hidden');
                });

                // Add active class to clicked button
                button.classList.add('active');
                button.setAttribute('aria-selected', 'true');
                button.parentElement.classList.add('active');

                // Show target content
                const targetContent = document.querySelector(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                    targetContent.classList.remove('hidden');

                    // Smooth scroll to tab content on mobile
                    if (window.innerWidth < 768) {
                        targetContent.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }

                // Hide loading state after a brief delay
                setTimeout(() => {
                    this.hideLoading();
                }, 300);

                this.activeTab = targetId;
            }

            initializeActiveTab() {
                const hash = window.location.hash.replace('#', '');
                let targetTab = '#tab-content-general'; // Default tab

                if (hash) {
                    const possibleTarget = `#tab-content-${hash}`;
                    if (document.querySelector(possibleTarget)) {
                        targetTab = possibleTarget;
                    }
                }

                const targetButton = document.querySelector(`[data-target="${targetTab}"]`);
                if (targetButton) {
                    this.switchTab(targetTab, targetButton);
                }
            }

            handleMobileTabSelection() {
                // Add touch-friendly interactions for mobile
                if ('ontouchstart' in window) {
                    this.tabButtons.forEach(button => {
                        button.addEventListener('touchstart', () => {
                            button.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                        });

                        button.addEventListener('touchend', () => {
                            setTimeout(() => {
                                button.style.backgroundColor = '';
                            }, 150);
                        });
                    });
                }
            }

            addKeyboardNavigation() {
                this.tabButtons.forEach((button, index) => {
                    button.addEventListener('keydown', (e) => {
                        let targetIndex = index;

                        switch (e.key) {
                            case 'ArrowLeft':
                                e.preventDefault();
                                targetIndex = index > 0 ? index - 1 : this.tabButtons.length - 1;
                                break;
                            case 'ArrowRight':
                                e.preventDefault();
                                targetIndex = index < this.tabButtons.length - 1 ? index + 1 : 0;
                                break;
                            case 'Home':
                                e.preventDefault();
                                targetIndex = 0;
                                break;
                            case 'End':
                                e.preventDefault();
                                targetIndex = this.tabButtons.length - 1;
                                break;
                            default:
                                return;
                        }

                        this.tabButtons[targetIndex].focus();
                        this.tabButtons[targetIndex].click();
                    });
                });
            }
        }

        // Initialize the tab manager when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new DriverTabManager();
        });
    </script>
    @endpush
    @endsection