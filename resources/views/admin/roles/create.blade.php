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
                    <h3 class="text-lg font-medium mb-4">Assign Permissions</h3>
                
                    <!-- Users Permissions -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-2 text-primary">User Management</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($permissions as $permission)
                                @if (str_starts_with($permission->name, 'view users') || str_starts_with($permission->name, 'create users') || 
                                     str_starts_with($permission->name, 'edit users') || str_starts_with($permission->name, 'delete users'))
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                            id="permission_{{ $permission->id }}"
                                            class="form-checkbox h-5 w-5 text-primary">
                                        <label for="permission_{{ $permission->id }}" class="ml-2 cursor-pointer">{{ $permission->name }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                
                    <!-- Roles Permissions -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-2 text-primary">Roles Management</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($permissions as $permission)
                                @if (str_starts_with($permission->name, 'view roles') || str_starts_with($permission->name, 'create roles') || 
                                     str_starts_with($permission->name, 'edit roles') || str_starts_with($permission->name, 'delete roles') ||
                                     str_starts_with($permission->name, 'assign permissions'))
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                            id="permission_{{ $permission->id }}"
                                            class="form-checkbox h-5 w-5 text-primary">
                                        <label for="permission_{{ $permission->id }}" class="ml-2 cursor-pointer">{{ $permission->name }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                
                    <!-- Carriers Permissions -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-2 text-primary">Carriers Management</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($permissions as $permission)
                                @if (str_starts_with($permission->name, 'view carriers') || str_starts_with($permission->name, 'create carriers') || 
                                     str_starts_with($permission->name, 'edit carriers') || str_starts_with($permission->name, 'delete carriers') ||
                                     str_starts_with($permission->name, 'manage carrier'))
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                            id="permission_{{ $permission->id }}"
                                            class="form-checkbox h-5 w-5 text-primary">
                                        <label for="permission_{{ $permission->id }}" class="ml-2 cursor-pointer">{{ $permission->name }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                
                    <!-- Drivers Permissions -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-2 text-primary">Drivers Management</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($permissions as $permission)
                                @if (str_starts_with($permission->name, 'view drivers') || str_starts_with($permission->name, 'create drivers') || 
                                     str_starts_with($permission->name, 'edit drivers') || str_starts_with($permission->name, 'delete drivers') ||
                                     str_starts_with($permission->name, 'manage driver'))
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                            id="permission_{{ $permission->id }}"
                                            class="form-checkbox h-5 w-5 text-primary">
                                        <label for="permission_{{ $permission->id }}" class="ml-2 cursor-pointer">{{ $permission->name }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                
                    <!-- Dashboard Permissions -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-2 text-primary">Dashboard Access</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($permissions as $permission)
                                @if (str_starts_with($permission->name, 'view admin dashboard') || str_starts_with($permission->name, 'view carrier dashboard') || 
                                     str_starts_with($permission->name, 'view driver dashboard'))
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                            id="permission_{{ $permission->id }}"
                                            class="form-checkbox h-5 w-5 text-primary">
                                        <label for="permission_{{ $permission->id }}" class="ml-2 cursor-pointer">{{ $permission->name }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                
                    <!-- Other Permissions -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium mb-2 text-primary">Other Permissions</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($permissions as $permission)
                                @if (!str_starts_with($permission->name, 'view users') && !str_starts_with($permission->name, 'create users') && 
                                     !str_starts_with($permission->name, 'edit users') && !str_starts_with($permission->name, 'delete users') &&
                                     !str_starts_with($permission->name, 'view roles') && !str_starts_with($permission->name, 'create roles') && 
                                     !str_starts_with($permission->name, 'edit roles') && !str_starts_with($permission->name, 'delete roles') &&
                                     !str_starts_with($permission->name, 'assign permissions') &&
                                     !str_starts_with($permission->name, 'view carriers') && !str_starts_with($permission->name, 'create carriers') && 
                                     !str_starts_with($permission->name, 'edit carriers') && !str_starts_with($permission->name, 'delete carriers') &&
                                     !str_starts_with($permission->name, 'manage carrier') &&
                                     !str_starts_with($permission->name, 'view drivers') && !str_starts_with($permission->name, 'create drivers') && 
                                     !str_starts_with($permission->name, 'edit drivers') && !str_starts_with($permission->name, 'delete drivers') &&
                                     !str_starts_with($permission->name, 'manage driver') &&
                                     !str_starts_with($permission->name, 'view admin dashboard') && !str_starts_with($permission->name, 'view carrier dashboard') && 
                                     !str_starts_with($permission->name, 'view driver dashboard'))
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                            id="permission_{{ $permission->id }}"
                                            class="form-checkbox h-5 w-5 text-primary">
                                        <label for="permission_{{ $permission->id }}" class="ml-2 cursor-pointer">{{ $permission->name }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
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
