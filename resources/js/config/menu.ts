import type {Component} from 'vue'
import type {MenuGroup, MenuItem} from '@/types/menu'
import {adminRoutes} from '@/router/admin.routes'

/**
 * Generate menu otomatis dari admin routes
 */
function generateMenuFromRoutes(): MenuGroup[] {
    const groups: Map<string, MenuItem[]> = new Map()

    adminRoutes.forEach((route) => {
        const meta = route.meta
        if (!meta || meta.hidden) return

        const groupName = meta.menuGroup || 'Other'
        const title = meta.title || String(route.name || '')
        const icon = meta.icon as Component | undefined
        const permission = meta.permission
        const role = meta.role
        const menuOrder = meta.menuOrder || 999

        const menuItem: MenuItem = {
            type: 'item',
            title,
            icon,
            routeName: String(route.name),
            permissions: permission ? [permission] : undefined,
            roles: role ? [role] : undefined,
        }

        if (!groups.has(groupName)) {
            groups.set(groupName, [])
        }
        groups.get(groupName)!.push(menuItem)
    })

    // Sort groups dan items
    return Array.from(groups.entries())
        .map(([heading, items]) => ({
            heading,
            items: items.sort((a, b) => {
                const aOrder = (a as any).menuOrder || 999
                const bOrder = (b as any).menuOrder || 999
                return aOrder - bOrder
            }),
        }))
        .sort((a, b) => {
            const orderMap: Record<string, number> = {
                Main: 1,
                Management: 2,
                System: 3,
                Other: 99,
            }
            return (orderMap[a.heading] || 99) - (orderMap[b.heading] || 99)
        })
}

export const appMenuGroups: MenuGroup[] = generateMenuFromRoutes()