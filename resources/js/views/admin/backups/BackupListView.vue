<script setup lang="ts">
import {ref, onMounted} from 'vue'
import {
  Database,
  Download,
  RotateCcw,
  Trash2,
  Plus,
  RefreshCw,
  ShieldCheck,
  HardDrive,
  Clock,
  History,
  FileCheck2,
  UploadCloud,
} from 'lucide-vue-next'

import AppButton from '@/components/shared/app-button.vue'
import AppBadge from '@/components/shared/app-badge.vue'
import UploadRestoreDialog from './partials/UploadRestoreDialog.vue'
import RestoreConfirmDialog from './partials/RestoreConfirmDialog.vue'
import DeleteBackupDialog from './partials/DeleteBackupDialog.vue'

import {backupService} from '@/services/admin/backup.service'
import {useSystemProcessOverlay} from '@/composables/useSystemProcessOverlay'
import type {BackupItem, RestoreHistoryItem} from '@/types/backup'

const overlay = useSystemProcessOverlay()

// Data States
const backups = ref<BackupItem[]>([])
const restoreHistories = ref<RestoreHistoryItem[]>([])
const activeTab = ref<'backups' | 'history'>('backups')

// Loading Indicators
const isLoading = ref(false)
const isCreating = ref(false)

// Modals State
const showUploadDialog = ref(false)
const showRestoreDialog = ref(false)
const showDeleteDialog = ref(false)
const activeBackup = ref<BackupItem | null>(null)

const loadData = async () => {
  isLoading.value = true
  try {
    const [backupsRes, historyRes] = await Promise.all([
      backupService.getBackups(),
      backupService.getRestoreHistories(),
    ])
    backups.value = backupsRes || []
    restoreHistories.value = historyRes || []
  } finally {
    isLoading.value = false
  }
}

const handleCreateBackup = async () => {
  await overlay.wrap(
    async () => {
      isCreating.value = true
      try {
        await backupService.createBackup()
        await loadData()
      } finally {
        isCreating.value = false
      }
    },
    {
      title: 'Membuat Cadangan Database',
      description: 'Mengekspor struktur skema MySQL dan mengenkripsi ke berkas .sidosts.',
      statusStep: 'Menjalankan mysqldump & enkripsi AES-256...',
    }
  )
}

const handleDownload = async (backup: BackupItem) => {
  await backupService.downloadBackup(backup.id)
}

const openRestoreModal = (backup: BackupItem) => {
  activeBackup.value = backup
  showRestoreDialog.value = true
}

const openDeleteModal = (backup: BackupItem) => {
  activeBackup.value = backup
  showDeleteDialog.value = true
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="space-y-4 p-5 select-none">
    <!-- Compact Header Section -->
    <div class="flex flex-col gap-3.5 border-b border-border/50 pb-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3 min-w-0">
        <div
          class="bg-primary/10 text-primary ring-1 ring-primary/20 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
        >
          <Database class="size-4.5"/>
        </div>
        <div class="min-w-0 space-y-0.5">
          <h1 class="text-sm sm:text-base font-bold tracking-tight text-foreground truncate">
            Cadangan & Pemulihan Basis Data
          </h1>
          <p class="text-muted-foreground text-[11px] leading-none truncate">
            Manajemen snapshot MySQL terenkripsi AES-256 (.sidosts) dan pemulihan sistem point-in-time.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <AppButton
          variant="outline"
          size="icon-sm"
          tooltip="Segarkan Data"
          :disabled="isLoading || isCreating || overlay.state.show"
          @click="loadData"
        >
          <RefreshCw class="size-3.5" :class="{ 'animate-spin': isLoading }"/>
        </AppButton>

        <div class="bg-border/60 h-4 w-px mx-0.5 hidden sm:block"/>

        <AppButton
          variant="outline"
          size="sm"
          class="whitespace-nowrap h-8 px-3 text-xs"
          :disabled="isLoading || isCreating || overlay.state.show"
          :left-icon="UploadCloud"
          @click="showUploadDialog = true"
        >
          Unggah & Pulihkan
        </AppButton>

        <AppButton
          size="sm"
          class="whitespace-nowrap h-8 px-3 text-xs shadow-xs"
          :loading="isCreating"
          loading-text="Mencadangkan..."
          :disabled="overlay.state.show"
          :left-icon="Plus"
          @click="handleCreateBackup"
        >
          Buat Backup Baru
        </AppButton>
      </div>
    </div>

    <!-- Metrics Overview -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
      <div class="bg-card border-border/60 flex items-center justify-between rounded-xl border p-3 shadow-2xs">
        <div>
          <p class="text-muted-foreground text-[10px] font-semibold uppercase tracking-wider">Kapasitas Arsip</p>
          <div class="flex items-baseline gap-1.5 mt-0.5">
            <span class="text-foreground font-mono text-lg font-bold">{{ backups.length }}</span>
            <span class="text-muted-foreground text-[11px]">/ 5 Rotasi Maksimal</span>
          </div>
        </div>
        <div
          class="bg-primary/5 text-primary flex h-8 w-8 items-center justify-center rounded-lg border border-primary/10"
        >
          <HardDrive class="size-4"/>
        </div>
      </div>

      <div class="bg-card border-border/60 flex items-center justify-between rounded-xl border p-3 shadow-2xs">
        <div>
          <p class="text-muted-foreground text-[10px] font-semibold uppercase tracking-wider">Enkripsi Snapshot</p>
          <div class="flex items-baseline gap-1 mt-0.5">
            <span class="text-foreground font-mono text-xs font-bold">AES-256-CBC</span>
            <span class="text-muted-foreground text-[10px]">(PBKDF2)</span>
          </div>
        </div>
        <div
          class="bg-emerald-500/10 text-emerald-600 flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-500/20"
        >
          <ShieldCheck class="size-4"/>
        </div>
      </div>

      <div class="bg-card border-border/60 flex items-center justify-between rounded-xl border p-3 shadow-2xs">
        <div class="truncate mr-2">
          <p class="text-muted-foreground text-[10px] font-semibold uppercase tracking-wider">
            Titik Pemulihan Terkini
          </p>
          <p class="text-foreground text-xs font-semibold truncate mt-0.5">
            {{ backups[0]?.date_indo || 'Belum ada arsip' }}
          </p>
        </div>
        <div
          class="bg-amber-500/10 text-amber-600 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-amber-500/20"
        >
          <Clock class="size-4"/>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1.5 border-b border-border/60 pb-2">
      <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer"
        :class="activeTab === 'backups' ? 'bg-muted text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground'"
        @click="activeTab = 'backups'"
      >
        <FileCheck2 class="size-3.5"/>
        <span>Berkas Snapshot ({{ backups.length }})</span>
      </button>

      <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer"
        :class="activeTab === 'history' ? 'bg-muted text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground'"
        @click="activeTab = 'history'"
      >
        <History class="size-3.5"/>
        <span>Riwayat Pemulihan ({{ restoreHistories.length }})</span>
      </button>
    </div>

    <!-- View 1: Backups Table -->
    <div v-show="activeTab === 'backups'" class="bg-card border-border/60 overflow-hidden rounded-xl border shadow-2xs">
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-xs">
          <thead>
          <tr class="bg-muted/40 text-muted-foreground border-border/60 border-b text-[11px] font-semibold">
            <th class="px-3.5 py-2.5 font-medium">Nama Berkas Snapshot</th>
            <th class="px-3.5 py-2.5 font-medium">Ukuran</th>
            <th class="px-3.5 py-2.5 font-medium">Waktu Pembuatan</th>
            <th class="px-3.5 py-2.5 font-medium">Usia Berkas</th>
            <th class="px-3.5 py-2.5 text-right font-medium">Tindakan</th>
          </tr>
          </thead>
          <tbody class="divide-border/40 divide-y font-normal">
          <tr v-if="isLoading && backups.length === 0">
            <td colspan="5" class="text-muted-foreground p-6 text-center italic">
              <RefreshCw class="mx-auto mb-1.5 size-4 animate-spin text-primary"/>
              Memuat snapshot tersimpan...
            </td>
          </tr>
          <tr v-else-if="backups.length === 0">
            <td colspan="5" class="text-muted-foreground p-6 text-center italic">
              Belum ada arsip cadangan basis data tersimpan.
            </td>
          </tr>
          <tr
            v-for="backup in backups"
            :key="backup.id"
            class="hover:bg-muted/20 transition-colors"
          >
            <td class="px-3.5 py-2 font-mono font-medium text-foreground">
              {{ backup.filename }}
            </td>
            <td class="px-3.5 py-2 font-mono text-muted-foreground text-[11px]">
              {{ backup.size_human }}
            </td>
            <td class="px-3.5 py-2 whitespace-nowrap text-muted-foreground text-[11px]">
              {{ backup.date_indo }}
            </td>
            <td class="px-3.5 py-2 whitespace-nowrap text-muted-foreground text-[11px]">
              {{ backup.age || '-' }}
            </td>
            <td class="px-3.5 py-2">
              <div class="flex items-center justify-end gap-1">
                <AppButton
                  variant="ghost"
                  size="icon-sm"
                  tooltip="Unduh Berkas Snapshot"
                  @click="handleDownload(backup)"
                >
                  <Download class="size-3.5 text-muted-foreground hover:text-foreground"/>
                </AppButton>

                <AppButton
                  variant="ghost"
                  size="icon-sm"
                  tooltip="Pulihkan Database"
                  class="hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400"
                  @click="openRestoreModal(backup)"
                >
                  <RotateCcw class="size-3.5 text-amber-500"/>
                </AppButton>

                <AppButton
                  variant="ghost"
                  size="icon-sm"
                  tooltip="Hapus Arsip"
                  class="hover:bg-destructive/10 hover:text-destructive"
                  @click="openDeleteModal(backup)"
                >
                  <Trash2 class="size-3.5 text-destructive"/>
                </AppButton>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- View 2: History Audit Table -->
    <div v-show="activeTab === 'history'" class="bg-card border-border/60 overflow-hidden rounded-xl border shadow-2xs">
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-xs">
          <thead>
          <tr class="bg-muted/40 text-muted-foreground border-border/60 border-b text-[11px] font-semibold">
            <th class="px-3.5 py-2.5 font-medium">Nama Berkas Sumber</th>
            <th class="px-3.5 py-2.5 font-medium">Operator Eksekutor</th>
            <th class="px-3.5 py-2.5 font-medium">Alamat IP</th>
            <th class="px-3.5 py-2.5 font-medium">Waktu Pemulihan</th>
            <th class="px-3.5 py-2.5 text-right font-medium">Status Operasi</th>
          </tr>
          </thead>
          <tbody class="divide-border/40 divide-y font-normal">
          <tr v-if="restoreHistories.length === 0">
            <td colspan="5" class="text-muted-foreground p-6 text-center italic">
              Belum ada catatan riwayat pemulihan sistem.
            </td>
          </tr>
          <tr
            v-for="log in restoreHistories"
            :key="log.id"
            class="hover:bg-muted/20 transition-colors"
          >
            <td class="px-3.5 py-2 font-mono font-medium text-foreground text-[11px]">
              {{ log.filename }}
            </td>
            <td class="px-3.5 py-2 whitespace-nowrap text-foreground/90 text-[11px]">
              {{ log.actor_name }}
            </td>
            <td class="px-3.5 py-2 font-mono whitespace-nowrap text-muted-foreground text-[11px]">
              {{ log.ip_address || '-' }}
            </td>
            <td class="px-3.5 py-2 whitespace-nowrap text-muted-foreground text-[11px]">
              {{ log.date_indo }} <span class="text-[10px]">({{ log.time_ago }})</span>
            </td>
            <td class="px-3.5 py-2 text-right">
              <AppBadge
                :variant="log.status === 'success' ? 'default' : 'destructive'"
                size="sm"
                class="text-[10px] font-semibold uppercase tracking-wider"
              >
                {{ log.status === 'success' ? 'Berhasil' : 'Gagal' }}
              </AppBadge>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modals -->
    <UploadRestoreDialog
      v-model:open="showUploadDialog"
      @success="loadData"
    />

    <RestoreConfirmDialog
      v-model:open="showRestoreDialog"
      :backup="activeBackup"
      @success="loadData"
    />

    <DeleteBackupDialog
      v-model:open="showDeleteDialog"
      :backup="activeBackup"
      @success="loadData"
    />
  </div>
</template>