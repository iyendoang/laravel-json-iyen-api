<script setup lang="ts">
import {X} from 'lucide-vue-next'
import {Button} from '@/components/ui/button'
import SelectControl from '@/components/shared/input/select-control.vue'

export interface FilterOption {
  value: string
  label: string
}

const props = withDefaults(defineProps<{
  modelValue: string
  placeholder?: string
  options?: FilterOption[]
  label?: string
}>(), {
  placeholder: 'Semua',
  options: () => [],
  label: '',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const selectOptions = [
  {label: props.placeholder, value: 'all'},
  ...props.options.map((opt) => ({label: opt.label, value: opt.value})),
]
</script>

<template>
  <div class="flex items-center gap-1.5">
    <!--    <span v-if="label" class="text-muted-foreground text-[11px]">{{ label }}</span>-->
    <SelectControl
      :model-value="modelValue"
      name="filter"
      :options="selectOptions"
      container-class="w-[110px]"
      :placeholder="placeholder"
      size="sm"
      @update:model-value="(v) => emit('update:modelValue', String(v))"
    />

    <Button
      v-if="modelValue && modelValue !== 'all'"
      variant="ghost"
      size="icon-sm"
      class="text-muted-foreground h-6 w-6"
      @click="emit('update:modelValue', 'all')"
    >
      <X class="h-3 w-3"/>
    </Button>
  </div>
</template>