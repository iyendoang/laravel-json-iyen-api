import {defineStore} from 'pinia'
import {ref, computed} from 'vue'
import {settingService} from '@/services/admin/setting.service'
import type {SystemSettingsData} from '@/types'

export const useSettingStore = defineStore('setting', () => {
    const settings = ref<SystemSettingsData>({
        app_name: 'Laravel API',
        app_slogan: '',
        app_logo: '',
        app_logo_raw: '',
        contact_email: '',
        contact_phone: '',
        contact_address: '',
    })

    const isInitialized = ref(false)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    const appName = computed(() => settings.value.app_name || 'Laravel API')
    const appSlogan = computed(() => settings.value.app_slogan || '')
    const appLogo = computed(() => settings.value.app_logo || '')
    const appLogoRaw = computed(() => settings.value.app_logo_raw || '')
    const appContact = computed(() => ({
        email: settings.value.contact_email || '',
        phone: settings.value.contact_phone || '',
        address: settings.value.contact_address || '',
    }))

    const isReady = computed(() => isInitialized.value && !isLoading.value)

    const initializeSettings = async (forceRefresh = false): Promise<void> => {
        if (isInitialized.value && !forceRefresh) return

        isLoading.value = true
        error.value = null

        try {
            const data = await settingService.getSettings()
            settings.value = {...settings.value, ...data}
            isInitialized.value = true
            updateDocumentTitle()
        } catch (err: any) {
            error.value = err?.message || 'Gagal memuat pengaturan'
            console.error('Setting Initialization Failed:', err)
        } finally {
            isLoading.value = false
        }
    }

    const saveSettings = async (
        newSettings: Record<string, any>,
        logoFile?: File | null
    ): Promise<boolean> => {
        isLoading.value = true
        error.value = null

        try {
            let success = false
            if (logoFile) {
                success = await settingService.updateSettingsWithLogo(newSettings, logoFile)
            } else {
                success = await settingService.updateSettings(newSettings)
            }

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

    const updateLocalSettings = (newSettings: Partial<SystemSettingsData>): void => {
        settings.value = {...settings.value, ...newSettings}
        updateDocumentTitle()
    }

    const resetSettings = (): void => {
        settings.value = {
            app_name: 'Laravel API',
            app_slogan: '',
            app_logo: '',
            app_logo_raw: '',
            contact_email: '',
            contact_phone: '',
            contact_address: '',
        }
        isInitialized.value = false
        error.value = null
    }

    const updateDocumentTitle = (): void => {
        if (settings.value.app_name) {
            document.title = settings.value.app_slogan
                ? `${settings.value.app_name} - ${settings.value.app_slogan}`
                : settings.value.app_name
        }
    }

    return {
        settings,
        isInitialized,
        isLoading,
        error,
        appName,
        appSlogan,
        appLogo,
        appLogoRaw,
        appContact,
        isReady,
        initializeSettings,
        saveSettings,
        updateLocalSettings,
        resetSettings,
    }
})