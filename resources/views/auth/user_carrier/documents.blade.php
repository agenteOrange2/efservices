<x-guest-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-semibold mb-6">Upload Your Documents</h2>
            
            <!-- Mensaje informativo -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Don't have all documents ready? No problem! You can continue without uploading all documents and complete them later.
                            Required documents are marked with an asterisk (*).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Lista de documentos -->
            <div class="space-y-6">
                @foreach($documents as $document)
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-start justify-between">
                            <div class="flex-grow">
                                <h3 class="text-lg font-medium">
                                    {{ $document['type']->name }}
                                    @if($document['type']->requirement)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Status: <span class="font-medium text-{{ $document['status_name'] === 'Not Uploaded' ? 'yellow' : 'green' }}-600">
                                        {{ $document['status_name'] }}
                                    </span>
                                </p>
                            </div>
                            
                            <!-- Acciones -->
                            <div class="ml-4">
                                @if($document['file_url'])
                                    <a href="{{ $document['file_url'] }}" target="_blank" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                        View File
                                    </a>
                                @endif
                                
                                <button onclick="openUploadModal('{{ $document['type']->id }}')" 
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none ml-2">
                                    {{ $document['file_url'] ? 'Replace' : 'Upload' }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botones de acción -->
            <div class="mt-8 flex justify-end space-x-4">
                <form action="{{ route('carrier.skip-documents') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        Complete Later
                    </button>
                </form>
                
                <form action="{{ route('carrier.documents.complete') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        Submit Documents
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de carga -->
    <div id="uploadModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium mb-4">Upload Document</h3>
                <form id="uploadForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="document" class="w-full mb-4" accept=".pdf,.jpg,.png">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeUploadModal()" 
                                class="px-4 py-2 border border-gray-300 text-sm rounded-md">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const modal = document.getElementById('uploadModal');
        const uploadForm = document.getElementById('uploadForm');
        let currentDocumentTypeId = null;

        function openUploadModal(documentTypeId) {
            currentDocumentTypeId = documentTypeId;
            uploadForm.action = `/carrier/documents/${documentTypeId}/upload`;
            modal.classList.remove('hidden');
        }

        function closeUploadModal() {
            modal.classList.add('hidden');
            uploadForm.reset();
        }

        // Cerrar modal con Esc
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeUploadModal();
        });
    </script>
    @endpush
</x-guest-layout>