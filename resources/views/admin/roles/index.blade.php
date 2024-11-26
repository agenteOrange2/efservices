@extends('../themes/' . $activeTheme)

@section('title', 'Roles')

@php
    $breadcrumbLinks = [['label' => 'App', 'url' => route('admin.dashboard')], ['label' => 'Rols', 'active' => true]];
@endphp
@section('subcontent')

    <x-base.notificationtoast.notification-toast :notification="session('notification')" />

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Rols
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.roles.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New Roles
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">
                <livewire:generic-table 
                model="Spatie\Permission\Models\Role" 
                :columns="['name', 'created_at', 'updated_at']" 
                :searchableFields="['name']"
                editRoute="admin.roles.edit" 
                exportExcelRoute="admin.roles.export.excel"
                exportPdfRoute="admin.roles.export.pdf" 
            />
            </div>
        </div>
    </div>

    {{-- <div class="box box--stacked">
    <div class="p-6">
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
                @foreach ($roles as $role)
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
</div> --}}
@endsection
