@extends('../themes/' . $activeTheme)

@section('title', 'Permissions')

@section('subcontent')
<div class="box box--stacked">
    <div class="p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Permissions</h2>
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">Create Permission</a>
        </div>

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                <tr>
                    <td>{{ $permission->id }}</td>
                    <td>{{ $permission->name }}</td>
                    <td>
                        <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="inline-block">
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
            {{ $permissions->links() }}
        </div>
    </div>
</div>
@endsection
