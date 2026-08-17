<script setup lang="ts">
import { computed, useAttrs } from 'vue'
import { useVModel } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { CircleAlert } from 'lucide-vue-next'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label' // 🔥 Memanggil dasar komponen Textarea Shadcn UI kawan

interface Props {
  modelValue?: string | undefined
  label?: string
  error?: string
  hint?: string
  id?: string
  containerClass?: string
  rows?: number | string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  id: () => `textarea-${Math.random().toString(36).substring(2, 9)}`,
  rows: 3 // Default tinggi kolom teks 3 baris agar tetap compact kawan
})

const emit = defineEmits(['update:modelValue', 'blur', 'change'])
const attrs = useAttrs()

// Mengandalkan inferensi otomatis TypeScript seperti pada input-control kawan
const modelValue = useVModel(props, 'modelValue', emit)
</script>

<template>
  <div :class="cn('flex w-full flex-col gap-1.5', props.containerClass)">
    <label
      v-if="label"
      :for="id"
      class="text-sm leading-none font-medium select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>

    <div class="relative flex items-start">
      <div
        v-if="$slots.prefix"
        class="pointer-events-none absolute top-2.5 left-3 flex items-center"
      >
        <slot name="prefix" />
      </div>

      <Textarea
        :id="id"
        v-model="modelValue"
        v-bind="attrs"
        :rows="rows"
        :aria-invalid="!!error"
        @blur="$emit('blur', $event)"
        @change="$emit('change', $event)"
        :class="
          cn(
            'placeholder:text-muted-foreground bg-background border-input min-h-[80px] w-full border px-3 py-2 text-xs font-medium transition-colors focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
            'focus-visible:ring-ring rounded-lg focus-visible:ring-1 focus-visible:ring-offset-0',
            $slots.prefix && 'pl-9',
            error && 'pr-10',
            error
              ? ['border-destructive', 'focus-visible:ring-destructive']
              : 'focus-visible:border-input'
          )
        "
      />

      <div v-if="error" class="pointer-events-none absolute top-2.5 right-3">
        <CircleAlert class="text-destructive animate-in fade-in h-4 w-4" />
      </div>
    </div>

    <p v-if="error" class="text-destructive animate-in fade-in text-xs italic transition-all">
      * {{ error }}
    </p>
    <p v-else-if="hint" class="text-muted-foreground animate-in fade-in text-[0.8rem]">
      {{ hint }}
    </p>
  </div>
</template>
