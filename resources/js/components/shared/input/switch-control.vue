<!--resources/js/components/shared/input/switch-control.vue-->
<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'

interface Props {
  modelValue: boolean
  label: string
  error?: string
  hint?: string
  containerClass?: string
  disabled?: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

/**
 * Berdasarkan API Reka-UI:
 * Kita menggunakan model-value untuk sinkronisasi state.
 */
const handleUpdate = (value: boolean) => {
  console.log(`[Reka-UI Switch] "${props.label}" changed to:`, value)
  emit('update:modelValue', value)
}
</script>

<template>
  <div :class="cn('flex flex-col gap-2', props.containerClass)">
    <div
      class="flex items-center justify-between rounded-lg border p-4 shadow-sm transition-colors hover:bg-slate-50/50"
    >
      <div class="space-y-0.5">
        <Label :for="label" class="cursor-pointer text-sm font-semibold text-slate-700">
          {{ label }}
        </Label>

        <p v-if="hint && !error" class="text-[0.8rem] leading-snug text-slate-500">
          {{ hint }}
        </p>

        <p v-if="error" class="text-destructive animate-in fade-in text-[0.8rem] font-medium">
          {{ error }}
        </p>
      </div>

      <Switch
        :id="label"
        :disabled="disabled"
        :model-value="props.modelValue"
        @update:model-value="handleUpdate"
      />
    </div>
  </div>
</template>
