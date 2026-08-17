<?php

   namespace Tests\Api;

   use App\Models\Role;

   class RoleTest extends BaseApiTest
   {
      public function test_get_roles_list(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/admin/roles');
         $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  '*' => [
                     'id',
                     'name',
                     'guard_name',
                     'permissions',
                  ],
               ],
            ])
            ->assertJson([
               'status'  => 'success',
               'message' => 'Daftar role berhasil diambil',
            ]);
      }

      public function test_create_role(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->postJson($this->baseUrl . '/admin/roles', [
               'name'        => 'student',
               'permissions' => ['view-users', 'view-roles'],
            ]);
         $response
            ->assertStatus(201)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Role berhasil dibuat',
               'data'    => [
                  'name'        => 'student',
                  'permissions' => ['view-users', 'view-roles'],
               ],
            ]);
         $this->assertDatabaseHas('roles', [
            'name' => 'student',
         ]);
      }

      public function test_show_role(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $role = Role::where('name', 'admin')->first();
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/admin/roles/' . $role->id);
         $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  'id',
                  'name',
                  'permissions',
               ],
            ])
            ->assertJson([
               'status' => 'success',
               'data'   => [
                  'name' => 'admin',
               ],
            ]);
      }

      public function test_update_role(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $role = Role::create([
            'name'       => 'test-role',
            'guard_name' => 'api',
         ]);
         $response = $this
            ->withAuth($token)
            ->putJson($this->baseUrl . '/admin/roles/' . $role->id, [
               'name'        => 'updated-role',
               'permissions' => ['view-users'],
            ]);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Role berhasil diperbarui',
               'data'    => [
                  'name'        => 'updated-role',
                  'permissions' => ['view-users'],
               ],
            ]);
      }

      public function test_delete_role(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $role = Role::create([
            'name'       => 'temp-role',
            'guard_name' => 'api',
         ]);
         $response = $this
            ->withAuth($token)
            ->deleteJson($this->baseUrl . '/admin/roles/' . $role->id);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Role berhasil dihapus',
            ]);
      }
   }