<script setup lang="ts">
import SelectControl from '@/components/shared/input/select-control.vue'

const props = withDefaults(defineProps<{
  modelValue: number | null
  options?: number[]
  showAll?: boolean
  allLabel?: string
}>(), {
  options: () => [5, 10, 15, 25, 50, 100],
  showAll: true,
  allLabel: 'Semua',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: number | null): void
}>()

const selectOptions = [
  ...(props.showAll ? [{label: props.allLabel, value: null as null}] : []),
  ...props.options.map((opt) => ({
    label: String(opt),
    value: opt,
  })),
]
</script>

<template>
  <div class="flex items-center gap-1.5">
    <span class="text-muted-foreground text-[11px]">Tampilkan</span>
    <SelectControl
      :model-value="modelValue"
      name="per_page"
      :options="selectOptions"
      container-class="w-[75px]"
      :placeholder="modelValue === null ? allLabel : String(modelValue)"
      size="sm"
      @update:model-value="(v) => emit('update:modelValue', v === null ? null : Number(v))"
    />
    <span class="text-muted-foreground text-[11px] hidden sm:inline">
            {{ modelValue === null ? 'semua' : 'data' }}
        </span>
  </div>
</template>