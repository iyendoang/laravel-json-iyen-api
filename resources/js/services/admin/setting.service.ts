import api from '@/lib/api'
import {unwrapOrThrow, unwrapOrDefault} from '@/utils/services-helper'
import type {ApiResponse, SystemSettingsData} from '@/types'

export const settingService = {
    /**
     * Get public settings
     */
    async getSettings(): Promise<SystemSettingsData> {
        const {data} = await api.get<ApiResponse<SystemSettingsData>>('/system/settings')
        return unwrapOrDefault(data, {} as SystemSettingsData, {
            showError: true,
            errorMessage: 'Gagal memuat pengaturan',
        })
    },

    /**
     * Get admin settings
     */
    async getAdminSettings(): Promise<SystemSettingsData> {
        const {data} = await api.get<ApiResponse<SystemSettingsData>>('/admin/settings')
        return unwrapOrDefault(data, {} as SystemSettingsData, {
            showError: true,
            errorMessage: 'Gagal memuat pengaturan',
        })
    },

    /**
     * Update settings
     * Returns boolean success
     */
    async updateSettings(settings: Record<string, any>): Promise<boolean> {
        try {
            const {data} = await api.post<ApiResponse>('/admin/settings', {settings})
            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Pengaturan berhasil disimpan',
                showError: true,
                errorMessage: 'Gagal menyimpan pengaturan',
            })
            return true
        } catch (error) {
            return false
        }
    },

    /**
     * Update settings dengan upload logo
     * Returns boolean success
     */
    async updateSettingsWithLogo(
        settings: Record<string, any>,
        logoFile?: File | null
    ): Promise<boolean> {
        try {
            const formData = new FormData()
            formData.append('settings', JSON.stringify(settings))

            if (logoFile) {
                formData.append('app_logo', logoFile)
            }

            const {data} = await api.post<ApiResponse>('/admin/settings', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            })

            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Pengaturan berhasil disimpan',
                showError: true,
                errorMessage: 'Gagal menyimpan pengaturan',
            })
            return true
        } catch (error) {
            return false
        }
    },
}