<script setup lang="ts">
import type {HTMLAttributes} from "vue"
import {ChevronRight} from "lucide-vue-next"
import {cn} from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()
</script>

<template>
  <a
    data-slot="pagination-next"
    :class="
            cn(
                'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                'h-9 w-9 cursor-pointer hover:bg-muted text-foreground',
                props.disabled ? 'pointer-events-none opacity-50' : '',
                props.class
            )
        "
    @click="(event) => emit('click', event)"
  >
    <slot>
      <ChevronRight class="h-4 w-4"/>
      <span class="sr-only">Next</span>
    </slot>
  </a>
</template>