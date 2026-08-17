<!--resources/js/components/shared/input/input-control.vue-->
<script setup lang="ts">
import { ref, computed, useAttrs } from 'vue'
import { useVModel } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { Eye, EyeOff, CircleAlert, Calendar } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { vMaska } from 'maska/vue'

interface Props {
  // Kita pastikan tipe data prop hanya menerima string, number, atau undefined
  modelValue?: string | number | undefined
  label?: string
  error?: string
  hint?: string
  type?: string
  id?: string
  containerClass?: string
  mask?: string | object
  isDateMask?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  type: 'text',
  id: () => `input-${Math.random().toString(36).substring(2, 9)}`,
  isDateMask: false
})

/**
 * PERBAIKAN 1: Tambahkan 'blur' dan 'update:modelValue' ke emits
 */
const emit = defineEmits(['update:modelValue', 'blur', 'change'])
const attrs = useAttrs()

/**
 * 🔥 PERBAIKAN 2: BIARKAN TS MENGINFERENSI OTOMATIS
 * Kita hapus penulisan manual generik <string | number | undefined>
 * TypeScript secara otomatis tahu tipe datanya berdasarkan definisi interface Props di atas.
 */
const modelValue = useVModel(props, 'modelValue', emit)
const isPasswordVisible = ref(false)

const inputType = computed(() => {
  if (props.type === 'password') {
    return isPasswordVisible.value ? 'text' : 'password'
  }
  return props.type
})

const togglePassword = () => {
  isPasswordVisible.value = !isPasswordVisible.value
}

const activeMask = computed(() => {
  if (props.isDateMask) return '##/##/####'
  return props.mask
})
</script>

<template>
  <div :class="cn('flex w-full flex-col gap-1.5', props.containerClass)">
    <label
      v-if="label"
      :for="id"
      class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>

    <div class="relative flex items-center">
      <div
        v-if="$slots.prefix || isDateMask"
        class="pointer-events-none absolute left-3 flex items-center"
      >
        <Calendar v-if="isDateMask" class="h-4 w-4" />
        <slot name="prefix" />
      </div>

      <Input
        :id="id"
        v-model="modelValue"
        v-maska="activeMask"
        v-bind="attrs"
        :type="inputType"
        :placeholder="isDateMask ? 'DD/MM/YYYY' : (attrs.placeholder as string)"
        :aria-invalid="!!error"
        @blur="$emit('blur', $event)"
        @change="$emit('change', $event)"
        :class="
          cn(
            'placeholder:text-muted-foreground h-9 px-3 py-1 transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
            'focus-visible:ring-ring focus-visible:ring-1 focus-visible:ring-offset-0',
            ($slots.prefix || isDateMask) && 'pl-9',
            (type === 'password' || error) && 'pr-10',
            error
              ? ['border-destructive', 'focus-visible:ring-destructive']
              : 'focus-visible:border-input'
          )
        "
      />

      <button
        v-if="type === 'password'"
        type="button"
        @click="togglePassword"
        class="text-muted-foreground hover:text-foreground absolute right-3 flex items-center justify-center transition-colors focus:outline-none"
      >
        <component :is="isPasswordVisible ? EyeOff : Eye" class="h-4 w-4" />
      </button>

      <div v-if="error && type !== 'password'" class="pointer-events-none absolute right-3">
        <CircleAlert class="text-destructive h-4 w-4" />
      </div>
    </div>

    <p v-if="error" class="text-destructive text-xs italic transition-all">* {{ error }}</p>
    <p v-else-if="hint" class="text-muted-foreground text-[0.8rem]">
      {{ hint }}
    </p>
  </div>
</template>
