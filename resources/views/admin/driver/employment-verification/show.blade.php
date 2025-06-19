@extends('../themes/' . $activeTheme)
@section('title', 'All Drivers Overview')

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Detalles de Verificación de Empleo</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.driver.employment-verification.index') }}" class="btn btn-secondary shadow-md mr-2">
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
                    <a href="{{ route('admin.driver.show', $employmentCompany->userDriverDetail->id) }}" class="btn btn-outline-primary w-full">Ver perfil completo</a>
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
                    
                    @if($employmentCompany->verification_notes)
                    <div class="mb-3">
                        <div class="font-medium">Notas de verificación:</div>
                        <div>{{ $employmentCompany->verification_notes }}</div>
                    </div>
                    @endif
                    
                    @if($employmentCompany->hasMedia('signature'))
                    <div class="mb-3">
                        <div class="font-medium mb-2">Firma:</div>
                        <img src="{{ $employmentCompany->getFirstMediaUrl('signature') }}" alt="Firma" class="border p-2 max-w-full h-auto">
                    </div>
                    @endif
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
                                            <td>{{ $token->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $token->expires_at->format('d/m/Y H:i') }}</td>
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
                        <form action="{{ route('admin.driver.employment-verification.resend', $employmentCompany->id) }}" method="POST">
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
<div id="mark-verified-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Marcar como verificado</h2>
            </div>
            <form action="{{ route('admin.driver.employment-verification.mark-verified', $employmentCompany->id) }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas adicionales</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Ingrese notas adicionales sobre esta verificación..."></textarea>
                    </div>
                    <div class="text-slate-500 mt-2">
                        <i data-lucide="info" class="w-4 h-4 mr-1 inline"></i>
                        Esta acción marcará manualmente el historial de empleo como verificado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancelar</button>
                    <button type="submit" class="btn btn-success w-20">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para marcar como rechazado -->
<div id="mark-rejected-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Marcar como rechazado</h2>
            </div>
            <form action="{{ route('admin.driver.employment-verification.mark-rejected', $employmentCompany->id) }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Motivo del rechazo</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Ingrese el motivo del rechazo..." required></textarea>
                    </div>
                    <div class="text-slate-500 mt-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 mr-1 inline"></i>
                        Esta acción marcará manualmente el historial de empleo como rechazado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancelar</button>
                    <button type="submit" class="btn btn-danger w-20">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Lucide.createIcons();
    });
</script>
@endpush
