@extends('../themes/' . $activeTheme)
@section('title', 'Driver Application Review')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Recruitment', 'url' => route('admin.driver-recruitment.index')],
        ['label' => ' Driver Application Review', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Driver Application Review
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="outline-secondary" href="{{ route('admin.driver-recruitment.index') }}">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                        Back to List
                    </x-base.button>
                </div>
            </div>
            
            <!-- Componente Livewire -->
            @livewire('admin.driver.recruitment.driver-recruitment-review', ['driverId' => $driverId])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/driver-recruitment-modals.js') }}"></script>
@endpush