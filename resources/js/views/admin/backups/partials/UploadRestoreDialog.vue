<script setup lang="ts">
import {ref, computed} from 'vue'
import {
  UploadCloud,
  FileCheck2,
  AlertTriangle,
  X,
  FileCode2,
} from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import AppButton from '@/components/shared/app-button.vue'
import {backupService} from '@/services/admin/backup.service'
import {useSystemProcessOverlay} from '@/composables/useSystemProcessOverlay'

interface Props {
  open: boolean
}

defineProps<Props>()
const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'success'): void
}>()

const overlay = useSystemProcessOverlay()
const fileInputRef = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const isDragging = ref(false)
const isUploading = ref(false)
const errorMessage = ref<string | null>(null)

const formattedFileSize = computed(() => {
  if (!selectedFile.value) return ''
  const bytes = selectedFile.value.size
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
})

const resetState = () => {
  selectedFile.value = null
  errorMessage.value = null
  isDragging.value = false
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const handleClose = () => {
  if (isUploading.value) return
  resetState()
  emit('update:open', false)
}

const validateAndSetFile = (file: File) => {
  errorMessage.value = null
  if (!file.name.toLowerCase().endsWith('.sidosts')) {
    errorMessage.value = 'Format berkas tidak valid! Hanya berkas snapshot .sidosts yang diizinkan.'
    selectedFile.value = null
    return
  }
  selectedFile.value = file
}

const handleFileInputChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) validateAndSetFile(file)
}

const handleDrop = (event: DragEvent) => {
  isDragging.value = false
  const file = event.dataTransfer?.files?.[0]
  if (file) validateAndSetFile(file)
}

const handleExecuteUpload = async () => {
  if (!selectedFile.value) return

  const fileToRestore = selectedFile.value
  emit('update:open', false)

  await overlay.wrap(
    async () => {
      isUploading.value = true
      try {
        const success = await backupService.uploadAndRestore(fileToRestore)
        if (success) {
          resetState()
          emit('success')
        }
      } catch (err: any) {
        errorMessage.value = err?.message || 'Gagal memulihkan database dari berkas unggahan.'
      } finally {
        isUploading.value = false
      }
    },
    {
      title: 'Mengunggah & Memulihkan Database',
      description: `Memproses berkas '${fileToRestore.name}'.`,
      statusStep: 'Mengunggah berkas, mendekripsi AES-256 & mengimpor skema...',
    }
  )
}
</script>

<template>
  <Dialog :open="open" @update:open="(v) => (!v ? handleClose() : emit('update:open', v))">
    <DialogContent class="border-border/60 bg-background sm:max-w-md p-5 select-none">
      <DialogHeader class="space-y-1 pb-2 border-b border-border/40">
        <div class="flex items-center gap-2">
          <div
            class="bg-primary/10 text-primary flex h-8 w-8 items-center justify-center rounded-lg border border-primary/20"
          >
            <UploadCloud class="size-4"/>
          </div>
          <div>
            <DialogTitle class="text-sm font-bold tracking-tight">Unggah & Pulihkan Snapshot</DialogTitle>
            <DialogDescription class="text-[11px] text-muted-foreground">
              Pulihkan basis data dari arsip terenkripsi komputer Anda (.sidosts).
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <div class="space-y-3.5 py-2">
        <input
          ref="fileInputRef"
          type="file"
          accept=".sidosts"
          class="hidden"
          @change="handleFileInputChange"
        />

        <!-- Dropzone Area -->
        <div
          v-if="!selectedFile"
          class="border-border/60 hover:border-primary/50 hover:bg-muted/30 group flex flex-col items-center justify-center rounded-xl border border-dashed p-6 text-center cursor-pointer transition-colors"
          :class="{ 'border-primary bg-primary/5': isDragging }"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop"
          @click="fileInputRef?.click()"
        >
          <div
            class="bg-muted/60 text-muted-foreground group-hover:text-primary mb-2.5 flex h-10 w-10 items-center justify-center rounded-full border transition-colors"
          >
            <UploadCloud class="size-5"/>
          </div>
          <p class="text-xs font-semibold text-foreground">Klik untuk memilih atau tarik berkas ke sini</p>
          <p class="text-muted-foreground text-[10px] mt-0.5">
            Hanya menerima arsip berekstensi .sidosts (Maks. 500 MB)
          </p>
        </div>

        <!-- Selected File Badge/Card -->
        <div
          v-else
          class="bg-card border-border/70 flex items-center justify-between rounded-xl border p-3 shadow-2xs"
        >
          <div class="flex items-center gap-2.5 overflow-hidden">
            <div
              class="bg-emerald-500/10 text-emerald-600 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-emerald-500/20"
            >
              <FileCheck2 class="size-4"/>
            </div>
            <div class="truncate text-left">
              <p class="truncate font-mono text-xs font-semibold text-foreground leading-tight">
                {{ selectedFile.name }}
              </p>
              <p class="text-muted-foreground text-[10px]">{{ formattedFileSize }}</p>
            </div>
          </div>
          <button
            type="button"
            :disabled="isUploading"
            class="text-muted-foreground hover:text-destructive p-1 rounded-md transition-colors"
            @click="resetState"
          >
            <X class="size-4"/>
          </button>
        </div>

        <!-- Error Message -->
        <p v-if="errorMessage" class="text-destructive text-[11px] font-medium leading-tight">
          {{ errorMessage }}
        </p>

        <!-- Risk Warning -->
        <div
          class="border-amber-500/25 bg-amber-500/5 text-amber-800 dark:text-amber-400 flex items-start gap-2.5 rounded-lg border p-2.5 text-[11px] leading-relaxed"
        >
          <AlertTriangle class="mt-0.5 size-3.5 shrink-0 text-amber-500"/>
          <span>Seluruh skema dan data database aktif akan langsung ditimpa oleh isi arsip ini. Pastikan tidak ada transaksi yang terputus.</span>
        </div>
      </div>

      <DialogFooter class="gap-2 pt-2 border-t border-border/40">
        <AppButton
          variant="outline"
          size="sm"
          :disabled="isUploading"
          @click="handleClose"
        >
          Batal
        </AppButton>
        <AppButton
          size="sm"
          :disabled="!selectedFile"
          :loading="isUploading"
          loading-text="Mendekripsi & Memulihkan..."
          :left-icon="FileCode2"
          @click="handleExecuteUpload"
        >
          Mulai Eksekusi Restore
        </AppButton>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>