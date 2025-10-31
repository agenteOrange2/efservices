@extends('../themes/' . $activeTheme)
@section('title', 'Documents Overview')
@php
    $breadcrumbLinks = [
        ['label' => 'Carrier', 'url' => route('carrier.dashboard')],
        ['label' => 'Documents Overview', 'active' => true],
    ];
@endphp

@section('subcontent')
    <style>
        .document-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .document-card.uploaded {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%);
            border-color: #22c55e;
        }

        .document-card.pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%);
            border-color: #f59e0b;
        }

        .document-card.missing {
            background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
            border-color: #ef4444;
        }

        .document-card.default-available {
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            border-color: #3b82f6;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-uploaded {
            background-color: #22c55e;
            color: white;
        }

        .status-pending {
            background-color: #f59e0b;
            color: white;
        }

        .status-missing {
            background-color: #ef4444;
            color: white;
        }

        .status-default-available {
            background-color: #3b82f6;
            color: white;
        }

        .upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .upload-area:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .upload-area.dragover {
            border-color: #22c55e;
            background: #f0f9f0;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: none;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-success {
            background-color: #22c55e;
            color: white;
        }

        .btn-success:hover {
            background-color: #16a34a;
        }

        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
        }

        .btn-outline {
            background-color: transparent;
            border-color: #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background-color: #f3f4f6;
        }

        .requirement-badge {
            font-size: 0.625rem;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .mandatory {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .optional {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        /* Tab filtering styles */
        .document-item {
            display: block;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .document-item.hidden {
            display: none;
        }

        .tab-filter {
            position: relative;
            transition: all 0.3s ease;
        }

        .tab-filter.active {
            background-color: #1B1C6E;
            color: white;
            padding: 15px;
        }

        .tab-filter.active #count-all,
        .tab-filter.active #count-uploaded,
        .tab-filter.active #count-pending,
        .tab-filter.active #count-missing,
        .tab-filter.active #count-default-available {
            color: #f0f9f0;
            background:rgba(240, 249, 240, 0.44)!important;
        }

        .tab-filter.active .document-count {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .document-count {
            background-color: #e5e7eb;
            color: #374151;
            font-size: 0.75rem;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            margin-left: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Document Center
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="primary">
                    <x-base.lucide class="mr-3 h-4 w-4 stroke-[1.3]" icon="FileText" />
                    Document Status
                </x-base.button>
            </div>
        </div>
        <div class="mt-3.5 grid grid-cols-12 gap-x-6 gap-y-10">
            <div class="relative col-span-12 xl:col-span-3">
                <div class="sticky top-[104px]">
                    <div class="box box--stacked flex flex-col px-2 pb-6 pt-5">
                        <a href="#" class="tab-filter flex items-center py-3 first:-mt-3 last:-mb-3 active text-primary font-medium" data-filter="all">
                            <x-base.lucide class="mr-3 h-4 w-4 stroke-[1.3]" icon="FileText" />
                            All Documents 
                            <span class="ml-auto text-xs bg-primary/10 text-primary px-2 py-1 rounded-full" id="count-all">0</span>
                        </a>
                        <a href="#" class="tab-filter flex items-center py-3 first:-mt-3 last:-mb-3 hover:text-primary" data-filter="uploaded">
                            <x-base.lucide class="mr-3 h-4 w-4 stroke-[1.3]" icon="CheckCircle" />
                            Uploaded 
                            <span class="ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full" id="count-uploaded">0</span>
                        </a>
                        <a href="#" class="tab-filter flex items-center py-3 first:-mt-3 last:-mb-3 hover:text-primary" data-filter="pending">
                            <x-base.lucide class="mr-3 h-4 w-4 stroke-[1.3]" icon="Clock" />
                            Pending 
                            <span class="ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full" id="count-pending">0</span>
                        </a>
                        <a href="#" class="tab-filter flex items-center py-3 first:-mt-3 last:-mb-3 hover:text-primary" data-filter="missing">
                            <x-base.lucide class="mr-3 h-4 w-4 stroke-[1.3]" icon="AlertCircle" />
                            Missing 
                            <span class="ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full" id="count-missing">0</span>
                        </a>
                        <a href="#" class="tab-filter flex items-center py-3 first:-mt-3 last:-mb-3 hover:text-primary" data-filter="default-available">
                            <x-base.lucide class="mr-3 h-4 w-4 stroke-[1.3]" icon="Download" />
                            Default Available 
                            <span class="ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full" id="count-default-available">0</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-span-12 flex flex-col gap-y-7 xl:col-span-9">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="box box--stacked flex flex-col p-5">
                        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="box box--stacked flex flex-col p-5">
                        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="box box--stacked flex flex-col p-5">
                    <div class="mb-6 border-b border-dashed border-slate-300/70 pb-5 text-[0.94rem] font-medium">
                        Document Management
                    </div>
                    
                    @if(count($mappedDocuments) > 0)
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            @foreach($mappedDocuments as $document)
                                @php
                                    // $mappedDocuments viene del controlador y contiene la estructura correcta
                                    // Cada elemento tiene: type, document, status, has_file, has_default
                                    $documentType = $document['type'];
                                    $carrierDocument = $document['document'];
                                    $hasDefaultDocument = $documentType->getFirstMediaUrl('default_documents');
                                    $carrierDocumentUrl = $carrierDocument ? $carrierDocument->getFirstMediaUrl('carrier_documents') : null;
                                    $isRequired = $documentType->requirement;
                                    
                                    $status = 'missing';
                                    $statusText = 'Missing';
                                    $statusClass = 'status-missing';
                                    $cardClass = 'missing';
                                    
                                    // Determinar el estado del documento basado en la lógica del admin
                                    if ($carrierDocumentUrl) {
                                        // El carrier ha subido su propio documento
                                        $status = 'uploaded';
                                        $statusClass = 'status-uploaded';
                                        $cardClass = 'uploaded';
                                        $statusText = match($carrierDocument->status ?? 1) {
                                            0 => 'Pending',
                                            1 => 'Approved',
                                            2 => 'Rejected',
                                            3 => 'In Process',
                                            default => 'Uploaded'
                                        };
                                    } elseif ($hasDefaultDocument) {
                                        // Hay documento por defecto disponible
                                        $status = 'default-available';
                                        $statusText = 'Default Available';
                                        $statusClass = 'status-default-available';
                                        $cardClass = 'default-available';
                                    }
                                    
                                    // Determinar el icono basado en el estado
                                    $statusIcon = match($status) {
                                        'uploaded' => match($carrierDocument->status ?? 1) {
                                            1 => 'check-circle',
                                            2 => 'x-circle', 
                                            default => 'clock'
                                        },
                                        'default-available' => 'file-text',
                                        'missing' => 'alert-circle',
                                        default => 'file'
                                    };
                                @endphp

                                <div class="document-card {{ $cardClass }} rounded-[0.6rem] border border-slate-200/80 p-5">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="font-medium text-slate-700 mb-2">
                                                {{ $documentType->name }}
                                                @if($documentType->requirement)
                                <span class="ml-1 text-danger font-bold">*</span>
                                @endif
                                            </h3>
                                            <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                        </div>
                                        <div class="ml-4">
                                            @if($status === 'uploaded')
                                                <x-base.lucide class="w-6 h-6 text-success" icon="CheckCircle" />
                                            @elseif($status === 'default-available')
                                                <x-base.lucide class="w-6 h-6 text-primary" icon="Download" />
                                            @else
                                                <x-base.lucide class="w-6 h-6 text-danger" icon="AlertCircle" />
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-xs text-slate-500 mb-2">
                                            {{ $documentType->description ?? 'Please upload this document to complete your registration.' }}
                                        </p>
                                        <span class="requirement-badge {{ $isRequired ? 'mandatory' : 'optional' }}">
                                            {{ $isRequired ? 'OBLIGATORY' : 'OPTIONAL' }}
                                        </span>
                                    </div>

                                    @if($carrierDocumentUrl)
                                    <!-- Documento subido -->
                                    <div class="bg-slate-50 rounded-lg p-4 mb-4">
                                        <div class="flex flex-col items-start justify-between gap-5">
                                            <div class="flex items-center">
                                                <x-base.lucide class="w-8 h-8 text-primary mr-3" icon="FileText" />
                                                <div>
                                                    <p class="text-sm font-medium text-slate-700">{{ $carrierDocument->getFirstMedia('carrier_documents')->file_name ?? 'Document' }}</p>
                                                     <p class="text-xs text-slate-500">Uploaded {{ $carrierDocument->created_at->format('M d, Y') }}</p>
                                                    @if($hasDefaultDocument)
                                                    <p class="text-xs text-primary mt-1">Admin default document also available</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <x-base.button onclick="window.open('{{ $carrierDocumentUrl }}', '_blank')" 
                                                              variant="outline-primary" size="sm">
                                                     <x-base.lucide class="w-4 h-4 mr-1" icon="Eye" />
                                                     View Document
                                                 </x-base.button>
                                                <x-base.button onclick="openReplaceModal({{ $documentType->id }}, '{{ $documentType->name }}')" 
                                                             variant="outline-warning" size="sm">
                                                    <x-base.lucide class="w-4 h-4 mr-1" icon="Upload" />
                                                    Replace
                                                </x-base.button>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif($hasDefaultDocument)
                                    <!-- Default document available -->
                                    <div class="bg-primary/5 rounded-lg p-4 mb-4">
                                        <div class="flex flex-col items-start justify-between gap-5">
                                            <div class="flex items-center">
                                                <x-base.lucide class="w-8 h-8 text-primary mr-3" icon="FileText" />
                                                <div>
                                                    <p class="text-sm font-medium text-primary">Admin default document available</p>
                                                    <p class="text-xs text-primary/70">You can accept this document or upload your own</p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <x-base.button onclick="window.open('{{ $hasDefaultDocument }}', '_blank')" 
                                                             variant="outline-primary" size="sm">
                                                    <x-base.lucide class="w-4 h-4 mr-1" icon="Eye" />
                                                    View Default
                                                </x-base.button>
                                                <x-base.button onclick="acceptDefaultDocument({{ $documentType->id }})" 
                                                             variant="success" size="sm" class="text-white">
                                                    <x-base.lucide class="w-4 h-4 mr-1" icon="Check" />
                                                    Accept
                                                </x-base.button>
                                            </div>
                                        </div>
                                    </div>
                                    @if($hasDefaultDocument && !$carrierDocumentUrl)
                                        <div class="mb-4 p-3 bg-slate-50 rounded-md border border-slate-200">
                                            <div class="flex items-center">
                                                <x-base.form-check>
                                                    <x-base.form-check.input 
                                                        id="accept_default_{{ $documentType->id }}" 
                                                        type="checkbox" 
                                                        onchange="toggleAcceptDefault({{ $documentType->id }}, this.checked)" />
                                                    <x-base.form-check.label for="accept_default_{{ $documentType->id }}" class="ml-2 text-sm text-slate-600">
                                                        Accept default document
                                                    </x-base.form-check.label>
                                                </x-base.form-check>
                                            </div>
                                        </div>
                                    @endif
                                    <!-- Upload custom option -->
                                    <div class="text-center">
                                        <x-base.button onclick="openUploadModal({{ $documentType->id }}, '{{ $documentType->name }}')" 
                                                     variant="outline-primary" class="w-full">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Upload" />
                                            Upload Your Own Document
                                        </x-base.button>
                                        <p class="text-xs text-slate-500 mt-2">PDF, JPG, PNG up to 10MB</p>
                                    </div>
                                    @else
                                    <!-- Zona de carga -->
                                    <div class="text-center p-6">
                                        <x-base.button onclick="openUploadModal({{ $documentType->id }}, '{{ $documentType->name }}')" 
                                                     variant="primary" class="w-full">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Upload" />
                                            Upload Document
                                        </x-base.button>
                                        <p class="text-xs text-slate-500 mt-2">PDF, JPG, PNG up to 10MB</p>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="FileText" />
                            <h3 class="mt-2 text-sm font-medium text-slate-700">No documents found</h3>
                            <p class="mt-1 text-sm text-slate-500">No document types have been configured yet.</p>
                        </div>
                    @endif
                </div>

                <!-- Action buttons -->
                <div class="box box--stacked flex flex-col p-5">
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                        <x-base.button onclick="window.location.href='{{ route('carrier.dashboard') }}'" 
                                     variant="outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="ArrowLeft" />
                            Back to Dashboard
                        </x-base.button>

                        @if(!empty($mappedDocuments))
                        <x-base.button onclick="skipDocuments()" variant="primary">
                            Skip for Now
                        </x-base.button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Modal de carga de documentos -->
    <div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="uploadModalTitle">Upload Document</h3>
                    <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="uploadForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="document_type_id" id="uploadDocumentTypeId">

                    <div class="mb-4">
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors" id="dropZone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="file-upload" name="document" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" onchange="handleFileSelect(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, PNG, JPG up to 10MB</p>
                            </div>
                        </div>
                    </div>

                    <div id="fileInfo" class="hidden mb-4 p-3 bg-gray-50 rounded-md">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900" id="fileName"></p>
                                <p class="text-xs text-gray-500" id="fileSize"></p>
                            </div>
                            <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de reemplazo de documentos -->
    <div id="replaceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="replaceModalTitle">Replace Document</h3>
                    <button onclick="closeReplaceModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="replaceForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="document_type_id" id="replaceDocumentTypeId">

                    <div class="mb-4">
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors" id="replaceDropZone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="replace-file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="replace-file-upload" name="document" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" onchange="handleReplaceFileSelect(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, PNG, JPG up to 10MB</p>
                            </div>
                        </div>
                    </div>

                    <div id="replaceFileInfo" class="hidden mb-4 p-3 bg-gray-50 rounded-md">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900" id="replaceFileName"></p>
                                <p class="text-xs text-gray-500" id="replaceFileSize"></p>
                            </div>
                            <button type="button" onclick="removeReplaceFile()" class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeReplaceModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Replace
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let currentDocumentTypeId = null;
    let currentDocumentTypeName = null;

    // Funciones para modal de carga
    function openUploadModal(documentTypeId, documentTypeName) {
        currentDocumentTypeId = documentTypeId;
        currentDocumentTypeName = documentTypeName;

        document.getElementById('uploadModalTitle').textContent = `Upload ${documentTypeName}`;
        document.getElementById('uploadDocumentTypeId').value = documentTypeId;
        document.getElementById('uploadForm').action = `{{ route('carrier.documents.upload', [$carrier->slug, ':id']) }}`.replace(':id', documentTypeId);

        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
        resetUploadForm();
    }

    function resetUploadForm() {
        document.getElementById('file-upload').value = '';
        document.getElementById('fileInfo').classList.add('hidden');
    }

    // Funciones para modal de reemplazo
    function openReplaceModal(documentTypeId, documentTypeName) {
        currentDocumentTypeId = documentTypeId;
        currentDocumentTypeName = documentTypeName;

        document.getElementById('replaceModalTitle').textContent = `Replace ${documentTypeName}`;
        document.getElementById('replaceDocumentTypeId').value = documentTypeId;
        document.getElementById('replaceForm').action = `{{ route('carrier.documents.upload', [$carrier->slug, ':id']) }}`.replace(':id', documentTypeId);

        document.getElementById('replaceModal').classList.remove('hidden');
    }

    function closeReplaceModal() {
        document.getElementById('replaceModal').classList.add('hidden');
        resetReplaceForm();
    }

    function resetReplaceForm() {
        document.getElementById('replace-file-upload').value = '';
        document.getElementById('replaceFileInfo').classList.add('hidden');
    }

    // Validación de archivos
    function validateFile(file) {
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 10 * 1024 * 1024; // 10MB en bytes

        if (!allowedTypes.includes(file.type)) {
            alert('Only PDF, JPG, and PNG files are allowed.');
            return false;
        }

        if (file.size > maxSize) {
            alert('File size must be less than 10MB.');
            return false;
        }

        return true;
    }

    // Manejo de archivos para upload
    function handleFileSelect(input) {
        const file = input.files[0];
        if (file) {
            if (validateFile(file)) {
                displayFileInfo(file, 'fileInfo', 'fileName', 'fileSize');
            } else {
                input.value = ''; // Clear invalid file
            }
        }
    }

    function handleReplaceFileSelect(input) {
        const file = input.files[0];
        if (file) {
            if (validateFile(file)) {
                displayFileInfo(file, 'replaceFileInfo', 'replaceFileName', 'replaceFileSize');
            } else {
                input.value = ''; // Clear invalid file
            }
        }
    }

    function displayFileInfo(file, infoId, nameId, sizeId) {
        document.getElementById(nameId).textContent = file.name;
        document.getElementById(sizeId).textContent = formatFileSize(file.size);
        document.getElementById(infoId).classList.remove('hidden');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function removeFile() {
        document.getElementById('file-upload').value = '';
        document.getElementById('fileInfo').classList.add('hidden');
    }

    function removeReplaceFile() {
        document.getElementById('replace-file-upload').value = '';
        document.getElementById('replaceFileInfo').classList.add('hidden');
    }

    // Función para saltar documentos
    function skipDocuments() {
        if (confirm('Are you sure you want to skip document upload? You can upload them later from your dashboard.')) {
            window.location.href = '{{ route('carrier.dashboard') }}';
        }
    }

    // Funcionalidad de filtrado de tabs
    document.addEventListener('DOMContentLoaded', function() {
        initializeTabFiltering();
        updateDocumentCounts();
    });

    function initializeTabFiltering() {
        const tabFilters = document.querySelectorAll('.tab-filter');
        
        tabFilters.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remover clase active de todos los tabs
                tabFilters.forEach(t => t.classList.remove('active'));
                
                // Agregar clase active al tab clickeado
                this.classList.add('active');
                
                // Obtener el filtro
                const filter = this.getAttribute('data-filter');
                
                // Filtrar documentos
                filterDocuments(filter);
            });
        });
    }

    function filterDocuments(filter) {
        const documentCards = document.querySelectorAll('.document-card');
        
        documentCards.forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
                card.classList.add('fade-in');
            } else {
                if (card.classList.contains(filter)) {
                    card.style.display = 'block';
                    card.classList.add('fade-in');
                } else {
                    card.style.display = 'none';
                    card.classList.remove('fade-in');
                }
            }
        });
    }

    function updateDocumentCounts() {
        const documentCards = document.querySelectorAll('.document-card');
        
        // Contadores
        let counts = {
            all: documentCards.length,
            uploaded: 0,
            pending: 0,
            missing: 0,
            'default-available': 0
        };
        
        // Contar documentos por categoría
        documentCards.forEach(card => {
            if (card.classList.contains('uploaded')) {
                counts.uploaded++;
            } else if (card.classList.contains('default-available')) {
                counts['default-available']++;
            } else if (card.classList.contains('missing')) {
                counts.missing++;
            }
            
            // Verificar si es pending basado en el status badge
            const statusBadge = card.querySelector('.status-badge');
            if (statusBadge && statusBadge.textContent.trim() === 'Pending') {
                counts.pending++;
            }
        });
        
        // Actualizar los contadores en la UI
        Object.keys(counts).forEach(key => {
            const countElement = document.getElementById(`count-${key}`);
            if (countElement) {
                countElement.textContent = counts[key];
            }
        });
    }

    // Función para aceptar documento por defecto
    function acceptDefaultDocument(documentTypeId) {
        fetch(`{{ route('carrier.documents.toggle-default', [$carrier->slug, ':id']) }}`.replace(':id', documentTypeId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Toastify({
                    text: data.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#10B981",
                }).showToast();
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                Toastify({
                    text: data.message || 'Error accepting default document',
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EF4444",
                }).showToast();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toastify({
                text: 'Error accepting default document',
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EF4444",
            }).showToast();
        });
    }

    function toggleAcceptDefault(documentTypeId, isChecked) {
        if (isChecked) {
            // Show confirmation before accepting
            if (confirm('Are you sure you want to accept the default document? This will use the admin-provided document for your carrier.')) {
                acceptDefaultDocument(documentTypeId);
            } else {
                // Uncheck the checkbox if user cancels
                document.getElementById(`accept_default_${documentTypeId}`).checked = false;
            }
        }
    }

    // Envío de formularios
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Upload form submitted');
        
        const fileInput = document.getElementById('file-upload');
        if (!fileInput.files[0]) {
            alert('Please select a file to upload.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('document_type_id', currentDocumentTypeId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        console.log('FormData prepared:', {
            file: fileInput.files[0].name,
            documentTypeId: currentDocumentTypeId,
            action: this.action
        });
        
        const submitButton = this.querySelector('button[type="submit"]');
        
        submitButton.disabled = true;
        submitButton.textContent = 'Uploading...';

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                Toastify({
                    text: data.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#10B981",
                }).showToast();
                
                closeUploadModal();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                Toastify({
                    text: data.message || 'Error uploading document',
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EF4444",
                }).showToast();
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            Toastify({
                text: 'Error uploading document: ' + error.message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EF4444",
            }).showToast();
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Upload';
        });
    });

    document.getElementById('replaceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Replace form submitted');
        
        const fileInput = document.getElementById('replace-file-upload');
        if (!fileInput.files[0]) {
            alert('Please select a file to upload.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('document_type_id', currentDocumentTypeId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        console.log('FormData prepared:', {
            file: fileInput.files[0].name,
            documentTypeId: currentDocumentTypeId,
            action: this.action
        });
        
        const submitButton = this.querySelector('button[type="submit"]');
        
        submitButton.disabled = true;
        submitButton.textContent = 'Replacing...';

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                Toastify({
                    text: data.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#10B981",
                }).showToast();
                
                closeReplaceModal();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                Toastify({
                    text: data.message || 'Error replacing document',
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EF4444",
                }).showToast();
            }
        })
        .catch(error => {
            console.error('Replace error:', error);
            Toastify({
                text: 'Error replacing document: ' + error.message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EF4444",
            }).showToast();
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Replace';
        });
    });

    // Funciones para filtrado de documentos
    function filterDocuments(filter) {
        const documentCards = document.querySelectorAll('.document-card');
        
        documentCards.forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
            } else {
                const hasClass = card.classList.contains(filter);
                card.style.display = hasClass ? 'block' : 'none';
            }
        });
        
        // Actualizar estado activo de los tabs
        document.querySelectorAll('.tab-filter').forEach(tab => {
            tab.classList.remove('active', 'text-primary', 'font-medium');
            tab.classList.add('hover:text-primary');
        });
        
        const activeTab = document.querySelector(`[data-filter="${filter}"]`);
        if (activeTab) {
            activeTab.classList.add('active', 'text-primary', 'font-medium');
            activeTab.classList.remove('hover:text-primary');
        }
    }
    
    function updateDocumentCounts() {
        const documentCards = document.querySelectorAll('.document-card');
        const counts = {
            all: documentCards.length,
            uploaded: 0,
            pending: 0,
            missing: 0,
            'default-available': 0
        };
        
        documentCards.forEach(card => {
            if (card.classList.contains('uploaded')) {
                counts.uploaded++;
            } else if (card.classList.contains('missing')) {
                counts.missing++;
            } else if (card.classList.contains('default-available')) {
                counts['default-available']++;
            }
            
            // Contar pending basado en el status del documento
            const statusBadge = card.querySelector('.status-badge');
            if (statusBadge && statusBadge.textContent.includes('Pending')) {
                counts.pending++;
            }
        });
        
        // Actualizar los contadores en la UI
        Object.keys(counts).forEach(key => {
            const countElement = document.getElementById(`count-${key}`);
            if (countElement) {
                countElement.textContent = counts[key];
                
                // Actualizar estilos del contador
                if (key === 'all' && counts[key] > 0) {
                    countElement.className = 'ml-auto text-xs bg-primary/10 text-primary px-2 py-1 rounded-full';
                } else if (counts[key] > 0) {
                    countElement.className = 'ml-auto text-xs bg-primary/10 text-primary px-2 py-1 rounded-full';
                } else {
                    countElement.className = 'ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full';
                }
            }
        });
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar contadores
        updateDocumentCounts();
        
        // Agregar event listeners a los tabs
        document.querySelectorAll('.tab-filter').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');
                filterDocuments(filter);
            });
        });
        
        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', function(e) {
            const uploadModal = document.getElementById('uploadModal');
            const replaceModal = document.getElementById('replaceModal');

            if (e.target === uploadModal) {
                closeUploadModal();
            }
            if (e.target === replaceModal) {
                closeReplaceModal();
            }
        });
    });
</script>
@endpush