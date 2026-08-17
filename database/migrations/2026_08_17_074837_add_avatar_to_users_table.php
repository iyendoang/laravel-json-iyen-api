<?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration {
      public function up(): void {
         Schema::table('users', function(Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->text('bio')->nullable()->after('phone');
            $table->string('address')->nullable()->after('bio');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('country', 100)->nullable()->after('city');
            $table->string('postal_code', 10)->nullable()->after('country');
         });
      }

      public function down(): void {
         Schema::table('users', function(Blueprint $table) {
            $table->dropColumn([
               'avatar',
               'phone',
               'bio',
               'address',
               'city',
               'country',
               'postal_code',
            ]);
         });
      }
   };