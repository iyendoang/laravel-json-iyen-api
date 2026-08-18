<?php

   namespace Database\Seeders;

   use App\Models\Setting;
   use Illuminate\Database\Seeder;

   class SettingSeeder extends Seeder
   {
      public function run(): void {
         $settings = [
            'app_name'         => 'PT Inovasi Digital Nusantara',
            'app_slogan'       => 'Solusi Transformasi Digital Terdepan',
            'company_name'     => 'PT Inovasi Digital Nusantara',
            'company_tagline'  => 'Empowering Businesses with Scalable Technology',
            'app_description'  => 'Penyedia layanan solusi TI, pengembangan aplikasi web & mobile terintegrasi.',
            'about_us'         => 'Didirikan dengan komitmen memberikan solusi rekayasa perangkat lunak berstandar global.',
            'vision'           => 'Menjadi mitra teknologi terpercaya nomor satu di Asia Tenggara.',
            'mission'          => 'Menghadirkan produk berkualitas tinggi, aman, dan berorientasi pada kepuasan klien.',
            'contact_email'    => 'info@nusantaradigital.com',
            'contact_phone'    => '+62 21 555 1234',
            'contact_whatsapp' => '+62 812 3456 7890',
            'contact_address'  => 'Gedung Menara Cyber Lt. 15, Jl. Rasuna Said, Jakarta Selatan',
            'working_hours'    => 'Senin - Jumat: 08:30 - 17:30 WIB',
            'social_facebook'  => 'https://facebook.com/nusantaradigital',
            'social_instagram' => 'https://instagram.com/nusantaradigital',
            'social_linkedin'  => 'https://linkedin.com/company/nusantaradigital',
            'footer_text'      => '© 2026 PT Inovasi Digital Nusantara. All rights reserved.',
         ];
         foreach($settings as $key => $value) {
            Setting::updateOrCreate(
               ['key' => $key],
               ['value' => $value]
            );
         }
      }
   }