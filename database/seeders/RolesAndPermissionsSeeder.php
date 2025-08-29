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

        // Crear permisos para usuarios
        Permission::firstOrCreate(['name' => 'view users']);
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'edit users']);
        Permission::firstOrCreate(['name' => 'delete users']);
        
        // Crear permisos para roles y permisos
        Permission::firstOrCreate(['name' => 'view roles']);
        Permission::firstOrCreate(['name' => 'create roles']);
        Permission::firstOrCreate(['name' => 'edit roles']);
        Permission::firstOrCreate(['name' => 'delete roles']);
        Permission::firstOrCreate(['name' => 'assign permissions']);
        
        // Crear permisos para carriers
        Permission::firstOrCreate(['name' => 'view carriers']);
        Permission::firstOrCreate(['name' => 'create carriers']);
        Permission::firstOrCreate(['name' => 'edit carriers']);
        Permission::firstOrCreate(['name' => 'delete carriers']);
        Permission::firstOrCreate(['name' => 'manage carrier documents']);
        
        // Crear permisos para drivers
        Permission::firstOrCreate(['name' => 'view drivers']);
        Permission::firstOrCreate(['name' => 'create drivers']);
        Permission::firstOrCreate(['name' => 'edit drivers']);
        Permission::firstOrCreate(['name' => 'delete drivers']);
        Permission::firstOrCreate(['name' => 'manage driver documents']);
        
        // Crear permisos para dashboard
        Permission::firstOrCreate(['name' => 'view admin dashboard']);
        Permission::firstOrCreate(['name' => 'view carrier dashboard']);
        Permission::firstOrCreate(['name' => 'view driver dashboard']);
        
        // Permisos para infracciones de tráfico
        Permission::firstOrCreate(['name' => 'view traffic violations']);
        Permission::firstOrCreate(['name' => 'create traffic violations']);
        Permission::firstOrCreate(['name' => 'edit traffic violations']);
        Permission::firstOrCreate(['name' => 'delete traffic violations']);
        
        // Permisos para verificaciones de vehículos
        Permission::firstOrCreate(['name' => 'manage vehicle verifications']);
        
        // Crear roles y asignar permisos
        $superAdmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superAdmin->givePermissionTo(Permission::all());

        $carrierAdmin = Role::firstOrCreate(['name' => 'user_carrier']);
        $carrierAdmin->givePermissionTo([
            'view carriers',
            'edit carriers',
            'manage carrier documents',
            'view drivers',
            'create drivers',
            'edit drivers',
            'view carrier dashboard',
            'view traffic violations',
            'create traffic violations',
            'edit traffic violations'
        ]);

        $driver = Role::firstOrCreate(['name' => 'user_driver']);
        $driver->givePermissionTo([
            'view driver dashboard',
            'view drivers',
            'edit drivers',
            'manage driver documents'
        ]);
        
        // Asignar un usuario al rol de superadmin si existe
        $user = \App\Models\User::find(1);
        if ($user) {
            $user->assignRole('superadmin');
        }
    }
}
