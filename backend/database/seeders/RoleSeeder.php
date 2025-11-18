<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view orders',
            'create orders',
            'edit orders',
            'cancel orders',
            'view users',
            'manage users',
            'view dashboard',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $grocer = Role::create(['name' => 'grocer']);
        $grocer->givePermissionTo([
            'view products',
            'view orders',
            'create orders',
            'cancel orders',
        ]);

        $driver = Role::create(['name' => 'driver']);
        $driver->givePermissionTo([
            'view orders',
            'edit orders',
        ]);
    }
}
