<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    // Listar roles y permisos
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('admin.roles_permissions.index', compact('roles', 'permissions'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles_permissions.create', compact('permissions'));
    }

    // Guardar un nuevo rol
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    // Mostrar formulario de edición
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('admin.roles_permissions.edit', compact('role', 'permissions'));
    }

    // Actualizar un rol
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    // Eliminar un rol
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
