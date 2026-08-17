<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\User\StoreUserRequest;
   use App\Http\Requests\User\UpdateUserRequest;
   use App\Http\Resources\UserResource;
   use App\Models\User;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Hash;

   class UserController extends Controller
   {
      public function index(): JsonResponse {
         if($error = check_permission('view-users', 'Anda tidak memiliki izin untuk melihat daftar user')){
            return $error;
         }
         // 🔥 Gunakan paginate dengan search
         $query = User::with('roles', 'permissions')
                      ->orderBy('created_at', 'desc');
         // Search
         if(request('search')){
            $search = request('search');
            $query->where(function($q) use ($search) {
               $q
                  ->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
         }
         $users = $query->paginate(request('per_page', 15));

         return $this->responsePaginate(
            UserResource::collection($users),
            'Daftar user berhasil diambil'
         );
      }

      public function store(StoreUserRequest $request): JsonResponse {
         if($error = check_permission('create-users', 'Anda tidak memiliki izin untuk membuat user')){
            return $error;
         }
         $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
         ]);
         if($request->has('role')){
            $user->syncRoles([$request->role]);
         }
         if($request->has('permissions')){
            $user->syncPermissions($request->permissions);
         }
         $user->load('roles', 'permissions');

         return $this->responseResource(
            new UserResource($user),
            'User berhasil dibuat',
            201
         );
      }

      public function show(string $id): JsonResponse {
         if($error = check_permission('view-users', 'Anda tidak memiliki izin untuk melihat detail user')){
            return $error;
         }
         $user = User::with('roles', 'permissions')->findOrFail($id);

         return $this->responseResource(
            new UserResource($user),
            'Detail user berhasil diambil'
         );
      }

      public function update(UpdateUserRequest $request, string $id): JsonResponse {
         if($error = check_permission('edit-users', 'Anda tidak memiliki izin untuk mengubah user')){
            return $error;
         }
         $user = User::findOrFail($id);
         $data = $request->validated();
         if(isset($data['password'])){
            $data['password'] = Hash::make($data['password']);
         }
         $user->update($data);
         if($request->has('role')){
            $user->syncRoles([$request->role]);
         }
         if($request->has('permissions')){
            $user->syncPermissions($request->permissions);
         }
         $user->load('roles', 'permissions');

         return $this->responseResource(
            new UserResource($user),
            'User berhasil diperbarui'
         );
      }

      public function destroy(string $id): JsonResponse {
         if($error = check_permission('delete-users', 'Anda tidak memiliki izin untuk menghapus user')){
            return $error;
         }
         $user = User::findOrFail($id);
         // Cegah hapus diri sendiri
         if($user->id === auth('api')->id()){
            return $this->responseError('Anda tidak dapat menghapus akun sendiri', 422);
         }
         $user->delete();

         return $this->responseSuccess('User berhasil dihapus');
      }
   }