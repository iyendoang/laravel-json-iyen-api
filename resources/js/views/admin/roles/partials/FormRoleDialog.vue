<script setup lang="ts">
import {watch, computed, ref} from 'vue'
import * as z from 'zod'
import { Loader2, Shield, CheckSquare, Square, Search } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import InputControl from '@/components/shared/input/input-control.vue'
import CheckboxControl from '@/components/shared/input/checkbox-control.vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { useForm } from '@/composables/useForm'
import { roleService } from '@/services/admin/role.service'
import type { Role, OptionItem } from '@/types'

const props = defineProps<{
  open: boolean
  role: Role | null
  permissions: OptionItem[]
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
    .min(1, 'Nama role wajib diisi')
    .max(255, 'Nama role maksimal 255 karakter')
    .regex(/^[a-z0-9-]+$/, 'Hanya boleh huruf kecil, angka, dan tanda hubung (-)'),
  permissions: z.array(z.string()).default([]),
})

const {
  form,
  errors,
  loading: formLoading,
  submit,
  reset,
  validateField,
} = useForm(
  { name: '', permissions: [] as string[] },
  { schema, autoFocusError: true }
)

// 🔥 Search permission
const permissionSearch = ref('')
const filteredPermissions = computed(() => {
  const query = permissionSearch.value.toLowerCase().trim()
  if (!query) return props.permissions

  return props.permissions.filter((p) =>
    p.label.toLowerCase().includes(query) || p.value.toLowerCase().includes(query)
  )
})

// 🔥 Select All state
const isAllSelected = computed(() => {
  if (props.permissions.length === 0) return false
  return props.permissions.every((p) => form.permissions.includes(p.value))
})

const isPartialSelected = computed(() => {
  const selectedCount = props.permissions.filter((p) => form.permissions.includes(p.value)).length
  return selectedCount > 0 && selectedCount < props.permissions.length
})

const selectedCount = computed(() => {
  return form.permissions.length
})

// 🔥 Toggle All
const toggleAll = (checked: boolean) => {
  if (checked) {
    form.permissions = props.permissions.map((p) => p.value)
  } else {
    form.permissions = []
  }
}

// Reset form saat dialog dibuka
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    reset()
    form.name = props.role?.name || ''
    form.permissions = props.role?.permissions || []
    permissionSearch.value = ''
  }
})

// Toggle permission
const togglePermission = (value: string, checked: boolean) => {
  if (checked) {
    if (!form.permissions.includes(value)) {
      form.permissions.push(value)
    }
  } else {
    form.permissions = form.permissions.filter((p) => p !== value)
  }
}

// Submit handler
const onSubmit = () => {
  submit(
    async (values) => {
      const payload = {
        name: values.name.trim().toLowerCase(),
        permissions: values.permissions,
      }

      if (props.role) {
        await roleService.updateRole(props.role.id, payload)
      } else {
        await roleService.createRole(payload)
      }
    },
    {
      showSuccessToast: true,
      successMessage: 'Role berhasil disimpan',
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
    <DialogContent class="sm:max-w-lg">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Shield class="text-primary h-4 w-4" />
          {{ role ? 'Edit Role' : 'Tambah Role' }}
        </DialogTitle>
        <DialogDescription>
          {{ role ? 'Perbarui role dan permission.' : 'Tambahkan role baru untuk sistem.' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="onSubmit" class="space-y-4">
        <!-- Nama Role -->
        <InputControl
          v-model="form.name"
          id="name"
          label="Nama Role"
          type="text"
          placeholder="contoh: manager"
          :error="errors.name?.[0]"
          hint="Gunakan huruf kecil dan tanda hubung (-)"
          :disabled="props.loading || formLoading"
          @blur="() => validateField('name')"
        >
          <template #prefix>
            <Shield class="text-muted-foreground/50 h-3.5 w-3.5" />
          </template>
        </InputControl>

        <!-- Permissions -->
        <div>
          <!-- Header: Label + Select All -->
          <div class="flex items-center justify-between">
            <label class="text-sm leading-none font-medium">
              Permissions
              <span class="text-muted-foreground ml-1 text-xs">
                                ({{ selectedCount }} terpilih)
                            </span>
            </label>

            <button
              type="button"
              class="hover:bg-muted/50 flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium transition-colors"
              @click="toggleAll(!isAllSelected)"
            >
              <component
                :is="isAllSelected ? CheckSquare : isPartialSelected ? CheckSquare : Square"
                :class="[
                                    'h-4 w-4',
                                    isAllSelected || isPartialSelected ? 'text-primary' : 'text-muted-foreground/50',
                                ]"
              />
              {{ isAllSelected ? 'Batal Semua' : 'Pilih Semua' }}
            </button>
          </div>

          <!-- Search -->
          <div class="relative mt-2">
            <Search class="text-muted-foreground/50 pointer-events-none absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2" />
            <input
              v-model="permissionSearch"
              type="text"
              placeholder="Cari permission..."
              class="border-border/50 bg-muted/20 focus:border-primary/40 focus:bg-background h-8 w-full rounded-md border pr-3 pl-8 text-xs transition-all focus:outline-none focus:ring-1 focus:ring-ring"
            />
          </div>

          <!-- Permission List -->
          <div class="bg-muted/20 mt-2 max-h-48 space-y-0.5 overflow-y-auto rounded-md border p-1.5">
            <div
              v-for="permission in filteredPermissions"
              :key="permission.value"
              :class="[
                                'rounded-md px-2 py-1 transition-colors',
                                form.permissions.includes(permission.value)
                                    ? 'bg-primary/5 hover:bg-primary/10'
                                    : 'hover:bg-muted/40',
                            ]"
            >
              <CheckboxControl
                :model-value="form.permissions.includes(permission.value)"
                :label="permission.label"
                container-class="p-0"
                @update:model-value="(v: boolean) => togglePermission(permission.value, v)"
              />
            </div>

            <!-- Empty search -->
            <div v-if="filteredPermissions.length === 0" class="py-6 text-center">
              <p class="text-muted-foreground text-xs">Permission tidak ditemukan</p>
            </div>
          </div>
        </div>

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