import {reactive, readonly} from 'vue'

export interface OverlayTaskItem {
    id?: string | number
    label: string
    status?: 'pending' | 'processing' | 'completed' | 'failed'
}

export interface ProcessOverlayOptions {
    title?: string
    description?: string
    statusStep?: string
    currentStep?: number
    totalSteps?: number
    percentage?: number
    items?: OverlayTaskItem[]
}

const defaultState: Required<ProcessOverlayOptions> & { show: boolean } = {
    show: false,
    title: 'Operasi Kritis Berjalan',
    description: 'Proses sinkronisasi data sedang berlangsung.',
    statusStep: 'Mengeksekusi perintah...',
    currentStep: 0,
    totalSteps: 0,
    percentage: 0,
    items: [],
}

const state = reactive({...defaultState})

export function useSystemProcessOverlay() {
    /**
     * Tampilkan overlay dengan konfigurasi opsi
     */
    const show = (options: ProcessOverlayOptions = {}) => {
        state.title = options.title ?? defaultState.title
        state.description = options.description ?? defaultState.description
        state.statusStep = options.statusStep ?? defaultState.statusStep
        state.currentStep = options.currentStep ?? defaultState.currentStep
        state.totalSteps = options.totalSteps ?? defaultState.totalSteps
        state.percentage = options.percentage ?? defaultState.percentage
        state.items = options.items ? [...options.items] : []
        state.show = true
    }

    /**
     * Perbarui progress / pesan status saat proses sedang berjalan
     */
    const update = (options: Partial<ProcessOverlayOptions>) => {
        if (options.title !== undefined) state.title = options.title
        if (options.description !== undefined) state.description = options.description
        if (options.statusStep !== undefined) state.statusStep = options.statusStep
        if (options.currentStep !== undefined) state.currentStep = options.currentStep
        if (options.totalSteps !== undefined) state.totalSteps = options.totalSteps
        if (options.percentage !== undefined) state.percentage = options.percentage
        if (options.items !== undefined) state.items = [...options.items]
    }

    /**
     * Tutup overlay dan kembalikan state ke default
     */
    const hide = () => {
        state.show = false
        setTimeout(() => {
            Object.assign(state, defaultState)
        }, 200) // Delay sinkron dengan durasi animasi leave
    }

    /**
     * Bungkus async function otomatis: Buka overlay -> Jalankan task -> Tutup overlay
     */
    const wrap = async <T>(
        asyncFn: () => Promise<T>,
        options: ProcessOverlayOptions = {}
    ): Promise<T> => {
        show(options)
        try {
            return await asyncFn()
        } finally {
            hide()
        }
    }

    return {
        state: readonly(state),
        show,
        update,
        hide,
        wrap,
    }
}