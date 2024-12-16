@extends('../themes/' . $activeTheme)
@section('title', 'Crate Document Carriers')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],       
        ['label' => 'Document Carriers ', 'url' => route('admin.carrier_documents.all')], 
        ['label' => 'Document Carriers', 'active' => true],
    ];
@endphp

@section('subcontent')

@endsection