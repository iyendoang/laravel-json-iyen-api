<script setup lang="ts">
import { useThemeStore } from '@/stores/theme-store'
import { Monitor, Moon, Sun } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from '@/components/ui/dropdown-menu'

const theme = useThemeStore()
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        size="icon"
        class="border-sidebar-border/50 bg-background/50 hover:bg-muted h-9 w-9 rounded-lg border backdrop-blur-sm transition-all"
      >
        <Sun
          v-if="theme.mode === 'light'"
          class="h-[1.2rem] w-[1.2rem] scale-100 rotate-0 transition-all"
        />
        <Moon
          v-else-if="theme.mode === 'dark'"
          class="h-[1.2rem] w-[1.2rem] scale-100 rotate-0 transition-all"
        />
        <Monitor v-else class="h-[1.2rem] w-[1.2rem] scale-100 rotate-0 transition-all" />
        <span class="sr-only">Ganti Tema</span>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent
      align="end"
      class="border-sidebar-border/50 mt-2 w-36 shadow-xl backdrop-blur-lg"
    >
      <DropdownMenuItem @click="theme.setMode('light')" class="cursor-pointer gap-2">
        <Sun class="h-4 w-4 opacity-70" />
        <span class="text-sm font-medium">Terang</span>
      </DropdownMenuItem>

      <DropdownMenuItem @click="theme.setMode('dark')" class="cursor-pointer gap-2">
        <Moon class="h-4 w-4 opacity-70" />
        <span class="text-sm font-medium">Gelap</span>
      </DropdownMenuItem>

      <DropdownMenuItem @click="theme.setMode('system')" class="cursor-pointer gap-2">
        <Monitor class="h-4 w-4 opacity-70" />
        <span class="text-sm font-medium">Sistem</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
