<?php

   namespace Tests\Api;

   use Illuminate\Http\UploadedFile;
   use Illuminate\Support\Facades\Storage;

   class ProfileTest extends BaseApiTest
   {
      public function test_get_profile(): void {
         $user  = $this->createTeacher();
         $token = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->getJson($this->baseUrl . '/profile');
         $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'status',
               'message',
               'data' => [
                  'id',
                  'name',
                  'email',
                  'avatar',
                  'phone',
                  'bio',
                  'address',
                  'city',
                  'country',
                  'postal_code',
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

      public function test_update_profile(): void {
         $user  = $this->createTeacher();
         $token = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->putJson($this->baseUrl . '/profile', [
               'name'        => 'Updated Name',
               'phone'       => '08123456789',
               'bio'         => 'This is my bio',
               'address'     => 'Jl. Test No. 1',
               'city'        => 'Jakarta',
               'country'     => 'Indonesia',
               'postal_code' => '12345',
            ]);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Profile berhasil diperbarui',
               'data'    => [
                  'name'    => 'Updated Name',
                  'phone'   => '08123456789',
                  'bio'     => 'This is my bio',
                  'city'    => 'Jakarta',
                  'country' => 'Indonesia',
               ],
            ]);
      }

      public function test_update_password(): void {
         $user  = $this->createTeacher();
         $token = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->putJson($this->baseUrl . '/profile/password', [
               'current_password'          => 'password123',
               'new_password'              => 'newpassword123',
               'new_password_confirmation' => 'newpassword123',
            ]);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Password berhasil diperbarui',
            ]);
      }

      public function test_update_password_wrong_current(): void {
         $user  = $this->createTeacher();
         $token = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->putJson($this->baseUrl . '/profile/password', [
               'current_password'          => 'wrongpassword',
               'new_password'              => 'newpassword123',
               'new_password_confirmation' => 'newpassword123',
            ]);
         $response
            ->assertStatus(422)
            ->assertJsonStructure([
               'status',
               'message',
               'errors' => [
                  'current_password',
               ],
            ]);
      }

      public function test_update_avatar(): void {
         Storage::fake('public');
         $user  = $this->createTeacher();
         $token = $this->login($user);
         $file = UploadedFile::fake()->image('avatar.jpg');
         $response = $this
            ->withAuth($token)
            ->postJson($this->baseUrl . '/profile/avatar', [
               'avatar' => $file,
            ]);
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Avatar berhasil diperbarui',
            ]);
      }

      public function test_delete_avatar(): void {
         Storage::fake('public');
         $user  = $this->createTeacher();
         $token = $this->login($user);
         $response = $this
            ->withAuth($token)
            ->deleteJson($this->baseUrl . '/profile/avatar');
         $response
            ->assertStatus(200)
            ->assertJson([
               'status'  => 'success',
               'message' => 'Avatar berhasil dihapus',
            ]);
      }
   }