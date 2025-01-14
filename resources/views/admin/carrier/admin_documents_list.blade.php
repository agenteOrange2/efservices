@extends('../themes/' . $activeTheme)

@section('title', 'Carrier Documents Overview')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],        
        ['label' => 'Carriers Documents', 'active' => true],
    ];
@endphp

@section('subcontent')
    <h1 class="text-2xl font-bold mb-6">Carriers Document Review asas</h1>

     <livewire:document.document-table/>
@endsection



