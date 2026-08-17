<script setup lang="ts">
import {
  Table,
  TableHeader,
  TableHead,
  TableRow,
  TableBody,
  TableCell,
} from "@/components/ui/table"

interface ColumnConfig {
    label: string
    type: 'avatar-text' | 'text' | 'badge' | 'actions' | 'index'
    width?: string
}

interface Props {
  rows?: number
  columns: ColumnConfig[]
}

const props = withDefaults(defineProps<Props>(), {
  rows: 10,
})
</script>

<template>
  <Table class="text-sm">

    <!-- HEADER -->
    <TableHeader class="bg-muted/30 border-b">
      <TableRow>
        <TableHead
            v-for="(column, index) in columns"
            :key="index"
            :class="[
                'h-10 px-4 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground',
                column.type === 'actions' ? 'text-right' : 'text-left'
              ]"
        >
          {{ column.label }}
        </TableHead>
      </TableRow>
    </TableHeader>

    <!-- BODY -->
    <TableBody>
      <TableRow
          v-for="row in rows"
          :key="row"
          class="h-10 border-b"
      >
        <TableCell
            v-for="(column, index) in columns"
            :key="index"
            class="px-4 py-2"
        >

          <!-- Avatar + Text -->
          <template v-if="column.type === 'avatar-text'">
            <div class="flex items-center gap-3">
              <div class="h-8 w-8 rounded-full skeleton-shimmer"></div>
              <div class="space-y-2">
                <div class="h-3 w-[120px] rounded-md skeleton-shimmer"></div>
                <div class="h-2.5 w-[80px] rounded-md skeleton-shimmer opacity-60"></div>
              </div>
            </div>
          </template>

          <!-- Text -->
          <template v-else-if="column.type === 'text'">
            <div
                class="h-3 rounded-md skeleton-shimmer"
                :style="{ width: column.width || '150px' }"
            ></div>
          </template>

          <!-- Badge -->
          <template v-else-if="column.type === 'badge'">
            <div class="h-5 w-[60px] rounded-full skeleton-shimmer"></div>
          </template>

          <!-- Actions -->
          <template v-else-if="column.type === 'actions'">
            <div class="flex justify-end gap-2">
              <div class="h-7 w-7 rounded-md skeleton-shimmer"></div>
              <div class="h-7 w-7 rounded-md skeleton-shimmer"></div>
            </div>
          </template>

        </TableCell>
      </TableRow>
    </TableBody>

  </Table>
</template>