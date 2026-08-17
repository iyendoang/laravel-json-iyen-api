// ============================================
// API RESPONSE TYPES
// ============================================

export interface ApiResponse<T = any> {
    status: 'success' | 'error'
    message: string
    data?: T
    errors?: Record<string, string[]>
    meta?: PaginationMeta
    links?: PaginationLinks
}

export interface ApiError {
    status: 'error'
    message: string
    errors?: Record<string, string[]>
}

export interface ApiSuccess<T = any> {
    status: 'success'
    message: string
    data: T
}

export interface PaginationMeta {
    current_page: number
    from: number
    last_page: number
    links: Array<{
        url: string | null
        label: string
        page: number | null
        active: boolean
    }>
    path: string
    per_page: number
    to: number
    total: number
}

export interface PaginationLinks {
    first: string
    last: string
    prev: string | null
    next: string | null
}

export interface PaginatedResponse<T = any> {
    data: T[]
    meta: PaginationMeta
    links: PaginationLinks
}

// Paginated response untuk DataTable
export interface PaginatedApiResponse<T = any> {
    status: 'success' | 'error'
    message: string
    data: T[]
    meta: PaginationMeta | null
    links?: PaginationLinks | null
    errors?: Record<string, string[]>
}

// ============================================
// QUERY PARAMS TYPES
// ============================================

export interface PaginationParams {
    page?: number
    per_page?: number
}

export interface SearchParams extends PaginationParams {
    search?: string
    sort_by?: string
    sort_order?: 'asc' | 'desc'
}

export interface UserQueryParams extends SearchParams {
    role?: string
    status?: 'active' | 'inactive'
}