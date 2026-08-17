export interface Permission {
  id: string
  name: string
  guard_name: string
  created_at: string
  updated_at: string
}

export interface CreatePermissionData {
  name: string
}

export interface UpdatePermissionData {
  name?: string
}
