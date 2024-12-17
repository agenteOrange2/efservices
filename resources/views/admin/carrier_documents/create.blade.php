@extends('../themes/' . $activeTheme)
@section('title', 'Upload New Document')

@section('subcontent')
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Upload New Document for Carrier: {{ $carrier->name }}</h3>
    </div>
    <div class="box-body">
        <form action="{{ route('admin.carrier.documents.store', $carrier->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="document_type_id">Document Type</label>
                <select name="document_type_id" id="document_type_id" class="form-control">
                    @foreach ($documentTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('document_type_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="document">Upload Document</label>
                <input type="file" name="document" id="document" class="form-control">
                @error('document')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}">
                @error('date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Upload Document</button>
        </form>
    </div>
</div>
@endsection
