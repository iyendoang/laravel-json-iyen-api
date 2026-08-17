<!--resources/js/components/shared/confirm-dialog.vue-->
<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"

interface Props {
  open: boolean
  title?: string
  description?: string
  confirmText?: string
  cancelText?: string
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: "Konfirmasi",
  description: "Apakah Anda yakin ingin melanjutkan?",
  confirmText: "Konfirmasi",
  cancelText: "Batal",
  loading: false,
})

const emit = defineEmits<{
  (e: "update:open", value: boolean): void
  (e: "confirm"): void
}>()

const handleConfirm = () => {
  emit("confirm")
}
</script>

<template>
  <AlertDialog
      :open="open"
      @update:open="(val) => emit('update:open', val)"
  >
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>
          {{ title }}
        </AlertDialogTitle>

        <AlertDialogDescription>
          {{ description }}
        </AlertDialogDescription>
      </AlertDialogHeader>

      <AlertDialogFooter>
        <AlertDialogCancel>
          {{ cancelText }}
        </AlertDialogCancel>

        <AlertDialogAction
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            :disabled="loading"
            @click="handleConfirm"
        >
          <span v-if="!loading">
            {{ confirmText }}
          </span>
          <span v-else>
            Memproses...
          </span>
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>