@extends('../themes/' . $activeTheme)

@section('title', 'Carrier Documents Overview')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],        
        ['label' => 'Carriers Documents', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Carriers Document Review</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.carrier.index') }}" class="btn btn-primary shadow-md mr-2">
                <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Back to Carriers
            </a>
        </div>
    </div>

    <div class="intro-y box p-5 mt-5">
        <div class="text-slate-500 text-xs">Review and manage carrier documents. Use the filters to find specific carriers and check their document status.</div>
    </div>

    <livewire:document.document-table/>
@endsection


