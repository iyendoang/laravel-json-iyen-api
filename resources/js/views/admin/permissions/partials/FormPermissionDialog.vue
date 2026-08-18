<script setup lang="ts">
import {watch} from 'vue'
import * as z from 'zod'
import {Loader2, KeyRound} from 'lucide-vue-next'
import {Button} from '@/components/ui/button'
import InputControl from '@/components/shared/input/input-control.vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {useForm} from '@/composables/useForm'
import {permissionService} from '@/services/admin/permission.service'
import type {Permission} from '@/types'

const props = defineProps<{
  open: boolean
  permission: Permission | null
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'saved'): void
}>()

// Zod Schema
const schema = z.object({
  name: z
    .string()
    .min(1, 'Nama permission wajib diisi')
    .max(255, 'Nama permission maksimal 255 karakter')
    .regex(/^[a-z0-9-]+$/, 'Hanya boleh huruf kecil, angka, dan tanda hubung (-)'),
})

const {
  form,
  errors,
  loading: formLoading,
  submit,
  reset,
  validateField,
} = useForm({name: ''}, {schema, autoFocusError: true})

// Reset form saat dialog dibuka
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    reset()
    form.name = props.permission?.name || ''
  }
})

// Submit handler
const onSubmit = () => {
  submit(
    async (values) => {
      const payload = {
        name: values.name.trim().toLowerCase(),
      }

      if (props.permission) {
        await permissionService.updatePermission(props.permission.id, payload)
      } else {
        await permissionService.createPermission(payload)
      }
    },
    {
      showSuccessToast: true,
      successMessage: 'Permission berhasil disimpan',
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
          <KeyRound class="text-primary h-4 w-4"/>
          {{ permission ? 'Edit Permission' : 'Tambah Permission' }}
        </DialogTitle>
        <DialogDescription>
          {{ permission ? 'Perbarui nama permission.' : 'Tambahkan permission baru untuk sistem.' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="onSubmit" class="space-y-4">
        <InputControl
          v-model="form.name"
          id="name"
          label="Nama Permission"
          type="text"
          placeholder="contoh: view-reports"
          :error="errors.name?.[0] || errors._general"
          hint="Gunakan huruf kecil dan tanda hubung (-)"
          :disabled="props.loading || formLoading"
          @blur="() => validateField('name')"
        >
          <template #prefix>
            <KeyRound class="text-muted-foreground/50 h-3.5 w-3.5"/>
          </template>
        </InputControl>

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
            <Loader2 v-if="props.loading || formLoading" class="mr-1.5 h-3.5 w-3.5 animate-spin"/>
            {{ props.loading || formLoading ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>