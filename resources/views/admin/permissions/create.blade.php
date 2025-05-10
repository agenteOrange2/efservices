@extends('../themes/' . $activeTheme)

@section('title', 'Create Permission')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Permissions', 'url' => route('admin.permissions.index')],
        ['label' => 'New Permissions', 'active' => true],
    ];
@endphp

@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce

@section('subcontent')
<div class="box box--stacked">
    <div class="p-6">
        <h2 class="text-lg font-semibold">Create Permission</h2>

        <form action="{{ route('admin.permissions.store') }}" method="POST" class="mt-4">
            @csrf

            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                    <div class="text-left">
                        <div class="flex items-center">
                            <div class="font-medium">Role Name</div>
                            <div
                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                Required
                            </div>
                        </div>
                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                            Enter your full legal name as it appears on your official
                            identification.
                        </div>
                    </div>
                </div>
                <div class="mt-3 w-full flex-1 xl:mt-0">
                    <x-base.form-input name="name" type="text" placeholder="Enter Permission Name" id="name"
                        value="{{ old('name') }}" />
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end">
                <x-base.button type="submit" class="w-full border-primary/50 px-10 md:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                    Create
                </x-base.button>
            </div>               
        </form>
    </div>
</div>
@endsection
