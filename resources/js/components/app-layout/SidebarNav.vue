<template>
  <SidebarGroup class="py-1">
    <SidebarGroupLabel
      class="text-muted-foreground/40 px-2 py-1 text-[10px] font-bold tracking-wider uppercase select-none">
      {{ label }}
    </SidebarGroupLabel>

    <SidebarMenu class="gap-0.5">
      <template v-for="item in items" :key="item.title || Math.random()">
        <!-- Divider -->
        <div v-if="isMenuDivider(item)" class="border-border/30 my-2 border-t">
                    <span v-if="item.title" class="text-muted-foreground/50 px-3 text-[9px] font-semibold uppercase">
                        {{ item.title }}
                    </span>
        </div>

        <!-- Menu Leaf -->
        <SidebarMenuItem v-else-if="isMenuLeaf(item)">
          <SidebarMenuButton
            as-child
            :is-active="isItemActive(item)"
            :tooltip="item.tooltip || item.title"
            class="group hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground data-[active=true]:bg-sidebar-accent data-[active=true]:text-sidebar-accent-foreground h-8.5 w-full rounded-md px-2.5 text-xs font-medium transition-all duration-150 data-[active=true]:font-semibold"
          >
            <RouterLink :to="{ name: item.routeName }" class="flex w-full items-center gap-2.5">
              <component
                :is="item.icon"
                v-if="item.icon"
                class="text-muted-foreground group-hover:text-sidebar-accent-foreground group-data-[active=true]:text-sidebar-accent-foreground size-4 shrink-0 transition-colors"
              />
              <span class="truncate transition-opacity duration-200 group-data-[state=collapsed]:opacity-0">
                                {{ item.title }}
                            </span>
            </RouterLink>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <!-- Menu Parent (Dropdown Group) -->
        <Collapsible
          v-else-if="isMenuParent(item)"
          :open="isGroupExpanded(item)"
          class="group/collapsible"
        >
          <SidebarMenuItem>
            <CollapsibleTrigger as-child>
              <SidebarMenuButton
                :tooltip="item.tooltip || item.title"
                :is-active="isItemActive(item)"
                class="group hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground data-[active=true]:bg-sidebar-accent data-[active=true]:text-sidebar-accent-foreground flex h-8.5 w-full items-center gap-2.5 rounded-md px-2.5 text-xs font-medium transition-all duration-150 data-[active=true]:font-semibold"
                @click="toggleGroup(item.title)"
              >
                <component
                  :is="item.icon"
                  v-if="item.icon"
                  class="text-muted-foreground group-hover:text-sidebar-accent-foreground group-data-[active=true]:text-sidebar-accent-foreground size-4 shrink-0 transition-colors"
                />
                <span class="truncate transition-opacity duration-200 group-data-[state=collapsed]:opacity-0">
                                    {{ item.title }}
                                </span>
                <ChevronRight
                  class="text-muted-foreground/70 ml-auto size-3.5 shrink-0 transition-transform duration-200 ease-in-out group-data-[state=collapsed]:hidden group-data-[state=open]/collapsible:rotate-90"
                />
              </SidebarMenuButton>
            </CollapsibleTrigger>

            <CollapsibleContent
              class="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down overflow-hidden">
              <SidebarMenuSub
                class="border-border/40 mx-4.5 mt-0.5 space-y-0.5 border-l pl-2.5 group-data-[state=collapsed]:hidden">
                <SidebarMenuSubItem v-for="child in item.children" :key="child.title">
                  <SidebarMenuSubButton
                    as-child
                    :is-active="isItemActive(child)"
                    class="group text-muted-foreground hover:bg-sidebar-accent/40 hover:text-sidebar-accent-foreground data-[active=true]:bg-sidebar-accent/70 data-[active=true]:text-sidebar-accent-foreground h-7.5 w-full rounded-md px-2 text-xs font-medium transition-all duration-150 data-[active=true]:font-semibold"
                  >
                    <RouterLink :to="{ name: child.routeName }" class="flex w-full items-center gap-2">
                      <component
                        :is="child.icon"
                        v-if="child.icon"
                        class="text-muted-foreground/60 group-hover:text-sidebar-accent-foreground group-data-[active=true]:text-sidebar-accent-foreground size-3.5 shrink-0 transition-colors"
                      />
                      <span class="truncate">{{ child.title }}</span>
                    </RouterLink>
                  </SidebarMenuSubButton>
                </SidebarMenuSubItem>
              </SidebarMenuSub>
            </CollapsibleContent>
          </SidebarMenuItem>
        </Collapsible>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>

<script setup lang="ts">
import {watch, ref} from 'vue'
import {useRoute, RouterLink} from 'vue-router'
import {ChevronRight} from 'lucide-vue-next'
import {useMenu} from '@/composables/useMenu'
import type {MenuItem} from '@/types/menu'

import {Collapsible, CollapsibleContent, CollapsibleTrigger} from '@/components/ui/collapsible'

import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from '@/components/ui/sidebar'

const props = defineProps<{
  label: string
  items: MenuItem[]
}>()

const route = useRoute()

const {
  isItemActive,
  isItemOrChildActive,
  isMenuLeaf,
  isMenuParent,
  isMenuDivider,
} = useMenu()

const openGroups = ref<Record<string, boolean>>({})

const syncOpenStates = () => {
  props.items.forEach((item) => {
    if (isMenuParent(item)) {
      if (isItemOrChildActive(item)) {
        openGroups.value[item.title] = true
      } else if (openGroups.value[item.title] === undefined) {
        openGroups.value[item.title] = item.defaultOpen || false
      }
    }
  })
}

watch(() => route.path, () => syncOpenStates(), {immediate: true})

const toggleGroup = (title: string) => {
  openGroups.value[title] = !openGroups.value[title]
}

const isGroupExpanded = (group: any) => {
  return openGroups.value[group.title] !== undefined
    ? openGroups.value[group.title]
    : !!group.defaultOpen
}
</script>