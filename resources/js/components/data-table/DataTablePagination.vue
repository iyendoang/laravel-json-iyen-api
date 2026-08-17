<script setup lang="ts">
import type { PaginationMeta } from '@/types/api'
import { computed } from 'vue'
import {
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  meta: PaginationMeta | null | undefined
  perPage: number | null // 🔥 Bisa null
}>()

const emit = defineEmits<{
  (e: 'changePage', page: number): void
  (e: 'changePerPage', perPage: number | null): void // 🔥 Bisa null
}>()

const totalPages = computed(() => props.meta?.last_page || 1)
const currentPage = computed(() => props.meta?.current_page || 1)
</script>

<template>
  <div class="flex items-center justify-between py-1.5">
    <p class="text-muted-foreground text-[11px]">
      {{ meta?.from || 0 }}–{{ meta?.to || 0 }} dari {{ meta?.total || 0 }} data
    </p>

    <div class="flex items-center gap-0.5">
      <Button
        variant="ghost"
        size="icon-sm"
        class="h-7 w-7"
        :disabled="currentPage <= 1"
        @click="emit('changePage', 1)"
      >
        <ChevronsLeft class="h-3 w-3" />
      </Button>
      <Button
        variant="ghost"
        size="icon-sm"
        class="h-7 w-7"
        :disabled="currentPage <= 1"
        @click="emit('changePage', currentPage - 1)"
      >
        <ChevronLeft class="h-3 w-3" />
      </Button>
      <span class="px-1.5 text-[11px] font-medium tabular-nums">
                {{ currentPage }}/{{ totalPages }}
            </span>
      <Button
        variant="ghost"
        size="icon-sm"
        class="h-7 w-7"
        :disabled="currentPage >= totalPages"
        @click="emit('changePage', currentPage + 1)"
      >
        <ChevronRight class="h-3 w-3" />
      </Button>
      <Button
        variant="ghost"
        size="icon-sm"
        class="h-7 w-7"
        :disabled="currentPage >= totalPages"
        @click="emit('changePage', totalPages)"
      >
        <ChevronsRight class="h-3 w-3" />
      </Button>
    </div>
  </div>
</template>