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

        {{-- <!-- Información del Test -->
        <div class="box box--stacked mt-5">
            <div class="box-header box-header--transparent">
                <div class="box-title">Drug & Alcohol Test Information</div>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Test Details</h3>
                        <div class="overflow-x-auto">
                            <table class="table text-left">
                                <tbody>
                                    <tr>
                                        <th class="w-40 bg-slate-50/60">Test ID</th>
                                        <td>{{ $driverTesting->id }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Test Date</th>
                                        <td>{{ $driverTesting->test_date->format('m/d/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Test Type</th>
                                        <td>{{ $driverTesting->test_type }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Result</th>
                                        <td>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                                @if ($driverTesting->test_result == 'passed') bg-success-soft text-success-dark 
                                                @elseif($driverTesting->test_result == 'failed') bg-danger-soft text-danger-dark 
                                                @else bg-info-soft text-info-dark @endif">
                                                {{ ucfirst($driverTesting->test_result) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Status</th>
                                        <td>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                                @if ($driverTesting->status == 'approved') bg-success-soft text-success-dark 
                                                @elseif($driverTesting->status == 'rejected') bg-danger-soft text-danger-dark 
                                                @else bg-warning-soft text-warning-dark @endif">
                                                {{ \App\Models\Admin\Driver\DriverTesting::getStatuses()[$driverTesting->status] }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Test Categories</th>
                                        <td>
                                            @if ($driverTesting->is_random_test)
                                                <span class="badge badge-primary mr-1">Random</span>
                                            @endif
                                            @if ($driverTesting->is_post_accident_test)
                                                <span class="badge badge-warning mr-1">Post Accident</span>
                                            @endif
                                            @if ($driverTesting->is_reasonable_suspicion_test)
                                                <span class="badge badge-danger mr-1">Reasonable Suspicion</span>
                                            @endif
                                            @if (!$driverTesting->is_random_test && !$driverTesting->is_post_accident_test && !$driverTesting->is_reasonable_suspicion_test)
                                                <span class="text-gray-500">None specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Location</th>
                                        <td>{{ $driverTesting->location ?: 'Not specified' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Administered By</th>
                                        <td>{{ $driverTesting->administered_by ?: 'Not specified' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Requested By</th>
                                        <td>{{ $driverTesting->requester_name ?: 'Not specified' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Scheduled Time</th>
                                        <td>{{ $driverTesting->scheduled_time ? $driverTesting->scheduled_time->format('m/d/Y h:i A') : 'Not scheduled' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Next Test Due</th>
                                        <td>{{ $driverTesting->next_test_due ? $driverTesting->next_test_due->format('m/d/Y') : 'Not specified' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Bill To</th>
                                        <td>{{ $driverTesting->bill_to ?: 'Not specified' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium mb-3">Driver & Carrier Information</h3>
                        <div class="overflow-x-auto">
                            <table class="table table-bordered text-left">
                                <tbody>
                                    <tr>
                                        <th class="w-40 bg-slate-50/60">Carrier</th>
                                        <td>
                                            <div class="font-medium">{{ $driverTesting->carrier->name }}</div>
                                            <div class="text-slate-500 text-xs mt-0.5">ID: {{ $driverTesting->carrier->id }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Driver Name</th>
                                        <td>
                                            <div class="font-medium">
                                                {{ $driverTesting->userDriverDetail->user->name }} 
                                                {{ $driverTesting->userDriverDetail->last_name }}
                                            </div>
                                            <div class="text-slate-500 text-xs mt-0.5">
                                                ID: {{ $driverTesting->userDriverDetail->id }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Driver Email</th>
                                        <td>{{ $driverTesting->userDriverDetail->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Driver Phone</th>
                                        <td>{{ $driverTesting->userDriverDetail->phone ?: 'Not available' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-lg font-medium mt-6 mb-3">Additional Information</h3>
                        <div class="overflow-x-auto">
                            <table class="table table-bordered text-left">
                                <tbody>
                                    <tr>
                                        <th class="w-40 bg-slate-50/60">Notes</th>
                                        <td class="whitespace-normal">{{ $driverTesting->notes ?: 'No notes available' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Created At</th>
                                        <td>{{ $driverTesting->created_at->format('m/d/Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Created By</th>
                                        <td>{{ $driverTesting->createdBy ? $driverTesting->createdBy->name : 'System' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Last Updated</th>
                                        <td>{{ $driverTesting->updated_at->format('m/d/Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Updated By</th>
                                        <td>{{ $driverTesting->updatedBy ? $driverTesting->updatedBy->name : 'System' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Sección de Documentos Adjuntos -->
                        <h3 class="text-lg font-medium mt-6 mb-3">Documents Attached</h3>
                        <div class="overflow-x-auto">
                            @if ($driverTesting->getMedia('document_attachments')->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($driverTesting->getMedia('document_attachments') as $media)
                                        <div class="border rounded-lg p-3 flex items-center">
                                            <div class="flex-shrink-0 mr-3">
                                                @php
                                                    $fileExtension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                @endphp
                                                
                                                @if ($isImage)
                                                    <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" class="w-12 h-12 object-cover rounded">
                                                @else
                                                    <div class="w-12 h-12 flex items-center justify-center bg-slate-100 rounded">
                                                        <x-base.lucide class="w-6 h-6 text-slate-500" icon="file-text" />
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow">
                                                <div class="font-medium truncate" title="{{ $media->name }}">{{ $media->name }}</div>
                                                <div class="text-xs text-slate-500">{{ human_filesize($media->size) }}</div>
                                            </div>
                                            <div class="flex-shrink-0 ml-2">
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-primary">
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
        </div> --}}

        <div class="box box--stacked mt-5 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="bg-white shadow-sm mb-8">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-vial text-blue-600 text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h1 class="text-2xl font-bold text-gray-900">Drug & Alcohol Test Information</h1>
                                    <p class="text-sm text-gray-600 mt-1">Test ID: {{ $driverTesting->id }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                <div class="flex space-x-3">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        @if ($driverTesting->status == 'approved') bg-success-soft text-success-dark 
                                        @elseif($driverTesting->status == 'rejected') bg-danger-soft text-danger-dark 
                                        @else bg-warning-soft text-warning-dark @endif">
                                        <i
                                            class="fas @if ($driverTesting->status == 'approved') fa-check-circle @elseif($driverTesting->status == 'rejected') fa-times-circle @else fa-clock @endif mr-1.5 text-xs"></i>
                                        {{ \App\Models\Admin\Driver\DriverTesting::getStatuses()[$driverTesting->status] }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        @if ($driverTesting->test_result == 'passed') bg-success-soft text-success-dark 
                                        @elseif($driverTesting->test_result == 'failed') bg-danger-soft text-danger-dark 
                                        @else bg-info-soft text-info-dark @endif">
                                        <i
                                            class="fas @if ($driverTesting->test_result == 'passed') fa-check @elseif($driverTesting->test_result == 'failed') fa-times @else fa-question @endif mr-1.5 text-xs"></i>
                                        {{ ucfirst($driverTesting->test_result) }}
                                    </span>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.driver-testings.edit', $driverTesting->id) }}"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-edit mr-2"></i> Edit
                                    </a>
                                    <button type="button" data-tw-toggle="modal"
                                        data-tw-target="#delete-confirmation-modal"
                                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
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
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                <i class="fas fa-random mr-1"></i>
                                                Random
                                            </span>
                                        @endif
                                        @if ($driverTesting->is_post_accident_test)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Post Accident
                                            </span>
                                        @endif
                                        @if ($driverTesting->is_reasonable_suspicion_test)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                <i class="fas fa-eye mr-1"></i>
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
                                        <div class="max-w-full overflow-hidden">
                                            <embed src="{{ $driverTesting->getFirstMediaUrl('drug_test_pdf') }}" type="application/pdf"
                                                width="100%" height="600px">
                                        </div>
                                    @else
                                        <div class="text-center py-10 text-gray-500">
                                            <x-base.lucide class="w-16 h-16 mx-auto" icon="file-question" />
                                            <p class="mt-3">No PDF report has been generated for this test yet.</p>
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
                                        <i class="fas fa-envelope text-gray-400"></i>
                                        <span
                                            class="text-sm text-gray-600">{{ $driverTesting->userDriverDetail->user->email ?: 'No email available' }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-phone text-gray-400"></i>
                                        <span
                                            class="text-sm text-gray-600">{{ $driverTesting->userDriverDetail->phone ?: 'No phone available' }}</span>
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
@endsection
