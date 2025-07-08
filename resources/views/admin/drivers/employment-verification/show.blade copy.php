@extends('../themes/' . $activeTheme)
@section('title', 'Employment Verification Details')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Employment Verifications', 'active' => true],
        
    ];
@endphp

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Employment Verification Details</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.drivers.employment-verification.index') }}" class="btn btn-secondary shadow-md mr-2">
            <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Back to Verifications
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible show flex items-center mb-2 mt-5" role="alert">
        <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible show flex items-center mb-2 mt-5" role="alert">
        <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

<div class="grid grid-cols-12 gap-5 mt-5">
    <!-- Información del conductor -->
    <div class="col-span-12 xl:col-span-4">
        <div class="box p-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <div class="font-medium text-base truncate">Driver Information</div>
            </div>
            <div class="flex flex-col">
                <div class="flex items-center mb-3">
                    <div class="font-medium">Name:</div>
                    <div class="ml-auto">{{ $employmentCompany->userDriverDetail->user->name }} {{ $employmentCompany->userDriverDetail->last_name }}</div>
                </div>
                <div class="flex items-center mb-3">
                    <div class="font-medium">Email:</div>
                    <div class="ml-auto">{{ $employmentCompany->userDriverDetail->user->email }}</div>
                </div>
                <div class="flex items-center mb-3">
                    <div class="font-medium">Phone:</div>
                    <div class="ml-auto">{{ $employmentCompany->userDriverDetail->phone }}</div>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('admin.drivers.show', $employmentCompany->userDriverDetail->id) }}" class="btn btn-outline-primary w-full">View full profile</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de la empresa -->
    <div class="col-span-12 xl:col-span-8">
        <div class="box p-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <div class="font-medium text-base truncate">Company Information</div>
            </div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">Company name:</div>
                        <div>{{ $employmentCompany->masterCompany ? $employmentCompany->masterCompany->name : 'Empresa personalizada' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Contact email:</div>
                        <div>{{ $employmentCompany->email }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Position held:</div>
                        <div>{{ $employmentCompany->positions_held }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">Employment period:</div>
                        <div>{{ $employmentCompany->employed_from->format('m/d/Y') }} - {{ $employmentCompany->employed_to->format('m/d/Y') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Reason for leaving:</div>
                        <div>{{ $employmentCompany->reason_for_leaving }}</div>
                        @if($employmentCompany->reason_for_leaving == 'Other' && $employmentCompany->other_reason_description)
                            <div class="text-slate-500">{{ $employmentCompany->other_reason_description }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Regulations:</div>
                        <div>
                            @if($employmentCompany->subject_to_fmcsr)
                                <span class="text-success"><i data-lucide="check" class="w-4 h-4 inline"></i> Subject to FMCSR</span><br>
                            @endif
                            @if($employmentCompany->safety_sensitive_function)
                                <span class="text-success"><i data-lucide="check" class="w-4 h-4 inline"></i> Safety sensitive function</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de verificación -->
    <div class="col-span-12">
        <div class="box p-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <div class="font-medium text-base truncate">Verification Status</div>
                <div class="ml-auto">
                    @if($employmentCompany->verification_status == 'verified')
                        <span class="bg-success/20 text-success rounded px-2 py-1">Verified</span>
                    @elseif($employmentCompany->verification_status == 'rejected')
                        <span class="bg-danger/20 text-danger rounded px-2 py-1">Rejected</span>
                    @else
                        <span class="bg-warning/20 text-warning rounded px-2 py-1">Pending</span>
                    @endif
                </div>
            </div>
            
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 xl:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">Email sent:</div>
                        <div>{{ $employmentCompany->email_sent ? 'Yes' : 'No' }}</div>
                    </div>
                    
                    @if($employmentCompany->verification_date)
                    <div class="mb-3">
                        <div class="font-medium">Verification date:</div>
                        <div>{{ $employmentCompany->verification_date->format('m/d/Y H:i') }}</div>
                    </div>
                    @endif
                    
                    @if($employmentCompany->verification_by)
                    <div class="mb-3">
                        <div class="font-medium">Verified by:</div>
                        <div>{{ $employmentCompany->verification_by }}</div>
                    </div>
                    @endif
                    
                    @if($employmentCompany->verification_notes)
                    <div class="mb-3">
                        <div class="font-medium">Verification notes:</div>
                        <div>{{ $employmentCompany->verification_notes }}</div>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        @php
                            // Buscar el token verificado más reciente
                            $verifiedToken = $employmentCompany->verificationTokens
                                ->where('verified_at', '!=', null)
                                ->sortByDesc('verified_at')
                                ->first();
                            
                            $signaturePath = $verifiedToken && $verifiedToken->signature_path ? 
                                asset('storage/' . $verifiedToken->signature_path) : null;
                                
                            $pdfPath = $verifiedToken && $verifiedToken->document_path ? 
                                asset('storage/' . $verifiedToken->document_path) : null;
                        @endphp
                        
                        @if($signaturePath)
                        <div>
                            <div class="font-medium mb-2">Signature:</div>
                            <div class="border rounded-lg p-3 bg-gray-50">
                                <img src="{{ $signaturePath }}" alt="Signature" class="max-w-full h-auto">
                            </div>
                        </div>
                        @endif
                        
                        @if($pdfPath)
                        <div>
                            <div class="font-medium mb-2">PDF Document:</div>
                            <div class="flex flex-col space-y-2">
                                <a href="{{ $pdfPath }}" target="_blank" class="btn btn-outline-primary flex items-center">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> View PDF
                                </a>
                                <a href="{{ $pdfPath }}" download class="btn btn-outline-secondary flex items-center">
                                    <i data-lucide="download" class="w-4 h-4 mr-2"></i> Descargar
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Sección para subir documentos manualmente -->
                        <div class="mt-4">
                            <div class="font-medium mb-2">Upload Additional Documents:</div>
                            <div class="bg-white p-4 rounded-md border border-gray-200">
                                <p class="text-sm text-gray-600 mb-3">If you already have the verification document, you can upload it directly here.</p>
                                @livewire('components.file-uploader', [
                                    'modelName' => 'employment_verification_documents',
                                    'modelIndex' => $employmentCompany->id,
                                    'label' => 'Upload Verification Document',
                                    'existingFiles' => $employmentCompany->getMedia('employment_verification_documents')->map(function($media) {
                                        return [
                                            'id' => $media->id,
                                            'name' => $media->file_name,
                                            'file_name' => $media->file_name,
                                            'mime_type' => $media->mime_type,
                                            'size' => $media->size,
                                            'url' => $media->getUrl(),
                                            'created_at' => $media->created_at->format('Y-m-d H:i:s')
                                        ];
                                    })->toArray()
                                ])
                                
                                <div class="mt-3">
                                    <form action="{{ route('admin.drivers.employment-verification.upload-document', $employmentCompany->id) }}" method="POST" id="uploadDocumentForm">
                                        @csrf
                                        <input type="hidden" name="uploaded_files" id="uploadedFiles" value="">
                                        <button type="submit" class="btn btn-primary w-full">
                                            <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Documents
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-span-12 xl:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">History of tokens:</div>
                        <div class="overflow-auto max-h-40">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Creation date</th>
                                        <th>Expiration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employmentCompany->verificationTokens as $token)
                                        <tr>
                                            <td>{{ $token->created_at->format('m/d/Y H:i') }}</td>
                                            <td>{{ $token->expires_at->format('m/d/Y H:i') }}</td>
                                            <td>
                                                @if($token->verified_at)
                                                    <span class="text-success">Verified</span>
                                                @elseif($token->expires_at < now())
                                                    <span class="text-danger">Expired</span>
                                                @else
                                                    <span class="text-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No tokens registered</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="flex flex-col space-y-2 mt-5">
                        <form action="{{ route('admin.drivers.employment-verification.resend', $employmentCompany->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-full">
                                <i data-lucide="mail" class="w-4 h-4 mr-2"></i> Resend verification email
                            </button>
                        </form>
                        
                        @if(!$employmentCompany->verification_status)
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="btn btn-success" data-tw-toggle="modal" data-tw-target="#mark-verified-modal">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Mark as verified
                                </button>
                                <button type="button" class="btn btn-danger" data-tw-toggle="modal" data-tw-target="#mark-rejected-modal">
                                    <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Mark as rejected
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para marcar como verificado -->
<x-base.dialog id="mark-verified-modal" size="md">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="font-medium text-base mr-auto">Marcar como verificado</h2>
        </x-base.dialog.title>
        <form action="{{ route('admin.drivers.employment-verification.mark-verified', $employmentCompany->id) }}" method="POST">
            @csrf
            <x-base.dialog.description>
                <div class="mb-3">
                    <x-base.form-label for="notes">Notas adicionales</x-base.form-label>
                    <x-base.form-textarea id="notes" name="notes" rows="3" placeholder="Ingrese notas adicionales sobre esta verificación..."></x-base.form-textarea>
                </div>
                <div class="text-slate-500 mt-2 flex items-center">
                    <i data-lucide="info" class="w-4 h-4 mr-1"></i>
                    Esta acción marcará manualmente el historial de empleo como verificado.
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                    Cancelar
                </x-base.button>
                <x-base.button type="submit" variant="success" class="w-20">
                    Confirmar
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>

<!-- Modal para marcar como rechazado -->
<x-base.dialog id="mark-rejected-modal" size="md">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="font-medium text-base mr-auto">Marcar como rechazado</h2>
        </x-base.dialog.title>
        <form action="{{ route('admin.drivers.employment-verification.mark-rejected', $employmentCompany->id) }}" method="POST">
            @csrf
            <x-base.dialog.description>
                <div class="mb-3">
                    <x-base.form-label for="reject-notes">Motivo del rechazo</x-base.form-label>
                    <x-base.form-textarea id="reject-notes" name="notes" rows="3" placeholder="Ingrese el motivo del rechazo..." required></x-base.form-textarea>
                </div>
                <div class="text-slate-500 mt-2 flex items-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4 mr-1"></i>
                    Esta acción marcará manualmente el historial de empleo como rechazado.
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                    Cancelar
                </x-base.button>
                <x-base.button type="submit" variant="danger" class="w-20">
                    Confirmar
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Lucide.createIcons();
        
        // Inicializar el array para almacenar los archivos subidos
        let uploadedFiles = [];
        
        // Escuchar el evento fileUploaded del componente FileUploader
        window.addEventListener('livewire:initialized', function () {
            Livewire.on('fileUploaded', function(event) {
                // Agregar el archivo al array
                uploadedFiles.push({
                    tempPath: event.tempPath,
                    originalName: event.originalName,
                    mimeType: event.mimeType,
                    size: event.size,
                    modelName: event.modelName,
                    modelIndex: event.modelIndex
                });
                
                // Actualizar el campo oculto con el JSON de archivos
                document.getElementById('uploadedFiles').value = JSON.stringify(uploadedFiles);
                
                console.log('File uploaded:', event);
            });
            
            // Escuchar el evento fileRemoved del componente FileUploader
            Livewire.on('fileRemoved', function(event) {
                // Si es un archivo temporal, eliminarlo del array
                if (event.isTemp) {
                    uploadedFiles = uploadedFiles.filter(file => 
                        !(file.tempPath === event.tempPath && 
                          file.originalName === event.originalName)
                    );
                    
                    // Actualizar el campo oculto con el JSON de archivos
                    document.getElementById('uploadedFiles').value = JSON.stringify(uploadedFiles);
                    
                    console.log('Temporary file removed:', event);
                }
            });
        });
    });
</script>
@endpush
