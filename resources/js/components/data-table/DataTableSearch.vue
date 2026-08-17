<script setup lang="ts">
import { Search, X } from 'lucide-vue-next'

const props = withDefaults(defineProps<{
  modelValue: string
  placeholder?: string
}>(), {
  placeholder: 'Cari...',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()
</script>

<template>
  <div class="relative w-full sm:w-48">
    <Search class="text-muted-foreground/50 pointer-events-none absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2" />
    <input
      :value="modelValue"
      :placeholder="placeholder"
      class="border-border/50 bg-muted/20 focus:border-primary/40 focus:bg-background h-7 w-full rounded-md border pr-7 pl-8 text-[11px] transition-all focus:outline-none focus:ring-1 focus:ring-ring"
      @input="(e) => emit('update:modelValue', (e.target as HTMLInputElement).value)"
    />
    <button
      v-if="modelValue"
      class="text-muted-foreground/50 hover:text-foreground absolute top-1/2 right-1.5 flex h-5 w-5 -translate-y-1/2 items-center justify-center rounded transition-colors"
      @click="emit('update:modelValue', '')"
    >
      <X class="h-3 w-3" />
    </button>
  </div>
</template>