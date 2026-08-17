<script setup lang="ts">
import { Inbox } from "lucide-vue-next"

interface Props {
  colspan: number
  title?: string
  description?: string
  showIcon?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: "Tidak ada data",
  description: "Data yang Anda cari tidak ditemukan.",
  showIcon: true,
})
</script>

<template>
  <tr>
    <td :colspan="colspan" class="py-14">
      <div class="flex flex-col items-center justify-center text-center space-y-4">

        <!-- Icon -->
        <div
            v-if="showIcon"
            class="flex h-12 w-12 items-center justify-center rounded-full bg-muted/40"
        >
          <Inbox class="h-5 w-5 text-muted-foreground/70" />
        </div>

        <!-- Text -->
        <div class="space-y-1">
          <p class="text-sm font-semibold text-foreground">
            {{ title }}
          </p>

          <p class="text-sm text-muted-foreground max-w-sm">
            {{ description }}
          </p>
        </div>

        <!-- Optional Action Slot -->
        <div v-if="$slots.default" class="pt-2">
          <slot />
        </div>

      </div>
    </td>
  </tr>
</template>