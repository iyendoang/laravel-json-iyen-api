<script setup lang="ts">
import ThemeToggle from '@/components/ThemeToggle.vue'
import ThemeColorPicker from '@/components/ThemeColorPicker.vue'
import ThemeToggleMobile from '@/components/ThemeToggleMobile.vue'
import ThemeColorPickerMobile from '@/components/ThemeColorPickerMobile.vue'
import {useSettingStore} from '@/stores/setting-store'

const settingStore = useSettingStore()
</script>

<template>
  <div
    class="bg-background relative flex min-h-screen flex-col items-center justify-center overflow-hidden p-6 transition-colors duration-500">
    <!-- Theme Controls - Desktop -->
    <div class="absolute top-6 right-6 z-20 hidden items-center gap-2 md:flex">
      <div
        class="border-border/40 bg-background/50 flex items-center gap-1 rounded-xl border p-1 shadow-sm backdrop-blur-md">
        <ThemeColorPicker/>
        <div class="bg-border/50 mx-1 h-4 w-[1px]"/>
        <ThemeToggle/>
      </div>
    </div>

    <!-- Theme Controls - Mobile -->
    <div class="absolute top-6 right-6 z-20 flex items-center gap-2 md:hidden">
      <ThemeColorPickerMobile/>
      <ThemeToggleMobile/>
    </div>

    <!-- Main Content -->
    <main class="relative z-10 w-full max-w-[400px]">
      <slot/>
    </main>
  </div>
</template>