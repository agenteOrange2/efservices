@extends('layouts.admin')

@section('title', 'Accidentes por Transportista')

@section('content')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Accidentes por Transportista</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.driver-accidents.index') }}" class="btn btn-secondary shadow-md mr-2">
            <i class="w-4 h-4 mr-2" data-lucide="corner-up-left"></i> Volver a Accidentes
        </a>
    </div>
</div>

<div class="intro-y box p-5 mt-5">
    <!-- Aquí va el componente Livewire que maneja toda la lógica, 
         filtrado por el carrier_id que viene del controlador -->
    <livewire:admin.driver.accidents-by-carrier :carrierId="$carrier_id" />
</div>
@endsection