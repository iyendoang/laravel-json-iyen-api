// resources/js/services/admin/setting.service.ts
import api from '@/lib/api'
import { unwrapOrThrow, unwrapOrDefault } from '@/utils/services-helper'
import type { ApiResponse, SystemSettingsData, SettingFilesPayload } from '@/types'

export const settingService = {
    /**
     * Ambil pengaturan publik (tanpa auth)
     */
    async getSettings(): Promise<SystemSettingsData> {
        const { data } = await api.get<ApiResponse<SystemSettingsData>>('/system/settings')
        return unwrapOrDefault(data, {} as SystemSettingsData, {
            showError: true,
            errorMessage: 'Gagal memuat pengaturan sistem',
        })
    },

    /**
     * Ambil pengaturan admin (dengan auth)
     */
    async getAdminSettings(): Promise<SystemSettingsData> {
        const { data } = await api.get<ApiResponse<SystemSettingsData>>('/admin/settings')
        return unwrapOrDefault(data, {} as SystemSettingsData, {
            showError: true,
            errorMessage: 'Gagal memuat pengaturan sistem',
        })
    },

    /**
     * Update settings (Hanya data teks / JSON murni)
     */
    async updateSettings(settings: Record<string, any>): Promise<boolean> {
        try {
            const { data } = await api.post<ApiResponse>('/admin/settings', { settings })
            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Pengaturan berhasil disimpan',
                showError: true,
                errorMessage: 'Gagal menyimpan pengaturan',
            })
            return true
        } catch (error) {
            console.error('Update settings error:', error)
            return false
        }
    },

    /**
     * Update settings dengan file upload (Logo, Favicon, Banner, dll)
     */
    async updateSettingsWithFiles(
        settings: Record<string, any>,
        files?: SettingFilesPayload
    ): Promise<boolean> {
        try {
            const formData = new FormData()

            // Kirim payload teks settings sebagai JSON string
            formData.append('settings', JSON.stringify(settings))

            // Lampirkan file jika tersedia
            if (files) {
                Object.entries(files).forEach(([key, file]) => {
                    if (file instanceof File) {
                        formData.append(key, file)
                    }
                })
            }

            const { data } = await api.post<ApiResponse>('/admin/settings', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            })

            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Pengaturan profil perusahaan berhasil disimpan',
                showError: true,
                errorMessage: 'Gagal menyimpan pengaturan',
            })
            return true
        } catch (error) {
            console.error('Update settings with files error:', error)
            return false
        }
    },

    /**
     * Alias backward-compatible untuk update dengan logo saja
     */
    async updateSettingsWithLogo(
        settings: Record<string, any>,
        logoFile?: File | null
    ): Promise<boolean> {
        return this.updateSettingsWithFiles(settings, { app_logo: logoFile })
    },
}