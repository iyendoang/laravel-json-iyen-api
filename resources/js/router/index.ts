import {createRouter, createWebHistory, RouteRecordRaw} from 'vue-router'
import {useAuthStore} from '@/stores/auth-store'
import {useSettingStore} from '@/stores/setting-store'
import {guestRoutes} from './guest.routes'
import {adminRoutes} from './admin.routes'

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        meta: {layout: 'GuestLayout'},
        children: guestRoutes,
    },
    {
        path: '/admin',
        meta: {requiresAuth: true, layout: 'AppLayout'},
        children: adminRoutes,
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/views/NotFoundView.vue'),
        meta: {title: '404 Not Found', layout: 'GuestLayout'},
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition
        if (to.hash) return {el: to.hash, behavior: 'smooth'}
        return {top: 0}
    },
})

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()
    const settingStore = useSettingStore()

    const appName = settingStore.appName || 'Laravel API'
    document.title = to.meta.title ? `${to.meta.title} - ${appName}` : appName

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
    const isGuestOnly = to.matched.some(record => record.meta.guest)

    if (requiresAuth) {
        const token = localStorage.getItem('token')
        if (!token) {
            next({name: 'login', query: {redirect: to.fullPath}})
            return
        }
        if (!authStore.user) {
            const success = await authStore.fetchUser()
            if (!success) {
                authStore.clearAuth()
                next({name: 'login', query: {redirect: to.fullPath}})
                return
            }
        }
    }

    if (isGuestOnly && authStore.isAuthenticated) {
        next({name: 'dashboard'})
        return
    }

    if (to.meta.permission && !authStore.hasPermission(to.meta.permission as string)) {
        next({name: 'dashboard'})
        return
    }

    if (to.meta.role && !authStore.hasRole(to.meta.role as string)) {
        next({name: 'dashboard'})
        return
    }

    next()
})

export default router