<script setup lang="ts">
import { computed, ref } from 'vue'
import { Calendar as CalendarIcon, X } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Label } from '@/components/ui/label'

interface Props {
  modelValue: any
  mode?: 'date' | 'datetime' | 'range' | 'range-datetime'
  label?: string
  error?: string
  placeholder?: string
  disabled?: boolean
  containerClass?: string // Disamakan dengan InputControl
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'date',
  placeholder: 'Pilih tanggal'
})

const emit = defineEmits(['update:modelValue'])
const isPopoverOpen = ref(false)

const date = computed({
  get: () => props.modelValue,
  set: (val) => {
    emit('update:modelValue', val)
    // Otomatis tutup jika pilih tanggal tunggal
    if (!props.mode.includes('range') && !props.mode.includes('datetime')) {
      isPopoverOpen.value = false
    }
  }
})

const calendarMode = computed(() => (props.mode.includes('datetime') ? 'datetime' : 'date'))
const isRange = computed(() => props.mode.includes('range'))

// Format tampilan pada button
const formattedDate = computed(() => {
  if (!props.modelValue) return props.placeholder

  const options: Intl.DateTimeFormatOptions = props.mode.includes('datetime')
    ? { dateStyle: 'medium', timeStyle: 'short' }
    : { dateStyle: 'medium' }

  if (isRange.value) {
    if (!props.modelValue.start) return props.placeholder
    const start = new Date(props.modelValue.start).toLocaleDateString('id-ID')
    const end = props.modelValue.end
      ? new Date(props.modelValue.end).toLocaleDateString('id-ID')
      : '...'
    return `${start} — ${end}`
  }

  return new Date(props.modelValue).toLocaleDateString('id-ID', options)
})
</script>

<template>
  <div :class="cn('flex w-full flex-col gap-1.5', props.containerClass)">
    <label
      v-if="label"
      class="text-sm leading-none font-medium select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>

    <Popover v-model:open="isPopoverOpen">
      <PopoverTrigger as-child>
        <div class="relative w-full">
          <Button
            variant="outline"
            type="button"
            :disabled="disabled"
            :class="
              cn(
                'h-9 w-full justify-start px-3 text-left font-normal transition-all',
                'bg-background placeholder:text-muted-foreground text-[13px]',
                // GAYA FOKUS NEW YORK: Ring tipis 1px, offset 0
                'focus-visible:ring-ring focus-visible:ring-1 focus-visible:ring-offset-0 focus-visible:outline-none',
                !modelValue && 'text-muted-foreground',
                error
                  ? [
                      'border-destructive',
                      'focus-visible:ring-destructive',
                      'focus-visible:border-destructive'
                    ]
                  : 'border-input focus-visible:border-input'
              )
            "
          >
            <CalendarIcon class="mr-2 h-4 w-4 opacity-70" />
            <span class="truncate font-medium">
              {{ formattedDate }}
            </span>
          </Button>

          <button
            v-if="modelValue && !disabled"
            type="button"
            @click.stop="date = isRange ? { start: null, end: null } : null"
            class="text-muted-foreground hover:text-destructive absolute top-1/2 right-2.5 -translate-y-1/2 rounded-full p-0.5 transition-colors focus:outline-none"
          >
            <X class="h-3.5 w-3.5" />
          </button>
        </div>
      </PopoverTrigger>

      <PopoverContent class="w-auto border-none p-0 shadow-2xl" align="start" :side-offset="6">
        <VDatePicker
          v-model="date"
          :mode="calendarMode"
          :is-range="isRange"
          :is24hr="true"
          :locale="'id'"
          color="indigo"
          borderless
          transparent
          class="font-sans"
        />
      </PopoverContent>
    </Popover>

    <p v-if="error" class="text-destructive text-xs italic transition-all">* {{ error }}</p>
  </div>
</template>
