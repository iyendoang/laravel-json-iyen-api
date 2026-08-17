<?php

   namespace Tests\Api;

   use App\Models\Permission;

   class PermissionTest extends BaseApiTest
   {
      public function test_get_permissions_list(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/admin/permissions');
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
                  ],
               ],
            ])
            ->assertJson([
               'status'  => 'success',
               'message' => 'Daftar permission berhasil diambil',
            ]);
      }

      public function test_create_permission(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->postJson($this->baseUrl . '/admin/permissions', [
               'name' => 'view-reports',
            ]);
         $response
            ->assertStatus(201)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Permission berhasil dibuat',
               'data'    => [
                  'name' => 'view-reports',
               ],
            ]);
         $this->assertDatabaseHas('permissions', [
            'name' => 'view-reports',
         ]);
      }

      public function test_show_permission(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $permission = Permission::where('name', 'view-users')->first();
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/admin/permissions/' . $permission->id);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status' => 'success',
               'data'   => [
                  'name' => 'view-users',
               ],
            ]);
      }

      public function test_update_permission(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $permission = Permission::create([
            'name'       => 'test-permission',
            'guard_name' => 'api',
         ]);
         $response = $this
            ->withAuth($token)
            ->putJson($this->baseUrl . '/admin/permissions/' . $permission->id, [
               'name' => 'updated-permission',
            ]);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Permission berhasil diperbarui',
               'data'    => [
                  'name' => 'updated-permission',
               ],
            ]);
      }

      public function test_delete_permission(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $permission = Permission::create([
            'name'       => 'temp-permission',
            'guard_name' => 'api',
         ]);
         $response = $this
            ->withAuth($token)
            ->deleteJson($this->baseUrl . '/admin/permissions/' . $permission->id);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Permission berhasil dihapus',
            ]);
      }
   }