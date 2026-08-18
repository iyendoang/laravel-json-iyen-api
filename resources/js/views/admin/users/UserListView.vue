<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold">Users</h2>
        <p class="text-muted-foreground text-xs">Kelola user dan role</p>
      </div>
      <Button
        v-if="authStore.hasPermission('create-users')"
        size="sm"
        @click="router.push('/admin/users/create')"
      >
        <Plus class="mr-1.5 h-3.5 w-3.5" />
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
          :filter="roleFilter"
          :filter-options="roleFilterOptions"
          filter-label="Role"
          filter-placeholder="Semua Role"
          :loading="isInitialLoading"
          :selectable="authStore.hasPermission('delete-users')"
          :show-bulk-delete="authStore.hasPermission('delete-users')"
          :show-all-per-page="true"
          :skeleton-rows="5"
          :skeleton-columns="[
                        { label: 'User', type: 'avatar-text', width: '250px' },
                        { label: 'Role', type: 'badge', width: '100px' },
                        { label: 'Status', type: 'badge', width: '80px' },
                        { label: 'Dibuat', type: 'text', width: '120px' },
                        { label: 'Aksi', type: 'actions' },
                    ]"
          empty-title="Tidak ada user"
          empty-description="User tidak ditemukan atau belum ada data."
          @sort-change="handleSortChange"
          @page-change="changePage"
          @per-page-change="changePerPage"
          @update:search="(v) => (search = v)"
          @update:filter="(v) => (roleFilter = v)"
          @selection-change="handleSelectionChange"
          @bulk-delete="handleBulkDelete"
        >
          <!-- Cell: name -->
          <template #cell-name="{ row }">
            <div class="flex items-center gap-2.5">
              <img
                :src="row.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(row.name)}&background=6366f1&color=fff`"
                :alt="row.name"
                class="h-7 w-7 rounded-full object-cover"
              />
              <div class="leading-tight">
                <div class="text-xs font-medium">{{ row.name }}</div>
                <div class="text-muted-foreground text-[10px]">{{ row.email }}</div>
              </div>
            </div>
          </template>

          <!-- Cell: role -->
          <template #cell-role="{ row }">
            <Badge variant="outline" class="text-[10px] capitalize">
              {{ formatRoleName(row.role) }}
            </Badge>
          </template>

          <!-- Cell: status -->
          <template #cell-email_verified_at="{ row }">
            <Badge :variant="row.email_verified_at ? 'outline' : 'default'" class="text-[10px]">
              {{ row.email_verified_at ? 'Verified' : 'Pending' }}
            </Badge>
          </template>

          <!-- Cell: created_at -->
          <template #cell-created_at="{ row }">
            <span class="text-muted-foreground text-xs">{{ formatDate(row.created_at) }}</span>
          </template>

          <!-- Cell: actions -->
          <template #cell-actions="{ row }">
            <DataTableActions
              mode="buttons"
              :has-edit="authStore.hasPermission('edit-users')"
              :has-delete="authStore.hasPermission('delete-users') && row.id !== authStore.user?.id"
              @edit="router.push(`/admin/users/${row.id}/edit`)"
              @delete="openDeleteDialog(row)"
            />
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Delete Single Confirmation -->
    <ConfirmDialog
      v-model:open="deleteDialogOpen"
      title="Hapus User"
      :description="`Yakin ingin menghapus user ${userToDelete?.name}?`"
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
      :description="`Yakin ingin menghapus ${selectedRows.length} user terpilih?`"
      :confirm-text="`Hapus ${selectedRows.length} User`"
      cancel-text="Batal"
      :loading="bulkDeleting"
      variant="danger"
      @confirm="confirmBulkDelete"
      @cancel="bulkDeleteDialogOpen = false"
    >
      <div class="bg-muted/30 max-h-32 overflow-y-auto rounded-md p-2">
        <div v-for="row in selectedRows" :key="row.id" class="flex items-center gap-2 py-0.5">
          <User class="text-muted-foreground h-3 w-3" />
          <span class="text-[11px]">{{ row.name }}</span>
          <span class="text-muted-foreground text-[10px]">({{ row.email }})</span>
        </div>
      </div>
    </ConfirmDialog>
  </div>
</template>

<script setup lang="ts">
import { h, ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth-store'
import { useDataTable } from '@/composables/useDataTable'
import { userService } from '@/services/admin/user.service'
import { optionService } from '@/services/admin/option.service'
import DataTable from '@/components/data-table/DataTable.vue'
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue'
import DataTableActions from '@/components/data-table/DataTableActions.vue'
import ConfirmDialog from '@/components/shared/confirm-dialog.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Plus, User } from 'lucide-vue-next'
import type { ColumnDef } from '@tanstack/vue-table'
import type { User as UserType, OptionItem, PaginatedApiResponse } from '@/types'
import type { DataTableQuery } from '@/composables/useDataTable'
import type { FilterOption } from '@/components/data-table/DataTableFilter.vue'

const router = useRouter()
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
  setFilter,
  refresh,
} = useDataTable<UserType>(
  (params: DataTableQuery): Promise<PaginatedApiResponse<UserType>> => {
    return userService.getUsersWithParams(params)
  },
  10
)

const dataTableRef = ref()

// 🔥 Filter Role
const roleFilter = ref('all')
const roleFilterOptions = ref<FilterOption[]>([])

// Watch filter role
watch(roleFilter, (value) => {
  setFilter('role', value === 'all' ? undefined : value)
})

// Load role options untuk filter
onMounted(async () => {
  const roles = await optionService.getRoleOptionsAll()
  roleFilterOptions.value = roles.map((r) => ({
    value: r.value,
    label: r.label,
  }))
})

const deleteDialogOpen = ref(false)
const bulkDeleteDialogOpen = ref(false)
const loading = ref(false)
const bulkDeleting = ref(false)
const userToDelete = ref<UserType | null>(null)
const selectedRows = ref<UserType[]>([])

const columns: ColumnDef<UserType, any>[] = [
  {
    id: 'name',
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'User' }),
  },
  {
    id: 'role',
    accessorKey: 'role',
    header: 'Role',
  },
  {
    id: 'email_verified_at',
    accessorKey: 'email_verified_at',
    header: 'Status',
  },
  {
    id: 'created_at',
    accessorKey: 'created_at',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Dibuat' }),
  },
  {
    id: 'actions',
    header: 'Aksi',
    cell: () => h('div'),
  },
]

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const formatRoleName = (name?: string) => {
  return name?.replace(/-/g, ' ') || '-'
}

const handleSortChange = ({ column, direction }: { column: string; direction: 'asc' | 'desc' | null }) => {
  changeSorting(column, direction)
}

const handleSelectionChange = (rows: UserType[]) => {
  selectedRows.value = rows
}

const openDeleteDialog = (user: UserType) => {
  userToDelete.value = user
  deleteDialogOpen.value = true
}

const handleBulkDelete = (rows: UserType[]) => {
  selectedRows.value = rows
  bulkDeleteDialogOpen.value = true
}

const confirmDelete = async () => {
  if (loading.value) return
  if (!userToDelete.value) return

  loading.value = true
  try {
    const success = await userService.deleteUser(userToDelete.value.id)
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
      selectedRows.value.map((u) => userService.deleteUser(u.id))
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