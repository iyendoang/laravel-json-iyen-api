<script setup lang="ts">
import {useThemeStore} from '@/stores/theme-store'
import {Monitor, Moon, Sun, Check} from 'lucide-vue-next'
import {Button} from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const theme = useThemeStore()

const modes = [
  {value: 'light' as const, label: 'Terang', icon: Sun},
  {value: 'dark' as const, label: 'Gelap', icon: Moon},
  {value: 'system' as const, label: 'Sistem', icon: Monitor},
]
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        size="icon"
        class="border-border/40 bg-background/50 hover:bg-accent/50 h-9 w-9 cursor-pointer rounded-xl border transition-all duration-300 active:scale-90"
      >
        <Sun
          v-if="theme.mode === 'light'"
          class="h-4 w-4 opacity-70 transition-all duration-300"
        />
        <Moon
          v-else-if="theme.mode === 'dark'"
          class="h-4 w-4 opacity-70 transition-all duration-300"
        />
        <Monitor
          v-else
          class="h-4 w-4 opacity-70 transition-all duration-300"
        />
        <span class="sr-only">Ganti Tema</span>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent
      align="end"
      :side-offset="8"
      class="border-border/50 bg-background/95 w-44 rounded-2xl p-2 shadow-2xl backdrop-blur-xl"
    >
      <DropdownMenuLabel class="text-muted-foreground/60 px-2.5 py-1.5 text-[10px] font-bold tracking-widest uppercase">
        Mode Tampilan
      </DropdownMenuLabel>

      <DropdownMenuSeparator class="bg-border/40"/>

      <DropdownMenuItem
        v-for="mode in modes"
        :key="mode.value"
        @click="theme.setMode(mode.value)"
        class="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-medium transition-colors"
      >
        <component :is="mode.icon" class="mr-2 h-4 w-4 opacity-70"/>
        {{ mode.label }}
        <Check
          v-if="theme.mode === mode.value"
          class="text-primary ml-auto h-4 w-4"
          stroke-width="3"
        />
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>