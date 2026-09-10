<script setup lang="ts">
import {ref, onMounted} from 'vue'
import {
  ArrowUpCircle,
  RefreshCw,
  Clock,
  ShieldCheck,
  FileText,
  Server,
  Sparkles,
  GitBranch,
} from 'lucide-vue-next'

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import AppButton from '@/components/shared/app-button.vue'
import AppBadge from '@/components/shared/app-badge.vue'
import ConfirmDialog from '@/components/shared/confirm-dialog.vue'
import {useSystemUpdateStore} from '@/stores/system-update.store'

interface Props {
  open: boolean
}

defineProps<Props>()
const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
}>()

const updateStore = useSystemUpdateStore()
const showConfirmModal = ref(false)

const handleRefreshCheck = async () => {
  await updateStore.checkUpdate(true)
}

const handleOpenConfirm = () => {
  showConfirmModal.value = true
}

const handleConfirmUpdate = async () => {
  showConfirmModal.value = false
  emit('update:open', false) // Tutup dialog utama agar fokus ke global overlay

  const success = await updateStore.executeUpdate()
  if (success) {
    window.location.reload()
  }
}

onMounted(() => {
  if (updateStore.canCheckUpdate && updateStore.history.length === 0) {
    updateStore.checkUpdate()
  }
})
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent
      class="border-border/60 bg-background max-h-[88vh] overflow-y-auto p-6 sm:max-w-2xl select-none"
    >
      <!-- Header Section -->
      <DialogHeader class="space-y-1.5 border-b border-border/40 pb-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div
              class="bg-primary/10 text-primary flex h-9 w-9 items-center justify-center rounded-lg border border-primary/20"
            >
              <ArrowUpCircle class="size-5"/>
            </div>
            <div>
              <DialogTitle class="text-base font-bold tracking-tight">
                Pusat Distribusi & Pembaruan Sistem
              </DialogTitle>
              <DialogDescription class="text-muted-foreground text-xs">
                Infrastruktur OTA untuk pemutakhiran inti framework, aset statis, dan migrasi skema database.
              </DialogDescription>
            </div>
          </div>

          <AppButton
            variant="outline"
            size="icon-sm"
            tooltip="Segarkan Informasi Status"
            :disabled="updateStore.isChecking || updateStore.isUpdating"
            @click="handleRefreshCheck"
          >
            <RefreshCw class="size-3.5" :class="{ 'animate-spin': updateStore.isChecking }"/>
          </AppButton>
        </div>
      </DialogHeader>

      <div class="space-y-5 pt-2">
        <!-- Status Versi (Metrics Grid) -->
        <div class="grid grid-cols-2 gap-3">
          <!-- Versi Lokal -->
          <div
            class="bg-muted/30 border-border/60 flex flex-col justify-between rounded-xl border p-3.5 transition-all"
          >
            <div class="flex items-center justify-between">
              <span
                class="text-muted-foreground flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider"
              >
                <Server class="size-3.5"/>
                Lingkungan Lokal
              </span>
              <AppBadge variant="secondary" size="sm" class="font-mono text-[10px]">
                Installed
              </AppBadge>
            </div>
            <div class="mt-3 flex items-baseline gap-1">
              <span class="text-muted-foreground font-mono text-sm font-medium">v</span>
              <span class="text-foreground font-mono text-xl font-bold tracking-tight">
                {{ updateStore.currentVersion }}
              </span>
            </div>
          </div>

          <!-- Versi Server Pusat -->
          <div
            class="flex flex-col justify-between rounded-xl border p-3.5 transition-all"
            :class="[
              updateStore.hasUpdateAvailable
                ? 'border-amber-500/30 bg-amber-500/5'
                : 'border-border/60 bg-muted/30'
            ]"
          >
            <div class="flex items-center justify-between">
              <span
                class="text-muted-foreground flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider"
              >
                <GitBranch class="size-3.5"/>
                Katalog Rilis
              </span>
              <AppBadge
                v-if="updateStore.hasUpdateAvailable"
                variant="outline"
                size="sm"
                class="border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[10px]"
              >
                Tersedia Versi Baru
              </AppBadge>
              <AppBadge
                v-else
                variant="outline"
                size="sm"
                class="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px]"
              >
                Up to Date
              </AppBadge>
            </div>
            <div class="mt-3 flex items-baseline gap-1">
              <span class="text-muted-foreground font-mono text-sm font-medium">v</span>
              <span
                class="font-mono text-xl font-bold tracking-tight"
                :class="updateStore.hasUpdateAvailable ? 'text-amber-600 dark:text-amber-500' : 'text-foreground'"
              >
                {{ updateStore.latestVersion || updateStore.currentVersion }}
              </span>
            </div>
          </div>
        </div>

        <!-- Banner Status Update Tersedia -->
        <div
          v-if="updateStore.hasUpdateAvailable"
          class="border-border/70 bg-card shadow-2xs space-y-3.5 rounded-xl border p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2.5">
              <div
                class="bg-amber-500/10 text-amber-600 dark:text-amber-400 mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
              >
                <Sparkles class="size-4"/>
              </div>
              <div class="space-y-0.5">
                <h4 class="text-foreground text-sm font-bold">
                  {{ updateStore.updateInfo?.title || 'Paket Pembaruan Inti Tersedia' }}
                </h4>
                <p class="text-muted-foreground text-xs">
                  Versi terbaru membawa pembaruan dependensi, perbaikan keamanan, dan optimalisasi query.
                </p>
              </div>
            </div>

            <AppButton
              v-if="updateStore.canExecuteUpdate"
              size="sm"
              :loading="updateStore.isUpdating"
              loading-text="Memperbarui..."
              :left-icon="ArrowUpCircle"
              @click="handleOpenConfirm"
            >
              Update Sekarang
            </AppButton>
          </div>

          <!-- Changelog Container -->
          <div class="space-y-1.5">
            <div class="text-muted-foreground flex items-center gap-1.5 text-[11px] font-semibold">
              <FileText class="size-3.5"/>
              <span>Catatan Rilis & Log Perubahan</span>
            </div>
            <div
              class="border-border/60 bg-muted/20 text-muted-foreground max-h-40 overflow-y-auto rounded-lg border p-3 font-mono text-xs leading-relaxed"
            >
              <div v-html="updateStore.updateInfo?.changelog || 'Tidak ada detail catatan rilis.'"></div>
            </div>
          </div>
        </div>

        <!-- Banner Sistem Mutakhir -->
        <div
          v-else
          class="border-emerald-500/20 bg-emerald-500/5 flex items-center gap-3 rounded-xl border p-3.5 text-xs text-emerald-700 dark:text-emerald-400"
        >
          <div class="bg-emerald-500/15 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg">
            <ShieldCheck class="size-4.5"/>
          </div>
          <div class="space-y-0.5">
            <p class="font-semibold">Integritas Sistem Terjaga</p>
            <p class="text-emerald-600/90 dark:text-emerald-400/90 text-[11px]">
              Instalasi ini telah sinkron dengan repositori server pusat dan berjalan di versi produksi paling stabil.
            </p>
          </div>
        </div>

        <!-- Tabel Log Riwayat Pembaruan -->
        <div class="space-y-2.5">
          <div class="flex items-center justify-between">
            <h4 class="text-foreground flex items-center gap-1.5 text-xs font-bold tracking-tight">
              <Clock class="text-muted-foreground size-3.5"/>
              <span>Histori Audit Pembaruan Sistem</span>
            </h4>
            <span class="text-muted-foreground text-[10px]">Menampilkan 5 aktivitas terakhir</span>
          </div>

          <div class="border-border/60 bg-card overflow-hidden rounded-xl border shadow-2xs">
            <table class="w-full border-collapse text-left text-xs">
              <thead>
              <tr class="bg-muted/40 text-muted-foreground border-border/60 border-b text-[11px] font-semibold">
                <th class="px-3.5 py-2.5 font-medium">Transisi Versi</th>
                <th class="px-3.5 py-2.5 font-medium">Tanggal Eksekusi</th>
                <th class="px-3.5 py-2.5 font-medium">Status Operasi</th>
                <th class="px-3.5 py-2.5 font-medium">Operator</th>
                <th class="px-3.5 py-2.5 font-medium">Rincian Log</th>
              </tr>
              </thead>
              <tbody class="divide-border/40 divide-y font-normal">
              <tr v-if="updateStore.history.length === 0">
                <td colspan="5" class="text-muted-foreground p-6 text-center italic">
                  Belum ada riwayat aktivitas pembaruan yang tercatat di database.
                </td>
              </tr>
              <tr
                v-for="(log, idx) in updateStore.history"
                :key="idx"
                class="hover:bg-muted/30 transition-colors"
              >
                <td class="px-3.5 py-2.5 font-mono text-[11px]">
                  <span class="text-muted-foreground">v{{ log.version_from }}</span>
                  <span class="text-muted-foreground/40 mx-1.5 font-sans">→</span>
                  <span class="text-foreground font-semibold">v{{ log.version_to }}</span>
                </td>
                <td class="text-muted-foreground px-3.5 py-2.5 whitespace-nowrap text-[11px]">
                  {{ log.date }}
                </td>
                <td class="px-3.5 py-2.5">
                  <AppBadge
                    :variant="log.status === 'success' ? 'default' : 'destructive'"
                    size="sm"
                    class="text-[10px] font-semibold uppercase tracking-wider"
                  >
                    {{ log.status === 'success' ? 'Sukses' : 'Gagal' }}
                  </AppBadge>
                </td>
                <td class="text-foreground/90 px-3.5 py-2.5 font-medium whitespace-nowrap text-[11px]">
                  {{ log.admin }}
                </td>
                <td
                  class="text-muted-foreground max-w-[220px] truncate px-3.5 py-2.5 text-[11px]"
                  :title="log.log || ''"
                >
                  {{ log.log || '-' }}
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>

  <!-- Modal Konfirmasi Berbasis Shadcn/Alert-Dialog -->
  <ConfirmDialog
    :open="showConfirmModal"
    variant="warning"
    title="Konfirmasi Pembaruan Inti Sistem"
    description="Proses ini akan mengunduh paket pembaruan, menimpa file sistem, dan menjalankan migrasi skema database. Sistem akan masuk ke mode maintenance sementara."
    confirm-text="Lanjutkan Pembaruan"
    cancel-text="Batal"
    :loading="updateStore.isUpdating"
    @update:open="showConfirmModal = $event"
    @confirm="handleConfirmUpdate"
    @cancel="showConfirmModal = false"
  />
</template>