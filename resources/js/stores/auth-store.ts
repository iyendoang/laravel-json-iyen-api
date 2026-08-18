import {defineStore} from 'pinia'
import {ref, computed} from 'vue'
import {authService} from '@/services/auth.service'
import type {User, LoginCredentials, RegisterData, AuthResponse} from '@/types'

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    const token = ref<string | null>(localStorage.getItem('token'))
    const permissions = ref<string[]>([])
    const roles = ref<string[]>([])
    const role = ref<string | null>(null)
    const loading = ref(false)
    const error = ref<string | null>(null)

    const isAuthenticated = computed(() => !!token.value)
    const isAdmin = computed(() => role.value === 'admin' || role.value === 'super-admin')
    const isSuperAdmin = computed(() => role.value === 'super-admin')

    function setAuth(data: AuthResponse) {
        token.value = data.access_token
        user.value = data.user
        role.value = data.user.role
        roles.value = data.user.role ? [data.user.role] : []
        permissions.value = data.user.permissions || []
        localStorage.setItem('token', data.access_token)
        error.value = null
    }

    function clearAuth() {
        user.value = null
        token.value = null
        role.value = null
        roles.value = []
        permissions.value = []
        error.value = null
        localStorage.removeItem('token')
    }

    async function login(credentials: LoginCredentials): Promise<boolean> {
        loading.value = true
        error.value = null
        try {
            const response = await authService.login(credentials)
            setAuth(response)
            return true
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Login gagal'
            return false
        } finally {
            loading.value = false
        }
    }

    async function register(userData: RegisterData): Promise<boolean> {
        loading.value = true
        error.value = null
        try {
            const response = await authService.register(userData)
            setAuth(response)
            return true
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Registrasi gagal'
            return false
        } finally {
            loading.value = false
        }
    }

    async function logout(): Promise<void> {
        try {
            await authService.logout()
        } catch (err) {
            console.warn('Logout error:', err)
        } finally {
            clearAuth()
            window.location.href = '/'
        }
    }

    async function fetchUser(): Promise<boolean> {
        try {
            const userData = await authService.me()
            user.value = userData
            role.value = userData.role
            roles.value = userData.role ? [userData.role] : []
            permissions.value = userData.permissions || []
            return true
        } catch (err) {
            console.warn('Fetch user error:', err)
            return false
        }
    }

    function hasPermission(permission: string): boolean {
        if (permissions.value.includes('*')) return true
        return permissions.value.includes(permission)
    }

    function hasAnyPermission(permissionsList: string[]): boolean {
        if (permissions.value.includes('*')) return true
        return permissionsList.some(p => permissions.value.includes(p))
    }

    function hasAllPermissions(permissionsList: string[]): boolean {
        if (permissions.value.includes('*')) return true
        return permissionsList.every(p => permissions.value.includes(p))
    }

    function hasRole(roleName: string): boolean {
        return role.value === roleName || roles.value.includes(roleName)
    }

    function hasAnyRole(rolesList: string[]): boolean {
        return rolesList.some(r => role.value === r || roles.value.includes(r))
    }

    return {
        user,
        token,
        permissions,
        roles,
        role,
        loading,
        error,
        isAuthenticated,
        isAdmin,
        isSuperAdmin,
        setAuth,
        clearAuth,
        login,
        register,
        logout,
        fetchUser,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasRole,
        hasAnyRole,
    }
})