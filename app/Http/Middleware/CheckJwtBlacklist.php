<?php

   namespace App\Http\Middleware;

   use App\Models\JwtBlacklist;
   use Closure;
   use Illuminate\Http\Request;
   use Symfony\Component\HttpFoundation\Response;

   class CheckJwtBlacklist
   {
      public function handle(Request $request, Closure $next): Response {
         $token = auth('api')->getToken();
         if($token){
            $tokenHash     = hash('sha256', $token->get());
            $isBlacklisted = JwtBlacklist::where('token_hash', $tokenHash)
                                         ->where('expires_at', '>', now())
                                         ->exists();
            if($isBlacklisted){
               return response()->json([
                  'status'  => 'error',
                  'message' => 'Token telah diblacklist',
               ], 401);
            }
         }

         return $next($request);
      }
   }