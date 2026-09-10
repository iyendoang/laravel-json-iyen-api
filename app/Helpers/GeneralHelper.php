<?php

   use Carbon\Carbon;
   use PhpOffice\PhpSpreadsheet\Shared\Date;

   if(!function_exists('say_hello')){
      function say_hello($name) {
         return "Hello " . $name;
      }
   }
   if(!function_exists('sidoel_encrypt')){
      /**
       * Enkripsi ID (AES-128-CBC + HMAC + URL Safe)
       */
      function sidoel_encrypt($value) {
         $secretKey = config('services.sidoel.secret_key');
         if(!$secretKey){
            throw new Exception('Sidoel secret key not configured.');
         }
         // Binary key (lebih benar daripada hex string)
         $key       = substr(hash('sha256', $secretKey, true), 0, 16);
         $cipher    = 'aes-128-cbc';
         $ivLength  = openssl_cipher_iv_length($cipher);
         $iv        = random_bytes($ivLength);
         $encrypted = openssl_encrypt(
            (string) $value,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
         );
         if($encrypted === false){
            return NULL;
         }
         // Tambahkan MAC untuk integrity check
         $mac      = hash_hmac('sha256', $iv . $encrypted, $key, true);
         $combined = $iv . $mac . $encrypted;

         // URL-safe base64
         return rtrim(strtr(base64_encode($combined), '+/', '-_'), '=');
      }
   }
   if(!function_exists('sidoel_decrypt')){
      /**
       * Dekripsi ID dengan Integrity Check
       */
      function sidoel_decrypt($value) {
         $secretKey = config('services.sidoel.secret_key');
         if(!$secretKey){
            return NULL;
         }
         $key      = substr(hash('sha256', $secretKey, true), 0, 16);
         $cipher   = 'aes-128-cbc';
         $ivLength = openssl_cipher_iv_length($cipher);
         // Decode URL-safe base64
         $data = base64_decode(strtr($value, '-_', '+/'));
         if(!$data){
            return NULL;
         }
         $iv        = substr($data, 0, $ivLength);
         $mac       = substr($data, $ivLength, 32);
         $encrypted = substr($data, $ivLength+32);
         // Validasi MAC (anti tampering)
         $calculatedMac = hash_hmac('sha256', $iv . $encrypted, $key, true);
         if(!hash_equals($mac, $calculatedMac)){
            return NULL;
         }
         $decrypted = openssl_decrypt(
            $encrypted,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
         );

         return $decrypted !== false ? $decrypted : NULL;
      }
   }
   if(!function_exists('sidoel_encrypt_check')){
      /**
       * Memeriksa apakah nilai mentah (plain) cocok dengan token enkripsi
       * (Aman dari Timing Attack seperti Hash::check)
       * * @param mixed $plainValue Nilai asli sebelum dienkripsi (misal: ID 45)
       * @param string|null $encryptedValue Token hasil enkripsi sidoel_encrypt
       * @return bool
       */
      function sidoel_encrypt_check($plainValue, $encryptedValue): bool {
         if (empty($encryptedValue) || is_null($plainValue)) {
            return false;
         }

         // 1. Coba dekripsi token terlebih dahulu
         $decrypted = sidoel_decrypt($encryptedValue);

         // 2. Jika gagal didekripsi (palsu/telah dimodifikasi), langsung kembalikan false
         if ($decrypted === NULL) {
            return false;
         }

         // 3. Bandingkan secara aman menggunakan hash_equals
         return hash_equals((string) $decrypted, (string) $plainValue);
      }
   }
   if(!function_exists('to_database_date')){
      /**
       * Standarisasi input tanggal ke format Database (YYYY-MM-DD)
       * Berguna saat Import dari Excel
       */
      function to_database_date($date) {
         if(empty($date))
            return NULL;
         try {
            // 1. Jika berupa Serial Date Excel (numerik)
            if(is_numeric($date)){
               return Carbon::instance(Date::excelToDateTimeObject($date))
                                    ->format('Y-m-d');
            }
            // 2. Jika string dengan format dd-mm-yyyy (Format Indonesia di Excel)
            if(is_string($date) && str_contains($date, '-')){
               // Gunakan createFromFormat untuk memastikan urutan day-month-year
               return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            }

            // 3. Fallback untuk format standar (YYYY-MM-DD atau format lainnya)
            return Carbon::parse($date)->format('Y-m-d');
         } catch(\Exception $e) {
            return NULL;
         }
      }
   }
   if(!function_exists('to_excel_date')){
      /**
       * Konversi tanggal ke format Excel/Tampilan Indonesia (DD-MM-YYYY)
       * Berguna saat Export atau menampilkan data di Resource
       */
      function to_excel_date($date) {
         if(empty($date))
            return NULL;
         try {
            // Jika sudah berupa objek Carbon
            if($date instanceof Carbon){
               return $date->format('d-m-Y');
            }

            // Jika string, parse dulu baru format
            return Carbon::parse($date)->format('d-m-Y');
         } catch(\Exception $e) {
            return NULL;
         }
      }
   }
   if(!function_exists('format_rupiah')){
      /**
       * Format Angka ke Rupiah
       * Contoh: 10000 -> Rp 10.000
       */
      function format_rupiah($angka, $prefix = true) {
         if(!is_numeric($angka))
            return $prefix ? "Rp 0" : "0";
         $hasil = number_format($angka, 0, ',', '.');

         return $prefix ? "Rp " . $hasil : $hasil;
      }
   }
   if(!function_exists('tanggal_indo')){
      /**
       * Format Tanggal Indonesia
       * Contoh: 2026-01-28 -> 28 Januari 2026
       */
      function tanggal_indo($tanggal, $cetak_hari = false) {
         if(empty($tanggal))
            return "-";
         $hari     = [
            1 => 'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu',
         ];
         $bulan    = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
         ];
         $split    = explode('-', date('Y-m-d', strtotime($tanggal)));
         $tgl_indo = $split[2] . ' ' . $bulan[(int) $split[1]] . ' ' . $split[0];
         if($cetak_hari){
            $num = date('N', strtotime($tanggal));

            return $hari[$num] . ', ' . $tgl_indo;
         }

         return $tgl_indo;
      }
   }
   if(!function_exists('terbilang')){
      /**
       * Mengubah angka menjadi teks penyebutan
       * Contoh: 1000500 -> Satu Juta Lima Ratus Rupiah
       */
      function terbilang($angka) {
         $angka     = abs($angka);
         $baca      = [
            "",
            "Satu",
            "Dua",
            "Tiga",
            "Empat",
            "Lima",
            "Enam",
            "Tujuh",
            "Delapan",
            "Sembilan",
            "Sepuluh",
            "Sebelas",
         ];
         $terbilang = "";
         if($angka < 12){
            $terbilang = " " . $baca[$angka];
         }
         else if($angka < 20){
            $terbilang = terbilang($angka-10) . " Belas";
         }
         else if($angka < 100){
            $terbilang = terbilang($angka/10) . " Puluh" . terbilang($angka%10);
         }
         else if($angka < 200){
            $terbilang = " Seratus" . terbilang($angka-100);
         }
         else if($angka < 1000){
            $terbilang = terbilang($angka/100) . " Ratus" . terbilang($angka%100);
         }
         else if($angka < 2000){
            $terbilang = " Seribu" . terbilang($angka-1000);
         }
         else if($angka < 1000000){
            $terbilang = terbilang($angka/1000) . " Ribu" . terbilang($angka%1000);
         }
         else if($angka < 1000000000){
            $terbilang = terbilang($angka/1000000) . " Juta" . terbilang($angka%1000000);
         }

         return trim($terbilang) . " Rupiah";
      }
   }
   if(!function_exists('time_age')){
      /**
       * Format Waktu Relatif (Time Ago)
       * Contoh: 2 menit yang lalu, 1 jam yang lalu
       */
      function time_age($timestamp) {
         if(empty($timestamp))
            return "-";
         $time  = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
         $etime = time()-$time;
         if($etime < 1)
            return 'Baru saja';
         $a = [
            365*24*60*60 => 'tahun',
            30*24*60*60  => 'bulan',
            24*60*60     => 'hari',
            60*60        => 'jam',
            60           => 'menit',
            1            => 'detik',
         ];
         foreach($a as $secs => $str) {
            $d = $etime/$secs;
            if($d >= 1){
               $r = round($d);

               return $r . ' ' . $str . ' yang lalu';
            }
         }
      }
   }
