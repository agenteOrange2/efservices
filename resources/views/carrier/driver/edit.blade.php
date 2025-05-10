@extends('../themes/' . $activeTheme)

@section('title', 'Edit User Driver')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('carrier.dashboard')],
    ['label' => 'Drivers', 'url' => route('carrier.drivers.index')],
    ['label' => 'Edit Driver', 'active' => true],
];
@endphp

@section('subcontent')
    <div class="py-5">
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('carrier.drivers.index') }}" class="btn btn-outline-secondary mr-4">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Volver
                </a>
                <h2 class="text-2xl font-medium">Editar Conductor</h2>
            </div>
            <div class="mt-2 text-slate-500">
                Actualice la información del conductor {{ $driver->user->name }} {{ $driver->last_name }}.
            </div>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="box p-5">
            <!-- Componente Livewire para edición por pasos -->
            <livewire:carrier.step.carrier-driver-registration-manager :driverId="$driverId" />            
        </div>
    </div>
@endsection

@pushOnce('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
@endPushOnce