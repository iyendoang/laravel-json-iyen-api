<script setup lang="ts">
import {useAttrs, computed, useSlots} from 'vue'
import {useVModel} from '@vueuse/core'
import {cn} from '@/lib/utils'
import {CircleAlert} from 'lucide-vue-next'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface Option {
  label: string
  value: string | number | boolean | null
}

interface Props {
  modelValue?: string | number | boolean | null
  name: string
  label?: string
  error?: string
  hint?: string
  placeholder?: string
  options: Option[]
  containerClass?: string
  disabled?: boolean
  size?: 'sm' | 'md'
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Pilih opsi...',
  size: 'md',
})

const emit = defineEmits(['update:modelValue'])
const attrs = useAttrs()
const slots = useSlots()

const modelValue = useVModel(props, 'modelValue', emit)

const proxyValue = computed({
  get: () =>
    modelValue.value === null || modelValue.value === undefined
      ? undefined
      : String(modelValue.value),
  set: (val) => {
    if (val === 'null') {
      modelValue.value = null
      return
    }
    const found = props.options.find((opt) => String(opt.value) === val)
    modelValue.value = found ? found.value : val
  },
})
</script>

<template>
  <div :class="cn('flex w-full flex-col gap-1.5', props.containerClass)">
    <label
      v-if="label"
      class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>

    <div class="group relative">
      <div
        v-if="slots.prefix"
        class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center"
      >
        <slot name="prefix"/>
      </div>

      <Select v-model="proxyValue" :disabled="disabled" v-bind="attrs">
        <SelectTrigger
          :class="
                        cn(
                            'bg-background w-full border py-1 transition-colors',
                            'focus:ring-ring focus:ring-1 focus:ring-offset-0 focus:outline-none',
                            // 🔥 Tambahkan flex items-center + truncate + whitespace-nowrap
                            'flex items-center justify-between gap-2',
                            props.size === 'sm'
                                ? 'h-7 text-[11px] px-2'
                                : 'h-9 text-[13px] px-3',
                            slots.prefix ? 'pl-9' : '',
                            error
                                ? 'border-destructive focus:ring-destructive pr-10'
                                : 'border-input'
                        )
                    "
        >
          <SelectValue :placeholder="placeholder"/>
        </SelectTrigger>

        <SelectContent>
          <SelectItem
            v-for="option in options"
            :key="String(option.value)"
            :value="option.value === null ? 'null' : String(option.value)"
            :class="props.size === 'sm' ? 'h-7 text-[11px]' : ''"
          >
            {{ option.label }}
          </SelectItem>
        </SelectContent>
      </Select>

      <div v-if="error" class="pointer-events-none absolute inset-y-0 right-8 flex items-center">
        <CircleAlert class="text-destructive h-4 w-4"/>
      </div>
    </div>

    <p v-if="error" class="text-destructive text-[11px] font-medium italic">* {{ error }}</p>
    <p v-else-if="hint" class="text-muted-foreground mt-1 text-[0.8rem] leading-tight">
      {{ hint }}
    </p>
  </div>
</template>