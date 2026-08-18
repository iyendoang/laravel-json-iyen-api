import { reactive, ref, computed, nextTick, unref, type MaybeRef } from 'vue'
import type { ZodType, ZodError } from 'zod'
import { toast } from 'vue-sonner'

type FormErrors<T> = Partial<Record<keyof T, string[]>> & {
    _general?: string
}

interface SubmitOptions<T> {
    onSuccess?: (data: any) => void
    onError?: (error: any) => void
    transform?: (values: T) => any
    asFormData?: boolean
    showSuccessToast?: boolean
    successMessage?: string
    showErrorToast?: boolean
}

interface UseFormOptions<T> {
    schema?: MaybeRef<ZodType<T> | any>
    validateOnBlur?: boolean
    autoFocusError?: boolean
}

export function useForm<T extends Record<string, any>>(
    initialValues: T,
    options?: UseFormOptions<T>
) {
    const originalValues = JSON.parse(JSON.stringify(initialValues))
    const form = reactive({ ...initialValues }) as T
    const errors = ref<FormErrors<T>>({})
    const loading = ref(false)
    const touched = reactive({}) as Record<keyof T, boolean>

    const getSchema = () => unref(options?.schema)

    const isDirty = computed(() =>
        Object.keys(originalValues).some((key) => {
            const k = key as keyof T
            return form[k] !== originalValues[k]
        })
    )

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

    const reset = () => {
        Object.keys(originalValues).forEach((key) => {
            const k = key as keyof T
            form[k] = originalValues[k]
        })
        Object.keys(touched).forEach((key) => {
            const k = key as keyof T
            delete touched[k]
        })
        clearErrors()
    }

    const runValidation = async (): Promise<boolean> => {
        const schema = getSchema()
        if (!schema) return true

        try {
            if (typeof schema.parseAsync === 'function') {
                await schema.parseAsync(form)
            } else if (typeof schema.parse === 'function') {
                await schema.parse(form)
            }
            clearErrors()
            return true
        } catch (err) {
            const zodError = err as ZodError
            const fieldErrors: Record<string, string[]> = {}

            zodError.errors?.forEach((issue: any) => {
                const key = issue.path?.[0] as keyof T
                if (!fieldErrors[key as string]) fieldErrors[key as string] = []
                fieldErrors[key as string].push(issue.message)
            })

            errors.value = fieldErrors as FormErrors<T>

            if (options?.autoFocusError) {
                await nextTick()
                focusFirstError()
            }
            return false
        }
    }

    const validateField = async (field: keyof T) => {
        const schema = getSchema()
        if (!schema) return

        touched[field] = true

        try {
            const result = await schema.safeParseAsync?.(form) || await schema.safeParse?.(form)

            if (result?.success) {
                clearFieldError(field)
            } else if (result?.error) {
                const fieldIssues = result.error.errors
                    .filter((issue: any) => issue.path?.[0] === field)
                    .map((issue: any) => issue.message)

                if (fieldIssues.length > 0) {
                    errors.value = { ...errors.value, [field]: fieldIssues }
                } else {
                    clearFieldError(field)
                }
            }
        } catch (err) {
            console.error('Gagal validasi field:', err)
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

    // 🔥 Helper untuk extract first error message
    const getFirstErrorMessage = (serverErrors: Record<string, string[]>): string | undefined => {
        const keys = Object.keys(serverErrors)
        if (keys.length === 0) return undefined

        const firstKey = keys[0] as string
        const messages = serverErrors[firstKey]
        if (Array.isArray(messages) && messages.length > 0) {
            return messages[0]
        }
        return undefined
    }

    const submit = async (
        handler: (values: T) => Promise<any> | any,
        submitOptions?: SubmitOptions<T>
    ): Promise<boolean> => {
        try {
            loading.value = true
            clearErrors()

            console.log('🔵 [useForm] submit dipanggil')

            const isValid = await runValidation()
            console.log('   Validasi:', isValid)
            if (!isValid) return false

            let payload: any = submitOptions?.transform ? submitOptions.transform({ ...form }) : { ...form }

            console.log('   Payload awal:', { ...payload })

            if (submitOptions?.asFormData) {
                const fd = new FormData()
                Object.entries(payload).forEach(([k, v]) => {
                    if (v instanceof File) fd.append(k, v)
                    else if (Array.isArray(v)) {
                        v.forEach((item, i) => fd.append(`${k}[${i}]`, item))
                    } else if (v !== null && v !== undefined && v !== '') {
                        fd.append(k, v as any)
                    }
                })
                payload = fd
                console.log('   Payload FormData (asFormData)')
            }

            console.log('   Memanggil handler dengan payload...')
            const handlerResult = await handler(payload)
            console.log('   Handler selesai, hasil:', handlerResult)

            // ✅ Sukses
            if (submitOptions?.showSuccessToast !== false) {
                toast.success(submitOptions?.successMessage || 'Berhasil disimpan')
            }
            submitOptions?.onSuccess?.(handlerResult ?? true)
            return true
        } catch (err: any) {
            console.log('❌ [useForm] submit error:', err)
            const response = err?.response?.data

            if (response?.errors) {
                const serverErrors = response.errors as Record<string, string[]>
                setErrors(serverErrors)

                if (submitOptions?.showErrorToast !== false) {
                    const firstError = getFirstErrorMessage(serverErrors)
                    toast.error(firstError || 'Validasi gagal')
                }
            } else if (response?.message) {
                setGeneralError(response.message)
                if (submitOptions?.showErrorToast !== false) {
                    toast.error(response.message)
                }
            } else {
                setGeneralError('Terjadi kesalahan koneksi ke server.')
                if (submitOptions?.showErrorToast !== false) {
                    toast.error('Terjadi kesalahan koneksi')
                }
            }

            submitOptions?.onError?.(err)
            return false
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
        validateField,
        setErrors,
        setGeneralError,
    }
}