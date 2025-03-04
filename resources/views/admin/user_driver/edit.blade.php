{{-- resources/views/admin/user_driver/livewire/edit.blade.php --}}
@extends('../themes/' . $activeTheme)
@section('title', 'Edit User Driver')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Drivers', 'url' => route('admin.carrier.user_drivers.index', $carrier->slug)],
    ['label' => 'Edit Driver', 'active' => true],
];
@endphp

@section('subcontent')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Driver</h1>
        <a href="{{ route('admin.carrier.user_drivers.index', $carrier) }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md">
        <livewire:admin.driver.driver-edit-form :carrier="$carrier" :userDriverDetail="$userDriverDetail" />
    </div>
</div>
@endsection

@pushOnce('scripts')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
@endPushOnce