<?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Concerns\HasUlids;
   use Illuminate\Database\Eloquent\Model;

   class JwtBlacklist extends Model
   {
      use HasUlids;

      protected $table = 'jwt_blacklist';
      protected $fillable = [
         'token_hash',
         'expires_at',
      ];
      protected $casts = [
         'expires_at' => 'datetime',
      ];
   }