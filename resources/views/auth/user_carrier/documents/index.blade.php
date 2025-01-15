<x-carrier-layout>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-blue-800 shadow-md">
            <div class="container mx-auto px-4 py-4 flex items-center">
                <div class="text-white font-bold text-xl">EF Services</div>
            </div>
        </header>

        <main class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Upload Your Documents</h2>
                
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 flex items-start">
                    <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-blue-700">
                        Don't have all documents ready? No problem! You can continue without uploading all documents and complete them later.
                        Required documents are marked with an asterisk (*).
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach($documents as $document)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <h3 class="text-lg font-medium text-gray-800">
                                    {{ $document['type']->name }}
                                    @if($document['type']->requirement)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </h3>
                                <p class="text-sm {{ $document['status_name'] === 'Not Uploaded' ? 'text-yellow-600' : 'text-green-600' }}">
                                    Status: {{ $document['status_name'] }}
                                </p>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <!-- Primero mostramos el documento subido si existe -->
                                @if($document['file_url'])
                                    <a href="{{ $document['file_url'] }}" target="_blank" 
                                        class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        View Uploaded
                                    </a>
                                <!-- Si no hay documento subido pero hay uno por defecto -->
                                @elseif($document['type']->getFirstMediaUrl('default_documents'))
                                    <a href="{{ $document['type']->getFirstMediaUrl('default_documents') }}" target="_blank" 
                                        class="px-3 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        View Default
                                    </a>
                                    @if($document['type']->getFirstMediaUrl('default_documents'))
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            id="default-doc-{{ $document['type']->id }}"
                                            class="form-checkbox h-4 w-4 text-blue-600"
                                            onchange="handleDefaultDocument(this, '{{ $document['type']->id }}')"
                                            @if($document['document'] && $document['document']->status === \App\Models\CarrierDocument::STATUS_APPROVED) checked @endif
                                        >
                                        <label class="ml-2">Usar documento por defecto</label>
                                    </div>
                                @endif
                                @endif
                
                                <button onclick="openUploadModal('{{ $document['type']->id }}')"
                                    class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    {{ $document['file_url'] ? 'Replace' : 'Upload' }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <form action="{{ route('carrier.documents.skip', $carrier->slug) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Complete Later
                        </button>
                    </form>
                    
                    <form action="{{ route('carrier.documents.complete', $carrier->slug) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Submit Documents
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <!-- Modal de carga -->
        <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Upload Document</h3>
                    <button onclick="closeUploadModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="uploadForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Drag and drop your file here, or click to select a file</p>
                        <input type="file" name="document" class="hidden" accept=".pdf,.jpg,.png">
                    </div>
                    <div class="text-sm text-gray-500">
                        Accepted file types: PDF, JPG, PNG (max 10MB)
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeUploadModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    @push('scripts')
    <script>

        console.log('Desde consola');
        // Las funciones del modal que ya teníamos
        function openUploadModal(documentTypeId) {
            var modal = document.getElementById('uploadModal');
            var form = document.getElementById('uploadForm');
            form.action = '/carrier/{{ $carrier->slug }}/documents/upload/' + documentTypeId;
            modal.classList.remove('hidden');
        }

        function closeUploadModal() {
            var modal = document.getElementById('uploadModal');
            modal.classList.add('hidden');
        }

        // Nueva función para manejar el checkbox
        function handleDefaultDocument(checkbox, documentTypeId) {
            fetch('/carrier/{{ $carrier->slug }}/documents/' + documentTypeId + '/toggle-default', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    approved: checkbox.checked
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                checkbox.checked = !checkbox.checked;
            });
        }
    </script>


    @endpush
</x-carrier-layout>