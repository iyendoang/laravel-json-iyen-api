import type {RouteRecordRaw} from 'vue-router'
import {
    LayoutDashboard,
    Users,
    Shield,
    Key,
    Settings,
    User,
} from 'lucide-vue-next'

export const adminRoutes: RouteRecordRaw[] = [
    // ============================================
    // DASHBOARD
    // ============================================
    {
        path: '',
        name: 'dashboard',
        component: () => import('@/views/admin/DashboardView.vue'),
        meta: {
            title: 'Dashboard',
            requiresAuth: true,
            layout: 'AppLayout',
            icon: LayoutDashboard,
            menuGroup: 'Main',
            menuOrder: 1,
        },
    },

    // ============================================
    // PROFILE
    // ============================================
    {
        path: 'profile',
        name: 'profile',
        component: () => import('@/views/admin/ProfileView.vue'),
        meta: {
            title: 'Profile',
            requiresAuth: true,
            layout: 'AppLayout',
            icon: User,
            menuGroup: 'System',
            menuOrder: 99,
        },
    },

    // ============================================
    // USER MANAGEMENT
    // ============================================
    {
        path: 'users',
        name: 'users',
        component: () => import('@/views/admin/users/UserListView.vue'),
        meta: {
            title: 'Users',
            requiresAuth: true,
            permission: 'view-users',
            layout: 'AppLayout',
            icon: Users,
            menuGroup: 'Management',
            menuOrder: 10,
        },
    },

    // ============================================
    // ROLE MANAGEMENT
    // ============================================
    {
        path: 'roles',
        name: 'roles',
        component: () => import('@/views/admin/roles/RoleListView.vue'),
        meta: {
            title: 'Roles',
            requiresAuth: true,
            permission: 'view-roles',
            layout: 'AppLayout',
            icon: Shield,
            menuGroup: 'Management',
            menuOrder: 20,
        },
    },

    // ============================================
    // PERMISSION MANAGEMENT
    // ============================================
    {
        path: 'permissions',
        name: 'permissions',
        component: () => import('@/views/admin/permissions/PermissionListView.vue'),
        meta: {
            title: 'Permissions',
            requiresAuth: true,
            permission: 'view-permissions',
            layout: 'AppLayout',
            icon: Key,
            menuGroup: 'Management',
            menuOrder: 30,
        },
    },

    // ============================================
    // SETTINGS
    // ============================================
    {
        path: 'settings',
        name: 'settings',
        component: () => import('@/views/admin/settings/SettingView.vue'),
        meta: {
            title: 'Settings',
            requiresAuth: true,
            role: 'super-admin',
            layout: 'AppLayout',
            icon: Settings,
            menuGroup: 'System',
            menuOrder: 100,
        },
    },
]