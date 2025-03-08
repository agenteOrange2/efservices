<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Driver Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Welcome, {{ auth()->user()->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Your current status: 
                            <span class="font-medium">
                                @if($driver->status === 1)
                                    Active
                                @elseif($driver->status === 0)
                                    Inactive
                                @else
                                    Pending
                                @endif
                            </span>
                        </p>
                    </div>

                    <!-- Aquí puedes agregar más secciones del dashboard -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>