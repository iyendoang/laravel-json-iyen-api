<script setup lang="ts">
import { computed } from 'vue'
import { useSettingStore } from '@/stores/setting-store'
import { Activity } from 'lucide-vue-next'

const settingStore = useSettingStore()
const currentYear = new Date().getFullYear()

const copyrightText = computed(() => {
  if (settingStore.footerText) {
    return settingStore.footerText
  }
  return `© ${currentYear} ${settingStore.appName || 'Laravel API'}. All rights reserved.`
})
</script>

<template>
  <footer
    class="border-border/60 bg-background/80 relative mt-auto border-t px-4 py-2.5 shadow-2xs backdrop-blur-md transition-colors duration-300 select-none"
  >
    <div class="mx-auto flex flex-col items-center justify-between gap-2 sm:flex-row text-[11px]">
      <!-- Left: Dynamic Copyright & System Name -->
      <div class="flex items-center gap-2 text-muted-foreground">
        <span>{{ copyrightText }}</span>
        <span class="opacity-30 hidden sm:inline">•</span>
        <span class="bg-primary/10 text-primary border-primary/20 hidden items-center rounded border px-1.5 py-0.2 text-[9px] font-semibold sm:inline-flex">
          v1.0.0
        </span>
      </div>

      <!-- Right: System Status & Quick Links -->
      <div class="flex items-center gap-4 text-muted-foreground">
        <!-- Live Status Indicator -->
        <div class="flex items-center gap-1.5 text-[10px]">
          <span class="relative flex h-2 w-2">
            <span class="bg-emerald-400 absolute inline-flex h-full w-full animate-ping rounded-full opacity-75" />
            <span class="bg-emerald-500 relative inline-flex h-2 w-2 rounded-full" />
          </span>
          <span class="font-medium text-foreground/80">Sistem Online</span>
        </div>

        <span class="opacity-30">•</span>

        <!-- External/Internal Support Links -->
        <router-link
          to="/admin/settings"
          class="hover:text-foreground transition-colors"
        >
          Pengaturan
        </router-link>

        <a
          v-if="settingStore.appContact?.email"
          :href="`mailto:${settingStore.appContact.email}`"
          class="hover:text-foreground transition-colors"
        >
          Bantuan
        </a>
        <span v-else class="text-muted-foreground/60">
          Support
        </span>
      </div>
    </div>
  </footer>
</template>