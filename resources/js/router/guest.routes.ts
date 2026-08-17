import type {RouteRecordRaw} from 'vue-router'

export const guestRoutes: RouteRecordRaw[] = [
    {
        path: '',
        name: 'home',
        component: () => import('@/views/HomeView.vue'),
        meta: {
            title: 'Home',
            layout: 'GuestLayout',
        },
    },
    {
        path: 'login',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: {
            title: 'Login',
            guest: true,
            layout: 'GuestLayout',
        },
    },
    {
        path: 'register',
        name: 'register',
        component: () => import('@/views/auth/RegisterView.vue'),
        meta: {
            title: 'Register',
            guest: true,
            layout: 'GuestLayout',
        },
    },
]