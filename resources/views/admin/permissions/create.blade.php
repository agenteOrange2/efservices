@extends('../themes/' . $activeTheme)

@section('title', 'Create Permission')

@section('subcontent')
<div class="box box--stacked">
    <div class="p-6">
        <h2 class="text-lg font-semibold">Create Permission</h2>

        <form action="{{ route('admin.permissions.store') }}" method="POST" class="mt-4">
            @csrf
            <div class="form-group">
                <label for="name">Permission Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
                @error('name')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary mt-4">Create</button>
        </form>
    </div>
</div>
@endsection
