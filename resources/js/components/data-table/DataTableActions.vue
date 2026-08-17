<script setup lang="ts">
import { MoreHorizontal, Pencil, Trash } from 'lucide-vue-next'
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
  editLabel?: string
  deleteLabel?: string
}>(), {
  hasEdit: true,
  hasDelete: true,
  editLabel: 'Edit',
  deleteLabel: 'Hapus',
})

const emit = defineEmits<{
  (e: 'edit'): void
  (e: 'delete'): void
}>()
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon-sm" class="h-8 w-8">
        <MoreHorizontal class="h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-36">
      <DropdownMenuItem v-if="hasEdit" @click="emit('edit')">
        <Pencil class="mr-2 h-3.5 w-3.5" />
        {{ editLabel }}
      </DropdownMenuItem>

      <DropdownMenuSeparator v-if="hasEdit && hasDelete" />

      <!-- Hapus focus:text-destructive karena sudah ada text-destructive -->
      <DropdownMenuItem
        v-if="hasDelete"
        class="text-destructive"
        @click="emit('delete')"
      >
        <Trash class="mr-2 h-3.5 w-3.5" />
        {{ deleteLabel }}
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>