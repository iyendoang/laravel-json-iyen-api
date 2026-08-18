<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Models\Setting;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Http\Request;
   use Illuminate\Http\UploadedFile;
   use Illuminate\Support\Facades\Storage;
   use Intervention\Image\Drivers\Gd\Driver;
   use Intervention\Image\Encoders\WebpEncoder;
   use Intervention\Image\ImageManager;

   class SettingController extends Controller
   {
      /**
       * Daftar key gambar yang memerlukan konversi URL publik & upload handler
       */
      private array $imageKeys = [
         'app_logo'    => ['dir' => 'brand', 'width' => 300],
         'app_favicon' => ['dir' => 'brand', 'width' => 128],
         'hero_image'  => ['dir' => 'landing', 'width' => 1200],
         'about_image' => ['dir' => 'landing', 'width' => 800],
      ];

      /**
       * Handle file upload dengan kompresi dan resize (Intervention Image v4)
       */
      private function handleFileUpload($file, ?string $oldPath = NULL, string $dirName = 'landing', int $width = 800): ?string {
         if(!$file){
            return $oldPath;
         }
         $isUploadedFile = $file instanceof UploadedFile;
         $isBase64       = is_string($file) && str_contains($file, 'data:image');
         if(!$isUploadedFile && !$isBase64){
            return is_string($file) ? $file : $oldPath;
         }
         // Hapus file lama jika ada
         if($oldPath && Storage::disk('public')->exists($oldPath)){
            Storage::disk('public')->delete($oldPath);
         }
         // Inisialisasi ImageManager dengan Driver GD
         $manager = new ImageManager(new Driver());
         $nameGen = hexdec(uniqid()) . '.webp';
         // Baca file gambar menggunakan decoder v4
         if($isUploadedFile){
            $img = $manager->decodePath($file->getRealPath());
         }
         else if($isBase64) {
            $img = $manager->decodeDataUri($file);
         }
         else {
            $img = $manager->decode($file);
         }
         // Resize dimensi sesuai peruntukan
         $img->scale(width:$width);
         // Encode ke format WebP kualitas 80% menggunakan WebpEncoder
         $encoded = $img->encode(new WebpEncoder(quality:80));
         $path    = "images/{$dirName}/" . $nameGen;
         // Simpan ke storage disk public
         Storage::disk('public')->put($path, (string) $encoded);

         return $path;
      }

      /**
       * Default list fields company profile agar API selalu mengembalikan struktur yang rapi dan konsisten
       */
      private function getDefaultSettings(): array {
         return [
            // Identitas Brand & Aplikasi
            'app_name'          => 'Laravel API',
            'app_slogan'        => '',
            'company_name'      => '',
            'company_tagline'   => '',
            'app_description'   => '',
            'about_us'          => '',
            'vision'            => '',
            'mission'           => '',
            // Asset Visual / Media
            'app_logo'          => NULL,
            'app_favicon'       => NULL,
            'hero_image'        => NULL,
            'about_image'       => NULL,
            // Kontak & Lokasi
            'contact_email'     => '',
            'contact_phone'     => '',
            'contact_whatsapp'  => '',
            'contact_address'   => '',
            'google_maps_embed' => '',
            'working_hours'     => 'Senin - Jumat: 08:00 - 17:00 WIB',
            // Media Sosial
            'social_facebook'   => '',
            'social_instagram'  => '',
            'social_twitter_x'  => '',
            'social_linkedin'   => '',
            'social_youtube'    => '',
            'social_tiktok'     => '',
            'social_github'     => '',
            // Legalitas & Footer
            'company_nib'       => '',
            'company_npwp'      => '',
            'footer_text'       => '© 2026 Laravel API. All rights reserved.',
         ];
      }

      /**
       * Get all settings
       */
      public function index(): JsonResponse {
         try {
            $settingsData = Setting::all()->pluck('value', 'key')->toArray();
            $defaults     = $this->getDefaultSettings();
            // Gabungkan default dengan data dari database
            $mergedSettings    = array_merge($defaults, $settingsData);
            $formattedSettings = [];
            foreach($mergedSettings as $key => $value) {
               // Jika merupakan key gambar, buatkan path absolut dan raw path
               if(array_key_exists($key, $this->imageKeys)){
                  if($value && !str_starts_with($value, 'http')){
                     $formattedSettings[$key]          = asset('storage/' . $value);
                     $formattedSettings[$key . '_raw'] = $value;
                  }
                  else {
                     $formattedSettings[$key]          = $value;
                     $formattedSettings[$key . '_raw'] = $value;
                  }
               }
               else {
                  $formattedSettings[$key] = $value;
               }
            }

            return $this->responseSuccess('Pengaturan sistem berhasil dimuat', $formattedSettings);
         } catch(\Exception $e) {
            return $this->responseError('Gagal memuat pengaturan sistem: ' . $e->getMessage(), 500);
         }
      }

      /**
       * Update settings
       */
      public function store(Request $request): JsonResponse {
         $request->validate([
            'settings' => 'required',
         ]);
         try {
            // Ambil payload settings (bisa berupa array JSON atau string JSON dari FormData)
            $settingsPayload = $request->input('settings');
            if(is_string($settingsPayload)){
               $decoded = json_decode($settingsPayload, true);
               if(is_array($decoded)){
                  $settingsPayload = $decoded;
               }
            }
            if(!is_array($settingsPayload)){
               $settingsPayload = [];
            }
            // Tangani upload untuk semua gambar yang terdaftar (app_logo, app_favicon, hero_image, about_image)
            foreach($this->imageKeys as $imgKey => $config) {
               $oldSetting = Setting::where('key', $imgKey)->first();
               $oldPath    = $oldSetting ? $oldSetting->value : NULL;
               $file = NULL;
               if($request->hasFile($imgKey)){
                  $file = $request->file($imgKey);
               }
               else if($request->hasFile("settings.{$imgKey}")) {
                  $file = $request->file("settings.{$imgKey}");
               }
               else if(
                  isset($settingsPayload[$imgKey])
                  && is_string($settingsPayload[$imgKey])
                  && str_contains($settingsPayload[$imgKey], 'data:image')
               ) {
                  $file = $settingsPayload[$imgKey];
               }
               if($file){
                  $newPath                  = $this->handleFileUpload($file, $oldPath, $config['dir'], $config['width']);
                  $settingsPayload[$imgKey] = $newPath;
               }
            }
            // Simpan seluruh data ke database
            foreach($settingsPayload as $key => $value) {
               // Abaikan helper key internal (*_raw)
               if(str_ends_with($key, '_raw')){
                  continue;
               }
               Setting::updateOrCreate(
                  ['key' => $key],
                  ['value' => $value]
               );
            }

            return $this->responseSuccess('Perubahan pengaturan sistem berhasil disimpan!');
         } catch(\Exception $e) {
            return $this->responseError('Gagal memperbarui pengaturan sistem: ' . $e->getMessage(), 500);
         }
      }
   }