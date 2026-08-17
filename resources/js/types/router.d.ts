import 'vue-router'

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
        // Menu config
        icon?: Component
        menuGroup?: string
        menuOrder?: number
        hidden?: boolean
        badge?: string | number
    }
}