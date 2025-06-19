@extends('../themes/' . $activeTheme)
@section('title', 'All Drivers Overview')

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Verificaciones de Empleo</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.driver.index') }}" class="btn btn-secondary shadow-md mr-2">
            <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Volver a Conductores
        </a>
    </div>
</div>

<div class="intro-y box p-5 mt-5">
    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
        <form id="tabulator-html-filter-form" class="xl:flex sm:mr-auto" action="{{ route('admin.driver.employment-verification.index') }}" method="GET">
            <div class="sm:flex items-center sm:mr-4">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Estado:</label>
                <select name="status" class="form-select w-full 2xl:w-full mt-2 sm:mt-0 sm:w-auto">
                    <option value="">Todos</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verificados</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazados</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                </select>
            </div>
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Conductor:</label>
                <select name="driver" class="form-select w-full mt-2 sm:mt-0 sm:w-auto">
                    <option value="">Todos los conductores</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ request('driver') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->user->name }} {{ $driver->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mt-2 xl:mt-0">
                <button id="tabulator-html-filter-go" type="submit" class="btn btn-primary w-full sm:w-16">Filtrar</button>
                <a href="{{ route('admin.driver.employment-verification.index') }}" id="tabulator-html-filter-reset" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1">Limpiar</a>
            </div>
        </form>
    </div>
    
    <div class="overflow-x-auto scrollbar-hidden">
        <div class="mt-5 table-report table-report--tabulator">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Conductor</th>
                        <th class="whitespace-nowrap">Empresa</th>
                        <th class="whitespace-nowrap">Email</th>
                        <th class="whitespace-nowrap">Fecha de Envío</th>
                        <th class="whitespace-nowrap">Estado</th>
                        <th class="whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employmentVerifications as $verification)
                        <tr>
                            <td>
                                <a href="{{ route('admin.driver.show', $verification->userDriverDetail->id) }}" class="font-medium whitespace-nowrap">
                                    {{ $verification->userDriverDetail->user->name }} {{ $verification->userDriverDetail->last_name }}
                                </a>
                            </td>
                            <td>
                                {{ $verification->masterCompany ? $verification->masterCompany->name : 'Empresa personalizada' }}
                            </td>
                            <td>
                                {{ $verification->email }}
                            </td>
                            <td>
                                {{ $verification->updated_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @if($verification->verification_status == 'verified')
                                    <div class="flex items-center text-success">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verificado
                                    </div>
                                @elseif($verification->verification_status == 'rejected')
                                    <div class="flex items-center text-danger">
                                        <i data-lucide="x-circle" class="w-4 h-4 mr-1"></i> Rechazado
                                    </div>
                                @else
                                    <div class="flex items-center text-warning">
                                        <i data-lucide="clock" class="w-4 h-4 mr-1"></i> Pendiente
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="flex">
                                    <a href="{{ route('admin.driver.employment-verification.show', $verification->id) }}" class="btn btn-sm btn-primary mr-1">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.driver.employment-verification.resend', $verification->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary mr-1" title="Reenviar correo">
                                            <i data-lucide="mail" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay verificaciones de empleo disponibles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-5">
            {{ $employmentVerifications->links() }}
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
