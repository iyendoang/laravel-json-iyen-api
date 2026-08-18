<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Role\StoreRoleRequest;
   use App\Http\Requests\Role\UpdateRoleRequest;
   use App\Http\Resources\RoleResource;
   use App\Models\Role;
   use Illuminate\Http\JsonResponse;
   use Spatie\QueryBuilder\QueryBuilder;
   use Spatie\QueryBuilder\AllowedFilter;

   class RoleController extends Controller
   {
      public function index(): JsonResponse {
         if($error = check_permission('view-roles', 'Anda tidak memiliki izin untuk melihat daftar role')){
            return $error;
         }
         $perPage = request('per_page', 10);
         // 🔥 Jika "Semua", tanpa pagination
         if($perPage === 'all' || $perPage === NULL || $perPage === 'null'){
            $roles = QueryBuilder::for(Role::class)
                                 ->with('permissions')
                                 ->allowedFilters(
                                    AllowedFilter::partial('name'),
                                    AllowedFilter::exact('guard_name'),
                                 )
                                 ->allowedSorts('name', 'created_at')
                                 ->defaultSort('name')
                                 ->get();

            return $this->responseResource(
               RoleResource::collection($roles),
               'Daftar role berhasil diambil'
            );
         }
         $roles = QueryBuilder::for(Role::class)
                              ->with('permissions')
                              ->allowedFilters(
                                 AllowedFilter::partial('name'),
                                 AllowedFilter::exact('guard_name'),
                              )
                              ->allowedSorts('name', 'created_at')
                              ->defaultSort('name')
                              ->paginate((int) $perPage)
                              ->withQueryString();

         return $this->responsePaginate(
            RoleResource::collection($roles),
            'Daftar role berhasil diambil'
         );
      }

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

         return $this->responseResource(
            new RoleResource($role),
            'Role berhasil dibuat',
            201
         );
      }

      public function show(string $id): JsonResponse {
         if($error = check_permission('view-roles', 'Anda tidak memiliki izin untuk melihat detail role')){
            return $error;
         }
         $role = Role::with('permissions')->findOrFail($id);

         return $this->responseResource(
            new RoleResource($role),
            'Detail role berhasil diambil'
         );
      }

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

         return $this->responseResource(
            new RoleResource($role),
            'Role berhasil diperbarui'
         );
      }

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

         return $this->responseSuccess('Role berhasil dihapus');
      }
   }