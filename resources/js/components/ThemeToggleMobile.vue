<script setup lang="ts">
import {useThemeStore} from '@/stores/theme-store'
import {Monitor, Moon, Sun} from 'lucide-vue-next'

const theme = useThemeStore()

const toggleTheme = () => {
  const modes = ['light', 'dark', 'system'] as const
  const currentIndex = modes.indexOf(theme.mode)
  const nextIndex = (currentIndex + 1) % modes.length
  theme.setMode(modes[nextIndex])
}
</script>

<template>
  <button
    @click="toggleTheme"
    class="flex items-center gap-2 rounded-xl border border-border/40 bg-background/50 px-3 py-2 text-xs font-medium transition-all active:scale-95"
  >
    <Sun v-if="theme.mode === 'light'" class="h-4 w-4 opacity-70"/>
    <Moon v-else-if="theme.mode === 'dark'" class="h-4 w-4 opacity-70"/>
    <Monitor v-else class="h-4 w-4 opacity-70"/>
    <span class="capitalize">{{ theme.mode }}</span>
  </button>
</template>