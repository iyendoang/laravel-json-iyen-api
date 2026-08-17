// ============================================
// USER TYPES (SATU-SATUNYA DEFINISI USER)
// ============================================

export interface User {
  id: string
  name: string
  email: string
  avatar: string | null
  phone: string | null
  bio: string | null
  address: string | null
  city: string | null
  country: string | null
  postal_code: string | null
  email_verified_at: string | null
  role: string | null
  permissions: string[]
  created_at: string
  updated_at: string
}

export interface CreateUserData {
  name: string
  email: string
  password: string
  role?: string
  permissions?: string[]
}

export interface UpdateUserData {
  name?: string
  email?: string
  password?: string
  role?: string
  permissions?: string[]
}

// ============================================
// PROFILE TYPES
// ============================================

export interface ProfileData {
  name?: string
  email?: string
  phone?: string | null
  bio?: string | null
  address?: string | null
  city?: string | null
  country?: string | null
  postal_code?: string | null
}

export interface UpdatePasswordData {
  current_password: string
  new_password: string
  new_password_confirmation: string
}
