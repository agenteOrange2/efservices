<x-carrier-layout>
    @push('styles')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap');

            body {
                font-family: 'DM Sans', sans-serif;
            }

            .document-card {
                transition: all 0.3s ease;
            }

            .document-card:hover {
                transform: translateY(-4px);
            }

            .upload-zone {
                transition: all 0.2s ease;
                background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' stroke='%23E5E7EB' stroke-width='2' stroke-dasharray='6%2c 8' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
            }

            .upload-zone:hover,
            .upload-zone.dragging {
                background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' stroke='%233B82F6' stroke-width='2' stroke-dasharray='6%2c 8' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
            }

            .status-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 6px;
            }

            .progress-bar {
                height: 2px;
                background-color: #e5e7eb;
                overflow: hidden;
                position: relative;
            }

            .progress-value {
                position: absolute;
                height: 100%;
                background-color: #3b82f6;
                transition: width 0.3s ease;
            }

            .toggle-checkbox:checked {
                right: 0;
                border-color: #3b82f6;
            }

            .toggle-checkbox:checked+.toggle-label {
                background-color: #3b82f6;
            }
        </style>
    @endpush

    <div class="min-h-screen bg-gradient-to-br from-white to-gray-50">

        <main class="max-w-6xl mx-auto px-6 py-16">
            <header class="mb-12">
                <div class="flex items-center space-x-2 mb-2">
                    <div class="h-8 w-1 bg-blue-500 rounded-full"></div>
                    <h1 class="text-3xl font-bold tracking-tight">Document Center</h1>
                </div>
                <p class="text-gray-500 text-sm ml-10 max-w-2xl">
                    Don't have all documents ready? No problem! You can continue without uploading all documents and
                    complete them later.
                    <span class="text-blue-500 font-medium">Required documents are marked with an asterisk (*)</span>.
                </p>
                <div class="ml-10 mt-2 text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full inline-block">
                    Carrier: <span class="font-semibold">{{ $carrier->name }}</span>
                </div>
            </header>

            <div class="gap-8">
                <!-- Columna principal - Todos los documentos -->
                <div class="md:col-span-8 space-y-6">
                    <h2 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                        <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                        Documentos
                    </h2>

                    @foreach ($documents as $document)
                        <div class="document-card bg-white rounded-2xl shadow-sm overflow-hidden">
                            <div class="p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-3">
                                    <div class="flex-1">
                                        <h3 class="text-gray-900 font-medium flex items-center">
                                            {{ $document['type']->name }}
                                            @if ($document['type']->requirement)
                                                <span class="ml-1 text-red-500 font-bold">*</span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1 max-w-xl">
                                            {{ $document['type']->description ?? 'Please upload this document to complete your registration.' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center">
                                        <span
                                            class="status-indicator {{ $document['status_name'] === 'Not Uploaded' ? 'bg-gray-300' : ($document['status_name'] === 'Pending' ? 'bg-yellow-400' : 'bg-green-500') }}"></span>
                                        <span
                                            class="text-xs font-medium text-gray-600 px-4 py-2 rounded-sm {{ $document['status_name'] === 'Not Uploaded' ? 'bg-gray-100' : ($document['status_name'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">{{ $document['status_name'] }}</span>
                                    </div>
                                </div>

                                @if ($document['file_url'])
                                    <!-- Documento ya subido -->
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 bg-blue-50 rounded-xl gap-4">
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-500 mr-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Document uploaded</div>
                                                <div class="text-xs text-gray-500">Date:
                                                    {{ $document['document'] ? $document['document']->created_at->format('m/d/Y H:i') : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                            <a href="{{ $document['file_url'] }}" target="_blank"
                                                class="px-4 py-2 bg-primary text-white text-sm rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                                                <svg class="w-6 h-6 sm:w-4 sm:h-4 text-white mr-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                View Document
                                            </a>
                                            <form
                                                action="{{ route('carrier.documents.upload', [$carrier, $document['type']->id]) }}"
                                                method="POST" enctype="multipart/form-data" class="inline">
                                                @csrf
                                                <label
                                                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100 transition-colors flex items-center cursor-pointer justify-center w-full">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                                                    </svg>
                                                    Replace Document
                                                    <input type="file" name="document" class="hidden"
                                                        onchange="this.form.submit()">
                                                </label>
                                            </form>
                                        </div>
                                    </div>
                                @elseif($document['type']->getFirstMediaUrl('default_documents'))
                                    <!-- Documento con plantilla disponible -->
                                    <div class="mt-4 flex flex-col space-y-4">
                                        <!-- Document Info Card -->
                                        <div
                                            class="flex flex-col gap-4 py-4 bg-gray-50 rounded-lg lg:flex-row lg:items-center lg:justify-between lg:gap-6">
                                            <!-- Document Info -->
                                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                                <div class="flex-shrink-0">
                                                    <svg class="w-6 h-6 text-gray-400 sm:w-8 sm:h-8" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-sm font-medium text-gray-900 sm:text-base">
                                                        Document available
                                                    </div>
                                                    <div class="text-xs text-gray-500 sm:text-sm">
                                                        You can use the document or upload your own document
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- View Document Button -->
                                            <div class="flex-shrink-0">
                                                <a href="{{ $document['type']->getFirstMediaUrl('default_documents') }}"
                                                    target="_blank"
                                                    class="inline-flex items-center justify-center w-full px-4 py-2 bg-primary text-white text-sm rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors sm:w-auto sm:px-6">
                                                    <svg class="w-6 h-6 sm:w-4 sm:h-4 text-white mr-3" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span class="whitespace-nowrap">View Document</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Controls Section -->
                                        <div
                                            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                            <!-- Toggle Section -->
                                            <div class="flex items-center gap-3">
                                                <div class="relative inline-block">
                                                    <input type="checkbox" id="toggle-{{ $document['type']->id }}"
                                                        name="toggle-{{ $document['type']->id }}"
                                                        class="toggle-input sr-only"
                                                        {{ $document['document'] && $document['document']->status === 'approved' ? 'checked' : '' }}
                                                        onchange="handleDefaultDocument(this, {{ $document['type']->id }})" />
                                                    <label for="toggle-{{ $document['type']->id }}"
                                                        class="toggle-label relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                        <span class="sr-only">Use document</span>
                                                        <span
                                                            class="toggle-dot pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                                    </label>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700 sm:text-base">Use
                                                    document</span>
                                            </div>

                                            <!-- Upload Section -->
                                            <div class="flex-shrink-0">
                                                <form
                                                    action="{{ route('carrier.documents.upload', [$carrier, $document['type']->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    class="inline-block">
                                                    @csrf
                                                    <label
                                                        class="inline-flex items-center justify-center w-full px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors cursor-pointer sm:w-auto sm:px-6">
                                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                                                        </svg>
                                                        <span class="whitespace-nowrap">Upload Document</span>
                                                        <input type="file" name="document" class="sr-only"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                            onchange="this.form.submit()">
                                                    </label>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                        /* Toggle Switch Styles */
                                        .toggle-input:checked+.toggle-label {
                                            background-color: #03045e;
                                        }

                                        .toggle-input:checked+.toggle-label .toggle-dot {
                                            transform: translateX(100%);
                                        }

                                        .toggle-input:focus+.toggle-label {
                                            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
                                        }
                                    </style>
                                @else
                                    <!-- Sin documento ni plantilla - Formulario directo -->
                                    <form
                                        action="{{ route('carrier.documents.upload', [$carrier, $document['type']->id]) }}"
                                        method="POST" enctype="multipart/form-data" class="mt-4">
                                        @csrf
                                        <div id="upload-zone-{{ $document['type']->id }}"
                                            class="upload-zone p-4 sm:p-6 rounded-xl flex flex-col items-center justify-center cursor-pointer relative border-2 border-dashed border-gray-300">
                                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400 mb-2" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <div class="text-sm font-medium text-gray-900">Click to upload
                                                document</div>
                                            <div class="text-xs text-gray-500 mt-1">or drag and drop here</div>
                                            <input type="file" name="document"
                                                id="document-{{ $document['type']->id }}"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                onchange="showFileName(this, {{ $document['type']->id }})">
                                        </div>
                                        <div id="file-info-{{ $document['type']->id }}"
                                            class="hidden mt-3 p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center flex-1 min-w-0">
                                                    <svg class="w-5 h-5 text-gray-400 mr-2 flex-shrink-0"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span id="file-name-{{ $document['type']->id }}"
                                                        class="text-sm font-medium text-gray-700 truncate"></span>
                                                </div>
                                                <button type="button"
                                                    onclick="removeSelectedFile({{ $document['type']->id }})"
                                                    class="text-gray-400 hover:text-gray-600 flex-shrink-0 ml-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex justify-end">
                                            <button type="submit" id="submit-btn-{{ $document['type']->id }}"
                                                class="px-4 py-2 bg-primary text-white text-sm rounded-md hover:bg-blue-700 transition-colors flex items-center"
                                                style="display: none;">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                                                </svg>
                                                Upload Document
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 border-t border-gray-200 pt-6">
                    <div
                        class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="text-gray-600 text-sm w-full sm:w-auto">
                            <p class="flex items-center justify-center sm:justify-start">
                                <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-center sm:text-left">You can complete your registration now and
                                    upload documents later</span>
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                            <form action="{{ route('carrier.documents.skip', $carrier->slug) }}" method="POST"
                                class="inline w-full sm:w-auto">
                                @csrf
                                <button type="submit"
                                    class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors shadow-sm flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-500 flex-shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                    Skip For Now
                                </button>
                            </form>

                            <form action="{{ route('carrier.documents.complete', $carrier->slug) }}" method="POST"
                                class="inline w-full sm:w-auto">
                                @csrf
                                <button type="submit" id="completeSubmissionBtn"
                                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Complete Submission
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal de carga mejorado -->
        <div id="uploadModal"
            class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
            <div id="modalContent"
                class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Upload Document
                    </h3>
                    <button onclick="closeUploadModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="uploadForm" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div class="border-2 border-dashed border-blue-300 bg-blue-50 rounded-xl p-8 text-center cursor-pointer hover:bg-blue-100 transition-colors duration-200"
                        id="dropZone">
                        <svg class="mx-auto h-16 w-16 text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <p class="mt-4 text-sm font-medium text-blue-600" id="fileStatusText">Drag and drop your file
                            here, or click to select a file</p>
                        <p class="mt-2 text-xs text-blue-500">Files will be uploaded immediately when selected</p>
                        <input type="file" name="document" class="hidden" accept=".pdf,.jpg,.png"
                            id="fileInput">
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-gray-500 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-gray-600">
                                <p class="font-medium">Accepted file types:</p>
                                <ul class="mt-1 list-disc list-inside text-xs text-gray-500 space-y-1">
                                    <li>PDF documents (max 10MB)</li>
                                    <li>JPG/JPEG images (max 10MB)</li>
                                    <li>PNG images (max 10MB)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" onclick="closeUploadModal()"
                            class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button type="submit" id="uploadButton"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>



        @push('scripts')
            <script>
                // Función para manejar el checkbox de documentos por defecto
                async function handleDefaultDocument(checkbox, documentTypeId) {
                    // Deshabilitar el checkbox mientras se procesa
                    checkbox.disabled = true;

                    // Determina si el documento está aprobado o pendiente
                    const approved = checkbox.checked ? 1 : 0;

                    try {
                        const response = await fetch('{{ route('carrier.documents.use-default', $carrier) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                document_type_id: documentTypeId,
                                approved: approved
                            }),
                        });

                        if (response.ok) {
                            const result = await response.json();
                            // Actualizar el estado visual sin recargar la página
                            const statusElement = checkbox.closest('.document-card').querySelector('.status-indicator')
                                .nextElementSibling;
                            if (statusElement) {
                                if (approved) {
                                    statusElement.textContent = 'Approved';
                                    statusElement.className =
                                        'text-xs font-medium text-gray-600 px-4 py-2 rounded-sm bg-green-100 text-green-800';
                                    statusElement.previousElementSibling.className = 'status-indicator bg-green-500';
                                } else {
                                    statusElement.textContent = 'Pending';
                                    statusElement.className =
                                        'text-xs font-medium text-gray-600 px-4 py-2 rounded-sm bg-yellow-100 text-yellow-800';
                                    statusElement.previousElementSibling.className = 'status-indicator bg-yellow-400';
                                }
                            }
                        } else {
                            throw new Error('Failed to update document status.');
                        }
                    } catch (error) {
                        console.error(error);
                        // Revertir el estado del checkbox si ocurre un error
                        checkbox.checked = !checkbox.checked;
                        alert('Error al actualizar el estado del documento. Por favor, intenta de nuevo.');
                    } finally {
                        // Habilitar el checkbox nuevamente
                        checkbox.disabled = false;
                    }
                }

                // Función para mostrar el nombre del archivo seleccionado
                function showFileName(input, documentTypeId) {
                    const fileInfoElement = document.getElementById(`file-info-${documentTypeId}`);
                    const fileNameElement = document.getElementById(`file-name-${documentTypeId}`);
                    const uploadZoneElement = document.getElementById(`upload-zone-${documentTypeId}`);
                    const submitButton = document.getElementById(`submit-btn-${documentTypeId}`);

                    if (input.files && input.files[0]) {
                        const fileName = input.files[0].name;
                        fileNameElement.textContent = fileName;
                        fileInfoElement.classList.remove('hidden');
                        uploadZoneElement.classList.add('border-blue-500');
                        submitButton.style.display = 'inline-flex';
                    }
                }

                // Función para eliminar el archivo seleccionado
                function removeSelectedFile(documentTypeId) {
                    const fileInput = document.getElementById(`document-${documentTypeId}`);
                    const fileInfoElement = document.getElementById(`file-info-${documentTypeId}`);
                    const uploadZoneElement = document.getElementById(`upload-zone-${documentTypeId}`);
                    const submitButton = document.getElementById(`submit-btn-${documentTypeId}`);

                    fileInput.value = '';
                    fileInfoElement.classList.add('hidden');
                    uploadZoneElement.classList.remove('border-blue-500');
                    submitButton.style.display = 'none';
                }

                // Configurar eventos de arrastrar y soltar para cada zona de carga
                document.addEventListener('DOMContentLoaded', function() {
                    // Obtener todas las zonas de carga
                    const uploadZones = document.querySelectorAll('.upload-zone');

                    uploadZones.forEach(zone => {
                        zone.addEventListener('dragover', function(e) {
                            e.preventDefault();
                            this.classList.add('dragging');
                        });

                        zone.addEventListener('dragleave', function() {
                            this.classList.remove('dragging');
                        });

                        zone.addEventListener('drop', function(e) {
                            e.preventDefault();
                            this.classList.remove('dragging');

                            // Obtener el input de archivo dentro de esta zona
                            const fileInput = this.querySelector('input[type="file"]');
                            if (fileInput && e.dataTransfer.files.length) {
                                fileInput.files = e.dataTransfer.files;
                                // Disparar el evento change manualmente
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                fileInput.dispatchEvent(event);
                            }
                        });
                    });
                    
                    // Todo el código de validación ha sido eliminado para que el botón Complete Submission siempre esté activo                        
                    // Todo el código de validación y listeners ha sido eliminado para que el botón Complete Submission siempre esté activo                    
                });
            </script>
        @endpush
        

</x-carrier-layout>
