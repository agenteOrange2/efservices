@extends('../themes/' . $activeTheme)
@section('title', 'Employment Verification Details')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Employment Verifications', 'url' => route('admin.drivers.employment-verification.index')],
        ['label' => 'Employment Verification Details', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Employment Verification Details</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <x-base.button as="a" variation="primary" href="{{ route('admin.drivers.employment-verification.index') }}" class="btn btn-secondary shadow-md mr-2">
            <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Back to Verifications
        </x-base.button>
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

    <div class="col-span-12">
        <div class="box box--stacked flex flex-col">
            <!-- Card Header -->
            <div class="bg-gradient-to-r  p-6 text-gray-600 border-b border-slate-200/60 dark:border-darkmode-400">
                <div class="flex items-center justify-between">
                    <div class="flex items-center ">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                            <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Verification Status</h3>
                            <p class="text-gray-600 text-sm">Current verification state</p>
                        </div>
                    </div>
                    <div class="ml-auto">
                        @if($employmentCompany->verification_status == 'verified')
                            <div class="flex items-center bg-success text-white rounded-sm px-4 py-2  ">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                <span class="font-medium">Verified</span>
                            </div>
                        @elseif($employmentCompany->verification_status == 'rejected')
                            <div class="flex items-center bg-danger text-white rounded-sm px-4 py-2">
                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>
                                <span class="font-medium">Rejected</span>
                            </div>
                        @else
                            <div class="flex items-center bg-warning text-white rounded-sm px-4 py-2">
                                <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                                <span class="font-medium">Pending</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Card Content -->
            <div class="p-6">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 xl:col-span-6">
                        <!-- Verification Details -->
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-4">
                                    <i data-lucide="mail" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase font-medium tracking-wide">Email Sent</div>
                                    <div class="font-semibold text-slate-800 dark:text-slate-200 mt-1">{{ $employmentCompany->email_sent ? 'Yes' : 'No' }}</div>
                                </div>
                            </div>
                            
                            @if($employmentCompany->verification_date)
                            <div class="flex items-center p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900 rounded-full flex items-center justify-center mr-4">
                                    <i data-lucide="calendar" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase font-medium tracking-wide">Verification Date</div>
                                    <div class="font-semibold text-slate-800 dark:text-slate-200 mt-1">{{ $employmentCompany->verification_date->format('m/d/Y H:i') }}</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($employmentCompany->verification_by)
                            <div class="flex items-center p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-4">
                                    <i data-lucide="user-check" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase font-medium tracking-wide">Verified By</div>
                                    <div class="font-semibold text-slate-800 dark:text-slate-200 mt-1">{{ $employmentCompany->verification_by }}</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($employmentCompany->verification_notes)
                            <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl">
                                <div class="flex items-center mb-3">
                                    <i data-lucide="file-text" class="w-5 h-5 text-orange-500 mr-2"></i>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 uppercase font-medium tracking-wide">Verification Notes</span>
                                </div>
                                <div class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $employmentCompany->verification_notes }}</div>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Documents Section -->
                        <div class="space-y-4">
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
                            
                            @if($signaturePath || $pdfPath)
                            <div class="bg-blue-50 dark:bg-blue-900 dark:bg-opacity-30 p-6 rounded-xl border border-blue-200 dark:border-blue-800">
                                <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center">
                                    <i data-lucide="file-check" class="w-5 h-5 mr-2"></i>
                                    Verification Documents
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @if($signaturePath)
                                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex items-center mb-3">
                                            <i data-lucide="pen-tool" class="w-4 h-4 text-purple-500 mr-2"></i>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">Digital Signature</span>
                                        </div>
                                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                                            <img src="{{ $signaturePath }}" alt="Signature" class="max-w-full h-auto mx-auto">
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($pdfPath)
                                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex items-center mb-3">
                                            <i data-lucide="file-text" class="w-4 h-4 text-red-500 mr-2"></i>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">PDF Document</span>
                                        </div>
                                        <div class="space-y-2">
                                            <a href="{{ $pdfPath }}" target="_blank" class="flex items-center w-full hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">
                                                <i data-lucide="eye" class="w-4 h-4 mr-2"></i> View PDF
                                            </a>
                                            <a href="{{ $pdfPath }}" download class="flex items-center w-full hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">
                                                <i data-lucide="download" class="w-4 h-4 mr-2"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- Manual Document Upload -->
                            <div class="bg-emerald-50 dark:bg-emerald-900 dark:bg-opacity-30 p-6 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                <h4 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-4 flex items-center">
                                    <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                                    Upload Additional Documents
                                </h4>
                                
                                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm">
                                    <div class="flex items-start p-3 bg-blue-50 dark:bg-blue-900 dark:bg-opacity-50 rounded-lg mb-4">
                                        <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0"></i>
                                        <p class="text-sm text-blue-700 dark:text-blue-300">
                                            If you already have the verification document, you can upload it directly here.
                                        </p>
                                    </div>
                                    
                                    <!-- Botón para subir documento manualmente -->
                                    <x-base.button type="button" class="w-full text-center text-white" variant="primary" data-tw-toggle="modal" data-tw-target="#upload-manual-document-modal">
                                        <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Upload Manual Verification Document
                                    </x-base.button>
                                    
                                    <!-- Documentos subidos -->
                                    @if($employmentCompany->getMedia('employment_verification_documents')->count() > 0)
                                        <div class="mt-5">
                                            <h5 class="text-base font-medium mb-2 text-slate-700 dark:text-slate-300">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-1 inline-block"></i>
                                                Documentos de verificación
                                            </h5>
                                            <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                                <ul class="divide-y divide-slate-200 dark:divide-slate-600">
                                                    @foreach($employmentCompany->getMedia('employment_verification_documents') as $media)
                                                        <li class="py-2 flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <div>
                                                                    @if(in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/jpg']))
                                                                        <i data-lucide="image" class="w-8 h-8 text-blue-500"></i>
                                                                    @elseif($media->mime_type === 'application/pdf')
                                                                        <i data-lucide="file-text" class="w-8 h-8 text-red-500"></i>
                                                                    @else
                                                                        <i data-lucide="file" class="w-8 h-8 text-slate-500"></i>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <p class="text-sm font-medium">
                                                                        {{ $media->custom_properties['original_name'] ?? $media->file_name }}
                                                                    </p>
                                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                                        Subido: {{ \Carbon\Carbon::parse($media->custom_properties['uploaded_at'] ?? $media->created_at)->format('d/m/Y H:i') }}
                                                                        @if(isset($media->custom_properties['uploaded_by']))
                                                                            por {{ $media->custom_properties['uploaded_by'] }}
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                                                    Ver
                                                                </a>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-span-12 xl:col-span-6">
                        <!-- Token History -->
                        <div class="bg-slate-50 dark:bg-slate-800 p-6 rounded-xl mb-6 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                            <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4 flex items-center">
                                <i data-lucide="key" class="w-5 h-5 mr-2 text-amber-500"></i>
                                Token History
                            </h4>
                            
                            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm overflow-hidden">
                                <div class="overflow-auto max-h-80">
                                    <table class="table table-sm w-full">
                                        <thead class="bg-slate-100 dark:bg-slate-800">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wider">Creation Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wider">Expiration</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                            @forelse($employmentCompany->verificationTokens as $token)
                                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $token->created_at->format('m/d/Y H:i') }}</td>
                                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $token->expires_at->format('m/d/Y H:i') }}</td>
                                                    <td class="px-4 py-3">
                                                        @if($token->verified_at)
                                                            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-success text-white dark:bg-emerald-900 dark:green-200">
                                                                <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                                                Verified
                                                            </span>
                                                        @elseif($token->expires_at < now())
                                                            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                                <i data-lucide="x" class="w-3 h-3 mr-1"></i>
                                                                Expired
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                                                                Pending
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                                        <div class="flex flex-col items-center">
                                                            <i data-lucide="inbox" class="w-8 h-8 mb-2 text-slate-400"></i>
                                                            <span>No tokens registered</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-4">
                            <div class="bg-blue-50 dark:bg-blue-900 dark:bg-opacity-30 p-6 rounded-xl border border-blue-200 dark:border-blue-800">
                                <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center">
                                    <i data-lucide="settings" class="w-5 h-5 mr-2"></i>
                                    Actions
                                </h4>
                                
                                <div class="space-y-3">
                                    <form action="{{ route('admin.drivers.employment-verification.resend', $employmentCompany->id) }}" method="POST">
                                        @csrf                                        
                                        <x-base.button  type="submit" class="w-full text-center text-white" variant="warning">
                                            <i data-lucide="mail" class="w-4 h-4 mr-2"></i> Resend Verification Email
                                        </x-base.button>
                                    </form>                                                                        
                                    
                                    @if(!$employmentCompany->verification_status)
                                        <div class="grid md:grid-cols-2 grid-cols-1 gap-3">
                                            <x-base.button type="button" class="w-full text-center text-white" variant="success" data-tw-toggle="modal" data-tw-target="#mark-verified-modal">
                                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Mark as Verified
                                            </x-base.button>
                                            <x-base.button type="button" class="w-full text-center" variant="danger" data-tw-toggle="modal" data-tw-target="#mark-rejected-modal">
                                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Mark as Rejected
                                            </x-base.button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
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

<!-- Modal para subir documento manualmente -->
<x-base.dialog id="upload-manual-document-modal" size="md">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="font-medium text-base mr-auto">Subir documento de verificación manual</h2>
        </x-base.dialog.title>
        <form action="{{ route('admin.drivers.employment-verification.upload-manual-verification', $employmentCompany->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-base.dialog.description>
                <div class="mb-3">
                    <x-base.form-label for="verification_document" required>Documento de verificación</x-base.form-label>
                    <x-base.form-input id="verification_document" name="verification_document" type="file" accept=".pdf,.jpg,.jpeg,.png" required></x-base.form-input>
                    @error('verification_document')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    <div class="text-slate-500 text-xs mt-1">
                        Formatos permitidos: PDF, JPG, JPEG, PNG. Tamaño máximo: 10MB
                    </div>
                </div>
                
                <div class="mb-3">
                    <x-base.form-label for="verification_date" required>Fecha de verificación</x-base.form-label>
                    <x-base.form-input id="verification_date" name="verification_date" type="date" value="{{ date('Y-m-d') }}" required></x-base.form-input>
                    @error('verification_date')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <x-base.form-label for="verification_notes">Notas adicionales</x-base.form-label>
                    <x-base.form-textarea id="verification_notes" name="verification_notes" rows="3" placeholder="Ingrese notas adicionales sobre esta verificación..."></x-base.form-textarea>
                    @error('verification_notes')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="text-slate-500 mt-2 flex items-center">
                    <i data-lucide="info" class="w-4 h-4 mr-1"></i>
                    Este proceso subirá un documento de verificación de empleo digitalizado y marcará la verificación como completada.
                </div>
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                    Cancelar
                </x-base.button>
                <x-base.button type="submit" variant="primary" class="w-24">
                    <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Subir
                </x-base.button>
            </x-base.dialog.footer>
        </form>
    </x-base.dialog.panel>
</x-base.dialog>
@endsection

@push('scripts')
<script>
    // Variables para almacenar los archivos temporales
    let tempFiles = [];
    
    // Función para escuchar eventos de Livewire (tanto v2 como v3)
    function listenToLivewireEvents() {
        // Para Livewire v3
        if (typeof window.Livewire !== 'undefined') {
            console.log('Configurando listener para Livewire v3');
            document.addEventListener('livewire:init', () => {
                Livewire.hook('message.processed', (message, component) => {
                    // Solo procesar si hay dispatches
                    if (message?.response?.effects?.dispatches) {
                        // Revisar cada dispatch
                        message.response.effects.dispatches.forEach(dispatch => {
                            // Detectar evento fileUploaded
                            if (dispatch.event === 'fileUploaded' && dispatch.data && dispatch.data.length > 0) {
                                const fileData = dispatch.data[0];
                                console.log('Archivo detectado:', fileData);
                                if (fileData && fileData.tempPath && fileData.originalName) {
                                    // Guardar archivo
                                    tempFiles.push({
                                        path: fileData.tempPath,
                                        name: fileData.originalName
                                    });
                                    // Actualizar campo oculto inmediatamente
                                    updateHiddenField();
                                    console.log('Archivo agregado a tempFiles:', tempFiles);
                                }
                            }
                            // Detectar evento fileRemoved
                            if (dispatch.event === 'fileRemoved' && dispatch.data && dispatch.data.length > 0) {
                                const fileData = dispatch.data[0];
                                if (fileData && fileData.tempPath) {
                                    // Eliminar archivo
                                    tempFiles = tempFiles.filter(file => file.path !== fileData.tempPath);
                                    // Actualizar campo oculto inmediatamente
                                    updateHiddenField();
                                    console.log('Archivo eliminado, tempFiles actualizado:', tempFiles);
                                }
                            }
                        });
                    }
                });
            });
        } else {
            // Para Livewire v2 (antiguo)
            console.log('Configurando listener para Livewire v2');
            document.addEventListener('livewire:load', function() {
                Livewire.on('fileUploaded', function(data) {
                    console.log('Evento fileUploaded v2 recibido:', data);
                    if (data && data.tempPath && data.originalName) {
                        // Guardar archivo
                        tempFiles.push({
                            path: data.tempPath,
                            name: data.originalName
                        });
                        // Actualizar campo oculto inmediatamente
                        updateHiddenField();
                    }
                });
                Livewire.on('fileRemoved', function(data) {
                    console.log('Evento fileRemoved v2 recibido:', data);
                    if (data && data.tempPath) {
                        // Eliminar archivo
                        tempFiles = tempFiles.filter(file => file.path !== data.tempPath);
                        // Actualizar campo oculto inmediatamente
                        updateHiddenField();
                    }
                });
            });
        }
    }
    
    // Función para actualizar el campo oculto
    function updateHiddenField() {
        const hiddenField = document.getElementById('livewire_files');
        if (hiddenField && tempFiles.length > 0) {
            hiddenField.value = JSON.stringify(tempFiles);
            console.log('Campo oculto actualizado:', hiddenField.value);
        } else if (hiddenField) {
            hiddenField.value = '';
            console.log('Campo oculto limpiado (no hay archivos).');
        } else {
            console.error('No se encontró el campo oculto livewire_files');
        }
    }
    
    // Manejar el envío del formulario para asegurar que el campo oculto está actualizado
    document.addEventListener('DOMContentLoaded', function() {
        // Iniciar escucha de eventos
        listenToLivewireEvents();
        
        // Manejar envío de formulario
        const form = document.getElementById('uploadDocumentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Verificar si hay archivos
                if (tempFiles.length === 0) {
                    e.preventDefault();
                    alert('Por favor, sube al menos un documento antes de guardar.');
                    return false;
                }
                
                // Asegurar que el campo oculto tenga los datos más recientes
                updateHiddenField();
                console.log('Formulario enviado con:', document.getElementById('livewire_files').value);
                return true;
            });
        }
        
        // Función para depuración desde consola
        window.debugFileUpload = function() {
            console.log('Estado actual de tempFiles:', tempFiles);
            console.log('Valor actual del campo oculto:', document.getElementById('livewire_files')?.value || 'no encontrado');
        };
    });
</script>
@endpush
