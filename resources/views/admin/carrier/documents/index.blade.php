@extends('../themes/' . $activeTheme)

@section('title', 'Documents for ' . $carrier->name)

@section('subcontent')
    <h1 class="text-xl font-semibold">Documents for {{ $carrier->name }}</h1>

    <div class="mt-4">
        @forelse ($documents as $document)
            <div class="border-b py-4">
                <h2 class="text-lg font-bold">{{ $document->documentType->name }}</h2>
                <p class="text-gray-600">Status: {{ $document->status_name }}</p>

                @if ($document->notes)
                    <p class="text-gray-600">Notes: {{ $document->notes }}</p>
                @endif

                @if (!$document->getFirstMediaUrl('document'))
                    <!-- Mostrar formulario si no hay archivo subido -->
                    <form action="{{ route('admin.carrier.user_documents.upload', [$carrier->slug, $document->type->id]) }}" 
                        method="POST" enctype="multipart/form-data">                    
                        @csrf
                        <input type="file" name="document" class="block mb-2" required>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload File</button>
                    </form>
                @else
                    <!-- Mostrar enlace si el archivo está subido -->
                    <a href="{{ $document->getFirstMediaUrl('document') }}" target="_blank" class="text-blue-500 underline">
                        View Uploaded File
                    </a>
                @endif
            </div>
        @empty
            <p class="text-gray-500">No documents available for this carrier.</p>
        @endforelse
    </div>
@endsection
