@extends('../themes/' . $activeTheme)
@section('title', 'Traffic Conviction Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Traffic Convictions', 'url' => route('admin.traffic.index')],
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
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Documents for Traffic Conviction: {{ $conviction->charge }}
                ({{ $conviction->conviction_date->format('M d, Y') }})
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">

                <x-base.button as="a" href="{{ route('admin.traffic.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                    Back to Traffic Convictions
                </x-base.button>
                <x-base.button data-tw-toggle="modal" data-tw-target="#add-document-modal" class="w-full sm:w-auto"
                    variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                    Add Document
                </x-base.button>
            </div>
        </div>

        <!-- Información de la Infracción de Tráfico -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Traffic Conviction Details</h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <span class="text-gray-500 text-sm">Driver:</span>
                        <p class="font-medium">{{ $conviction->userDriverDetail->user->name }}
                            {{ $conviction->userDriverDetail->user->last_name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Date:</span>
                        <p class="font-medium">{{ $conviction->conviction_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Location:</span>
                        <p class="font-medium">{{ $conviction->location }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Charge:</span>
                        <p class="font-medium">{{ $conviction->charge }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Penalty:</span>
                        <p class="font-medium">{{ $conviction->penalty }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Documentos -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Documents</h3>
            </div>
            <div class="box-body p-5">
                @if (count($mediaItems) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($mediaItems as $media)
                            <div class="border border-gray-200 rounded-md p-4 hover:shadow-md transition-shadow bg-white">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        @if (Str::contains($media->mime_type, 'image'))
                                            <div
                                                class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <img src="{{ $media->getUrl() }}" alt="{{ $media->file_name }}"
                                                    class="h-14 w-14 object-cover rounded">
                                            </div>
                                        @elseif(Str::contains($media->mime_type, 'pdf'))
                                            <div
                                                class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                            </div>
                                        @elseif(Str::contains($media->mime_type, 'word') || Str::contains($media->mime_type, 'doc'))
                                            <div
                                                class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-word text-blue-500 text-xl"></i>
                                            </div>
                                        @else
                                            <div
                                                class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium truncate" title="{{ $media->file_name }}">
                                            {{ $media->file_name ?? 'Unnamed Document' }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ round($media->size / 1024, 2) }} KB</p>
                                        <p class="text-xs text-gray-500">{{ $media->created_at->format('M d, Y H:i') }}
                                        </p>
                                        @if ($media->custom_properties)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <span class="font-semibold">Source:</span>
                                                {{ isset($media->custom_properties['source']) ? ucfirst($media->custom_properties['source']) : 'Admin' }}
                                            </p>
                                        @endif
                                        <div class="flex mt-2">
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="text-xs text-blue-600 hover:text-blue-800 mr-3 flex items-center">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            <a href="{{ $media->getUrl() }}" download
                                                class="text-xs text-green-600 hover:text-green-800 mr-3 flex items-center">
                                                <i class="fas fa-download mr-1"></i> Download
                                            </a>
                                            <a href="{{ route('admin.traffic.documents.delete', $media->id) }}"
                                                onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this document?')) document.getElementById('delete-form-{{ $media->id }}').submit();"
                                                class="text-xs text-red-600 hover:text-red-800 flex items-center">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </a>
                                            <form id="delete-form-{{ $media->id }}"
                                                action="{{ route('admin.traffic.documents.delete', $media->id) }}"
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if (count($legacyDocuments) > 0)
                        <div class="mt-8">
                            <h4 class="text-base font-medium mb-3">Legacy Documents</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($legacyDocuments as $document)
                                    <div class="border border-gray-200 rounded-md p-4 hover:shadow-md transition-shadow bg-white">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                @if (Str::contains($document->mime_type, 'image'))
                                                    <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                        <img src="{{ $document->getUrl() }}" alt="{{ $document->file_name }}" class="h-14 w-14 object-cover rounded">
                                                    </div>
                                                @elseif(Str::contains($document->mime_type, 'pdf'))
                                                    <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                        <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                                    </div>
                                                @else
                                                    <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                        <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium truncate" title="{{ $document->file_name }}">
                                                    {{ $document->file_name ?? 'Unnamed Document' }}
                                                    <span class="text-xs text-amber-600">(Legacy)</span>
                                                </p>
                                                <p class="text-xs text-gray-500">{{ round($document->size / 1024, 2) }} KB</p>
                                                <p class="text-xs text-gray-500">{{ $document->created_at->format('M d, Y H:i') }}</p>
                                                <div class="flex mt-2">
                                                    <a href="{{ $document->getUrl() }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 mr-3 flex items-center">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                    <a href="{{ $document->getUrl() }}" download class="text-xs text-green-600 hover:text-green-800 mr-3 flex items-center">
                                                        <i class="fas fa-download mr-1"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @elseif (count($legacyDocuments) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($legacyDocuments as $document)
                            <div class="border border-gray-200 rounded-md p-4 hover:shadow-md transition-shadow bg-white">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        @if (Str::contains($document->mime_type, 'image'))
                                            <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <img src="{{ $document->getUrl() }}" alt="{{ $document->file_name }}" class="h-14 w-14 object-cover rounded">
                                            </div>
                                        @elseif(Str::contains($document->mime_type, 'pdf'))
                                            <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                            </div>
                                        @else
                                            <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium truncate" title="{{ $document->file_name }}">
                                            {{ $document->file_name ?? 'Unnamed Document' }}
                                            <span class="text-xs text-amber-600">(Legacy)</span>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ round($document->size / 1024, 2) }} KB</p>
                                        <p class="text-xs text-gray-500">{{ $document->created_at->format('M d, Y H:i') }}</p>
                                        <div class="flex mt-2">
                                            <a href="{{ $document->getUrl() }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 mr-3 flex items-center">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            <a href="{{ $document->getUrl() }}" download class="text-xs text-green-600 hover:text-green-800 mr-3 flex items-center">
                                                <i class="fas fa-download mr-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8">
                        <x-base.lucide class="w-16 h-16 text-slate-300" icon="file-question" />
                        <p class="mt-2 text-slate-500">No documents found for this traffic conviction</p>
                        <button data-tw-toggle="modal" data-tw-target="#add-document-modal"
                            class="btn btn-outline-primary mt-4 flex align-center">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                            Add First Document
                        </button>
                    </div>
                @endif

                @if (isset($debugInfo))
                    <div class="mt-6 bg-gray-100 p-4 rounded-md">
                        <h3 class="text-lg font-semibold mb-2">Información de depuración</h3>
                        <ul class="space-y-1 text-sm">
                            <li><strong>ID de infracción:</strong> {{ $debugInfo['conviction_id'] }}</li>
                            <li><strong>ID de conductor:</strong> {{ $debugInfo['user_driver_detail_id'] }}</li>
                            <li><strong>Documentos en Media Library (traffic_images):</strong> {{ $debugInfo['media_items_count'] }}</li>
                            <li><strong>Documentos legacy (DocumentAttachment):</strong> {{ $debugInfo['legacy_documents_count'] }}</li>
                        </ul>

                        @if(count($debugInfo['media_items']) > 0)
                            <div class="mt-3">
                                <p class="text-sm font-medium">Detalles de Media Items:</p>
                                <div class="mt-1 text-xs text-gray-600 max-h-40 overflow-y-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr>
                                                <th class="p-1">ID</th>
                                                <th class="p-1">Nombre</th>
                                                <th class="p-1">Colección</th>
                                                <th class="p-1">Tipo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($debugInfo['media_items'] as $item)
                                                <tr>
                                                    <td class="p-1">{{ $item['id'] }}</td>
                                                    <td class="p-1 truncate max-w-[150px]" title="{{ $item['file_name'] }}">{{ $item['file_name'] }}</td>
                                                    <td class="p-1">{{ $item['collection_name'] }}</td>
                                                    <td class="p-1">{{ $item['mime_type'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="mt-3">
                            <p class="text-xs text-gray-500">Ruta esperada de documentos Media Library:
                                storage/app/public/{{ $conviction->id }}/traffic_images/
                            </p>
                        </div>
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

            <form action="{{ route('admin.traffic.update', $conviction->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_driver_detail_id" value="{{ $conviction->user_driver_detail_id }}">
                <input type="hidden" name="conviction_date"
                    value="{{ $conviction->conviction_date->format('Y-m-d') }}">
                <input type="hidden" name="location" value="{{ $conviction->location }}">
                <input type="hidden" name="charge" value="{{ $conviction->charge }}">
                <input type="hidden" name="penalty" value="{{ $conviction->penalty }}">
                <input type="hidden" name="collection" value="traffic_convictions">

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
@endsection
