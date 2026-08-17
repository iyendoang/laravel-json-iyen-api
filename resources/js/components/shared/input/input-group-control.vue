<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// 1. Matikan inheritAttrs agar class h-8 tidak nempel di DIV terluar
defineOptions({
  inheritAttrs: false
})

interface Props {
  modelValue: string | number
  label?: string
  placeholder?: string
  type?: string
  error?: string
  hint?: string
  disabled?: boolean
  containerClass?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  placeholder: 'Ketik sesuatu...'
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'blur'): void
}>()

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.containerClass)">
    <label
      v-if="label"
      :for="label"
      class="text-sm leading-none font-medium select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>
    <div class="relative flex w-full items-center">
      <div
        v-if="$slots.start"
        class="text-muted-foreground pointer-events-none absolute inset-y-0 start-0 flex items-center justify-center px-3"
      >
        <slot name="start" />
      </div>

      <Input
        v-bind="$attrs"
        :id="label"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="handleInput"
        @blur="$emit('blur')"
        :class="
          cn(
            'transition-all',
            $slots.start && 'ps-10',
            $slots.end && 'pe-10',
            error && 'border-destructive ring-destructive/20 focus-visible:ring-destructive'
          )
        "
      />

      <div
        v-if="$slots.end"
        class="text-muted-foreground pointer-events-none absolute inset-y-0 end-0 flex items-center justify-center px-3"
      >
        <slot name="end" />
      </div>
    </div>
  </div>
</template>
