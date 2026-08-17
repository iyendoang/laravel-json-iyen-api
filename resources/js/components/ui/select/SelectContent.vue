<script setup lang="ts">
import type {HTMLAttributes} from 'vue'
import {cn} from '@/lib/utils'
import {SelectContent, SelectPortal, SelectViewport} from 'reka-ui'

const props = defineProps<{
  class?: HTMLAttributes['class']
  position?: 'popper' | 'item-aligned'
}>()

const forwarded = {position: props.position}
</script>

<template>
  <SelectPortal>
    <SelectContent
      v-bind="forwarded"
      :class="
                cn(
                    'bg-popover text-popover-foreground relative z-50 min-w-32 overflow-hidden rounded-md border shadow-md',
                    props.position === 'popper' && 'data-[side=bottom]:translate-y-1 data-[side=top]:-translate-y-1',
                    props.class
                )
            "
    >
      <SelectViewport
        :class="
                    cn(
                        'p-1',
                        props.position === 'popper' &&
                            'h-[var(--reka-select-trigger-height)] w-full min-w-[var(--reka-select-trigger-width)]'
                    )
                "
      >
        <slot/>
      </SelectViewport>
    </SelectContent>
  </SelectPortal>
</template>