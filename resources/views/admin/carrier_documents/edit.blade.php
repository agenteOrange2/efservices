@extends('../themes/' . $activeTheme)
@section('title', 'Edit Document ' . $document->documentType->name)

@section('subcontent')
    <div class="box box--stacked">
        <div class="box-body">
            <form action="{{ route('admin.carrier.documents.update', ['carrier' => $document->carrier->slug, 'document' => $document->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="p-5">
                    <!-- Tipo de documento -->
                    <div class="form-group">
                        <label for="document_type">Document Type</label>
                        <input type="text" class="form-control" id="document_type" value="{{ $document->documentType->name }}" disabled>
                    </div>

                    <!-- Estado -->
                    <div class="form-group">
                        <label for="status">Document Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="{{ \App\Models\CarrierDocument::STATUS_PENDING }}" {{ $document->status == \App\Models\CarrierDocument::STATUS_PENDING ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="{{ \App\Models\CarrierDocument::STATUS_APPROVED }}" {{ $document->status == \App\Models\CarrierDocument::STATUS_APPROVED ? 'selected' : '' }}>
                                Approved
                            </option>
                            <option value="{{ \App\Models\CarrierDocument::STATUS_REJECTED }}" {{ $document->status == \App\Models\CarrierDocument::STATUS_REJECTED ? 'selected' : '' }}>
                                Rejected
                            </option>
                        </select>
                    </div>

                    <!-- Archivo del documento -->
                    <div class="form-group">
                        <label for="document">Document File</label>
                        @if ($document->filename)
                            <a href="{{ asset('storage/' . $document->filename) }}" target="_blank" class="btn btn-link">View Document</a>
                        @endif
                        <input type="file" class="form-control" name="document" id="document">
                    </div>

                    <!-- Botón de envío -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
