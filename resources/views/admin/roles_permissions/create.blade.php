@extends('../themes/' . $activeTheme)

@section('title', 'Create Role')

@section('subcontent')
<div class="container">
    <h1>Create New Role</h1>

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Role Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="permissions">Permissions</label>
            @foreach ($permissions as $permission)
                <div>
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}">
                    <label>{{ $permission->name }}</label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Save Role</button>
    </form>
</div>
@endsection
