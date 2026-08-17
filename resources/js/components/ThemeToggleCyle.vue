<script setup lang="ts">
import { useThemeStore } from '@/stores/theme-store'
import { Monitor, Moon, Sun } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

const theme = useThemeStore()

/**
 * Fungsi untuk mengganti mode secara berurutan
 */
const toggleTheme = () => {
  const modes: ('light' | 'dark' | 'system')[] = ['light', 'dark', 'system']
  const currentIndex = modes.indexOf(theme.mode)
  const nextIndex = (currentIndex + 1) % modes.length
  theme.setMode(modes[nextIndex])
}
</script>

<template>
  <TooltipProvider :delay-duration="100">
    <Tooltip>
      <TooltipTrigger as-child>
        <Button
          variant="ghost"
          size="icon"
          @click="toggleTheme"
          class="border-sidebar-border/50 bg-background/50 h-9 w-9 rounded-lg border backdrop-blur-sm transition-all active:scale-90"
        >
          <div class="relative flex items-center justify-center">
            <Sun
              :class="[
                'h-[1.2rem] w-[1.2rem] transition-all duration-300',
                theme.mode === 'light' ? 'scale-100 rotate-0' : 'absolute scale-0 rotate-90'
              ]"
            />
            <Moon
              :class="[
                'h-[1.2rem] w-[1.2rem] transition-all duration-300',
                theme.mode === 'dark' ? 'scale-100 rotate-0' : 'absolute scale-0 rotate-90'
              ]"
            />
            <Monitor
              :class="[
                'h-[1.2rem] w-[1.2rem] transition-all duration-300',
                theme.mode === 'system' ? 'scale-100 rotate-0' : 'absolute scale-0 rotate-90'
              ]"
            />
          </div>
          <span class="sr-only">Ganti Tema</span>
        </Button>
      </TooltipTrigger>
      <TooltipContent>Mode: {{ theme.mode }}</TooltipContent>
    </Tooltip>
  </TooltipProvider>
</template>
