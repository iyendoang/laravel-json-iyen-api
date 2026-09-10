<?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration {
      public function up(): void {
         Schema::create('backups', function(Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
         });
      }

      public function down(): void {
         Schema::dropIfExists('backups');
      }
   };