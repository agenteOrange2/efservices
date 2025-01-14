@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard EF Services ')


@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],        
        ['label' => 'Dashboard', 'active' => true],
    ];
@endphp

@section('subcontent')
<h1>Admin user carrier</h1>
@endsection