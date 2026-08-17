<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { SelectIcon, SelectTrigger, useForwardProps } from 'reka-ui'
import { ChevronDown } from 'lucide-vue-next'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const forwarded = useForwardProps(props)
</script>

<template>
  <SelectTrigger
    v-bind="forwarded"
    :class="
            cn(
                // 🔥 Tambahkan flex items-center justify-between gap-2
                // 🔥 Tambahkan truncate
                'border-input bg-background flex w-full items-center justify-between gap-2 rounded-md border shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
                '[&>span]:truncate [&>span]:whitespace-nowrap',
                props.class
            )
        "
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
    </SelectIcon>
  </SelectTrigger>
</template>