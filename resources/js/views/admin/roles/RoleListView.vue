<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold">Roles</h2>
        <p class="text-muted-foreground text-xs">Kelola role dan permission</p>
      </div>
      <Button v-if="authStore.hasPermission('create-roles')" size="sm" @click="openCreateModal">
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
          :loading="isInitialLoading"
          :selectable="authStore.hasPermission('delete-roles')"
          :show-bulk-delete="authStore.hasPermission('delete-roles')"
          :show-all-per-page="true"
          :skeleton-rows="5"
          :skeleton-columns="[
                        { label: 'Nama', type: 'text', width: '150px' },
                        { label: 'Permissions', type: 'badge', width: '200px' },
                        { label: 'Dibuat', type: 'text', width: '120px' },
                        { label: 'Aksi', type: 'actions' },
                    ]"
          empty-title="Tidak ada role"
          empty-description="Role tidak ditemukan atau belum ada data."
          @sort-change="handleSortChange"
          @page-change="changePage"
          @per-page-change="changePerPage"
          @update:search="(v) => (search = v)"
          @selection-change="handleSelectionChange"
          @bulk-delete="handleBulkDelete"
        >
          <!-- Cell: name -->
          <template #cell-name="{ row }">
            <div class="flex items-center gap-2">
              <Shield class="text-primary/70 h-3.5 w-3.5"/>
              <span class="text-xs font-medium capitalize">{{ formatRoleName(row.name) }}</span>
            </div>
          </template>

          <!-- Cell: permissions -->
          <template #cell-permissions="{ row }">
            <div class="flex flex-wrap gap-1">
              <Badge
                v-for="permission in row.permissions?.slice(0, 3)"
                :key="permission"
                variant="secondary"
                class="font-mono text-[9px]"
              >
                {{ permission }}
              </Badge>
              <Badge v-if="row.permissions?.length > 3" variant="outline" class="text-[9px]">
                +{{ row.permissions.length - 3 }}
              </Badge>
            </div>
          </template>

          <!-- Cell: created_at -->
          <template #cell-created_at="{ row }">
            <span class="text-muted-foreground text-xs">{{ formatDate(row.created_at) }}</span>
          </template>

          <!-- Cell: actions -->
          <template #cell-actions="{ row }">
            <DataTableActions
              mode="buttons"
              :has-edit="authStore.hasPermission('edit-roles') && row.name !== 'super-admin'"
              :has-delete="authStore.hasPermission('delete-roles') && row.name !== 'super-admin'"
              @edit="openEditModal(row)"
              @delete="openDeleteDialog(row)"
            />
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Form Dialog -->
    <FormRoleDialog
      v-model:open="modalOpen"
      :role="editingRole"
      :permissions="permissionOptions"
      @saved="handleSaved"
    />

    <!-- Delete Single Confirmation -->
    <ConfirmDialog
      v-model:open="deleteDialogOpen"
      title="Hapus Role"
      :description="`Yakin ingin menghapus role ${formatRoleName(roleToDelete?.name)}?`"
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
      :description="`Yakin ingin menghapus ${selectedRows.length} role terpilih?`"
      :confirm-text="`Hapus ${selectedRows.length} Role`"
      cancel-text="Batal"
      :loading="bulkDeleting"
      variant="danger"
      @confirm="confirmBulkDelete"
      @cancel="bulkDeleteDialogOpen = false"
    >
      <div class="bg-muted/30 max-h-32 overflow-y-auto rounded-md p-2">
        <div v-for="row in selectedRows" :key="row.id" class="flex items-center gap-2 py-0.5">
          <Shield class="text-muted-foreground h-3 w-3"/>
          <span class="text-[11px] capitalize">{{ formatRoleName(row.name) }}</span>
        </div>
      </div>
    </ConfirmDialog>
  </div>
</template>

<script setup lang="ts">
import {h, ref} from 'vue'
import {useAuthStore} from '@/stores/auth-store'
import {useDataTable} from '@/composables/useDataTable'
import {roleService} from '@/services/admin/role.service'
import {optionService} from '@/services/admin/option.service'
import DataTable from '@/components/data-table/DataTable.vue'
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue'
import DataTableActions from '@/components/data-table/DataTableActions.vue'
import ConfirmDialog from '@/components/shared/confirm-dialog.vue'
import FormRoleDialog from './partials/FormRoleDialog.vue'
import {Button} from '@/components/ui/button'
import {Card, CardContent} from '@/components/ui/card'
import {Badge} from '@/components/ui/badge'
import {Plus, Shield} from 'lucide-vue-next'
import type {ColumnDef} from '@tanstack/vue-table'
import type {Role, OptionItem, PaginatedApiResponse} from '@/types'
import type {DataTableQuery} from '@/composables/useDataTable'

const authStore = useAuthStore()

const {
  items,
  pagination,
  perPage,
  search,
  isInitialLoading,
  changePage,
  changePerPage,
  changeSorting,
  refresh,
} = useDataTable<Role>(
  (params: DataTableQuery): Promise<PaginatedApiResponse<Role>> => {
    return roleService.getRolesWithParams(params)
  },
  10
)

const dataTableRef = ref()

// Modal state
const modalOpen = ref(false)
const deleteDialogOpen = ref(false)
const bulkDeleteDialogOpen = ref(false)
const loading = ref(false)
const bulkDeleting = ref(false)
const editingRole = ref<Role | null>(null)
const roleToDelete = ref<Role | null>(null)
const selectedRows = ref<Role[]>([])
const permissionOptions = ref<OptionItem[]>([])

// Load permissions untuk form
const loadPermissions = async () => {
  permissionOptions.value = await optionService.getPermissionOptionsAll()
}

// Columns
const columns: ColumnDef<Role, any>[] = [
  {
    id: 'name',
    accessorKey: 'name',
    header: ({column}) => h(DataTableColumnHeader, {column, title: 'Nama'}),
  },
  {
    id: 'permissions',
    accessorKey: 'permissions',
    header: 'Permissions',
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

const formatRoleName = (name?: string) => {
  return name?.replace(/-/g, ' ') || ''
}

// Handlers
const handleSortChange = ({column, direction}: { column: string; direction: 'asc' | 'desc' | null }) => {
  changeSorting(column, direction)
}

const handleSelectionChange = (rows: Role[]) => {
  selectedRows.value = rows
}

const openCreateModal = () => {
  editingRole.value = null
  loadPermissions()
  modalOpen.value = true
}

const openEditModal = (role: Role) => {
  editingRole.value = role
  loadPermissions()
  modalOpen.value = true
}

const openDeleteDialog = (role: Role) => {
  roleToDelete.value = role
  deleteDialogOpen.value = true
}

const handleBulkDelete = (rows: Role[]) => {
  selectedRows.value = rows
  bulkDeleteDialogOpen.value = true
}

// Actions
const handleSaved = async () => {
  await refresh()
}

const confirmDelete = async () => {
  if (loading.value) return
  if (!roleToDelete.value) return

  loading.value = true
  try {
    const success = await roleService.deleteRole(roleToDelete.value.id)
    if (success) {
      deleteDialogOpen.value = false
      await refresh()
    }
  } catch (error: any) {
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
      selectedRows.value.map((r) => roleService.deleteRole(r.id))
    )

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