import {defineConfig} from 'vitest/config'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

export default defineConfig({
    // Plugins internal proyek Laravel, Vue 3, dan Tailwind CSS v4
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false
                }
            }
        }),
        tailwindcss()
    ],

    // 🔥 FIX: chunkSizeWarningLimit dipindahkan ke dalam objek 'build' agar sesuai dengan overload signature Vitest/Vite
    build: {
        chunkSizeWarningLimit: 1000
    },

    // Konfigurasi unit testing menggunakan Vitest (Aman dari komplain TypeScript)
    test: {
        environment: 'jsdom',
        globals: true
    },

    // Konfigurasi mapping jalur folder alias proyek
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@css': path.resolve(__dirname, './resources/css')
        }
    }
})
