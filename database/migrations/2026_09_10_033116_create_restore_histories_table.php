<?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration {
      public function up(): void {
         Schema::create('restore_histories', function(Blueprint $table) {
            $table->ulid('id')->primary();
            $table
               ->foreignUlid('user_id')
               ->nullable()
               ->constrained('users')
               ->nullOnDelete();
            $table->string('backup_filename');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status')->default('success');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
         });
      }

      public function down(): void {
         Schema::dropIfExists('restore_histories');
      }
   };