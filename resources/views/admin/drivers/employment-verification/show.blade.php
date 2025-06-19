@extends('../themes/' . $activeTheme)
@section('title', 'All Drivers Overview')

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Detalles de Verificación de Empleo</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.drivers.employment-verification.index') }}" class="btn btn-secondary shadow-md mr-2">
            <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Volver a Verificaciones
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
                <div class="font-medium text-base truncate">Información del Conductor</div>
            </div>
            <div class="flex flex-col">
                <div class="flex items-center mb-3">
                    <div class="font-medium">Nombre:</div>
                    <div class="ml-auto">{{ $employmentCompany->userDriverDetail->user->name }} {{ $employmentCompany->userDriverDetail->last_name }}</div>
                </div>
                <div class="flex items-center mb-3">
                    <div class="font-medium">Email:</div>
                    <div class="ml-auto">{{ $employmentCompany->userDriverDetail->user->email }}</div>
                </div>
                <div class="flex items-center mb-3">
                    <div class="font-medium">Teléfono:</div>
                    <div class="ml-auto">{{ $employmentCompany->userDriverDetail->phone }}</div>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('admin.drivers.show', $employmentCompany->userDriverDetail->id) }}" class="btn btn-outline-primary w-full">Ver perfil completo</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de la empresa -->
    <div class="col-span-12 xl:col-span-8">
        <div class="box p-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <div class="font-medium text-base truncate">Información de la Empresa</div>
            </div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">Nombre de la empresa:</div>
                        <div>{{ $employmentCompany->masterCompany ? $employmentCompany->masterCompany->name : 'Empresa personalizada' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Email de contacto:</div>
                        <div>{{ $employmentCompany->email }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Posición ocupada:</div>
                        <div>{{ $employmentCompany->positions_held }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">Período de empleo:</div>
                        <div>{{ $employmentCompany->employed_from->format('d/m/Y') }} - {{ $employmentCompany->employed_to->format('d/m/Y') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Razón de salida:</div>
                        <div>{{ $employmentCompany->reason_for_leaving }}</div>
                        @if($employmentCompany->reason_for_leaving == 'Otro' && $employmentCompany->other_reason_description)
                            <div class="text-slate-500">{{ $employmentCompany->other_reason_description }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <div class="font-medium">Regulaciones:</div>
                        <div>
                            @if($employmentCompany->subject_to_fmcsr)
                                <span class="text-success"><i data-lucide="check" class="w-4 h-4 inline"></i> Sujeto a FMCSR</span><br>
                            @endif
                            @if($employmentCompany->safety_sensitive_function)
                                <span class="text-success"><i data-lucide="check" class="w-4 h-4 inline"></i> Función sensible a la seguridad</span>
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
                <div class="font-medium text-base truncate">Estado de Verificación</div>
                <div class="ml-auto">
                    @if($employmentCompany->verification_status == 'verified')
                        <span class="bg-success/20 text-success rounded px-2 py-1">Verificado</span>
                    @elseif($employmentCompany->verification_status == 'rejected')
                        <span class="bg-danger/20 text-danger rounded px-2 py-1">Rechazado</span>
                    @else
                        <span class="bg-warning/20 text-warning rounded px-2 py-1">Pendiente</span>
                    @endif
                </div>
            </div>
            
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 xl:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">Email enviado:</div>
                        <div>{{ $employmentCompany->email_sent ? 'Sí' : 'No' }}</div>
                    </div>
                    
                    @if($employmentCompany->verification_date)
                    <div class="mb-3">
                        <div class="font-medium">Fecha de verificación:</div>
                        <div>{{ $employmentCompany->verification_date->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                    
                    @if($employmentCompany->verification_by)
                    <div class="mb-3">
                        <div class="font-medium">Verificado por:</div>
                        <div>{{ $employmentCompany->verification_by }}</div>
                    </div>
                    @endif
                    
                    @if($employmentCompany->verification_notes)
                    <div class="mb-3">
                        <div class="font-medium">Notas de verificación:</div>
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
                            <div class="font-medium mb-2">Firma:</div>
                            <div class="border rounded-lg p-3 bg-gray-50">
                                <img src="{{ $signaturePath }}" alt="Firma" class="max-w-full h-auto">
                            </div>
                        </div>
                        @endif
                        
                        @if($pdfPath)
                        <div>
                            <div class="font-medium mb-2">Documento PDF:</div>
                            <div class="flex flex-col space-y-2">
                                <a href="{{ $pdfPath }}" target="_blank" class="btn btn-outline-primary flex items-center">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Ver PDF
                                </a>
                                <a href="{{ $pdfPath }}" download class="btn btn-outline-secondary flex items-center">
                                    <i data-lucide="download" class="w-4 h-4 mr-2"></i> Descargar
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="col-span-12 xl:col-span-6">
                    <div class="mb-3">
                        <div class="font-medium">Historial de tokens:</div>
                        <div class="overflow-auto max-h-40">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha de creación</th>
                                        <th>Expiración</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employmentCompany->verificationTokens as $token)
                                        <tr>
                                            <td>{{ $token->created_at->format('m/d/Y H:i') }}</td>
                                            <td>{{ $token->expires_at->format('m/d/Y H:i') }}</td>
                                            <td>
                                                @if($token->verified_at)
                                                    <span class="text-success">Verificado</span>
                                                @elseif($token->expires_at < now())
                                                    <span class="text-danger">Expirado</span>
                                                @else
                                                    <span class="text-warning">Pendiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No hay tokens registrados</td>
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
                                <i data-lucide="mail" class="w-4 h-4 mr-2"></i> Reenviar correo de verificación
                            </button>
                        </form>
                        
                        @if(!$employmentCompany->verification_status)
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="btn btn-success" data-tw-toggle="modal" data-tw-target="#mark-verified-modal">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Marcar como verificado
                                </button>
                                <button type="button" class="btn btn-danger" data-tw-toggle="modal" data-tw-target="#mark-rejected-modal">
                                    <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Marcar como rechazado
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
    });
</script>
@endpush
