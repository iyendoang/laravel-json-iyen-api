<?php

   namespace App\Http\Controllers\Api\System;

   use App\Http\Controllers\Controller;
   use App\Models\SystemUpdate;
   use App\Services\System\UpdaterService;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Http;
   use Illuminate\Support\Facades\Log;

   class SystemUpdateController extends Controller
   {
      protected $updater;

      public function __construct(UpdaterService $updater) {
         $this->updater = $updater;
      }

      /**
       * Cek Update ke Server Pusat
       */
      public function checkUpdate() {
         if($error = check_permission('update-check', 'Anda tidak memiliki izin untuk check update')){
            return $error;
         }
         // 1. Ambil Versi Lokal
         $localVersion = $this->updater->getCurrentVersion();
         // 2. Ambil 5 Riwayat Update Terakhir dari Database
         $history = SystemUpdate::with('user:id,name')
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get()
                                ->map(function($item) {
                                   return [
                                      'version_from' => $item->version_from,
                                      'version_to'   => $item->version_to,
                                      'date'         => function_exists('tanggal_indo') ? tanggal_indo($item->created_at) : $item->created_at->format('d-m-Y H:i'),
                                      'status'       => $item->status,
                                      'admin'        => $item->user ? $item->user->name : 'System',
                                      'log'          => $item->log_details,
                                   ];
                                });
         // 3. Ambil URL dari Config services.php
         $metaUrl = trim((string) config('services.sidoel.update_url'));
         if(empty($metaUrl) || !filter_var($metaUrl, FILTER_VALIDATE_URL)){
            return response()->json([
               'success' => false,
               'message' => 'Konfigurasi URL update belum diatur atau format URL tidak valid di config/services.php.',
               'history' => $history,
            ], 500);
         }
         try {
            // 4. Request ke Server Pusat
            $response = Http::timeout(15)->get($metaUrl);
            if($response->failed()){
               return response()->json([
                  'success' => false,
                  'message' => 'Gagal terhubung ke server update pusat (HTTP ' . $response->status() . ').',
                  'history' => $history,
               ], 502);
            }
            $remoteData = $response->json();
            // Cek apakah response payload valid
            if(!isset($remoteData['version'])){
               return response()->json([
                  'success'         => true,
                  'has_update'      => false,
                  'current_version' => $localVersion,
                  'message'         => $remoteData['description'] ?? 'Tidak ada informasi versi terbaru.',
                  'history'         => $history,
               ]);
            }
            $remoteVersion = $remoteData['version'];
            // 5. Bandingkan Versi
            $hasUpdate = version_compare($remoteVersion, $localVersion, '>');

            return response()->json([
               'success'         => true,
               'current_version' => $localVersion,
               'latest_version'  => $remoteVersion,
               'has_update'      => $hasUpdate,
               'title'           => $remoteData['title'] ?? 'Update Tersedia',
               'changelog'       => $remoteData['description'] ?? '-',
               'download_url'    => $remoteData['download_url'] ?? NULL,
               'history'         => $history,
            ]);
         } catch(\Exception $e) {
            Log::error("Check Update Error: " . $e->getMessage());

            return response()->json([
               'success' => false,
               'message' => 'Error koneksi: ' . $e->getMessage(),
               'history' => $history,
            ], 500);
         }
      }

      /**
       * Eksekusi Update
       */
      public function update(Request $request) {
         if($error = check_permission('update-execute', 'Anda tidak memiliki izin untuk update')){
            return $error;
         }
         set_time_limit(0);
         ini_set('memory_limit', '512M');
         $metaUrl = trim((string) config('services.sidoel.update_url'));
         if(empty($metaUrl) || !filter_var($metaUrl, FILTER_VALIDATE_URL)){
            return response()->json([
               'success' => false,
               'message' => 'Konfigurasi URL update belum valid.',
            ], 500);
         }
         try {
            Log::info("Memulai proses update sistem...");
            $response = Http::timeout(15)->get($metaUrl);
            if($response->failed()){
               throw new \Exception("Gagal mengambil metadata rilis dari server pusat. HTTP " . $response->status());
            }
            $remoteData = $response->json();
            if(empty($remoteData['download_url'])){
               throw new \Exception("URL Download tidak ditemukan dalam data respon server pusat.");
            }
            $rawDownloadUrl = $remoteData['download_url'];
            $downloadUrl    = function_exists('sidoel_decrypt') ? sidoel_decrypt($rawDownloadUrl) : $rawDownloadUrl;
            if(empty($downloadUrl)){
               throw new \Exception("Gagal mendekripsi berkas URL unduhan dari server pusat.");
            }
            // Step 1: Download berkas zip
            $zipPath = $this->updater->downloadUpdate($downloadUrl);
            Log::info("Download berhasil: {$zipPath}");
            // Step 2: Ekstrak arsip zip
            $extractPath = $this->updater->extractUpdate($zipPath);
            Log::info("Ekstrak berhasil: {$extractPath}");
            // Step 3: Timpa core file & migrate database
            $this->updater->updateSystem($extractPath);
            Log::info("Update sistem selesai.");

            return response()->json([
               'success' => true,
               'message' => 'Aplikasi berhasil diperbarui. Silakan refresh halaman.',
            ]);
         } catch(\Exception $e) {
            Log::error("Update Gagal: " . $e->getMessage());

            return response()->json([
               'success' => false,
               'message' => 'Gagal Update: ' . $e->getMessage(),
            ], 500);
         }
      }
   }