@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="flex flex-col items-center mt-8">
        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">User Details</h2>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5 w-full max-w-6xl">
            <!-- User Profile Card -->
            <div class="intro-y box col-span-12 lg:col-span-4">
                <div class="flex flex-col items-center p-5">
                    <div class="w-24 h-24 rounded-full overflow-hidden mb-4">
                        @if($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <h2 class="text-xl font-medium">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <div class="mt-3">
                        <span class="px-3 py-1 rounded-full {{ $user->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 justify-center">
                        @foreach($roles as $role)
                            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="border-t border-gray-200 p-5">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Created</span>
                        <span>{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-gray-600">Last Updated</span>
                        <span>{{ $user->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="p-5 flex justify-center">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary mr-2">
                        <x-base.lucide class="w-4 h-4 mr-1" icon="Edit" />
                        Edit User
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <x-base.lucide class="w-4 h-4 mr-1" icon="ArrowLeft" />
                        Back
                    </a>
                </div>
            </div>

            <!-- User Details -->
            <div class="intro-y box col-span-12 lg:col-span-8">
                <div class="p-5">
                    <h3 class="text-lg font-medium mb-4">User Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-800">Account Details</h4>
                            <div class="mt-3 space-y-3">
                                <div>
                                    <label class="text-gray-600 block">Full Name</label>
                                    <p class="font-medium">{{ $user->name }}</p>
                                </div>
                                <div>
                                    <label class="text-gray-600 block">Email Address</label>
                                    <p class="font-medium">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <label class="text-gray-600 block">Status</label>
                                    <p class="font-medium">{{ $user->status ? 'Active' : 'Inactive' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-800">Roles & Permissions</h4>
                            <div class="mt-3">
                                <label class="text-gray-600 block">Assigned Roles</label>
                                <div class="mt-2 space-y-2">
                                    @forelse($roles as $role)
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-blue-600 rounded-full mr-2"></div>
                                            <span>{{ $role->name }}</span>
                                        </div>
                                    @empty
                                        <p class="text-gray-500 italic">No roles assigned</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <h4 class="font-medium text-gray-800 mb-3">Activity Timeline</h4>
                        <div class="border-l-2 border-gray-200 pl-4 space-y-6">
                            <div class="relative">
                                <div class="absolute -left-[25px] mt-1.5">
                                    <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                                </div>
                                <p class="text-sm text-gray-600">Account Created</p>
                                <p class="font-medium">{{ $user->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="relative">
                                <div class="absolute -left-[25px] mt-1.5">
                                    <div class="w-4 h-4 rounded-full bg-green-500"></div>
                                </div>
                                <p class="text-sm text-gray-600">Last Updated</p>
                                <p class="font-medium">{{ $user->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
