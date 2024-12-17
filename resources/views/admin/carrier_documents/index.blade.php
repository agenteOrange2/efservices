@extends('../themes/' . $activeTheme)
@section('title', 'Carrier Documents')

@section('subcontent')
    <h2>Required Documents</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Document Type</th>
                <th>Status</th>
                <th>Uploaded File</th>
                <th>Upload New File</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
                <tr>
                    <td>{{ $document->documentType->name }}</td>
                    <td>
                        <span class="badge {{ $document->status == \App\Models\CarrierDocument::STATUS_APPROVED ? 'bg-success' : ($document->status == \App\Models\CarrierDocument::STATUS_REJECTED ? 'bg-danger' : 'bg-warning') }}">
                            {{ $document->status_name }}
                        </span>
                    </td>
                    <td>
                        @if ($document->filename)
                            <a href="{{ asset('storage/' . $document->filename) }}" target="_blank">View File</a>
                        @else
                            No file uploaded
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.carrier.documents.store', $carrier->slug) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="document_type_id" value="{{ $document->documentType->id }}">
                            <input type="file" name="document" class="form-control mb-2" required>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
