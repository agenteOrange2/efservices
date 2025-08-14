@extends('../themes/' . $activeTheme)
@section('title', 'Nueva Verificación de Empleo')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Employment Verifications', 'url' => route('admin.drivers.employment-verification.index')],
        ['label' => 'Nueva Verificación', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Nueva Verificación de Empleo</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">            
            <a href="{{ route('admin.drivers.employment-verification.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="box box--stacked mt-5">
        <div class="box-header p-5">
            <ul class="nav nav-tabs flex flex-col md:flex-row flex-wrap list-none border-b-0 pl-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ route('admin.drivers.employment-verification.index') }}"
                        class="nav-link w-full block font-medium text-sm leading-tight uppercase px-6 py-3 my-2 hover:bg-gray-100">
                        Verification Requests
                    </a>
                </li>                
                <li class="nav-item" role="presentation">
                    <a href="{{ route('admin.drivers.employment-verification.new') }}"
                        class="nav-link w-full block font-medium text-sm leading-tight uppercase px-6 py-3 border-b-2 border-primary my-2 bg-slate-50">
                        Nueva Verificación
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Contenedor del Wizard -->
    <div class="box box--stacked mt-5">
        <div class="box-body p-5">
            <!-- Componente Livewire del Wizard -->
            <div class="w-full">
                @livewire('admin.driver.employment-verification-wizard')
            </div>
        </div>
    </div>
@endsection
