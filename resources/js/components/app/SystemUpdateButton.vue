<script setup lang="ts">
import {ref} from 'vue'
import {ArrowUpCircle} from 'lucide-vue-next'
import {useSystemUpdateStore} from '@/stores/system-update.store'
import SystemUpdateDialog from '@/components/app/system-update-dialog-form/index.vue'

const updateStore = useSystemUpdateStore()
const isDialogOpen = ref(false)
</script>

<template>
  <!-- Render hanya jika user memiliki permission 'update-check' -->
  <template v-if="updateStore.canCheckUpdate">
    <button
      type="button"
      class="text-muted-foreground hover:text-foreground hover:bg-accent/50 relative flex h-7 w-7 items-center justify-center rounded-md transition-colors outline-none cursor-pointer"
      title="Pembaruan Sistem"
      aria-label="Pembaruan Sistem"
      @click="isDialogOpen = true"
    >
      <ArrowUpCircle class="h-4 w-4"/>

      <!-- Indikator Badge Ping Oranye jika Ada Pembaruan -->
      <span
        v-if="updateStore.hasUpdateAvailable"
        class="absolute top-1 right-1 flex h-2 w-2"
      >
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
      </span>
    </button>

    <!-- Dialog Form Modal Pembaruan -->
    <SystemUpdateDialog
      :open="isDialogOpen"
      @update:open="isDialogOpen = $event"
    />
  </template>
</template>