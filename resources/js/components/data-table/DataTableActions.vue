<script setup lang="ts">
import { MoreHorizontal, Pencil, Trash, Eye, Copy, Download } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

withDefaults(defineProps<{
  hasEdit?: boolean
  hasDelete?: boolean
  hasView?: boolean
  hasCopy?: boolean
  hasDownload?: boolean
  editLabel?: string
  deleteLabel?: string
  viewLabel?: string
  copyLabel?: string
  downloadLabel?: string
  mode?: 'dropdown' | 'buttons'
}>(), {
  hasEdit: false,
  hasDelete: false,
  hasView: false,
  hasCopy: false,
  hasDownload: false,
  editLabel: 'Edit',
  deleteLabel: 'Hapus',
  viewLabel: 'Lihat',
  copyLabel: 'Salin',
  downloadLabel: 'Unduh',
  mode: 'dropdown',
})

const emit = defineEmits<{
  (e: 'edit'): void
  (e: 'delete'): void
  (e: 'view'): void
  (e: 'copy'): void
  (e: 'download'): void
}>()
</script>

<template>
  <!-- Dropdown Mode -->
  <DropdownMenu v-if="mode === 'dropdown'">
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon-sm" class="h-8 w-8">
        <MoreHorizontal class="h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-40">
      <!-- 🔥 Slot untuk custom dropdown items di atas -->
      <slot name="dropdown-start" />

      <DropdownMenuItem v-if="hasView" @click="emit('view')">
        <Eye class="mr-2 h-3.5 w-3.5" />
        {{ viewLabel }}
      </DropdownMenuItem>

      <DropdownMenuItem v-if="hasEdit" @click="emit('edit')">
        <Pencil class="mr-2 h-3.5 w-3.5" />
        {{ editLabel }}
      </DropdownMenuItem>

      <DropdownMenuItem v-if="hasCopy" @click="emit('copy')">
        <Copy class="mr-2 h-3.5 w-3.5" />
        {{ copyLabel }}
      </DropdownMenuItem>

      <DropdownMenuItem v-if="hasDownload" @click="emit('download')">
        <Download class="mr-2 h-3.5 w-3.5" />
        {{ downloadLabel }}
      </DropdownMenuItem>

      <!-- 🔥 Slot untuk custom dropdown items di tengah -->
      <slot name="dropdown-middle" />

      <DropdownMenuSeparator v-if="hasDelete" />

      <DropdownMenuItem
        v-if="hasDelete"
        class="text-destructive"
        @click="emit('delete')"
      >
        <Trash class="mr-2 h-3.5 w-3.5" />
        {{ deleteLabel }}
      </DropdownMenuItem>

      <!-- 🔥 Slot untuk custom dropdown items di bawah -->
      <slot name="dropdown-end" />
    </DropdownMenuContent>
  </DropdownMenu>

  <!-- Buttons Mode -->
  <div v-else class="flex items-center justify-end gap-0.5">
    <!-- 🔥 Slot untuk custom buttons di awal -->
    <slot name="buttons-start" />

    <Button v-if="hasView" variant="ghost" size="icon-sm" class="h-8 w-8" @click.stop="emit('view')">
      <Eye class="h-3.5 w-3.5" />
    </Button>

    <Button v-if="hasEdit" variant="ghost" size="icon-sm" class="h-8 w-8" @click.stop="emit('edit')">
      <Pencil class="h-3.5 w-3.5" />
    </Button>

    <Button v-if="hasCopy" variant="ghost" size="icon-sm" class="h-8 w-8" @click.stop="emit('copy')">
      <Copy class="h-3.5 w-3.5" />
    </Button>

    <Button v-if="hasDownload" variant="ghost" size="icon-sm" class="h-8 w-8" @click.stop="emit('download')">
      <Download class="h-3.5 w-3.5" />
    </Button>

    <!-- 🔥 Slot untuk custom buttons di tengah -->
    <slot name="buttons-middle" />

    <Button
      v-if="hasDelete"
      variant="ghost"
      size="icon-sm"
      class="text-destructive hover:text-destructive h-8 w-8"
      @click.stop="emit('delete')"
    >
      <Trash class="h-3.5 w-3.5" />
    </Button>

    <!-- 🔥 Slot untuk custom buttons di akhir -->
    <slot name="buttons-end" />
  </div>
</template>