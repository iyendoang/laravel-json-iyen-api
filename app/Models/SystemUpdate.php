<?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\Model;

   class SystemUpdate extends Model
   {
      use HasFactory;

      /**
       * Nama tabel (opsional jika sesuai standar, tapi bagus untuk kejelasan)
       */
      protected $table = 'system_updates';
      /**
       * Kolom yang boleh diisi secara massal (Mass Assignment)
       */
      protected $fillable = [
         'version_from',
         'version_to',
         'updated_at_server',
         'updated_by',
         'status',
         'log_details',
      ];
      /**
       * Casting tipe data otomatis
       */
      protected $casts = [
         'updated_at_server' => 'datetime', // Agar bisa langsung $update->updated_at_server->format('d M Y')
      ];

      /**
       * Relasi ke User (Admin yang melakukan update)
       */
      public function user() {
         return $this->belongsTo(User::class, 'updated_by');
      }
   }