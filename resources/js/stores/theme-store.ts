import {defineStore} from 'pinia'

export type ThemeMode = 'light' | 'dark' | 'system'
export type ThemeColor =
    | 'default'
    | 'blue'
    | 'green'
    | 'rose'
    | 'red'
    | 'violet'
    | 'yellow'
    | 'orange'
    | 'claude'
    | 'caffeine'
    | 'luxury'
    | 'marvel'
    | 'aurora'

const THEME_CLASSES = [
    'theme-default', 'theme-blue', 'theme-green', 'theme-rose', 'theme-red',
    'theme-violet', 'theme-yellow', 'theme-orange', 'theme-claude',
    'theme-caffeine', 'theme-luxury', 'theme-marvel', 'theme-aurora',
]

export const useThemeStore = defineStore('theme', {
    state: () => ({
        mode: (localStorage.getItem('theme-mode') as ThemeMode) || 'system',
        color: (localStorage.getItem('theme-color') as ThemeColor) || 'default',
        isInitialized: false,
    }),

    getters: {
        isDark: (state): boolean => {
            if (state.mode === 'system') {
                return window.matchMedia('(prefers-color-scheme: dark)').matches
            }
            return state.mode === 'dark'
        },
    },

    actions: {
        setMode(mode: ThemeMode) {
            this.mode = mode
            localStorage.setItem('theme-mode', mode)
            this.apply()
        },

        setColor(color: ThemeColor) {
            this.color = color
            localStorage.setItem('theme-color', color)
            this.apply()
        },

        apply() {
            const html = document.documentElement

            html.classList.toggle('dark', this.isDark)
            html.classList.remove(...THEME_CLASSES)
            html.classList.add(`theme-${this.color}`)
            html.style.colorScheme = this.isDark ? 'dark' : 'light'
        },

        initTheme() {
            if (this.isInitialized) return
            this.apply()
            this.isInitialized = true
        },
    },
})