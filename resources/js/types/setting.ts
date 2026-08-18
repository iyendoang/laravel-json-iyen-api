// ============================================
// SETTING TYPES (COMPANY PROFILE & SYSTEM)
// ============================================

export interface SystemSettingsData {
    // Identitas Brand & Aplikasi
    app_name: string
    app_slogan: string
    company_name: string
    company_tagline: string
    app_description: string
    about_us: string
    vision: string
    mission: string

    // Asset Visual / Media (URL Publik)
    app_logo: string | null
    app_favicon: string | null
    hero_image: string | null
    about_image: string | null

    // Raw Path Penyimpanan Storage
    app_logo_raw?: string | null
    app_favicon_raw?: string | null
    hero_image_raw?: string | null
    about_image_raw?: string | null

    // Kontak & Lokasi
    contact_email: string
    contact_phone: string
    contact_whatsapp: string
    contact_address: string
    google_maps_embed: string
    working_hours: string

    // Media Sosial
    social_facebook: string
    social_instagram: string
    social_twitter_x: string
    social_linkedin: string
    social_youtube: string
    social_tiktok: string
    social_github: string

    // Legalitas & Footer
    company_nib: string
    company_npwp: string
    footer_text: string

    // Index signature untuk fleksibilitas custom key lainnya
    [key: string]: any
}

export interface SettingPayload {
    settings: Record<string, any>
}

export interface SettingFilesPayload {
    app_logo?: File | null
    app_favicon?: File | null
    hero_image?: File | null
    about_image?: File | null

    [key: string]: File | null | undefined
}

export interface SettingResponse {
    status: 'success' | 'error'
    message: string
    data?: SystemSettingsData
}