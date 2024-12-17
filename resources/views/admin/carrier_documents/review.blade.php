@extends('../themes/' . $activeTheme)
@section('title', 'Review Document')

@section('subcontent')
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Review Document for Carrier: {{ $carrier->name }}</h3>
    </div>
    <div class="box-body">
        <table class="table-auto w-full">
            <tr>
                <th>Document Type</th>
                <td>{{ $document->documentType->name }}</td>
            </tr>
            <tr>
                <th>Current Status</th>
                <td>
                    <span class="badge badge-{{ $document->status == 1 ? 'success' : ($document->status == 2 ? 'danger' : 'warning') }}">
                        {{ $document->status_name }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Uploaded File</th>
                <td>
                    @if ($document->getFirstMediaUrl('carrier_documents'))
                        <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" target="_blank" class="text-primary">
                            View File
                        </a>
                    @else
                        <span class="text-gray-500">No file uploaded</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Notes</th>
                <td>{{ $document->notes ?? 'No notes available' }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $document->date->format('Y-m-d') }}</td>
            </tr>
        </table>

        <form action="{{ route('admin.documents.process-review', [$carrier->slug, $document->id]) }}" method="POST" class="mt-5">
            @csrf
            <div class="form-group">
                <label for="status">Update Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="{{ \App\Models\CarrierDocument::STATUS_APPROVED }}">Approve</option>
                    <option value="{{ \App\Models\CarrierDocument::STATUS_REJECTED }}">Reject</option>
                </select>
                @error('status')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" class="form-control">{{ old('notes', $document->notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Update Document</button>
        </form>
    </div>
</div>
@endsection
