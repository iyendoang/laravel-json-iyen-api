<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Permission\StorePermissionRequest;
   use App\Http\Requests\Permission\UpdatePermissionRequest;
   use App\Http\Resources\PermissionResource;
   use App\Models\Permission;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\Cache;
   use Spatie\Permission\PermissionRegistrar;
   use Spatie\QueryBuilder\AllowedFilter;
   use Spatie\QueryBuilder\QueryBuilder;

   class PermissionController extends Controller
   {
      /**
       * Durasi cache Redis (dalam detik) - Default: 1 hari (86400 detik)
       */
      private const CACHE_TTL = 86400;

      /**
       * Tag cache untuk grouping seluruh cache permission
       */
      private const CACHE_TAG = 'permissions';

      /**
       * Helper untuk membersihkan seluruh cache permission dan cache internal Spatie
       */
      private function clearPermissionCache(): void {
         // 1. Reset cache internal Spatie Permission
         app(PermissionRegistrar::class)->forgetCachedPermissions();
         // 2. Flush seluruh cache permission di Redis yang berada di bawah tag 'permissions'
         Cache::store('redis')->tags([self::CACHE_TAG])->flush();
      }

      /**
       * Display a listing of the resource.
       */
      public function index(): JsonResponse {
         if($error = check_permission('view-permissions', 'Anda tidak memiliki izin untuk melihat daftar permission')){
            return $error;
         }
         $perPage     = request('per_page', 10);
         $queryString = http_build_query(request()->query());
         $cacheKey    = 'list:' . md5($queryString);
         // Ambil data dari Cache Redis jika ada
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         // Jika "Semua", ambil tanpa pagination
         if($perPage === 'all' || $perPage === NULL || $perPage === 'null'){
            $permissions = QueryBuilder::for(Permission::class)
                                       ->allowedFilters(
                                          AllowedFilter::partial('name'),
                                          AllowedFilter::exact('guard_name')
                                       )
                                       ->allowedSorts('name', 'guard_name', 'created_at')
                                       ->defaultSort('name')
                                       ->get();
            $response = $this->responseResource(
               PermissionResource::collection($permissions),
               'Daftar permission berhasil diambil'
            );
            // Simpan ke Cache Redis
            Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

            return $response;
         }
         // Ambil data dengan pagination
         $permissions = QueryBuilder::for(Permission::class)
                                    ->allowedFilters(
                                       AllowedFilter::partial('name'),
                                       AllowedFilter::exact('guard_name')
                                    )
                                    ->allowedSorts('name', 'guard_name', 'created_at')
                                    ->defaultSort('name')
                                    ->paginate((int) $perPage)
                                    ->withQueryString();
         $response = $this->responsePaginate(
            PermissionResource::collection($permissions),
            'Daftar permission berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Store a newly created resource in storage.
       */
      public function store(StorePermissionRequest $request): JsonResponse {
         if($error = check_permission('create-permissions', 'Anda tidak memiliki izin untuk membuat permission')){
            return $error;
         }
         $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'api',
         ]);
         // Invalidate seluruh cache permissions
         $this->clearPermissionCache();

         return $this->responseResource(
            new PermissionResource($permission),
            'Permission berhasil dibuat',
            201
         );
      }

      /**
       * Display the specified resource.
       */
      public function show(string $id): JsonResponse {
         if($error = check_permission('view-permissions', 'Anda tidak memiliki izin untuk melihat detail permission')){
            return $error;
         }
         $cacheKey = "detail:{$id}";
         // Ambil dari Cache Redis jika ada
         $cachedData = Cache::store('redis')->tags([self::CACHE_TAG])->get($cacheKey);
         if($cachedData){
            return response()->json($cachedData);
         }
         $permission = Permission::findOrFail($id);
         $response = $this->responseResource(
            new PermissionResource($permission),
            'Detail permission berhasil diambil'
         );
         // Simpan ke Cache Redis
         Cache::store('redis')->tags([self::CACHE_TAG])->put($cacheKey, $response->getData(true), self::CACHE_TTL);

         return $response;
      }

      /**
       * Update the specified resource in storage.
       */
      public function update(UpdatePermissionRequest $request, string $id): JsonResponse {
         if($error = check_permission('edit-permissions', 'Anda tidak memiliki izin untuk mengubah permission')){
            return $error;
         }
         $permission = Permission::findOrFail($id);
         $permission->update([
            'name' => $request->name ?? $permission->name,
         ]);
         // Invalidate seluruh cache permissions (list & detail)
         $this->clearPermissionCache();

         return $this->responseResource(
            new PermissionResource($permission),
            'Permission berhasil diperbarui'
         );
      }

      /**
       * Remove the specified resource from storage.
       */
      public function destroy(string $id): JsonResponse {
         if($error = check_permission('delete-permissions', 'Anda tidak memiliki izin untuk menghapus permission')){
            return $error;
         }
         $permission = Permission::findOrFail($id);
         if($permission->roles()->count() > 0){
            return $this->responseError('Permission tidak dapat dihapus karena masih digunakan oleh role', 422);
         }
         if($permission->users()->count() > 0){
            return $this->responseError('Permission tidak dapat dihapus karena masih digunakan oleh user', 422);
         }
         $permission->delete();
         // Invalidate seluruh cache permissions
         $this->clearPermissionCache();

         return $this->responseSuccess('Permission berhasil dihapus');
      }
   }