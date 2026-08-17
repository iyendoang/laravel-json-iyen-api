<script setup lang="ts">
import { computed, type Component } from 'vue'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

// Definisi Variant menggunakan CVA
const badgeVariants = cva(
  'inline-flex items-center justify-center rounded-full font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
  {
    variants: {
      variant: {
        default: 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
        secondary:
          'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
        destructive:
          'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80',
        outline: 'text-foreground border-border border',
        success:
          'border-transparent bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20',
        warning:
          'border-transparent bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20',
        info: 'border-transparent bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-500/20'
      },
      size: {
        xs: 'px-1.5 py-0.5 text-[10px] gap-1',
        sm: 'px-2 py-0.5 text-xs gap-1.5',
        md: 'px-2.5 py-1 text-sm gap-2',
        lg: 'px-3 py-1.5 text-base gap-2'
      }
    },
    defaultVariants: {
      variant: 'default',
      size: 'sm'
    }
  }
)

interface Props {
  variant?: VariantProps<typeof badgeVariants>['variant']
  size?: VariantProps<typeof badgeVariants>['size']
  class?: string
  icon?: Component // Mendukung icon dari lucide-vue-next
  iconPosition?: 'left' | 'right'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'sm',
  iconPosition: 'left'
})

// Menentukan ukuran icon secara otomatis berdasarkan ukuran badge
const iconSizeClass = computed(() => {
  switch (props.size) {
    case 'xs':
      return 'h-2.5 w-2.5'
    case 'sm':
      return 'h-3 w-3'
    case 'md':
      return 'h-4 w-4'
    case 'lg':
      return 'h-5 w-5'
    default:
      return 'h-3 w-3'
  }
})
</script>

<template>
  <div :class="cn(badgeVariants({ variant, size }), props.class)">
    <component
      :is="icon"
      v-if="icon && iconPosition === 'left'"
      :class="cn('shrink-0', iconSizeClass)"
    />

    <slot />

    <component
      :is="icon"
      v-if="icon && iconPosition === 'right'"
      :class="cn('shrink-0', iconSizeClass)"
    />
  </div>
</template>
