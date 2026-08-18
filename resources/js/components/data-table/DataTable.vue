<script setup lang="ts">
import {
  type ColumnDef,
  FlexRender,
  getCoreRowModel,
  type SortingState,
  useVueTable,
} from '@tanstack/vue-table'
import {ref, computed} from 'vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {Button} from '@/components/ui/button'
import {Trash} from 'lucide-vue-next'
import CheckboxControl from '@/components/shared/input/checkbox-control.vue'
import TableEmptyState from '@/components/shared/table-empty-state.vue'
import TableSkeleton from '@/components/shared/table-skeleton.vue'
import DataTablePagination from './DataTablePagination.vue'
import DataTableSearch from './DataTableSearch.vue'
import DataTablePerPage from './DataTablePerPage.vue'
import DataTableFilter from './DataTableFilter.vue'
import type {PaginationMeta} from '@/types/api'
import type {FilterOption} from './DataTableFilter.vue'

interface SkeletonColumnConfig {
  label: string
  type: 'avatar-text' | 'text' | 'badge' | 'actions' | 'index'
  width?: string
}

interface Props<T> {
  columns: ColumnDef<T, any>[]
  data: T[]
  pagination?: PaginationMeta | null
  perPage?: number | null
  search?: string
  filter?: string
  filterOptions?: FilterOption[]
  filterLabel?: string
  filterPlaceholder?: string
  emptyTitle?: string
  emptyDescription?: string
  clickable?: boolean
  loading?: boolean
  showToolbar?: boolean
  showPagination?: boolean
  selectable?: boolean
  showBulkDelete?: boolean
  showAllPerPage?: boolean
  skeletonRows?: number
  skeletonColumns?: SkeletonColumnConfig[]
}

const props = withDefaults(defineProps<Props<any>>(), {
  pagination: undefined,
  perPage: 15,
  search: '',
  filter: 'all',
  filterOptions: () => [],
  filterLabel: 'Filter',
  filterPlaceholder: 'Semua',
  emptyTitle: 'Tidak ada data',
  emptyDescription: 'Data tidak ditemukan atau belum ada data.',
  clickable: false,
  loading: false,
  showToolbar: true,
  showPagination: true,
  selectable: false,
  showBulkDelete: true,
  showAllPerPage: true,
  skeletonRows: 5,
  skeletonColumns: () => [],
})

const emit = defineEmits<{
  (e: 'sort-change', payload: { column: string; direction: 'asc' | 'desc' | null }): void
  (e: 'page-change', page: number): void
  (e: 'per-page-change', perPage: number | null): void
  (e: 'row-click', row: any): void
  (e: 'update:search', value: string): void
  (e: 'update:filter', value: string): void
  (e: 'selection-change', rows: any[]): void
  (e: 'bulk-delete', rows: any[]): void
}>()

const sorting = ref<SortingState>([])
const rowSelection = ref<Record<string, boolean>>({})

const table = useVueTable({
  get data() {
    return props.data
  },
  get columns() {
    return props.columns
  },
  state: {
    get sorting() {
      return sorting.value
    },
    get rowSelection() {
      return rowSelection.value
    },
  },
  enableRowSelection: props.selectable,
  manualSorting: true,
  getRowId: (row: any) => {
    return row.id || row.ulid || ''
  },
  onSortingChange: (updater) => {
    const newSorting = typeof updater === 'function' ? updater(sorting.value) : updater
    sorting.value = newSorting
    const sort = newSorting[0]
    emit('sort-change', {
      column: sort?.id ?? '',
      direction: sort ? (sort.desc ? 'desc' : 'asc') : null,
    })
  },
  onRowSelectionChange: (updater) => {
    const newSelection = typeof updater === 'function' ? updater(rowSelection.value) : updater
    rowSelection.value = newSelection
    emit('selection-change', selectedRows.value)
  },
  getCoreRowModel: getCoreRowModel(),
})

const selectedRows = computed(() => {
  return table.getSelectedRowModel().rows.map((r) => r.original)
})

const resetSelection = () => {
  rowSelection.value = {}
  emit('selection-change', [])
}

const handleBulkDeleteClick = () => {
  emit('bulk-delete', selectedRows.value)
}

// 🔥 Generate skeleton columns dari columns jika tidak disediakan
const effectiveSkeletonColumns = computed<SkeletonColumnConfig[]>(() => {
  if (props.skeletonColumns.length > 0) return props.skeletonColumns

  return [
    ...(props.selectable ? [{label: '', type: 'index' as const, width: '40px'}] : []),
    ...props.columns.map((c) => ({
      label: typeof c.header === 'string' ? c.header : c.id || '',
      type: c.id === 'name' || c.id === 'user' ? 'avatar-text' as const : 'text' as const,
      width: '120px',
    })),
  ]
})

defineExpose({resetSelection, table})
</script>

<template>
  <div>
    <!-- Toolbar -->
    <div
      v-if="showToolbar || (selectable && selectedRows.length > 0)"
      class="border-b"
    >
      <!-- Bulk Delete Bar -->
      <div
        v-if="selectable && selectedRows.length > 0"
        class="bg-primary/5 border-primary/10 flex items-center justify-between gap-2 border-b px-3 py-2"
      >
                <span class="text-primary text-[11px] font-semibold">
                    {{ selectedRows.length }} terpilih
                </span>
        <div class="flex items-center gap-1">
          <Button
            v-if="showBulkDelete"
            variant="destructive"
            size="sm"
            class="h-7 text-[11px]"
            @click="handleBulkDeleteClick"
          >
            <Trash class="mr-1 h-3 w-3"/>
            Hapus
          </Button>
          <Button
            variant="ghost"
            size="sm"
            class="h-7 text-[11px]"
            @click="resetSelection"
          >
            Batal
          </Button>
        </div>
      </div>

      <!-- Toolbar Controls -->
      <div class="space-y-2 px-3 py-2">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <!-- Kiri: Per Page + Filter -->
          <div class="flex flex-wrap items-center gap-2">
            <DataTablePerPage
              v-if="!(selectable && selectedRows.length > 0)"
              :model-value="perPage"
              :show-all="showAllPerPage"
              @update:model-value="(v) => emit('per-page-change', v)"
            />

            <DataTableFilter
              v-if="filterOptions.length > 0 && !(selectable && selectedRows.length > 0)"
              :model-value="filter"
              :options="filterOptions"
              :label="filterLabel"
              :placeholder="filterPlaceholder"
              @update:model-value="(v) => emit('update:filter', v)"
            />
          </div>

          <!-- Kanan: Search -->
          <DataTableSearch
            v-if="!(selectable && selectedRows.length > 0)"
            :model-value="search"
            placeholder="Cari..."
            @update:model-value="(v) => emit('update:search', v)"
          />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <Table>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
            class="hover:bg-transparent"
          >
            <!-- Checkbox Header -->
            <TableHead v-if="selectable" class="h-8 w-10 px-3" @click.stop>
              <CheckboxControl
                :model-value="table.getIsAllPageRowsSelected()"
                label=""
                container-class="p-0"
                @update:model-value="(v: boolean) => table.toggleAllPageRowsSelected(v)"
              />
            </TableHead>

            <TableHead
              v-for="header in headerGroup.headers"
              :key="header.id"
              class="h-8 px-3 text-[11px] font-semibold whitespace-nowrap"
            >
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <!-- Loading State -->
          <TableSkeleton
            v-if="loading"
            :rows="skeletonRows"
            :columns="effectiveSkeletonColumns"
          />

          <!-- Empty State -->
          <TableEmptyState
            v-else-if="!table.getRowModel().rows.length"
            :colspan="columns.length + (selectable ? 1 : 0)"
            :title="emptyTitle"
            :description="emptyDescription"
          />

          <!-- Data Rows -->
          <TableRow
            v-for="row in table.getRowModel().rows"
            :key="row.id"
            :class="[
                            clickable ? 'cursor-pointer' : '',
                            row.getIsSelected() ? 'bg-primary/5' : '',
                        ]"
            @click="clickable && emit('row-click', row.original)"
          >
            <!-- Checkbox Cell -->
            <TableCell v-if="selectable" class="w-10 px-3 py-2" @click.stop>
              <CheckboxControl
                :model-value="row.getIsSelected()"
                label=""
                container-class="p-0"
                @update:model-value="(v: boolean) => row.toggleSelected(v)"
              />
            </TableCell>

            <TableCell
              v-for="cell in row.getVisibleCells()"
              :key="cell.id"
              class="px-3 py-2"
            >
              <slot
                :name="`cell-${cell.column.id}`"
                :value="cell.getValue()"
                :row="row.original"
                :index="row.index"
              >
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()"/>
              </slot>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div v-if="showPagination && pagination && !loading" class="border-t px-3 py-2">
      <DataTablePagination
        :meta="pagination"
        :per-page="perPage"
        @change-page="(p: number) => emit('page-change', p)"
        @change-per-page="(pp: number | null) => emit('per-page-change', pp)"
      />
    </div>
  </div>
</template>