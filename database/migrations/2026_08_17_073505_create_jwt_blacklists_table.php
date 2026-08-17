<?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration {
      public function up(): void {
         Schema::create('jwt_blacklist', function(Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('token_hash', 64); // SHA256 hash
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->unique('token_hash', 'jwt_blacklist_token_hash_unique');
            $table->index('expires_at', 'jwt_blacklist_expires_at_index');
         });
      }

      public function down(): void {
         Schema::dropIfExists('jwt_blacklist');
      }
   };