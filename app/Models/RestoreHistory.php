<?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Concerns\HasUlids;
   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Database\Eloquent\Relations\BelongsTo;

   class RestoreHistory extends Model
   {
      use HasFactory, HasUlids;

      protected $keyType      = 'string';
      public    $incrementing = false;
      protected $fillable = [
         'user_id',
         'backup_filename',
         'ip_address',
         'user_agent',
         'status',
         'restored_at',
      ];
      protected $casts = [
         'restored_at' => 'datetime',
      ];

      public function actor(): BelongsTo {
         return $this->belongsTo(User::class, 'user_id');
      }
   }