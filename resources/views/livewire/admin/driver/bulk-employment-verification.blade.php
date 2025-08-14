<div>
    <div class="box box--stacked mt-5">
        <div class="box-header p-5">
            <div class="intro-y flex flex-col sm:flex-row items-center">
                <h2 class="text-lg font-medium mr-auto">Verificaciones de Empleo</h2>
                <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                    <a href="{{ route('admin.drivers.employment-verification.new') }}" class="btn btn-primary shadow-md">
                        <i data-feather="plus" class="w-4 h-4 mr-2"></i> Nueva Verificación
                    </a>
                </div>
            </div>
        </div>
        
        <div class="box-body p-5">
            <!-- Mensajes flash -->
            @if (session()->has('success'))
                <div class="alert alert-success mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Tabla de historial de verificaciones -->
            <div class="intro-y overflow-x-auto mt-5">
                <table class="table table-report table-hover">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">FECHA</th>
                            <th class="whitespace-nowrap">CONDUCTOR</th>
                            <th class="whitespace-nowrap">CARRIER</th>
                            <th class="whitespace-nowrap">EMPRESA</th>
                            <th class="whitespace-nowrap">EMAIL</th>
                            <th class="whitespace-nowrap">PERIODO</th>
                            <th class="whitespace-nowrap">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verificaciones as $verificacion)
                            <tr class="intro-x">
                                <td>{{ $verificacion->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($verificacion->userDriverDetail && $verificacion->userDriverDetail->user)
                                        {{ $verificacion->userDriverDetail->user->name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($verificacion->userDriverDetail && $verificacion->userDriverDetail->carrier)
                                        {{ $verificacion->userDriverDetail->carrier->name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $verificacion->company_name }}</td>
                                <td>{{ $verificacion->email }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($verificacion->employed_from)->format('d/m/Y') }} - 
                                    {{ \Carbon\Carbon::parse($verificacion->employed_to)->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if($verificacion->email_sent)
                                        <div class="flex items-center text-success">
                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i> Enviado
                                        </div>
                                    @else
                                        <div class="flex items-center text-warning">
                                            <i data-feather="clock" class="w-4 h-4 mr-1"></i> Pendiente
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-feather="inbox" class="w-16 h-16 text-slate-300"></i>
                                        <p class="text-slate-500 mt-2">No hay verificaciones de empleo registradas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Verificación -->
    <div x-data="{ showWizard: false }" @open-wizard.window="showWizard = true" @close-wizard.window="showWizard = false">
        <!-- Modal estándar para contener el wizard -->
        <div x-show="showWizard" class="modal" tabindex="-1" aria-hidden="true" style="display: none;" x-transition>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">
                            Verificación de Empleo
                        </h2>
                        <button type="button" @click="showWizard = false" class="btn btn-outline-secondary">
                            <i data-feather="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body p-0">
                        <!-- Componente Livewire del Wizard -->
                        <div>
                            @livewire('admin.driver.employment-verification-wizard')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
