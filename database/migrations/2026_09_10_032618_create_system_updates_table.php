<?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration {
      /**
       * Run the migrations.
       */
      public function up(): void {
         // LANGSUNG SAJA DI SINI (Jangan bungkus pakai function lagi)
         Schema::create('system_updates', function(Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('version_from');
            $table->string('version_to');
            $table->timestamp('updated_at_server')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('status')->default('success');
            $table->text('log_details')->nullable();
            $table->timestamps();
         });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void {
         Schema::dropIfExists('system_updates');
      }
   };