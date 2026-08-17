<?php

   namespace App\Http\Controllers\Api\Auth;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Auth\LoginRequest;
   use App\Http\Requests\Auth\RegisterRequest;
   use App\Http\Resources\UserResource;
   use App\Models\JwtBlacklist;
   use App\Models\User;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Hash;

   class AuthController extends Controller
   {
      public function register(RegisterRequest $request): JsonResponse {
         $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
         ]);
         $user->syncRoles(['teacher']);
         $user->load('roles', 'permissions');
         $token = auth('api')->login($user);

         return $this->respondWithToken($token, $user, 'Registrasi berhasil', 201);
      }

      public function login(LoginRequest $request): JsonResponse {
         $credentials = $request->only('email', 'password');
         if(!$token = auth('api')->attempt($credentials)){
            return $this->responseError('Email atau password salah', 401);
         }
         $user = auth('api')->user();
         $user->load('roles', 'permissions');

         return $this->respondWithToken($token, $user, 'Login berhasil');
      }

      public function me(): JsonResponse {
         $user = auth('api')->user();
         $user->load('roles', 'permissions');

         return $this->responseResource(
            new UserResource($user),
            'Data user berhasil diambil'
         );
      }

      public function refresh(): JsonResponse {
         $token = auth('api')->refresh();
         $user  = auth('api')->user();
         $user->load('roles', 'permissions');

         return $this->respondWithToken($token, $user, 'Token berhasil diperbarui');
      }

      public function logout(): JsonResponse {
         try {
            $token = auth('api')->getToken();
            if($token){
               $tokenString = $token->get();
               $tokenHash   = hash('sha256', $tokenString);
               $exists = JwtBlacklist::where('token_hash', $tokenHash)->exists();
               if(!$exists){
                  JwtBlacklist::create([
                     'token_hash' => $tokenHash,
                     'expires_at' => now()->addMinutes(config('jwt.ttl', 60)),
                  ]);
               }
            }
            auth('api')->logout();

            return $this->responseSuccess('Logout berhasil');
         } catch(\Exception $e) {
            return $this->responseSuccess('Logout berhasil');
         }
      }

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