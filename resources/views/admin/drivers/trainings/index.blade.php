@extends('../themes/' . $activeTheme)

@section('title', 'Trainings')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Trainings', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Trainings</h1>
                <p class="mt-1 text-sm text-gray-600">Manage trainings for drivers</p>
            </div>
            <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-2">
                <x-base.button as="a" href="{{ route('admin.trainings.create') }}" class="w-full sm:w-auto">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="plus" />
                    Create Training
                </x-base.button>
                
                {{-- Se eliminó el botón general de asignar entrenamientos --}}
                
                <x-base.button as="a" href="{{ route('admin.training-assignments.index') }}" class="w-full sm:w-auto" variant="outline-primary">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="clipboard-list" />
                    View Assignments
                </x-base.button>
            </div>
        </div>

        <!-- Instrucciones de asignación -->
        <div class="box box--stacked mt-5 p-3 bg-blue-50">
            <h3 class="box-title text-primary">Instructions for assigning trainings</h3>
            <div class="p-3">
                <p class="text-primary"><strong>To assign a training:</strong> Click on the "View details" button of any active training and then use the "Assign to drivers" button on the details page.</p>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="box box--stacked mt-5 p-3">
            <h3 class="box-title">Filter Trainings</h3>
            <div class="box-content">
                <form action="{{ route('admin.trainings.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-base.form-label for="search">Search</x-base.form-label>
                        <x-base.form-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Title or description" />
                    </div>
                    
                    <div>
                        <x-base.form-label for="status">Status</x-base.form-label>
                        <x-base.form-select name="status" id="status">
                            <option value="">All</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </x-base.form-select>
                    </div>
                    
                    <div>
                        <x-base.form-label for="type">Content Type</x-base.form-label>
                        <x-base.form-select name="type" id="type">
                            <option value="">All</option>
                            <option value="file" {{ request('type') === 'file' ? 'selected' : '' }}>File</option>
                            <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Video</option>
                            <option value="url" {{ request('type') === 'url' ? 'selected' : '' }}>URL</option>
                        </x-base.form-select>
                    </div>
                    
                    <div class="flex items-end">
                        <x-base.button type="submit" class="mr-2">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="search" />
                            Filter
                        </x-base.button>
                        
                        <x-base.button type="button" variant="outline" onclick="window.location.href='{{ route('admin.trainings.index') }}'">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="x" />
                            Clear
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Trainings ({{ $trainings->total() ?? 0 }})</h3>
            </div>
            <div class="box-content">
                @if($trainings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>                                    
                                    <th>Date</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ route('admin.trainings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'title', 'direction' => request('sort') == 'title' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                            Title
                                            @if(request('sort') == 'title')
                                                @if(request('direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-1" icon="arrow-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-1" icon="arrow-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ route('admin.trainings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'content_type', 'direction' => request('sort') == 'content_type' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                            Content Type
                                            @if(request('sort') == 'content_type')
                                                @if(request('direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-1" icon="arrow-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-1" icon="arrow-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ route('admin.trainings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                            Status
                                            @if(request('sort') == 'status')
                                                @if(request('direction') == 'asc')
                                                    <x-base.lucide class="w-4 h-4 ml-1" icon="arrow-up" />
                                                @else
                                                    <x-base.lucide class="w-4 h-4 ml-1" icon="arrow-down" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Files
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($trainings as $training)
                                    <tr>
                                        <td>
                                            {{ $training->created_at->format('m/d/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $training->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($training->content_type === 'file')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    File
                                                </span>
                                            @elseif($training->content_type === 'video')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Video
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    URL
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($training->status === 'active')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @php
                                                $filesCount = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\Training::class)
                                                    ->where('model_id', $training->id)
                                                    ->where('collection_name', 'training_files')
                                                    ->count();
                                            @endphp
                                            
                                            <span class="text-blue-600">
                                                {{ $filesCount }} {{ $filesCount === 1 ? 'File' : 'Files' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.trainings.show', $training->id) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900" 
                                                   title="Ver detalles">
                                                    <x-base.lucide class="w-5 h-5" icon="eye" />
                                                </a>
                                                
                                                @if($training->status == 'active')
                                                <a href="/admin/trainings/{{ $training->id }}/assign" 
                                                   class="text-green-600 hover:text-green-900" 
                                                   title="Asignar a conductores">
                                                    <x-base.lucide class="w-5 h-5" icon="users" />
                                                </a>
                                                @endif
                                                
                                                <a href="{{ route('admin.trainings.edit', $training->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900" 
                                                   title="Editar">
                                                    <x-base.lucide class="w-5 h-5" icon="edit" />
                                                </a>
                                                
                                                <form action="{{ route('admin.trainings.destroy', $training->id) }}" 
                                                      method="POST" 
                                                      class="inline" 
                                                      onsubmit="return confirm('¿Está seguro de que desea eliminar este entrenamiento?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                        <x-base.lucide class="w-5 h-5" icon="trash-2" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $trainings->appends(request()->all())->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <x-base.lucide class="mx-auto h-12 w-12 text-gray-400" icon="file-text" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No trainings found</h3>
                        <p class="mt-1 text-sm text-gray-500">Start by creating a new training.</p>
                        <div class="mt-6">
                            <x-base.button as="a" href="{{ route('admin.trainings.create') }}" class="mt-5">
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
