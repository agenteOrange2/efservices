@extends('../themes/' . $activeTheme)
@section('title', 'Vehicle Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => $vehicle->make . ' ' . $vehicle->model, 'url' => route('admin.vehicles.show', $vehicle->id)],
        ['label' => 'Documents', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div class="grid grid-cols-1 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium">
                    Vehicle Documents: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.vehicles.show', $vehicle->id) }}"
                        class="w-full sm:w-auto" variant="outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                        Back to Vehicle
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.vehicles-documents.index') }}"
                        class="w-full sm:w-auto" variant="outline-primary">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                        Documents Overview
                    </x-base.button>
                    <x-base.button data-tw-toggle="modal" data-tw-target="#add-document-modal" class="w-full sm:w-auto"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New Document
                    </x-base.button>
                </div>
            </div>
            {{-- Document Status Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mt-5">
                <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="rounded-full bg-success/20 p-3 mr-3">
                            <x-base.lucide class="h-5 w-5 text-success" icon="CheckCircle" />
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs">Active Documents</div>
                            <div class="font-medium text-xl">{{ $vehicle->documents->where('status', 'active')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="rounded-full bg-danger/20 p-3 mr-3">
                            <x-base.lucide class="h-5 w-5 text-danger" icon="AlertOctagon" />
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs">Expired Documents</div>
                            <div class="font-medium text-xl">{{ $vehicle->documents->where('status', 'expired')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="rounded-full bg-warning/20 p-3 mr-3">
                            <x-base.lucide class="h-5 w-5 text-warning" icon="Clock" />
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs">Pending Documents</div>
                            <div class="font-medium text-xl">{{ $vehicle->documents->where('status', 'pending')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="rounded-full bg-primary/20 p-3 mr-3">
                            <x-base.lucide class="h-5 w-5 text-primary" icon="FileText" />
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs">Total Documents</div>
                            <div class="font-medium text-xl">{{ $vehicle->documents->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Documents by Type --}}
            <div class="mt-7">
                <div class="box box--stacked">
                    <div class="box-header bg-slate-50 p-5 border-b border-slate-200/60">
                        <div class="flex flex-col gap-y-3 md:flex-row md:items-center">
                            <div class="box-title font-medium">Document List</div>
                            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                                {{-- <div class="relative">
                                    <input type="text" class="form-input pl-9 w-full sm:w-64"
                                        placeholder="Search documents..." id="document-search">
                                    <x-base.lucide
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400"
                                        icon="Search" />
                                </div> --}}
                                <div class="relative"> 
                                    <x-base.lucide
                                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                        icon="Search" />
                                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" id="vehicle-search" type="text"
                                        placeholder="Search documents..." id="document-search" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body p-5">
                        @if ($vehicle->documents->isEmpty())
                            <div class="text-center py-10">
                                <x-base.lucide class="h-12 w-12 mx-auto text-slate-300" icon="FileText" />
                                <div class="mt-3 text-slate-500">No documents found</div>
                                <div class="mt-3 flex justify-center ">
                                    <button type="button" data-tw-toggle="modal" data-tw-target="#add-document-modal"
                                        class="btn btn-primary btn-sm flex items-center">                                        
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Plus" />
                                        Add First Document
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
                                    id="documents-table">
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Document Type</th>
                                            <th scope="col" class="px-6 py-3">Document #</th>
                                            <th scope="col" class="px-6 py-3">Issued Date</th>
                                            <th scope="col" class="px-6 py-3">Expiration Date</th>
                                            <th scope="col" class="px-6 py-3">Status</th>
                                            <th class="whitespace-nowrap text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($vehicle->documents as $document)
                                            <tr
                                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 document-row">
                                                <td class="px-6 py-4">
                                                    <div class="font-medium">{{ $document->documentTypeName }}</div>
                                                </td>
                                                <td class="px-6 py-4">{{ $document->document_number ?? 'N/A' }}</td>
                                                <td class="px-6 py-4">
                                                    {{ $document->issued_date ? $document->issued_date->format('m/d/Y') : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if ($document->expiration_date)
                                                        <div
                                                            class="{{ $document->isExpired() ? 'text-danger' : ($document->isAboutToExpire() ? 'text-warning' : 'text-success') }}">
                                                            {{ $document->expiration_date->format('m/d/Y') }}
                                                            @if ($document->isExpired())
                                                                <span
                                                                    class="text-xs bg-danger/10 text-danger px-1.5 py-0.5 rounded">Expired</span>
                                                            @elseif($document->isAboutToExpire())
                                                                <span
                                                                    class="text-xs bg-warning/10 text-warning px-1.5 py-0.5 rounded">Expiring
                                                                    Soon</span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-slate-400">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center whitespace-nowrap">
                                                        <div
                                                            class="w-2 h-2 rounded-full mr-2 {{ $document->status === 'active' ? 'bg-success' : ($document->status === 'expired' ? 'bg-danger' : ($document->status === 'pending' ? 'bg-warning' : 'bg-slate-400')) }}">
                                                        </div>
                                                        {{ $document->statusName }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="flex justify-center space-x-1 gap-2">
                                                        <a href="{{ route('admin.vehicles.documents.preview', [$vehicle->id, $document->id]) }}"
                                                            class="btn btn-primary btn-sm" target="_blank">
                                                            <x-base.lucide class="h-4 w-4" icon="Eye" />
                                                        </a>
                                                        <a href="{{ route('admin.vehicles.documents.download', [$vehicle->id, $document->id]) }}"
                                                            class="btn btn-success btn-sm">
                                                            <x-base.lucide class="h-4 w-4" icon="Download" />
                                                        </a>
                                                        <button type="button" data-tw-toggle="modal"
                                                            data-tw-target="#edit-document-modal"
                                                            class="btn btn-warning btn-sm edit-document-btn"
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
                                                            <x-base.lucide class="h-4 w-4" icon="Edit" />
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="confirmDelete({{ $vehicle->id }}, {{ $document->id }})">
                                                            <x-base.lucide class="h-4 w-4" icon="Trash" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <x-base.dialog id="delete-confirmation-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="XCircle" />
                <div class="mt-5 text-3xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this document? <br>
                    This process cannot be undone.
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <form id="delete-document-form" method="POST" action="">
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
        </x-base.dialog.panel>
    </x-base.dialog>

    <!-- Add Document Modal -->
    <x-base.dialog id="add-document-modal" size="lg">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">
                    Add Document for {{ $vehicle->make }} {{ $vehicle->model }}
                </h2>
            </x-base.dialog.title>
            <form action="{{ route('admin.vehicles.documents.store', $vehicle->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <x-base.dialog.description>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Document Type --}}
                        <div class="col-span-2 md:col-span-1">
                            <x-base.form-label for="document_type">Document Type <span
                                    class="text-danger">*</span></x-base.form-label>
                            <x-base.form-select id="document_type" name="document_type" required>
                                <option value="">Select Document Type</option>
                                @foreach ($documentTypes as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('document_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </x-base.form-select>
                            @error('document_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Document Number --}}
                        <div class="col-span-2 md:col-span-1">
                            <x-base.form-label for="document_number">Document Number</x-base.form-label>
                            <x-base.form-input id="document_number" name="document_number" type="text"
                                value="{{ old('document_number') }}"
                                placeholder="e.g. Policy number or registration ID" />
                            @error('document_number')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Issued Date --}}
                        <div class="col-span-1">
                            <x-base.form-label for="issued_date">Issue Date</x-base.form-label>
                            <x-base.form-input id="issued_date" name="issued_date" type="date"
                                value="{{ old('issued_date') }}" />
                            @error('issued_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Expiration Date --}}
                        <div class="col-span-1">
                            <x-base.form-label for="expiration_date">Expiration Date</x-base.form-label>
                            <x-base.form-input id="expiration_date" name="expiration_date" type="date"
                                value="{{ old('expiration_date') }}" />
                            @error('expiration_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-span-2">
                            <x-base.form-label for="edit_status">Status <span
                                    class="text-danger">*</span></x-base.form-label>
                            <x-base.form-select id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="expired">Expired</option>
                            </x-base.form-select>
                        </div>

                        {{-- Document File Upload --}}
                        <div class="col-span-2">
                            <x-base.form-label for="document_file">Upload Document <span
                                    class="text-danger">*</span></x-base.form-label>
                            <div class="border-2 border-dashed rounded-md pt-4 pb-6 px-4 cursor-pointer"
                                id="file-upload-box">
                                <div class="text-center">
                                    <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Upload" />
                                    <div class="mt-2 text-sm text-slate-600">
                                        <label for="document_file" class="cursor-pointer text-primary font-medium">
                                            Click to upload
                                        </label>
                                        or drag and drop a file
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        PDF, JPG, JPEG, PNG (Max size: 10MB)
                                    </div>
                                    <input id="document_file" name="document_file" type="file" class="hidden"
                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                <div id="file-preview" class="mt-4 hidden">
                                    <div class="flex items-center justify-between bg-slate-100 p-2 rounded">
                                        <div class="flex items-center">
                                            <div class="file-icon bg-primary/10 text-primary p-2 rounded-md">
                                                <x-base.lucide class="h-5 w-5" icon="File" />
                                            </div>
                                            <div class="ml-2 overflow-hidden">
                                                <div class="text-sm font-medium truncate file-name"></div>
                                                <div class="text-xs text-slate-500 file-size"></div>
                                            </div>
                                        </div>
                                        <button type="button" class="text-slate-500 hover:text-danger" id="remove-file">
                                            <x-base.lucide class="h-4 w-4" icon="X" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('document_file')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="col-span-2">
                            <x-base.form-label for="notes">Notes</x-base.form-label>
                            <x-base.form-textarea id="notes" name="notes"
                                placeholder="Additional information about this document">{{ old('notes') }}</x-base.form-textarea>
                            @error('notes')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-base.dialog.description>
                <x-base.dialog.footer>
                    <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-20" type="submit" variant="primary">
                        Save
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

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
                            <x-base.form-label for="edit_document_type">Document Type <span
                                    class="text-danger">*</span></x-base.form-label>
                            <x-base.form-select id="edit_document_type" name="document_type" required>
                                <option value="">Select Document Type</option>
                                @foreach ($documentTypes as $value => $label)
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
                            <x-base.form-label for="edit_status">Status <span
                                    class="text-danger">*</span></x-base.form-label>
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
                                        <div
                                            class="h-16 w-16 flex items-center justify-center bg-primary/10 text-primary rounded">
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
                            <div class="border-2 border-dashed rounded-md pt-4 pb-6 px-4 cursor-pointer"
                                id="edit-file-upload-box">
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
                                    <input id="edit_document_file" name="document_file" type="file" class="hidden"
                                        accept=".pdf,.jpg,.jpeg,.png">
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
                                        <button type="button" class="text-slate-500 hover:text-danger"
                                            id="edit-remove-file">
                                            <x-base.lucide class="h-4 w-4" icon="X" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-span-2">
                            <x-base.form-label for="edit_notes">Notes</x-base.form-label>
                            <x-base.form-textarea id="edit_notes" name="notes"
                                placeholder="Additional information about this document"></x-base.form-textarea>
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
                // Filtro de búsqueda de documentos
                const searchInput = document.getElementById('document-search');
                const rows = document.querySelectorAll('.document-row');

                if (searchInput) {
                    searchInput.addEventListener('keyup', function() {
                        const searchTerm = this.value.toLowerCase();
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(searchTerm) ? '' : 'none';
                        });
                    });
                }

                // File upload for add document
                const fileInput = document.getElementById('document_file');
                const fileUploadBox = document.getElementById('file-upload-box');
                const filePreview = document.getElementById('file-preview');
                const fileName = document.querySelector('.file-name');
                const fileSize = document.querySelector('.file-size');
                const removeFileBtn = document.getElementById('remove-file');

                // Drag and drop functionality for add
                if (fileUploadBox) {
                    fileUploadBox.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        this.classList.add('border-primary');
                        this.classList.add('bg-primary/5');
                    });

                    fileUploadBox.addEventListener('dragleave', function() {
                        this.classList.remove('border-primary');
                        this.classList.remove('bg-primary/5');
                    });

                    fileUploadBox.addEventListener('drop', function(e) {
                        e.preventDefault();
                        this.classList.remove('border-primary');
                        this.classList.remove('bg-primary/5');

                        if (e.dataTransfer.files.length) {
                            fileInput.files = e.dataTransfer.files;
                            updateFilePreview(fileInput, filePreview, fileName, fileSize);
                        }
                    });

                    fileUploadBox.addEventListener('click', function() {
                        fileInput.click();
                    });

                    fileInput.addEventListener('change', function() {
                        updateFilePreview(this, filePreview, fileName, fileSize);
                    });

                    if (removeFileBtn) {
                        removeFileBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            fileInput.value = '';
                            filePreview.classList.add('hidden');
                        });
                    }
                }

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
                            fileIcon.innerHTML =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M16 13H8"></path><path d="M16 17H8"></path><path d="M10 9H8"></path></svg>';
                        } else if (file.type.includes('image')) {
                            fileIcon.innerHTML =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>';
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
                            form.action =
                                `{{ route('admin.vehicles.documents.index', $vehicle->id) }}/${documentId}`;

                            // Fill in form values
                            document.getElementById('edit_document_type').value = documentType;
                            document.getElementById('edit_document_number').value = documentNumber;
                            document.getElementById('edit_issued_date').value = issuedDate;
                            document.getElementById('edit_expiration_date').value = expirationDate;
                            document.getElementById('edit_status').value = status;
                            document.getElementById('edit_notes').value = notes;

                            // Update current document display
                            const currentDocContainer = document.getElementById(
                                'current-document-container');
                            const documentFileName = document.getElementById('edit-file-name');
                            const documentFileSize = document.getElementById('edit-file-size');
                            const documentActionLinks = document.getElementById(
                            'document-action-links');

                            if (hasFile) {
                                currentDocContainer.classList.remove('hidden');
                                documentFileName.textContent = fileName;
                                documentFileSize.textContent = fileSize;

                                // Clear and add action links
                                documentActionLinks.innerHTML = '';

                                // Preview link
                                const previewLink = document.createElement('a');
                                previewLink.href =
                                    `{{ route('admin.vehicles.documents.index', $vehicle->id) }}/${documentId}/preview`;
                                previewLink.className = 'text-xs text-primary';
                                previewLink.target = '_blank';
                                previewLink.innerHTML =
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 inline-block mr-1"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg> Preview';
                                documentActionLinks.appendChild(previewLink);

                                // Spacer
                                documentActionLinks.appendChild(document.createTextNode(' '));

                                // Download link
                                const downloadLink = document.createElement('a');
                                downloadLink.href =
                                    `{{ route('admin.vehicles.documents.index', $vehicle->id) }}/${documentId}/download`;
                                downloadLink.className = 'text-xs text-primary';
                                downloadLink.innerHTML =
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 inline-block mr-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg> Download';
                                documentActionLinks.appendChild(downloadLink);
                            } else {
                                currentDocContainer.classList.add('hidden');
                            }
                        });
                    });
                }

                // Confirm delete function - Simple implementation
                window.confirmDelete = function(vehicleId, documentId) {
                    // Set form action - Usar la ruta correcta para eliminar documentos
                    const form = document.getElementById('delete-document-form');
                    form.action = `{{ route('admin.vehicles.documents.destroy', [$vehicle->id, 'DOCUMENT_ID']) }}`
                        .replace('DOCUMENT_ID', documentId);

                    // Show modal using Tailwind
                    const modal = document.getElementById('delete-confirmation-modal');
                    const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
                    modalInstance.show();
                };

            });
        </script>
    @endpush
@endsection
