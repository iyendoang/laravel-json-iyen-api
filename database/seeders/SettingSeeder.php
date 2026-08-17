<?php

   namespace Database\Seeders;

   use App\Models\Setting;
   use Illuminate\Database\Seeder;

   class SettingSeeder extends Seeder
   {
      public function run(): void {
         $settings = [
            'app_name'        => 'Laravel API',
            'app_logo'        => NULL,
            'app_description' => 'Backend REST API dengan ULID, JWT, dan Spatie Permission',
            'app_email'       => 'admin@example.com',
            'app_phone'       => '08123456789',
            'app_address'     => 'Jl. Contoh No. 123',
            'footer_text'     => '© 2026 Laravel API. All rights reserved.',
         ];
         foreach($settings as $key => $value) {
            Setting::updateOrCreate(
               ['key' => $key],
               ['value' => $value]
            );
         }
      }
   }