<?php
   return [
      'models' => [
         'permission' => App\Models\Permission::class, // ← Harus ke model yang menggunakan HasUlids
         'role'       => App\Models\Role::class,       // ← Harus ke model yang menggunakan HasUlids
      ],
      'table_names' => [
         'roles'                 => 'roles',
         'permissions'           => 'permissions',
         'model_has_permissions' => 'model_has_permissions',
         'model_has_roles'       => 'model_has_roles',
         'role_has_permissions'  => 'role_has_permissions',
      ],
      'column_names' => [
         'role_pivot_key'       => NULL,
         'permission_pivot_key' => NULL,
         'model_morph_key'      => 'model_id',
         'team_foreign_key'     => 'team_id',
      ],
      'teams' => false,
      'display_permission_in_exception' => false,
      'display_role_in_exception' => false,
      'enable_wildcard_permission' => false,
      'cache' => [
         'expiration_time' => \DateInterval::createFromDateString('24 hours'),
         'key'             => 'spatie.permission.cache',
         'store'           => 'default',
      ],
   ];