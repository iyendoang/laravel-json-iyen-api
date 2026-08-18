<script setup lang="ts">
import {
  Table,
  TableHeader,
  TableHead,
  TableRow,
  TableBody,
  TableCell,
} from '@/components/ui/table'

interface ColumnConfig {
  label: string
  type: 'avatar-text' | 'text' | 'badge' | 'actions' | 'index'
  width?: string
}

interface Props {
  rows?: number
  columns: ColumnConfig[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  rows: 5,
  loading: true,
})
</script>

<template>
  <template v-if="loading">
    <TableRow v-for="row in rows" :key="row" class="h-10 border-b">
      <TableCell
        v-for="(column, index) in columns"
        :key="index"
        class="px-3 py-2"
      >
        <!-- Avatar + Text -->
        <template v-if="column.type === 'avatar-text'">
          <div class="flex items-center gap-2.5">
            <!-- Avatar -->
            <div class="h-7 w-7 shrink-0 rounded-full skeleton-shimmer"></div>
            <!-- Text -->
            <div class="space-y-1.5">
              <div class="h-2.5 w-[100px] rounded skeleton-shimmer"></div>
              <div class="h-2 w-[70px] rounded skeleton-shimmer opacity-60"></div>
            </div>
          </div>
        </template>

        <!-- Text -->
        <template v-else-if="column.type === 'text'">
          <div
            class="h-2.5 rounded skeleton-shimmer"
            :style="{ width: column.width || '100px' }"
          ></div>
        </template>

        <!-- Badge -->
        <template v-else-if="column.type === 'badge'">
          <div class="h-5 w-[50px] rounded-full skeleton-shimmer"></div>
        </template>

        <!-- Actions -->
        <template v-else-if="column.type === 'actions'">
          <div class="flex justify-end gap-1">
            <div class="h-6 w-6 rounded skeleton-shimmer"></div>
            <div class="h-6 w-6 rounded skeleton-shimmer"></div>
          </div>
        </template>

        <!-- Index (Checkbox) -->
        <template v-else-if="column.type === 'index'">
          <div class="h-4 w-4 rounded-sm skeleton-shimmer"></div>
        </template>
      </TableCell>
    </TableRow>
  </template>
</template>