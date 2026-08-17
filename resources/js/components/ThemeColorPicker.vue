<script setup lang="ts">
import {useThemeStore} from '@/stores/theme-store'
import type {ThemeColor} from '@/stores/theme-store'
import {Check, Palette} from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {Button} from '@/components/ui/button'

const themeStore = useThemeStore()

const themeOptions: Array<{
  name: string
  color: ThemeColor
  hex: string
}> = [
  // Standard
  {name: 'Zinc', color: 'default', hex: 'oklch(0.37 0.013 285.805)'},
  {name: 'Biru', color: 'blue', hex: 'oklch(0.488 0.243 264.376)'},
  {name: 'Hijau', color: 'green', hex: 'oklch(0.596 0.145 163.225)'},
  {name: 'Mawar', color: 'rose', hex: 'oklch(0.586 0.253 17.585)'},
  {name: 'Merah', color: 'red', hex: 'oklch(0.577 0.245 27.325)'},
  {name: 'Ungu', color: 'violet', hex: 'oklch(0.541 0.281 293.009)'},
  {name: 'Kuning', color: 'yellow', hex: 'oklch(0.852 0.199 91.936)'},
  {name: 'Oranye', color: 'orange', hex: 'oklch(0.646 0.222 41.116)'},
  // Premium
  {name: 'Claude', color: 'claude', hex: 'oklch(0.62 0.14 39.15)'},
  {name: 'Kopi', color: 'caffeine', hex: 'oklch(0.43 0.04 42.00)'},
  {name: 'Anggur', color: 'luxury', hex: 'oklch(0.47 0.15 25.06)'},
  {name: 'Marvel', color: 'marvel', hex: 'oklch(0.55 0.22 27.03)'},
  {name: 'Aurora', color: 'aurora', hex: 'oklch(0.75 0.18 190)'},
]
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        size="icon"
        class="border-border/40 bg-background/50 hover:bg-accent/50 relative h-9 w-9 cursor-pointer rounded-xl border transition-all duration-300 active:scale-90"
      >
        <Palette class="h-4 w-4 opacity-70"/>
        <span
          class="ring-background absolute top-1.5 right-1.5 h-2 w-2 rounded-full ring-2 transition-colors duration-500"
          :style="{
                        backgroundColor: themeOptions.find((o) => o.color === themeStore.color)?.hex,
                    }"
        />
        <span class="sr-only">Pilih Warna</span>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent
      align="end"
      :side-offset="8"
      class="border-border/50 bg-background/95 w-64 rounded-2xl p-4 shadow-2xl backdrop-blur-xl"
    >
      <DropdownMenuLabel class="text-muted-foreground/60 px-1 pb-2 text-[10px] font-bold tracking-widest uppercase">
        Warna Aksen
      </DropdownMenuLabel>

      <DropdownMenuSeparator class="bg-border/40 mb-3"/>

      <div class="grid grid-cols-5 gap-2.5">
        <button
          v-for="option in themeOptions"
          :key="option.color"
          @click="themeStore.setColor(option.color)"
          :title="option.name"
          class="group relative flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-300 active:scale-75"
        >
          <div
            v-if="themeStore.color === option.color"
            class="border-primary/30 absolute -inset-0.5 rounded-xl border-2"
          ></div>

          <div
            class="border-border/20 h-8 w-8 rounded-lg border shadow-sm transition-all duration-300 group-hover:scale-110 group-active:scale-95"
            :style="{
                            backgroundColor: option.hex,
                        }"
          >
            <div
              v-if="themeStore.color === option.color"
              class="flex h-full w-full items-center justify-center text-white mix-blend-difference"
            >
              <Check class="h-3.5 w-3.5" stroke-width="3"/>
            </div>
          </div>
        </button>
      </div>

      <div class="mt-3 flex items-center justify-between border-t border-border/40 pt-2">
                <span class="text-muted-foreground text-[10px]">
                    {{ themeOptions.find((o) => o.color === themeStore.color)?.name }}
                </span>
        <span
          class="h-3 w-3 rounded-full"
          :style="{
                        backgroundColor: themeOptions.find((o) => o.color === themeStore.color)?.hex,
                    }"
        />
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>