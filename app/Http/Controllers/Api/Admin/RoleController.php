<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Role\StoreRoleRequest;
   use App\Http\Requests\Role\UpdateRoleRequest;
   use App\Http\Resources\RoleResource;
   use App\Models\Role;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Cache;
   use Spatie\Permission\PermissionRegistrar;
   use Spatie\QueryBuilder\AllowedFilter;
   use Spatie\QueryBuilder\QueryBuilder;

   class RoleController extends Controller
   {
      /**
       * Durasi cache Redis (dalam detik) - Default: 1 hari (86400s)
       */
      private const CACHE_TTL = 86400;

      /**
       * Tag cache untuk grouping seluruh data roles
       */
      private const CACHE_TAG = 'roles';

      /**
       * Helper untuk membersihkan cache roles, permissions, dan internal Spatie
       */
      private function clearRoleCache(): void {
         // 1. Reset cache internal Spatie Permission (role-permission map)
         app(PermissionRegistrar::class)->forgetCachedPermissions();
         // 2. Flush tag cache roles di Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->flush();
      }

      /**
       * Display a listing of the resource.
       */
      public function index(): JsonResponse {
         if($error = check_permission('view-roles', 'Anda tidak memiliki izin untuk melihat daftar role')){
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
            $roles = QueryBuilder::for(Role::class)
                                 ->with('permissions')
                                 ->allowedFilters(
                                    AllowedFilter::partial('name'),
                                    AllowedFilter::exact('guard_name')
                                 )
                                 ->allowedSorts('name', 'created_at')
                                 ->defaultSort('name')
                                 ->get();
            $response = $this->responseResource(
               RoleResource::collection($roles),
               'Daftar role berhasil diambil'
            );
            // Simpan ke Cache Redis
            Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

            return $response;
         }
         // Ambil data dengan pagination
         $roles = QueryBuilder::for(Role::class)
                              ->with('permissions')
                              ->allowedFilters(
                                 AllowedFilter::partial('name'),
                                 AllowedFilter::exact('guard_name')
                              )
                              ->allowedSorts('name', 'created_at')
                              ->defaultSort('name')
                              ->paginate((int) $perPage)
                              ->withQueryString();
         $response = $this->responsePaginate(
            RoleResource::collection($roles),
            'Daftar role berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Store a newly created resource in storage.
       */
      public function store(StoreRoleRequest $request): JsonResponse {
         if($error = check_permission('create-roles', 'Anda tidak memiliki izin untuk membuat role')){
            return $error;
         }
         $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'api',
         ]);
         if($request->has('permissions')){
            $role->syncPermissions($request->permissions);
         }
         $role->load('permissions');
         // Invalidate cache
         $this->clearRoleCache();

         return $this->responseResource(
            new RoleResource($role),
            'Role berhasil dibuat',
            201
         );
      }

      /**
       * Display the specified resource.
       */
      public function show(string $id): JsonResponse {
         if($error = check_permission('view-roles', 'Anda tidak memiliki izin untuk melihat detail role')){
            return $error;
         }
         $cacheKey = "detail:{$id}";
         // Cek cache Redis
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $role = Role::with('permissions')->findOrFail($id);
         $response = $this->responseResource(
            new RoleResource($role),
            'Detail role berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Update the specified resource in storage.
       */
      public function update(UpdateRoleRequest $request, string $id): JsonResponse {
         if($error = check_permission('edit-roles', 'Anda tidak memiliki izin untuk mengubah role')){
            return $error;
         }
         $role = Role::findOrFail($id);
         $role->update([
            'name' => $request->name ?? $role->name,
         ]);
         if($request->has('permissions')){
            $role->syncPermissions($request->permissions);
         }
         $role->load('permissions');
         // Invalidate seluruh cache roles
         $this->clearRoleCache();

         return $this->responseResource(
            new RoleResource($role),
            'Role berhasil diperbarui'
         );
      }

      /**
       * Remove the specified resource from storage.
       */
      public function destroy(string $id): JsonResponse {
         if($error = check_permission('delete-roles', 'Anda tidak memiliki izin untuk menghapus role')){
            return $error;
         }
         $role = Role::findOrFail($id);
         // Cegah hapus role super-admin
         if($role->name === 'super-admin'){
            return $this->responseError('Role super-admin tidak dapat dihapus', 422);
         }
         $role->delete();
         // Invalidate seluruh cache roles
         $this->clearRoleCache();

         return $this->responseSuccess('Role berhasil dihapus');
      }
   }