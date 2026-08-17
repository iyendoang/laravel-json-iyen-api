import api from '@/lib/api'
import {unwrapOrThrow} from '@/utils/services-helper'
import type {ApiResponse, User, ProfileData, UpdatePasswordData} from '@/types'

export const profileService = {
    /**
     * Get profile user yang sedang login
     */
    async getProfile(): Promise<User> {
        const {data} = await api.get<ApiResponse<User>>('/profile')
        return unwrapOrThrow(data, {
            showError: true,
            errorMessage: 'Gagal memuat profile',
        })
    },

    /**
     * Update profile
     */
    async updateProfile(profileData: ProfileData): Promise<User> {
        const {data} = await api.put<ApiResponse<User>>('/profile', profileData)
        return unwrapOrThrow(data, {
            showSuccess: true,
            successMessage: 'Profile berhasil diperbarui',
            showError: true,
            errorMessage: 'Gagal memperbarui profile',
        })
    },

    /**
     * Update password
     */
    async updatePassword(passwordData: UpdatePasswordData): Promise<void> {
        const {data} = await api.put<ApiResponse>('/profile/password', passwordData)
        unwrapOrThrow(data, {
            showSuccess: true,
            successMessage: 'Password berhasil diperbarui',
            showError: true,
            errorMessage: 'Gagal memperbarui password',
        })
    },

    /**
     * Upload avatar
     */
    async updateAvatar(file: File): Promise<User> {
        const formData = new FormData()
        formData.append('avatar', file)

        const {data} = await api.post<ApiResponse<User>>('/profile/avatar', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })

        return unwrapOrThrow(data, {
            showSuccess: true,
            successMessage: 'Avatar berhasil diperbarui',
            showError: true,
            errorMessage: 'Gagal upload avatar',
        })
    },

    /**
     * Delete avatar
     */
    async deleteAvatar(): Promise<User> {
        const {data} = await api.delete<ApiResponse<User>>('/profile/avatar')
        return unwrapOrThrow(data, {
            showSuccess: true,
            successMessage: 'Avatar berhasil dihapus',
            showError: true,
            errorMessage: 'Gagal menghapus avatar',
        })
    },
}