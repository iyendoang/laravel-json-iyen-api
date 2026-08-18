<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Resources\OptionResource;
   use App\Models\Permission;
   use App\Models\Role;
   use App\Models\User;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Cache;

   class OptionController extends Controller
   {
      /**
       * Durasi cache Redis (dalam detik) - Default: 1 hari (86400s)
       */
      private const CACHE_TTL = 86400;

      /**
       * Tag cache utama untuk options dropdown
       */
      private const CACHE_TAG = 'options';

      /**
       * Get permissions untuk dropdown
       */
      public function permissions(): JsonResponse {
         $cacheKey = 'options:permissions';
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG, 'permissions'])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $permissions = Permission::orderBy('name')->get();
         $response = $this->responseSuccess(
            'Daftar permission berhasil diambil',
            OptionResource::collection($permissions)
         );
         Cache::store('redis')
              ->tags([self::CACHE_TAG, 'permissions'])
              ->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Get roles untuk dropdown
       */
      public function roles(): JsonResponse {
         $cacheKey = 'options:roles';
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG, 'roles'])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $roles = Role::orderBy('name')->get();
         $response = $this->responseSuccess(
            'Daftar role berhasil diambil',
            OptionResource::collection($roles)
         );
         Cache::store('redis')
              ->tags([self::CACHE_TAG, 'roles'])
              ->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Get users untuk dropdown
       */
      public function users(): JsonResponse {
         $cacheKey = 'options:users';
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG, 'users'])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $users = User::orderBy('name')->get();
         $response = $this->responseSuccess(
            'Daftar user berhasil diambil',
            OptionResource::collection($users)
         );
         Cache::store('redis')
              ->tags([self::CACHE_TAG, 'users'])
              ->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Get semua permissions untuk role form (dengan detail)
       */
      public function permissionsAll(): JsonResponse {
         $cacheKey = 'options:permissions_all';
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG, 'permissions'])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $permissions = Permission::orderBy('name')->get();
         $response = $this->responseSuccess(
            'Daftar permission berhasil diambil',
            OptionResource::collection($permissions)
         );
         Cache::store('redis')
              ->tags([self::CACHE_TAG, 'permissions'])
              ->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Get roles untuk assign ke user
       */
      public function rolesAll(): JsonResponse {
         $cacheKey = 'options:roles_all';
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG, 'roles'])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $roles = Role::orderBy('name')->get();
         $response = $this->responseSuccess(
            'Daftar role berhasil diambil',
            OptionResource::collection($roles)
         );
         Cache::store('redis')
              ->tags([self::CACHE_TAG, 'roles'])
              ->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }
   }