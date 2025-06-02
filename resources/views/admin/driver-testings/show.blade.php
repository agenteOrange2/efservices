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
                <a href="{{ route('admin.driver-testings.download-pdf', ['driverTesting' => $driverTesting->id]) }}" target="_blank">
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

        <!-- Información del Test -->
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
                                                @if($driverTesting->test_result == 'passed') bg-green-100 text-green-800 
                                                @elseif($driverTesting->test_result == 'failed') bg-red-100 text-red-800 
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst($driverTesting->test_result) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Status</th>
                                        <td>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                                @if($driverTesting->status == 'approved') bg-green-100 text-green-800 
                                                @elseif($driverTesting->status == 'rejected') bg-red-100 text-red-800 
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ \App\Models\Admin\Driver\DriverTesting::getStatuses()[$driverTesting->status] }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-slate-50/60">Test Categories</th>
                                        <td>
                                            @if($driverTesting->is_random_test)
                                                <span class="badge badge-primary mr-1">Random</span>
                                            @endif
                                            @if($driverTesting->is_post_accident_test)
                                                <span class="badge badge-warning mr-1">Post Accident</span>
                                            @endif
                                            @if($driverTesting->is_reasonable_suspicion_test)
                                                <span class="badge badge-danger mr-1">Reasonable Suspicion</span>
                                            @endif
                                            @if(!$driverTesting->is_random_test && !$driverTesting->is_post_accident_test && !$driverTesting->is_reasonable_suspicion_test)
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
                            @if($driverTesting->getMedia('document_attachments')->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($driverTesting->getMedia('document_attachments') as $media)
                                        <div class="border rounded-lg p-3 flex items-center">
                                            <div class="flex-shrink-0 mr-3">
                                                @php
                                                    $fileExtension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                @endphp
                                                
                                                @if($isImage)
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
        </div>

        <!-- PDF Preview -->
        <div class="box box--stacked mt-5">
            <div class="box-header box-header--transparent">
                <div class="box-title">PDF Preview</div>
            </div>
            <div class="box-body p-5">
                <div >
                    @if($driverTesting->getMedia('drug_test_pdf')->count() > 0)
                        <div class="max-w-full overflow-hidden">
                            <embed src="{{ $driverTesting->getFirstMediaUrl('drug_test_pdf') }}" 
                                type="application/pdf" width="100%" height="600px">
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

        <!-- Botones de Acción -->
        <div class="flex justify-end mt-5 space-x-2">
            <button type="button" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal"
                class="btn btn-danger">
                <x-base.lucide class="w-4 h-4 mr-2" icon="trash" />
                Delete Test
            </button>
        </div>
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
                <form id="delete_testing_form" method="POST" action="{{ route('admin.driver-testings.destroy', ['driverTesting' => $driverTesting->id]) }}">
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
