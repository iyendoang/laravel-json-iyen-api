<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Resources\OptionResource;
   use App\Models\Permission;
   use App\Models\Role;
   use App\Models\User;
   use Illuminate\Http\JsonResponse;

   class OptionController extends Controller
   {
      /**
       * Get permissions untuk dropdown
       */
      public function permissions(): JsonResponse {
         $permissions = Permission::orderBy('name')->get();

         return $this->responseSuccess(
            'Daftar permission berhasil diambil',
            OptionResource::collection($permissions)
         );
      }

      /**
       * Get roles untuk dropdown
       */
      public function roles(): JsonResponse {
         $roles = Role::orderBy('name')->get();

         return $this->responseSuccess(
            'Daftar role berhasil diambil',
            OptionResource::collection($roles)
         );
      }

      /**
       * Get users untuk dropdown
       */
      public function users(): JsonResponse {
         $users = User::orderBy('name')->get();

         return $this->responseSuccess(
            'Daftar user berhasil diambil',
            OptionResource::collection($users)
         );
      }

      /**
       * Get semua permissions untuk role form (dengan detail)
       */
      public function permissionsAll(): JsonResponse {
         $permissions = Permission::orderBy('name')->get();

         return $this->responseSuccess(
            'Daftar permission berhasil diambil',
            OptionResource::collection($permissions)
         );
      }

      /**
       * Get roles untuk assign ke user
       */
      public function rolesAll(): JsonResponse {
         $roles = Role::orderBy('name')->get();

         return $this->responseSuccess(
            'Daftar role berhasil diambil',
            OptionResource::collection($roles)
         );
      }
   }