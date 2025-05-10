@extends('admin.layouts.base')

@section('subhead')
    <title>Create Driver Test Record</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Create New Driver Test Record</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.driver-testings.index') }}" class="btn btn-secondary shadow-md mr-2">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to List
            </a>
        </div>
    </div>

    <div class="intro-y box p-5 mt-5">
        <div class="mb-5">
            <h2 class="font-medium text-base mr-auto mb-2">Test Information</h2>
            <p class="text-slate-500">Fill in the details for the new driver test record.</p>
        </div>

        <!-- Livewire Component -->
        @livewire('admin.driver.testings-list', ['driverId' => request('driver_id'), 'showModal' => true])
    </div>
@endsection
