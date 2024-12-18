@extends('../themes/' . $activeTheme)

@section('title', 'Review Documents for ' . $carrier->name)

@section('subcontent')
    <h1 class="text-2xl font-bold mb-4">Documents for {{ $carrier->name }}</h1>    
    <table class="table-auto w-full border-collapse border">
        <thead>
            <tr>
                <th class="border p-2">Document Type</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Uploaded File</th>
                <th class="border p-2">Notes</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
                <tr>
                    <td class="border p-2">{{ $document->documentType->name }}</td>
                    <td class="border p-2">{{ $document->status_name }}</td>
                    <td class="border p-2">
                        @if ($document->getFirstMediaUrl('carrier_documents'))
                            <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" 
                               target="_blank" class="text-blue-500 underline">View File</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="border p-2">{{ $document->notes ?? 'No notes' }}</td>
                    <td class="border p-2">
                        <form action="{{ route('admin.carriers.documents.update', ['carrier' => $carrier->slug, 'document' => $document->id]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <select name="status" class="border p-1">
                                <option value="1" {{ $document->status == 1 ? 'selected' : '' }}>Approved</option>
                                <option value="2" {{ $document->status == 2 ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <input type="text" name="notes" placeholder="Add notes" class="border p-1" value="{{ $document->notes }}">
                            <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Update</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
