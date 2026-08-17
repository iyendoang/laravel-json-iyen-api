<script setup lang="ts">
import type {HTMLAttributes} from "vue"
import {cn} from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
  isActive?: boolean
}>()
</script>

<template>
  <li
    data-slot="pagination-item"
    :class="
            cn(
                'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
                'h-9 w-9 cursor-pointer',
                props.isActive
                    ? 'bg-primary text-primary-foreground shadow-sm hover:bg-primary/90'
                    : 'hover:bg-muted text-foreground',
                props.class
            )
        "
  >
    <slot/>
  </li>
</template>