<?php

   namespace Database\Seeders;

   use App\Models\Permission;
   use App\Models\Role;
   use Illuminate\Database\Seeder;

   class RolePermissionSeeder extends Seeder
   {
      public function run(): void {
         // Reset cached roles and permissions
         app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
         // ============================================
         // Create Permissions
         // ============================================
         $permissions = [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
         ];
         foreach($permissions as $permission) {
            Permission::firstOrCreate([
               'name'       => $permission,
               'guard_name' => 'api',
            ]);
         }
         // ============================================
         // Create Roles
         // ============================================
         $roles = [
            'super-admin' => Permission::all()->pluck('name')->toArray(),
            'admin'       => [
               'view-users',
               'create-users',
               'edit-users',
               'delete-users',
               'view-roles',
               'create-roles',
               'edit-roles',
               'view-permissions',
               'create-permissions',
               'edit-permissions',
            ],
            'teacher'     => [
               'view-users',
               'edit-users',
               'view-roles',
            ],
         ];
         foreach($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
               'name'       => $roleName,
               'guard_name' => 'api',
            ]);
            $role->syncPermissions($rolePermissions);
         }
         $this->command->info('RolePermissionSeeder: ' . Permission::count() . ' permissions, ' . Role::count() . ' roles created successfully!');
      }
   }