import api from '@/lib/api'
import {handleApiError, type ApiErrorWithValidation} from '@/utils/services-helper'
import {toast} from 'vue-sonner'

export interface SystemUpdateHistoryItem {
    version_from: string
    version_to: string
    date: string
    status: 'success' | 'failed' | string
    admin: string
    log: string | null
}

export interface CheckUpdateResponse {
    success: boolean
    current_version: string
    latest_version?: string
    has_update?: boolean
    title?: string
    changelog?: string
    download_url?: string | null
    message?: string
    history: SystemUpdateHistoryItem[]
}

export interface ExecuteUpdateResponse {
    success: boolean
    message: string
}

export const systemUpdateService = {
    /**
     * Cek versi sistem ke server & ambil riwayat update terakhir
     */
    async checkUpdate(): Promise<CheckUpdateResponse> {
        try {
            const {data} = await api.get<CheckUpdateResponse>('/system/check-update')

            // Tangani jika backend mengembalikan status HTTP 200 tetapi success bernilai false
            if (!data.success && data.message) {
                toast.error(data.message)
            }

            return data
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal memeriksa pembaruan sistem')
            throw error
        }
    },

    /**
     * Eksekusi proses unduh, ekstrak, dan update core system
     */
    async executeUpdate(): Promise<ExecuteUpdateResponse> {
        try {
            // Proses download file, extract zip, dan migrate DB membutuhkan durasi lama
            const {data} = await api.post<ExecuteUpdateResponse>(
                '/system/update-now',
                {},
                {
                    timeout: 600000, // 10 menit
                }
            )

            if (!data.success) {
                toast.error(data.message || 'Gagal memperbarui sistem')
                throw new Error(data.message || 'Gagal memperbarui sistem')
            }

            toast.success(data.message || 'Sistem berhasil diperbarui. Silakan refresh.')
            return data
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal memproses pembaruan sistem')
            throw error
        }
    },
}