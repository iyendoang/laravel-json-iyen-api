<script setup lang="ts">
import {Inbox} from 'lucide-vue-next'

interface Props {
  colspan: number
  title?: string
  description?: string
  showIcon?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Tidak ada data',
  description: 'Data yang Anda cari tidak ditemukan.',
  showIcon: true,
})
</script>

<template>
  <tr>
    <td :colspan="colspan" class="py-10">
      <div class="flex flex-col items-center justify-center gap-3 text-center">
        <div v-if="showIcon" class="bg-muted/40 flex h-10 w-10 items-center justify-center rounded-full">
          <Inbox class="text-muted-foreground/70 h-4 w-4"/>
        </div>
        <div class="space-y-0.5">
          <p class="text-sm font-medium">{{ title }}</p>
          <p class="text-muted-foreground text-xs">{{ description }}</p>
        </div>
        <div v-if="$slots.default">
          <slot/>
        </div>
      </div>
    </td>
  </tr>
</template>