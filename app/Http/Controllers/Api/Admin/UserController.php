<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\User\StoreUserRequest;
   use App\Http\Requests\User\UpdateUserRequest;
   use App\Http\Resources\UserResource;
   use App\Models\User;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Hash;
   use Illuminate\Support\Facades\Storage;
   use Spatie\QueryBuilder\QueryBuilder;
   use Spatie\QueryBuilder\AllowedFilter;

   class UserController extends Controller
   {
      public function index(): JsonResponse {
         if($error = check_permission('view-users', 'Anda tidak memiliki izin untuk melihat daftar user')){
            return $error;
         }
         $perPage = request('per_page', 10);
         if($perPage === 'all' || $perPage === NULL || $perPage === 'null'){
            $users = QueryBuilder::for(User::class)
                                 ->with('roles', 'permissions')
                                 ->allowedFilters(
                                    AllowedFilter::partial('name'),
                                    AllowedFilter::partial('email'),
                                    AllowedFilter::callback('role', function($query, $value) {
                                       $query->whereHas('roles', function($q) use ($value) {
                                          $q->where('name', $value);
                                       });
                                    }),
                                 )
                                 ->allowedSorts('name', 'email', 'created_at')
                                 ->defaultSort('created_at')
                                 ->get();

            return $this->responseResource(
               UserResource::collection($users),
               'Daftar user berhasil diambil'
            );
         }
         $users = QueryBuilder::for(User::class)
                              ->with('roles', 'permissions')
                              ->allowedFilters(
                                 AllowedFilter::partial('name'),
                                 AllowedFilter::partial('email'),
                                 AllowedFilter::callback('role', function($query, $value) {
                                    $query->whereHas('roles', function($q) use ($value) {
                                       $q->where('name', $value);
                                    });
                                 }),
                              )
                              ->allowedSorts('name', 'email', 'created_at')
                              ->defaultSort('created_at')
                              ->paginate((int) $perPage)
                              ->withQueryString();

         return $this->responsePaginate(
            UserResource::collection($users),
            'Daftar user berhasil diambil'
         );
      }

      public function store(StoreUserRequest $request): JsonResponse {
         if($error = check_permission('create-users', 'Anda tidak memiliki izin untuk membuat user')){
            return $error;
         }
         $data = $request->validated();
         // 🔥 Handle avatar upload
         if($request->hasFile('avatar')){
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
         }
         // Hash password
         $data['password'] = Hash::make($data['password']);
         // Pisahkan role dan permissions dari data user
         $role        = $data['role'] ?? NULL;
         $permissions = $data['permissions'] ?? NULL;
         unset($data['role'], $data['permissions']);
         $user = User::create($data);
         // Assign role
         if($role){
            $user->syncRoles([$role]);
         }
         // Sync permissions
         if($permissions){
            $user->syncPermissions($permissions);
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
         // 🔥 Handle avatar dengan benar
         if($request->hasFile('avatar')){
            // Hapus avatar lama
            if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
               Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
         }
         else if($request->has('remove_avatar') && $request->boolean('remove_avatar')) {
            if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
               Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = NULL;
         }
         // Hapus remove_avatar dari data
         unset($data['remove_avatar']);
         // Hash password
         if(isset($data['password']) && $data['password']){
            $data['password'] = Hash::make($data['password']);
         }
         else {
            unset($data['password']);
         }
         // Pisahkan role dan permissions
         $role        = $data['role'] ?? NULL;
         $permissions = $data['permissions'] ?? NULL;
         unset($data['role'], $data['permissions']);
         $user->update($data);
         if($role){
            $user->syncRoles([$role]);
         }
         if($permissions !== NULL){
            $user->syncPermissions($permissions);
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
         // Hapus avatar
         if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
            Storage::disk('public')->delete($user->avatar);
         }
         $user->delete();

         return $this->responseSuccess('User berhasil dihapus');
      }
   }