<!--/opt/homebrew/var/www/laravel/laravel-services-structure/resources/js/components/shared/input/radio-control.vue-->
<script setup lang="ts">
import { useAttrs } from 'vue'
import { useVModel } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Label } from '@/components/ui/label'

interface Option {
  label: string
  value: string | number
}

interface Props {
  modelValue?: string | number
  label?: string
  error?: string
  hint?: string
  options: Option[]
  containerClass?: string
  disabled?: boolean
  orientation?: 'vertical' | 'horizontal'
}

const props = withDefaults(defineProps<Props>(), {
  orientation: 'horizontal'
})

const emit = defineEmits(['update:modelValue'])
const attrs = useAttrs()

const modelValue = useVModel(props, 'modelValue', emit)
</script>

<template>
  <div :class="cn('flex w-full flex-col gap-2', props.containerClass)">
    <label
      v-if="label"
      class="text-sm leading-none font-medium select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>

    <RadioGroup
      v-model="modelValue"
      :disabled="disabled"
      v-bind="attrs"
      :class="
        cn('flex py-1', orientation === 'vertical' ? 'flex-col gap-3' : 'flex-row flex-wrap gap-6')
      "
    >
      <div
        v-for="option in options"
        :key="option.value"
        class="group flex items-center space-x-2.5"
      >
        <RadioGroupItem
          :id="String(option.value)"
          :value="String(option.value)"
          :class="
            cn(
              'transition-all duration-200',
              error && 'border-destructive focus:ring-destructive focus-visible:ring-destructive/30'
            )
          "
        />
        <Label
          :for="String(option.value)"
          class="cursor-pointer text-sm font-normal"
        >
          {{ option.label }}
        </Label>
      </div>
    </RadioGroup>

    <p v-if="error" class="text-destructive mt-0.5 text-[0.8rem] leading-tight font-medium">
      {{ error }}
    </p>

    <p v-else-if="hint" class="text-muted-foreground mt-0.5 text-[0.8rem] leading-tight">
      {{ hint }}
    </p>
  </div>
</template>
