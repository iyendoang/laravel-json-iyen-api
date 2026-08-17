<!-- resources/js/components/shared/app-button.vue -->
<script lang="ts">
export default {
  inheritAttrs: false
}
</script>

<script setup lang="ts">
import { computed, useAttrs } from 'vue'
import { Loader2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button' // Base Shadcn Button
import { cn } from '@/lib/utils'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

interface Props {
  variant?:
    | 'default'
    | 'destructive'
    | 'outline'
    | 'secondary'
    | 'ghost'
    | 'link'
    | 'success'
    | 'warning'
  size?: 'default' | 'sm' | 'lg' | 'icon' | 'icon-sm' | 'xl'
  loading?: boolean
  loadingText?: string
  disabled?: boolean
  leftIcon?: any
  rightIcon?: any
  iconOnly?: any
  title?: string
  tooltip?: string
  class?: any
  type?: 'button' | 'submit' | 'reset'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'default',
  loading: false,
  loadingText: 'Menyimpan...',
  disabled: false,
  type: 'button'
})

const attrs = useAttrs()

// Memisahkan class dan event agar tidak duplikasi dengan v-bind
const filteredAttrs = computed(() => {
  const { class: _, ...rest } = attrs
  return rest
})

// Logika styling tambahan di luar default Shadcn
const extendedClasses = computed(() => {
  return cn(
    'relative transition-all active:scale-[0.98]',
    props.title && 'h-auto py-2',
    // Custom Success & Warning (Karena Shadcn default tidak punya ini)
    props.variant === 'success' &&
      'bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-500',
    props.variant === 'warning' && 'bg-amber-500 text-white hover:bg-amber-600 dark:bg-amber-600',
    props.size === 'xl' && 'h-12 px-8 text-base gap-3',
    props.size === 'icon-sm' && 'h-8 w-8 p-0',
    props.class
  )
})
</script>

<template>
  <TooltipProvider :delay-duration="200">
    <Tooltip :delay-duration="0">
      <TooltipTrigger as-child>
        <!-- Gunakan Button bawaan Shadcn langsung sebagai root -->
        <Button
          v-bind="filteredAttrs"
          :type="type"
          :variant="variant === 'success' || variant === 'warning' ? 'default' : variant"
          :size="size as any"
          :disabled="disabled || loading"
          :class="extendedClasses"
        >
          <!-- 1. State: Loading -->
          <template v-if="loading">
            <Loader2 class="mr-2 h-4 w-4 shrink-0 animate-spin" />
            <span class="truncate">{{ loadingText }}</span>
          </template>

          <!-- 2. State: Normal -->
          <template v-else>
            <!-- Icon Only Mode -->
            <component :is="iconOnly" v-if="iconOnly" class="h-4 w-4" />

            <!-- Text & Icons Mode -->
            <template v-else>
              <component :is="leftIcon" v-if="leftIcon" class="mr-2 h-4 w-4 shrink-0" />

              <div
                v-if="$slots.default || title"
                class="flex flex-col items-start text-left leading-tight"
              >
                <span class="truncate text-sm leading-none font-semibold">
                  <slot />
                </span>
                <span
                  v-if="title"
                  class="mt-1 text-[10px] font-normal tracking-wide uppercase opacity-70"
                >
                  {{ title }}
                </span>
              </div>

              <component :is="rightIcon" v-if="rightIcon" class="ml-2 h-4 w-4 shrink-0" />
            </template>
          </template>
        </Button>
      </TooltipTrigger>

      <TooltipContent v-if="tooltip" side="top" :side-offset="8">
        <p class="text-xs">{{ tooltip }}</p>
      </TooltipContent>
    </Tooltip>
  </TooltipProvider>
</template>
