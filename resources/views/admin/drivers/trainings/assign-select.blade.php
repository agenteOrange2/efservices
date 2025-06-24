@extends('../themes/' . $activeTheme)

@section('title', 'Select Training to Assign')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Trainings', 'url' => route('admin.trainings.index')],
        ['label' => 'Select to Assign', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Select Training to Assign</h1>
                <p class="mt-1 text-sm text-gray-600">Select a training to assign to drivers</p>
            </div>
            <div>
                <x-base.button as="a" href="{{ route('admin.trainings.index') }}" variant="outline">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="arrow-left" />
                    Back
                </x-base.button>
            </div>
        </div>

        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Available Trainings</h3>
            </div>
            <div class="box-content">
                @if($trainings->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($trainings as $training)
                            <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                <div class="p-4 border-b bg-gray-50">
                                    <h3 class="text-lg font-medium text-gray-900 truncate">{{ $training->title }}</h3>
                                </div>
                                <div class="p-4">
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        {{ Str::limit($training->description, 150) }}
                                    </p>
                                    
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <x-base.lucide class="w-4 h-4 mr-1" icon="calendar" />
                                        <span>{{ $training->created_at->format('m/d/Y') }}</span>
                                        
                                        <span class="mx-2">•</span>
                                        
                                        @php
                                            $filesCount = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\Training::class)
                                                ->where('model_id', $training->id)
                                                ->where('collection_name', 'training_files')
                                                ->count();
                                        @endphp
                                        
                                        <x-base.lucide class="w-4 h-4 mr-1" icon="file" />
                                        <span>{{ $filesCount }} {{ $filesCount === 1 ? 'file' : 'files' }}</span>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <x-base.button as="a" href="{{ route('admin.trainings.assign.form', $training->id) }}" variant="primary">
                                            <x-base.lucide class="w-5 h-5 mr-2" icon="users" />
                                            Assign
                                        </x-base.button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-base.lucide class="w-16 h-16 mx-auto text-gray-400" icon="file-question" />
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No trainings available</h3>
                        <p class="mt-1 text-sm text-gray-500">Create a training first to be able to assign it to drivers.</p>
                        <div class="mt-6">
                            <x-base.button as="a" href="{{ route('admin.trainings.create') }}">
                                <x-base.lucide class="w-5 h-5 mr-2" icon="plus" />
                                Create Training
                            </x-base.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

