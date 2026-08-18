import api from '@/lib/api'
import { unwrapOrThrow, unwrapOrNull, unwrapOrDefault } from '@/utils/services-helper'
import type {
    ApiResponse,
    User,
    CreateUserData,
    UpdateUserData,
    PaginatedApiResponse,
    OptionItem,
} from '@/types'
import type { DataTableQuery } from '@/composables/useDataTable'

export const userService = {
    async getUsersWithParams(params: DataTableQuery): Promise<PaginatedApiResponse<User>> {
        const { data } = await api.get<PaginatedApiResponse<User>>('/admin/users', { params })
        return data
    },

    async getUserOptions(): Promise<OptionItem[]> {
        const { data } = await api.get<ApiResponse<OptionItem[]>>('/admin/options/users')
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar user',
        })
    },

    async getUser(id: string): Promise<User | null> {
        const { data } = await api.get<ApiResponse<User>>(`/admin/users/${id}`)
        return unwrapOrNull(data, {
            showError: true,
            errorMessage: 'Gagal memuat detail user',
        })
    },

    // Create dengan dukungan FormData
    async createUser(userData: CreateUserData | FormData): Promise<ApiResponse<User>> {
        const isFormData = userData instanceof FormData

        if (isFormData) {
            const { data } = await api.post<ApiResponse<User>>(
                '/admin/users',
                userData,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            )
            return data
        }

        const { data } = await api.post<ApiResponse<User>>('/admin/users', userData)
        return data
    },

    // Update dengan dukungan FormData
    async updateUser(id: string, userData: UpdateUserData | FormData): Promise<ApiResponse<User>> {
        const isFormData = userData instanceof FormData

        if (isFormData) {
            // Untuk FormData, gunakan POST dengan _method=PUT
            userData.append('_method', 'PUT')

            const { data } = await api.post<ApiResponse<User>>(
                `/admin/users/${id}`,
                userData,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            )
            return data
        }

        const { data } = await api.put<ApiResponse<User>>(`/admin/users/${id}`, userData)
        return data
    },

    async deleteUser(id: string): Promise<boolean> {
        try {
            const { data } = await api.delete<ApiResponse>(`/admin/users/${id}`)
            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'User berhasil dihapus',
                showError: true,
                errorMessage: 'Gagal menghapus user',
            })
            return true
        } catch (error) {
            return false
        }
    },
}