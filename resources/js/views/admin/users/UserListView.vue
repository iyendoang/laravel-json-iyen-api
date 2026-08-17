<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-bold">Users</h2>
      <div class="flex items-center gap-2">
        <!-- Search -->
        <Input
          v-model="search"
          placeholder="Cari user..."
          class="h-9 w-64"
        />
        <Button @click="openCreateModal">
          <Plus class="mr-2 h-4 w-4"/>
          Tambah User
        </Button>
      </div>
    </div>

    <!-- Data Table -->
    <Card>
      <CardContent class="p-0">
        <AppDataTable
          :columns="columns"
          :data="items"
          :pagination="pagination"
          :per-page="perPage"
          :empty-title="'Tidak ada user'"
          :empty-description="'User tidak ditemukan atau belum ada data.'"
          :clickable="true"
          :selectable="true"
          @sort-change="handleSortChange"
          @page-change="changePage"
          @per-page-change="changePerPage"
          @row-click="handleRowClick"
        >
          <!-- Custom cell untuk status -->
          <template #cell(status)="{ value }">
            <Badge :variant="value === 'active' ? 'default' : 'secondary'">
              {{ value }}
            </Badge>
          </template>
        </AppDataTable>
      </CardContent>
    </Card>

    <!-- Loading Overlay -->
    <div v-if="isInitialLoading" class="text-center py-8">
      <Loader2 class="mx-auto h-6 w-6 animate-spin"/>
      <p class="mt-2 text-sm text-muted-foreground">Memuat data...</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {h, ref} from 'vue'
import {useRouter} from 'vue-router'
import {useDataTable} from '@/composables/useDataTable'
import {userService} from '@/services/admin/user.service'
import AppDataTable from '@/components/data-table/app-datatable.vue'
import DataTableColumnHeader from '@/components/data-table/data-table-column-header.vue'
import {Button} from '@/components/ui/button'
import {Input} from '@/components/ui/input'
import {Card, CardContent} from '@/components/ui/card'
import {Badge} from '@/components/ui/badge'
import {Avatar, AvatarFallback, AvatarImage} from '@/components/ui/avatar'
import {Plus, Loader2, MoreHorizontal, Pencil, Trash} from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import type {ColumnDef} from '@tanstack/vue-table'
import type {User} from '@/types'

const router = useRouter()

// Gunakan useDataTable
const {
  items,
  pagination,
  page,
  perPage,
  search,
  loading,
  isInitialLoading,
  isRefetching,
  isEmpty,
  changePage,
  changePerPage,
  changeSorting,
  refresh,
} = useDataTable<User>((params) => userService.getUsersWithParams(params), 10)

// Column definitions
const columns: ColumnDef<User, any>[] = [
  {
    id: 'name',
    accessorKey: 'name',
    header: ({column}) => h(DataTableColumnHeader, {column, title: 'Nama'}),
    cell: ({row}) => {
      const user = row.original
      return h('div', {class: 'flex items-center gap-3'}, [
        h(Avatar, null, {
          default: () => [
            h(AvatarImage, {src: user.avatar || ''}),
            h(AvatarFallback, null, {
              default: () => user.name?.charAt(0).toUpperCase() || '?',
            }),
          ],
        }),
        h('div', null, [
          h('div', {class: 'font-medium'}, user.name),
          h('div', {class: 'text-xs text-muted-foreground'}, user.email),
        ]),
      ])
    },
  },
  {
    id: 'role',
    accessorKey: 'role',
    header: ({column}) => h(DataTableColumnHeader, {column, title: 'Role'}),
    cell: ({row}) => {
      return h(Badge, {variant: 'outline'}, {
        default: () => row.original.role,
      })
    },
  },
  {
    id: 'created_at',
    accessorKey: 'created_at',
    header: ({column}) => h(DataTableColumnHeader, {column, title: 'Tanggal'}),
    cell: ({row}) => {
      return new Date(row.original.created_at).toLocaleDateString('id-ID')
    },
  },
  {
    id: 'actions',
    header: '',
    cell: ({row}) => {
      return h(DropdownMenu, null, {
        default: () => [
          h(DropdownMenuTrigger, {asChild: true}, {
            default: () => h(Button, {variant: 'ghost', size: 'icon'}, {
              default: () => h(MoreHorizontal, {class: 'h-4 w-4'}),
            }),
          }),
          h(DropdownMenuContent, {align: 'end'}, {
            default: () => [
              h(DropdownMenuItem, {onClick: () => handleEdit(row.original)}, {
                default: () => [h(Pencil, {class: 'mr-2 h-4 w-4'}), 'Edit'],
              }),
              h(DropdownMenuItem, {onClick: () => handleDelete(row.original), class: 'text-red-600'}, {
                default: () => [h(Trash, {class: 'mr-2 h-4 w-4'}), 'Delete'],
              }),
            ],
          }),
        ],
      })
    },
  },
]

const handleSortChange = ({column, direction}: { column: string; direction: 'asc' | 'desc' | null }) => {
  changeSorting(column, direction)
}

const handleRowClick = (row: User) => {
  console.log('Row clicked:', row)
}

const handleEdit = (user: User) => {
  // Buka modal edit
}

const handleDelete = async (user: User) => {
  if (confirm(`Yakin ingin menghapus ${user.name}?`)) {
    await userService.deleteUser(user.id)
    await refresh()
  }
}

const openCreateModal = () => {
  // Buka modal create
}
</script>