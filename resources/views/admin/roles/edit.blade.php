@extends('../themes/' . $activeTheme)

@section('title', 'Edit Role')

@section('subcontent')
<div class="box box--stacked">
    <div class="p-6">
        <h2 class="text-lg font-semibold">Edit Role</h2>

        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Role Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $role->name }}" required>
                @error('name')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mt-4">
                <label for="permissions">Assign Permissions</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($permissions as $permission)
                    <div>
                        <label>
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                            {{ $permission->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Update</button>
        </form>
    </div>
</div>
@endsection
