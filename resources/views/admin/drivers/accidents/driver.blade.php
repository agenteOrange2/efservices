@extends('layouts.admin')

@section('title', 'Accidentes del Conductor')

@section('content')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Accidentes del Conductor</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.driver-accidents.carrier', $carrier_id) }}" class="btn btn-secondary shadow-md mr-2">
            <i class="w-4 h-4 mr-2" data-lucide="corner-up-left"></i> Volver al Transportista
        </a>
    </div>
</div>

<div class="intro-y box p-5 mt-5">
    <!-- Componente Livewire que maneja la lógica para un conductor específico -->
    <livewire:admin.driver.accidents-list :driverId="$driver_id" />
</div>
@endsection