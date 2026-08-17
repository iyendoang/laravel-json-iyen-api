// @/utils/format-helper.ts
import { format } from 'date-fns'

export const parseToNumber = (value: string | number): number => {
  if (typeof value === 'number') return value
  // Menghapus titik (separator ribuan) dan mengubah ke integer
  return parseInt(value.replace(/\./g, '')) || 0
}

export const toSqlFormat = (date: Date | string | null): string | null => {
  if (!date) return null
  const d = typeof date === 'string' ? new Date(date) : date
  return format(d, 'yyyy-MM-dd')
}

/**
 * Mengubah string menjadi format slug (lowercase, dash-only)
 */
export const slugify = (text: string): string => {
  return text
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
    .replace(/-+/g, '-')
    .replace(/^-+|-+$/g, '')
}

/**
 * Mengambil ekstensi file dari mime type (e.g., 'application/pdf' -> 'PDF')
 */
export const formatMimeType = (mime: string): string => {
  if (!mime) return 'FILE'
  const part = mime.split('/')[1]
  return part ? part.toUpperCase() : 'FILE'
}

/**
 * Memformat ukuran file dari bytes ke unit yang mudah dibaca (KB/MB)
 */
/**
 * Memformat ukuran file dari bytes ke unit yang mudah dibaca (KB/MB)
 * Ditambahkan pengecekan tipe data untuk mencegah NaN
 */
export const formatFileSize = (bytes: number | string | null | undefined): string => {
  // Ubah ke number dan pastikan valid
  const numBytes = typeof bytes === 'string' ? parseInt(bytes) : bytes

  if (numBytes === null || numBytes === undefined || isNaN(numBytes as number) || numBytes < 0) {
    return '0 KB'
  }

  if (numBytes === 0) return '0 Bytes'

  const k = 1024
  const dm = 1
  const sizes = ['Bytes', 'KB', 'MB', 'GB']

  // Hitung index untuk menentukan unit (KB, MB, dsb)
  const i = Math.floor(Math.log(numBytes) / Math.log(k))

  return parseFloat((numBytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}
