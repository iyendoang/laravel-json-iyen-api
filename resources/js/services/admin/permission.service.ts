import api from '@/lib/api'
import {unwrapOrThrow} from '@/utils/services-helper'
import type {ApiResponse, Permission, CreatePermissionData, UpdatePermissionData, PaginatedApiResponse} from '@/types'
import type {DataTableQuery} from '@/composables/useDataTable'

export const permissionService = {
    /**
     * Get permissions dengan params untuk DataTable
     * DataTable handle error sendiri
     */
    async getPermissionsWithParams(params: DataTableQuery): Promise<PaginatedApiResponse<Permission>> {
        const {data} = await api.get<PaginatedApiResponse<Permission>>('/admin/permissions', {params})
        return data
    },

    /**
     * Create permission - useForm handle error
     */
    async createPermission(permissionData: CreatePermissionData): Promise<ApiResponse<Permission>> {
        const {data} = await api.post<ApiResponse<Permission>>('/admin/permissions', permissionData)
        return data
    },

    /**
     * Update permission - useForm handle error
     */
    async updatePermission(id: string, permissionData: UpdatePermissionData): Promise<ApiResponse<Permission>> {
        const {data} = await api.put<ApiResponse<Permission>>(`/admin/permissions/${id}`, permissionData)
        return data
    },

    /**
     * Delete permission - unwrapOrThrow untuk toast + error handling
     */
    async deletePermission(id: string): Promise<boolean> {
        try {
            const {data} = await api.delete<ApiResponse>(`/admin/permissions/${id}`)
            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Permission berhasil dihapus',
                showError: true,
                errorMessage: 'Gagal menghapus permission',
            })
            return true
        } catch (error) {
            return false
        }
    },
}