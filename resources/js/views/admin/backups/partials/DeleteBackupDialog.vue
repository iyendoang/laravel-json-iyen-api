<script setup lang="ts">
import {ref} from 'vue'
import ConfirmDialog from '@/components/shared/confirm-dialog.vue'
import {backupService} from '@/services/admin/backup.service'
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

const isDeleting = ref(false)

const handleConfirm = async () => {
  if (!props.backup) return
  isDeleting.value = true
  try {
    const success = await backupService.deleteBackup(props.backup.id)
    if (success) {
      emit('update:open', false)
      emit('success')
    }
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <ConfirmDialog
    :open="open"
    variant="danger"
    title="Hapus Arsip Snapshot"
    :description="`Apakah Anda yakin ingin menghapus berkas snapshot '${backup?.filename}' secara permanen dari server penyimpanan?`"
    confirm-text="Hapus Berkas"
    cancel-text="Batal"
    :loading="isDeleting"
    @update:open="emit('update:open', $event)"
    @confirm="handleConfirm"
    @cancel="emit('update:open', false)"
  />
</template>