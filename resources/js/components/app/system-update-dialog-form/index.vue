<script setup lang="ts">
import { onMounted } from 'vue'
import { useSystemUpdateStore } from '@/stores/system-update-store'
import {
  ArrowUpCircle,
  Calendar,
  RefreshCw,
  Clock,
  ShieldCheck,
  AlertCircle,
  X
} from 'lucide-vue-next'

// UI Components Standard
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle
} from '@/components/ui/dialog'
import AppButton from '@/components/shared/app-button.vue'
import AppBadge from '@/components/shared/app-badge.vue'

interface Props {
  open: boolean
}

const props = defineProps<Props>()
const emit = defineEmits(['update:open'])

const updateStore = useSystemUpdateStore()

const handleRefreshCheck = async () => {
  await updateStore.checkRemoteUpdate(true)
}

const handleStartUpdate = async () => {
  await updateStore.executeSystemUpdate()
}

onMounted(() => {
  if (updateStore.canManageUpdate && updateStore.historyList.length === 0) {
    updateStore.checkRemoteUpdate()
  }
})
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent
      class="border-border/40 bg-background max-h-[85vh] overflow-y-auto p-6 select-none sm:max-w-2xl"
    >
      <DialogHeader>
        <DialogTitle class="flex items-center justify-between text-base font-bold tracking-tight">
          <div class="flex items-center gap-2">
            <ArrowUpCircle class="text-primary size-5" />
            <span>Pembaruan Pusat Aplikasi Sidoel Finance</span>
          </div>

          <AppButton
            variant="ghost"
            size="icon-sm"
            tooltip="Segarkan Informasi"
            :disabled="updateStore.isChecking || updateStore.isUpdating"
            @click="handleRefreshCheck"
          >
            <RefreshCw class="size-3.5" :class="{ 'animate-spin': updateStore.isChecking }" />
          </AppButton>
        </DialogTitle>
        <DialogDescription class="text-muted-foreground/80 text-xs">
          Kelola rilis kode inti sistem, migrasi database skema, dan periksa pemeliharaan OTA
          terpusat.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-5 py-2">
        <div class="bg-muted/40 grid grid-cols-2 gap-4 rounded-xl border p-4 text-center">
          <div class="border-border/60 space-y-1 border-r">
            <span class="text-muted-foreground text-[10px] font-bold tracking-wider uppercase"
              >Versi Saat Ini</span
            >
            <p class="text-foreground font-mono text-lg font-bold">
              v{{ updateStore.currentVersion }}
            </p>
          </div>
          <div class="space-y-1">
            <span class="text-muted-foreground text-[10px] font-bold tracking-wider uppercase"
              >Versi Server Pusat</span
            >
            <p
              class="font-mono text-lg font-bold"
              :class="updateStore.hasUpdate ? 'text-amber-500' : 'text-emerald-500'"
            >
              v{{ updateStore.latestVersion || updateStore.currentVersion }}
            </p>
          </div>
        </div>

        <div
          v-if="updateStore.hasUpdate"
          class="animate-in fade-in slide-in-from-top-2 rounded-xl border border-amber-500/20 bg-amber-500/5 p-4"
        >
          <div class="flex items-start gap-3">
            <AlertCircle class="mt-0.5 size-5 shrink-0 text-amber-500" />
            <div class="flex-1 space-y-2">
              <h4 class="text-foreground text-sm leading-none font-bold">
                {{ updateStore.updateTitle }}
              </h4>

              <div
                class="text-muted-foreground bg-background/50 max-h-32 overflow-y-auto rounded-lg border p-3 text-xs leading-relaxed font-normal"
              >
                <div v-html="updateStore.changelog"></div>
              </div>

              <div class="flex items-center justify-end pt-1">
                <AppButton
                  size="sm"
                  :loading="updateStore.isUpdating"
                  loading-text="Sedang Menginstal Paket Pembaruan..."
                  :left-icon="ArrowUpCircle"
                  @click="handleStartUpdate"
                >
                  Unduh & Perbarui Sekarang
                </AppButton>
              </div>
            </div>
          </div>
        </div>

        <div
          v-else
          class="flex items-center gap-2.5 rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-3.5 text-xs font-medium text-emerald-600 dark:text-emerald-400"
        >
          <ShieldCheck class="size-4.5" />
          <span
            >Selamat! Sistem Anda aman dan sudah menggunakan kode eksekusi rilisan paling
            mutakhir.</span
          >
        </div>

        <div class="space-y-2">
          <h4
            class="text-foreground flex items-center gap-1.5 text-xs font-bold tracking-tight select-none"
          >
            <Clock class="text-muted-foreground size-3.5" />
            <span>Histori Log Pembaruan Terakhir</span>
          </h4>

          <div class="bg-card overflow-hidden rounded-xl border">
            <table class="w-full border-collapse text-left text-xs">
              <thead>
                <tr class="bg-muted/50 text-muted-foreground border-b font-semibold">
                  <th class="p-2.5 font-medium">Versi Jalur</th>
                  <th class="p-2.5 font-medium">Tanggal</th>
                  <th class="p-2.5 font-medium">Status</th>
                  <th class="p-2.5 font-medium">Eksekutor</th>
                  <th class="p-2.5 font-medium">Keterangan Log</th>
                </tr>
              </thead>
              <tbody class="divide-y font-normal">
                <tr v-if="updateStore.historyList.length === 0">
                  <td colspan="5" class="text-muted-foreground p-4 text-center italic">
                    Belum ada riwayat pembaruan sistem yang tercatat.
                  </td>
                </tr>
                <tr
                  v-for="(log, idx) in updateStore.historyList"
                  :key="idx"
                  class="hover:bg-muted/20 transition-colors"
                >
                  <td class="p-2.5 font-mono text-[11px]">
                    <span class="text-muted-foreground">v{{ log.version_from }}</span>
                    <span class="text-muted-foreground/50 mx-1">→</span>
                    <span class="text-foreground font-semibold">v{{ log.version_to }}</span>
                  </td>
                  <td class="text-muted-foreground p-2.5 whitespace-nowrap">{{ log.date }}</td>
                  <td class="p-2.5">
                    <AppBadge
                      :variant="log.status === 'success' ? 'default' : 'destructive'"
                      size="sm"
                    >
                      {{ log.status === 'success' ? 'Sukses' : 'Gagal' }}
                    </AppBadge>
                  </td>
                  <td class="text-foreground/80 p-2.5 font-medium whitespace-nowrap">
                    {{ log.admin }}
                  </td>
                  <td class="text-muted-foreground max-w-[200px] truncate p-2.5" :tooltip="log.log">
                    {{ log.log }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
