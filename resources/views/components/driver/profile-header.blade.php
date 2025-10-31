@props(['driver'])

<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex py-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <x-base.lucide icon="home" class="w-4 h-4 mr-2" />
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-base.lucide icon="chevron-right" class="w-4 h-4 text-gray-400" />
                        <a href="{{ route('admin.drivers.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Drivers</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <x-base.lucide icon="chevron-right" class="w-4 h-4 text-gray-400" />
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $driver->user->name ?? 'Driver Details' }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header Content -->
        <div class="pb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <!-- Title and Back Button -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.drivers.index') }}" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <x-base.lucide icon="arrow-left" class="w-4 h-4 mr-2" />
                        Back to Drivers
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $driver->user->name ?? 'Driver Details' }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Driver ID: #{{ $driver->id }} • 
                            <span class="capitalize">{{ $driver->status ?? 'active' }}</span> • 
                            Member since {{ $driver->created_at ? $driver->created_at->format('M Y') : 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <a href="{{ route('admin.drivers.documents.download', $driver->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <x-base.lucide icon="download" class="w-4 h-4 mr-2" />
                        Download Documents
                    </a>
                    <a href="{{ route('admin.drivers.regenerate-application-forms', $driver->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <x-base.lucide icon="refresh-cw" class="w-4 h-4 mr-2" />
                        Regenerate Forms
                    </a>
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <x-base.lucide icon="more-horizontal" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>