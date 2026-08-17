<?php

   namespace App\Http\Controllers\Api;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Profile\UpdateAvatarRequest;
   use App\Http\Requests\Profile\UpdatePasswordRequest;
   use App\Http\Requests\Profile\UpdateProfileRequest;
   use App\Http\Resources\UserResource;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Hash;
   use Illuminate\Support\Facades\Storage;

   class ProfileController extends Controller
   {
      public function show(): JsonResponse {
         $user = auth('api')->user();
         $user->load('roles', 'permissions');

         return $this->responseResource(
            new UserResource($user),
            'Profile berhasil diambil'
         );
      }

      public function update(UpdateProfileRequest $request): JsonResponse {
         $user = auth('api')->user();
         $user->update($request->validated());
         $user->load('roles', 'permissions');

         return $this->responseResource(
            new UserResource($user),
            'Profile berhasil diperbarui'
         );
      }

      public function updatePassword(UpdatePasswordRequest $request): JsonResponse {
         $user = auth('api')->user();
         $user->update([
            'password' => Hash::make($request->new_password),
         ]);

         return $this->responseSuccess('Password berhasil diperbarui');
      }

      public function updateAvatar(UpdateAvatarRequest $request): JsonResponse {
         $user = auth('api')->user();
         if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
            Storage::disk('public')->delete($user->avatar);
         }
         $path = $request->file('avatar')->store('avatars', 'public');
         $user->update([
            'avatar' => $path,
         ]);
         $user->load('roles', 'permissions');

         return $this->responseResource(
            new UserResource($user),
            'Avatar berhasil diperbarui'
         );
      }

      public function deleteAvatar(): JsonResponse {
         $user = auth('api')->user();
         if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
            Storage::disk('public')->delete($user->avatar);
         }
         $user->update([
            'avatar' => NULL,
         ]);
         $user->load('roles', 'permissions');

         return $this->responseResource(
            new UserResource($user),
            'Avatar berhasil dihapus'
         );
      }
   }