<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import ThemeToggle from '@/components/ThemeToggle.vue'
import ThemeColorPicker from '@/components/ThemeColorPicker.vue'
import ThemeToggleMobile from '@/components/ThemeToggleMobile.vue'
import ThemeColorPickerMobile from '@/components/ThemeColorPickerMobile.vue'
import { useSettingStore } from '@/stores/setting-store'

const route = useRoute()
const settingStore = useSettingStore()

// Cek apakah sedang di halaman login/register
const isAuthPage = computed(() => route.name === 'login' || route.name === 'register')
</script>

<template>
  <div
    class="bg-background min-h-screen flex flex-col text-foreground transition-colors duration-500"
    :class="[isAuthPage ? 'items-center justify-center p-4 sm:p-6' : 'w-full']"
  >
    <!-- Theme Controls khusus halaman Auth (Login/Register) -->
    <template v-if="isAuthPage">
      <!-- Desktop -->
      <div class="fixed top-6 right-6 z-50 hidden items-center gap-2 md:flex">
        <div class="border-border/40 bg-background/80 flex items-center gap-1 rounded-xl border p-1 shadow-sm backdrop-blur-md">
          <ThemeColorPicker />
          <div class="bg-border/50 mx-1 h-4 w-[1px]" />
          <ThemeToggle />
        </div>
      </div>
      <!-- Mobile -->
      <div class="fixed top-4 right-4 z-50 flex items-center gap-2 md:hidden">
        <div class="border-border/40 bg-background/80 flex items-center gap-1 rounded-xl border p-1 shadow-sm backdrop-blur-md">
          <ThemeColorPickerMobile />
          <ThemeToggleMobile />
        </div>
      </div>
    </template>

    <!-- Main Content Slot -->
    <main
      class="w-full relative z-10"
      :class="[isAuthPage ? 'max-w-[420px]' : 'w-full']"
    >
      <slot />
    </main>
  </div>
</template>