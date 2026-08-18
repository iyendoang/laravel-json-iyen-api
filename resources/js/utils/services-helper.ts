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
    /** Callback saat ada validation errors */
    onValidationError?: (errors: Record<string, string[]>) => void
}

// ============================================
// ERROR TYPES
// ============================================

export interface ApiErrorWithValidation extends Error {
    response?: {
        data?: {
            status?: string
            message?: string
            errors?: Record<string, string[]>
        }
        status?: number
    }
    validationErrors?: Record<string, string[]>
}

// ============================================
// MAIN FUNCTION
// ============================================

export function unwrapResponse<T>(
    response: ApiResponse<T>,
    options?: UnwrapOptions
): T | null {
    if (response.status === 'error') {
        // 🔥 Jika ada validation errors, panggil callback
        if (response.errors && Object.keys(response.errors).length > 0) {
            options?.onValidationError?.(response.errors)
        }

        // 🔥 Tampilkan toast error (pesan pertama dari errors atau message)
        if (options?.showError !== false) {
            const firstError = response.errors
                ? Object.values(response.errors)[0]?.[0]
                : undefined
            toast.error(
                options?.errorMessage || firstError || response.message || 'Terjadi kesalahan'
            )
        }

        if (options?.throwOnError !== false) {
            const error: ApiErrorWithValidation = new Error(response.message) as ApiErrorWithValidation
            error.response = {data: response}
            error.validationErrors = response.errors
            throw error
        }

        return null
    }

    if (options?.showSuccess) {
        toast.success(options.successMessage || response.message || 'Berhasil')
    }

    return response.data as T
}

// ============================================
// HELPER FUNCTIONS
// ============================================

export function unwrapOrThrow<T>(
    response: ApiResponse<T>,
    options?: UnwrapOptions
): T {
    if (response.status === 'error') {
        if (response.errors && Object.keys(response.errors).length > 0) {
            options?.onValidationError?.(response.errors)
        }

        if (options?.showError !== false) {
            const firstError = response.errors
                ? Object.values(response.errors)[0]?.[0]
                : undefined
            toast.error(
                options?.errorMessage || firstError || response.message || 'Terjadi kesalahan'
            )
        }

        const error: ApiErrorWithValidation = new Error(response.message) as ApiErrorWithValidation
        error.response = {data: response}
        error.validationErrors = response.errors
        throw error
    }

    if (options?.showSuccess) {
        toast.success(options.successMessage || response.message || 'Berhasil')
    }

    return response.data as T
}

export function unwrapOrNull<T>(
    response: ApiResponse<T>,
    options?: UnwrapOptions
): T | null {
    if (response.status === 'error') {
        if (response.errors && Object.keys(response.errors).length > 0) {
            options?.onValidationError?.(response.errors)
        }

        if (options?.showError !== false) {
            const firstError = response.errors
                ? Object.values(response.errors)[0]?.[0]
                : undefined
            toast.error(
                options?.errorMessage || firstError || response.message || 'Terjadi kesalahan'
            )
        }
        return null
    }

    if (options?.showSuccess) {
        toast.success(options.successMessage || response.message || 'Berhasil')
    }

    return response.data as T
}

export function unwrapOrDefault<T>(
    response: ApiResponse<T>,
    defaultValue: T,
    options?: UnwrapOptions
): T {
    if (response.status === 'error') {
        if (response.errors && Object.keys(response.errors).length > 0) {
            options?.onValidationError?.(response.errors)
        }

        if (options?.showError !== false) {
            const firstError = response.errors
                ? Object.values(response.errors)[0]?.[0]
                : undefined
            toast.error(
                options?.errorMessage || firstError || response.message || 'Terjadi kesalahan'
            )
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

export function handleApiError(
    error: ApiErrorWithValidation,
    fallbackMessage: string = 'Terjadi kesalahan'
): void {
    // 🔥 Cek validation errors dulu
    const validationErrors = error?.response?.data?.errors || error?.validationErrors
    if (validationErrors && Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.error(firstError || fallbackMessage)
        return
    }

    const message = error?.response?.data?.message || error?.message || fallbackMessage
    toast.error(message)
}

// ============================================
// VALIDATION ERROR HANDLER
// ============================================

export function getValidationErrors(
    error: ApiErrorWithValidation
): Record<string, string[]> {
    return error?.response?.data?.errors || error?.validationErrors || {}
}

export function showValidationErrors(
    error: ApiErrorWithValidation,
    fallbackMessage: string = 'Validasi gagal'
): void {
    const errors = getValidationErrors(error)

    if (Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0]?.[0]
        toast.error(firstError || fallbackMessage)
    } else {
        handleApiError(error, fallbackMessage)
    }
}

// ============================================
// TYPE GUARDS
// ============================================

export function isApiError(response: ApiResponse<any>): response is ApiError {
    return response.status === 'error'
}

export function hasData<T>(response: ApiResponse<T>): response is ApiResponse<T> & { data: T } {
    return response.status === 'success' && response.data !== undefined
}