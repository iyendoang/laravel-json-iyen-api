// resources/js/stores/setting-store.ts
import {defineStore} from 'pinia'
import {ref, computed} from 'vue'
import {settingService} from '@/services/admin/setting.service'
import type {SystemSettingsData, SettingFilesPayload} from '@/types'

export const useSettingStore = defineStore('setting', () => {
    const defaultSettings: SystemSettingsData = {
        // Identitas Aplikasi & Brand
        app_name: 'Laravel API',
        app_slogan: '',
        company_name: '',
        company_tagline: '',
        app_description: '',
        about_us: '',
        vision: '',
        mission: '',

        // Media & Visual (URL Publik)
        app_logo: '',
        app_favicon: '',
        hero_image: '',
        about_image: '',

        // Media (Raw Storage Path)
        app_logo_raw: '',
        app_favicon_raw: '',
        hero_image_raw: '',
        about_image_raw: '',

        // Kontak & Lokasi
        contact_email: '',
        contact_phone: '',
        contact_whatsapp: '',
        contact_address: '',
        google_maps_embed: '',
        working_hours: '',

        // Media Sosial
        social_facebook: '',
        social_instagram: '',
        social_twitter_x: '',
        social_linkedin: '',
        social_youtube: '',
        social_tiktok: '',
        social_github: '',

        // Legalitas & Footer
        company_nib: '',
        company_npwp: '',
        footer_text: '© 2026 Laravel API. All rights reserved.',
    }

    const settings = ref<SystemSettingsData>({...defaultSettings})
    const isInitialized = ref(false)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // ============================================
    // COMPUTED / GETTERS
    // ============================================
    const appName = computed(() => settings.value.app_name || 'Laravel API')
    const appSlogan = computed(() => settings.value.app_slogan || '')
    const appLogo = computed(() => settings.value.app_logo || '')
    const appLogoRaw = computed(() => settings.value.app_logo_raw || '')
    const appFavicon = computed(() => settings.value.app_favicon || '')

    const companyInfo = computed(() => ({
        name: settings.value.company_name || settings.value.app_name || '',
        tagline: settings.value.company_tagline || settings.value.app_slogan || '',
        description: settings.value.app_description || '',
        aboutUs: settings.value.about_us || '',
        vision: settings.value.vision || '',
        mission: settings.value.mission || '',
        nib: settings.value.company_nib || '',
        npwp: settings.value.company_npwp || '',
    }))

    const appContact = computed(() => ({
        email: settings.value.contact_email || '',
        phone: settings.value.contact_phone || '',
        whatsapp: settings.value.contact_whatsapp || '',
        address: settings.value.contact_address || '',
        googleMaps: settings.value.google_maps_embed || '',
        workingHours: settings.value.working_hours || '',
    }))

    const socialLinks = computed(() => ({
        facebook: settings.value.social_facebook || '',
        instagram: settings.value.social_instagram || '',
        twitterX: settings.value.social_twitter_x || '',
        linkedin: settings.value.social_linkedin || '',
        youtube: settings.value.social_youtube || '',
        tiktok: settings.value.social_tiktok || '',
        github: settings.value.social_github || '',
    }))

    const footerText = computed(() => settings.value.footer_text || '')
    const isReady = computed(() => isInitialized.value && !isLoading.value)

    // ============================================
    // ACTIONS
    // ============================================

    /**
     * Inisialisasi atau refresh pengaturan dari API backend
     */
    const initializeSettings = async (forceRefresh = false): Promise<void> => {
        if (isInitialized.value && !forceRefresh) return

        isLoading.value = true
        error.value = null

        try {
            const data = await settingService.getSettings()
            settings.value = {...settings.value, ...data}
            isInitialized.value = true
            updateDocumentTitle()
            updateDocumentFavicon()
        } catch (err: any) {
            error.value = err?.message || 'Gagal memuat pengaturan'
            console.error('Setting Initialization Failed:', err)
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Simpan pengaturan dengan dukungan multi-file upload (Logo, Favicon, Banner, dll)
     */
    const saveSettingsWithFiles = async (
        newSettings: Record<string, any>,
        files?: SettingFilesPayload
    ): Promise<boolean> => {
        isLoading.value = true
        error.value = null

        try {
            const success = await settingService.updateSettingsWithFiles(newSettings, files)
            if (success) {
                updateLocalSettings(newSettings)
                return true
            }
            return false
        } catch (err: any) {
            error.value = err?.message || 'Gagal menyimpan pengaturan'
            console.error('Setting Save Failed:', err)
            return false
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Simpan pengaturan (hanya logo atau teks saja)
     */
    const saveSettings = async (
        newSettings: Record<string, any>,
        logoFile?: File | null
    ): Promise<boolean> => {
        return saveSettingsWithFiles(newSettings, {app_logo: logoFile})
    }

    /**
     * Update state local secara instan
     */
    const updateLocalSettings = (newSettings: Partial<SystemSettingsData>): void => {
        settings.value = {...settings.value, ...newSettings}
        updateDocumentTitle()
        updateDocumentFavicon()
    }

    /**
     * Reset state store ke nilai awal
     */
    const resetSettings = (): void => {
        settings.value = {...defaultSettings}
        isInitialized.value = false
        error.value = null
    }

    /**
     * Helper sinkronisasi title browser
     */
    const updateDocumentTitle = (): void => {
        if (settings.value.app_name) {
            document.title = settings.value.app_slogan
                ? `${settings.value.app_name} - ${settings.value.app_slogan}`
                : settings.value.app_name
        }
    }

    /**
     * Helper sinkronisasi icon favicon browser secara dinamis
     */
    const updateDocumentFavicon = (): void => {
        if (settings.value.app_favicon) {
            let link = document.querySelector<HTMLLinkElement>("link[rel~='icon']")
            if (!link) {
                link = document.createElement('link')
                link.rel = 'icon'
                document.getElementsByTagName('head')[0].appendChild(link)
            }
            link.href = settings.value.app_favicon
        }
    }

    return {
        // State
        settings,
        isInitialized,
        isLoading,
        error,
        isReady,

        // Getters
        appName,
        appSlogan,
        appLogo,
        appLogoRaw,
        appFavicon,
        companyInfo,
        appContact,
        socialLinks,
        footerText,

        // Actions
        initializeSettings,
        saveSettings,
        saveSettingsWithFiles,
        updateLocalSettings,
        resetSettings,
    }
})