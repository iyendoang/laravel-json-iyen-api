import api from '@/lib/api'
import {unwrapOrThrow, unwrapOrNull, unwrapOrDefault} from '@/utils/services-helper'
import type {ApiResponse, Role, PaginatedResponse, CreateRoleData, UpdateRoleData, PaginationParams} from '@/types'

export const roleService = {
    /**
     * Get roles dengan pagination
     */
    async getRoles(params: PaginationParams = {}): Promise<Role[]> {
        const {page = 1, per_page = 15} = params

        const {data} = await api.get<ApiResponse<Role[]> & PaginatedResponse<Role>>('/admin/roles', {
            params: {page, per_page},
        })

        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar role',
        })
    },

    /**
     * Get role by ID
     */
    async getRole(id: string): Promise<Role | null> {
        const {data} = await api.get<ApiResponse<Role>>(`/admin/roles/${id}`)
        return unwrapOrNull(data, {
            showError: true,
            errorMessage: 'Gagal memuat detail role',
        })
    },

    /**
     * Create role baru
     */
    async createRole(roleData: CreateRoleData): Promise<Role | null> {
        const {data} = await api.post<ApiResponse<Role>>('/admin/roles', roleData)
        return unwrapOrNull(data, {
            showSuccess: true,
            successMessage: 'Role berhasil dibuat',
            showError: true,
            errorMessage: 'Gagal membuat role',
        })
    },

    /**
     * Update role
     */
    async updateRole(id: string, roleData: UpdateRoleData): Promise<Role | null> {
        const {data} = await api.put<ApiResponse<Role>>(`/admin/roles/${id}`, roleData)
        return unwrapOrNull(data, {
            showSuccess: true,
            successMessage: 'Role berhasil diperbarui',
            showError: true,
            errorMessage: 'Gagal memperbarui role',
        })
    },

    /**
     * Delete role
     */
    async deleteRole(id: string): Promise<boolean> {
        try {
            const {data} = await api.delete<ApiResponse>(`/admin/roles/${id}`)
            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Role berhasil dihapus',
                showError: true,
                errorMessage: 'Gagal menghapus role',
            })
            return true
        } catch (error) {
            return false
        }
    },
}