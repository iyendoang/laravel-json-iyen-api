import type {MenuGroup} from '@/types/menu'
import {
    LayoutDashboard,
    Users,
    Shield,
    Key,
    Settings,
    User,
} from 'lucide-vue-next'

export const appMenuGroups: MenuGroup[] = [
    // ============================================
    // MAIN
    // ============================================
    {
        heading: 'Main',
        items: [
            {
                type: 'item',
                title: 'Dashboard',
                icon: LayoutDashboard,
                routeName: 'dashboard',
                exact: true,
            },
        ],
    },

    // ============================================
    // MANAGEMENT (Dengan Dropdown Group)
    // ============================================
    {
        heading: 'Management',
        items: [
            {
                type: 'group',
                title: 'User Management',
                icon: Users,
                defaultOpen: true,
                children: [
                    {
                        type: 'item',
                        title: 'Users',
                        routeName: 'users',
                        permissions: ['view-users'],
                        activeRules: ['users', 'users-create', 'users-edit'],
                    },
                    {
                        type: 'item',
                        title: 'Roles',
                        routeName: 'roles',
                        permissions: ['view-roles'],
                        activeRules: ['roles', 'roles-create', 'roles-edit'],
                    },
                    {
                        type: 'item',
                        title: 'Permissions',
                        routeName: 'permissions',
                        permissions: ['view-permissions'],
                        activeRules: ['permissions', 'permissions-create', 'permissions-edit'],
                    },
                ],
            },
        ],
    },

    // ============================================
    // SYSTEM
    // ============================================
    {
        heading: 'System',
        items: [
            {
                type: 'item',
                title: 'Profile',
                icon: User,
                routeName: 'profile',
                exact: true,
            },
            {
                type: 'item',
                title: 'Settings',
                icon: Settings,
                routeName: 'settings',
                roles: ['super-admin'],
                exact: true,
            },
        ],
    },
]