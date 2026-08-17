<script setup lang="ts">
import { markRaw, onMounted } from 'vue'
import { Toaster } from 'vue-sonner'
import { TooltipProvider } from '@/components/ui/tooltip'
import { useAuthStore } from '@/stores/auth-store'
import { useSettingStore } from '@/stores/setting-store'
import { useThemeStore } from '@/stores/theme-store'
import AppLayout from '@/layouts/app-layout.vue'
import GuestLayout from '@/layouts/guest-layout.vue'

type LayoutType = 'AppLayout' | 'GuestLayout'

const themeStore = useThemeStore()
const authStore = useAuthStore()
const settingStore = useSettingStore()

onMounted(async () => {
  themeStore.initTheme()
  await settingStore.initializeSettings()
  const token = localStorage.getItem('token')
  if (token) {
    await authStore.fetchUser().catch(() => console.warn('Session expired'))
  }
})

const layouts = {
  AppLayout: markRaw(AppLayout),
  GuestLayout: markRaw(GuestLayout),
}
</script>

<template>
  <TooltipProvider :delay-duration="100">
    <div
      v-if="authStore.loading"
      class="bg-background fixed inset-0 z-[100] flex items-center justify-center"
    >
      <div class="border-primary h-8 w-8 animate-spin rounded-full border-4 border-t-transparent" />
    </div>

    <Toaster
      :theme="themeStore.isDark ? 'dark' : 'light'"
      position="top-right"
      :rich-colors="true"
      close-button
    />

    <router-view v-slot="{ Component, route }">
      <transition name="page-fade" mode="out-in">
        <component
          :is="layouts[route.meta.layout as LayoutType] || layouts.GuestLayout"
          :key="(route.meta.layout as string) || 'guest'"
        >
          <component :is="Component" :key="route.path" />
        </component>
      </transition>
    </router-view>
  </TooltipProvider>
</template>