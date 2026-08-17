import api from '@/lib/api'
import {unwrapOrThrow} from '@/utils/services-helper'
import type {ApiResponse, User, LoginCredentials, RegisterData, AuthResponse} from '@/types'

export const authService = {
    /**
     * Login user
     */
    async login(credentials: LoginCredentials): Promise<AuthResponse> {
        const {data} = await api.post<ApiResponse<AuthResponse>>('/auth/login', credentials)
        return unwrapOrThrow(data, {
            showSuccess: true,
            successMessage: 'Login berhasil',
            showError: true,
            errorMessage: 'Login gagal',
        })
    },

    /**
     * Register user baru
     */
    async register(userData: RegisterData): Promise<AuthResponse> {
        const {data} = await api.post<ApiResponse<AuthResponse>>('/auth/register', userData)
        return unwrapOrThrow(data, {
            showSuccess: true,
            successMessage: 'Registrasi berhasil',
            showError: true,
            errorMessage: 'Registrasi gagal',
        })
    },

    /**
     * Logout user
     */
    async logout(): Promise<void> {
        const {data} = await api.post<ApiResponse>('/auth/logout')
        unwrapOrThrow(data, {
            showSuccess: true,
            successMessage: 'Logout berhasil',
        })
    },

    /**
     * Refresh token
     */
    async refresh(): Promise<AuthResponse> {
        const {data} = await api.post<ApiResponse<AuthResponse>>('/auth/refresh')
        return unwrapOrThrow(data, {
            showError: true,
            errorMessage: 'Gagal refresh token',
        })
    },

    /**
     * Get authenticated user
     */
    async me(): Promise<User> {
        const {data} = await api.get<ApiResponse<User>>('/auth/me')
        return unwrapOrThrow(data, {
            showError: true,
            errorMessage: 'Gagal memuat data user',
        })
    },
}