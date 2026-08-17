<?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\Model;

   class Setting extends Model
   {
      // 🔥 Beritahu Laravel bahwa tabel ini menggunakan primary key bernama 'key'
      protected $primaryKey = 'key';
      // 🔥 Beritahu Laravel bahwa primary key tabel ini tipenya adalah String (bukan incrementing integer)
      public    $incrementing = false;
      protected $keyType      = 'string';
      /**
       * Atribut yang dapat diisi secara massal (Mass Assignable).
       *
       * @var array<int, string>
       */
      protected $fillable = [
         'key',
         'value',
      ];
   }