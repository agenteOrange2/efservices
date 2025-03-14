@extends('../themes/' . $activeTheme)

@section('title', 'Upload Documents for ' . $carrier->name)



@section('subcontent')
    <h1 class="text-xl font-semibold">Documents for {{ $carrier->name }}</h1>    
    <div class="mt-4">
        @foreach ($documents as $item)
            <div class="border-b py-4">
                <h2 class="text-lg font-bold">{{ $item['type']->name }}</h2>
                <p class="text-gray-600">Status: {{ $item['status_name'] }}</p>

                @if ($item['notes'])
                    <p class="text-gray-600">Notes: {{ $item['notes'] }}</p>
                @endif

                @if ($item['file_url'])
                    <a href="{{ $item['file_url'] }}" target="_blank" class="text-blue-500 underline">
                        View Uploaded File
                    </a>
                @else
                <form action="{{ route('admin.carrier.user_documents.upload', [$carrier->slug, $item['type']->id]) }}" 
                    method="POST" enctype="multipart/form-data" class="mt-2">
                  @csrf
                  <input type="file" name="document" class="block mb-2">
                  <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload File</button>
              </form>
              
                @endif
            </div>
        @endforeach
    </div>
@endsection
