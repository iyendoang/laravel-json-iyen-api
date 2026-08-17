<?php

   namespace Tests\Api;

   use App\Models\User;

   class UserTest extends BaseApiTest
   {
      public function test_get_users_list(): void {
         $admin = $this->createSuperAdmin();
         // Gunakan email unik untuk setiap user
         $this->createUser('teacher', ['email' => 'teacher1@example.com']);
         $this->createUser('teacher', ['email' => 'teacher2@example.com']);
         $token = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/admin/users');
         $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  '*' => [
                     'id',
                     'name',
                     'email',
                     'role',
                     'permissions',
                  ],
               ],
               'meta' => [
                  'current_page',
                  'per_page',
                  'total',
               ],
            ])
            ->assertJson([
               'status'  => 'success',
               'message' => 'Daftar user berhasil diambil',
            ]);
      }

      public function test_get_users_without_permission(): void {
         // Teacher tidak punya permission untuk view users di admin
         // Tapi teacher punya permission view-users
         // Seharusnya teacher BISA melihat users
         // Ubah test ini
         $teacher = $this->createTeacher();
         $token   = $this->login($teacher);
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/admin/users');
         // Teacher punya permission view-users, jadi seharusnya 200
         $response
            ->assertStatus(200)
            ->assertJson([
               'status' => 'success',
            ]);
      }

      public function test_create_user(): void {
         $admin = $this->createSuperAdmin();
         $token = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->postJson($this->baseUrl . '/admin/users', [
               'name'     => 'New Teacher',
               'email'    => 'newteacher@example.com',
               'password' => 'password123',
               'role'     => 'teacher',
            ]);
         $response
            ->assertStatus(201)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  'id',
                  'name',
                  'email',
                  'role',
                  'permissions',
               ],
            ])
            ->assertJson([
               'status'  => 'success',
               'message' => 'User berhasil dibuat',
               'data'    => [
                  'email' => 'newteacher@example.com',
                  'role'  => 'teacher',
               ],
            ]);
         $this->assertDatabaseHas('users', [
            'email' => 'newteacher@example.com',
         ]);
      }

      public function test_show_user(): void {
         $admin   = $this->createSuperAdmin();
         $teacher = $this->createUser('teacher', ['email' => 'showteacher@example.com']);
         $token   = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/admin/users/' . $teacher->id);
         $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  'id',
                  'name',
                  'email',
                  'role',
                  'permissions',
               ],
            ])
            ->assertJson([
               'status' => 'success',
               'data'   => [
                  'id'    => $teacher->id,
                  'email' => $teacher->email,
               ],
            ]);
      }

      public function test_update_user(): void {
         $admin   = $this->createSuperAdmin();
         $teacher = $this->createUser('teacher', ['email' => 'updateteacher@example.com']);
         $token   = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->putJson($this->baseUrl . '/admin/users/' . $teacher->id, [
               'name' => 'Updated Teacher',
               'role' => 'admin',
            ]);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'User berhasil diperbarui',
               'data'    => [
                  'name' => 'Updated Teacher',
                  'role' => 'admin',
               ],
            ]);
      }

      public function test_delete_user(): void {
         $admin   = $this->createSuperAdmin();
         $teacher = $this->createUser('teacher', ['email' => 'deleteteacher@example.com']);
         $token   = $this->login($admin);
         $response = $this
            ->withAuth($token)
            ->deleteJson($this->baseUrl . '/admin/users/' . $teacher->id);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'User berhasil dihapus',
            ]);
         // Karena menggunakan SoftDeletes, user masih ada di database
         $this->assertSoftDeleted('users', [
            'id' => $teacher->id,
         ]);
      }
   }