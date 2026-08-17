<?php

   namespace Database\Seeders;

   use App\Models\User;
   use Illuminate\Database\Seeder;
   use Illuminate\Support\Facades\Hash;

   class UserSeeder extends Seeder
   {
      public function run(): void {
         $users = [
            [
               'name'  => 'Super Admin',
               'email' => 'superadmin@example.com',
               'role'  => 'super-admin',
            ],
            [
               'name'  => 'Admin User',
               'email' => 'admin@example.com',
               'role'  => 'admin',
            ],
            [
               'name'  => 'Teacher User',
               'email' => 'teacher@example.com',
               'role'  => 'teacher',
            ],
            [
               'name'  => 'John Doe',
               'email' => 'john@example.com',
               'role'  => 'teacher',
            ],
            [
               'name'  => 'Jane Smith',
               'email' => 'jane@example.com',
               'role'  => 'teacher',
            ],
            [
               'name'  => 'Bob Johnson',
               'email' => 'bob@example.com',
               'role'  => 'teacher',
            ],
         ];
         foreach($users as $userData) {
            $user = User::firstOrCreate(
               ['email' => $userData['email']],
               [
                  'name'              => $userData['name'],
                  'password'          => Hash::make('password'),
                  'email_verified_at' => now(),
               ]
            );
            $user->syncRoles([$userData['role']]);
         }
         $this->command->info('==================================================');
         $this->command->info('UserSeeder berhasil dijalankan!');
         $this->command->info('==================================================');
         $this->command->info('Total Users: ' . User::count());
         $this->command->info('--------------------------------------------------');
         $this->command->info('Akun Test:');
         $this->command->info('Super Admin: superadmin@example.com / password');
         $this->command->info('Admin: admin@example.com / password');
         $this->command->info('Teacher: teacher@example.com / password');
         $this->command->info('Teacher: john@example.com / password');
         $this->command->info('Teacher: jane@example.com / password');
         $this->command->info('Teacher: bob@example.com / password');
         $this->command->info('==================================================');
      }
   }