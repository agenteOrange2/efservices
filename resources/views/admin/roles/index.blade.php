@extends('../themes/' . $activeTheme)

@section('title', 'Roles')

@section('subcontent')
<div class="box box--stacked">
    <div class="p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Roles</h2>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Create Role</a>
        </div>

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>{{ $role->name }}</td>
                    <td>{{ implode(', ', $role->permissions->pluck('name')->toArray()) }}</td>
                    <td>
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection
