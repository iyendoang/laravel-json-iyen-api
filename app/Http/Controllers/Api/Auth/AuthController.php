<?php

   namespace App\Http\Controllers\Api\Auth;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Auth\LoginRequest;
   use App\Http\Requests\Auth\RegisterRequest;
   use App\Http\Resources\UserResource;
   use App\Models\JwtBlacklist;
   use App\Models\User;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Cache;
   use Illuminate\Support\Facades\Hash;
   use Spatie\Permission\PermissionRegistrar;

   class AuthController extends Controller
   {
      /**
       * Durasi cache Redis profile user (dalam detik) - Default: 1 hari
       */
      private const CACHE_TTL = 86400;

      /**
       * Tag cache untuk user
       */
      private const CACHE_TAG = 'users';

      /**
       * Helper untuk membersihkan cache users & spatie
       */
      private function clearUserCache(): void {
         app(PermissionRegistrar::class)->forgetCachedPermissions();
         Cache::store('redis')->tags([self::CACHE_TAG])->flush();
      }

      /**
       * Register user baru
       */
      public function register(RegisterRequest $request): JsonResponse {
         $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
         ]);
         $user->syncRoles(['teacher']);
         $user->load('roles', 'permissions');
         // Invalidate list user cache karena ada data baru
         $this->clearUserCache();
         $token = auth('api')->login($user);

         return $this->respondWithToken($token, $user, 'Registrasi berhasil', 201);
      }

      /**
       * Login user & generate JWT Token
       */
      public function login(LoginRequest $request): JsonResponse {
         $credentials = $request->only('email', 'password');
         if(!$token = auth('api')->attempt($credentials)){
            return $this->responseError('Email atau password salah', 401);
         }
         $user = auth('api')->user();
         $user->load('roles', 'permissions');

         return $this->respondWithToken($token, $user, 'Login berhasil');
      }

      /**
       * Ambil profil user yang sedang login (dengan Redis Caching)
       */
      public function me(): JsonResponse {
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
            'Data user berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Refresh JWT Token
       */
      public function refresh(): JsonResponse {
         $token = auth('api')->refresh();
         $user  = auth('api')->user();
         $user->load('roles', 'permissions');

         return $this->respondWithToken($token, $user, 'Token berhasil diperbarui');
      }

      /**
       * Logout user & masukkan token ke Redis Blacklist
       */
      public function logout(): JsonResponse {
         try {
            $token = auth('api')->getToken();
            if($token){
               $tokenString = $token->get();
               $tokenHash   = hash('sha256', $tokenString);
               $ttlMinutes  = (int) config('jwt.ttl', 60);
               $ttlSeconds  = $ttlMinutes*60;
               // 1. Simpan ke Redis Blacklist untuk lookup cepat
               $blacklistCacheKey = "jwt:blacklist:{$tokenHash}";
               Cache::store('redis')->put($blacklistCacheKey, true, $ttlSeconds);
               // 2. Simpan ke Database sebagai persistent fallback
               $exists = JwtBlacklist::where('token_hash', $tokenHash)->exists();
               if(!$exists){
                  JwtBlacklist::create([
                     'token_hash' => $tokenHash,
                     'expires_at' => now()->addMinutes($ttlMinutes),
                  ]);
               }
            }
            // Hapus cache profile user yang logout
            if($userId = auth('api')->id()){
               Cache::store('redis')->tags([self::CACHE_TAG])->forget("profile:{$userId}");
            }
            auth('api')->logout();

            return $this->responseSuccess('Logout berhasil');
         } catch(\Exception $e) {
            return $this->responseSuccess('Logout berhasil');
         }
      }

      /**
       * Helper response berstandar dengan metadata token
       */
      private function respondWithToken(string $token, User $user, string $message, int $code = 200): JsonResponse {
         $data = [
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL()*60,
         ];

         return $this->responseSuccess($message, $data, $code);
      }
   }