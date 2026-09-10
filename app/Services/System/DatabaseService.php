<?php

   namespace App\Services\System;

   use App\Models\Backup;
   use Illuminate\Support\Facades\Log;
   use Symfony\Component\Process\Process;
   use Symfony\Component\Process\Exception\ProcessFailedException;

   class DatabaseService
   {
      protected $dbName;
      protected $dbUser;
      protected $dbPass;
      protected $dbHost;
      protected $dbPort;
      protected $encryptionKey;
      protected $maxBackups = 5;
      // Path Binary
      protected $pathMysql;
      protected $pathDump;
      protected $pathOpenssl;

      public function __construct() {
         // 1. Load Database Config
         $this->dbName = config('database.connections.mysql.database');
         $this->dbUser = config('database.connections.mysql.username');
         $this->dbPass = config('database.connections.mysql.password');
         $this->dbHost = config('database.connections.mysql.host', '127.0.0.1');
         $this->dbPort = config('database.connections.mysql.port', '3306');
         // 2. Load Secret Key
         $this->encryptionKey = config('services.sidoel.secret_key', 'S1D03LBI54D0N6');
         // 3. Resolve Binary Paths secara otomatis (Mendukung macOS Homebrew & Linux Server)
         $this->pathMysql   = $this->resolveBinary(config('services.binaries.mysql'), 'mysql');
         $this->pathDump    = $this->resolveBinary(config('services.binaries.mysqldump'), 'mysqldump');
         $this->pathOpenssl = $this->resolveBinary(config('services.binaries.openssl'), 'openssl');
      }

      /**
       * Resolusi letak binary executable di sistem (Mac & Linux)
       */
      protected function resolveBinary(?string $configuredPath, string $binaryName): string {
         // 1. Jika diisi manual di .env/config dan file-nya valid, gunakan itu
         if(!empty($configuredPath) && (file_exists($configuredPath) || is_executable($configuredPath))){
            return $configuredPath;
         }
         // 2. Daftar kandidat path otomatis (Mencakup Mac Homebrew standar, keg-only MySQL 8.0, dan Linux)
         $candidatePaths = [
            "/opt/homebrew/opt/mysql@8.0/bin/{$binaryName}", // Mac Homebrew MySQL 8.0
            "/opt/homebrew/opt/mysql/bin/{$binaryName}",     // Mac Homebrew MySQL latest
            "/opt/homebrew/bin/{$binaryName}",               // Mac Homebrew umum
            "/usr/local/bin/{$binaryName}",                  // Mac Intel / Linux local
            "/usr/bin/{$binaryName}",                        // Ubuntu / Debian / CentOS standar
            "/bin/{$binaryName}",
            "/usr/local/mysql/bin/{$binaryName}",
         ];
         foreach($candidatePaths as $path) {
            if(file_exists($path) && is_executable($path)){
               return $path;
            }
         }

         // 3. Fallback jika tidak ditemukan secara eksplisit
         return $binaryName;
      }

      /**
       * Membuat Backup Terenkripsi (.sidosts)
       * Menggunakan AES-256 dan menyembunyikan Password/Key dari Process List
       */
      public function createBackup($prefix = 'backup') {
         $fileName    = $prefix . '_' . date('Y-m-d_H-i-s') . '.sidosts';
         $storagePath = storage_path('app/backups');
         if(!file_exists($storagePath)){
            mkdir($storagePath, 0755, true);
         }
         $filePath = $storagePath . DIRECTORY_SEPARATOR . $fileName;
         // Command Construction dengan port support
         $command = sprintf(
            '%s --user=%s --host=%s --port=%s %s | %s enc -aes-256-cbc -pbkdf2 -salt -pass env:BACKUP_KEY > %s',
            escapeshellcmd($this->pathDump),
            escapeshellarg($this->dbUser),
            escapeshellarg($this->dbHost),
            escapeshellarg($this->dbPort),
            escapeshellarg($this->dbName),
            escapeshellcmd($this->pathOpenssl),
            escapeshellarg($filePath)
         );
         // Environment variables rahasia
         $envVars = [
            'MYSQL_PWD'  => $this->dbPass,
            'BACKUP_KEY' => $this->encryptionKey,
         ];
         try {
            Log::info("Starting Backup Process...", ['file' => $fileName]);
            $this->runProcess($command, $envVars);
            if(file_exists($filePath) && filesize($filePath) > 0){
               Backup::create([
                  'filename' => $fileName,
                  'path'     => $filePath,
                  'size'     => filesize($filePath),
               ]);
               Log::info("Backup sukses dan terenkripsi.");
               $this->pruneOldBackups();

               return $filePath;
            }
            else {
               throw new \Exception("File backup terbentuk tapi kosong (0 bytes).");
            }
         } catch(\Exception $e) {
            Log::error("Backup Gagal: " . $e->getMessage());
            if(file_exists($filePath)){
               @unlink($filePath);
            }
            throw $e;
         }
      }

      /**
       * Restore Database dari file .sidosts
       */
      public function restoreDatabase($fullPath) {
         if(!file_exists($fullPath)){
            throw new \Exception("File backup tidak ditemukan: " . $fullPath);
         }
         Log::warning("Memulai Restore Database...", ['file' => basename($fullPath)]);
         // Command Construction: OpenSSL Decrypt -> MySQL Import
         $command = sprintf(
            '%s enc -d -aes-256-cbc -pbkdf2 -salt -pass env:BACKUP_KEY -in %s | %s --user=%s --host=%s --port=%s %s',
            escapeshellcmd($this->pathOpenssl),
            escapeshellarg($fullPath),
            escapeshellcmd($this->pathMysql),
            escapeshellarg($this->dbUser),
            escapeshellarg($this->dbHost),
            escapeshellarg($this->dbPort),
            escapeshellarg($this->dbName)
         );
         $envVars = [
            'MYSQL_PWD'  => $this->dbPass,
            'BACKUP_KEY' => $this->encryptionKey,
         ];
         try {
            $this->runProcess($command, $envVars);
            Log::info("Restore Database Berhasil Diselesaikan.");

            return true;
         } catch(\Exception $e) {
            Log::error("Restore Gagal: " . $e->getMessage());
            throw $e;
         }
      }

      /**
       * Hapus Backup Lama (> 5)
       */
      protected function pruneOldBackups() {
         $count = Backup::count();
         if($count > $this->maxBackups){
            $deleteCount = $count-$this->maxBackups;
            $oldBackups  = Backup::orderBy('created_at', 'asc')->take($deleteCount)->get();
            foreach($oldBackups as $backup) {
               if(file_exists($backup->path)){
                  @unlink($backup->path);
               }
               $backup->delete();
            }
            Log::info("Pruning: Menghapus $deleteCount backup lama.");
         }
      }

      /**
       * Menjalankan perintah shell dengan environment variables terisolasi
       */
      private function runProcess($command, $env = []) {
         $process = Process::fromShellCommandline($command);
         $process->setTimeout(600); // 10 menit
         // Injeksi direktori sistem agar binary pendukung selalu terbaca di sub-shell
         $systemPaths = '/opt/homebrew/opt/mysql@8.0/bin:/opt/homebrew/bin:/opt/homebrew/sbin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin';
         $currentPath = getenv('PATH') ? : '';
         $env['PATH'] = $currentPath ? ($currentPath . ':' . $systemPaths) : $systemPaths;
         $process->setEnv($env);
         $process->run();
         if(!$process->isSuccessful()){
            throw new ProcessFailedException($process);
         }
      }
   }