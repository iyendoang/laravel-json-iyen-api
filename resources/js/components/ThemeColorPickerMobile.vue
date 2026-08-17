<script setup lang="ts">
import {useThemeStore} from '@/stores/theme-store'
import type {ThemeColor} from '@/stores/theme-store'
import {Check, Palette, X} from 'lucide-vue-next'
import {Button} from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import {ref} from 'vue'

const themeStore = useThemeStore()
const open = ref(false)

const themeOptions: Array<{
  name: string
  color: ThemeColor
  hex: string
}> = [
  {name: 'Zinc', color: 'default', hex: 'oklch(0.37 0.013 285.805)'},
  {name: 'Biru', color: 'blue', hex: 'oklch(0.488 0.243 264.376)'},
  {name: 'Hijau', color: 'green', hex: 'oklch(0.596 0.145 163.225)'},
  {name: 'Mawar', color: 'rose', hex: 'oklch(0.586 0.253 17.585)'},
  {name: 'Merah', color: 'red', hex: 'oklch(0.577 0.245 27.325)'},
  {name: 'Ungu', color: 'violet', hex: 'oklch(0.541 0.281 293.009)'},
  {name: 'Kuning', color: 'yellow', hex: 'oklch(0.852 0.199 91.936)'},
  {name: 'Oranye', color: 'orange', hex: 'oklch(0.646 0.222 41.116)'},
  {name: 'Claude', color: 'claude', hex: 'oklch(0.62 0.14 39.15)'},
  {name: 'Kopi', color: 'caffeine', hex: 'oklch(0.43 0.04 42.00)'},
  {name: 'Anggur', color: 'luxury', hex: 'oklch(0.47 0.15 25.06)'},
  {name: 'Marvel', color: 'marvel', hex: 'oklch(0.55 0.22 27.03)'},
  {name: 'Aurora', color: 'aurora', hex: 'oklch(0.75 0.18 190)'},
]
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button
        variant="outline"
        class="flex items-center gap-2 rounded-xl border-border/40 bg-background/50 px-3 py-2 text-xs font-medium"
      >
        <Palette class="h-4 w-4 opacity-70"/>
        <span>Warna</span>
        <span
          class="h-2 w-2 rounded-full"
          :style="{
                        backgroundColor: themeOptions.find((o) => o.color === themeStore.color)?.hex,
                    }"
        />
      </Button>
    </DialogTrigger>

    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Palette class="h-4 w-4"/>
          Pilih Warna Aksen
        </DialogTitle>
      </DialogHeader>

      <div class="grid grid-cols-5 gap-3 py-4">
        <button
          v-for="option in themeOptions"
          :key="option.color"
          @click="themeStore.setColor(option.color); open = false"
          class="group relative flex h-12 w-12 items-center justify-center rounded-xl transition-all active:scale-90"
        >
          <div
            v-if="themeStore.color === option.color"
            class="border-primary/30 absolute -inset-0.5 rounded-xl border-2"
          />
          <div
            class="border-border/20 flex h-10 w-10 flex-col items-center justify-center rounded-lg border shadow-sm transition-all group-hover:scale-105"
            :style="{ backgroundColor: option.hex }"
          >
            <Check
              v-if="themeStore.color === option.color"
              class="h-4 w-4 text-white mix-blend-difference"
              stroke-width="3"
            />
          </div>
          <span class="text-muted-foreground mt-1 text-[9px] font-medium">
                        {{ option.name }}
                    </span>
        </button>
      </div>
    </DialogContent>
  </Dialog>
</template>