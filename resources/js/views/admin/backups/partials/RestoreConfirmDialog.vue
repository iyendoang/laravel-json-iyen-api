<script setup lang="ts">
import {ref} from 'vue'
import {AlertTriangle} from 'lucide-vue-next'
import ConfirmDialog from '@/components/shared/confirm-dialog.vue'
import {backupService} from '@/services/admin/backup.service'
import {useSystemProcessOverlay} from '@/composables/useSystemProcessOverlay'
import type {BackupItem} from '@/types/backup'

interface Props {
  open: boolean
  backup: BackupItem | null
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'success'): void
}>()

const overlay = useSystemProcessOverlay()
const isRestoring = ref(false)

const handleConfirm = async () => {
  if (!props.backup) return

  const backupTarget = props.backup
  emit('update:open', false)

  await overlay.wrap(
    async () => {
      isRestoring.value = true
      try {
        const success = await backupService.restoreDatabase(backupTarget.id)
        if (success) {
          emit('success')
        }
      } finally {
        isRestoring.value = false
      }
    },
    {
      title: 'Sedang Memulihkan Basis Data',
      description: `Mendekripsi snapshot '${backupTarget.filename}' dan mengimpor struktur skema database.`,
      statusStep: 'Mendekripsi cipher AES-256 & mengimpor data SQL...',
    }
  )
}
</script>

<template>
  <ConfirmDialog
    :open="open"
    variant="warning"
    title="Konfirmasi Pemulihan Snapshot"
    :description="`Apakah Anda yakin ingin memulihkan database dari snapshot '${backup?.filename}'? Semua perubahan setelah titik ini akan ditimpa.`"
    confirm-text="Mulai Pemulihan"
    cancel-text="Batal"
    :loading="isRestoring"
    @update:open="emit('update:open', $event)"
    @confirm="handleConfirm"
    @cancel="emit('update:open', false)"
  >
    <div
      class="border-amber-500/20 bg-amber-500/5 text-amber-700 dark:text-amber-400 mt-2 flex items-start gap-2.5 rounded-lg border p-2.5 text-xs"
    >
      <AlertTriangle class="mt-0.5 size-4 shrink-0 text-amber-500"/>
      <span>Operasi ini akan mendekripsi cipher AES-256 dan mengimpor struktur skema database ke keadaan waktu snapshot diambil.</span>
    </div>
  </ConfirmDialog>
</template>