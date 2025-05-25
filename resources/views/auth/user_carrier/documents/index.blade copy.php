<x-carrier-layout>
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
        <header class="bg-gradient-to-r from-blue-800 to-blue-700 shadow-lg">
            <div class="container mx-auto px-6 py-5 flex items-center">
                <div class="text-white font-bold text-2xl">EF Services</div>
            </div>
        </header>

        <main class="container mx-auto px-4 py-10">
            <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-xl p-8 border border-gray-100">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Document Center</h2>
                    <div class="text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                        Carrier: <span class="font-semibold">{{ $carrier->name }}</span>
                    </div>
                </div>
                
                <div class="bg-blue-50 border-l-4 border-blue-500 p-5 mb-8 rounded-r-lg flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-blue-800">
                        Don't have all documents ready? No problem! You can continue without uploading all documents and complete them later.
                        <span class="font-medium">Required documents are marked with an asterisk (*)</span>.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 mb-8">
                    @foreach($documents as $document)
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <!-- Información del documento -->                            
                            <div class="flex-grow p-5 border-b md:border-b-0 md:border-r border-gray-100">
                                <div class="flex items-start">
                                    <!-- Icono basado en el estado del documento -->
                                    <div class="mr-4 p-3 rounded-full {{ $document['status_name'] === 'Not Uploaded' ? 'bg-yellow-50 text-yellow-500' : 'bg-green-50 text-green-500' }}">
                                        @if($document['status_name'] === 'Not Uploaded')
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                            {{ $document['type']->name }}
                                            @if($document['type']->requirement)
                                                <span class="ml-1 text-red-500 font-bold">*</span>
                                            @endif
                                        </h3>
                                        <div class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document['status_name'] === 'Not Uploaded' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $document['status_name'] }}
                                        </div>
                                        <p class="mt-2 text-sm text-gray-600">
                                            {{ $document['type']->description ?? 'Please upload this document to complete your registration.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Acciones del documento -->
                            <div class="p-5 bg-gray-50 flex flex-col justify-center space-y-3 md:w-64">
                                <!-- Documento subido -->
                                @if($document['file_url'])
                                    <a href="{{ $document['file_url'] }}" target="_blank" 
                                        class="w-full px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View Document
                                    </a>
                                    
                                    <button onclick="openUploadModal('{{ $document['type']->id }}')"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Replace Document
                                    </button>
                                
                                <!-- Documento por defecto -->
                                @elseif($document['type']->getFirstMediaUrl('default_documents'))
                                    <a href="{{ $document['type']->getFirstMediaUrl('default_documents') }}" target="_blank" 
                                        class="w-full px-4 py-2 bg-white border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        View Template
                                    </a>
                                    
                                    <div class="flex items-center justify-between p-2 bg-white border border-gray-200 rounded-lg">
                                        <label for="use_default_{{ $document['type']->id }}" class="text-sm text-gray-700 cursor-pointer">Use template document</label>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                            <input 
                                                type="checkbox" 
                                                id="use_default_{{ $document['type']->id }}" 
                                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                                onchange="handleDefaultDocument(this, '{{ $document['type']->id }}')" 
                                                {{ $document['document'] && $document['document']->use_default ? 'checked' : '' }}
                                            >
                                            <label for="use_default_{{ $document['type']->id }}" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                        </div>
                                    </div>
                                    
                                    <button onclick="openUploadModal('{{ $document['type']->id }}')"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Upload Custom
                                    </button>
                                
                                <!-- Sin documento -->
                                @else
                                    <button onclick="openUploadModal('{{ $document['type']->id }}')"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Upload Document
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-10 border-t border-gray-200 pt-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="text-gray-600 text-sm">
                            <p class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                You can complete your registration now and upload documents later
                            </p>
                        </div>
                        
                        <div class="flex space-x-4">
                            <form action="{{ route('carrier.documents.skip', $carrier->slug) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                    Skip For Now
                                </button>
                            </form>
                            
                            <form action="{{ route('carrier.documents.complete', $carrier->slug) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
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
        <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
            <div id="modalContent" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Upload Document
                    </h3>
                    <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="uploadForm" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div class="border-2 border-dashed border-blue-300 bg-blue-50 rounded-xl p-8 text-center cursor-pointer hover:bg-blue-100 transition-colors duration-200" id="dropZone">
                        <svg class="mx-auto h-16 w-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="mt-4 text-sm font-medium text-blue-600" id="fileStatusText">Drag and drop your file here, or click to select a file</p>
                        <p class="mt-2 text-xs text-blue-500">Files will be uploaded immediately when selected</p>
                        <input type="file" name="document" class="hidden" accept=".pdf,.jpg,.png" id="fileInput">
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
        // Función para abrir el modal de carga
        function openUploadModal(documentTypeId) {
            const modal = document.getElementById('uploadModal');
            const modalContent = document.getElementById('modalContent');
            const form = document.getElementById('uploadForm');
            
            // Configurar la acción del formulario
            form.action = '/carrier/{{ $carrier->slug }}/documents/upload/' + documentTypeId;
            
            // Mostrar el modal y aplicar flex
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'opacity-100');
            
            // Animar la entrada del contenido
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 50);
            
            // Resetear el estado del formulario
            const fileStatusText = document.getElementById('fileStatusText');
            fileStatusText.textContent = 'Drag and drop your file here, or click to select a file';
            fileStatusText.classList.remove('text-green-600');
            fileStatusText.classList.add('text-blue-600');
        }

        function closeUploadModal() {
            const modal = document.getElementById('uploadModal');
            const modalContent = document.getElementById('modalContent');
            
            // Animar el cierre
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            // Pequeño retraso antes de ocultar completamente
            setTimeout(() => {
                modal.classList.remove('flex', 'opacity-100');
                modal.classList.add('hidden');
            }, 200);
        }

        // Función para manejar el checkbox de documentos por defecto
        function handleDefaultDocument(checkbox, documentTypeId) {
            // Mostrar indicador de carga
            const originalLabel = checkbox.parentElement.previousElementSibling.textContent;
            checkbox.parentElement.previousElementSibling.textContent = 'Updating...';
            
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
                checkbox.parentElement.previousElementSibling.textContent = originalLabel;
            });
        }

        // Mejorar la interacción de arrastrar y soltar
        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const fileStatusText = document.getElementById('fileStatusText');
            const uploadButton = document.getElementById('uploadButton');

            // Hacer clic en la zona de drop abre el selector de archivos
            dropZone.addEventListener('click', () => fileInput.click());

            // Manejar el drag & drop
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-blue-500', 'bg-blue-100');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('border-blue-500', 'bg-blue-100');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-100');
                
                const files = e.dataTransfer.files;
                if (files.length) {
                    fileInput.files = files;
                    updateFileStatus(files[0].name);
                }
            });

            // Actualizar el estado cuando se selecciona un archivo
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    updateFileStatus(e.target.files[0].name);
                }
            });

            function updateFileStatus(name) {
                fileStatusText.textContent = `Selected: ${name}`;
                fileStatusText.classList.remove('text-blue-600');
                fileStatusText.classList.add('text-green-600');
                uploadButton.focus();
            }
            
            // Corregir el problema de CSS con las clases flex y hidden
            const modal = document.getElementById('uploadModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            
            // Añadir clase flex solo cuando se abre el modal
            const originalOpenModal = window.openUploadModal;
            window.openUploadModal = function(documentTypeId) {
                modal.classList.add('flex');
                originalOpenModal(documentTypeId);
            };
        });
    </script>
    @endpush
</x-carrier-layout>