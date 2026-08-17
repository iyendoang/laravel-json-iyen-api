import type {Component} from 'vue'

// ============================================
// MENU TYPES
// ============================================

interface BaseMenuItem {
    title: string
    icon?: Component
    roles?: string[]
    permissions?: string[]
    activeRules?: string[]
    badge?: string | number
    badgeColor?: string
    disabled?: boolean
    visible?: boolean
    tooltip?: string
}

export interface MenuLeaf extends BaseMenuItem {
    type: 'item'
    routeName: string
    external?: boolean
    target?: '_blank' | '_self'
    exact?: boolean
}

export interface MenuParent extends BaseMenuItem {
    type: 'group'
    children: MenuLeaf[]
    defaultOpen?: boolean
    collapsible?: boolean
}

export interface MenuDivider {
    type: 'divider'
    title?: string
}

export type MenuItem = MenuLeaf | MenuParent | MenuDivider

export interface MenuGroup {
    heading: string
    headingIcon?: Component
    items: MenuItem[]
    collapsed?: boolean
}

export interface SidebarConfig {
    groups: MenuGroup[]
    collapsed?: boolean
    width?: number
    collapsedWidth?: number
    showUserInfo?: boolean
    showSearch?: boolean
    accordion?: boolean
    expandOnHover?: boolean
}

export interface MenuState {
    expandedGroups: Record<string, boolean>
    activeItem: string | null
    searchQuery: string
    collapsed: boolean
}

export interface FilteredMenu {
    groups: MenuGroup[]
    hasAccessibleItems: boolean
    totalItems: number
    totalGroups: number
}