<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { z } from 'zod'
import { Save, X, CircleAlert } from 'lucide-vue-next'
import { useForm } from '@/composables/useForm'
import { userService } from '@/services/v1/user-service'
import { roleService } from '@/services/v1/role-service'
import type { User } from '@/types/user'

// UI Components
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle
} from '@/components/ui/dialog'
import InputControl from '@/components/shared/input/input-control.vue'
import SelectControl from '@/components/shared/input/select-control.vue'
import AppButton from '@/components/shared/app-button.vue'

/* =========================================================
   1. VALIDATION SCHEMAS & OPTIONS
========================================================= */
const roleOptions = ref<{ value: string; label: string }[]>([])
const loadingRoles = ref(false)

const fetchRoleOptions = async () => {
  try {
    loadingRoles.value = true
    const roles = await roleService.getAllDropdown()
    roleOptions.value = roles.map((r) => ({
      value: r.name,
      label: r.name
    }))
  } catch (error) {
    console.error('Gagal mengambil daftar role:', error)
  } finally {
    loadingRoles.value = false
  }
}

const baseSchema = {
  name: z.string().trim().min(1, 'Nama wajib diisi').max(255, 'Nama terlalu panjang'),
  email: z.string().trim().min(1, 'Email wajib diisi').email('Format email tidak valid'),
  role: z.string().min(1, 'Pilih role akses')
}

const createUserSchema = z.object({
  ...baseSchema,
  password: z.string().min(8, 'Password minimal 8 karakter')
})

const updateUserSchema = z.object({
  ...baseSchema,
  password: z
    .string()
    .optional()
    .or(z.literal(''))
    .transform((val) => (val === '' ? undefined : val))
    .refine((val) => !val || val.length >= 8, 'Password minimal 8 karakter jika diubah')
})

type CreateUserForm = z.infer<typeof createUserSchema>
type UpdateUserForm = z.infer<typeof updateUserSchema>

/* =========================================================
   2. PROPS & EMITS
========================================================= */
interface Props {
  open: boolean
  user?: User | null
}

const props = defineProps<Props>()
const emit = defineEmits(['update:open', 'saved'])

/* =========================================================
   3. FORM INITIALIZATION
========================================================= */
const currentSchema = computed(() => (props.user ? updateUserSchema : createUserSchema))

const { form, errors, loading, submit, reset, validateField } = useForm<
  CreateUserForm | UpdateUserForm
>(
  {
    name: '',
    email: '',
    password: '',
    role: ''
  },
  {
    schema: currentSchema,
    autoFocusError: true
  }
)

/* =========================================================
   4. HELPERS
========================================================= */
const getError = (field: string) => {
  const err = (errors.value as any)[field]
  return Array.isArray(err) ? err[0] : err
}

/* =========================================================
   5. WATCHER (Sinkronisasi Modal)
========================================================= */
watch(
  () => props.open,
  async (isOpen) => {
    if (isOpen) {
      await fetchRoleOptions()
      reset()
      if (props.user) {
        form.name = props.user.name
        form.email = props.user.email
        // Mengambil role name dari Spatie roles array atau fallback ke role property
        const activeRole = (props.user as any).roles?.[0]?.name || (props.user as any).role || ''
        form.role = activeRole
        form.password = ''
      } else {
        form.name = ''
        form.email = ''
        form.role = roleOptions.value[0]?.value || ''
        form.password = ''
      }
    }
  }
)

/* =========================================================
   6. SUBMIT HANDLER
========================================================= */
const handleSubmit = async () => {
  await submit(
    async (values) => {
      // Disesuaikan dengan controller: Backend mengharapkan 'roles' berupa Array of String
      const payload: any = {
        name: values.name,
        email: values.email,
        roles: [values.role]
      }

      if (values.password) {
        payload.password = values.password
      }

      if (props.user) {
        return userService.update(props.user.id, payload)
      }
      return userService.create(payload)
    },
    {
      onSuccess: () => {
        emit('saved')
        emit('update:open', false)
      }
    }
  )
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
          <span class="font-bold tracking-tight">
            {{ props.user ? 'Perbarui Akses Pengguna' : 'Registrasi User Baru' }}
          </span>
        </DialogTitle>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-5 py-2" autocomplete="off">
        <div
          v-if="errors._general"
          class="bg-destructive/10 text-destructive animate-in fade-in slide-in-from-top-1 flex items-center gap-2 rounded-lg p-3 text-xs font-medium"
        >
          <CircleAlert class="h-4 w-4" />
          {{ getError('_general') }}
        </div>

        <InputControl
          v-model="form.name"
          label="Nama Lengkap"
          name="name"
          placeholder="Masukkan nama sesuai identitas"
          :error="getError('name')"
          @update:model-value="validateField('name')"
          @blur="validateField('name')"
        />

        <InputControl
          v-model="form.email"
          label="Alamat Email"
          name="email"
          type="email"
          placeholder="contoh@sidoel.com"
          :error="getError('email')"
          @update:model-value="validateField('email')"
          @blur="validateField('email')"
        />

        <InputControl
          v-model="form.password"
          label="Kata Sandi"
          name="password"
          type="password"
          :placeholder="props.user ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
          :error="getError('password')"
          @update:model-value="validateField('password')"
          @blur="validateField('password')"
        />

        <SelectControl
          v-model="form.role"
          name="role"
          label="Level Otoritas (Role)"
          :options="roleOptions"
          :disabled="loadingRoles"
          :error="getError('role')"
          placeholder="Pilih tingkatan akses"
          @update:model-value="validateField('role')"
        />

        <DialogFooter class="mt-6 gap-2">
          <AppButton
            variant="outline"
            size="sm"
            type="button"
            :left-icon="X"
            @click="emit('update:open', false)"
          >
            Batal
          </AppButton>

          <AppButton type="submit" size="sm" :loading="loading" :left-icon="Save">
            {{ props.user ? 'Simpan Perubahan' : 'Daftarkan User' }}
          </AppButton>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
