@extends('../themes/' . $activeTheme)
@section('title', 'Accident Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Accidents', 'url' => route('admin.accidents.index')],
        ['label' => 'Documents', 'active' => true],
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

        @if (session()->has('error'))
            <div class="alert alert-danger flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Cabecera -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
            <h2 class="text-lg font-medium">
                Documents for Accident: {{ $accident->nature_of_accident }}
                ({{ $accident->accident_date->format('M d, Y') }})
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <x-base.button as="a" href="{{ route('admin.accidents.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to Accidents
                </x-base.button>
                <x-base.button data-tw-toggle="modal" data-tw-target="#add-document-modal" class="w-full sm:w-auto"
                    variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Document
                </x-base.button>
            </div>
        </div>



        <!-- Información del Accidente -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Accident Details</h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <span class="text-gray-500 text-sm">Driver:</span>
                        <p class="font-medium">{{ $accident->userDriverDetail->user->name }}
                            {{ $accident->userDriverDetail->user->last_name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Date:</span>
                        <p class="font-medium">{{ $accident->accident_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Nature of Accident:</span>
                        <p class="font-medium">{{ $accident->nature_of_accident }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Injuries:</span>
                        <p class="font-medium">
                            @if ($accident->had_injuries)
                                <span class="text-success">Yes ({{ $accident->number_of_injuries }})</span>
                            @else
                                <span class="text-danger">No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Fatalities:</span>
                        <p class="font-medium">
                            @if ($accident->had_fatalities)
                                <span class="text-success">Yes ({{ $accident->number_of_fatalities }})</span>
                            @else
                                <span class="text-danger">No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Comments:</span>
                        <p class="font-medium">{{ $accident->comments ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de Documentos -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h3 class="box-title">Documents ({{ $totalDocuments }})</h3>
                    <div class="inline-flex rounded-md shadow-sm w-full sm:w-auto" role="group">
                        <button id="show-all-btn" type="button"
                            class="btn-tab-active px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 flex-1 sm:flex-none">
                            All ({{ $totalDocuments }})
                        </button>
                        <button id="show-images-btn" type="button"
                            class="px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 flex-1 sm:flex-none">
                            Images ({{ count($groupedDocuments['images']) }})
                        </button>
                        <button id="show-pdfs-btn" type="button"
                            class="px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 flex-1 sm:flex-none">
                            PDFs ({{ count($groupedDocuments['pdfs']) }})
                        </button>
                        <button id="show-docs-btn" type="button"
                            class="px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 hover:text-blue-700 focus:z-10 flex-1 sm:flex-none">
                            Other ({{ count($groupedDocuments['documents']) }})
                        </button>
                    </div>
                </div>
            </div>
            <div class="box-body p-5">
                @if ($totalDocuments > 0)
                    <!-- ALL DOCUMENTS -->
                    <div id="all-documents" class="document-group">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($documents as $document)
                                <div
                                    class="border border-gray-200 rounded-md p-4 hover:shadow-md transition-shadow bg-white">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            @if (strpos($document->mime_type, 'image/') === 0)
                                                <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                    target="_blank" class="block">
                                                    <img src="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                        alt="{{ $document->file_name }}"
                                                        class="h-16 w-16 object-cover rounded-md border border-gray-200 hover:border-primary transition">
                                                </a>
                                            @elseif($document->mime_type === 'application/pdf')
                                                <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                    target="_blank" class="block">
                                                    <div
                                                        class="h-16 w-16 flex items-center justify-center bg-red-50 rounded-md border border-gray-200 hover:border-primary transition">
                                                        <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                                    </div>
                                                </a>
                                            @elseif(strpos($document->mime_type, 'word') !== false || strpos($document->mime_type, 'doc') !== false)
                                                <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                    class="block">
                                                    <div
                                                        class="h-16 w-16 flex items-center justify-center bg-blue-50 rounded-md border border-gray-200 hover:border-primary transition">
                                                        <i class="fas fa-file-word text-blue-500 text-xl"></i>
                                                    </div>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                    class="block">
                                                    <div
                                                        class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200 hover:border-primary transition">
                                                        <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                                                    </div>
                                                </a>
                                            @endif
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium truncate" title="{{ $document->file_name }}">
                                                {{ $document->file_name }}</p>
                                            <p class="text-xs text-gray-500">{{ round($document->size / 1024, 2) }} KB ·
                                                {{ $document->created_at->format('M d, Y') }}</p>
                                            <div class="flex mt-2 space-x-2">
                                                <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                    target="_blank"
                                                    class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                                    <i class="fas fa-eye mr-1"></i> <span
                                                        class="hidden sm:inline">View</span>
                                                </a>
                                                <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                    download
                                                    class="text-xs text-green-600 hover:text-green-800 flex items-center">
                                                    <i class="fas fa-download mr-1"></i> <span
                                                        class="hidden sm:inline">Download</span>
                                                </a>
                                                <form
                                                    action="{{ route('admin.accidents.documents.destroy', $document->id) }}"
                                                    method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        onclick="return confirm('Are you sure you want to delete this document?')"
                                                        class="text-xs text-red-600 hover:text-red-800 flex items-center">
                                                        <i class="fas fa-trash mr-1"></i> <span
                                                            class="hidden sm:inline">Delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- IMAGES ONLY -->
                    <div id="images-documents" class="document-group hidden">
                        @if (count($groupedDocuments['images']) > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach ($groupedDocuments['images'] as $image)
                                    <div
                                        class="border border-gray-200 rounded-md p-2 hover:shadow-md transition-shadow bg-white">
                                        <a href="{{ route('admin.accidents.document.preview', $image->id) }}"
                                            target="_blank" class="block">
                                            <img src="{{ route('admin.accidents.document.preview', $image->id) }}"
                                                alt="{{ $image->file_name }}"
                                                class="w-full h-32 object-cover rounded-md hover:opacity-90 transition">
                                        </a>
                                        <div class="mt-2">
                                            <p class="text-xs font-medium truncate" title="{{ $image->file_name }}">
                                                {{ $image->file_name }}</p>
                                            <div class="flex mt-1 space-x-1 justify-between">
                                                <a href="{{ route('admin.accidents.document.preview', $image->id) }}"
                                                    target="_blank" class="text-xs text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.accidents.document.preview', $image->id) }}"
                                                    download class="text-xs text-green-600 hover:text-green-800">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form
                                                    action="{{ route('admin.accidents.documents.destroy', $image->id) }}"
                                                    method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="text-xs text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8">
                                <x-base.lucide class="w-16 h-16 text-slate-300" icon="image" />
                                <p class="mt-2 text-slate-500">No images found for this accident</p>
                            </div>
                        @endif
                    </div>

                    <!-- PDFs ONLY -->
                    <div id="pdfs-documents" class="document-group hidden">
                        @if (count($groupedDocuments['pdfs']) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($groupedDocuments['pdfs'] as $pdf)
                                    <div
                                        class="border border-gray-200 rounded-md p-4 hover:shadow-md transition-shadow bg-white">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <a href="{{ route('admin.accidents.document.preview', $pdf->id) }}"
                                                    target="_blank" class="block">
                                                    <div
                                                        class="h-16 w-16 flex items-center justify-center bg-red-50 rounded-md border border-gray-200 hover:border-primary transition">
                                                        <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium truncate" title="{{ $pdf->file_name }}">
                                                    {{ $pdf->file_name }}</p>
                                                <p class="text-xs text-gray-500">{{ round($pdf->size / 1024, 2) }} KB</p>
                                                <div class="flex mt-2 space-x-2">
                                                    <a href="{{ route('admin.accidents.document.preview', $pdf->id) }}"
                                                        target="_blank" class="text-xs text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-eye mr-1"></i> <span
                                                            class="hidden sm:inline">View</span>
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.accidents.documents.destroy', $pdf->id) }}"
                                                        method="POST" class="inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('Are you sure?')"
                                                            class="text-xs text-red-600 hover:text-red-800">
                                                            <i class="fas fa-trash mr-1"></i> <span
                                                                class="hidden sm:inline">Delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8">
                                <x-base.lucide class="w-16 h-16 text-slate-300" icon="file-text" />
                                <p class="mt-2 text-slate-500">No PDF documents found for this accident</p>
                            </div>
                        @endif
                    </div>

                    <!-- OTHER DOCUMENTS -->
                    <div id="other-documents" class="document-group hidden">
                        @if (count($groupedDocuments['documents']) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($groupedDocuments['documents'] as $doc)
                                    <div
                                        class="border border-gray-200 rounded-md p-4 hover:shadow-md transition-shadow bg-white">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <a href="{{ route('admin.accidents.document.preview', $doc->id) }}"
                                                    class="block">
                                                    <div
                                                        class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200 hover:border-primary transition">
                                                        @if (strpos($doc->mime_type, 'word') !== false || strpos($doc->mime_type, 'doc') !== false)
                                                            <i class="fas fa-file-word text-blue-500 text-xl"></i>
                                                        @else
                                                            <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium truncate" title="{{ $doc->file_name }}">
                                                    {{ $doc->file_name }}</p>
                                                <p class="text-xs text-gray-500">{{ round($doc->size / 1024, 2) }} KB</p>
                                                <div class="flex mt-2 space-x-2">
                                                    <a href="{{ route('admin.accidents.document.preview', $doc->id) }}"
                                                        class="text-xs text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-download mr-1"></i> <span
                                                            class="hidden sm:inline">Download</span>
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.accidents.documents.destroy', $doc->id) }}"
                                                        method="POST" class="inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('Are you sure?')"
                                                            class="text-xs text-red-600 hover:text-red-800">
                                                            <i class="fas fa-trash mr-1"></i> <span
                                                                class="hidden sm:inline">Delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8">
                                <x-base.lucide class="w-16 h-16 text-slate-300" icon="file" />
                                <p class="mt-2 text-slate-500">No other documents found for this accident</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8">
                        <x-base.lucide class="w-16 h-16 text-slate-300" icon="file-question" />
                        <p class="mt-2 text-slate-500">No documents found for this accident</p>
                        <button data-tw-toggle="modal" data-tw-target="#add-document-modal"
                            class="btn btn-outline-primary mt-4">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                            Add First Document
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para Añadir Documento -->
    <x-base.dialog id="add-document-modal" size="md">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Document</h2>
            </x-base.dialog.title>

            <form action="{{ route('admin.accidents.update', $accident->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_driver_detail_id" value="{{ $accident->user_driver_detail_id }}">
                <input type="hidden" name="accident_date" value="{{ $accident->accident_date->format('Y-m-d') }}">
                <input type="hidden" name="nature_of_accident" value="{{ $accident->nature_of_accident }}">
                <input type="hidden" name="had_injuries" value="{{ $accident->had_injuries ? '1' : '0' }}">
                <input type="hidden" name="number_of_injuries" value="{{ $accident->number_of_injuries }}">
                <input type="hidden" name="had_fatalities" value="{{ $accident->had_fatalities ? '1' : '0' }}">
                <input type="hidden" name="number_of_fatalities" value="{{ $accident->number_of_fatalities }}">
                <input type="hidden" name="comments" value="{{ $accident->comments }}">

                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">Upload Documents</label>
                        <div class="border-2 border-dashed rounded-md p-6 text-center">
                            <div class="mx-auto cursor-pointer relative">
                                <input type="file" name="documents[]" multiple
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                    class="w-full h-full opacity-0 absolute inset-0 cursor-pointer z-50">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Drag and drop files here or click to browse</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-base.dialog.description>

                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Upload
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Manejar las pestañas de documentos
                const showAllBtn = document.getElementById('show-all-btn');
                const showImagesBtn = document.getElementById('show-images-btn');
                const showPdfsBtn = document.getElementById('show-pdfs-btn');
                const showDocsBtn = document.getElementById('show-docs-btn');

                const allDocuments = document.getElementById('all-documents');
                const imagesDocuments = document.getElementById('images-documents');
                const pdfsDocuments = document.getElementById('pdfs-documents');
                const otherDocuments = document.getElementById('other-documents');

                // Clase para los botones activos
                const activeClass = 'bg-primary text-white';
                const inactiveClass = 'bg-white text-gray-700';

                // Función para ocultar todos los grupos de documentos
                function hideAllDocumentGroups() {
                    const groups = document.querySelectorAll('.document-group');
                    groups.forEach(group => {
                        group.classList.add('hidden');
                    });

                    // Resetear todos los botones
                    [showAllBtn, showImagesBtn, showPdfsBtn, showDocsBtn].forEach(btn => {
                        btn.classList.remove('bg-primary', 'text-white');
                        btn.classList.add('bg-white', 'text-gray-700');
                    });
                }

                // Mostrar todos los documentos
                showAllBtn.addEventListener('click', function() {
                    hideAllDocumentGroups();
                    allDocuments.classList.remove('hidden');
                    this.classList.remove('bg-white', 'text-gray-700');
                    this.classList.add('bg-primary', 'text-white');
                });

                // Mostrar solo imágenes
                showImagesBtn.addEventListener('click', function() {
                    hideAllDocumentGroups();
                    imagesDocuments.classList.remove('hidden');
                    this.classList.remove('bg-white', 'text-gray-700');
                    this.classList.add('bg-primary', 'text-white');
                });

                // Mostrar solo PDFs
                showPdfsBtn.addEventListener('click', function() {
                    hideAllDocumentGroups();
                    pdfsDocuments.classList.remove('hidden');
                    this.classList.remove('bg-white', 'text-gray-700');
                    this.classList.add('bg-primary', 'text-white');
                });

                // Mostrar otros documentos
                showDocsBtn.addEventListener('click', function() {
                    hideAllDocumentGroups();
                    otherDocuments.classList.remove('hidden');
                    this.classList.remove('bg-white', 'text-gray-700');
                    this.classList.add('bg-primary', 'text-white');
                });

                // Inicializar - mostrar todos los documentos por defecto
                showAllBtn.classList.add('bg-primary', 'text-white');
                showAllBtn.classList.remove('bg-white', 'text-gray-700');

                // Previsualización de documentos al hacer clic
                const previewLinks = document.querySelectorAll('a[href*="document.preview"]');
                previewLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        // Solo para imágenes y PDFs en nuevas pestañas
                        if (this.getAttribute('target') === '_blank') {
                            return true; // Continuar normalmente
                        }

                        // Para otros documentos, preguntar si desea descargar
                        if (!confirm('¿Desea descargar este documento?')) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });
            });
        </script>
    @endpush

@endsection
