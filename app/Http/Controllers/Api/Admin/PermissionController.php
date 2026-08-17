<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Permission\StorePermissionRequest;
   use App\Http\Requests\Permission\UpdatePermissionRequest;
   use App\Http\Resources\PermissionResource;
   use App\Models\Permission;
   use Illuminate\Http\JsonResponse;
   use Spatie\QueryBuilder\QueryBuilder;
   use Spatie\QueryBuilder\AllowedFilter;

   class PermissionController extends Controller
   {
      public function index(): JsonResponse {
         if($error = check_permission('view-permissions', 'Anda tidak memiliki izin untuk melihat daftar permission')){
            return $error;
         }
         $perPage = request('per_page', 10);
         // 🔥 Jika "Semua", tanpa pagination
         if($perPage === 'all' || $perPage === NULL || $perPage === 'null'){
            $permissions = QueryBuilder::for(Permission::class)
                                       ->allowedFilters(
                                          AllowedFilter::partial('name'),
                                          AllowedFilter::exact('guard_name'),
                                       )
                                       ->allowedSorts('name', 'guard_name', 'created_at')
                                       ->defaultSort('name')
                                       ->get();
            return $this->responseResource(
               PermissionResource::collection($permissions),
               'Daftar permission berhasil diambil'
            );
         }
         $permissions = QueryBuilder::for(Permission::class)
                                    ->allowedFilters(
                                       AllowedFilter::partial('name'),
                                       AllowedFilter::exact('guard_name'),
                                    )
                                    ->allowedSorts('name', 'guard_name', 'created_at')
                                    ->defaultSort('name')
                                    ->paginate((int) $perPage)
                                    ->withQueryString();

         return $this->responsePaginate(
            PermissionResource::collection($permissions),
            'Daftar permission berhasil diambil'
         );
      }

      public function store(StorePermissionRequest $request): JsonResponse {
         if($error = check_permission('create-permissions', 'Anda tidak memiliki izin untuk membuat permission')){
            return $error;
         }
         $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'api',
         ]);

         return $this->responseResource(
            new PermissionResource($permission),
            'Permission berhasil dibuat',
            201
         );
      }

      public function show(string $id): JsonResponse {
         if($error = check_permission('view-permissions', 'Anda tidak memiliki izin untuk melihat detail permission')){
            return $error;
         }
         $permission = Permission::findOrFail($id);

         return $this->responseResource(
            new PermissionResource($permission),
            'Detail permission berhasil diambil'
         );
      }

      public function update(UpdatePermissionRequest $request, string $id): JsonResponse {
         if($error = check_permission('edit-permissions', 'Anda tidak memiliki izin untuk mengubah permission')){
            return $error;
         }
         $permission = Permission::findOrFail($id);
         $permission->update([
            'name' => $request->name ?? $permission->name,
         ]);

         return $this->responseResource(
            new PermissionResource($permission),
            'Permission berhasil diperbarui'
         );
      }

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

         return $this->responseSuccess('Permission berhasil dihapus');
      }
   }