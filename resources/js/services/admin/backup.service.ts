import api from '@/lib/api'
import {unwrapOrThrow, handleApiError, type ApiErrorWithValidation} from '@/utils/services-helper'
import type {ApiResponse} from '@/types/api'
import type {
    BackupItem,
    RestoreHistoryItem,
    CreateBackupResult,
    SignedUrlResult
} from '@/types/backup'

export const backupService = {
    /**
     * Mengambil daftar file backup yang tersedia
     */
    async getBackups(): Promise<BackupItem[]> {
        try {
            const {data} = await api.get<ApiResponse<BackupItem[]>>('/admin/backups')
            return unwrapOrThrow(data, {
                showError: true,
                errorMessage: 'Gagal memuat daftar backup',
            })
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal memuat daftar backup')
            throw error
        }
    },

    /**
     * Mengambil riwayat pemulihan (restore history)
     */
    async getRestoreHistories(): Promise<RestoreHistoryItem[]> {
        try {
            const {data} = await api.get<ApiResponse<RestoreHistoryItem[]>>('/admin/backups/history')
            return unwrapOrThrow(data, {
                showError: true,
                errorMessage: 'Gagal memuat riwayat restore',
            })
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal memuat riwayat restore')
            throw error
        }
    },

    /**
     * Membuat file backup baru (.sidosts terenkripsi)
     */
    async createBackup(): Promise<CreateBackupResult> {
        try {
            const {data} = await api.post<ApiResponse<CreateBackupResult>>(
                '/admin/backups',
                {},
                {
                    timeout: 600000,
                }
            )
            return unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Berkas cadangan basis data berhasil dibuat',
                showError: true,
                errorMessage: 'Gagal membuat berkas cadangan basis data',
            })
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal membuat backup database')
            throw error
        }
    },

    /**
     * Melakukan restore database dari backup yang dipilih
     */
    async restoreDatabase(backupId: string): Promise<boolean> {
        try {
            const {data} = await api.post<ApiResponse<null>>(
                '/admin/backups/restore',
                {id: backupId},
                {
                    timeout: 600000,
                }
            )
            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Basis data berhasil dipulihkan',
                showError: true,
                errorMessage: 'Gagal memulihkan basis data',
            })
            return true
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal memulihkan database')
            throw error
        }
    },

    /**
     * Generate Signed URL dan langsung memicu unduhan file ke browser
     */
    async downloadBackup(backupId: string): Promise<void> {
        try {
            const {data} = await api.post<ApiResponse<SignedUrlResult>>(`/admin/backups/${backupId}/signed-url`)
            const result = unwrapOrThrow(data, {
                showError: true,
                errorMessage: 'Gagal membuat tautan unduhan',
            })

            if (result && result.url) {
                window.location.href = result.url
            }
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal mengunduh berkas backup')
            throw error
        }
    },
    /**
     * Unggah berkas .sidosts dari komputer dan eksekusi restore
     */
    async uploadAndRestore(file: File): Promise<boolean> {
        try {
            const formData = new FormData()
            formData.append('backup_file', file)

            const {data} = await api.post<ApiResponse<null>>(
                '/admin/backups/upload-restore',
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    timeout: 600000, // 10 menit
                }
            )

            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Basis data berhasil dipulihkan dari berkas yang diunggah',
                showError: true,
                errorMessage: 'Gagal memulihkan basis data dari berkas',
            })
            return true
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal mengunggah dan merestore berkas')
            throw error
        }
    },
    /**
     * Menghapus file backup fisik dan record dari database
     */
    async deleteBackup(backupId: string): Promise<boolean> {
        try {
            const {data} = await api.delete<ApiResponse<null>>(`/admin/backups/${backupId}`)
            unwrapOrThrow(data, {
                showSuccess: true,
                successMessage: 'Berkas cadangan berhasil dihapus',
                showError: true,
                errorMessage: 'Gagal menghapus berkas cadangan',
            })
            return true
        } catch (error) {
            handleApiError(error as ApiErrorWithValidation, 'Gagal menghapus berkas cadangan')
            return false
        }
    },
}