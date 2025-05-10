@extends('../themes/' . $activeTheme)
@section('title', 'Driver Accidents Management')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Driver Accidents Management', 'active' => true],
];
@endphp

@section('subcontent')
<div class="container mx-auto px-4 py-8">
    @livewire('admin.accidents.all-accidents-list')
</div>
@endsection