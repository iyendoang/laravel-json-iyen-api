<?php

   namespace App\Http\Controllers\Api;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Profile\UpdateAvatarRequest;
   use App\Http\Requests\Profile\UpdatePasswordRequest;
   use App\Http\Requests\Profile\UpdateProfileRequest;
   use App\Http\Resources\UserResource;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Cache;
   use Illuminate\Support\Facades\Hash;
   use Illuminate\Support\Facades\Storage;
   use Spatie\Permission\PermissionRegistrar;

   class ProfileController extends Controller
   {
      /**
       * Durasi cache Redis (dalam detik) - Default: 1 hari (86400s)
       */
      private const CACHE_TTL = 86400;

      /**
       * Tag cache untuk data user
       */
      private const CACHE_TAG = 'users';

      /**
       * Helper untuk membersihkan cache profile & list user
       */
      private function clearProfileCache(string $userId): void {
         // 1. Reset cache internal Spatie Permission
         app(PermissionRegistrar::class)->forgetCachedPermissions();
         // 2. Invalidate seluruh cache di bawah tag 'users'
         Cache::store('redis')->tags([self::CACHE_TAG])->flush();
      }

      /**
       * Get authenticated user profile
       */
      public function show(): JsonResponse {
         $userId   = auth('api')->id();
         $cacheKey = "profile:{$userId}";
         // Cek cache Redis
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $user = auth('api')->user();
         $user->load('roles', 'permissions');
         $response = $this->responseResource(
            new UserResource($user),
            'Profile berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Update user profile information
       */
      public function update(UpdateProfileRequest $request): JsonResponse {
         $user = auth('api')->user();
         $user->update($request->validated());
         $user->load('roles', 'permissions');
         // Invalidate cache
         $this->clearProfileCache($user->id);

         return $this->responseResource(
            new UserResource($user),
            'Profile berhasil diperbarui'
         );
      }

      /**
       * Update user password
       */
      public function updatePassword(UpdatePasswordRequest $request): JsonResponse {
         $user = auth('api')->user();
         $user->update([
            'password' => Hash::make($request->new_password),
         ]);
         // Invalidate cache
         $this->clearProfileCache($user->id);

         return $this->responseSuccess('Password berhasil diperbarui');
      }

      /**
       * Upload / update avatar
       */
      public function updateAvatar(UpdateAvatarRequest $request): JsonResponse {
         $user = auth('api')->user();
         // Hapus avatar lama jika ada
         if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
            Storage::disk('public')->delete($user->avatar);
         }
         $path = $request->file('avatar')->store('avatars', 'public');
         $user->update([
            'avatar' => $path,
         ]);
         $user->load('roles', 'permissions');
         // Invalidate cache
         $this->clearProfileCache($user->id);

         return $this->responseResource(
            new UserResource($user),
            'Avatar berhasil diperbarui'
         );
      }

      /**
       * Delete user avatar
       */
      public function deleteAvatar(): JsonResponse {
         $user = auth('api')->user();
         // Hapus avatar dari storage disk
         if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
            Storage::disk('public')->delete($user->avatar);
         }
         $user->update([
            'avatar' => NULL,
         ]);
         $user->load('roles', 'permissions');
         // Invalidate cache
         $this->clearProfileCache($user->id);

         return $this->responseResource(
            new UserResource($user),
            'Avatar berhasil dihapus'
         );
      }
   }