import {toast} from 'vue-sonner'
import type {ApiResponse, ApiError} from '@/types/api'

// ============================================
// OPTIONS TYPE
// ============================================

export interface UnwrapOptions {
    /** Tampilkan toast success */
    showSuccess?: boolean
    /** Pesan success kustom */
    successMessage?: string
    /** Tampilkan toast error */
    showError?: boolean
    /** Pesan error kustom */
    errorMessage?: string
    /** Throw error atau return null */
    throwOnError?: boolean
}

// ============================================
// MAIN FUNCTION
// ============================================

/**
 * Unwrap response API dan handle error secara otomatis
 *
 * @param response - Response dari API
 * @param options - Opsi konfigurasi
 * @returns Data dari response atau null jika error
 *
 * @example
 * const user = unwrapResponse(response, { showSuccess: true })
 */
export function unwrapResponse<T>(
    response: ApiResponse<T>,
    options?: UnwrapOptions
): T | null {
    // Handle error response
    if (response.status === 'error') {
        // Tampilkan toast error
        if (options?.showError !== false) {
            toast.error(options?.errorMessage || response.message || 'Terjadi kesalahan')
        }

        // Throw error jika diminta
        if (options?.throwOnError !== false) {
            const error: any = new Error(response.message)
            error.response = {data: response}
            throw error
        }

        return null
    }

    // Handle success response
    if (options?.showSuccess) {
        toast.success(options.successMessage || response.message || 'Berhasil')
    }

    // Return data (bisa undefined jika tidak ada)
    return response.data as T
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Unwrap response dan return data (throw error jika gagal)
 */
export function unwrapOrThrow<T>(
    response: ApiResponse<T>,
    options?: UnwrapOptions
): T {
    if (response.status === 'error') {
        if (options?.showError !== false) {
            toast.error(options?.errorMessage || response.message || 'Terjadi kesalahan')
        }

        const error: any = new Error(response.message)
        error.response = {data: response}
        throw error
    }

    if (options?.showSuccess) {
        toast.success(options.successMessage || response.message || 'Berhasil')
    }

    return response.data as T
}

/**
 * Unwrap response dan return data (return null jika gagal, tanpa throw)
 */
export function unwrapOrNull<T>(
    response: ApiResponse<T>,
    options?: UnwrapOptions
): T | null {
    if (response.status === 'error') {
        if (options?.showError !== false) {
            toast.error(options?.errorMessage || response.message || 'Terjadi kesalahan')
        }
        return null
    }

    if (options?.showSuccess) {
        toast.success(options.successMessage || response.message || 'Berhasil')
    }

    return response.data as T
}

/**
 * Unwrap response dan return default value jika gagal
 */
export function unwrapOrDefault<T>(
    response: ApiResponse<T>,
    defaultValue: T,
    options?: UnwrapOptions
): T {
    if (response.status === 'error') {
        if (options?.showError !== false) {
            toast.error(options?.errorMessage || response.message || 'Terjadi kesalahan')
        }
        return defaultValue
    }

    if (options?.showSuccess) {
        toast.success(options.successMessage || response.message || 'Berhasil')
    }

    return response.data as T || defaultValue
}

// ============================================
// ERROR HANDLER
// ============================================

/**
 * Handle error dari try-catch dan tampilkan toast
 */
export function handleApiError(
    error: any,
    fallbackMessage: string = 'Terjadi kesalahan'
): void {
    const message = error?.response?.data?.message || error?.message || fallbackMessage
    toast.error(message)
}

// ============================================
// VALIDATION ERROR HANDLER
// ============================================

/**
 * Extract validation errors dari response
 */
export function getValidationErrors(
    error: any
): Record<string, string[]> {
    return error?.response?.data?.errors || {}
}

/**
 * Tampilkan semua validation errors sebagai toast
 */
export function showValidationErrors(
    error: any,
    fallbackMessage: string = 'Validasi gagal'
): void {
    const errors = getValidationErrors(error)

    if (Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0][0]
        toast.error(firstError || fallbackMessage)
    } else {
        handleApiError(error, fallbackMessage)
    }
}

// ============================================
// TYPE GUARDS
// ============================================

/**
 * Check if response is error
 */
export function isApiError(response: ApiResponse<any>): response is ApiError {
    return response.status === 'error'
}

/**
 * Check if response has data
 */
export function hasData<T>(response: ApiResponse<T>): response is ApiResponse<T> & { data: T } {
    return response.status === 'success' && response.data !== undefined
}