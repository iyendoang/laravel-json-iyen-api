import {defineStore} from 'pinia'
import {computed, ref} from 'vue'
import {type CheckUpdateResponse, systemUpdateService} from '@/services/system/system-update.service'
import {useAuthStore} from '@/stores/auth-store'
import {useSystemProcessOverlay, type OverlayTaskItem} from '@/composables/useSystemProcessOverlay'

export const useSystemUpdateStore = defineStore('systemUpdate', () => {
    const authStore = useAuthStore()
    const overlay = useSystemProcessOverlay()

    const updateInfo = ref<CheckUpdateResponse | null>(null)
    const isChecking = ref(false)
    const isUpdating = ref(false)
    const error = ref<string | null>(null)

    // Cek permission langsung dari authStore
    const canCheckUpdate = computed(() => authStore.hasPermission('update-check'))
    const canExecuteUpdate = computed(() => authStore.hasPermission('update-execute'))

    const hasUpdateAvailable = computed(() => !!updateInfo.value?.has_update)
    const currentVersion = computed(() => updateInfo.value?.current_version || '0.0.0')
    const latestVersion = computed(() => updateInfo.value?.latest_version || currentVersion.value)
    const history = computed(() => updateInfo.value?.history || [])

    async function checkUpdate(force = false): Promise<void> {
        // Guard: Jangan eksekusi jika user tidak punya izin
        if (!canCheckUpdate.value) {
            return
        }

        // Hindari request berulang jika sedang loading
        if (isChecking.value) return

        isChecking.value = true
        error.value = null

        try {
            updateInfo.value = await systemUpdateService.checkUpdate()
        } catch (err: any) {
            error.value = err.message || 'Gagal mengecek pembaruan'
        } finally {
            isChecking.value = false
        }
    }

    async function executeUpdate(): Promise<boolean> {
        // Guard: Cek izin eksekusi update
        if (!canExecuteUpdate.value) {
            error.value = 'Anda tidak memiliki izin untuk melakukan update'
            return false
        }

        if (isUpdating.value) return false

        isUpdating.value = true
        error.value = null

        // Daftar tahapan pembaruan sistem untuk overlay
        const initialTasks: OverlayTaskItem[] = [
            {id: 1, label: 'Mengunduh paket rilis OTA', status: 'processing'},
            {id: 2, label: 'Ekstraksi & penimpaan berkas core', status: 'pending'},
            {id: 3, label: 'Migrasi struktur skema basis data', status: 'pending'},
            {id: 4, label: 'Pembersihan cache & optimalisasi sistem', status: 'pending'},
        ]

        try {
            // Bungkus proses dalam overlay.wrap
            return await overlay.wrap(
                async () => {
                    // Tahap 1: Mulai Request ke Server Backend
                    overlay.update({
                        currentStep: 1,
                        percentage: 25,
                        statusStep: 'Menghubungi server update & mengunduh paket...',
                    })

                    // Eksekusi API Update ke Backend Laravel
                    const result = await systemUpdateService.executeUpdate()

                    // Tahap 2 & 3: File overwriting & DB migrations
                    overlay.update({
                        currentStep: 3,
                        percentage: 75,
                        statusStep: 'Menjalankan migrasi database & regenerasi skema...',
                        items: [
                            {id: 1, label: 'Mengunduh paket rilis OTA', status: 'completed'},
                            {id: 2, label: 'Ekstraksi & penimpaan berkas core', status: 'completed'},
                            {id: 3, label: 'Migrasi struktur skema basis data', status: 'processing'},
                            {id: 4, label: 'Pembersihan cache & optimalisasi sistem', status: 'pending'},
                        ],
                    })

                    // Tahap 4: Finalisasi
                    overlay.update({
                        currentStep: 4,
                        percentage: 100,
                        statusStep: 'Membersihkan cache framework & memuat ulang status...',
                        items: [
                            {id: 1, label: 'Mengunduh paket rilis OTA', status: 'completed'},
                            {id: 2, label: 'Ekstraksi & penimpaan berkas core', status: 'completed'},
                            {id: 3, label: 'Migrasi struktur skema basis data', status: 'completed'},
                            {id: 4, label: 'Pembersihan cache & optimalisasi sistem', status: 'completed'},
                        ],
                    })

                    // Beri jeda singkat agar user melihat status selesai sebelum reload
                    await new Promise((resolve) => setTimeout(resolve, 800))

                    // Setelah update sukses, refresh info versi terbaru
                    await checkUpdate(true)
                    return true
                },
                {
                    title: `Memperbarui Sistem (v${latestVersion.value})`,
                    description: 'Sistem sedang menerapkan pembaruan rilis. Mohon tidak menutup jendela browser.',
                    statusStep: 'Menginisialisasi pipeline pembaruan...',
                    currentStep: 1,
                    totalSteps: 4,
                    percentage: 10,
                    items: initialTasks,
                }
            )
        } catch (err: any) {
            error.value = err.message || 'Gagal mengeksekusi update'
            return false
        } finally {
            isUpdating.value = false
        }
    }

    return {
        updateInfo,
        isChecking,
        isUpdating,
        error,
        canCheckUpdate,
        canExecuteUpdate,
        hasUpdateAvailable,
        currentVersion,
        latestVersion,
        history,
        checkUpdate,
        executeUpdate,
    }
})