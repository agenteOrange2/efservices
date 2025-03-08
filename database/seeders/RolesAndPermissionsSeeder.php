<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run() : void
    {
        // Resetear caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos (opcional)
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage carriers']);
        Permission::create(['name' => 'manage drivers']);

        // Crear roles y asignar permisos
        $superAdmin = Role::create(['name' => 'superadmin']);
        $superAdmin->givePermissionTo(Permission::all());

        $carrierAdmin = Role::create(['name' => 'user_carrier']);
        $carrierAdmin->givePermissionTo(['manage carriers']);

        $driver = Role::create(['name' => 'driver']);
        $driver->givePermissionTo(['manage drivers']);

        // Asignar un usuario al rol de superadmin (ajusta el ID según tu base de datos)
        $user = \App\Models\User::find(1);
        $user->assignRole('superadmin');
    }
}
