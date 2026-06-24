<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'view monitoring',
            'view devices',
            'view readings',
            'manage devices',
            'manage users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $superadmin = Role::firstOrCreate(['name' => "super_admin"]);
        $operator = Role::firstOrCreate(['name' => "operator"]);

        $superadmin->syncPermissions($permissions);
        $operator->syncPermissions([
            'view dashboard',
            'view monitoring',
            'view devices',
            'view readings',
        ]);
    }
}
