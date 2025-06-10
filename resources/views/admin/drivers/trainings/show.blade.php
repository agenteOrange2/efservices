@extends('../themes/' . $activeTheme)

@section('title', 'Training Details')

@section('subcontent')
    <div class="container mx-auto py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $training->title }}</h1>
                <p class="mt-1 text-sm text-gray-600">Training details</p>
            </div>
            <div class="flex space-x-2">
                <x-base.button as="a" href="{{ route('admin.trainings.edit', $training->id) }}"
                    variant="outline-primary">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="pencil" />
                    Edit
                </x-base.button>

                <x-base.button as="a" href="{{ route('admin.trainings.index') }}" variant="outline">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="arrow-left" />
                    Back
                </x-base.button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Training Information -->
            <div class="md:col-span-2">
                <div class="box box--stacked mt-5 p-3">
                    <div class="box-content">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                            <div class="col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Title</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ $training->title }}</dd>
                            </div>

                            <div class="col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-base text-gray-900">
                                    {!! nl2br(e($training->description)) !!}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Content Type</dt>
                                <dd class="mt-1">
                                    @if ($training->content_type === 'file')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            File
                                        </span>
                                    @elseif($training->content_type === 'video')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Video
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            URL
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @if ($training->status === 'active')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created by</dt>
                                <dd class="mt-1 text-base text-gray-900">
                                    {{ $training->creator ? $training->creator->name : 'N/A' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Creation Date</dt>
                                <dd class="mt-1 text-base text-gray-900">
                                    {{ $training->created_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>

                            @if ($training->content_type === 'video' && $training->video_url)
                                <div class="col-span-2 mt-4">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">Video</dt>
                                    <dd>
                                        @php
                                            // Extraer el ID del video de YouTube si es una URL de YouTube
                                            $videoId = null;
                                            if (strpos($training->video_url, 'youtube.com') !== false) {
                                                parse_str(parse_url($training->video_url, PHP_URL_QUERY), $params);
                                                $videoId = $params['v'] ?? null;
                                            } elseif (strpos($training->video_url, 'youtu.be') !== false) {
                                                $videoId = substr(parse_url($training->video_url, PHP_URL_PATH), 1);
                                            }
                                        @endphp

                                        @if ($videoId)
                                            <!-- Contenedor con aspect ratio responsive -->
                                            <div class="relative w-full"
                                                style="padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                                                <iframe src="https://www.youtube.com/embed/{{ $videoId }}"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    allowfullscreen
                                                    class="absolute top-0 left-0 w-full h-full rounded-lg shadow-lg border-0"
                                                    loading="lazy" title="Training Video"></iframe>
                                            </div>
                                        @else
                                            <!-- Opción alternativa: detectar otros tipos de video -->
                                            @php
                                                $isVimeo = strpos($training->video_url, 'vimeo.com') !== false;
                                                $vimeoId = null;
                                                if ($isVimeo) {
                                                    preg_match('/vimeo\.com\/(\d+)/', $training->video_url, $matches);
                                                    $vimeoId = $matches[1] ?? null;
                                                }
                                            @endphp

                                            @if ($isVimeo && $vimeoId)
                                                <div class="relative w-full" style="padding-bottom: 56.25%;">
                                                    <iframe src="https://player.vimeo.com/video/{{ $vimeoId }}"
                                                        frameborder="0" allow="autoplay; fullscreen; picture-in-picture"
                                                        allowfullscreen
                                                        class="absolute top-0 left-0 w-full h-full rounded-lg shadow-lg border-0"
                                                        loading="lazy" title="Training Video"></iframe>
                                                </div>
                                            @else
                                                <!-- Video HTML5 nativo o enlace externo -->
                                                @php
                                                    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
                                                    $extension = strtolower(
                                                        pathinfo(
                                                            parse_url($training->video_url, PHP_URL_PATH),
                                                            PATHINFO_EXTENSION,
                                                        ),
                                                    );
                                                    $isDirectVideo = in_array($extension, $videoExtensions);
                                                @endphp

                                                @if ($isDirectVideo)
                                                    <div class="w-full">
                                                        <video controls class="w-full rounded-lg shadow-lg max-h-96"
                                                            preload="metadata">
                                                            <source src="{{ $training->video_url }}"
                                                                type="video/{{ $extension === 'mov' ? 'quicktime' : $extension }}">
                                                            <p class="text-red-600">Your browser doesn't support HTML5
                                                                video. <a href="{{ $training->video_url }}"
                                                                    class="underline">Download the video</a> instead.</p>
                                                        </video>
                                                    </div>
                                                @else
                                                    <!-- Enlace externo genérico -->
                                                    <div
                                                        class="w-full bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                                        <div class="flex flex-col items-center">
                                                            <x-base.lucide class="w-12 h-12 text-gray-400 mb-4"
                                                                icon="play-circle" />
                                                            <h3 class="text-lg font-medium text-gray-900 mb-2">External
                                                                Video</h3>
                                                            <p class="text-sm text-gray-600 mb-4">This video is hosted on an
                                                                external platform</p>
                                                            <x-base.button as="a" href="{{ $training->video_url }}"
                                                                target="_blank" rel="noopener noreferrer"
                                                                class="inline-flex items-center">
                                                                <x-base.lucide class="w-5 h-5 mr-2" icon="external-link" />
                                                                Watch Video
                                                            </x-base.button>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif
                                    </dd>
                                </div>
                            @endif

                            @if ($training->content_type === 'url' && isset($training->url))
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Content URL</dt>
                                    <dd class="mt-1">
                                        <a href="{{ $training->url }}" target="_blank"
                                            class="text-blue-600 hover:underline flex items-center">
                                            {{-- <x-base.icon.external-link class="w-5 h-5 mr-2" /> --}}
                                            {{ $training->url }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Attached Files -->
                @if ($training->content_type === 'file')
                    <div class="box box--stacked mt-5 p-3">
                        <div class="box-header">
                            <h3 class="box-title">Attached Files</h3>
                        </div>
                        <div >
                            @if ($training->media->count() > 0)
                                <ul class="divide-y divide-gray-200">
                                    @foreach ($training->media as $file)
                                        <li class="py-4 flex items-center justify-between">
                                            <div class="flex items-center">
                                                @php
                                                    $extension = pathinfo($file->file_name, PATHINFO_EXTENSION);
                                                    $iconClass = match (strtolower($extension)) {
                                                        'pdf' => 'text-red-600',
                                                        'doc', 'docx' => 'text-blue-600',
                                                        'xls', 'xlsx' => 'text-green-600',
                                                        'ppt', 'pptx' => 'text-orange-600',
                                                        'jpg', 'jpeg', 'png', 'gif' => 'text-purple-600',
                                                        default => 'text-gray-600',
                                                    };
                                                @endphp

                                                @if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                    <x-base.lucide class="flex-shrink-0 h-5 w-5 {{ $iconClass }} mr-3"
                                                        icon="image" />
                                                @else
                                                    <x-base.lucide class="flex-shrink-0 h-5 w-5 {{ $iconClass }} mr-3"
                                                        icon="file-text" />
                                                @endif

                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $file->file_name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ number_format($file->size / 1024, 2) }} KB ·
                                                        {{ strtoupper($extension) }} ·
                                                        Uploaded on {{ $file->created_at->format('d/m/Y H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.trainings.preview-document', $file->id) }}"
                                                    target="_blank" class="text-blue-600 hover:text-blue-900"
                                                    title="View">
                                                    <x-base.lucide class="w-5 h-5" icon="eye" />
                                                </a>
                                                <a href="{{ route('admin.trainings.preview-document', ['document' => $file->id, 'download' => true]) }}"
                                                    class="text-green-600 hover:text-green-900" title="Download">
                                                    <x-base.lucide class="w-5 h-5" icon="download" />
                                                </a>
                                                <button type="button"
                                                    onclick="if(confirm('Are you sure you want to delete this file?')) { 
                                                        fetch('{{ route('api.documents.delete.post') }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Content-Type': 'application/json',
                                                                'Accept': 'application/json'
                                                            },
                                                            body: JSON.stringify({ 
                                                                mediaId: {{ $file->id }},
                                                                _token: '{{ csrf_token() }}'
                                                            })
                                                        })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if(data.success) {
                                                                window.location.reload();
                                                            } else {
                                                                alert('Error deleting file: ' + (data.error || 'Unknown error'));
                                                            }
                                                        })
                                                        .catch(error => {
                                                            alert('Error: ' + error);
                                                        });
                                                    }"
                                                    class="text-red-600 hover:text-red-900" title="Delete">
                                                    <x-base.lucide class="w-5 h-5" icon="trash-2" />
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-6">
                                    <x-base.lucide class="mx-auto h-12 w-12 text-gray-400" icon="file-search" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No files attached</h3>
                                    <p class="mt-1 text-sm text-gray-500">Add files by editing this training.</p>
                                    <div class="mt-6">
                                        <x-base.button as="a"
                                            href="{{ route('admin.trainings.edit', $training->id) }}">
                                            <x-base.lucide class="w-5 h-5 mr-2" icon="pencil" />
                                            Edit Training
                                        </x-base.button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Statistics and actions -->
            <div>
                <div class="box box--stacked mt-5 p-3">
                    <div class="box-header">
                        <h3 class="box-title">Statistics</h3>
                    </div>
                    <div class="box-content">
                        <dl class="grid grid-cols-1 gap-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assignments</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $assignmentStats['total'] }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Completed</dt>
                                <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $assignmentStats['completed'] }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">In Progress</dt>
                                <dd class="mt-1 text-3xl font-semibold text-blue-600">
                                    {{ $assignmentStats['in_progress'] }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pending</dt>
                                <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $assignmentStats['pending'] }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 border-t border-gray-200 pt-4">
                            <x-base.button as="a"
                                href="{{ route('admin.trainings.assign.form', $training->id) }}"
                                class="w-full justify-center">
                                Assign to Drivers
                            </x-base.button>
                        </div>

                        <div class="mt-3">
                            <x-base.button as="a"
                                href="{{ route('admin.training-assignments.index') }}"
                                variant="outline-primary" class="w-full justify-center">
                                {{-- <x-base.icon.clipboard-list class="w-5 h-5 mr-2" /> --}}
                                View Assignments
                            </x-base.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
