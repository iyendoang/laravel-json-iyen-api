import 'vue-router'
import type {Component} from 'vue'

declare module 'vue-router' {
    interface RouteMeta {
        title?: string
        requiresAuth?: boolean
        guest?: boolean
        permission?: string
        anyPermission?: string[]
        allPermissions?: string[]
        role?: string
        anyRole?: string[]
        layout?: 'AppLayout' | 'GuestLayout'
        icon?: Component
        menuGroup?: string
        menuParent?: string // 🔥 Parent group untuk dropdown
        menuOrder?: number
        hidden?: boolean
        badge?: string | number
        exact?: boolean
        activeRules?: string[]
    }
}