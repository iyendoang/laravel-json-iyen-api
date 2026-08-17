<?php

   namespace App\Http\Controllers\Api\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\Permission\StorePermissionRequest;
   use App\Http\Requests\Permission\UpdatePermissionRequest;
   use App\Http\Resources\PermissionResource;
   use App\Models\Permission;
   use Illuminate\Http\JsonResponse;

   class PermissionController extends Controller
   {
      public function index(): JsonResponse {
         if($error = check_permission('view-permissions', 'Anda tidak memiliki izin untuk melihat daftar permission')){
            return $error;
         }
         $permissions = Permission::orderBy('name')->get();

         return $this->responseResource(
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