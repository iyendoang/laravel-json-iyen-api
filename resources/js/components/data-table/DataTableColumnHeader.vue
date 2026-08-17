<script setup lang="ts">
import {ChevronUp, ChevronDown, ChevronsUpDown} from 'lucide-vue-next'
import type {Column} from '@tanstack/vue-table'
import {Button} from '@/components/ui/button'

const props = defineProps<{
  column: Column<any, any>
  title: string
}>()
</script>

<template>
  <Button
    variant="ghost"
    size="sm"
    class="-ml-3 h-8 data-[state=open]:bg-accent"
    @click="column.toggleSorting(column.getIsSorted() === 'asc')"
  >
    <span>{{ title }}</span>
    <ChevronUp v-if="column.getIsSorted() === 'asc'" class="ml-1 h-3.5 w-3.5"/>
    <ChevronDown v-else-if="column.getIsSorted() === 'desc'" class="ml-1 h-3.5 w-3.5"/>
    <ChevronsUpDown v-else class="ml-1 h-3.5 w-3.5 opacity-50"/>
  </Button>
</template>