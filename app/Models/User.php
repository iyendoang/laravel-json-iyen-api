<?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Concerns\HasUlids;
   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\SoftDeletes;
   use Illuminate\Foundation\Auth\User as Authenticatable;
   use Illuminate\Notifications\Notifiable;
   use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
   use Spatie\Permission\Traits\HasRoles;

   class User extends Authenticatable implements JWTSubject
   {
      use HasFactory;
      use HasUlids;
      use HasRoles;
      use Notifiable;
      use SoftDeletes;

      protected $fillable = [
         'name',
         'email',
         'password',
         'avatar',
         'phone',
         'bio',
         'address',
         'city',
         'country',
         'postal_code',
      ];
      protected $hidden = [
         'password',
         'remember_token',
      ];

      protected function casts(): array {
         return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
         ];
      }

      public function getJWTIdentifier(): mixed {
         return $this->getKey();
      }

      public function getJWTCustomClaims(): array {
         return [
            'uid'   => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
         ];
      }

      /**
       * Get the user's role name (single role)
       */
      public function getRoleAttribute(): ?string {
         return $this->roles->first()?->name;
      }

      /**
       * Get all permissions including those from role
       */
      public function getAllPermissionsAttribute(): array {
         return $this->getAllPermissions()->pluck('name')->toArray();
      }

      /**
       * Assign a single role to user
       */
      public function assignSingleRole(string $roleName): void {
         $this->syncRoles([$roleName]);
      }

      /**
       * Get avatar URL
       */
      public function getAvatarUrlAttribute(): ?string {
         if($this->avatar){
            // Jika avatar adalah URL lengkap
            if(filter_var($this->avatar, FILTER_VALIDATE_URL)){
               return $this->avatar;
            }

            // Jika avatar disimpan di storage
            return asset('storage/' . $this->avatar);
         }

         // Return default avatar (bisa dari UI Avatars)
         return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
      }
   }