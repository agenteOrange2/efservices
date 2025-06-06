@extends('../themes/' . $activeTheme)
@section('title', 'Driver Recruitment')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Recruitment', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Driver Recruitment
                </div>
            </div>
            
            <!-- Componente Livewire -->
            @livewire('admin.driver.recruitment.driver-recruitment-list')
        </div>
    </div>
@endsection