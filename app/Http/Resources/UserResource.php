<?php

   namespace App\Http\Resources;

   use Illuminate\Http\Request;
   use Illuminate\Http\Resources\Json\JsonResource;

   class UserResource extends JsonResource
   {
      public function toArray(Request $request): array {
         return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'avatar'            => $this->avatar_url,
            'phone'             => $this->phone,
            'bio'               => $this->bio,
            'address'           => $this->address,
            'city'              => $this->city,
            'country'           => $this->country,
            'postal_code'       => $this->postal_code,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'role'              => $this->whenLoaded('roles', function() {
               return $this->roles->first()?->name;
            }),
            'permissions'       => $this->whenLoaded('permissions', function() {
               return $this->getAllPermissions()->pluck('name');
            }),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
         ];
      }
   }