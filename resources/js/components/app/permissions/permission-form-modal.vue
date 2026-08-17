<script setup lang="ts">
import { computed, watch } from 'vue'
import { z } from 'zod'
import { Save, X, CircleAlert } from 'lucide-vue-next'
import { useForm } from '@/composables/useForm'
import { permissionService, type Permission } from '@/services/v1/permission-service'

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

const GUARD_OPTIONS = [
  { value: 'web', label: 'Web' },
  { value: 'api', label: 'API' }
]

const permissionSchema = z.object({
  name: z
    .string()
    .trim()
    .min(1, 'Nama permission wajib diisi')
    .max(255, 'Nama terlalu panjang')
    .regex(/^[a-z\s]+$/, 'Nama permission harus huruf kecil dan spasi (contoh: view users)'),
  guard_name: z.enum(['web', 'api'], {
    errorMap: () => ({ message: 'Pilih guard yang valid' })
  })
})

type PermissionForm = z.infer<typeof permissionSchema>

interface Props {
  open: boolean
  permission?: Permission | null
}

const props = defineProps<Props>()
const emit = defineEmits(['update:open', 'saved'])

const { form, errors, loading, submit, reset, validateField } = useForm<PermissionForm>(
  {
    name: '',
    guard_name: 'web'
  },
  {
    schema: permissionSchema,
    autoFocusError: true
  }
)

const getError = (field: string) => {
  const err = (errors.value as any)[field]
  return Array.isArray(err) ? err[0] : err
}

const isEdit = computed(() => !!props.permission)

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      reset()
      if (props.permission) {
        form.name = props.permission.name
        form.guard_name = props.permission.guard_name as 'web' | 'api'
      } else {
        form.name = ''
        form.guard_name = 'web'
      }
    }
  }
)

const handleSubmit = async () => {
  await submit(
    async (values) => {
      const payload = {
        name: values.name,
        guard_name: values.guard_name
      }

      if (isEdit.value) {
        return permissionService.update(props.permission!.id, payload)
      }
      return permissionService.create(payload)
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
          <span class="bg-primary h-2.5 w-2.5 rounded-full"></span>
          <span class="font-bold tracking-tight">
            {{ isEdit ? 'Edit Permission' : 'Tambah Permission Baru' }}
          </span>
        </DialogTitle>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4 py-1" autocomplete="off">
        <div
          v-if="errors._general"
          class="bg-destructive/10 text-destructive animate-in fade-in slide-in-from-top-1 flex items-center gap-2 rounded-lg p-3 text-xs font-medium"
        >
          <CircleAlert class="h-4 w-4 shrink-0" />
          {{ getError('_general') }}
        </div>

        <InputControl
          v-model="form.name"
          label="Nama Permission"
          name="name"
          placeholder="Contoh: view users, create posts"
          hint="Gunakan huruf kecil dan spasi. Contoh: view users"
          :error="getError('name')"
          :disabled="isEdit"
          @blur="validateField('name')"
        />

        <SelectControl
          v-slot
          v-model="form.guard_name"
          name="guard_name"
          label="Guard"
          :options="GUARD_OPTIONS"
          :error="getError('guard_name')"
          placeholder="Pilih guard"
          @update:model-value="validateField('guard_name')"
        />

        <DialogFooter class="mt-6 gap-2 sm:gap-0">
          <AppButton
            variant="outline"
            size="sm"
            type="button"
            :left-icon="X"
            :disabled="loading"
            @click="emit('update:open', false)"
          >
            Batal
          </AppButton>

          <AppButton type="submit" size="sm" :loading="loading" :left-icon="Save">
            {{ isEdit ? 'Simpan Perubahan' : 'Tambahkan Permission' }}
          </AppButton>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
