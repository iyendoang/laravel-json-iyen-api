<?php

   use Illuminate\Http\JsonResponse;

   if(!function_exists('check_permission')){
      /**
       * Check if authenticated user has permission
       */
      function check_permission(string $permission, ?string $message = NULL): ?JsonResponse {
         if(!auth('api')->user()->hasPermissionTo($permission)){
            return response()->json([
               'status'  => 'error',
               'message' => $message ?? "Anda tidak memiliki izin untuk {$permission}",
            ], 403);
         }

         return NULL;
      }
   }
   if(!function_exists('check_role')){
      /**
       * Check if authenticated user has role
       */
      function check_role(string $role, ?string $message = NULL): ?JsonResponse {
         if(!auth('api')->user()->hasRole($role)){
            return response()->json([
               'status'  => 'error',
               'message' => $message ?? "Anda tidak memiliki role {$role}",
            ], 403);
         }

         return NULL;
      }
   }
   if(!function_exists('has_permission')){
      /**
       * Check if authenticated user has permission (boolean)
       */
      function has_permission(string $permission): bool {
         return auth('api')->user()->hasPermissionTo($permission);
      }
   }
   if(!function_exists('has_role')){
      /**
       * Check if authenticated user has role (boolean)
       */
      function has_role(string $role): bool {
         return auth('api')->user()->hasRole($role);
      }
   }