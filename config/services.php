<?php
   return [
      /*
      |--------------------------------------------------------------------------
      | Third Party Services
      |--------------------------------------------------------------------------
      |
      | This file is for storing the credentials for third party services such
      | as Resend, Postmark, AWS, and more. This file provides the de facto
      | location for this type of information, allowing packages to have
      | a conventional file to locate the various service credentials.
      |
      */
      'postmark' => [
         'key' => env('POSTMARK_API_KEY'),
      ],
      'resend'   => [
         'key' => env('RESEND_API_KEY'),
      ],
      'ses'      => [
         'key'    => env('AWS_ACCESS_KEY_ID'),
         'secret' => env('AWS_SECRET_ACCESS_KEY'),
         'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
      ],
      'slack'    => [
         'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
         ],
      ],
      // KONFIGURASI SIDOEL (Member & Update)
      'sidoel'   => [
         'is_member'    => env('SIDOEL_MEMBER', false),
         'secret_key'   => env('SIDOEL_SECRET_KEY', 'S1D03LBI54D0N6'),
         'update_url'   => env('SIDOEL_UPDATE_INFO_URL'),
         'package_code' => env('SIDOEL_PACKAGE', 'sidoel-starter-core'),
      ],
      // Tambahkan bagian ini:
      'binaries' => [
         'mysql'     => env('MYSQL_BINARY_PATH'),
         'mysqldump' => env('MYSQLDUMP_BINARY_PATH'),
         'openssl'   => env('OPENSSL_BINARY_PATH'),
      ],
   ];
