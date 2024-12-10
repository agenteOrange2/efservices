<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::paginate(10);
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
        ]);

        $permission = Permission::create(['name' => $validated['name']]);

        // Mensaje dinámico para la notificación
        return redirect()
            ->route('admin.permissions.edit', $permission->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'Permission created',
                'details' => 'Created successfully.',
            ]);        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id . '|max:255',
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()
        ->route('admin.permissions.edit', $permission->id)
        ->with('notification', [
            'type' => 'success',
            'message' => 'Permission updated',
            'details' => 'Permission updated successfully.',
        ]);        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
