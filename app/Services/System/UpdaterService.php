<?php

   namespace App\Services\System;

   use Illuminate\Support\Facades\Http;
   use Illuminate\Support\Facades\File;
   use Illuminate\Support\Facades\Artisan;
   use Illuminate\Support\Facades\Log;
   use Illuminate\Support\Facades\DB;
   use ZipArchive;

   class UpdaterService
   {
      // Folder sementara untuk menyimpan update
      protected $tempPath;

      public function __construct() {
         $this->tempPath = storage_path('app/temp_update');
      }

      /**
       * Helper: Ambil versi saat ini dari file version.json
       */
      public function getCurrentVersion() {
         $path = base_path('version.json');
         if(!File::exists($path)){
            return '0.0.0';
         }
         $json = json_decode(File::get($path), true);

         return $json['version'] ?? '0.0.0';
      }

      /**
       * Langkah 1: Download File ZIP
       */
      public function downloadUpdate($url) {
         // Bersihkan folder temp lama (handling permission)
         if(File::exists($this->tempPath)){
            try {
               File::deleteDirectory($this->tempPath);
            } catch(\Exception $e) {
               Log::warning("Gagal membersihkan directory temp lama: " . $e->getMessage());
            }
         }
         // Buat folder baru
         if(!File::exists($this->tempPath)){
            @mkdir($this->tempPath, 0755, true);
         }
         $zipPath = $this->tempPath . '/update.zip';
         // Download file dengan batasan waktu yang longgar (600 detik)
         $response = Http::timeout(600)->sink($zipPath)->get($url);
         if($response->failed()){
            throw new \Exception("Gagal mengunduh file berkas dari server pusat. HTTP Status: " . $response->status());
         }

         return $zipPath;
      }

      /**
       * Langkah 2: Ekstrak Berkas ZIP
       */
      public function extractUpdate($zipPath) {
         $zip = new ZipArchive;
         if($zip->open($zipPath) === true){
            $extractPath = $this->tempPath . '/extracted';
            if(!File::exists($extractPath)){
               @mkdir($extractPath, 0755, true);
            }
            $zip->extractTo($extractPath);
            $zip->close();

            return $extractPath;
         }
         else {
            throw new \Exception("File arsip ZIP rusak, korup, atau tidak bisa dibuka.");
         }
      }

      /**
       * Langkah 3: Eksekusi Overwrite File & Jalankan Migrasi
       */
      public function updateSystem($extractPath) {
         $oldVersion = $this->getCurrentVersion();
         // Ambil ID admin sebelum masuk ke mode maintenance agar session tidak hilang
         $adminId = auth()->id() ?? 1;
         // Aktifkan Mode Maintenance Keamanan
         Artisan::call('down', [
            '--secret' => 'admin-update-access',
            '--render' => 'errors.503',
         ]);
         try {
            // Daftar folder/file sensitif yang TIDAK BOLEH ditimpa
            $ignoredFiles = [
               '.env',
               'storage',
               '.git',
               'node_modules',
               'public/uploads',
               'public/storage',
            ];
            // Eksekusi penyalinan file core system secara rekursif
            $this->recursiveCopy($extractPath, base_path(), $ignoredFiles);
            // Eksekusi pembaruan struktur database
            Artisan::call('migrate', ['--force' => true]);
            // Bersihkan seluruh cache sistem secara menyeluruh
            Artisan::call('optimize:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            // Optimasi Hak Akses Folder Tulis (Best Effort)
            if(function_exists('exec')){
               try {
                  @exec('chmod -R 775 ' . storage_path());
                  @exec('chmod -R 775 ' . base_path('bootstrap/cache'));
               } catch(\Exception $e) {
               }
            }
            $newVersion = $this->getCurrentVersion();
            // Catat log kesuksesan ke dalam database
            try {
               DB::table('system_updates')->insert([
                  'version_from'      => $oldVersion,
                  'version_to'        => $newVersion,
                  'updated_at_server' => now(),
                  'updated_by'        => $adminId,
                  'status'            => 'success',
                  'log_details'       => "Update OTA Sukses dari v$oldVersion ke v$newVersion.",
                  'created_at'        => now(),
                  'updated_at'        => now(),
               ]);
            } catch(\Exception $e) {
               Log::error("Gagal mencatat histori update ke DB: " . $e->getMessage());
            }
            // Kembalikan aplikasi menjadi Online
            Artisan::call('up');
            // Bersihkan directory sisa unduhan zip
            File::deleteDirectory($this->tempPath);

            return true;
         } catch(\Exception $e) {
            // Jika crash di tengah jalan, pastikan aplikasi dipaksa naik online kembali
            Artisan::call('up');
            Log::error("Proses Eksekusi Overwrite Core Gagal: " . $e->getMessage());
            throw $e;
         }
      }

      /**
       * Helper: Copy file dengan Permission Handling
       */
      private function recursiveCopy($src, $dst, $ignored) {
         $dir = opendir($src);
         if(!file_exists($dst)){
            if(!@mkdir($dst, 0755, true))
               return;
         }
         while(false !== ($file = readdir($dir))) {
            if(($file != '.') && ($file != '..')){
               if(in_array($file, $ignored))
                  continue;
               $srcFile = $src . '/' . $file;
               $dstFile = $dst . '/' . $file;
               if(is_dir($srcFile)){
                  $this->recursiveCopy($srcFile, $dstFile, $ignored);
               }
               else {
                  // Proteksi keamanan file tertulis
                  if(file_exists($dstFile) && !is_writable($dstFile)){
                     throw new \Exception("Akses Tulis Ditolak: File berkas $dstFile terkunci oleh hak akses root server.");
                  }
                  if(!copy($srcFile, $dstFile)){
                     throw new \Exception("Gagal menyalin file sistem baru ke tujuan: $dstFile");
                  }
                  @chmod($dstFile, 0644);
               }
            }
         }
         closedir($dir);
      }
   }