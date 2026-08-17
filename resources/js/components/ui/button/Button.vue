<script setup lang="ts">
import type {HTMLAttributes} from 'vue'
import {cn} from '@/lib/utils'

const props = defineProps<{
  variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost' | 'link'
  size?: 'default' | 'sm' | 'lg' | 'icon' | 'icon-sm'
  class?: HTMLAttributes['class']
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()
</script>

<template>
  <button
    :type="props.type || 'button'"
    :disabled="props.disabled"
    :class="
            cn(
                'inline-flex items-center justify-center rounded-md font-medium transition-all focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 active:scale-[0.98]',
                {
                    'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm': props.variant === 'default' || !props.variant,
                    'bg-secondary text-secondary-foreground hover:bg-secondary/80': props.variant === 'secondary',
                    'bg-destructive text-destructive-foreground hover:bg-destructive/90': props.variant === 'destructive',
                    'border border-input bg-transparent hover:bg-muted': props.variant === 'outline',
                    'hover:bg-muted': props.variant === 'ghost',
                    'text-primary underline-offset-4 hover:underline': props.variant === 'link',
                },
                {
                    'h-9 px-4 py-2 text-sm': props.size === 'default' || !props.size,
                    'h-8 px-3 text-xs': props.size === 'sm',
                    'h-10 px-6 text-base': props.size === 'lg',
                    'h-9 w-9 p-0': props.size === 'icon',
                    'h-7 w-7 p-0': props.size === 'icon-sm',
                },
                props.class
            )
        "
    @click="(event) => emit('click', event)"
  >
    <slot/>
  </button>
</template>