@extends('../themes/' . $activeTheme)

@section('title', 'Edit Role')

@section('subcontent')
<div class="container">
    <h1>Edit Role: {{ $role->name }}</h1>

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Role Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $role->name }}" required>
        </div>

        <div class="form-group">
            <label for="permissions">Permissions</label>
            @foreach ($permissions as $permission)
                <div>
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                           {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}>
                    <label>{{ $permission->name }}</label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Update Role</button>
    </form>
</div>
@endsection
