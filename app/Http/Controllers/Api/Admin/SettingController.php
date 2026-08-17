<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Models\Setting;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Storage;
   use Intervention\Image\Drivers\Gd\Driver;
   use Intervention\Image\ImageManager;

   class SettingController extends Controller
   {
      /**
       * Handle file upload dengan kompresi dan resize
       */
      private function handleFileUpload($file, ?string $oldPath = NULL, string $dirName = 'landing'): ?string {
         // Jika file kosong, tetap gunakan path lama
         if(!$file){
            return $oldPath;
         }
         // Jika file bukan base64 baru (hanya string path), return file itu sendiri
         if(!is_string($file) || !str_contains($file, 'data:image')){
            return $file;
         }
         // Hapus file lama jika ada upload baru
         if($oldPath && Storage::disk('public')->exists($oldPath)){
            Storage::disk('public')->delete($oldPath);
         }
         $manager = new ImageManager(new Driver());
         $nameGen = hexdec(uniqid()) . '.webp';
         // Proses gambar
         $img = $manager->read($file);
         // Resize: Ilustrasi biasanya lebih besar dari Logo
         $width = ($dirName === 'brand') ? 300 : 800;
         $img->scale(width:$width);
         $encoded = $img->toWebp(80);
         $path    = "images/{$dirName}/" . $nameGen;
         // Intervention v3: gunakan ->toString() atau cast string
         Storage::disk('public')->put($path, (string) $encoded);

         return $path;
      }

      /**
       * Get all settings
       */
      public function index(): JsonResponse {
         try {
            $settingsData      = Setting::all();
            $formattedSettings = [];
            foreach($settingsData as $setting) {
               // Sinkronisasi URL untuk logo
               if($setting->key === 'app_logo' && $setting->value && !str_starts_with($setting->value, 'http')){
                  $formattedSettings[$setting->key]  = asset('storage/' . $setting->value);
                  $formattedSettings['app_logo_raw'] = $setting->value;
               }
               else {
                  $formattedSettings[$setting->key] = $setting->value;
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
            'settings' => 'required|array',
         ]);
         try {
            // 1. Ambil data teks pengaturan
            $settingsPayload = $request->input('settings');
            // 2. Proses upload logo adaptif
            $logoFile = NULL;
            if($request->hasFile('app_logo')){
               $logoFile = $request->file('app_logo');
            }
            else if($request->hasFile('settings.app_logo')) {
               $logoFile = $request->file('settings.app_logo');
            }
            // Jika file logo terdeteksi
            if($logoFile){
               // Hapus logo lama
               $oldLogoSetting = Setting::where('key', 'app_logo')->first();
               if($oldLogoSetting && $oldLogoSetting->value){
                  Storage::disk('public')->delete($oldLogoSetting->value);
               }
               // Upload logo baru dengan kompresi
               $path                        = $this->handleFileUpload($logoFile, NULL, 'brand');
               $settingsPayload['app_logo'] = $path;
            }
            // 3. Simpan seluruh data pengaturan
            foreach($settingsPayload as $key => $value) {
               // Abaikan token helper internal
               if($key === 'app_logo_raw'){
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