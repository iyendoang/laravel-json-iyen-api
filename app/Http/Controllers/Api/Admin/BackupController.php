<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Models\Backup;
   use App\Models\RestoreHistory;
   use App\Services\System\DatabaseService;
   use App\Traits\ApiResponse;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Log;
   use Illuminate\Support\Facades\URL;

   class BackupController extends Controller
   {
      use ApiResponse;

      protected $dbService;

      public function __construct(DatabaseService $dbService) {
         $this->dbService = $dbService;
      }

      public function index() {
         $backups = Backup::orderBy('created_at', 'desc')->get()->map(function($item) {
            return [
               'id'         => $item->id,
               'filename'   => $item->filename,
               'size_raw'   => $item->size,
               'size_human' => $this->formatBytes($item->size),
               'created_at' => $item->created_at->format('Y-m-d H:i:s'),
               'date_indo'  => function_exists('tanggal_indo') ? tanggal_indo($item->created_at, true) : $item->created_at,
               'age'        => function_exists('time_age') ? time_age($item->created_at) : '',
               'path'       => $item->path,
            ];
         });

         return $this->responseSuccess('Daftar berkas cadangan berhasil diambil', $backups);
      }

      public function history() {
         $history = RestoreHistory::with('actor:id,name,email')
                                  ->orderBy('restored_at', 'desc')
                                  ->limit(20)
                                  ->get()
                                  ->map(function($item) {
                                     return [
                                        'id'         => $item->id,
                                        'actor_name' => $item->actor ? $item->actor->name : 'System/Unknown',
                                        'filename'   => $item->backup_filename,
                                        'ip_address' => $item->ip_address,
                                        'date_indo'  => function_exists('tanggal_indo') ? tanggal_indo($item->restored_at) : $item->restored_at,
                                        'time_ago'   => function_exists('time_age') ? time_age($item->restored_at) : '',
                                        'status'     => $item->status,
                                     ];
                                  });

         return $this->responseSuccess('Daftar riwayat pemulihan sistem berhasil diambil', $history);
      }

      public function store() {
         set_time_limit(0);
         ini_set('memory_limit', '512M');
         try {
            $filePath = $this->dbService->createBackup('backupdb');

            return $this->responseSuccess('Backup berhasil dibuat!', [
               'path' => basename($filePath),
            ]);
         } catch(\Exception $e) {
            Log::error("Sidoel FNC Backup Error: " . $e->getMessage());

            return $this->responseError('Gagal membuat backup: ' . $e->getMessage(), 500);
         }
      }

      public function restore(Request $request) {
         $request->validate([
            'id' => 'required|exists:backups,id',
         ]);
         set_time_limit(0);
         ini_set('memory_limit', '512M');
         $backup = Backup::find($request->id);
         $user   = auth()->user();
         $ip     = $request->ip();
         $agent  = $request->userAgent();
         try {
            Log::warning("Memulai RESTORE dari: " . $backup->filename);
            $this->dbService->restoreDatabase($backup->path);
            RestoreHistory::create([
               'user_id'         => $user ? $user->id : NULL,
               'backup_filename' => $backup->filename,
               'ip_address'      => $ip,
               'user_agent'      => $agent,
               'status'          => 'success',
               'restored_at'     => now(),
            ]);

            return $this->responseSuccess('Database berhasil dikembalikan ke versi: ' . $backup->filename);
         } catch(\Exception $e) {
            Log::error("Restore Error: " . $e->getMessage());

            return $this->responseError('Restore GAGAL! Error: ' . $e->getMessage(), 500);
         }
      }

      /**
       * 🔥 NEW METHOD: Membuat URL unduhan temporer berdurasi 5 menit saja
       */
      public function generateSignedUrl($id) {
         Backup::findOrFail($id); // Validasi data ada
         $signedUrl = URL::temporarySignedRoute(
            'backups.download.signed',
            now()->addMinutes(5),
            ['id' => $id]
         );

         return $this->responseSuccess('Link unduhan berhasil dibuat', [
            'url' => $signedUrl,
         ]);
      }

      /**
       * GET /api/v1/system/backups/{id}/download
       * 🔥 FIX DOWNLOAD: Ditambahkan pengaman hasValidSignature!
       */
      public function download(Request $request, $id) {
         // Cek apakah url ini dimodifikasi atau sudah kedaluwarsa lebih dari 5 menit
         if(!$request->hasValidSignature()){
            abort(403, 'Akses link download ilegal atau telah kedaluwarsa.');
         }
         $backup = Backup::findOrFail($id);
         if(!file_exists($backup->path)){
            return $this->responseError('File fisik tidak ditemukan di server.', 404);
         }

         return response()->download($backup->path, $backup->filename);
      }

      /**
       * Upload berkas .sidosts dari komputer dan langsung restore
       */
      /**
       * Upload berkas .sidosts dari komputer dan langsung restore
       */
      public function uploadAndRestore(Request $request) {
         $request->validate([
            'backup_file' => 'required|file|max:512000', // Maks 500MB
         ]);
         $file         = $request->file('backup_file');
         $originalName = $file->getClientOriginalName();
         // Validasi ekstensi harus .sidosts
         if(!str_ends_with(strtolower($originalName), '.sidosts')){
            return $this->responseError('Format berkas tidak valid. Hanya berkas berekstensi .sidosts yang diizinkan.', 422);
         }
         set_time_limit(0);
         ini_set('memory_limit', '512M');
         $user  = auth()->user();
         $ip    = $request->ip();
         $agent = $request->userAgent();
         // Pastikan folder direktori penampung ada
         $tempDir = storage_path('app/temp_restore');
         if(!file_exists($tempDir)){
            @mkdir($tempDir, 0755, true);
         }
         // Generate nama unik dan pindahkan file secara langsung menggunakan move()
         $tempFileName = 'upload_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $originalName);
         $file->move($tempDir, $tempFileName);
         $fullPath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;
         // Pastikan file benar-benar ada di filesystem
         if(!file_exists($fullPath)){
            return $this->responseError('Gagal memindahkan berkas unggahan ke folder penyimpanan sementara server.', 500);
         }
         try {
            Log::warning("Memulai RESTORE dari berkas upload: {$originalName} (Path: {$fullPath})");
            // Eksekusi Dekripsi & Import Database
            $this->dbService->restoreDatabase($fullPath);
            // Catat ke log histori
            RestoreHistory::create([
               'user_id'         => $user?->id,
               'backup_filename' => $originalName . ' (Manual Upload)',
               'ip_address'      => $ip,
               'user_agent'      => $agent,
               'status'          => 'success',
               'restored_at'     => now(),
            ]);
            // Bersihkan file sementara setelah sukses
            if(file_exists($fullPath)){
               @unlink($fullPath);
            }

            return $this->responseSuccess("Basis data berhasil dipulihkan dari berkas: {$originalName}");
         } catch(\Exception $e) {
            // Bersihkan file sementara jika gagal
            if(file_exists($fullPath)){
               @unlink($fullPath);
            }
            Log::error("Upload & Restore Gagal: " . $e->getMessage());
            RestoreHistory::create([
               'user_id'         => $user ? $user->id : NULL,
               'backup_filename' => $originalName . ' (Manual Upload Gagal)',
               'ip_address'      => $ip,
               'user_agent'      => $agent,
               'status'          => 'failed',
               'restored_at'     => now(),
            ]);

            return $this->responseError('Gagal memulihkan database: ' . $e->getMessage(), 500);
         }
      }

      public function destroy($id) {
         $backup = Backup::findOrFail($id);
         try {
            if(file_exists($backup->path)){
               unlink($backup->path);
            }
            $backup->delete();

            return $this->responseSuccess('Backup berhasil dihapus.');
         } catch(\Exception $e) {
            return $this->responseError('Gagal menghapus berkas: ' . $e->getMessage(), 500);
         }
      }

      private function formatBytes($bytes, $precision = 2) {
         $units = ['B', 'KB', 'MB', 'GB', 'TB'];
         $bytes = max($bytes, 0);
         $pow   = floor(($bytes ? log($bytes) : 0)/log(1024));
         $pow   = min($pow, count($units)-1);
         $bytes /= pow(1024, $pow);

         return round($bytes, $precision) . ' ' . $units[$pow];
      }
   }