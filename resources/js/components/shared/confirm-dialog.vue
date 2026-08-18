<script setup lang="ts">
import {computed} from 'vue'
import {Loader2, Trash, AlertTriangle, Info} from 'lucide-vue-next'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'

type DialogVariant = 'danger' | 'warning' | 'info'

const props = withDefaults(defineProps<{
  open: boolean
  title?: string
  description?: string
  confirmText?: string
  cancelText?: string
  loading?: boolean
  variant?: DialogVariant
  showIcon?: boolean
}>(), {
  title: 'Konfirmasi',
  description: 'Apakah Anda yakin?',
  confirmText: 'Ya',
  cancelText: 'Batal',
  loading: false,
  variant: 'danger',
  showIcon: true,
})

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'confirm'): void
  (e: 'cancel'): void
}>()

const variantConfig = computed(() => {
  const configs = {
    danger: {
      icon: Trash,
      iconWrapper: 'text-destructive bg-destructive/10',
      button: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
    },
    warning: {
      icon: AlertTriangle,
      iconWrapper: 'text-amber-600 bg-amber-500/10',
      button: 'bg-amber-600 text-white hover:bg-amber-700',
    },
    info: {
      icon: Info,
      iconWrapper: 'text-primary bg-primary/10',
      button: 'bg-primary text-primary-foreground hover:bg-primary/90',
    },
  }
  return configs[props.variant] || configs.danger
})

const handleConfirm = () => emit('confirm')
const handleCancel = () => {
  emit('update:open', false)
  emit('cancel')
}
</script>

<template>
  <AlertDialog
    :open="open"
    @update:open="(val) => {
            emit('update:open', val)
            if (!val) emit('cancel')
        }"
  >
    <AlertDialogContent class="sm:max-w-sm p-5">
      <!-- Header Compact -->
      <div class="flex items-start gap-3">
        <!-- Icon -->
        <div
          v-if="showIcon"
          :class="[
                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-full',
                        variantConfig.iconWrapper,
                    ]"
        >
          <component :is="variantConfig.icon" class="h-4 w-4"/>
        </div>

        <div class="flex-1 space-y-1">
          <AlertDialogTitle class="text-sm font-semibold">
            {{ title }}
          </AlertDialogTitle>
          <AlertDialogDescription class="text-xs leading-relaxed">
            {{ description }}
          </AlertDialogDescription>
        </div>
      </div>

      <!-- Konten Tambahan -->
      <div v-if="$slots.default" class="mt-3">
        <slot/>
      </div>

      <!-- Footer Compact -->
      <div class="mt-4 flex justify-end gap-2">
        <button
          :disabled="loading"
          class="border-input hover:bg-muted inline-flex h-8 items-center rounded-md border px-3 text-xs font-medium transition-colors disabled:opacity-50"
          @click="handleCancel"
        >
          {{ cancelText }}
        </button>

        <button
          :class="variantConfig.button"
          :disabled="loading"
          class="inline-flex h-8 items-center rounded-md px-3 text-xs font-medium transition-colors disabled:opacity-50"
          @click="handleConfirm"
        >
          <Loader2 v-if="loading" class="mr-1.5 h-3 w-3 animate-spin"/>
          {{ loading ? 'Memproses...' : confirmText }}
        </button>
      </div>
    </AlertDialogContent>
  </AlertDialog>
</template>