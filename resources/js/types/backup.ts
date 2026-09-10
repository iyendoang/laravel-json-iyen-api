export interface BackupItem {
    id: string
    filename: string
    size_raw: number
    size_human: string
    created_at: string
    date_indo: string
    age: string
    path: string
}

export interface RestoreHistoryItem {
    id: string
    actor_name: string
    filename: string
    ip_address: string | null
    date_indo: string
    time_ago: string
    status: 'success' | 'failed' | string
}

export interface CreateBackupResult {
    path: string
}

export interface SignedUrlResult {
    url: string
}