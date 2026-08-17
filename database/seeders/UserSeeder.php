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
               'email' => 'superadmin@dev.id',
               'role'  => 'super-admin',
            ],
            [
               'name'  => 'Admin User',
               'email' => 'admin@dev.id',
               'role'  => 'admin',
            ],
            [
               'name'  => 'Teacher User',
               'email' => 'teacher@dev.id',
               'role'  => 'teacher',
            ],
            [
               'name'  => 'John Doe',
               'email' => 'john@dev.id',
               'role'  => 'teacher',
            ],
            [
               'name'  => 'Jane Smith',
               'email' => 'jane@dev.id',
               'role'  => 'teacher',
            ],
            [
               'name'  => 'Bob Johnson',
               'email' => 'bob@dev.id',
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
         $this->command->info('Super Admin: superadmin@dev.id / password');
         $this->command->info('Admin: admin@dev.id / password');
         $this->command->info('Teacher: teacher@dev.id / password');
         $this->command->info('Teacher: john@dev.id / password');
         $this->command->info('Teacher: jane@dev.id / password');
         $this->command->info('Teacher: bob@dev.id / password');
         $this->command->info('==================================================');
      }
   }