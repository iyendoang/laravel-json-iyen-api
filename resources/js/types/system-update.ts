export interface SystemUpdateHistoryItem {
    version_from: string
    version_to: string
    date: string
    status: 'success' | 'failed' | string
    admin: string
    log: string | null
}

export interface CheckUpdateResponse {
    success: boolean
    current_version: string
    latest_version?: string
    has_update?: boolean
    title?: string
    changelog?: string
    download_url?: string | null
    message?: string
    history: SystemUpdateHistoryItem[]
}

export interface ExecuteUpdateResponse {
    success: boolean
    message: string
}