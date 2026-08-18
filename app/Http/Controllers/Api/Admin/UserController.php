<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\User\StoreUserRequest;
   use App\Http\Requests\User\UpdateUserRequest;
   use App\Http\Resources\UserResource;
   use App\Models\User;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Cache;
   use Illuminate\Support\Facades\Hash;
   use Illuminate\Support\Facades\Storage;
   use Spatie\Permission\PermissionRegistrar;
   use Spatie\QueryBuilder\AllowedFilter;
   use Spatie\QueryBuilder\QueryBuilder;

   class UserController extends Controller
   {
      /**
       * Durasi cache Redis (dalam detik) - Default: 1 hari (86400s)
       */
      private const CACHE_TTL = 86400;

      /**
       * Tag cache untuk grouping seluruh data users
       */
      private const CACHE_TAG = 'users';

      /**
       * Helper untuk membersihkan cache users dan cache permission Spatie
       */
      private function clearUserCache(): void {
         // 1. Reset cache internal Spatie Permission (karena user-role-permission berubah)
         app(PermissionRegistrar::class)->forgetCachedPermissions();
         // 2. Flush tag cache users di Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->flush();
      }

      /**
       * Display a listing of the resource.
       */
      public function index(): JsonResponse {
         if($error = check_permission('view-users', 'Anda tidak memiliki izin untuk melihat daftar user')){
            return $error;
         }
         $perPage     = request('per_page', 10);
         $queryString = http_build_query(request()->query());
         $cacheKey    = 'list:' . md5($queryString);
         // Cek apakah data sudah ada di Redis
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         // Jika "Semua", tanpa pagination
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
            $response = $this->responseResource(
               UserResource::collection($users),
               'Daftar user berhasil diambil'
            );
            // Simpan ke Cache Redis
            Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

            return $response;
         }
         // Ambil data dengan pagination
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
         $response = $this->responsePaginate(
            UserResource::collection($users),
            'Daftar user berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Store a newly created resource in storage.
       */
      public function store(StoreUserRequest $request): JsonResponse {
         if($error = check_permission('create-users', 'Anda tidak memiliki izin untuk membuat user')){
            return $error;
         }
         $data = $request->validated();
         // Handle avatar upload
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
         // Invalidate cache
         $this->clearUserCache();

         return $this->responseResource(
            new UserResource($user),
            'User berhasil dibuat',
            201
         );
      }

      /**
       * Display the specified resource.
       */
      public function show(string $id): JsonResponse {
         if($error = check_permission('view-users', 'Anda tidak memiliki izin untuk melihat detail user')){
            return $error;
         }
         $cacheKey = "detail:{$id}";
         // Cek cache Redis
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $user = User::with('roles', 'permissions')->findOrFail($id);
         $response = $this->responseResource(
            new UserResource($user),
            'Detail user berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Update the specified resource in storage.
       */
      public function update(UpdateUserRequest $request, string $id): JsonResponse {
         if($error = check_permission('edit-users', 'Anda tidak memiliki izin untuk mengubah user')){
            return $error;
         }
         $user = User::findOrFail($id);
         $data = $request->validated();
         // Handle avatar upload / remove
         if($request->hasFile('avatar')){
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
         unset($data['remove_avatar']);
         // Hash password jika diisi
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
         // Invalidate cache
         $this->clearUserCache();

         return $this->responseResource(
            new UserResource($user),
            'User berhasil diperbarui'
         );
      }

      /**
       * Remove the specified resource from storage.
       */
      public function destroy(string $id): JsonResponse {
         if($error = check_permission('delete-users', 'Anda tidak memiliki izin untuk menghapus user')){
            return $error;
         }
         $user = User::findOrFail($id);
         // Cegah hapus akun sendiri
         if($user->id === auth('api')->id()){
            return $this->responseError('Anda tidak dapat menghapus akun sendiri', 422);
         }
         // Hapus avatar dari storage jika ada
         if($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)){
            Storage::disk('public')->delete($user->avatar);
         }
         $user->delete();
         // Invalidate cache
         $this->clearUserCache();

         return $this->responseSuccess('User berhasil dihapus');
      }
   }