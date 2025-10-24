@extends('../themes/' . $activeTheme)

@section('title', 'Carrier Documents Overview')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],        
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Carriers Documents', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Carriers Document Review</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <x-base.button href="{{ route('admin.carrier.index') }}" variant="primary" class="shadow-md mr-2">
                <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Back to Carriers
            </x-base.button>
        </div>
    </div>
    <livewire:document.document-table/>
@endsection


