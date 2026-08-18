import {computed, ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import {appMenuGroups} from '@/config/menu'
import type {
    MenuGroup,
    MenuItem,
    MenuLeaf,
    MenuParent,
    MenuDivider,
    FilteredMenu,
    MenuState,
} from '@/types/menu'
import {useAuthStore} from '@/stores/auth-store'

export function useMenu() {
    const auth = useAuthStore()
    const route = useRoute()

    const menuState = ref<MenuState>({
        expandedGroups: {},
        activeItem: null,
        searchQuery: '',
        collapsed: false,
    })

    // Type guards
    const isMenuLeaf = (item: MenuItem): item is MenuLeaf => item.type === 'item'
    const isMenuParent = (item: MenuItem): item is MenuParent => item.type === 'group'
    const isMenuDivider = (item: MenuItem): item is MenuDivider => item.type === 'divider'

    // 🔥 HANYA SATU isLeafActive
    const isLeafActive = (item: MenuLeaf): boolean => {
        const currentRouteName = String(route.name || '')
        const currentRoutePath = route.path || ''

        // Cek activeRules
        if (item.activeRules && item.activeRules.length > 0) {
            return item.activeRules.some((rule) => {
                if (currentRouteName === rule) return true
                if (currentRouteName.startsWith(rule + '.')) return true
                return rule.startsWith('/') && currentRoutePath.startsWith(rule)
            })
        }

        // Cek routeName
        if (item.routeName) {
            if (item.exact) {
                // Exact match - hanya aktif jika route name sama persis
                return currentRouteName === item.routeName
            }
            // Non-exact - aktif jika route name sama atau child
            return (
                currentRouteName === item.routeName ||
                currentRouteName.startsWith(item.routeName + '.') ||
                currentRouteName.startsWith(item.routeName + '-')
            )
        }

        return false
    }

    const isParentActive = (item: MenuParent): boolean => {
        // Cek activeRules group
        if (item.activeRules && item.activeRules.length > 0) {
            const currentRouteName = String(route.name || '')
            const hasActiveRule = item.activeRules.some(
                (rule) => currentRouteName === rule || currentRouteName.startsWith(rule + '.')
            )
            if (hasActiveRule) return true
        }

        // Cek children
        return item.children.some((child) => isLeafActive(child))
    }

    const isItemActive = (item: MenuItem): boolean => {
        if (isMenuDivider(item)) return false
        if (isMenuLeaf(item)) return isLeafActive(item)
        if (isMenuParent(item)) return isParentActive(item)
        return false
    }

    const isItemOrChildActive = (item: MenuItem): boolean => {
        if (isItemActive(item)) return true
        if (isMenuParent(item)) {
            return item.children.some((child) => isLeafActive(child))
        }
        return false
    }

    const hasAccess = (item: MenuLeaf | MenuParent): boolean => {
        if (!auth.isAuthenticated) return false
        if (item.visible === false) return false
        if (item.hidden === true) return false
        if (auth.hasRole('super-admin')) return true

        if (item.permissions && item.permissions.length > 0) {
            const hasPermission = item.permissions.some((permission) =>
                auth.hasPermission(permission)
            )
            if (!hasPermission) return false
        }

        if (item.roles && item.roles.length > 0) {
            const hasRole = item.roles.some((role) => auth.hasRole(role))
            if (!hasRole) return false
        }

        return true
    }

    const filteredGroups = computed<MenuGroup[]>(() => {
        const query = menuState.value.searchQuery.toLowerCase().trim()

        return appMenuGroups
            .map((group) => {
                const visibleItems = group.items
                    .map((item) => {
                        // 🔥 Skip hidden items
                        if (isMenuLeaf(item) && item.hidden) return null
                        if (isMenuParent(item) && item.hidden) return null

                        if (isMenuDivider(item)) return item

                        if (isMenuParent(item)) {
                            if (!hasAccess(item)) return null
                            const visibleChildren = item.children.filter((child) => !child.hidden && hasAccess(child))
                            if (!visibleChildren.length) return null

                            if (query) {
                                const matchingChildren = visibleChildren.filter((child) =>
                                    child.title.toLowerCase().includes(query)
                                )
                                if (!matchingChildren.length) return null
                                return {...item, children: matchingChildren} as MenuParent
                            }

                            return {...item, children: visibleChildren} as MenuParent
                        }

                        if (isMenuLeaf(item)) {
                            if (!hasAccess(item)) return null
                            if (query && !item.title.toLowerCase().includes(query)) return null
                            return item
                        }

                        return null
                    })
                    .filter(Boolean) as MenuItem[]

                return {...group, items: visibleItems}
            })
            .filter((group) => group.items.length > 0)
    })

    const toggleGroup = (title: string) => {
        menuState.value.expandedGroups[title] = !menuState.value.expandedGroups[title]
    }

    const isGroupExpanded = (group: MenuParent): boolean => {
        if (menuState.value.expandedGroups[group.title] !== undefined) {
            return menuState.value.expandedGroups[group.title]
        }
        if (isItemOrChildActive(group)) return true
        return !!group.defaultOpen
    }

    const expandAll = () => {
        filteredGroups.value.forEach((group) => {
            group.items.forEach((item) => {
                if (isMenuParent(item) && item.collapsible !== false) {
                    menuState.value.expandedGroups[item.title] = true
                }
            })
        })
    }

    const collapseAll = () => {
        menuState.value.expandedGroups = {}
    }

    const toggleSidebar = () => {
        menuState.value.collapsed = !menuState.value.collapsed
    }

    const setSearch = (query: string) => {
        menuState.value.searchQuery = query
        if (query) expandAll()
    }

    const accessibleRoutes = computed<string[]>(() => {
        const routes: string[] = []
        filteredGroups.value.forEach((group) => {
            group.items.forEach((item) => {
                if (isMenuLeaf(item) && item.routeName) {
                    routes.push(item.routeName)
                }
                if (isMenuParent(item)) {
                    item.children.forEach((child) => {
                        if (child.routeName) routes.push(child.routeName)
                    })
                }
            })
        })
        return routes
    })

    const filteredMenu = computed<FilteredMenu>(() => {
        let totalItems = 0
        let totalGroups = 0

        filteredGroups.value.forEach((group) => {
            group.items.forEach((item) => {
                if (isMenuLeaf(item)) totalItems++
                if (isMenuParent(item)) {
                    totalGroups++
                    totalItems += item.children.length
                }
            })
        })

        return {
            groups: filteredGroups.value,
            hasAccessibleItems: totalItems > 0,
            totalItems,
            totalGroups,
        }
    })

    const canAccessRoute = (routeName: string): boolean => {
        return accessibleRoutes.value.includes(routeName)
    }

    const can = (permission: string): boolean => auth.hasPermission(permission)
    const hasRole = (role: string): boolean => auth.hasRole(role)

    watch(
        () => route.name,
        () => {
            menuState.value.activeItem = String(route.name || '')

            // Auto expand parent yang aktif
            appMenuGroups.forEach((group) => {
                group.items.forEach((item) => {
                    if (isMenuParent(item) && isItemOrChildActive(item)) {
                        menuState.value.expandedGroups[item.title] = true
                    }
                })
            })
        },
        {immediate: true}
    )

    return {
        menuState,
        filteredGroups,
        filteredMenu,
        accessibleRoutes,
        isItemActive,
        isItemOrChildActive,
        isMenuLeaf,
        isMenuParent,
        isMenuDivider,
        hasAccess,
        canAccessRoute,
        can,
        hasRole,
        toggleGroup,
        isGroupExpanded,
        expandAll,
        collapseAll,
        toggleSidebar,
        setSearch,
        isLeafActive,
    }
}