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
          @sort-change="handleSortChange"
          @page-change="changePage"
          @per-page-change="changePerPage"
          @update:search="(v) => (search = v)"
          @update:filter="(v) => (guardFilter = v)"
          @selection-change="handleSelectionChange"
          @bulk-delete="handleBulkDelete"
        >
          <!-- Cell: name -->
          <template #cell-name="{ row }">
            <div class="flex items-center gap-2">
              <KeyRound class="text-primary/70 h-3.5 w-3.5"/>
              <code class="font-mono text-xs font-medium">{{ row.name }}</code>
            </div>
          </template>

          <!-- Cell: guard_name -->
          <template #cell-guard_name="{ row }">
            <Badge variant="outline" class="font-mono text-[10px]">
              {{ row.guard_name || 'api' }}
            </Badge>
          </template>

          <!-- Cell: created_at -->
          <template #cell-created_at="{ row }">
            <span class="text-muted-foreground text-xs">{{ formatDate(row.created_at) }}</span>
          </template>

          <!-- Cell: actions -->
          <template #cell-actions="{ row }">
            <DataTableActions
              :has-edit="authStore.hasPermission('edit-permissions')"
              :has-delete="authStore.hasPermission('delete-permissions')"
              @edit="openEditModal(row)"
              @delete="openDeleteDialog(row)"
            />
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Modal Create/Edit -->
    <Dialog v-model:open="modalOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <KeyRound class="text-primary h-4 w-4"/>
            {{ editingPermission ? 'Edit Permission' : 'Tambah Permission' }}
          </DialogTitle>
          <DialogDescription>
            {{
              editingPermission ? 'Perbarui nama permission di bawah ini.' : 'Tambahkan permission baru untuk sistem.'
            }}
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-2">
            <Label for="name" class="text-xs font-medium">Nama Permission</Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="contoh: view-reports"
              class="h-9 font-mono text-xs"
              :disabled="loading"
            />
            <p class="text-muted-foreground text-[10px]">
              Gunakan huruf kecil dan tanda hubung (-)
            </p>
          </div>

          <DialogFooter class="gap-2">
            <Button type="button" variant="outline" size="sm" @click="modalOpen = false" :disabled="loading">
              Batal
            </Button>
            <Button type="submit" size="sm" :disabled="loading">
              <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin"/>
              {{ loading ? 'Menyimpan...' : 'Simpan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Single Confirmation -->
    <Dialog v-model:open="deleteDialogOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2 text-destructive">
            <Trash class="h-4 w-4"/>
            Hapus Permission
          </DialogTitle>
          <DialogDescription>
            Yakin ingin menghapus permission <strong class="font-mono">{{ permissionToDelete?.name }}</strong>?
            Tindakan ini tidak dapat dibatalkan.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <Button variant="outline" size="sm" @click="deleteDialogOpen = false" :disabled="loading">
            Batal
          </Button>
          <Button variant="destructive" size="sm" @click="confirmDelete" :disabled="loading">
            <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin"/>
            {{ loading ? 'Menghapus...' : 'Hapus' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Bulk Delete Confirmation -->
    <Dialog v-model:open="bulkDeleteDialogOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2 text-destructive">
            <Trash class="h-4 w-4"/>
            Hapus Massal
          </DialogTitle>
          <DialogDescription>
            Yakin ingin menghapus <strong>{{ selectedRows.length }}</strong> permission terpilih?
            Tindakan ini tidak dapat dibatalkan.
          </DialogDescription>
        </DialogHeader>

        <!-- Daftar permission yang akan dihapus -->
        <div class="bg-muted/30 max-h-32 overflow-y-auto rounded-md p-2">
          <div
            v-for="row in selectedRows"
            :key="row.id"
            class="flex items-center gap-2 py-0.5"
          >
            <KeyRound class="text-muted-foreground h-3 w-3"/>
            <code class="font-mono text-[11px]">{{ row.name }}</code>
          </div>
        </div>

        <DialogFooter class="gap-2">
          <Button variant="outline" size="sm" @click="bulkDeleteDialogOpen = false" :disabled="bulkDeleting">
            Batal
          </Button>
          <Button variant="destructive" size="sm" @click="confirmBulkDelete" :disabled="bulkDeleting">
            <Loader2 v-if="bulkDeleting" class="mr-1.5 h-3.5 w-3.5 animate-spin"/>
            {{ bulkDeleting ? 'Menghapus...' : `Hapus ${selectedRows.length} Permission` }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
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
import {Button} from '@/components/ui/button'
import {Input} from '@/components/ui/input'
import {Label} from '@/components/ui/label'
import {Card, CardContent} from '@/components/ui/card'
import {Badge} from '@/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {Plus, Loader2, KeyRound, Trash} from 'lucide-vue-next'
import {toast} from 'vue-sonner'
import api from '@/lib/api'
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
  }
)

// Ref DataTable
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
const form = ref({name: ''})

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
  form.value = {name: ''}
  modalOpen.value = true
}

const openEditModal = (permission: Permission) => {
  editingPermission.value = permission
  form.value = {name: permission.name}
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
const handleSubmit = async () => {
  if (!form.value.name.trim()) {
    toast.error('Nama permission wajib diisi')
    return
  }

  loading.value = true
  try {
    if (editingPermission.value) {
      await permissionService.updatePermission(editingPermission.value.id, {
        name: form.value.name.trim().toLowerCase(),
      })
      toast.success('Permission berhasil diperbarui')
    } else {
      await permissionService.createPermission({
        name: form.value.name.trim().toLowerCase(),
      })
      toast.success('Permission berhasil dibuat')
    }
    modalOpen.value = false
    await refresh()
  } catch (error: any) {
    toast.error(error?.response?.data?.message || 'Terjadi kesalahan')
  } finally {
    loading.value = false
  }
}

const confirmDelete = async () => {
  if (!permissionToDelete.value) return

  loading.value = true
  try {
    await permissionService.deletePermission(permissionToDelete.value.id)
    toast.success('Permission berhasil dihapus')
    deleteDialogOpen.value = false
    await refresh()
  } catch (error: any) {
    toast.error(error?.response?.data?.message || 'Terjadi kesalahan')
  } finally {
    loading.value = false
  }
}

const confirmBulkDelete = async () => {
  if (selectedRows.value.length === 0) return

  bulkDeleting.value = true
  try {
    await Promise.all(
      selectedRows.value.map((p) => permissionService.deletePermission(p.id))
    )
    toast.success(`${selectedRows.value.length} permission berhasil dihapus`)
    bulkDeleteDialogOpen.value = false
    dataTableRef.value?.resetSelection()
    selectedRows.value = []
    await refresh()
  } catch (error: any) {
    toast.error(error?.response?.data?.message || 'Terjadi kesalahan')
  } finally {
    bulkDeleting.value = false
  }
}
</script>