<?php

   namespace App\Http\Resources;

   use Illuminate\Http\Request;
   use Illuminate\Http\Resources\Json\JsonResource;

   class PermissionResource extends JsonResource
   {
      public function toArray(Request $request): array {
         return [
            'id'          => $this->id,
            'name'        => $this->name,
            'guard_name'  => $this->guard_name,
            'roles_count' => $this->whenCounted('roles', function() {
               return $this->roles_count;
            }),
            'users_count' => $this->whenCounted('users', function() {
               return $this->users_count;
            }),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
         ];
      }
   }