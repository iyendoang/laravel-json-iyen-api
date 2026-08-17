// ============================================
// SETTING TYPES
// ============================================

export interface SystemSettingsData {
    app_name: string
    app_slogan: string
    app_logo: string
    app_logo_raw?: string
    contact_email: string
    contact_phone: string
    contact_address: string
    [key: string]: any
}

export interface SettingPayload {
    settings: Record<string, any>
}

export interface SettingResponse {
    status: 'success' | 'error'
    message: string
    data?: SystemSettingsData
}