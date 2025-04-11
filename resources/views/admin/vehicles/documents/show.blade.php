@extends('../themes/' . $activeTheme)
@section('title', 'Document Details')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
    ['label' => $vehicle->make . ' ' . $vehicle->model, 'url' => route('admin.vehicles.show', $vehicle->id)],
    ['label' => 'Documents', 'url' => route('admin.vehicles.documents.index', $vehicle->id)],
    ['label' => 'Document Details', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12 md:col-span-10 md:col-start-2 lg:col-span-8 lg:col-start-3">
        <div class="mt-7">
            <div class="box box--stacked flex flex-col">
                <div class="box-header p-5 border-b border-slate-200/60 bg-slate-50">
                    <div class="flex items-center">
                        <div class="mr-auto">
                            <h3 class="text-base font-medium">{{ $document->documentTypeName }}</h3>
                            <div class="text-slate-500 text-sm mt-0.5">
                                {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.vehicles.documents.index', $vehicle->id) }}"
                                class="btn btn-outline-secondary">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                                Back to Documents
                            </a>
                            <a href="{{ route('admin.vehicles-documents.index') }}"
                                class="btn btn-outline-primary">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                                Documents Overview
                            </a>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#edit-document-modal"
                                class="btn btn-primary edit-document-btn"
                                data-document-id="{{ $document->id }}"
                                data-document-type="{{ $document->document_type }}"
                                data-document-number="{{ $document->document_number }}"
                                data-issued-date="{{ $document->issued_date ? $document->issued_date->format('Y-m-d') : '' }}"
                                data-expiration-date="{{ $document->expiration_date ? $document->expiration_date->format('Y-m-d') : '' }}"
                                data-status="{{ $document->status }}"
                                data-notes="{{ $document->notes }}"
                                data-has-file="{{ $document->getFirstMedia('document_files') ? 'true' : 'false' }}"
                                data-file-name="{{ $document->getFirstMedia('document_files') ? $document->getFirstMedia('document_files')->file_name : '' }}"
                                data-file-size="{{ $document->getFirstMedia('document_files') ? number_format($document->getFirstMedia('document_files')->size / 1024, 2) . ' KB' : '' }}">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="Edit" />
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
                <div class="box-body p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Document Information --}}
                        <div>
                            <h4 class="text-lg font-medium mb-4">Document Information</h4>
                            <div class="overflow-x-auto">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="text-slate-500 whitespace-nowrap">Document Type</td>
                                            <td class="font-medium">{{ $document->documentTypeName }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-slate-500 whitespace-nowrap">Document Number</td>
                                            <td>{{ $document->document_number ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-slate-500 whitespace-nowrap">Issued Date</td>
                                            <td>{{ $document->issued_date ? $document->issued_date->format('m/d/Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-slate-500 whitespace-nowrap">Expiration Date</td>
                                            <td>
                                                @if($document->expiration_date)
                                                <span class="{{ $document->isExpired() ? 'text-danger' : ($document->isAboutToExpire() ? 'text-warning' : 'text-success') }}">
                                                    {{ $document->expiration_date->format('m/d/Y') }}
                                                    @if($document->isExpired())
                                                    <span class="text-xs bg-danger/10 text-danger px-1.5 py-0.5 rounded ml-2">Expired</span>
                                                    @elseif($document->isAboutToExpire())
                                                    <span class="text-xs bg-warning/10 text-warning px-1.5 py-0.5 rounded ml-2">Expiring Soon</span>
                                                    @endif
                                                </span>
                                                @else
                                                N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-slate-500 whitespace-nowrap">Status</td>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 rounded-full mr-2 {{ $document->status === 'active' ? 'bg-success' : ($document->status === 'expired' ? 'bg-danger' : ($document->status === 'pending' ? 'bg-warning' : 'bg-slate-400')) }}">
                                                    </div>
                                                    {{ $document->statusName }}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-slate-500 whitespace-nowrap">Added On</td>
                                            <td>{{ $document->created_at->format('m/d/Y h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-slate-500 whitespace-nowrap">Last Updated</td>
                                            <td>{{ $document->updated_at->format('m/d/Y h:i A') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @if($document->notes)
                            <div class="mt-5">
                                <h5 class="font-medium mb-2">Notes</h5>
                                <div class="bg-slate-50 p-3 rounded-md">
                                    {{ $document->notes }}
                                </div>
                            </div>
                            @endif
                        </div>
                        {{-- Document Preview --}}
                        <div>
                            <h4 class="text-lg font-medium mb-4">Document Preview</h4>
                            @if($document->getFirstMedia('document_files'))
                            <div class="border rounded-md bg-slate-50 p-3">
                                <div class="text-center">
                                    @if(strpos($document->getFirstMedia('document_files')->mime_type, 'image/') === 0)
                                    <img src="{{ $document->getFirstMedia('document_files')->getUrl('preview') }}"
                                        alt="Document Preview" class="max-h-80 mx-auto rounded">
                                    @else
                                    <div class="h-48 flex items-center justify-center bg-primary/10 text-primary rounded">
                                        <x-base.lucide class="h-16 w-16" icon="FileText" />
                                    </div>
                                    <div class="mt-3 text-slate-500">Preview not available for this file format</div>
                                    @endif
                                </div>
                                <div class="mt-4 border-t border-slate-200 pt-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 flex items-center justify-center bg-primary/10 text-primary rounded">
                                                @if(strpos($document->getFirstMedia('document_files')->mime_type, 'image/') === 0)
                                                <x-base.lucide class="h-5 w-5" icon="Image" />
                                                @elseif(strpos($document->getFirstMedia('document_files')->mime_type, 'application/pdf') === 0)
                                                <x-base.lucide class="h-5 w-5" icon="FileText" />
                                                @else
                                                <x-base.lucide class="h-5 w-5" icon="File" />
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="text-sm font-medium truncate">{{ $document->getFirstMedia('document_files')->file_name }}</div>
                                            <div class="text-xs text-slate-500">
                                                {{ number_format($document->getFirstMedia('document_files')->size / 1024, 2) }} KB
                                                • {{ strtoupper(pathinfo($document->getFirstMedia('document_files')->file_name, PATHINFO_EXTENSION)) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-center space-x-4 mt-4">
                                        <a href="{{ route('admin.vehicles.documents.preview', [$vehicle->id, $document->id]) }}"
                                            class="btn btn-outline-primary btn-sm" target="_blank">
                                            <x-base.lucide class="mr-1 h-4 w-4" icon="Eye" />
                                            Open in Browser
                                        </a>
                                        <a href="{{ route('admin.vehicles.documents.download', [$vehicle->id, $document->id]) }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <x-base.lucide class="mr-1 h-4 w-4" icon="Download" />
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-10 bg-slate-50 rounded-md">
                                <x-base.lucide class="h-12 w-12 mx-auto text-slate-300" icon="FileX" />
                                <div class="mt-3 text-slate-500">No document file attached</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Vehicle Information --}}
                    <div class="mt-8 pt-6 border-t border-slate-200/60">
                        <h4 class="text-lg font-medium mb-4">Vehicle Information</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <td class="text-slate-500 whitespace-nowrap" width="20%">Make</td>
                                        <td class="font-medium">{{ $vehicle->make }}</td>
                                        <td class="text-slate-500 whitespace-nowrap" width="20%">Model</td>
                                        <td class="font-medium">{{ $vehicle->model }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-slate-500 whitespace-nowrap">Year</td>
                                        <td class="font-medium">{{ $vehicle->year }}</td>
                                        <td class="text-slate-500 whitespace-nowrap">VIN</td>
                                        <td class="font-medium font-mono text-xs">{{ $vehicle->vin }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-slate-500 whitespace-nowrap">Type</td>
                                        <td class="font-medium">{{ $vehicle->type }}</td>
                                        <td class="text-slate-500 whitespace-nowrap">Carrier</td>
                                        <td class="font-medium">{{ $vehicle->carrier->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-slate-500 whitespace-nowrap">Unit Number</td>
                                        <td class="font-medium">{{ $vehicle->company_unit_number ?: 'N/A' }}</td>
                                        <td class="text-slate-500 whitespace-nowrap">Status</td>
                                        <td class="font-medium">
                                            @if($vehicle->out_of_service)
                                            <span class="text-danger">Out of Service</span>
                                            @elseif($vehicle->suspended)
                                            <span class="text-warning">Suspended</span>
                                            @else
                                            <span class="text-success">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex justify-center">
                            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-outline-secondary">
                                <x-base.lucide class="mr-1 h-4 w-4" icon="Truck" />
                                View Vehicle Details
                            </a>
                        </div>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-200/60 flex justify-between">
                        <!-- Button to activate delete modal -->
                        <button type="button" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal" class="btn btn-outline-danger">
                            <x-base.lucide class="mr-1 h-4 w-4" icon="Trash" />
                            Delete Document
                        </button>
                        <button type="button" data-tw-toggle="modal" data-tw-target="#edit-document-modal" class="btn btn-primary edit-document-btn"
                            data-document-id="{{ $document->id }}"
                            data-document-type="{{ $document->document_type }}"
                            data-document-number="{{ $document->document_number }}"
                            data-issued-date="{{ $document->issued_date ? $document->issued_date->format('Y-m-d') : '' }}"
                            data-expiration-date="{{ $document->expiration_date ? $document->expiration_date->format('Y-m-d') : '' }}"
                            data-status="{{ $document->status }}"
                            data-notes="{{ $document->notes }}"
                            data-has-file="{{ $document->getFirstMedia('document_files') ? 'true' : 'false' }}"
                            data-file-name="{{ $document->getFirstMedia('document_files') ? $document->getFirstMedia('document_files')->file_name : '' }}"
                            data-file-size="{{ $document->getFirstMedia('document_files') ? number_format($document->getFirstMedia('document_files')->size / 1024, 2) . ' KB' : '' }}">
                            <x-base.lucide class="mr-1 h-4 w-4" icon="Edit" />
                            Edit Document
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="XCircle" />
                    <div class="mt-5 text-3xl">Are you sure?</div>
                    <div class="mt-2 text-slate-500">
                        Do you really want to delete this document? <br>
                        This process cannot be undone.
                    </div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <form action="{{ route('admin.vehicles.documents.destroy', [$vehicle->id, $document->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                            Cancel
                        </x-base.button>
                        <x-base.button class="w-24" type="submit" variant="danger">
                            Delete
                        </x-base.button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Document Modal -->
<x-base.dialog id="edit-document-modal" size="lg">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">
                Edit Document
            </h2>
        </x-base.dialog.title>
        <form id="edit-document-form" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <x-base.dialog.description>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Document Type --}}
                    <div class="col-span-2 md:col-span-1">
                        <x-base.form-label for="edit_document_type">Document Type <span class="text-danger">*</span></x-base.form-label>
                        <x-base.form-select id="edit_document_type" name="document_type" required>
                            <option value="">Select Document Type</option>
                            @foreach($documentTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-base.form-select>
                    </div>
                    
                    {{-- Document Number --}}
                    <div class="col-span-2 md:col-span-1">
                        <x-base.form-label for="edit_document_number">Document Number</x-base.form-label>
                        <x-base.form-input id="edit_document_number" name="document_number" type="text"
                            placeholder="e.g. Policy number or registration ID" />
                    </div>
                    
                    {{-- Issued Date --}}
                    <div class="col-span-1">
                        <x-base.form-label for="edit_issued_date">Issue Date</x-base.form-label>
                        <x-base.form-input id="edit_issued_date" name="issued_date" type="date" />
                    </div>
                    
                    {{-- Expiration Date --}}
                    <div class="col-span-1">
                        <x-base.form-label for="edit_expiration_date">Expiration Date</x-base.form-label>
                        <x-base.form-input id="edit_expiration_date" name="expiration_date" type="date" />
                    </div>
                    
                    {{-- Status --}}
                    <div class="col-span-2">
                        <x-base.form-label for="edit_status">Status <span class="text-danger">*</span></x-base.form-label>
                        <x-base.form-select id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="expired">Expired</option>
                        </x-base.form-select>
                    </div>
                    
                    {{-- Current Document Preview --}}
                    <div class="col-span-2" id="current-document-container">
                        <x-base.form-label>Current Document</x-base.form-label>
                        <div class="border rounded-md p-3 bg-slate-50">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-16 w-16 flex items-center justify-center bg-primary/10 text-primary rounded">
                                        <x-base.lucide class="h-8 w-8" icon="FileText" />
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <div class="text-sm font-medium" id="edit-file-name"></div>
                                    <div class="text-xs text-slate-500">
                                        <span id="edit-file-size"></span>
                                    </div>
                                    <div class="mt-1 flex space-x-2" id="document-action-links">
                                        <!-- Links will be added dynamically via JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Document File Upload --}}
                    <div class="col-span-2">
                        <x-base.form-label for="edit_document_file">Replace Document</x-base.form-label>
                        <div class="border-2 border-dashed rounded-md pt-4 pb-6 px-4 cursor-pointer" id="edit-file-upload-box">
                            <div class="text-center">
                                <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Upload" />
                                <div class="mt-2 text-sm text-slate-600">
                                    <label for="edit_document_file" class="cursor-pointer text-primary font-medium">
                                        Click to upload
                                    </label>
                                    or drag and drop a file
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    PDF, JPG, JPEG, PNG (Max size: 10MB)
                                </div>
                                <input id="edit_document_file" name="document_file" type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div id="edit-file-preview" class="mt-4 hidden">
                                <div class="flex items-center justify-between bg-slate-100 p-2 rounded">
                                    <div class="flex items-center">
                                        <div class="edit-file-icon bg-primary/10 text-primary p-2 rounded-md">
                                            <x-base.lucide class="h-5 w-5" icon="File" />
                                        </div>
                                        <div class="ml-2 overflow-hidden">
                                            <div class="text-sm font-medium truncate edit-file-name"></div>
                                            <div class="text-xs text-slate-500 edit-file-size"></div>
                                        </div>
                                    </div>
                                    <button type="button" class="text-slate-500 hover:text-danger" id="edit-remove-file">
                                        <x-base.lucide class="h-4 w-4" icon="X" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Notes --}}
                    <div class="col-span-2">
                        <x-base.form-label for="edit_notes">Notes</x-base.form-label>
                        <x-base.form-textarea id="edit_notes" name="notes" placeholder="Additional information about this document"></x-base.form-textarea>
                    </div>
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                    Cancel
                </x-base.button>
                <x-base.button class="w-20" type="submit" variant="primary">
                    Update
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // File upload for edit document
        const editFileInput = document.getElementById('edit_document_file');
        const editFileUploadBox = document.getElementById('edit-file-upload-box');
        const editFilePreview = document.getElementById('edit-file-preview');
        const editFileName = document.querySelector('.edit-file-name');
        const editFileSize = document.querySelector('.edit-file-size');
        const editRemoveFileBtn = document.getElementById('edit-remove-file');
        
        // Drag and drop functionality for edit
        if (editFileUploadBox) {
            editFileUploadBox.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-primary');
                this.classList.add('bg-primary/5');
            });
            
            editFileUploadBox.addEventListener('dragleave', function() {
                this.classList.remove('border-primary');
                this.classList.remove('bg-primary/5');
            });
            
            editFileUploadBox.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-primary');
                this.classList.remove('bg-primary/5');
                
                if (e.dataTransfer.files.length) {
                    editFileInput.files = e.dataTransfer.files;
                    updateFilePreview(editFileInput, editFilePreview, editFileName, editFileSize);
                }
            });
            
            editFileUploadBox.addEventListener('click', function() {
                editFileInput.click();
            });
            
            editFileInput.addEventListener('change', function() {
                updateFilePreview(this, editFilePreview, editFileName, editFileSize);
            });
            
            if (editRemoveFileBtn) {
                editRemoveFileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    editFileInput.value = '';
                    editFilePreview.classList.add('hidden');
                });
            }
        }
        
        // Generic file preview update function
        function updateFilePreview(input, previewContainer, nameElement, sizeElement) {
            if (input.files.length) {
                const file = input.files[0];
                nameElement.textContent = file.name;
                sizeElement.textContent = formatFileSize(file.size);
                previewContainer.classList.remove('hidden');
                
                // Update file icon based on type
                const fileIcon = previewContainer.querySelector('.file-icon, .edit-file-icon');
                if (file.type.includes('pdf')) {
                    fileIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M16 13H8"></path><path d="M16 17H8"></path><path d="M10 9H8"></path></svg>';
                } else if (file.type.includes('image')) {
                    fileIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>';
                }
            } else {
                previewContainer.classList.add('hidden');
            }
        }
        
        // Format file size helper
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Edit document button click handler
        const editDocumentBtns = document.querySelectorAll('.edit-document-btn');
        if (editDocumentBtns.length > 0) {
            editDocumentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const documentId = this.getAttribute('data-document-id');
                    const documentType = this.getAttribute('data-document-type');
                    const documentNumber = this.getAttribute('data-document-number');
                    const issuedDate = this.getAttribute('data-issued-date');
                    const expirationDate = this.getAttribute('data-expiration-date');
                    const status = this.getAttribute('data-status');
                    const notes = this.getAttribute('data-notes');
                    const hasFile = this.getAttribute('data-has-file') === 'true';
                    const fileName = this.getAttribute('data-file-name');
                    const fileSize = this.getAttribute('data-file-size');
                    
                    // Setup form action
                    const form = document.getElementById('edit-document-form');
                    form.action = `{{ route('admin.vehicles.documents.update', [$vehicle->id, $document->id]) }}`;
                    
                    // Fill in form values
                    document.getElementById('edit_document_type').value = documentType;
                    document.getElementById('edit_document_number').value = documentNumber;
                    document.getElementById('edit_issued_date').value = issuedDate;
                    document.getElementById('edit_expiration_date').value = expirationDate;
                    document.getElementById('edit_status').value = status;
                    document.getElementById('edit_notes').value = notes;
                    
                    // Update current document display
                    const currentDocContainer = document.getElementById('current-document-container');
                    const documentFileName = document.getElementById('edit-file-name');
                    const documentFileSize = document.getElementById('edit-file-size');
                    const documentActionLinks = document.getElementById('document-action-links');
                    
                    if (hasFile) {
                        currentDocContainer.classList.remove('hidden');
                        documentFileName.textContent = fileName;
                        documentFileSize.textContent = fileSize;
                        
                        // Clear and add action links
                        documentActionLinks.innerHTML = '';
                        
                        // Preview link
                        const previewLink = document.createElement('a');
                        previewLink.href = `{{ route('admin.vehicles.documents.preview', [$vehicle->id, $document->id]) }}`;
                        previewLink.className = 'text-xs text-primary';
                        previewLink.target = '_blank';
                        previewLink.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 inline-block mr-1"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg> Preview';
                        documentActionLinks.appendChild(previewLink);
                        
                        // Spacer
                        documentActionLinks.appendChild(document.createTextNode(' '));
                        
                        // Download link
                        const downloadLink = document.createElement('a');
                        downloadLink.href = `{{ route('admin.vehicles.documents.download', [$vehicle->id, $document->id]) }}`;
                        downloadLink.className = 'text-xs text-primary';
                        downloadLink.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 inline-block mr-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg> Download';
                        documentActionLinks.appendChild(downloadLink);
                    } else {
                        currentDocContainer.classList.add('hidden');
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection
