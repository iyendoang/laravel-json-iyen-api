<?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Concerns\HasUlids;
   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\Model;

   class Backup extends Model
   {
      use HasFactory, HasUlids;

      /**
       * Tipe data primary key adalah string (ULID)
       *
       * @var string
       */
      protected $keyType = 'string';
      /**
       * Nonaktifkan auto-incrementing integer bawaan Laravel
       *
       * @var bool
       */
      public $incrementing = false;
      /**
       * Kolom yang boleh diisi secara mass-assignment
       *
       * @var array<int, string>
       */
      protected $fillable = [
         'filename',
         'path',
         'size',
      ];
   }