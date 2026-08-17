<!--resources/js/components/shared/input/combobox-control.vue-->
<script setup lang="ts">
import { ref, computed } from 'vue'
import { Check, ChevronsUpDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator // Tambahkan separator agar lebih rapi
} from '@/components/ui/command'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Label } from '@/components/ui/label'

interface Option {
  label: string
  value: string | number
}

interface Props {
  modelValue: string | number | (string | number)[] | null
  options: Option[]
  label: string
  placeholder?: string
  searchPlaceholder?: string
  emptyMessage?: string
  error?: string
  disabled?: boolean
  multiple?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Pilih opsi...',
  searchPlaceholder: 'Cari...',
  emptyMessage: 'Data tidak ditemukan.',
  multiple: false
})

const emit = defineEmits(['update:modelValue'])
const open = ref(false)

const getArrayValue = (): (string | number)[] => {
  return Array.isArray(props.modelValue) ? props.modelValue : []
}

// Cek apakah semua opsi sudah terpilih
const isAllSelected = computed(() => {
  return props.options.length > 0 && getArrayValue().length === props.options.length
})

const isSelected = (value: string | number) => {
  if (props.multiple) return getArrayValue().includes(value)
  return props.modelValue === value
}

// Handler untuk Pilih Semua
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    // Jika sudah terpilih semua, maka kosongkan (Deselect All)
    emit('update:modelValue', [])
  } else {
    // Jika belum, masukkan semua value dari options
    const allValues = props.options.map((opt) => opt.value)
    emit('update:modelValue', allValues)
  }
}

const handleSelect = (val: string | number) => {
  if (props.multiple) {
    const currentValues = [...getArrayValue()]
    const index = currentValues.indexOf(val)
    if (index > -1) currentValues.splice(index, 1)
    else currentValues.push(val)
    emit('update:modelValue', currentValues)
  } else {
    emit('update:modelValue', val)
    open.value = false
  }
}

const selectedLabels = computed(() => {
  if (props.multiple) {
    const values = getArrayValue()
    return props.options.filter((opt) => values.includes(opt.value))
  }
  const single = props.options.find((opt) => opt.value === props.modelValue)
  return single ? [single] : []
})
</script>

<template>
  <div class="flex flex-col gap-2">
    <label
      v-if="label"
      class="text-sm leading-none font-medium select-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      {{ label }}
    </label>
    <Popover v-model:open="open">
      <PopoverTrigger as-child>
        <Button
          variant="outline"
          role="combobox"
          :disabled="disabled"
          :class="
            cn(
              'h-auto min-h-10 w-full justify-between py-2 font-normal',
              selectedLabels.length === 0 && 'text-muted-foreground',
              error && 'border-destructive ring-destructive/20'
            )
          "
        >
          <div class="flex items-center gap-2 overflow-hidden text-left">
            <div v-if="$slots.prefix" class="shrink-0">
              <slot name="prefix" />
            </div>
            <div class="flex flex-wrap items-center gap-1 overflow-hidden text-left">
              <template v-if="selectedLabels.length > 0">
                <template v-if="multiple">
                  <Badge
                    v-for="item in selectedLabels"
                    :key="item.value"
                    variant="secondary"
                    class="rounded-sm px-1 font-normal"
                  >
                    {{ item.label }}
                  </Badge>
                </template>
                <span v-else class="truncate">{{ selectedLabels[0].label }}</span>
              </template>
              <span v-else>{{ placeholder }}</span>
            </div>
          </div>
          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>

      <PopoverContent class="w-[--radix-popover-trigger-width] p-0" align="start">
        <Command>
          <CommandInput :placeholder="searchPlaceholder" />
          <CommandList>
            <CommandEmpty>{{ emptyMessage }}</CommandEmpty>

            <CommandGroup v-if="multiple && options.length > 0">
              <CommandItem value="all-items" @select="toggleSelectAll" class="font-bold">
                <div
                  :class="
                    cn(
                      'border-primary mr-2 flex h-4 w-4 items-center justify-center rounded-sm border',
                      isAllSelected ? 'bg-primary text-primary-foreground' : 'opacity-50'
                    )
                  "
                >
                  <Check v-if="isAllSelected" class="h-3 w-3" />
                </div>
                {{ isAllSelected ? 'Batalkan Semua' : 'Pilih Semua' }}
              </CommandItem>
            </CommandGroup>

            <CommandSeparator v-if="multiple && options.length > 0" />

            <CommandGroup>
              <CommandItem
                v-for="option in options"
                :key="option.value"
                :value="String(option.label)"
                @select="handleSelect(option.value)"
              >
                <div
                  :class="
                    cn(
                      'border-primary mr-2 flex h-4 w-4 items-center justify-center rounded-sm border',
                      isSelected(option.value) ? 'bg-primary text-primary-foreground' : 'opacity-50'
                    )
                  "
                >
                  <Check v-if="isSelected(option.value)" class="h-3 w-3" />
                </div>
                {{ option.label }}
              </CommandItem>
            </CommandGroup>
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>

    <p v-if="error" class="text-destructive animate-in fade-in text-[0.8rem] font-medium">
      {{ error }}
    </p>
  </div>
</template>
