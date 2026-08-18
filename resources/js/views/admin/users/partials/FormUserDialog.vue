<script setup lang="ts">
import { watch } from 'vue'
import * as z from 'zod'
import { Loader2, User, Mail, Lock } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import InputControl from '@/components/shared/input/input-control.vue'
import SelectControl from '@/components/shared/input/select-control.vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { useForm } from '@/composables/useForm'
import { userService } from '@/services/admin/user.service'
import type { User as UserType, OptionItem } from '@/types'

const props = defineProps<{
  open: boolean
  user: UserType | null
  roles: OptionItem[]
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'saved'): void
}>()

// Zod Schema
const schema = z.object({
  name: z.string().min(1, 'Nama wajib diisi').max(255, 'Nama maksimal 255 karakter'),
  email: z.string().min(1, 'Email wajib diisi').email('Format email tidak valid'),
  password: z.string().min(8, 'Password minimal 8 karakter').optional().or(z.literal('')),
  role: z.string().optional(),
})

const {
  form,
  errors,
  loading: formLoading,
  submit,
  reset,
  validateField,
} = useForm(
  { name: '', email: '', password: '', role: '' },
  { schema, autoFocusError: true }
)

// Reset form saat dialog dibuka
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    reset()
    form.name = props.user?.name || ''
    form.email = props.user?.email || ''
    form.password = ''
    form.role = props.user?.role || ''
  }
})

// Submit handler
const onSubmit = () => {
  submit(
    async (values) => {
      const payload: any = {
        name: values.name,
        email: values.email,
      }

      if (values.password) {
        payload.password = values.password
      }

      if (values.role) {
        payload.role = values.role
      }

      if (props.user) {
        await userService.updateUser(props.user.id, payload)
      } else {
        await userService.createUser(payload)
      }
    },
    {
      showSuccessToast: true,
      successMessage: 'User berhasil disimpan',
      showErrorToast: true,
      onSuccess: () => {
        emit('update:open', false)
        emit('saved')
      },
    }
  )
}
</script>

<template>
  <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <User class="text-primary h-4 w-4" />
          {{ user ? 'Edit User' : 'Tambah User' }}
        </DialogTitle>
        <DialogDescription>
          {{ user ? 'Perbarui data user.' : 'Tambahkan user baru untuk sistem.' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="onSubmit" class="space-y-4">
        <InputControl
          v-model="form.name"
          id="name"
          label="Nama Lengkap"
          type="text"
          placeholder="John Doe"
          :error="errors.name?.[0]"
          :disabled="props.loading || formLoading"
          @blur="() => validateField('name')"
        >
          <template #prefix>
            <User class="text-muted-foreground/50 h-3.5 w-3.5" />
          </template>
        </InputControl>

        <InputControl
          v-model="form.email"
          id="email"
          label="Email"
          type="email"
          placeholder="john@example.com"
          :error="errors.email?.[0]"
          :disabled="props.loading || formLoading"
          @blur="() => validateField('email')"
        >
          <template #prefix>
            <Mail class="text-muted-foreground/50 h-3.5 w-3.5" />
          </template>
        </InputControl>

        <InputControl
          v-model="form.password"
          id="password"
          :label="user ? 'Password Baru (opsional)' : 'Password'"
          type="password"
          placeholder="••••••••"
          :error="errors.password?.[0]"
          :disabled="props.loading || formLoading"
          @blur="() => validateField('password')"
        >
          <template #prefix>
            <Lock class="text-muted-foreground/50 h-3.5 w-3.5" />
          </template>
        </InputControl>

        <SelectControl
          v-model="form.role"
          name="role"
          label="Role"
          :options="roles"
          placeholder="Pilih role"
          :error="errors.role?.[0]"
        />

        <DialogFooter class="gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            :disabled="props.loading || formLoading"
            @click="emit('update:open', false)"
          >
            Batal
          </Button>
          <Button type="submit" size="sm" :disabled="props.loading || formLoading">
            <Loader2 v-if="props.loading || formLoading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
            {{ props.loading || formLoading ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>