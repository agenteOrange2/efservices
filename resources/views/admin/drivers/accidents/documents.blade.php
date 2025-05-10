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
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Documents for Accident: {{ $accident->nature_of_accident }} ({{ $accident->accident_date->format('M d, Y') }})
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('admin.accidents.index') }}" class="btn btn-outline-secondary mr-2">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to Accidents
                </a>
                <button data-tw-toggle="modal" data-tw-target="#add-document-modal" class="btn btn-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Document
                </button>
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
                        <p class="font-medium">{{ $accident->userDriverDetail->user->name }} {{ $accident->userDriverDetail->user->last_name }}</p>
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
                <h3 class="box-title">Documents</h3>
            </div>
            <div class="box-body p-5">
                @if(count($documents) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($documents as $document)
                            <div class="border border-gray-200 rounded-md p-4 hover:shadow-md transition-shadow bg-white">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        @if(Str::contains($document->mime_type, 'image'))
                                            <a href="{{ $document->getUrl() }}" target="_blank" class="block">
                                                <img src="{{ $document->getUrl() }}" alt="{{ $document->name }}" class="h-16 w-16 object-cover rounded-md border border-gray-200 hover:border-primary transition">
                                            </a>
                                        @elseif(Str::contains($document->mime_type, 'pdf'))
                                            <div class="h-16 w-16 flex items-center justify-center bg-red-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                            </div>
                                        @elseif(Str::contains($document->mime_type, 'word') || Str::contains($document->mime_type, 'doc'))
                                            <div class="h-16 w-16 flex items-center justify-center bg-blue-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-word text-blue-500 text-xl"></i>
                                            </div>
                                        @else
                                            <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium truncate" title="{{ $document->file_name }}">{{ $document->file_name }}</p>
                                        <p class="text-xs text-gray-500">{{ round($document->size / 1024, 2) }} KB · {{ $document->created_at->format('M d, Y') }}</p>
                                        <div class="flex mt-2 space-x-2">
                                            <a href="{{ $document->getUrl() }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            <a href="{{ route('admin.accidents.documents.delete', $document->id) }}" 
                                               onclick="return confirm('Are you sure you want to delete this document?')" 
                                               class="text-xs text-red-600 hover:text-red-800 flex items-center">
                                                <i class="fas fa-trash mr-1"></i> Delete
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
                        <p class="mt-2 text-slate-500">No documents found for this accident</p>
                        <button data-tw-toggle="modal" data-tw-target="#add-document-modal" class="btn btn-outline-primary mt-4">
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

            <form action="{{ route('admin.accidents.update', $accident->id) }}" method="POST" enctype="multipart/form-data">
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
                                <input type="file" name="documents[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="w-full h-full opacity-0 absolute inset-0 cursor-pointer z-50">
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
