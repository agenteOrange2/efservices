@extends('admin.layouts.base')

@section('subhead')
    <title>Test Documents</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Test Documents</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.testings.index') }}" class="btn btn-secondary shadow-md mr-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to List
            </a>
            <a href="{{ route('admin.drivers.testing-history', $testing->user_driver_detail_id) }}" class="btn btn-secondary shadow-md mr-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Driver History
            </a>
        </div>
    </div>

    <div class="intro-y box p-5 mt-5">
        <div class="mb-5">
            <h2 class="font-medium text-base mr-auto mb-2">
                Test Documents - {{ $testing->test_type }} - {{ \Carbon\Carbon::parse($testing->test_date)->format('M d, Y') }}
            </h2>
            <div class="text-slate-500">
                <div><strong>Driver:</strong> {{ $testing->userDriverDetail->user->name }}</div>
                <div><strong>Test Result:</strong> {{ $testing->test_result }}</div>
                <div><strong>Administered By:</strong> {{ $testing->administered_by }}</div>
            </div>
        </div>

        <!-- Formulario para subir nuevos documentos -->
        <div class="mt-5">
            <h3 class="font-medium text-base">Upload New Documents</h3>
            <form method="POST" action="{{ route('admin.testings.documents.store', $testing->id) }}" enctype="multipart/form-data" class="mt-3">
                @csrf
                <div class="form-group">
                    <input type="file" name="documents[]" multiple class="form-control-file block w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-violet-50 file:text-violet-700
                        hover:file:bg-violet-100">
                    <div class="text-xs text-slate-400 mt-1">Supported formats: PDF, Images, Word documents (Max: 10MB each)</div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Upload Documents</button>
            </form>
        </div>

        <!-- Lista de documentos existentes -->
        <div class="mt-8">
            <h3 class="font-medium text-base mb-5">Existing Documents</h3>
            
            @if(count($documents) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($documents as $document)
                        <div class="intro-y col-span-1">
                            <div class="box p-4 relative">
                                <!-- Documento -->
                                <div class="flex items-start">
                                    <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                        @if(str_starts_with($document->mime_type, 'image/'))
                                            <img src="{{ $document->getUrl() }}" alt="{{ $document->name }}">
                                        @elseif(str_contains($document->mime_type, 'pdf'))
                                            <div class="flex items-center justify-center w-full h-full bg-red-100">
                                                <i data-lucide="file-text" class="w-6 h-6 text-red-500"></i>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center w-full h-full bg-blue-100">
                                                <i data-lucide="file" class="w-6 h-6 text-blue-500"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-3 truncate">
                                        <div class="font-medium truncate w-40">{{ $document->name }}</div>
                                        <div class="text-slate-500 text-xs">{{ \Carbon\Carbon::parse($document->created_at)->format('M d, Y') }}</div>
                                        <div class="text-slate-500 text-xs">{{ round($document->size / 1024, 2) }} KB</div>
                                    </div>
                                </div>
                                
                                <!-- Acciones -->
                                <div class="flex mt-3 border-t pt-3">
                                    <a href="{{ route('admin.testings.documents.preview', $document->id) }}" target="_blank" class="btn btn-sm btn-secondary w-full mr-1">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger w-full ml-1 delete-document-btn" 
                                        data-document-id="{{ $document->id }}"
                                    >
                                        <i data-lucide="trash" class="w-4 h-4 mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center p-5 text-slate-500">No documents found for this test.</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar los botones de eliminar documento
        const deleteButtons = document.querySelectorAll('.delete-document-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const documentId = this.dataset.documentId;
                
                // Confirmar eliminación
                if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
                    // Realizar la solicitud AJAX para eliminar
                    fetch(`{{ route('admin.testings.documents.destroy', '') }}/${documentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Eliminar el elemento del DOM
                            const documentElement = button.closest('.intro-y');
                            documentElement.remove();
                            
                            // Mostrar mensaje de éxito
                            alert('Document deleted successfully');
                            
                            // Si no hay más documentos, mostrar mensaje
                            const remainingDocuments = document.querySelectorAll('.delete-document-btn');
                            if (remainingDocuments.length === 0) {
                                const grid = document.querySelector('.grid');
                                grid.innerHTML = '<div class="text-center p-5 text-slate-500 col-span-full">No documents found for this test.</div>';
                            }
                        } else {
                            alert('Error deleting document: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the document.');
                    });
                }
            });
        });
    });
</script>
@endpush
