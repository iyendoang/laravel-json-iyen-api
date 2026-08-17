<?php

   namespace Tests\Api;

   use App\Models\User;
   use Illuminate\Support\Facades\Hash;

   class AuthTest extends BaseApiTest
   {
      public function test_register_user(): void {
         $response = $this->postJson($this->baseUrl . '/auth/register', [
            'name'                  => 'New User',
            'email'                 => 'newuser@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
         ]);
         // Controller sekarang mengembalikan 201
         $response
            ->assertStatus(201)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  'user' => [
                     'id',
                     'name',
                     'email',
                     'role',
                     'permissions',
                  ],
                  'access_token',
                  'token_type',
                  'expires_in',
               ],
            ])
            ->assertJson([
               'status'  => 'success',
               'message' => 'Registrasi berhasil',
            ]);
         $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
         ]);
      }

      public function test_register_validation_error(): void {
         $response = $this->postJson($this->baseUrl . '/auth/register', [
            'name'     => '',
            'email'    => 'invalid-email',
            'password' => 'short',
         ]);
         $response
            ->assertStatus(422)
            ->assertJsonStructure([
               'status',
               'message',
               'errors' => [
                  'name',
                  'email',
                  'password',
               ],
            ])
            ->assertJson([
               'status' => 'error',
            ]);
      }

      public function test_login_success(): void {
         $user     = $this->createSuperAdmin();
         $response = $this->postJson($this->baseUrl . '/auth/login', [
            'email'    => $user->email,
            'password' => 'password123',
         ]);
         $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  'user',
                  'access_token',
                  'token_type',
                  'expires_in',
               ],
            ])
            ->assertJson([
               'status'  => 'success',
               'message' => 'Login berhasil',
            ]);
      }

      public function test_login_failed(): void {
         $user     = $this->createSuperAdmin();
         $response = $this->postJson($this->baseUrl . '/auth/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
         ]);
         $response
            ->assertStatus(401)
            ->assertJson([
               'status'  => 'error',
               'message' => 'Email atau password salah',
            ]);
      }

      public function test_get_me(): void {
         $user     = $this->createSuperAdmin();
         $token    = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/auth/me');
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
                  'id'    => $user->id,
                  'email' => $user->email,
               ],
            ]);
      }

      public function test_refresh_token(): void {
         $user     = $this->createSuperAdmin();
         $token    = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->postJson($this->baseUrl . '/auth/refresh');
         $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  'access_token',
                  'token_type',
                  'expires_in',
               ],
            ]);
      }

      public function test_logout(): void {
         $user     = $this->createSuperAdmin();
         $token    = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->postJson($this->baseUrl . '/auth/logout');
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Logout berhasil',
            ]);
      }
   }