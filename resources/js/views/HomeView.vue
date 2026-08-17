<template>
  <div class="w-full text-center">
    <!-- Logo/Brand -->
    <div class="mb-8 flex items-center justify-center gap-3">
      <div
        class="bg-primary/10 border-primary/20 flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border shadow-sm">
        <img
          v-if="settingStore.appLogo"
          :src="settingStore.appLogo"
          alt="App Logo"
          class="h-full w-full object-contain p-1.5"
        />
        <ShieldCheck v-else class="text-primary h-6 w-6"/>
      </div>
      <div class="text-left">
        <h1 class="text-foreground text-xl leading-none font-bold tracking-tight">
          {{ settingStore.appName || 'Laravel API' }}
        </h1>
        <p class="text-muted-foreground pt-0.5 text-[11px] font-medium opacity-60">
          {{ settingStore.appSlogan || 'Vue 3 + TypeScript + shadcn' }}
        </p>
      </div>
    </div>

    <!-- Hero Section -->
    <div class="space-y-4">
      <h2 class="text-3xl font-bold text-foreground">
        Selamat Datang! 🎉
      </h2>
      <p class="text-muted-foreground text-sm">
        Aplikasi modern dengan Laravel + Vue 3 + TypeScript + shadcn/ui
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8 flex flex-col gap-3">
      <router-link
        to="/login"
        class="bg-primary text-primary-foreground hover:bg-primary/90 focus-visible:ring-primary/30 inline-flex items-center justify-center rounded-md px-6 py-2.5 text-sm font-medium shadow-sm transition-all active:scale-[0.98] focus-visible:ring-4 focus-visible:outline-none"
      >
        <LogIn class="mr-2 h-4 w-4"/>
        Login
      </router-link>

      <router-link
        to="/register"
        class="border-border text-foreground hover:bg-muted inline-flex items-center justify-center rounded-md border bg-transparent px-6 py-2.5 text-sm font-medium transition-all active:scale-[0.98]"
      >
        <UserPlus class="mr-2 h-4 w-4"/>
        Register
      </router-link>
    </div>

    <!-- Features -->
    <div class="mt-12 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="bg-card border-border rounded-lg border p-4 text-center shadow-sm">
        <Shield class="text-primary mx-auto mb-2 h-6 w-6"/>
        <h3 class="text-foreground text-sm font-semibold">Secure</h3>
        <p class="text-muted-foreground mt-1 text-xs">JWT Authentication</p>
      </div>
      <div class="bg-card border-border rounded-lg border p-4 text-center shadow-sm">
        <Users class="text-primary mx-auto mb-2 h-6 w-6"/>
        <h3 class="text-foreground text-sm font-semibold">Role Based</h3>
        <p class="text-muted-foreground mt-1 text-xs">Spatie Permission</p>
      </div>
      <div class="bg-card border-border rounded-lg border p-4 text-center shadow-sm">
        <Palette class="text-primary mx-auto mb-2 h-6 w-6"/>
        <h3 class="text-foreground text-sm font-semibold">Themable</h3>
        <p class="text-muted-foreground mt-1 text-xs">13 Theme Colors</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {useSettingStore} from '@/stores/setting-store'
import {
  ShieldCheck,
  LogIn,
  UserPlus,
  Shield,
  Users,
  Palette,
} from 'lucide-vue-next'

const settingStore = useSettingStore()
</script>