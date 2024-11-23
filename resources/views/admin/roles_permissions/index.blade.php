@extends('../themes/' . $activeTheme)

@section('title', 'Roles and Permissions')

@php
    $breadcrumbLinks = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],        
        ['label' => 'Roles and Permissions', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container">
    <h1>Roles and Permissions</h1>

    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Create New Role</a>

    <h2>Roles</h2>

    <livewire:generic-table model="App\Models\User" :columns="['name', 'email', 'status', 'created_at', 'updated_at']" :searchableFields="['name', 'email','created_at']" />
    {{-- <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Permissions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->permissions->pluck('name')->join(', ') }}</td>
                    <td>
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table> --}}

    <h2>Permissions</h2>
    <ul>
        @foreach ($permissions as $permission)
            <li>{{ $permission->name }}</li>
        @endforeach
    </ul>
</div>
@endsection
