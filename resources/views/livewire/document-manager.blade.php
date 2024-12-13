<div>
    <h1 class="text-xl font-bold">Documents for Carrier: {{ $carrier->name }}</h1>
    <table class="table-auto w-full">
        <thead>
            <tr>
                <th>Document Name</th>
                <th>Uploaded At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($carrier->documents as $document)
                <tr>
                    <td>{{ $document->name }}</td>
                    <td>{{ $document->created_at }}</td>
                    <td>
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No documents found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
