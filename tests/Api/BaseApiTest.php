<?php

   namespace Tests\Api;

   use App\Models\Permission;
   use App\Models\Role;
   use App\Models\User;
   use Illuminate\Foundation\Testing\RefreshDatabase;
   use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
   use Illuminate\Support\Facades\Hash;
   use Tests\CreatesApplication;

   abstract class BaseApiTest extends BaseTestCase
   {
      use CreatesApplication;
      use RefreshDatabase;

      protected string $baseUrl = '/api/v1';

      protected function setUp(): void {
         parent::setUp();
         $this->seedRolesAndPermissions();
      }

      protected function seedRolesAndPermissions(): void {
         // Reset cached roles and permissions
         app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
         // Create permissions
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
         // Create roles
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
      }

      protected function createUser(string $role = 'teacher', array $attributes = []): User {
         $user = User::create(array_merge([
            'name'     => 'Test User',
            'email'    => 'test_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
         ], $attributes));
         $user->syncRoles([$role]);

         return $user;
      }

      protected function createSuperAdmin(): User {
         return $this->createUser('super-admin', [
            'name'  => 'Super Admin',
            'email' => 'superadmin@example.com',
         ]);
      }

      protected function createAdmin(): User {
         return $this->createUser('admin', [
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
         ]);
      }

      protected function createTeacher(): User {
         return $this->createUser('teacher', [
            'name'  => 'Teacher User',
            'email' => 'teacher@example.com',
         ]);
      }

      protected function login(User $user): string {
         $response = $this->postJson($this->baseUrl . '/auth/login', [
            'email'    => $user->email,
            'password' => 'password123',
         ]);

         return $response->json('data.access_token');
      }

      protected function withAuth(string $token): self {
         return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
         ]);
      }
   }