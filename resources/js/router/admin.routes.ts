import type { RouteRecordRaw } from 'vue-router'
import {
    LayoutDashboard,
    Users,
    UserPlus,
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
            exact: true,
        },
    },

    // ============================================
    // USER MANAGEMENT (Dropdown Group)
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
            menuGroup: 'management',
            menuParent: 'user-management', // 🔥 Parent group
            menuOrder: 1,
        },
    },
    {
        path: 'users/create',
        name: 'users-create',
        component: () => import('@/views/admin/users/UserCreateView.vue'),
        meta: {
            title: 'Tambah User',
            requiresAuth: true,
            permission: 'create-users',
            layout: 'AppLayout',
            hidden: true,
            menuParent: 'user-management',
        },
    },
    {
        path: 'users/:id/edit',
        name: 'users-edit',
        component: () => import('@/views/admin/users/UserEditView.vue'),
        meta: {
            title: 'Edit User',
            requiresAuth: true,
            permission: 'edit-users',
            layout: 'AppLayout',
            hidden: true,
            menuParent: 'user-management',
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
            menuGroup: 'management',
            menuParent: 'user-management', // 🔥 Parent group
            menuOrder: 2,
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
            menuGroup: 'management',
            menuParent: 'user-management', // 🔥 Parent group
            menuOrder: 3,
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
            exact: true,
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
            exact: true,
        },
    },
]