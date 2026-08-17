<?php

   use App\Http\Controllers\Api\Admin\PermissionController;
   use App\Http\Controllers\Api\Admin\RoleController;
   use App\Http\Controllers\Api\Admin\UserController;
   use App\Http\Controllers\Api\Auth\AuthController;
   use App\Http\Controllers\Api\ProfileController;
   use App\Http\Middleware\CheckJwtBlacklist;
   use Illuminate\Support\Facades\Route;

   Route::prefix('v1')->group(function() {
      // Public routes
      Route::prefix('auth')->group(function() {
         Route::post('register', [AuthController::class, 'register']);
         Route::post('login', [AuthController::class, 'login']);
      });
      // Protected routes
      Route::middleware(['auth:api', CheckJwtBlacklist::class])->group(function() {
         // Auth routes
         Route::prefix('auth')->group(function() {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
         });
         // Profile routes
         Route::prefix('profile')->group(function() {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::patch('/', [ProfileController::class, 'update']);
            Route::put('/password', [ProfileController::class, 'updatePassword']);
            Route::patch('/password', [ProfileController::class, 'updatePassword']);
            Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
            Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
         });
         // Admin routes
         Route::prefix('admin')->group(function() {
            // Users
            Route::get('users', [UserController::class, 'index']);
            Route::post('users', [UserController::class, 'store']);
            Route::get('users/{user}', [UserController::class, 'show']);
            Route::put('users/{user}', [UserController::class, 'update']);
            Route::delete('users/{user}', [UserController::class, 'destroy']);
            // Roles
            Route::get('roles', [RoleController::class, 'index']);
            Route::post('roles', [RoleController::class, 'store']);
            Route::get('roles/{role}', [RoleController::class, 'show']);
            Route::put('roles/{role}', [RoleController::class, 'update']);
            Route::delete('roles/{role}', [RoleController::class, 'destroy']);
            // Permissions
            Route::get('permissions', [PermissionController::class, 'index']);
            Route::post('permissions', [PermissionController::class, 'store']);
            Route::get('permissions/{permission}', [PermissionController::class, 'show']);
            Route::put('permissions/{permission}', [PermissionController::class, 'update']);
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy']);
         });
      });
   });