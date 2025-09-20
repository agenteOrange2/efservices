@extends('../themes/' . $activeTheme)
@section('title', 'Drug Test Details')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Testing Drugs Management', 'url' => route('admin.driver-testings.index')],
        ['label' => 'Test Details', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div>
        <!-- Mensajes Flash -->
        @if (session()->has('success'))
            <div class="alert alert-success flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
                {{ session('success') }}
            </div>
        @endif

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Drug Test Details #{{ $driverTesting->id }}
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0 gap-2">
                <a href="{{ route('admin.driver-testings.download-pdf', ['driverTesting' => $driverTesting->id]) }}"
                    target="_blank">
                    <x-base.button variant="outline-primary" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                        Download PDF
                    </x-base.button>
                </a>
                <a href="{{ route('admin.driver-testings.regenerate-pdf', ['driverTesting' => $driverTesting->id]) }}">
                    <x-base.button variant="outline-warning" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="refresh-cw" />
                        Regenerate PDF
                    </x-base.button>
                </a>
                <a href="{{ route('admin.driver-testings.edit', ['driverTesting' => $driverTesting->id]) }}">
                    <x-base.button variant="outline-success" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                        Edit
                    </x-base.button>
                </a>
                <a href="{{ route('admin.driver-testings.index') }}">
                    <x-base.button variant="outline-secondary" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                        Back to List
                    </x-base.button>
                </a>
            </div>
        </div>
        

        <div class="box box--stacked mt-5 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="bg-white shadow-sm mb-8">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">                                        
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity-icon lucide-activity"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h1 class="text-2xl font-bold text-gray-900">Drug & Alcohol Test Information</h1>
                                    <p class="text-sm text-gray-600 mt-1">Test ID: {{ $driverTesting->id }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-4">
                                    <!-- Status Badge -->
                                    <div class="flex flex-col">
                                        <span class="text-xs text-slate-500 mb-1 font-medium">Process Status:</span>                                        
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium 
                                            @if ($driverTesting->status == 'approved') bg-success text-white 
                                            @elseif($driverTesting->status == 'rejected') bg-danger text-white
                                            @else bg-warning text-white @endif shadow-sm">
                                            <i class="fas @if ($driverTesting->status == 'approved') fa-check-circle @elseif($driverTesting->status == 'rejected') fa-times-circle @else fa-hourglass-half @endif mr-2"></i>
                                            @php
                                                $statuses = \App\Models\Admin\Driver\DriverTesting::getStatuses();
                                                $statusDisplay = $statuses[$driverTesting->status] ?? ucfirst($driverTesting->status);
                                            @endphp
                                            {{ $statusDisplay }}
                                        </span>
                                    </div>
                                    
                                    <!-- Test Result Badge -->
                                    <div class="flex flex-col">
                                        <span class="text-xs text-slate-500 mb-1 font-medium">Test Result:</span>
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium shadow-sm
                                            @if ($driverTesting->test_result == 'passed') bg-success text-white 
                                            @elseif($driverTesting->test_result == 'failed') bg-danger text-white 
                                            @else bg-warning text-white @endif">
                                            <i class="fas @if ($driverTesting->test_result == 'passed') fa-check @elseif($driverTesting->test_result == 'failed') fa-times @else fa-spinner fa-pulse @endif mr-2"></i>
                                            {{ ucfirst($driverTesting->test_result) }}
                                        </span>
                                    </div>
                                </div>                                                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Test Details Card -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-clipboard-check text-blue-600 mr-2"></i>
                                    Test Details
                                </h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-6">
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-calendar-alt text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Test Date</dt>
                                                <dd class="text-sm text-gray-600 mt-1">
                                                    {{ $driverTesting->test_date ? $driverTesting->test_date->format('m/d/Y') : 'Not specified' }}
                                                </dd>
                                            </div>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-flask text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Test Type</dt>
                                                <dd class="text-sm text-gray-600 mt-1">{{ $driverTesting->test_type }}</dd>
                                            </div>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Location</dt>
                                                <dd class="text-sm text-gray-600 mt-1">
                                                    {{ $driverTesting->location ?: 'Not specified' }}</dd>
                                            </div>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-user-md text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Administered By</dt>
                                                <dd class="text-sm text-gray-600 mt-1">
                                                    {{ $driverTesting->administered_by ?: 'Not specified' }}</dd>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-6">
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-user-tie text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Requested By</dt>
                                                <dd class="text-sm text-gray-600 mt-1">
                                                    {{ $driverTesting->requester_name ?: 'Not specified' }}</dd>
                                            </div>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-clock text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Scheduled Time</dt>
                                                <dd class="text-sm text-gray-600 mt-1">
                                                    {{ $driverTesting->scheduled_time ? $driverTesting->scheduled_time->format('m/d/Y h:i A') : 'Not scheduled' }}
                                                </dd>
                                            </div>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-calendar-plus text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Next Test Due</dt>
                                                <dd class="text-sm text-gray-600 mt-1">
                                                    {{ $driverTesting->next_test_due ? $driverTesting->next_test_due->format('m/d/Y') : 'Not specified' }}
                                                </dd>
                                            </div>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-receipt text-gray-400 mt-1"></i>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-900">Bill To</dt>
                                                <dd class="text-sm text-gray-600 mt-1">
                                                    {{ $driverTesting->bill_to ?: $driverTesting->carrier->name }}</dd>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Test Categories -->
                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <dt class="text-sm font-medium text-gray-900 mb-3">Test Categories</dt>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($driverTesting->is_random_test)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">                                                
                                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info h-4 w-4 mr-2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                                Random
                                            </span>
                                        @endif
                                        @if ($driverTesting->is_post_accident_test)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info h-4 w-4 mr-2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                                Post Accident
                                            </span>
                                        @endif
                                        @if ($driverTesting->is_reasonable_suspicion_test)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info h-4 w-4 mr-2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                                Reasonable Suspicion
                                            </span>
                                        @endif
                                        @if (
                                            !$driverTesting->is_random_test &&
                                                !$driverTesting->is_post_accident_test &&
                                                !$driverTesting->is_reasonable_suspicion_test)
                                            <span class="text-gray-500">None specified</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-sticky-note text-gray-600 mr-2"></i>
                                    Notes & Additional Information
                                </h2>
                            </div>
                            <div class="p-6">
                                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        {{ $driverTesting->notes ?: 'No notes available for this test.' }}
                                    </p>
                                </div>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-500 border-t border-gray-200 pt-4">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-plus-circle"></i>
                                        <span><strong>Created:</strong>
                                            {{ $driverTesting->created_at ? $driverTesting->created_at->format('m/d/Y h:i A') : 'Unknown date' }}
                                            by
                                            {{ $driverTesting->createdBy ? $driverTesting->createdBy->name : 'System' }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-edit"></i>
                                        <span><strong>Updated:</strong>
                                            {{ $driverTesting->updated_at ? $driverTesting->updated_at->format('m/d/Y h:i A') : 'Unknown date' }}
                                            by
                                            {{ $driverTesting->updatedBy ? $driverTesting->updatedBy->name : 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PDF PREVIEW --}}
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-sticky-note text-gray-600 mr-2"></i>
                                    PDF PREVIEW
                                </h2>
                            </div>
                            <div class="box-body p-5">
                                <div>
                                    @if ($driverTesting->getMedia('drug_test_pdf')->count() > 0)
                                        @php
                                            $pdfUrl = $driverTesting->getFirstMediaUrl('drug_test_pdf');
                                        @endphp
                                        <script>
                                            console.log('PDF Debug Info:', {
                                                'media_count': {{ $driverTesting->getMedia('drug_test_pdf')->count() }},
                                                'pdf_url': '{{ $pdfUrl }}',
                                                'has_media': {{ $driverTesting->hasMedia('drug_test_pdf') ? 'true' : 'false' }},
                                                'testing_id': {{ $driverTesting->id }}
                                            });
                                        </script>
                                        <div class="w-full border border-slate-200 rounded-md overflow-hidden bg-gray-50">
                                            <!-- PDF Viewer con fallbacks -->
                                            <div class="pdf-viewer-container" style="height: 700px;">
                                                @if($pdfUrl)
                                                    <iframe 
                                                        src="{{ $pdfUrl }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                                                        class="w-full h-full border-0" 
                                                        title="PDF Preview"
                                                        onload="console.log('PDF iframe loaded successfully'); this.style.opacity=1;"
                                                        style="opacity:0; transition: opacity 0.3s;"
                                                        onerror="console.error('PDF iframe failed to load'); handlePdfError(this);">
                                                    </iframe>
                                                @else
                                                    <div class="text-center py-10">
                                                        <x-base.lucide class="w-16 h-16 mx-auto text-gray-500 mb-4" icon="file-x" />
                                                        <p class="text-gray-700 mb-4">No PDF available</p>
                                                        <p class="text-sm text-gray-500">The PDF will be generated when the test is created or updated.</p>
                                                    </div>
                                                @endif
                                                
                                                <!-- Fallback para navegadores que no soportan iframe con PDF -->
                                                <div id="pdf-fallback" class="hidden text-center py-10">
                                                    <x-base.lucide class="w-16 h-16 mx-auto text-blue-500 mb-4" icon="file-text" />
                                                    <p class="text-gray-700 mb-4">Unable to display PDF in browser</p>
                                                    <div class="space-y-2">
                                                        <a href="{{ $pdfUrl }}" target="_blank" 
                                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                                            <x-base.lucide class="w-4 h-4 mr-2" icon="external-link" />
                                                            Open in New Tab
                                                        </a>
                                                        <br>
                                                        <a href="{{ $pdfUrl }}" download 
                                                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                                            <x-base.lucide class="w-4 h-4 mr-2" icon="download" />
                                                            Download PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Controles adicionales -->
                                        <div class="mt-4 flex justify-between items-center">
                                            <div class="text-sm text-gray-600">
                                                <x-base.lucide class="w-4 h-4 inline mr-1" icon="info" />
                                                PDF Size: {{ human_filesize($driverTesting->getFirstMedia('drug_test_pdf')->size) }}
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ $pdfUrl }}" target="_blank" 
                                                   class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                                    <x-base.lucide class="w-3 h-3 mr-1" icon="external-link" />
                                                    Open in New Tab
                                                </a>
                                                <a href="{{ $pdfUrl }}" download 
                                                   class="inline-flex items-center px-3 py-1 text-sm bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors">
                                                    <x-base.lucide class="w-3 h-3 mr-1" icon="download" />
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-10 text-gray-500">
                                            <x-base.lucide class="w-16 h-16 mx-auto mb-4" icon="file-question" />
                                            <p class="text-lg font-medium mb-2">No PDF Report Available</p>
                                            <p class="text-sm mb-4">No PDF report has been generated for this test yet.</p>
                                            <a href="{{ route('admin.driver-testings.regenerate-pdf', $driverTesting->id) }}" 
                                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                                <x-base.lucide class="w-4 h-4 mr-2" icon="refresh-cw" />
                                                Generate PDF Report
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <!-- Carrier Information -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-building text-green-600 mr-2"></i>
                                    Carrier Information
                                </h2>
                            </div>
                            <div class="p-6">
                                @if ($driverTesting->carrier)
                                    <div class="bg-green-50 rounded-lg p-4 border border-green-200 mb-4">
                                        <h3 class="font-semibold text-green-900 text-lg">
                                            {{ $driverTesting->carrier->name ?? 'Carrier Name Not Available' }}</h3>
                                        <p class="text-sm text-green-700 mt-1">Carrier ID:
                                            {{ $driverTesting->carrier->id }}</p>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">DOT Number:</span>
                                            <span
                                                class="font-medium text-gray-900">{{ $driverTesting->carrier->dot_number ?: 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">MC Number:</span>
                                            <span
                                                class="font-medium text-gray-900">{{ $driverTesting->carrier->mc_number ? 'MC-' . $driverTesting->carrier->mc_number : 'N/A' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200 mb-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                            <p class="text-yellow-700">No carrier information available for this test.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Driver Information -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-50 to-violet-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-id-card text-purple-600 mr-2"></i>
                                    Driver Information
                                </h2>
                            </div>
                            <div class="p-6">
                                {{-- @if ($driverTesting->driver) --}}
                                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200 mb-4">
                                    <h3 class="font-semibold text-purple-900 text-lg">                                        
                                        {{ $driverTesting->userDriverDetail->user->name }}
                                        {{ $driverTesting->userDriverDetail->last_name }}
                                    </h3>
                                    <p class="text-sm text-purple-700 mt-1">Driver ID:
                                        {{ $driverTesting->userDriverDetail->id }}</p>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-3">                                        
                                        <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail text-gray-400 h-4 w-4"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                                        <span
                                            class="text-sm text-gray-600">{{ $driverTesting->userDriverDetail->user->email ?: 'No email available' }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone-icon lucide-phone text-gray-400 h-4 w-4"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/></svg>
                                        <span class="text-sm text-gray-600">{{ $driverTesting->userDriverDetail->formatted_phone }}</span>
                                    </div>
                                    {{-- <div class="flex items-center space-x-3">
                                            <i class="fas fa-id-badge text-gray-400"></i>
                                            <span class="text-sm text-gray-600">CDL: {{ $driverTesting->driver->license_number ?: 'Not available' }}</span>
                                        </div> --}}
                                </div>
                                {{-- @else
                                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200 mb-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                            <p class="text-yellow-700">No driver information available for this test.</p>
                                        </div>
                                    </div>
                                @endif --}}
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-paperclip text-amber-600 mr-2"></i>
                                    Documents Attached
                                </h2>
                            </div>
                            <div class="overflow-x-auto">
                                @if ($driverTesting->getMedia('document_attachments')->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                        @foreach ($driverTesting->getMedia('document_attachments') as $media)
                                            <div class="border rounded-lg p-3 flex items-center">
                                                <div class="flex-shrink-0 mr-3">
                                                    @php
                                                        $fileExtension = pathinfo(
                                                            $media->file_name,
                                                            PATHINFO_EXTENSION,
                                                        );
                                                        $isImage = in_array(strtolower($fileExtension), [
                                                            'jpg',
                                                            'jpeg',
                                                            'png',
                                                            'gif',
                                                            'webp',
                                                        ]);
                                                    @endphp

                                                    @if ($isImage)
                                                        <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}"
                                                            class="w-12 h-12 object-cover rounded">
                                                    @else
                                                        <div
                                                            class="w-12 h-12 flex items-center justify-center bg-slate-100 rounded">
                                                            <x-base.lucide class="w-6 h-6 text-slate-500"
                                                                icon="file-text" />
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow">

                                                    <div class="font-medium truncate" title="{{ $media->name }}">
                                                        {{ Str::limit($media->name, 25, '...') }}</div>
                                                    <div class="text-xs text-slate-500">{{ human_filesize($media->size) }}
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 ml-2">
                                                    <a href="{{ $media->getUrl() }}" target="_blank"
                                                        class="btn btn-sm btn-primary">
                                                        <x-base.lucide class="w-4 h-4" icon="eye" />
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-slate-500 italic">No documents attached</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Vista previa del PDF -->
        {{-- <div class="box box--stacked mt-5">
            <div class="box-header box-header--transparent">
                <div class="box-title">PDF Preview</div>
            </div>
            <div class="box-body p-5">
                @if($driverTesting->hasMedia('drug_test_pdf'))
                    <div class="w-full h-[800px] border border-slate-200 rounded-md overflow-hidden">
                        <iframe src="{{ $driverTesting->getFirstMediaUrl('drug_test_pdf') }}" class="w-full h-full" frameborder="0"></iframe>
                    </div>
                @else
                    <div class="flex items-center justify-center p-10 text-slate-500">
                        <x-base.lucide class="w-6 h-6 mr-2" icon="file-x" />
                        No PDF available. Please regenerate the PDF.
                    </div>
                @endif
            </div>
        </div> --}}
        
        <!-- Espacio para botones adicionales si se necesitan en el futuro -->
        <div class="mt-5"></div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <x-base.dialog id="delete-confirmation-modal">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Confirm Deletion</h2>
            </x-base.dialog.title>
            <x-base.dialog.description>
                Are you sure you want to delete this test record? This action cannot be undone.
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <form id="delete_testing_form" method="POST"
                    action="{{ route('admin.driver-testings.destroy', ['driverTesting' => $driverTesting->id]) }}">
                    @csrf
                    @method('DELETE')
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="danger" class="w-20">
                        Delete
                    </x-base.button>
                </form>
            </x-base.dialog.footer>
        </x-base.dialog.panel>
    </x-base.dialog>

    @push('scripts')
    <script>
        // Función para manejar errores del PDF viewer
        function handlePdfError(iframe) {
            console.warn('PDF iframe failed to load, showing fallback');
            console.log('PDF URL:', iframe.src);
            
            // Ocultar el iframe
            iframe.style.display = 'none';
            
            // Mostrar el fallback
            const fallback = document.getElementById('pdf-fallback');
            if (fallback) {
                fallback.classList.remove('hidden');
            }
        }
        
        // Función para verificar si el PDF se puede cargar
        function checkPdfAccess(url) {
            fetch(url, { method: 'HEAD' })
                .then(response => {
                    console.log('PDF accessibility check:', {
                        status: response.status,
                        contentType: response.headers.get('content-type'),
                        url: url
                    });
                    
                    if (!response.ok) {
                        console.error('PDF not accessible:', response.status, response.statusText);
                    }
                })
                .catch(error => {
                    console.error('Error checking PDF accessibility:', error);
                });
        }

        // Verificar si el PDF se cargó correctamente después de un tiempo
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar acceso al PDF cuando se carga la página
            @if($pdfUrl)
                checkPdfAccess('{{ $pdfUrl }}');
            @endif
            
            const iframe = document.querySelector('.pdf-viewer-container iframe');
            if (iframe) {
                // Timeout para verificar si el PDF se cargó
                setTimeout(function() {
                    try {
                        // Intentar acceder al contenido del iframe
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        
                        // Si no hay contenido o hay error, mostrar fallback
                        if (!iframeDoc || iframeDoc.body.innerHTML.trim() === '') {
                            handlePdfError(iframe);
                        }
                    } catch (e) {
                        // Error de acceso (CORS, etc.), pero el PDF podría estar cargando
                        console.log('PDF loading or CORS restriction detected');
                    }
                }, 3000); // Esperar 3 segundos

                // Listener adicional para errores de carga
                iframe.addEventListener('error', function() {
                    handlePdfError(this);
                });

                // Verificar si el src está vacío
                if (!iframe.src || iframe.src.trim() === '') {
                    handlePdfError(iframe);
                }
            }
        });
    </script>
    @endpush
@endsection
