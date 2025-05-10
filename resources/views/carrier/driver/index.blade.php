@extends('../themes/' . $activeTheme)
@section('title', 'Driver Management')

@section('subhead')
    <title>Driver Management - EF Services</title>
@endsection

@section('subcontent')
    <div class="py-5">
        <div class="mb-8">
            <h2 class="text-2xl font-medium">Driver Management</h2>
            <div class="mt-2 text-slate-500">
                Manage the drivers assigned to your transport company.
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert-success alert mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger alert mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Componente Livewire para listar y filtrar conductores -->
        <livewire:carrier.carrier-drivers-list />
    </div>
@endsection