// resources/js/composables/useForm.ts
import { reactive, ref, computed, nextTick, unref, type MaybeRef } from 'vue'
import type { ZodSchema, ZodError } from 'zod'

/**
 * Tipe data untuk menampung error per field
 */
type FormErrors<T> = Partial<Record<keyof T, string[]>> & {
  _general?: string
}

interface SubmitOptions<T> {
  onSuccess?: (data: any) => void
  onError?: (error: any) => void
  transform?: (values: T) => any
  asFormData?: boolean
}

interface UseFormOptions<T> {
  schema?: MaybeRef<ZodSchema<T>>
  validateOnBlur?: boolean
  autoFocusError?: boolean
}

export function useForm<T extends Record<string, any>>(
  initialValues: T,
  options?: UseFormOptions<T>
) {
  // Mengkloning nilai awal secara murni agar referensi memori aslinya terjaga
  const originalValues = JSON.parse(JSON.stringify(initialValues))

  // Gunakan 'as T' pada reactive untuk menghindari masalah indexing
  const form = reactive({ ...initialValues }) as T

  const errors = ref<FormErrors<T>>({})
  const loading = ref(false)

  // 🔥 FIX TYPE INDEXING ERROR: Memberikan casting penegasan Record murni di dalam proxy reactive
  const touched = reactive({}) as Record<keyof T, boolean>

  const getSchema = () => unref(options?.schema)

  /**
   * Pengecekan apakah form berubah (Dirty Check)
   */
  const isDirty = computed(() =>
    Object.keys(originalValues).some((key) => {
      const k = key as keyof T
      return form[k] !== originalValues[k]
    })
  )

  /**
   * ===============================
   * ERROR HANDLING
   * ===============================
   */
  const setErrors = (serverErrors: Record<string, string[]>) => {
    errors.value = { ...serverErrors } as FormErrors<T>
  }

  const setGeneralError = (message: string) => {
    errors.value = { ...errors.value, _general: message }
  }

  const clearErrors = () => {
    errors.value = {}
  }

  const clearFieldError = (field: keyof T) => {
    const newErrors = { ...errors.value }
    delete newErrors[field]
    errors.value = newErrors
  }

  /**
   * ===============================
   * RESET
   * ===============================
   */
  const reset = () => {
    // Mengembalikan nilai murni dari hasil kloning awal secara aman
    Object.keys(originalValues).forEach((key) => {
      const k = key as keyof T
      form[k] = originalValues[k]
    })

    // Bersihkan juga seluruh riwayat field yang pernah disentuh
    Object.keys(touched).forEach((key) => {
      const k = key as keyof T
      delete touched[k]
    })

    clearErrors()
  }

  /**
   * ===============================
   * VALIDATION (ZOD)
   * ===============================
   */
  const runValidation = async (): Promise<boolean> => {
    const schema = getSchema()
    if (!schema) return true

    try {
      await schema.parseAsync(form)
      clearErrors()
      return true
    } catch (err) {
      const zodError = err as ZodError
      const fieldErrors: any = {}

      zodError.errors.forEach((issue) => {
        const key = issue.path[0] as keyof T
        if (!fieldErrors[key]) fieldErrors[key] = []
        fieldErrors[key].push(issue.message)
      })

      errors.value = fieldErrors

      if (options?.autoFocusError) {
        await nextTick()
        focusFirstError()
      }
      return false
    }
  }

  /**
   * VALIDASI SPESIFIK FIELD TUNGGAL
   * Menggunakan .safeParse untuk mengecek field tunggal tanpa merusak field lain
   */
  const validateField = async (field: keyof T) => {
    const schema = getSchema()
    if (!schema) return

    // Tandai bahwa field ini telah disentuh oleh user secara aman murni
    touched[field] = true

    try {
      const result = await schema.safeParseAsync(form)

      if (result.success) {
        clearFieldError(field)
      } else {
        // Ambil semua pesan error murni yang eksklusif milik field target saja
        const fieldIssues = result.error.errors
          .filter((issue) => issue.path[0] === field)
          .map((issue) => issue.message)

        if (fieldIssues.length > 0) {
          errors.value = {
            ...errors.value,
            [field]: fieldIssues
          }
        } else {
          clearFieldError(field)
        }
      }
    } catch (err) {
      console.error('Gagal mengeksekusi parsial single field validation:', err)
    }
  }

  const focusFirstError = () => {
    const firstField = Object.keys(errors.value)[0]
    if (!firstField || firstField === '_general') return

    const element = document.querySelector(
      `[name="${firstField}"], #${firstField}`
    ) as HTMLElement | null
    element?.focus()
  }

  /**
   * ===============================
   * SUBMIT HANDLER CORE
   * ===============================
   */
  const submit = async (handler: (values: T) => Promise<any>, submitOptions?: SubmitOptions<T>) => {
    try {
      loading.value = true
      clearErrors()

      const isValid = await runValidation()
      if (!isValid) return

      let payload: any = submitOptions?.transform ? submitOptions.transform(form) : form

      if (submitOptions?.asFormData) {
        const fd = new FormData()
        Object.entries(payload).forEach(([k, v]) => {
          if (v instanceof File) fd.append(k, v)
          else if (Array.isArray(v)) {
            v.forEach((item, i) => fd.append(`${k}[${i}]`, item))
          } else if (v !== null && v !== undefined) {
            fd.append(k, v as any)
          }
        })
        payload = fd
      }

      const response = await handler(payload)

      if (response?.status === 'error' || response?.status === 422) {
        if (response.errors) setErrors(response.errors)
        if (response.message) setGeneralError(response.message)
        submitOptions?.onError?.(response)
        return response
      }

      submitOptions?.onSuccess?.(response)
      return response
    } catch (err: any) {
      const response = err?.response?.data
      if (response?.errors) {
        setErrors(response.errors)
      } else if (response?.message) {
        setGeneralError(response.message)
      } else {
        setGeneralError('Terjadi kesalahan koneksi ke server.')
      }
      submitOptions?.onError?.(err)
    } finally {
      loading.value = false
    }
  }

  return {
    form,
    errors,
    loading,
    isDirty,
    touched,
    submit,
    reset,
    clearErrors,
    validateField
  }
}
