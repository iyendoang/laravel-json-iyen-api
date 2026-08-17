import api from '@/lib/api'
import {unwrapOrThrow, unwrapOrNull, unwrapOrDefault} from '@/utils/services-helper'
import type {
    ApiResponse,
    User,
    PaginatedResponse,
    CreateUserData,
    UpdateUserData,
    PaginationParams,
    PaginatedApiResponse
} from '@/types'
import type {DataTableQuery} from '@/composables/useDataTable'

export const userService = {
    /**
     * Get users dengan params untuk DataTable
     */
    async getUsersWithParams(params: DataTableQuery): Promise<PaginatedApiResponse<User>> {
        const {data} = await api.get<PaginatedApiResponse<User>>('/admin/users', {params})
        return data
    },

    /**
     * Get users dengan pagination sederhana
     */
    async getUsers(params: PaginationParams = {}): Promise<User[]> {
        const {page = 1, per_page = 15} = params

        const {data} = await api.get<ApiResponse<User[]> & PaginatedResponse<User>>('/admin/users', {
            params: {page, per_page},
        })

        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar user',
        })
    },

    /**
     * Get user by ID
     */
    async getUser(id: string): Promise<User | null> {
        const {data} = await api.get<ApiResponse<User>>(`/admin/users/${id}`)
        return unwrapOrNull(data, {
            showError: true,
            errorMessage: 'Gagal memuat detail user',
        })
    },

    /**
     * Create user baru
     */
    async createUser(userData: CreateUserData): Promise<User | null> {
        const {data} = await api.post<ApiResponse<User>>('/admin/users', userData)
        return unwrapOrNull(data, {
            showSuccess: true,
            successMessage: 'User berhasil dibuat',
            showError: true,
            errorMessage: 'Gagal membuat user',
        })
    },

    /**
     * Update user
     */
    async updateUser(id: string, userData: UpdateUserData): Promise<User | null> {
        const {data} = await api.put<ApiResponse<User>>(`/admin/users/${id}`, userData)
        return unwrapOrNull(data, {
            showSuccess: true,
            successMessage: 'User berhasil diperbarui',
            showError: true,
            errorMessage: 'Gagal memperbarui user',
        })
    },

    /**
     * Delete user
     */
    async deleteUser(id: string): Promise<boolean> {
        try {
            const {data} = await api.delete<ApiResponse>(`/admin/users/${id}`)
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