@extends('../themes/' . $activeTheme)

@section('title', 'Create Role')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Roles', 'url' => route('admin.roles.index')],
        ['label' => 'New Role', 'active' => true],
    ];
@endphp

@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce

@section('subcontent')
    <div class="box box--stacked flex flex-col">
        <div class="p-6">
            <h2 class="text-lg font-semibold">Create Role</h2>

            <form action="{{ route('admin.roles.store') }}" method="POST" class="mt-4">
                @csrf
                <!-- Full Name -->
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
                        <x-base.form-input name="name" type="text" placeholder="Enter Role Name" id="name"
                            value="{{ old('name') }}" />
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="my-9">
                    <label>Vertical Checkbox</label>
                    @foreach ($permissions as $permission)
                        <div data-tw-merge class="flex items-center mt-2">
                            <input data-tw-merge type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50"
                                id="checkbox-switch-1" value="">
                            <label data-tw-merge class="cursor-pointer ml-2">{{ $permission->name }}</label>
                        </div>
                    @endforeach
                </div>
                <!-- Submit Button -->
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
@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
@endPushOnce
