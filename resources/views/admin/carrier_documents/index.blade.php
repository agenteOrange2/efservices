@extends('../themes/' . $activeTheme)
@section('title', 'Carrier Documents')

@section('subcontent')

    <div class="container mx-auto mt-6">
        <h1 class="text-2xl font-bold mb-4">Document Review</h1>
        
        <table class="w-full table-auto border-collapse border">
            <thead>
                <tr>
                    <th class="border p-2">Carrier</th>
                    <th class="border p-2">Document Type</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Uploaded File</th>
                    <th class="border p-2">Notes</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($carrierDocuments as $document)
                    <tr>
                        <td class="border p-2">{{ $document->carrier->name }}</td>
                        <td class="border p-2">{{ $document->documentType->name }}</td>
                        <td class="border p-2">{{ $document->status_name }}</td>
                        <td class="border p-2">
                            @if ($document->getFirstMediaUrl('carrier_documents'))
                                <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" target="_blank" class="text-blue-500 underline">
                                    View File
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="border p-2">{{ $document->notes ?? 'No comments' }}</td>
                        <td class="border p-2 flex gap-2">
                            <!-- Form to update status -->
                            <form action="{{ route('admin.carrier_documents.update', $document->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="status" class="border p-1">
                                    <option value="0" {{ $document->status == 0 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ $document->status == 1 ? 'selected' : '' }}>Approved</option>
                                    <option value="2" {{ $document->status == 2 ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <input type="text" name="notes" placeholder="Add notes" class="border p-1" value="{{ $document->notes }}">
                                <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Update</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center p-4">No documents available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
