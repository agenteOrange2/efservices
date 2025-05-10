@extends('../themes/' . $activeTheme)
@section('title', 'Driver Accident History')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Driver Accidents', 'url' => route('admin.accidents.index')],
    ['label' => 'Driver Accident History', 'active' => true],
];
@endphp

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">
        Accident History for {{ $driver->user->name }} {{ $driver->last_name }}
    </h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-outline-secondary mr-2">
            <x-base.lucide class="w-4 h-4 mr-2" icon="user" />
            Driver Profile
        </a>
        <a href="{{ route('admin.accidents.index') }}" class="btn btn-outline-secondary">
            <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
            All Accidents
        </a>
    </div>
</div>

<div class="container mx-auto px-4 py-4">
    @livewire('admin.accidents.accidents-manager', ['driverId' => $driver->id])
</div>
@endsection