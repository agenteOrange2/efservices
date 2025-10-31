<x-carrier-layout>
    @push('styles')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

            body {
                font-family: 'Inter', sans-serif;
            }

            /* Document Center Styles */
            .document-center {
                --primary-color: #2563eb;
                --primary-hover: #1d4ed8;
                --success-color: #059669;
                --warning-color: #d97706;
                --error-color: #dc2626;
                --gray-50: #f8fafc;
                --gray-100: #f1f5f9;
                --gray-200: #e2e8f0;
                --gray-300: #cbd5e1;
                --gray-400: #94a3b8;
                --gray-500: #64748b;
                --gray-600: #475569;
                --gray-700: #334155;
                --gray-800: #1e293b;
                --gray-900: #0f172a;
            }

            /* Document Card Styles */
            .document-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid var(--gray-200);
            }

            .document-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-color: var(--gray-300);
            }

            .document-card.selected {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 1px var(--primary-color);
            }

            /* Upload Zone Styles */
            .upload-zone {
                transition: all 0.2s ease;
                border: 2px dashed var(--gray-300);
                background-color: var(--gray-50);
            }

            .upload-zone:hover,
            .upload-zone.drag-over {
                border-color: var(--primary-color);
                background-color: #dbeafe;
            }

            .upload-zone.uploading {
                border-color: var(--warning-color);
                background-color: #fef3c7;
            }

            .upload-zone.success {
                border-color: var(--success-color);
                background-color: #d1fae5;
            }

            .upload-zone.error {
                border-color: var(--error-color);
                background-color: #fee2e2;
            }

            /* Status Indicators */
            .status-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }

            .status-badge.not-uploaded {
                background-color: var(--gray-100);
                color: var(--gray-600);
            }

            .status-badge.pending {
                background-color: #fef3c7;
                color: #92400e;
            }

            .status-badge.approved {
                background-color: #d1fae5;
                color: #065f46;
            }

            .status-badge.rejected {
                background-color: #fee2e2;
                color: #991b1b;
            }

            /* Progress Indicators */
            .progress-bar {
                height: 4px;
                background-color: var(--gray-200);
                border-radius: 9999px;
                overflow: hidden;
            }

            .progress-fill {
                height: 100%;
                background-color: var(--primary-color);
                border-radius: 9999px;
                transition: width 0.3s ease;
            }

            /* Responsive Grid */
            .document-grid {
                display: grid;
                gap: 1.5rem;
                grid-template-columns: 1fr;
            }

            @media (min-width: 768px) {
                .document-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (min-width: 1024px) {
                .document-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            .document-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            /* Animation Classes */
            .fade-in {
                animation: fadeIn 0.3s ease-in-out;
            }

            .slide-up {
                animation: slideUp 0.3s ease-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes slideUp {
                from { 
                    opacity: 0;
                    transform: translateY(10px);
                }
                to { 
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Loading States */
            .skeleton {
                background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-100) 50%, var(--gray-200) 75%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
            }

            @keyframes loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            /* Custom Scrollbar */
            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: var(--gray-100);
                border-radius: 3px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: var(--gray-400);
                border-radius: 3px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: var(--gray-500);
            }
        </style>
    @endpush

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 document-center">

        <!-- Document Center Component -->
        <x-carrier.document-center 
            :carrier="$carrier"
            :documents="$documents"
            :progress="[
                'overall' => $overallProgress ?? 0,
                'required' => $requiredProgress ?? 0,
                'optional' => $optionalProgress ?? 0,
                'approved' => $approvedCount ?? 0,
                'pending' => $pendingCount ?? 0,
                'rejected' => $rejectedCount ?? 0,
                'not_uploaded' => $notUploadedCount ?? 0
            ]"
            :filters="[
                'search' => request('search', ''),
                'status' => request('status', ''),
                'type' => request('type', ''),
                'sort' => request('sort', 'name'),
                'view' => request('view', 'grid')
            ]"
            :bulk-actions="true"
        />

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
