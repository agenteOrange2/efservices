@extends('../themes/' . $activeTheme)

@section('title', 'Edit Permission')

@section('subcontent')
<div class="box box--stacked">
    <div class="p-6">
        <h2 class="text-lg font-semibold">Edit Permission</h2>

        <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Permission Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $permission->name }}" required>
                @error('name')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary mt-4">Update</button>
        </form>
    </div>
</div>
@endsection
