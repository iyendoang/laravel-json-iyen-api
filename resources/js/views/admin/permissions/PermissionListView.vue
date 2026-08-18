<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold">Permissions</h2>
        <p class="text-muted-foreground text-xs">Kelola permission akses sistem</p>
      </div>
      <Button v-if="authStore.hasPermission('create-permissions')" size="sm" @click="openCreateModal">
        <Plus class="mr-1.5 h-3.5 w-3.5"/>
        Tambah
      </Button>
    </div>

    <!-- Data Table -->
    <Card class="border-border/40">
      <CardContent class="p-0">
        <DataTable
          ref="dataTableRef"
          :columns="columns"
          :data="items"
          :pagination="pagination"
          :per-page="perPage"
          :search="search"
          :filter="guardFilter"
          :filter-options="guardOptions"
          filter-label="Guard"
          filter-placeholder="Semua Guard"
          :loading="isInitialLoading"
          :selectable="authStore.hasPermission('delete-permissions')"
          :show-bulk-delete="authStore.hasPermission('delete-permissions')"
          :show-all-per-page="true"
          :skeleton-rows="5"
          :skeleton-columns="[
                        { label: 'Nama', type: 'text', width: '200px' },
                        { label: 'Guard', type: 'badge', width: '60px' },
                        { label: 'Dibuat', type: 'text', width: '120px' },
                        { label: 'Aksi', type: 'actions' },
                    ]"
          empty-title="Tidak ada permission"
          empty-description="Permission tidak ditemukan atau belum ada data."
          @sort-change="handleSortChange"
          @page-change="changePage"
          @per-page-change="changePerPage"
          @update:search="(v) => (search = v)"
          @update:filter="(v) => (guardFilter = v)"
          @selection-change="handleSelectionChange"
          @bulk-delete="handleBulkDelete"
        >
          <template #cell-name="{ row }">
            <div class="flex items-center gap-2">
              <KeyRound class="text-primary/70 h-3.5 w-3.5"/>
              <code class="font-mono text-xs font-medium">{{ row.name }}</code>
            </div>
          </template>

          <template #cell-guard_name="{ row }">
            <Badge variant="outline" class="font-mono text-[10px]">
              {{ row.guard_name || 'api' }}
            </Badge>
          </template>

          <template #cell-created_at="{ row }">
            <span class="text-muted-foreground text-xs">{{ formatDate(row.created_at) }}</span>
          </template>

          <template #cell-actions="{ row }">
            <DataTableActions
              mode="buttons"
              :has-edit="authStore.hasPermission('edit-permissions')"
              :has-delete="authStore.hasPermission('delete-permissions')"
              @edit="openEditModal(row)"
              @delete="openDeleteDialog(row)"
            />
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Form Dialog -->
    <FormPermissionDialog
      v-model:open="modalOpen"
      :permission="editingPermission"
      @saved="handleSaved"
    />

    <!-- Delete Single Confirmation -->
    <ConfirmDialog
      v-model:open="deleteDialogOpen"
      title="Hapus Permission"
      :description="`Yakin ingin menghapus permission ${permissionToDelete?.name}?`"
      confirm-text="Hapus"
      cancel-text="Batal"
      :loading="loading"
      variant="danger"
      @confirm="confirmDelete"
      @cancel="deleteDialogOpen = false"
    />

    <!-- Bulk Delete Confirmation -->
    <ConfirmDialog
      v-model:open="bulkDeleteDialogOpen"
      title="Hapus Massal"
      :description="`Yakin ingin menghapus ${selectedRows.length} permission terpilih?`"
      :confirm-text="`Hapus ${selectedRows.length} Permission`"
      cancel-text="Batal"
      :loading="bulkDeleting"
      variant="danger"
      @confirm="confirmBulkDelete"
      @cancel="bulkDeleteDialogOpen = false"
    >
      <div class="bg-muted/30 max-h-32 overflow-y-auto rounded-md p-2">
        <div v-for="row in selectedRows" :key="row.id" class="flex items-center gap-2 py-0.5">
          <KeyRound class="text-muted-foreground h-3 w-3"/>
          <code class="font-mono text-[11px]">{{ row.name }}</code>
        </div>
      </div>
    </ConfirmDialog>
  </div>
</template>

<script setup lang="ts">
import {h, ref, watch} from 'vue'
import {useAuthStore} from '@/stores/auth-store'
import {useDataTable} from '@/composables/useDataTable'
import {permissionService} from '@/services/admin/permission.service'
import DataTable from '@/components/data-table/DataTable.vue'
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue'
import DataTableActions from '@/components/data-table/DataTableActions.vue'
import ConfirmDialog from '@/components/shared/confirm-dialog.vue'
import FormPermissionDialog from './partials/FormPermissionDialog.vue'
import {Button} from '@/components/ui/button'
import {Card, CardContent} from '@/components/ui/card'
import {Badge} from '@/components/ui/badge'
import {Plus, KeyRound} from 'lucide-vue-next'
import type {ColumnDef} from '@tanstack/vue-table'
import type {Permission, PaginatedApiResponse} from '@/types'
import type {DataTableQuery} from '@/composables/useDataTable'
import type {FilterOption} from '@/components/data-table/DataTableFilter.vue'

const authStore = useAuthStore()

// DataTable
const {
  items,
  pagination,
  perPage,
  search,
  isInitialLoading,
  changePage,
  changePerPage,
  changeSorting,
  setFilter,
  refresh,
} = useDataTable<Permission>(
  (params: DataTableQuery): Promise<PaginatedApiResponse<Permission>> => {
    return permissionService.getPermissionsWithParams(params)
  },
  10
)

const dataTableRef = ref()

// Filter
const guardFilter = ref('all')
const guardOptions: FilterOption[] = [
  {value: 'api', label: 'API'},
  {value: 'web', label: 'Web'},
]

watch(guardFilter, (value) => {
  setFilter('guard_name', value === 'all' ? undefined : value)
})

// Modal state
const modalOpen = ref(false)
const deleteDialogOpen = ref(false)
const bulkDeleteDialogOpen = ref(false)
const loading = ref(false)
const bulkDeleting = ref(false)
const editingPermission = ref<Permission | null>(null)
const permissionToDelete = ref<Permission | null>(null)
const selectedRows = ref<Permission[]>([])

// Columns
const columns: ColumnDef<Permission, any>[] = [
  {
    id: 'name',
    accessorKey: 'name',
    header: ({column}) => h(DataTableColumnHeader, {column, title: 'Nama'}),
  },
  {
    id: 'guard_name',
    accessorKey: 'guard_name',
    header: ({column}) => h(DataTableColumnHeader, {column, title: 'Guard'}),
  },
  {
    id: 'created_at',
    accessorKey: 'created_at',
    header: ({column}) => h(DataTableColumnHeader, {column, title: 'Dibuat'}),
  },
  {
    id: 'actions',
    header: 'Aksi',
    cell: () => h('div'),
  },
]

// Helpers
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

// Handlers
const handleSortChange = ({column, direction}: { column: string; direction: 'asc' | 'desc' | null }) => {
  changeSorting(column, direction)
}

const handleSelectionChange = (rows: Permission[]) => {
  selectedRows.value = rows
}

const openCreateModal = () => {
  editingPermission.value = null
  modalOpen.value = true
}

const openEditModal = (permission: Permission) => {
  editingPermission.value = permission
  modalOpen.value = true
}

const openDeleteDialog = (permission: Permission) => {
  permissionToDelete.value = permission
  deleteDialogOpen.value = true
}

const handleBulkDelete = (rows: Permission[]) => {
  selectedRows.value = rows
  bulkDeleteDialogOpen.value = true
}

// Actions
const handleSaved = async () => {
  await refresh()
}

const confirmDelete = async () => {
  if (loading.value) return
  if (!permissionToDelete.value) return

  loading.value = true
  try {
    const success = await permissionService.deletePermission(permissionToDelete.value.id)
    if (success) {
      deleteDialogOpen.value = false
      await refresh()
    }
  } catch (error: any) {
    // 🔥 Service sudah handle toast, di sini hanya log
    console.warn('Delete error:', error)
  } finally {
    loading.value = false
  }
}

const confirmBulkDelete = async () => {
  if (bulkDeleting.value) return
  if (selectedRows.value.length === 0) return

  bulkDeleting.value = true
  try {
    const results = await Promise.all(
      selectedRows.value.map((p) => permissionService.deletePermission(p.id))
    )

    // 🔥 Cek jika semua berhasil
    if (results.every(Boolean)) {
      bulkDeleteDialogOpen.value = false
      dataTableRef.value?.resetSelection()
      selectedRows.value = []
      await refresh()
    }
  } catch (error: any) {
    console.warn('Bulk delete error:', error)
  } finally {
    bulkDeleting.value = false
  }
}
</script>