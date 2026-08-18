import api from '@/lib/api'
import {unwrapOrDefault} from '@/utils/services-helper'
import type {ApiResponse, OptionItem} from '@/types'

export const optionService = {
    /**
     * Get permissions untuk dropdown
     * Endpoint: GET /api/v1/admin/options/permissions
     */
    async getPermissionOptions(): Promise<OptionItem[]> {
        const {data} = await api.get<ApiResponse<OptionItem[]>>('/admin/options/permissions')
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar permission',
        })
    },

    /**
     * Get roles untuk dropdown
     * Endpoint: GET /api/v1/admin/options/roles
     */
    async getRoleOptions(): Promise<OptionItem[]> {
        const {data} = await api.get<ApiResponse<OptionItem[]>>('/admin/options/roles')
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar role',
        })
    },

    /**
     * Get users untuk dropdown
     * Endpoint: GET /api/v1/admin/options/users
     */
    async getUserOptions(): Promise<OptionItem[]> {
        const {data} = await api.get<ApiResponse<OptionItem[]>>('/admin/options/users')
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar user',
        })
    },

    /**
     * Get semua permissions untuk role form (dengan detail)
     * Endpoint: GET /api/v1/admin/options/permissions-all
     */
    async getPermissionOptionsAll(): Promise<OptionItem[]> {
        const {data} = await api.get<ApiResponse<OptionItem[]>>('/admin/options/permissions-all')
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar permission',
        })
    },

    /**
     * Get roles untuk assign ke user
     * Endpoint: GET /api/v1/admin/options/roles-all
     */
    async getRoleOptionsAll(): Promise<OptionItem[]> {
        const {data} = await api.get<ApiResponse<OptionItem[]>>('/admin/options/roles-all')
        return unwrapOrDefault(data, [], {
            showError: true,
            errorMessage: 'Gagal memuat daftar role',
        })
    },
}