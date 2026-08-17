<!--resources/js/components/shared/input/checkbox-control.vue-->
<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'

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
 * PERBAIKAN:
 * Reka UI mengirimkan boolean | "indeterminate".
 * Kita terima tipenya secara luas agar TS tidak error,
 * lalu kita pastikan nilainya menjadi boolean saat dikirim ke Parent.
 */
const handleUpdate = (value: boolean | 'indeterminate') => {
  // Jika indeterminate, kita anggap false atau sesuai logika bisnis Anda
  const newValue = value === 'indeterminate' ? false : value
  // console.log(`[Reka-UI Sync] "${props.label}" changed to:`, newValue)
  emit('update:modelValue', newValue)
}
</script>
<template>
  <div :class="cn('flex flex-col gap-1.5', props.containerClass)">
    <div class="flex items-center space-x-3">
      <Checkbox
        :id="label"
        :disabled="disabled"
        :model-value="props.modelValue"
        @update:model-value="handleUpdate"
        :class="
          cn(
            'focus-visible:ring-ring transition-all focus-visible:ring-1 focus-visible:ring-offset-0',
            error && 'border-destructive ring-destructive/20'
          )
        "
      />

      <div class="flex flex-col justify-center leading-none">
        <Label
          :for="label"
          class="cursor-pointer text-[13px] font-medium text-slate-700 hover:text-slate-900"
        >
          {{ label }}
        </Label>
        <p v-if="hint && !error" class="mt-1 text-[11px] leading-tight text-slate-500">
          {{ hint }}
        </p>
      </div>
    </div>

    <p v-if="error" class="text-destructive text-[11px] font-bold italic">* {{ error }}</p>
  </div>
</template>
