import api from '@/lib/api'
import {unwrapOrThrow, unwrapOrNull, unwrapOrDefault} from '@/utils/services-helper'
import type {ApiResponse, Role, CreateRoleData, UpdateRoleData, PaginatedApiResponse, OptionItem} from '@/types'
import type {DataTableQuery} from '@/composables/useDataTable'

export const roleService = {
    async getRolesWithParams(params: DataTableQuery): Promise<PaginatedApiResponse<Role>> {
        const {data} = await api.get<PaginatedApiResponse<Role>>('/admin/roles', {params})
        return data
    },

    // 🔥 Get roles untuk dropdown
    async getRoleOptions(): Promise<OptionItem[]> {
        const {data} = await api.get<ApiResponse<OptionItem[]>>('/admin/options/roles-all')
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar role',
        })
    },

    async getRole(id: string): Promise<Role | null> {
        const {data} = await api.get<ApiResponse<Role>>(`/admin/roles/${id}`)
        return unwrapOrNull(data, {
            showError: true,
            errorMessage: 'Gagal memuat detail role',
        })
    },

    async createRole(roleData: CreateRoleData): Promise<ApiResponse<Role>> {
        const {data} = await api.post<ApiResponse<Role>>('/admin/roles', roleData)
        return data
    },

    async updateRole(id: string, roleData: UpdateRoleData): Promise<ApiResponse<Role>> {
        const {data} = await api.put<ApiResponse<Role>>(`/admin/roles/${id}`, roleData)
        return data
    },

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