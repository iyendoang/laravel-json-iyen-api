// ============================================
// ROLE TYPES
// ============================================

export interface Role {
  id: string
  name: string
  guard_name: string
  permissions: string[]
  created_at: string
  updated_at: string
}

export interface CreateRoleData {
  name: string
  permissions?: string[]
}

export interface UpdateRoleData {
  name?: string
  permissions?: string[]
}
