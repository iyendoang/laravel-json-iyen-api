<script setup lang="ts">
import type {PaginationMeta} from '@/types/api'
import {computed} from 'vue'

import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination'

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const props = defineProps<{
  meta: PaginationMeta
  perPage: number
}>()

const emit = defineEmits<{
  (e: 'changePage', page: number): void
  (e: 'changePerPage', value: number): void
}>()

const isFirstPage = computed(() => props.meta.current_page <= 1)
const isLastPage = computed(() => props.meta.current_page >= props.meta.last_page)

const pageNumbers = computed(() => {
  const total = props.meta.last_page
  const current = props.meta.current_page
  const pages: (number | string)[] = []

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
      pages.push(i)
    }
    if (current < total - 2) pages.push('...')
    pages.push(total)
  }

  return pages
})

const perPageOptions = [10, 15, 25, 50, 100, 300, 500, 800, 1000]
</script>

<template>
  <div class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="text-muted-foreground text-xs">
      Menampilkan
      <span class="text-foreground font-medium">{{ meta.from ?? 0 }}</span>
      –
      <span class="text-foreground font-medium">{{ meta.to ?? 0 }}</span>
      dari
      <span class="text-foreground font-medium">{{ meta.total }}</span>
      data
    </div>

    <div class="flex items-center justify-between gap-3 sm:justify-end">
      <Pagination v-if="meta.last_page > 1" class="justify-end">
        <PaginationContent>
          <PaginationItem>
            <PaginationPrevious
              class="h-8 w-8 cursor-pointer"
              :class="isFirstPage ? 'pointer-events-none opacity-50' : ''"
              @click.prevent="!isFirstPage && emit('changePage', meta.current_page - 1)"
            />
          </PaginationItem>

          <template v-for="(page, index) in pageNumbers" :key="index">
            <PaginationItem v-if="page === '...'">
              <PaginationEllipsis class="h-8 w-8"/>
            </PaginationItem>

            <PaginationItem
              v-else
              :is-active="meta.current_page === page"
              class="h-8 w-8 cursor-pointer text-xs"
              @click="emit('changePage', Number(page))"
            >
              {{ page }}
            </PaginationItem>
          </template>

          <PaginationItem>
            <PaginationNext
              class="h-8 w-8 cursor-pointer"
              :class="isLastPage ? 'pointer-events-none opacity-50' : ''"
              @click.prevent="!isLastPage && emit('changePage', meta.current_page + 1)"
            />
          </PaginationItem>
        </PaginationContent>
      </Pagination>

      <div class="flex items-center gap-2">
        <span class="text-muted-foreground hidden text-xs md:inline">Baris</span>
        <Select
          :model-value="String(perPage)"
          @update:model-value="(val) => emit('changePerPage', Number(val))"
        >
          <SelectTrigger class="h-8 w-[70px] text-xs">
            <SelectValue/>
          </SelectTrigger>
          <SelectContent align="end">
            <SelectItem
              v-for="option in perPageOptions"
              :key="option"
              :value="String(option)"
              class="text-xs"
            >
              {{ option }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>
  </div>
</template>