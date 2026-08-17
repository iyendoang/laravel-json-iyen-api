import api from '@/lib/api'
import {unwrapOrThrow, unwrapOrNull, unwrapOrDefault} from '@/utils/services-helper'
import type {ApiResponse, Permission, CreatePermissionData, UpdatePermissionData, PaginatedApiResponse} from '@/types'
import type {DataTableQuery} from '@/composables/useDataTable'

export const permissionService = {
    /**
     * Get permissions dengan params untuk DataTable
     */
    async getPermissionsWithParams(params: DataTableQuery): Promise<PaginatedApiResponse<Permission>> {
        const {data} = await api.get<PaginatedApiResponse<Permission>>('/admin/permissions', {params})
        return data
    },

    /**
     * Get semua permissions (tanpa pagination)
     */
    async getPermissions(): Promise<Permission[]> {
        const {data} = await api.get<ApiResponse<Permission[]>>('/admin/permissions', {
            params: {per_page: 'all'}, // 🔥 Ambil semua
        })
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar permission',
        })
    },

    /**
     * Get permission by ID
     */
    async getPermission(id: string): Promise<Permission | null> {
        const {data} = await api.get<ApiResponse<Permission>>(`/admin/permissions/${id}`)
        return unwrapOrNull(data, {
            showError: true,
            errorMessage: 'Gagal memuat detail permission',
        })
    },

    /**
     * Create permission baru
     */
    async createPermission(permissionData: CreatePermissionData): Promise<Permission | null> {
        const {data} = await api.post<ApiResponse<Permission>>('/admin/permissions', permissionData)
        return unwrapOrNull(data, {
            showSuccess: true,
            successMessage: 'Permission berhasil dibuat',
            showError: true,
            errorMessage: 'Gagal membuat permission',
        })
    },

    /**
     * Update permission
     */
    async updatePermission(id: string, permissionData: UpdatePermissionData): Promise<Permission | null> {
        const {data} = await api.put<ApiResponse<Permission>>(`/admin/permissions/${id}`, permissionData)
        return unwrapOrNull(data, {
            showSuccess: true,
            successMessage: 'Permission berhasil diperbarui',
            showError: true,
            errorMessage: 'Gagal memperbarui permission',
        })
    },

    /**
     * Delete permission
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