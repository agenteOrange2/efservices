@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard EF Services ')


@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Dashboard', 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="p-0">

        <livewire:admin.dashboard-stats />
    </div>

    
@endsection

